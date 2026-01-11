<?php

namespace app\controllers\api\v1;

use app\components\AgentToolExecutor;
use app\components\LlmClient;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;

class ChatController extends Controller
{
    /** @var int Maximum tool call iterations to prevent infinite loops */
    private $maxIterations = 5;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        $behaviors['rateLimiter'] = [
            'class' => \app\components\RateLimiter::class,
            'limit' => 20,
            'window' => 60,
            'only' => ['send'],
            'category' => 'chat',
        ];

        return $behaviors;
    }

    /**
     * GET /api/v1/chat/greeting
     *
     * Returns the bot greeting message from salon settings.
     */
    public function actionGreeting()
    {
        $salon = \app\models\Salon::find()->where(['is_active' => 1])->limit(1)->one();
        $greeting = $salon ? $salon->getChatGreeting() : \app\models\Salon::DEFAULT_CHAT_GREETING;

        return ['greeting' => $greeting];
    }

    /**
     * POST /api/v1/chat
     *
     * Request body:
     *   - message (string, required): User message
     *   - conversation_id (string, optional): For conversation continuity
     *   - history (array, optional): Previous messages [['role' => '...', 'content' => '...'], ...]
     *
     * Response:
     *   - reply (string): Agent's text response
     *   - conversation_id (string): Conversation identifier
     *   - tool_calls_made (array): List of tools that were called (for transparency)
     */
    public function actionSend()
    {
        $request = Yii::$app->request;
        $message = $request->getBodyParam('message');

        if (empty($message) || !is_string($message)) {
            throw new BadRequestHttpException('Message is required.');
        }

        $conversationId = $request->getBodyParam('conversation_id', '');
        $clientHistory = $request->getBodyParam('history', []);

        if (empty($conversationId) || !preg_match('/^[a-f0-9]{1,64}$/i', $conversationId)) {
            $conversationId = $this->generateConversationId();
        }

        $redisKey = "chat:conversation:{$conversationId}";
        $history = $this->loadConversation($redisKey, $clientHistory);

        if (empty($history)) {
            $salon = \app\models\Salon::find()->where(['is_active' => 1])->limit(1)->one();
            $greeting = $salon ? $salon->getChatGreeting() : \app\models\Salon::DEFAULT_CHAT_GREETING;
            $history[] = ['role' => 'assistant', 'content' => $greeting];
        }

        $systemPrompt = $this->getSystemPrompt();

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $message]]
        );

        /** @var LlmClient $llm */
        $llm = Yii::$app->llm;

        /** @var AgentToolExecutor $executor */
        $executor = new AgentToolExecutor();

        $toolDefinitions = $executor->getToolDefinitions();
        $toolCallsLog = [];

        $iteration = 0;
        $assistantReply = '';

        while ($iteration < $this->maxIterations) {
            $iteration++;

            try {
                $response = $llm->chatCompletion($messages, $toolDefinitions);
            } catch (\RuntimeException $e) {
                Yii::error('LLM API call failed: ' . $e->getMessage());
                $assistantReply = 'Извините, я временно недоступен. Пожалуйста, попробуйте позже или свяжитесь с администратором салона.';
                break;
            }

            $choice = isset($response['choices'][0]) ? $response['choices'][0] : null;
            if (!$choice) {
                Yii::error('LLM returned no choices: ' . json_encode($response));
                $assistantReply = 'Что-то пошло не так. Пожалуйста, попробуйте ещё раз.';
                break;
            }

            $responseMessage = $choice['message'];
            $finishReason = isset($choice['finish_reason']) ? $choice['finish_reason'] : 'stop';

            $messages[] = $responseMessage;

            if ($finishReason === 'tool_calls' && !empty($responseMessage['tool_calls'])) {
                foreach ($responseMessage['tool_calls'] as $toolCall) {
                    $fnName = $toolCall['function']['name'];
                    $fnArgs = json_decode($toolCall['function']['arguments'], true);
                    if ($fnArgs === null) {
                        $fnArgs = [];
                    }

                    Yii::info("Agent tool call: {$fnName}(" . json_encode($fnArgs) . ')');

                    $toolResult = $executor->execute($fnName, $fnArgs);

                    $toolCallsLog[] = [
                        'tool' => $fnName,
                        'arguments' => $fnArgs,
                    ];

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => $toolResult,
                    ];
                }

                continue;
            }

            $assistantReply = isset($responseMessage['content']) ? $responseMessage['content'] : '';
            break;
        }

        if (empty($assistantReply) && $iteration >= $this->maxIterations) {
            $assistantReply = 'Не удалось обработать ваш запрос. Попробуйте переформулировать или свяжитесь с салоном напрямую.';
        }

        $updatedHistory = $history;
        $updatedHistory[] = ['role' => 'user', 'content' => $message];
        $updatedHistory[] = ['role' => 'assistant', 'content' => $assistantReply];

        $this->saveConversation($redisKey, $updatedHistory);

        return [
            'reply' => $assistantReply,
            'conversation_id' => $conversationId,
            'tool_calls_made' => $toolCallsLog,
        ];
    }

    /**
     * @return string
     */
    private function getSystemPrompt()
    {
        $file = Yii::getAlias('@app/config/agent-system-prompt.md');
        $template = file_get_contents($file);

        $salon = \app\models\Salon::find()->where(['is_active' => 1])->limit(1)->one();

        $vars = [
            '{{salonName}}' => $salon ? $salon->name : Yii::$app->params['salonName'],
            '{{salonPhone}}' => $salon && $salon->phone ? $salon->phone : Yii::$app->params['salonPhone'],
            '{{salonAddress}}' => $salon && $salon->address ? $salon->address : '',
            '{{today}}' => date('Y-m-d'),
        ];

        return strtr($template, $vars);
    }

    /**
     * @return string
     */
    private function generateConversationId()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param string $redisKey
     * @param array $clientHistory
     * @return array
     */
    private function loadConversation($redisKey, array $clientHistory)
    {
        $redis = Yii::$app->redis;
        $stored = $redis->get($redisKey);

        if ($stored) {
            $decoded = json_decode($stored, true);
            if (is_array($decoded)) {
                return array_slice($decoded, -20);
            }
        }

        if (!empty($clientHistory) && is_array($clientHistory)) {
            $filtered = [];
            foreach ($clientHistory as $msg) {
                if (isset($msg['role'], $msg['content'])
                    && in_array($msg['role'], ['user', 'assistant'], true)
                ) {
                    $filtered[] = [
                        'role' => $msg['role'],
                        'content' => (string) $msg['content'],
                    ];
                }
            }
            return array_slice($filtered, -20);
        }

        return [];
    }

    /**
     * @param string $redisKey
     * @param array $history
     */
    private function saveConversation($redisKey, array $history)
    {
        $redis = Yii::$app->redis;
        $trimmed = array_slice($history, -30);
        $redis->set($redisKey, json_encode($trimmed, JSON_UNESCAPED_UNICODE));
        $redis->expire($redisKey, 3600);
    }
}

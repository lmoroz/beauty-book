<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class LlmClient extends Component
{
    /** @var string */
    public $baseUrl = 'https://routerai.ru/api/v1';

    /** @var string */
    public $apiKey = '';

    /** @var string */
    public $model = 'z-ai/glm-5';

    /** @var float */
    public $temperature = 0.7;

    /** @var int */
    public $maxTokens = 1024;

    /** @var int */
    public $timeout = 30;

    /** @var bool */
    public $enableLogging = true;

    /** @var int */
    public $logRetentionDays = 7;

    /** @var string|null resolved log directory path */
    private $_logDir;

    public function init()
    {
        parent::init();

        if (empty($this->apiKey)) {
            throw new InvalidConfigException('LlmClient::apiKey must be set.');
        }
    }

    /**
     * Send a chat completion request with optional tool definitions.
     *
     * @param array $messages     Chat messages [['role' => '...', 'content' => '...'], ...]
     * @param array $tools        Tool definitions in OpenAI format (optional)
     * @param string|null $toolChoice  'auto', 'none', or specific tool name
     * @return array Raw API response decoded from JSON
     * @throws \RuntimeException on HTTP or cURL error
     */
    public function chatCompletion(array $messages, array $tools = [], $toolChoice = null)
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $toolChoice ?: 'auto';
        }

        return $this->request('POST', '/chat/completions', $payload);
    }

    /**
     * Low-level HTTP request via cURL.
     *
     * @param string $method
     * @param string $path
     * @param array $body
     * @return array
     * @throws \RuntimeException
     */
    private function request($method, $path, array $body = [])
    {
        $url = rtrim($this->baseUrl, '/') . $path;
        $json = json_encode($body, JSON_UNESCAPED_UNICODE);

        $startTime = microtime(true);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        $elapsed = round(microtime(true) - $startTime, 3);

        $data = json_decode($response, true);

        $this->writeLog($method, $url, $body, $httpCode, $elapsed, $curlTime, $data, $response, $errno, $error);

        if ($errno) {
            throw new \RuntimeException("LLM API cURL error ({$errno}): {$error}");
        }

        if ($httpCode >= 400) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : $response;
            Yii::error("LLM API error ({$httpCode}): {$msg}");
            throw new \RuntimeException("LLM API error ({$httpCode}): {$msg}");
        }

        if ($data === null) {
            throw new \RuntimeException('LLM API returned invalid JSON: ' . substr($response, 0, 500));
        }

        return $data;
    }

    // ─── Logging ────────────────────────────────────────────────

    /**
     * @param string $method
     * @param string $url
     * @param array $requestPayload
     * @param int $httpCode
     * @param float $elapsed
     * @param float $curlTime
     * @param array|null $responseData
     * @param string $rawResponse
     * @param int $errno
     * @param string $error
     */
    private function writeLog(
        $method,
        $url,
        array $requestPayload,
        $httpCode,
        $elapsed,
        $curlTime,
        $responseData,
        $rawResponse,
        $errno,
        $error
    ) {
        if (!$this->enableLogging) {
            return;
        }

        try {
            $dir = $this->getLogDir();

            $entry = [
                'timestamp' => date('Y-m-d\TH:i:s.') . substr(microtime(), 2, 3) . date('P'),
                'method' => $method,
                'url' => $url,
                'elapsed_sec' => $elapsed,
                'curl_time_sec' => round($curlTime, 3),
                'http_code' => $httpCode,
                'request' => $requestPayload,
                'response' => $responseData,
            ];

            if ($responseData === null) {
                $entry['response_raw'] = substr($rawResponse, 0, 5000);
            }

            if (isset($responseData['usage'])) {
                $entry['tokens'] = $responseData['usage'];
            }

            if ($errno) {
                $entry['curl_error'] = "({$errno}) {$error}";
            }

            $filename = date('Y-m-d-His-') . substr(microtime(), 2, 6) . '.json';
            $filepath = $dir . '/' . $filename;

            $content = json_encode(
                $entry,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            );

            file_put_contents($filepath, $content, LOCK_EX);

            $this->rotateOldLogs($dir);
        } catch (\Throwable $e) {
            Yii::warning('LLM log write failed: ' . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function getLogDir()
    {
        if ($this->_logDir === null) {
            $this->_logDir = Yii::getAlias('@app/logs/llm');
            if (!is_dir($this->_logDir)) {
                mkdir($this->_logDir, 0777, true);
            }
        }
        return $this->_logDir;
    }

    /**
     * @param string $dir
     */
    private function rotateOldLogs($dir)
    {
        static $lastCheck = 0;
        $now = time();

        if ($now - $lastCheck < 3600) {
            return;
        }
        $lastCheck = $now;

        $cutoff = $now - ($this->logRetentionDays * 86400);

        foreach (glob($dir . '/*.json') as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}

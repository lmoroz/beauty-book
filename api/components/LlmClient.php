<?php

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class LlmClient extends Component
{
    public string $baseUrl = 'https://routerai.ru/api/v1';

    public string $apiKey = '';

    public string $model = 'z-ai/glm-5';

    public float $temperature = 0.7;

    public int $maxTokens = 1024;

    public int $timeout = 30;

    public bool $enableLogging = true;

    public int $logRetentionDays = 7;

    private ?string $_logDir = null;

    public function init()
    {
        parent::init();

        try {
            $salon = \app\models\Salon::find()->where(['is_active' => 1])->limit(1)->one();
            if ($salon) {
                $data = $salon->getSettingsArray();
                if (!empty($data['llm_base_url'])) {
                    $this->baseUrl = $data['llm_base_url'];
                }
                if (!empty($data['llm_api_key'])) {
                    $this->apiKey = $data['llm_api_key'];
                }
                if (!empty($data['llm_model'])) {
                    $this->model = $data['llm_model'];
                }
                if (isset($data['llm_temperature']) && $data['llm_temperature'] !== '') {
                    $this->temperature = (float) $data['llm_temperature'];
                }
                if (isset($data['llm_max_tokens']) && $data['llm_max_tokens'] !== '') {
                    $this->maxTokens = (int) $data['llm_max_tokens'];
                }
                if (isset($data['llm_timeout']) && $data['llm_timeout'] !== '') {
                    $this->timeout = (int) $data['llm_timeout'];
                }
            }
        } catch (\Throwable $e) {
            Yii::warning('Failed to load LLM settings from salon: ' . $e->getMessage());
        }

        if (empty($this->apiKey)) {
            throw new InvalidConfigException('LlmClient::apiKey must be set.');
        }
    }

    public function chatCompletion(array $messages, array $tools = [], ?string $toolChoice = null): array
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

    private function request(string $method, string $path, array $body = []): array
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

    private function writeLog(
        string $method,
        string $url,
        array $requestPayload,
        int $httpCode,
        float $elapsed,
        float $curlTime,
        ?array $responseData,
        string $rawResponse,
        int $errno,
        string $error
    ): void {
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

    private function getLogDir(): string
    {
        if ($this->_logDir === null) {
            $this->_logDir = Yii::getAlias('@app/logs/llm');
            if (!is_dir($this->_logDir)) {
                mkdir($this->_logDir, 0777, true);
            }
        }
        return $this->_logDir;
    }

    private function rotateOldLogs(string $dir): void
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

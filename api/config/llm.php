<?php

return [
    'class' => 'app\components\LlmClient',
    'baseUrl' => 'https://routerai.ru/api/v1',
    'apiKey' => '', // set in llm-local.php
    'model' => 'z-ai/glm-5',
    'temperature' => 0.7,
    'maxTokens' => 1024,
    'timeout' => 30,
];

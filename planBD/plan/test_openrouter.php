<?php
require 'c:\xampp\htdocs\planBD\planBD\planBD\plan\config.php';
$apiKey = config::getOpenRouterApiKey();
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
$payload = json_encode([
    'model' => 'google/gemini-2.5-flash',
    'messages' => [['role' => 'user', 'content' => 'hello']],
    'response_format' => ['type' => 'json_object'],
    'max_tokens' => 1500
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
echo curl_exec($ch);

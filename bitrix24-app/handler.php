<?php

declare(strict_types=1);

const WELCOME_MESSAGE = "Hi back in the open channel";
const BOT_ID = "welcome_bot";
const BOT_NAME = "Welcome Bot";

$payload = json_decode((string) file_get_contents("php://input"), true);

if (!is_array($payload)) {
    $payload = $_REQUEST;
}

$auth = $payload["auth"] ?? [];
$domain = $auth["domain"] ?? null;
$accessToken = $auth["access_token"] ?? null;
$connectorId = $payload["data"]["CONNECTOR"] ?? $payload["connector"] ?? "openlines";
$lineId = $payload["data"]["LINE"] ?? $payload["line"] ?? null;
$event = $payload["event"] ?? $payload["EVENT"] ?? null;

if (!$domain || !$accessToken || !$lineId) {
    http_response_code(400);
    echo json_encode([
        "result" => "error",
        "message" => "Missing auth or line identifiers.",
    ]);
    exit;
}

if ($event && !in_array($event, ["ONIMCONNECTORLINECHAT", "ONIMCONNECTORMESSAGEADD"], true)) {
    echo json_encode([
        "result" => "skipped",
        "message" => "Event ignored.",
    ]);
    exit;
}

$messagePayload = [
    "id" => uniqid("msg_", true),
    "user" => [
        "id" => BOT_ID,
        "name" => BOT_NAME,
    ],
    "message" => [
        "text" => WELCOME_MESSAGE,
    ],
];

$restPayload = [
    "CONNECTOR" => $connectorId,
    "LINE" => $lineId,
    "MESSAGES" => [$messagePayload],
];

$restUrl = sprintf(
    "https://%s/rest/imconnector.send.messages.json",
    $domain,
);

$options = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/json",
        "content" => json_encode($restPayload + ["auth" => $accessToken], JSON_UNESCAPED_UNICODE),
        "timeout" => 5,
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($restUrl, false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        "result" => "error",
        "message" => "Failed to send welcome message.",
    ]);
    exit;
}

header("Content-Type: application/json");

echo json_encode([
    "result" => "ok",
    "sent" => WELCOME_MESSAGE,
]);

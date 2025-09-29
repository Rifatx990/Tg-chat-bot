<?php
// Data folder
$dataFolder = __DIR__ . "/data";
if (!is_dir($dataFolder)) mkdir($dataFolder, 0777, true);

// Telegram bot credentials
$telegram_bot_token = "8330196047:AAEM8FK_jcChRzddBy--DWI8kK6ae7a800w";

// Admins (Telegram IDs)
$admins = [
    "6631601772",
    ""
];

// Chat file
$chatFile = $dataFolder . "/chat.json";

// Chat settings file
$chatSettingsFile = $dataFolder . "/chat_settings.json";
if (!file_exists($chatSettingsFile)) {
    file_put_contents($chatSettingsFile, json_encode([
        'auto_delete_days' => 1
    ], JSON_PRETTY_PRINT));
}

// Load chat
function loadChat() {
    global $chatFile;
    return file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];
}

// Save chat
function saveChat($data) {
    global $chatFile;
    file_put_contents($chatFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Load settings
function loadSettings() {
    global $chatSettingsFile;
    return file_exists($chatSettingsFile) ? json_decode(file_get_contents($chatSettingsFile), true) : [];
}

// Save settings
function saveSettings($settings) {
    global $chatSettingsFile;
    file_put_contents($chatSettingsFile, json_encode($settings, JSON_PRETTY_PRINT));
}

// Send Telegram message
function sendTelegramMessage($botToken, $chatID, $message) {
    if (!$botToken || !$chatID) return;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = http_build_query([
        'chat_id' => $chatID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]);
    @file_get_contents("$url?$data");
}

// Auto-delete old chats
function cleanOldChats() {
    $chat = loadChat();
    $settings = loadSettings();
    $max_age = ($settings['auto_delete_days'] ?? 1) * 86400;
    $now = time();
    foreach ($chat as $chat_id => &$messages) {
        $messages = array_filter($messages, fn($msg) => ($now - $msg['timestamp']) <= $max_age);
    }
    saveChat($chat);
}

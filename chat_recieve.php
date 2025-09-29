<?php
include "config.php";
header('Content-Type: application/json');

$chat_id = $_REQUEST['chat_id'] ?? '';
$user_name = $_REQUEST['user_name'] ?? 'Unknown';
$message = $_REQUEST['message'] ?? '';

if (!$chat_id || !$message) {
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

// Save chat
$chat = loadChat();
if(!isset($chat[$chat_id])) $chat[$chat_id] = [];
$chat[$chat_id][] = [
    'from' => 'user',
    'name' => $user_name,
    'message' => $message,
    'timestamp' => time()
];
saveChat($chat);

// Optionally, forward to admin(s)
foreach($admins as $admin_id) {
    sendTelegramMessage($telegram_bot_token, $admin_id, "<b>$user_name:</b> $message");
}

echo json_encode(['status'=>'success']);

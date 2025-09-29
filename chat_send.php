<?php
include "config.php";

$admin_id = $_GET['admin_id'] ?? '';
$chat_id  = $_GET['chat_id'] ?? '';
$message  = $_GET['message'] ?? '';

if(!$admin_id || !$chat_id || !$message){
    exit(json_encode(['status'=>'error','msg'=>'Missing parameters']));
}

// Send message via Telegram bot
sendTelegramMessage($telegram_bot_token, $chat_id, $message);

// Save message in local chat file
$chatsFile = __DIR__ . "/data/chats.json";
$chats = file_exists($chatsFile) ? json_decode(file_get_contents($chatsFile), true) : [];

if(!isset($chats[$chat_id])) $chats[$chat_id] = ['name'=>'','profile_pic'=>'','messages'=>[]];

$chats[$chat_id]['messages'][] = [
    'from'=>'admin',
    'name'=>'Admin',
    'message'=>$message,
    'profile_pic'=>'', // optional admin pic
    'timestamp'=>time()
];

file_put_contents($chatsFile,json_encode($chats,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

echo json_encode(['status'=>'success']);

<?php
include "config.php";
header('Content-Type: application/json');

$admin_id = $_GET['admin_id'] ?? '';
if(!$admin_id) exit(json_encode(['status'=>'error','msg'=>'Admin ID required']));

$chatsFile = __DIR__ . "/data/chats.json";
$settingsFile = __DIR__ . "/data/chat_settings.json";

$chats = file_exists($chatsFile) ? json_decode(file_get_contents($chatsFile), true) : [];
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];
$days = $settings['auto_delete_days'] ?? 1;
$cutoff = time() - ($days * 86400);

// Remove old messages
foreach($chats as $chat_id=>$chat){
    $chat['messages'] = array_filter($chat['messages'], fn($m)=>$m['timestamp'] >= $cutoff);
    $chats[$chat_id] = $chat;
}

// Save cleaned chats
file_put_contents($chatsFile,json_encode($chats,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

echo json_encode(['status'=>'success','msg'=>"Chats older than $days day(s) deleted"]);

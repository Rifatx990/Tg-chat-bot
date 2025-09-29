<?php
include "config.php";
header('Content-Type: application/json');

// Admin check
$admin_id = $_GET['admin_id'] ?? '';
if(!in_array($admin_id, $admins)){
    echo json_encode([]);
    exit;
}

// Load chats safely
$chats = loadJson($chatsFile);

// Auto-delete old messages based on settings
$settings = loadJson($chatSettingsFile); // e.g., ['auto_delete_days'=>1]
$autoDeleteDays = $settings['auto_delete_days'] ?? 1;
$expiryTime = time() - ($autoDeleteDays * 86400);

foreach($chats as $chat_id => &$chat){
    if(!isset($chat['messages'])) continue;
    $chat['messages'] = array_filter($chat['messages'], function($msg) use ($expiryTime){
        return ($msg['timestamp'] ?? 0) >= $expiryTime;
    });
    // Reset array keys
    $chat['messages'] = array_values($chat['messages']);
}

// Return JSON
echo json_encode($chats);

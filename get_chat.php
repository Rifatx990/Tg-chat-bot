<?php
include "config.php";

$admin_id = $_GET['admin_id'] ?? '';
if(!in_array($admin_id, $admins)) exit(json_encode([]));

$chats = loadChat();

// Auto-clean old messages
$max_age = ($settings['auto_delete_days'] ?? 1) * 86400;
$now = time();
foreach($chats as $id => &$chat){
    if(isset($chat['messages'])){
        $chat['messages'] = array_filter($chat['messages'], fn($msg)=>($now-$msg['timestamp']) <= $max_age);
    }
}

echo json_encode($chats);

<?php
include "config.php";

$admin_id = $_GET['admin_id'] ?? '';
$chat_id = $_GET['chat_id'] ?? '';

if(!in_array($admin_id, $admins)){ echo json_encode([]); exit; }

$chats = loadChat();
if($chat_id && isset($chats[$chat_id])){
    $chat = $chats[$chat_id]['messages'] ?? [];
    echo json_encode($chat);
}else{
    echo json_encode([]);
}

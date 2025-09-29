<?php
include "config.php";

$admin_id = $_GET['admin_id'] ?? '';
if (!in_array($admin_id, $admins)) { echo json_encode([]); exit; }

$chats = loadChat();
$users = [];
foreach($chats as $chat_id => $chatData){
    $users[] = [
        'chat_id' => $chat_id,
        'name' => $chatData['name'] ?? $chat_id
    ];
}
header('Content-Type: application/json');
echo json_encode($users);

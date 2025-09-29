<?php
include "config.php";

$admin_id = $_POST['admin_id'] ?? '';
$chat_id  = $_POST['chat_id'] ?? '';
$message  = $_POST['message'] ?? '';
$file     = $_FILES['file'] ?? null;

if(!in_array($admin_id, $admins) || !$chat_id) exit('Access denied');

// Load current chats
$chats = loadChat();
if(!isset($chats[$chat_id])) $chats[$chat_id] = ['name'=>$chat_id, 'messages'=>[]];

// Handle file upload
$file_url = '';
if($file && $file['error'] == 0){
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "uploads/".time()."_".rand(1000,9999).".".$ext;
    if(!is_dir('uploads')) mkdir('uploads', 0777, true);
    move_uploaded_file($file['tmp_name'], $filename);
    $file_url = $filename;
}

// Add message
$chats[$chat_id]['messages'][] = [
    'from'=>'admin',
    'message'=>$message,
    'file_url'=>$file_url,
    'timestamp'=>time()
];

// Save chats
saveChat($chats);

// Optionally, send message via Telegram
$text = $message ?: ($file_url ? "Sent a file: $file_url" : '');
if($text) sendTelegramMessage($telegram_bot_token, $chat_id, $text);

echo json_encode(['status'=>'success']);

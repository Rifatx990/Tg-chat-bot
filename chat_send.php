<?php
include "config.php";

$admin_id = $_POST['admin_id'] ?? '';
$chat_id  = $_POST['chat_id'] ?? '';
$message  = $_POST['message'] ?? '';

if(!in_array($admin_id, $admins)) exit('Access denied');

if(!$chat_id || (!$message && empty($_FILES['file']))) exit('Nothing to send');

$chats = loadChat();
if(!isset($chats[$chat_id])) $chats[$chat_id] = ['name'=>$chat_id,'messages'=>[]];

// Handle file upload
$file_url = null;
if(!empty($_FILES['file']['tmp_name'])){
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . rand(1000,9999) . '.' . $ext;
    $dest = __DIR__ . "/data/".$filename;
    move_uploaded_file($_FILES['file']['tmp_name'], $dest);
    $file_url = "data/$filename";
}

// Add admin message
$chats[$chat_id]['messages'][] = [
    'from'=>'admin',
    'message'=>$message,
    'file_url'=>$file_url,
    'timestamp'=>time()
];

saveChat($chats);

// Send Telegram notification to user (optional)
sendTelegramMessage($telegram_bot_token, $chat_id, $message);

echo "ok";

<?php
include "config.php";

$admin_id = $_POST['admin_id'] ?? '';
$chat_id = $_POST['chat_id'] ?? '';
$message = $_POST['message'] ?? '';
$file = $_FILES['file'] ?? null;

if(!in_array($admin_id, $admins)){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

// Load chats
$chats = loadJson($chatsFile);

// Ensure chat exists
if(!isset($chats[$chat_id])) $chats[$chat_id] = ['name'=>$chat_id,'messages'=>[]];

// Handle file upload
$file_url = null;
if($file && $file['error']==0){
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $chat_id.'_'.time().'.'.$ext;
    $dest = $dataFolder.'/'.$filename;
    if(move_uploaded_file($file['tmp_name'], $dest)){
        $file_url = 'data/'.$filename;
    }
}

// Save message
$chats[$chat_id]['messages'][] = [
    'from'=>'admin',
    'message'=>$message,
    'file_url'=>$file_url,
    'time'=>date('Y-m-d H:i:s')
];

saveJson($chatsFile, $chats);

// Send message to Telegram
$text = $message ?: 'Sent a file';
if($file_url){
    // Telegram supports sending documents via Bot API
    $url = "https://api.telegram.org/bot$telegram_bot_token/sendDocument";
    $post_fields = [
        'chat_id'=>$chat_id,
        'document'=>new CURLFile($dest),
        'caption'=>$message
    ];
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_fields);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_exec($ch);
    curl_close($ch);
}else{
    sendTelegramMessage($telegram_bot_token, $chat_id, $text);
}

echo json_encode(['status'=>'success']);

<?php
include "config.php";
header('Content-Type: application/json');

$chatsFile = __DIR__ . "/data/chats.json";

// Load chats safely
$chats = file_exists($chatsFile) ? json_decode(file_get_contents($chatsFile), true) : [];

// Return chats
echo json_encode($chats);

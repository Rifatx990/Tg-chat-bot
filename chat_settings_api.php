<?php
include "config.php";
header('Content-Type: application/json');

$admin_id = $_REQUEST['admin_id'] ?? '';
if (!$admin_id || !in_array($admin_id, $admins)) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$settings = loadSettings();

if(isset($_REQUEST['auto_delete_days'])) {
    $settings['auto_delete_days'] = max(1,intval($_REQUEST['auto_delete_days']));
    saveSettings($settings);
}

echo json_encode($settings);

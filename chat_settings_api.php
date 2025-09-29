<?php
include "config.php";

$admin_id = $_GET['admin_id'] ?? '';
if(!in_array($admin_id, $admins)) exit(json_encode(['error'=>'Access denied']));

$settings = loadSettings();

// Update if parameter provided
if(isset($_GET['auto_delete_days'])){
    $days = max(1,intval($_GET['auto_delete_days']));
    $settings['auto_delete_days'] = $days;
    saveSettings($settings);
}

echo json_encode($settings);

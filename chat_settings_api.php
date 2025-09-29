<?php
include "config.php";
$admin_id = $_GET['admin_id'] ?? '';
if(!in_array($admin_id,$admins)) exit;

$settings = loadSettings();
if(isset($_GET['auto_delete_days'])){
    $settings['auto_delete_days'] = (int)$_GET['auto_delete_days'];
    saveSettings($settings);
}
header('Content-Type: application/json');
echo json_encode($settings);

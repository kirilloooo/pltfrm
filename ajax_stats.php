<?php
// Include necessary files and settings
include_once __DIR__ . '/../includes/settings.php';
include_once __DIR__ . '/../includes/functions.php';

// Fetch updated statistics data
$stats = getStats();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($stats);
?>

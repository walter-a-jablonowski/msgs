<?php
// Set unlimited execution time for long-running connection
set_time_limit(0);

require_once '../msgs_lib/MessageManager.php';
require_once '../msgs_lib/SseServer.php';

$sessionId = $_GET['sessionId'] ?? '';

// Get target from query parameter (optional)
$target = isset($_GET['target']) ? $_GET['target'] : null;

// Create and start SSE server with a 5-minute timeout
// After 5 minutes, it will gracefully close and the client will reconnect
$sseServer = new SseServer('../data/messages', $sessionId, $target, 100, 300);
$sseServer->start();

// The event loop is now handled by the SseServer class

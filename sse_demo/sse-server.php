<?php

require_once '../msgs_lib/MessageManager.php';
require_once '../msgs_lib/SseServer.php';

$sessionId = $_GET['sessionId'] ?? '';

// Get target from query parameter (optional)
$target = isset($_GET['target']) ? $_GET['target'] : null;

// Create and start SSE server
$sseServer = new SseServer('../data/messages', $sessionId, $target);
$sseServer->start();

// The event loop is now handled by the SseServer class

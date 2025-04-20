<?php

require_once '../msgs_lib/MessageManager.php';
require_once '../msgs_lib/SseServer.php';

$messagesDir = '../data/messages';
$sessionId   = $_GET['sessionId'] ?? '';

// Get target from query parameter (optional)
$target = isset($_GET['target']) ? $_GET['target'] : null;

// Create and start SSE server
$sseServer = new SseServer($messagesDir, $sessionId, $target);
$sseServer->start();

// The event loop is now handled by the SseServer class

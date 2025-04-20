<?php
require_once '../msgs_lib/MessageManager.php';

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if session ID is provided
if( empty($data['sessionId']) )
{
  echo json_encode(['success' => false, 'error' => 'Session ID is required']);
  exit;
}

// Initialize message manager
$messagesDir = '../data/messages';
$messageManager = new MessageManager($messagesDir, $data['sessionId']);

// Send initial response to client
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Process started']);

// Close the connection to the client
if( function_exists('fastcgi_finish_request') )
{
  fastcgi_finish_request();
}
else
{
  // Alternative method for non-FastCGI servers
  ob_end_flush();
  flush();
}

// Get target from data (optional)
$target = $data['target'] ?? 'default';

// Simulate a process with shorter sleep times
$messageManager->addMessage(['message' => "Starting process...", 'type' => "info"], $target);
usleep(200000); // 200ms

$messageManager->addMessage(['message' => "Initializing...", 'type' => "info"], $target);
usleep(300000); // 300ms

$messageManager->addMessage(['message' => "Processing step 1/5", 'type' => "info"], $target);
usleep(200000); // 200ms

$messageManager->addMessage(['message' => "Processing step 2/5", 'type' => "info"], $target);
usleep(300000); // 300ms

$messageManager->addMessage(['message' => "Processing step 3/5", 'type' => "warning"], $target);
usleep(200000); // 200ms

$messageManager->addMessage(['message' => "Processing step 4/5", 'type' => "info"], $target);
usleep(300000); // 300ms

$messageManager->addMessage(['message' => "Processing step 5/5", 'type' => "info"], $target);
usleep(200000); // 200ms

$messageManager->addMessage(['message' => "Process completed successfully!", 'type' => "success"], $target);

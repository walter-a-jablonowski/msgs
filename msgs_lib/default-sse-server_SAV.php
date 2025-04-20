<?php

require_once '../msgs_lib/MessageManager.php';

$messagesDir = '../data/messages';


// Disable output buffering
if( ob_get_level() )  ob_end_clean();

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');  // disable buffering for Nginx

// Get session ID from query parameter
$sessionId = $_GET['sessionId'] ?? '';
if( empty($sessionId) )
{
  echo "event: error\n";
  echo "data: Session ID is required\n\n";
  exit;
}

// Get target from query parameter (optional)
$target = isset($_GET['target']) ? $_GET['target'] : null;

// Initialize message manager
$messageManager = new MessageManager($messagesDir, $sessionId);

// Send initial message
echo "event: connected\n";
echo "data: " . json_encode(['status' => 'connected', 'sessionId' => $sessionId]) . "\n\n";
flush();

// Keep connection open and check for new messages
$lastCheck = 0;
$lastMessageCount = 0;

while( true )
{
  // Get all messages
  $messages = $messageManager->getMessages($target);
  $currentMessageCount = count($messages);
  
  // If there are new messages, send them
  if( $currentMessageCount > $lastMessageCount )
  {
    // Send only new messages
    $newMessages = array_slice($messages, $lastMessageCount);
    
    foreach( $newMessages as $message )
    {
      echo "event: message\n";
      echo "data: " . json_encode($message) . "\n\n";
      flush();
    }
    
    $lastMessageCount = $currentMessageCount;
  }
  
  // Check if client is still connected
  if( connection_aborted() )
    break;
  
  // Sleep to reduce CPU usage (reduced from 500ms to 100ms for faster updates)
  usleep(100000); // 100ms
}

<?php

require_once '../msgs_lib/MessageManager.php';

$messagesDir = '../data/messages';


// Handle CORS if needed
header('Content-Type: application/json');

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if( ! $data )
{
  echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
  exit;
}

// Check if session ID is provided
if( empty($data['sessionId']) )
{
  echo json_encode(['success' => false, 'error' => 'Session ID is required']);
  exit;
}

// Initialize message manager
$messageManager = new MessageManager($messagesDir, $data['sessionId']);
$action = $data['action'] ?? 'addMessage';

switch( $action )
{
  case 'addMessage':

    $message = $data['message'] ?? '';
    $type    = $data['type'] ?? 'info';
    $target  = $data['target'] ?? 'default';
    
    $success = $messageManager->addMessage($message, $type, $target);
    echo json_encode(['success' => $success]);
    break;
    
  case 'getMessages':

    $target = isset($data['target']) ? $data['target'] : null;
    $messages = $messageManager->getMessages($target);
    echo json_encode(['success' => true, 'messages' => $messages]);
    break;
    
  case 'clearMessages':
  
    $target = isset($data['target']) ? $data['target'] : null;
    $success = $messageManager->clearMessages($target);
    echo json_encode(['success' => $success]);
    break;
    
  default:
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    break;
}

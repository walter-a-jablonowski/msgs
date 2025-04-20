<?php
/**
 * Server-Sent Events (SSE) Server Class
 * 
 * This class provides a reusable implementation of a Server-Sent Events (SSE) server
 * that can be used to push real-time updates to clients.
 */
class SSEServer
{
  protected $messagesDir;
  protected $sessionId;
  protected $target;
  protected $messageManager;
  protected $pollingInterval;
  
  /**
   * Constructor
   * 
   * @param string $messagesDir Directory to store messages
   * @param string $sessionId Unique session identifier
   * @param string|null $target Optional target filter
   * @param int $pollingInterval Milliseconds between checks for new messages
   */
  public function __construct($messagesDir, $sessionId, $target = null, $pollingInterval = 100)
  {
    $this->messagesDir = $messagesDir;
    $this->sessionId = $sessionId;
    $this->target = $target;
    $this->pollingInterval = $pollingInterval;
    
    // Initialize message manager
    $this->messageManager = new MessageManager($messagesDir, $sessionId);
  }
  
  /**
   * Initialize the SSE connection
   * 
   * @return bool Success
   */
  public function initialize() : bool
  {
    // Validate session ID
    if( empty( $this->sessionId)) {
      $this->sendError('Session ID is required');
      return false;
    }
    
    // Disable output buffering
    if( ob_get_level() )  ob_end_clean();
    
    // Set headers for SSE
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');  // disable buffering for Nginx
    
    // Send initial connection message
    $this->sendEvent('connected', [
      'status'    => 'connected',
      'sessionId' => $this->sessionId
    ]);
    
    return true;
  }
  
  /**
   * Start the SSE event loop
   */
  public function start() : void
  {
    // Initialize connection
    if( ! $this->initialize())
      return;
    
    // Keep connection open and check for new messages
    $lastMessageCount = 0;
    
    while( true )
    {
      // Get all messages
      $messages = $this->messageManager->getMessages($this->target);
      $currentMessageCount = count($messages);
      
      // If there are new messages, send them
      if( $currentMessageCount > $lastMessageCount) {
        // Send only new messages
        $newMessages = array_slice($messages, $lastMessageCount);
        
        foreach( $newMessages as $message )
          $this->sendEvent('message', $message);
        
        $lastMessageCount = $currentMessageCount;
      }
      
      // Check if client is still connected
      if (connection_aborted()) {
        break;
      }
      
      // Sleep to reduce CPU usage
      usleep( $this->pollingInterval * 1000);  // convert ms to microseconds
    }
  }

  /**
   * Send an SSE event
   * 
   * @param string $eventName Event name
   * @param mixed $data Event data (will be JSON encoded)
   */
  public function sendEvent($eventName, $data) : void
  {
    echo "event: {$eventName}\n";
    echo "data: " . json_encode($data) . "\n\n";
    flush();
  }
  
  /**
   * Send an error event
   * 
   * @param string $message Error message
   */
  public function sendError($message) : void
  {
    $this->sendEvent('error', $message);
  }
}

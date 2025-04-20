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
  protected $maxExecutionTime;
  
  /**
   * Constructor
   * 
   * @param string $messagesDir Directory to store messages
   * @param string $sessionId Unique session identifier
   * @param string|null $target Optional target filter
   * @param int $pollingInterval Milliseconds between checks for new messages
   * @param int $maxExecutionTime Maximum execution time in seconds (0 = unlimited)
   */
  public function __construct($messagesDir, $sessionId, $target = null, $pollingInterval = 100, $maxExecutionTime = 300)
  {
    $this->messagesDir = $messagesDir;
    $this->sessionId = $sessionId;
    $this->target = $target;
    $this->pollingInterval = $pollingInterval;
    $this->maxExecutionTime = $maxExecutionTime;
    
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
    // Set unlimited execution time for long-running connection
    set_time_limit(0);
    
    // Disable output buffering and compression
    ini_set('output_buffering', 'off');
    ini_set('zlib.output_compression', false);
    ini_set('implicit_flush', true);
    ob_implicit_flush(true);
    
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
    
    // Send retry directive (reconnect after 1 second if connection is lost)
    echo "retry: 1000\n";
    flush();
    
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
    $startTime = time();
    $lastPingTime = 0;
    
    while( true )
    {
      // Check if we've exceeded the maximum execution time
      if ($this->maxExecutionTime > 0 && (time() - $startTime) > $this->maxExecutionTime) {
        // Send a graceful close message
        $this->sendEvent('info', [
          'message' => 'Connection timeout reached, reconnecting...',
          'type' => 'info'
        ]);
        break;
      }
      
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
      
      // Send a ping every 30 seconds to keep the connection alive
      $currentTime = time();
      if ($currentTime - $lastPingTime >= 30) {
        $this->sendEvent('ping', ['time' => $currentTime]);
        $lastPingTime = $currentTime;
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

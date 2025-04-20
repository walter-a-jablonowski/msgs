<?php
/**
 * Common message manager class for both SSE and AJAX implementations
 */
class MessageManager
{
  protected $messagesDir;
  protected $sessionId;
  
  /**
   * Constructor
   * 
   * @param string $messagesDir Directory to store messages
   * @param string $sessionId Unique session identifier
   */
  public function __construct( $messagesDir, $sessionId )
  {
    $this->messagesDir = $messagesDir;
    $this->sessionId   = $sessionId;
    
    // Create messages directory if it doesn't exist
    if( ! is_dir($messagesDir) )
    {
      mkdir($messagesDir, 0777, true);
    }
  }
  
  /**
   * Add a message to the session
   * 
   * @param string $message Message text
   * @param string $type Message type (info, warning, error, success)
   * @param string $target Target container ID (optional)
   * @return bool Success
   */
  public function addMessage( $message, $type = 'info', $target = 'default' ) : bool
  {
    $messageData = [
      'id' => uniqid(),
      'timestamp' => time(),
      'message' => $message,
      'type' => $type,
      'target' => $target
    ];
    
    $filename = $this->getMessageFilePath();
    
    $messages = [];
    if( file_exists($filename) )
      $messages = json_decode(file_get_contents($filename), true) ?: [];
    
    $messages[] = $messageData;
    
    return file_put_contents($filename, json_encode($messages)) !== false;
  }
  
  /**
   * Get all messages for the current session
   * 
   * @param string $target Filter by target (optional)
   * @return array Messages
   */
  public function getMessages( $target = null ) : array
  {
    $filename = $this->getMessageFilePath();
    
    if( ! file_exists($filename) )
      return [];
    
    $messages = json_decode(file_get_contents($filename), true) ?: [];
    
    if( $target !== null )
    {
      $messages = array_filter($messages, function($message) use ($target) {
        return $message['target'] === $target;
      });
    }
    
    return $messages;
  }
  
  /**
   * Clear all messages for the current session
   * 
   * @param string $target Filter by target (optional)
   * @return bool Success
   */
  public function clearMessages( $target = null ) : bool
  {
    $filename = $this->getMessageFilePath();
    
    if( ! file_exists($filename) )
      return true;
    
    if( $target === null )
      return unlink($filename);
    
    $messages = json_decode(file_get_contents($filename), true) ?: [];
    $messages = array_filter($messages, function($message) use ($target) {
      return $message['target'] !== $target;
    });
    
    return file_put_contents($filename, json_encode($messages)) !== false;
  }
  
  /**
   * Get the file path for the current session's messages
   * 
   * @return string File path
   */
  protected function getMessageFilePath() : string
  {
    return $this->messagesDir . '/' . $this->sessionId . '.json';
  }
}

/**
 * SSE-based message client
 */
class SseMessageClient
{
  constructor( options = {} )
  {
    this.options = Object.assign({
      serverUrl: 'sse-server.php',
      sessionId: this.generateSessionId(),
      onMessage: null,
      onConnect: null,
      onError: null,
      autoReconnect: true,
      reconnectInterval: 3000
    }, options);
    
    this.eventSource = null;
    this.connected = false;
    this.messageDisplay = null;
    
    // If messageDisplay is provided, use it
    if (options.messageDisplay) {
      this.messageDisplay = options.messageDisplay;
    }
  }
  
  /**
   * Generate a random session ID
   * 
   * @return {string} Session ID
   */
  generateSessionId() {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
  }
  
  /**
   * Connect to the SSE server
   * 
   * @param {string} target Optional target filter
   */
  connect(target = null)
  {
    if (this.eventSource)
      this.disconnect();
    
    let url = `${this.options.serverUrl}?sessionId=${this.options.sessionId}`;
    if (target)
      url += `&target=${encodeURIComponent(target)}`;
    
    this.eventSource = new EventSource(url);
    
    // Handle connection open
    this.eventSource.addEventListener('connected', (event) => {
      this.connected = true;
      const data = JSON.parse(event.data);
      
      if (typeof this.options.onConnect === 'function') {
        this.options.onConnect(data);
      }
    });
    
    // Handle messages
    this.eventSource.addEventListener('message', (event) => {
      const messageData = JSON.parse(event.data);
      
      // Display message if messageDisplay is available
      if (this.messageDisplay) {
        this.messageDisplay.displayMessage(messageData);
      }
      
      // Call onMessage callback if provided
      if (typeof this.options.onMessage === 'function') {
        this.options.onMessage(messageData);
      }
    });
    
    // Handle errors
    this.eventSource.addEventListener('error', (event) => {
      this.connected = false;
      
      if (typeof this.options.onError === 'function') {
        this.options.onError(event);
      }
      
      // Auto reconnect if enabled
      if (this.options.autoReconnect) {
        setTimeout(() => {
          this.connect(target);
        }, this.options.reconnectInterval);
      }
    });
  }
  
  /**
   * Disconnect from the SSE server
   */
  disconnect() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
      this.connected = false;
    }
  }
  
  /**
   * Send a message to the server via AJAX
   * 
   * @param {string} message Message text
   * @param {string} type Message type (info, warning, error, success)
   * @param {string} target Target container ID (optional)
   * @return {Promise} Promise that resolves when the message is sent
   */
  sendMessage(message, type = 'info', target = 'default') {
    return fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        sessionId: this.options.sessionId,
        message: message,
        type: type,
        target: target,
        action: 'addMessage'
      })
    })
    .then(response => response.json());
  }
  
  /**
   * Start a long-running process
   * 
   * @param {string} scriptUrl URL of the PHP script to run
   * @param {Object} params Additional parameters to send to the script
   * @param {string} target Target container ID (optional)
   * @return {Promise} Promise that resolves when the process starts
   */
  startProcess(scriptUrl, params = {}, target = 'default') {
    // Merge parameters with session ID
    const processParams = Object.assign({
      sessionId: this.options.sessionId,
      target: target
    }, params);
    
    // Start the process via AJAX
    return fetch(scriptUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(processParams)
    })
    .then(response => response.json());
  }
}

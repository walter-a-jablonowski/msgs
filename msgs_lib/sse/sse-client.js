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
    this.lastPingTime = 0;
    
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
    if (this.eventSource) {
      console.log('Closing existing EventSource connection before reconnecting');
      this.disconnect();
    }
    
    let url = `${this.options.serverUrl}?sessionId=${this.options.sessionId}`;
    if (target)
      url += `&target=${encodeURIComponent(target)}`;
    
    console.log('Connecting to SSE server:', url);
    
    try {
      this.eventSource = new EventSource(url);
      
      // Handle connection open
      this.eventSource.addEventListener('open', (event) => {
        console.log('EventSource connection opened');
      });
      
      this.eventSource.addEventListener('connected', (event) => {
        console.log('Received connected event from server');
        this.connected = true;
        this.lastPingTime = Date.now();
        
        try {
          const data = JSON.parse(event.data);
          
          if (typeof this.options.onConnect === 'function') {
            this.options.onConnect(data);
          }
        } catch (error) {
          console.error('Error parsing connected event data:', error, event.data);
        }
      });
      
      // Handle messages
      this.eventSource.addEventListener('message', (event) => {
        console.log('Received message event from server:', event.data);
        
        try {
          const messageData = JSON.parse(event.data);
          
          // Display message if messageDisplay is available
          if (this.messageDisplay) {
            this.messageDisplay.displayMessage(messageData);
          }
          
          // Call onMessage callback if provided
          if (typeof this.options.onMessage === 'function') {
            this.options.onMessage(messageData);
          }
        } catch (error) {
          console.error('Error parsing message event data:', error, event.data);
        }
      });
      
      // Handle ping events to keep connection alive
      this.eventSource.addEventListener('ping', (event) => {
        console.log('Received ping from server');
        this.lastPingTime = Date.now();
      });
      
      // Handle info events (like timeout notifications)
      this.eventSource.addEventListener('info', (event) => {
        console.log('Received info event from server:', event.data);
        try {
          const data = JSON.parse(event.data);
          if (data.message && this.messageDisplay) {
            this.messageDisplay.displayMessage({
              message: data.message,
              type: data.type || 'info',
              timestamp: Math.floor(Date.now() / 1000),
              id: 'info_' + Date.now(),
              system: true
            });
          }
        } catch (error) {
          console.error('Error parsing info event data:', error, event.data);
        }
      });
      
      // Handle errors
      this.eventSource.addEventListener('error', (event) => {
        console.error('EventSource error:', event);
        this.connected = false;
        
        if (typeof this.options.onError === 'function') {
          this.options.onError(event);
        }
        
        // Auto reconnect if enabled
        if (this.options.autoReconnect) {
          console.log('Attempting to reconnect in', this.options.reconnectInterval, 'ms');
          setTimeout(() => {
            this.connect(target);
          }, this.options.reconnectInterval);
        }
      });
    } catch (error) {
      console.error('Error creating EventSource:', error);
      
      if (typeof this.options.onError === 'function') {
        this.options.onError(error);
      }
    }
  }
  
  /**
   * Disconnect from the SSE server
   */
  disconnect() {
    if (this.eventSource) {
      console.log('Disconnecting from SSE server');
      this.eventSource.close();
      this.eventSource = null;
      this.connected = false;
    }
  }
  
  /**
   * Send a message to the server via AJAX
   * 
   * @param {Object} fields Message fields object containing at least 'message' and 'type'
   * @param {string} target Target container ID (optional)
   * @return {Promise} Promise that resolves when the message is sent
   */
  sendMessage(fields, target = 'default') {
    // Handle both object and string formats for backward compatibility
    let messageFields = {};
    
    if (typeof fields === 'string') {
      // Legacy format: first parameter is the message text, second is the type
      const message = fields;
      const type = arguments[1] || 'info';
      target = arguments[2] || 'default';
      
      messageFields = {
        message: message,
        type: type
      };
    } else if (typeof fields === 'object') {
      messageFields = fields;
      
      // Ensure message and type are set
      if (!messageFields.message) messageFields.message = '';
      if (!messageFields.type) messageFields.type = 'info';
    }
    
    console.log('Sending message:', messageFields, 'to target:', target);
    
    return fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        sessionId: this.options.sessionId,
        fields: messageFields,
        target: target,
        action: 'addMessage'
      })
    })
    .then(response => response.json())
    .catch(error => {
      console.error('Error sending message:', error);
      return { success: false, error: error.message };
    });
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
    
    console.log('Starting process:', scriptUrl, 'with params:', processParams);
    
    // Start the process via AJAX
    return fetch(scriptUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(processParams)
    })
    .then(response => response.json())
    .catch(error => {
      console.error('Error starting process:', error);
      return { success: false, error: error.message };
    });
  }
}

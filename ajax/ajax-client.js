/**
 * AJAX-based message client
 */
class AjaxMessageClient {
  constructor(options = {}) {
    this.options = Object.assign({
      processUrl: 'process.php',
      sessionId: this.generateSessionId(),
      pollInterval: 1000,
      onMessage: null,
      onError: null,
      autoPolling: true
    }, options);
    
    this.polling = false;
    this.pollTimer = null;
    this.messageDisplay = null;
    this.lastTimestamp = 0;
    
    // If messageDisplay is provided, use it
    if (options.messageDisplay) {
      this.messageDisplay = options.messageDisplay;
    }
    
    // Start polling if autoPolling is enabled
    if (this.options.autoPolling) {
      this.startPolling();
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
   * Start polling for new messages
   * 
   * @param {string} target Optional target filter
   */
  startPolling(target = null) {
    if (this.polling) {
      return;
    }
    
    this.polling = true;
    
    const poll = () => {
      this.getMessages(target)
        .then(response => {
          if (response.success && response.messages) {
            // Make sure messages is an array
            const messages = Array.isArray(response.messages) ? response.messages : [];
            
            // Update last timestamp
            messages.forEach(message => {
              if (message.timestamp > this.lastTimestamp) {
                this.lastTimestamp = message.timestamp;
              }
              
              // Display message if messageDisplay is available
              if (this.messageDisplay) {
                this.messageDisplay.displayMessage(message);
              }
              
              // Call onMessage callback if provided
              if (typeof this.options.onMessage === 'function') {
                this.options.onMessage(message);
              }
            });
          }
          
          // Continue polling if still active
          if (this.polling) {
            this.pollTimer = setTimeout(poll, this.options.pollInterval);
          }
        })
        .catch(error => {
          console.error('Error polling messages:', error);
          
          if (typeof this.options.onError === 'function') {
            this.options.onError(error);
          }
          
          // Continue polling if still active
          if (this.polling) {
            this.pollTimer = setTimeout(poll, this.options.pollInterval);
          }
        });
    };
    
    // Start polling
    poll();
  }
  
  /**
   * Stop polling for new messages
   */
  stopPolling() {
    this.polling = false;
    
    if (this.pollTimer) {
      clearTimeout(this.pollTimer);
      this.pollTimer = null;
    }
  }
  
  /**
   * Get messages from the server
   * 
   * @param {string} target Optional target filter
   * @return {Promise} Promise that resolves with the response
   */
  getMessages(target = null) {
    return fetch(this.options.processUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        sessionId: this.options.sessionId,
        target: target,
        lastTimestamp: this.lastTimestamp,
        action: 'getMessages'
      })
    })
    .then(response => response.json());
  }
  
  /**
   * Send a message to the server
   * 
   * @param {string} message Message text
   * @param {string} type Message type (info, warning, error, success)
   * @param {string} target Target container ID (optional)
   * @return {Promise} Promise that resolves when the message is sent
   */
  sendMessage(message, type = 'info', target = 'default') {
    return fetch(this.options.processUrl, {
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
  
  /**
   * Clear messages
   * 
   * @param {string} target Target container ID (optional)
   * @return {Promise} Promise that resolves when messages are cleared
   */
  clearMessages(target = null) {
    return fetch(this.options.processUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        sessionId: this.options.sessionId,
        target: target,
        action: 'clearMessages'
      })
    })
    .then(response => response.json());
  }
}

/**
 * Message display utility for both SSE and AJAX implementations
 */
class MessageDisplay {
  constructor(options = {}) {
    this.options = Object.assign({
      containerSelector: '.message-container',
      messageFormat: '<div class="alert alert-{type} mt-2">{message}</div>',
      autoScroll: true,
      preserveHistory: true
    }, options);
    
    this.containers = {};
    this.messageHistory = {};
    
    // Initialize containers
    document.querySelectorAll(this.options.containerSelector).forEach(container => {
      const targetId = container.dataset.target || 'default';
      this.containers[targetId] = container;
      this.messageHistory[targetId] = [];
    });
  }
  
  /**
   * Display a message in the appropriate container
   * 
   * @param {Object} messageData Message data object
   */
  displayMessage(messageData) {
    const targetId = messageData.target || 'default';
    const container = this.containers[targetId];
    
    if (!container) {
      console.warn(`Message container for target '${targetId}' not found`);
      return;
    }
    
    // Store message in history
    if (this.options.preserveHistory) {
      if (!this.messageHistory[targetId].some(m => m.id === messageData.id)) {
        this.messageHistory[targetId].push(messageData);
      }
    }
    
    // Create message element
    const messageHtml = this.formatMessage(messageData);
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = messageHtml;
    const messageElement = tempDiv.firstChild;
    messageElement.dataset.messageId = messageData.id;
    
    // Add message to container
    container.appendChild(messageElement);
    
    // Auto-scroll if enabled
    if (this.options.autoScroll) {
      container.scrollTop = container.scrollHeight;
    }
    
    return messageElement;
  }
  
  /**
   * Format a message according to the configured template
   * 
   * @param {Object} messageData Message data object
   * @return {string} Formatted message HTML
   */
  formatMessage(messageData) {
    return this.options.messageFormat
      .replace('{type}', messageData.type || 'info')
      .replace('{message}', messageData.message || '')
      .replace('{id}', messageData.id || '')
      .replace('{timestamp}', messageData.timestamp ? new Date(messageData.timestamp * 1000).toLocaleTimeString() : '');
  }
  
  /**
   * Clear all messages from a container
   * 
   * @param {string} targetId Target container ID (optional)
   */
  clearMessages(targetId = 'default') {
    const container = this.containers[targetId];
    if (container) {
      container.innerHTML = '';
      if (this.options.preserveHistory) {
        this.messageHistory[targetId] = [];
      }
    }
  }
  
  /**
   * Restore messages from history (useful when container is recreated, e.g., modal reopen)
   * 
   * @param {string} targetId Target container ID (optional)
   */
  restoreMessages(targetId = 'default') {
    const container = this.containers[targetId];
    if (container && this.messageHistory[targetId]) {
      this.messageHistory[targetId].forEach(messageData => {
        this.displayMessage(messageData);
      });
    }
  }
  
  /**
   * Register a new message container
   * 
   * @param {Element} container Container element
   * @param {string} targetId Target ID (optional)
   */
  registerContainer(container, targetId = 'default') {
    this.containers[targetId] = container;
    if (!this.messageHistory[targetId]) {
      this.messageHistory[targetId] = [];
    }
    
    // Restore messages if any exist in history
    if (this.options.preserveHistory && this.messageHistory[targetId].length > 0) {
      this.restoreMessages(targetId);
    }
  }
}

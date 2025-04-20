// Initialize message display

const messageDisplay = new MessageDisplay({
  containerSelector: '.message-container',
  messageFormat: '<div class="alert alert-{type} mt-2">{message}<small class="d-block text-muted">{timestamp}</small></div>',
  autoScroll: true,
  preserveHistory: true
});

// Initialize SSE client
const sseClient = new SseMessageClient({
  serverUrl: 'sse-server.php',
  messageDisplay: messageDisplay,
  onMessage: function(message) {
    // This is called for each new message
    console.log('New message:', message);
  },
  onConnect: function(data) {
    console.log('Connected to SSE server:', data);
    document.getElementById('connectionStatus').className = 'alert alert-success';
    document.getElementById('statusText').textContent = 'Connected';
  },
  onError: function(error) {
    console.error('SSE Error:', error);
    document.getElementById('connectionStatus').className = 'alert alert-danger';
    document.getElementById('statusText').textContent = 'Disconnected';
  }
});

// Connect to SSE server
sseClient.connect();

// Handle reconnect button
document.getElementById('reconnectButton').addEventListener('click', function() {
  sseClient.connect();
});

// Handle manual message form

document.getElementById('manualMessageForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const messageText = document.getElementById('messageText').value;
  const messageType = document.getElementById('messageType').value;
  const messageTarget = document.getElementById('messageTarget').value;
  
  if (messageText.trim() === '') {
    return;
  }
  
  // Use the new approach with an object containing message fields
  const messageData = {
    message: messageText,
    type: messageType
  };
  
  sseClient.sendMessage(messageData, messageTarget)
    .then(response => {
      if (response.success) {
        document.getElementById('messageText').value = '';
      }
    });
});

// Handle start process buttons

document.getElementById('startMainProcess').addEventListener('click', function() {
  sseClient.startProcess('demo-process.php', {}, 'main');
});

document.getElementById('startModalProcess').addEventListener('click', function() {
  sseClient.startProcess('demo-process.php', {}, 'modal');
});

// Handle clear messages buttons

document.getElementById('clearMainMessages').addEventListener('click', function() {
  messageDisplay.clearMessages('main');
  // Also clear on server
  fetch('ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sessionId: sseClient.options.sessionId,
      target: 'main',
      action: 'clearMessages'
    })
  });
});

document.getElementById('clearModalMessages').addEventListener('click', function() {
  messageDisplay.clearMessages('modal');
  // Also clear on server
  fetch('ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sessionId: sseClient.options.sessionId,
      target: 'modal',
      action: 'clearMessages'
    })
  });
});

// Handle modal events to restore messages
const messageModal = document.getElementById('messageModal');
messageModal.addEventListener('shown.bs.modal', function() {
  messageDisplay.restoreMessages('modal');
});

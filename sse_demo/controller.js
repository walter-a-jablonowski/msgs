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
  onConnect: function(data) {
    const statusEl = document.getElementById('connectionStatus');
    statusEl.textContent = 'Connected (Session ID: ' + data.sessionId + ')';
    statusEl.className = 'alert alert-success';
  },
  onError: function(event) {
    const statusEl = document.getElementById('connectionStatus');
    statusEl.textContent = 'Connection error. Attempting to reconnect...';
    statusEl.className = 'alert alert-danger';
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
  
  sseClient.sendMessage(messageText, messageType, messageTarget)
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

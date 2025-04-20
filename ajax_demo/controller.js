// Initialize message display

const messageDisplay = new MessageDisplay({
  containerSelector: '.message-container',
  messageFormat: '<div class="alert alert-{type} mt-2">{message}<small class="d-block text-muted">{timestamp}</small></div>',
  autoScroll: true,
  preserveHistory: true
});

// Initialize AJAX client
const ajaxClient = new AjaxMessageClient({
  processUrl: 'ajax.php',
  messageDisplay: messageDisplay,
  autoPolling: false,
  onMessage: function(message) {
    // This is called for each new message
    console.log('New message:', message);
  },
  onError: function(error) {
    console.error('Error:', error);
    
    const statusEl = document.getElementById('statusText');
    statusEl.textContent = 'Error polling';
    document.getElementById('pollingStatus').className = 'alert alert-danger';
  }
});

// Handle polling buttons

document.getElementById('startPollingButton').addEventListener('click', function() {
  ajaxClient.startPolling();
  
  const statusEl = document.getElementById('statusText');
  statusEl.textContent = 'Polling active';
  document.getElementById('pollingStatus').className = 'alert alert-success';
});

document.getElementById('stopPollingButton').addEventListener('click', function() {
  ajaxClient.stopPolling();
  
  const statusEl = document.getElementById('statusText');
  statusEl.textContent = 'Polling stopped';
  document.getElementById('pollingStatus').className = 'alert alert-secondary';
});

// Start polling by default

document.getElementById('startPollingButton').click();

// Handle manual message form

document.getElementById('manualMessageForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const messageText = document.getElementById('messageText').value;
  const messageType = document.getElementById('messageType').value;
  const messageTarget = document.getElementById('messageTarget').value;
  
  if( messageText.trim() === '' )
  {
    return;
  }
  
  // Use the new approach with an object containing message fields
  const messageData = {
    message: messageText,
    type: messageType
  };
  
  ajaxClient.sendMessage(messageData, messageTarget)
    .then(response => {
      if( response.success )
      {
        document.getElementById('messageText').value = '';
      }
    });
});

// Handle start process buttons

document.getElementById('startMainProcess').addEventListener('click', function() {
  ajaxClient.startProcess('demo-process.php', {}, 'main');
});

document.getElementById('startModalProcess').addEventListener('click', function() {
  ajaxClient.startProcess('demo-process.php', {}, 'modal');
});

// Handle clear messages buttons

document.getElementById('clearMainMessages').addEventListener('click', function() {
  messageDisplay.clearMessages('main');
  ajaxClient.clearMessages('main');
});

document.getElementById('clearModalMessages').addEventListener('click', function() {
  messageDisplay.clearMessages('modal');
  ajaxClient.clearMessages('modal');
});

// Handle modal events to restore messages
const messageModal = document.getElementById('messageModal');
messageModal.addEventListener('shown.bs.modal', function() {
  messageDisplay.restoreMessages('modal');
});

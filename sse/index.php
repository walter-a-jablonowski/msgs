<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSE Message Demo</title>
  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <link href="../common/message-display.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4">SSE Message Demo</h1>
    
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Main Page Messages</h5>
          </div>
          <div class="card-body">
            <div class="message-container" data-target="main"></div>
            
            <div class="mt-3">
              <button id="startMainProcess" class="btn btn-primary">Start Process</button>
              <button id="clearMainMessages" class="btn btn-secondary">Clear Messages</button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Connection Status</h5>
          </div>
          <div class="card-body">
            <div id="connectionStatus" class="alert alert-secondary">Not connected</div>
            <button id="reconnectButton" class="btn btn-outline-primary btn-sm">Reconnect</button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Manual Message</h5>
          </div>
          <div class="card-body">
            <form id="manualMessageForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <input type="text" id="messageText" class="form-control" placeholder="Enter message text">
                </div>
                <div class="col-md-2">
                  <select id="messageType" class="form-select">
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="danger">Error</option>
                    <option value="success">Success</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select id="messageTarget" class="form-select">
                    <option value="main">Main</option>
                    <option value="modal">Modal</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary w-100">Send</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">Modal Messages</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="message-container" data-target="modal"></div>
          </div>
          <div class="modal-footer">
            <button id="startModalProcess" class="btn btn-primary">Start Process</button>
            <button id="clearModalMessages" class="btn btn-secondary">Clear Messages</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="d-grid gap-2 col-md-4 mx-auto">
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#messageModal">
        Open Modal Messages
      </button>
    </div>
  </div>
  
  <!-- Bootstrap 5.3 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Message Display Library -->
  <script src="../common/message-display.js"></script>
  <!-- SSE Client -->
  <script src="sse-client.js"></script>
  
  <script>
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
      
      fetch('process.php', {
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
      
      fetch('process.php', {
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
  </script>
</body>
</html>

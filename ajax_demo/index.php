<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AJAX Message Demo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bs-primary: #FF6B00;
      --bs-primary-rgb: 255,107,0;
    }
    .btn-primary {
      background-color: #FF6B00 !important;
      border-color: #FF6B00 !important;
    }
    .btn-outline-primary {
      color: #FF6B00 !important;
      border-color: #FF6B00 !important;
    }
    .btn-primary:hover .btn-primary:focus,
    .btn-outline-primary:hover .btn-outline-primary:focus {
      background-color: #e65e00 !important;
      border-color: #e65e00 !important;
    }
  </style>
  <!-- Custom styles -->
  <link href="../msgs_lib/message-display.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4">AJAX Message Demo</h1>
    
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
            <h5 class="card-title mb-0">Status</h5>
          </div>
          <div class="card-body">
            <div id="pollingStatus" class="alert alert-secondary">Polling status: <span id="statusText">Not started</span></div>
            <div class="btn-group" role="group">
              <button id="startPollingButton" class="btn btn-outline-primary btn-sm">Start Polling</button>
              <button id="stopPollingButton" class="btn btn-outline-secondary btn-sm">Stop Polling</button>
            </div>
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
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Message Display Library -->
  <script src="../msgs_lib/message-display.js"></script>
  <!-- AJAX Client -->
  <script src="../msgs_lib/ajax/ajax-client.js"></script>
  <script src="controller.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP AJAX Progress Messages</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .implementation-card {
      transition: transform 0.2s;
    }
    .implementation-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row mb-4">
      <div class="col-12 text-center">
        <h1 class="display-4">PHP AJAX Progress Messages</h1>
        <p class="lead">Two implementation variants for displaying progress messages from PHP scripts</p>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card h-100 implementation-card">
          <div class="card-body">
            <h5 class="card-title">Server-Sent Events (SSE) Implementation</h5>
            <p class="card-text">Uses Server-Sent Events to stream messages from the server to the client in real-time.</p>
            <ul>
              <li>Real-time updates without polling</li>
              <li>Efficient server-to-client communication</li>
              <li>Better performance for frequent updates</li>
            </ul>
          </div>
          <div class="card-footer bg-transparent border-top-0">
            <a href="sse_demo/index.php" class="btn btn-primary w-100">View SSE Demo</a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 mb-4">
        <div class="card h-100 implementation-card">
          <div class="card-body">
            <h5 class="card-title">Plain AJAX Implementation</h5>
            <p class="card-text">Uses regular AJAX polling to fetch messages from the server at regular intervals.</p>
            <ul>
              <li>Works in all browsers</li>
              <li>Simple implementation</li>
              <li>No special server requirements</li>
            </ul>
          </div>
          <div class="card-footer bg-transparent border-top-0">
            <a href="ajax_demo/index.php" class="btn btn-primary w-100">View AJAX Demo</a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Implementation Details</h5>
          </div>
          <div class="card-body">
            <h6>Features</h6>
            <ul>
              <li>Usable in multiple apps</li>
              <li>Easy integration</li>
              <li>User-defined message format per app</li>
              <li>Messages can be displayed anywhere in the page, including Bootstrap modals</li>
              <li>Messages persist when modal is closed and reopened</li>
              <li>Messages persist when navigating away and back (if browser storage is available)</li>
              <li>Multiple separate processes can display messages in different locations</li>
            </ul>
            
            <h6>Technology</h6>
            <ul>
              <li>No jQuery or third-party libraries</li>
              <li>Pure JavaScript and PHP</li>
              <li>Bootstrap 5.3 for styling</li>
              <li>File-based message storage</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

# PHP AJAX Progress Messages

This project implements a system for displaying progress messages from PHP scripts via AJAX. It provides two implementation variants:

1. **Server-Sent Events (SSE)** - Real-time streaming of messages
2. **Plain AJAX** - Polling-based message retrieval


## Table of Contents

- [Features](#features)
- [Project Structure](#project-structure)
- [Sample Installation](#sample-installation)
- [Basic Integration](#basic-integration)
  - [1. Include the necessary files](#1-include-the-necessary-files)
  - [2. Initialize the client](#2-initialize-the-client)
  - [3. Start a long-running process](#3-start-a-long-running-process)
  - [4. Create your PHP process](#4-create-your-php-process)
- [Customization](#customization)
  - [Message Format](#message-format)
  - [Multiple Message Containers](#multiple-message-containers)
- [License](#license)


## Features

- Usable in multiple applications
- User-defined message format per app
- Messages can be displayed anywhere in the page, including Bootstrap modals
- Messages persist when modal is closed and reopened
- Messages persist when navigating away and back
- Multiple separate processes can display messages in different locations


## Project Structure

```
/msgs
|
├── /ajax_demo               # Ajax demo
│   ├── ajax.php             # 
│   ├── controller.js        # 
│   ├── demo-process.php     # 
│   └── index.php            # 
|                            #
├── /msgs_lib                # Libary
│   ├── MessageManager.php   #   PHP message management class
│   ├── message-display.css  #   UI message style (may be usd)
│   ├── message-display.js   # 
│   ├── /ajax                # 
│   │   └── ajax-client.js   #   AJAX client
│   └── /sse                 # 
│       └── sse-client.js    #   SSE client
|                            #
├── /data                    # 
│   └── /messages            # JSON message files (created automatically)
|                            #
├── /sse_demo                # SSE demo
│   ├── ajax.php             #   for clients to submit new messages or perform actions
│   ├── controller.js        # 
│   ├── demo-process.php     # 
│   ├── index.php            # 
│   └── sse-server.php       #   for pushing messages server -> client
|                            #
├── composer.json            # Composer dependencies
├── index.php                # Main entry point
└── README.md                # This file
```


## Sample installation

1. Place the files in your web server directory
2. Make sure the `data/messages` directory is writable by the web server
3. Run `composer install` to install dependencies


## Basic Integration

### 1. Include the necessary files

```html
<link href="path/to/msgs_lib/message-display.css" rel="stylesheet">

<!-- Create message containers -->
<div class="message-container" data-target="main"></div>

<script src="path/to/msgs_lib/message-display.js"></script>
<script src="path/to/msgs_lib/sse/sse-client.js"></script>
<!-- OR -->
<script src="path/to/msgs_lib/ajax/ajax-client.js"></script>
```

### 2. Initialize the client

```javascript
// Initialize message display
const messageDisplay = new MessageDisplay({
  containerSelector: '.message-container',
  messageFormat: '<div class="alert alert-{type} mt-2">{message}</div>',
  autoScroll: true,
  preserveHistory: true
});

// For SSE implementation
const messageClient = new SseMessageClient({
  serverUrl: 'path/to/sse-server.php',
  messageDisplay: messageDisplay
});
messageClient.connect();

// OR for AJAX implementation
const messageClient = new AjaxMessageClient({
  processUrl: 'path/to/process.php',
  messageDisplay: messageDisplay
});
messageClient.startPolling();
```

### 3. Start a long-running process

```javascript
// Start a process that will send messages
messageClient.startProcess('path/to/your-process.php', {
  // Additional parameters
  param1: 'value1',
  param2: 'value2'
}, 'main');  // target container ID
```

### 4. Create your PHP process

```php
<?php
require_once 'path/to/common/MessageManager.php';

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if session ID is provided
if( empty($data['sessionId']) ) {
  echo json_encode(['success' => false, 'error' => 'Session ID is required']);
  exit;
}

// Initialize message manager
$messagesDir = 'path/to/data/messages';
$messageManager = new MessageManager($messagesDir, $data['sessionId']);

// Send initial response to client
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Process started']);

// Close the connection to the client
if( function_exists('fastcgi_finish_request') ) {
  fastcgi_finish_request();
} else {
  ob_end_flush();
  flush();
}

// Get target from data (optional)
$target = $data['target'] ?? 'default';

// Your long-running process
$messageManager->addMessage("Starting process...", "info", $target);
// Do some work...
$messageManager->addMessage("Step 1 complete", "info", $target);
// Do some work...
$messageManager->addMessage("Process completed!", "success", $target);
```


## Customization

### Message Format

You can customize the message format by changing the `messageFormat` option:

```javascript
const messageDisplay = new MessageDisplay({
  messageFormat: '<div class="alert alert-{type}">{message}<small>{timestamp}</small></div>'
});
```

Available placeholders:
- `{message}` - The message text
- `{type}` - Message type (info, warning, danger, success)
- `{id}` - Unique message ID
- `{timestamp}` - Message timestamp

### Multiple Message Containers

You can have multiple message containers on a page:

```html
<div class="message-container" data-target="main"></div>
<div class="message-container" data-target="sidebar"></div>
```

When sending messages, specify the target:

```javascript
messageClient.sendMessage("This goes to the sidebar", "info", "sidebar");
```


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2025, free under [MIT license](LICENSE)

This app is build upon PHP and free software (see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)

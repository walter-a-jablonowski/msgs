<?php
// Set unlimited execution time
set_time_limit(0);

// Prevent buffering
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', true);
ob_implicit_flush(true);

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Disable output buffering
if (ob_get_level()) ob_end_clean();

echo "retry: 1000\n";

// Send a simple event
echo "event: message\n";
echo "data: " . json_encode(['message' => 'Test message', 'type' => 'info', 'timestamp' => time()]) . "\n\n";
flush();

// Send another event after a delay
sleep(2);
echo "event: message\n";
echo "data: " . json_encode(['message' => 'Second test message', 'type' => 'success', 'timestamp' => time()]) . "\n\n";
flush();

// Keep connection open
$counter = 0;
while ($counter < 5) {
    // Check if client is still connected
    if (connection_aborted()) {
        break;
    }
    
    // Send a ping every 5 seconds
    sleep(5);
    echo "event: ping\n";
    echo "data: " . json_encode(['time' => time()]) . "\n\n";
    flush();
    
    $counter++;
}

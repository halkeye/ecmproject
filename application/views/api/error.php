<?php

// Unique error identifier
$error_id = uniqid('error');
echo json_encode(array(
    'code' => $code,
    'message' => $message,
    'file' => Debug::path($file),
    'line' => $line,
));
?>

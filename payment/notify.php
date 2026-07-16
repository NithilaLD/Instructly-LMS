<?php
    file_put_contents("payment-log.txt", json_encode($_POST) . PHP_EOL, FILE_APPEND);
    http_response_code(200);
?>
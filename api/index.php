<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $response = array(
        'message' => 'Hello, World!'
    );

    echo json_encode($response);
} else {

    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'));
}
?>





<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="mystyle.css">
</head>
<body>

<h1>Football Statistic Viewer</h1>

<h3><a href="/api/home.html" class="home">Enter</a></h3>


</body>
</html>




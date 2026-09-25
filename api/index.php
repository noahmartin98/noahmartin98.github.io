<?php
// Get the requested URL path (e.g., /api/users or /api/login)
$requestUri = $_SERVER['REQUEST_URI'];

// Strip out any query strings like ?id=123 so they don't break our matching
$path = parse_url($requestUri, PHP_URL_PATH);

// Route the request to the correct file based on the URL path
switch ($path) {
    case '/api/bracket':
        include __DIR__ . '/bracket.php';
        break;

    case '/api/databaseConnect':
        include __DIR__ . '/databaseConnect.php';
        break;

    case '/api/defLeaders':
        include __DIR__ . '/defLeaders.php';
        break;

    case '/api/gamePage':
        include __DIR__ . '/gamePage.php';
        break;

    case '/api/navbar':
        include __DIR__ . '/navbar.php';
        break;

    case '/api/passingLeaders':
        include __DIR__ . '/passingLeaders.php';
        break;

    case '/api/playerPageDef':
        include __DIR__ . '/playerPageDef.php';
        break;
    
    case '/api/playerPagePass':
        include __DIR__ . '/playerPagePass.php';
        break;

    case '/api/playerPageRec':
        include __DIR__ . '/playerPageRec.php';
        break;

    case '/api/playerPageRush':
        include __DIR__ . '/playerPageRush.php';
        break;

    case '/api/receivingLeaders':
        include __DIR__ . '/receivingLeaders.php';
        break;

    case '/api/rushingLeaders':
        include __DIR__ . '/rushingLeaders.php';
        break;

    case '/api/scoreboard':
        include __DIR__ . '/scoreboard.php';
        break;

    case '/api/teamPage':
        include __DIR__ . '/teamPage.php';
        break;

  

    // Add a case block for each of your other PHP files here...

    default:
        // If the path doesn't match any of your files, return a 404
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}

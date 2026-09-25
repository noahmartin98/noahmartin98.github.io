<?php
// Get the requested URL path (e.g., /api/users or /api/login)
$requestUri = $_SERVER['REQUEST_URI'];

// Strip out query strings (e.g., ?id=123)
$path = parse_url($requestUri, PHP_URL_PATH);

// Clean up the path: strip trailing slashes so /api/users/ matches /api/users
$path = rtrim($path, '/');

// Route the request to the correct file based on the URL path
// ROUTER: Match both Vercel's internal rewritten paths and direct paths
switch ($path) {
    case '/api/bracket.php':
    case '/bracket':
        include __DIR__ . '/bracket.php';
        break;

    case '/api/databaseConnect.php':
    case '/databaseConnect':
        include __DIR__ . '/databaseConnect.php';
        break;

    case '/api/defLeaders.php':
    case '/defLeaders':
        include __DIR__ . '/defLeaders.php';
        break;

    case '/api/gamePage.php':
    case '/gamePage':
        include __DIR__ . '/gamePage.php';
        break;

    case '/api/navbar.php':
    case '/navbar':
        include __DIR__ . '/navbar.php';
        break;

    case '/api/passingLeaders.php':
    case '/passingLeaders':
        include __DIR__ . '/passingLeaders.php';
        break;

    case '/api/playerPageDef.php':
    case '/playerPageDef':
        include __DIR__ . '/playerPageDef.php';
        break;
    
    case '/api/playerPagePass.php':
    case '/playerPagePass':
        include __DIR__ . '/playerPagePass.php';
        break;

    case '/api/playerPageRec.php':
    case '/playerPageRec':
        include __DIR__ . '/playerPageRec.php';
        break;

    case '/api/playerPageRush.php':
    case '/playerPageRush':
        include __DIR__ . '/playerPageRush.php';
        break;

    case '/api/receivingLeaders.php':
    case '/receivingLeaders':
        include __DIR__ . '/receivingLeaders.php';
        break;

    case '/api/rushingLeaders.php':
    case '/rushingLeaders':
        include __DIR__ . '/rushingLeaders.php';
        break;

    case '/api/scoreboard.php':
    case '/scoreboard':
        include __DIR__ . '/scoreboard.php';
        break;

    case '/api/teamPage.php':
    case '/teamPage':
        include __DIR__ . '/teamPage.php';
        break;

  

    // Add a case block for each of your other PHP files here...

    default:
        // Debug mode: If it still fails, this tells us exactly what path PHP is seeing!
        http_response_code(404);
        echo json_encode([
            "error" => "Endpoint not found",
            "debug_received_path" => $path
        ]);
        break;
}

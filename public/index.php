<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$phpView = new PhpRenderer('../templates');
// Add routes
$app->get('/', function (Request $request, Response $response) use ($phpView) {
    // $html = '';
    // $response->getBody()->write($html);
    return $phpView->render($response, 'index.phtml');
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/users/{id}', function ($request, $response, $args) use ($phpView) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    return $phpView->render($response, 'show.phtml', $params);
});

$app->get('/about', function (Request $request, Response $response) use ($phpView) {
    return $phpView->render($response, 'about.phtml');
});

$app->run();

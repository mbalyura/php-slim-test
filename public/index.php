<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

// валидатор курсов
use App\OrderValidator;

require __DIR__ . '/../vendor/autoload.php';

// репозиторий для курсов
$repo = new App\OrderRepository();
// Список пользователей
// Каждый пользователь – ассоциативный массив
// следующей структуры: id, firstName, lastName, email
$users = App\Generator::generate(100);

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

$app->get('/users', function ($request, $response) use ($users, $phpView) {
    $term = $request->getQueryParams()['term'];
    if ($term) {
        $users = collect($users)->filter(fn($user) => stripos(($user['firstName']), $term) === 0);
    }
    $params = ['users' => $users, 'term' => $term];
    return $phpView->render($response, 'users/index.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) use ($users, $phpView) {
    $id = (int) $args['id'];
    $user = collect($users)->firstWhere('id', $id);
    $params = ['user' => $user];
    return $phpView->render($response, 'users/show.phtml', $params);
});

$app->get('/orders', function ($request, $response) use ($repo, $phpView) {
    $params = [
        'orders' => $repo->all()
    ];
    return $phpView->render($response, 'orders/index.phtml', $params);
});

$app->get('/orders/new', function ($request, $response) use ($repo, $phpView) {
    $params = [
        'order' => ['paid' => null, 'title' => ''],
        'errors' => []
    ];
    return $phpView->render($response, "orders/new.phtml", $params);
});

$app->post('/orders', function ($request, $response) use ($repo, $phpView) {
    $validator = new OrderValidator();
    $order = $request->getParsedBody()['order'];
    $errors = $validator->validate($order);
    if (count($errors) === 0) {
        $repo->save($order);
        return $response
            ->withHeader('Location', '/orders')
            ->withStatus(302);
    }
    $params = [
        'order' => $order,
        'errors' => $errors
    ];
    return $phpView->render($response->withStatus(422), "orders/new.phtml", $params);
});

$app->get('/about', function (Request $request, Response $response) use ($phpView) {
    return $phpView->render($response, 'about.phtml');
});

$app->run();

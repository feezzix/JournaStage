<?php
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/controllers/AdminController.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/HomeController.php';
require_once __DIR__ . '/app/controllers/ProfessorController.php';
require_once __DIR__ . '/app/controllers/ProfileController.php';
require_once __DIR__ . '/app/controllers/StudentController.php';

$userRepository = new UserRepository(Database::getInstance());
$sessionRepository = new SessionRepository(Database::getInstance());
$authService = new AuthService($userRepository, $sessionRepository);

$user = $authService->getAuthenticatedUser();
$userStatus = $user ? $user->getStatus() : null;

$router = new Router();


// *** ROUTES ***

// public routes
$router->get('/se-connecter', [AuthController::class, 'login']);
$router->post('/se-connecter', [AuthController::class, 'login']);

$router->get('/deconnexion', [AuthController::class, 'logout']);

$router->get('/mot-de-passe-oublie', [AuthController::class, 'forgotPassword']);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'forgotPassword']);;

$router->get('/demander-un-compte', [AuthController::class, 'requestAccount']);
$router->post('/demander-un-compte', [AuthController::class, 'requestAccount']);;

$router->get('/contact', [AuthController::class, 'contact']);
$router->post('/contact', [AuthController::class, 'contact']);;

// same for all status
$router->get('', [HomeController::class, 'home']);
$router->post('', [HomeController::class, 'home']);

$router->get('/informations-personnelles', [ProfileController::class, 'personalInfo']);

$router->get('/informations-personnelles/modifier-mot-de-passe', [ProfileController::class, 'editPassword']);
$router->post('/informations-personnelles/modifier-mot-de-passe', [ProfileController::class, 'editPassword']);

$router->get('/informations-personnelles/modifier-classe', [ProfileController::class, 'editClass']);
$router->post('/informations-personnelles/modifier-classe', [ProfileController::class, 'editClass']);

// student
$router->get('/nouveau-compte-rendu', [StudentController::class, 'newReport'], fn() => $userStatus === 0);
$router->post('/nouveau-compte-rendu', [StudentController::class, 'newReport'], fn() => $userStatus === 0);

$router->get('/mes-comptes-rendus', [StudentController::class, 'myReports'], fn() => $userStatus === 0);
$router->post('/mes-comptes-rendus', [StudentController::class, 'myReports'], fn() => $userStatus === 0);;

// professor
$router->get('/mes-classes', [ProfessorController::class, 'viewClasses'], fn() => $userStatus === 1);

$router->get('/classe', [ProfessorController::class, 'viewClass'], fn() => $userStatus === 1);

$router->get('/etudiant', [ProfessorController::class, 'viewStudent'], fn() => $userStatus === 1);


// *** DISPATCHER ***

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);

<?php

use App\Core\Router;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\ImportController;

$router = new Router();
$pub = new PublicController();
$auth = new AuthController();
$admin = new AdminController();
$imports = new ImportController();
$router->get('/', [$pub, 'home']);
$router->post('/recherche', [$pub, 'search']);
$router->get('/connexion', [$auth, 'login']);
$router->post('/connexion', [$auth, 'authenticate']);
$router->post('/deconnexion', [$auth, 'logout']);
$router->get('/admin', [$admin, 'dashboard']);
$router->get('/admin/etudiants', [$admin, 'students']);
$router->post('/admin/etudiants/creer', [$admin, 'createStudent']);
$router->get('/admin/diplomes', [$admin, 'diplomas']);
$router->post('/admin/diplomes/creer', [$admin, 'createDiploma']);
$router->post('/admin/diplomes/{id}/statut', [$admin, 'transitionDiploma']);
$router->get('/admin/imports', [$imports, 'index']);
$router->post('/admin/imports/televerser', [$imports, 'upload']);
$router->get('/admin/retraits', [$admin, 'withdrawals']);
$router->post('/admin/retraits/remettre', [$admin, 'handover']);
$router->get('/admin/retraits/{id}/recu', [$admin, 'receipt']);
$router->get('/admin/audit', [$admin, 'audit']);
return $router;

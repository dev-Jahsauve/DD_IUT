<?php
declare(strict_types=1);
require dirname(__DIR__).'/config/bootstrap.php';
use App\Core\Router;
$router=require dirname(__DIR__).'/routes/web.php';
$router->dispatch();
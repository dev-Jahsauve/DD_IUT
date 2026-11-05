<?php
namespace App\Core;
class Controller {
    protected function view(string $name, array $data=[]): void {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__).'/Views/layouts/header.php';
        require dirname(__DIR__).'/Views/'.$name.'.php';
        require dirname(__DIR__).'/Views/layouts/footer.php';
    }
    protected function redirect(string $path): never {
        header('Location: '.rtrim(Env::get('APP_URL',''),'/').$path); exit;
    }
    protected function json(array $data, int $status=200): never {
        http_response_code($status); header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data,JSON_UNESCAPED_UNICODE); exit;
    }
}

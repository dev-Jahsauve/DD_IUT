<?php
namespace App\Core;
final class Router {
    private array $routes=[];
    public function get(string $path, callable $handler): void {$this->routes['GET'][$path]=$handler;}
    public function post(string $path, callable $handler): void {$this->routes['POST'][$path]=$handler;}
    public function dispatch(): void {
        $method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri=parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base=rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base && str_starts_with($uri,$base)) $uri=substr($uri,strlen($base)) ?: '/';
        $uri='/'.trim($uri,'/');
        foreach ($this->routes[$method] ?? [] as $path=>$handler) {
            $regex=preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#','([^/]+)',$path);
            if (preg_match('#^'.$regex.'$#',$uri,$m)) {
                array_shift($m); $handler(...array_map('urldecode',$m)); return;
            }
        }
        http_response_code(404); echo "Page introuvable.";
    }
}

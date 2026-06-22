<?php
class Router
{
  private array $getRoutes = [];
  private array $postRoutes = [];

  // GET
  public function get(string $path, $callback, ?callable $access = null): void
  {
    $this->getRoutes[$path] = [
      'callback' => $callback,
      'access' => $access
    ];
  }

  // POST
  public function post(string $path, $callback, ?callable $access = null): void
  {
    $this->postRoutes[$path] = [
      'callback' => $callback,
      'access' => $access
    ];
  }

  // DISPATCH
  public function dispatch(string $method, string $uri): void
  {
    $prefix = 'JournaStage';
    $uri = parse_url($uri, PHP_URL_PATH);

    if (strpos(trim($uri, '/'), $prefix) === 0) {
      $uri = '/' . ltrim(substr(trim($uri, '/'), strlen($prefix)), '/');
    }

    if ($uri === '/' || $uri === '') {
      $uri = '';
    } else {
      $uri = '/' . ltrim($uri, '/');
    }

    $routes = $method === 'POST' ? $this->postRoutes : $this->getRoutes;

    if (isset($routes[$uri])) {
      $route = $routes[$uri];

      if (isset($route['access']) && is_callable($route['access']) && !$route['access']()) {
        http_response_code(403);
        renderView('error/403', [
          'title' => 'JournaStage - Erreur'
        ]);
        return;
      }

      if (is_array($route['callback'])) {
        $controller = new $route['callback'][0]();
        call_user_func([$controller, $route['callback'][1]]);
      } else {
        call_user_func($route['callback']);
      }
    } else {
      http_response_code(404);
      renderView('error/404', [
        'title' => 'JournaStage - Erreur'
      ]);
      return;
    }
  }
}

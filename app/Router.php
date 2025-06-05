<?php
declare(strict_types=1);

namespace App;

use App\Container;

class Router
{
    /**
     * Array storing all registered routes.
     * Structure:
     * [
     *   'GET'  => [
     *     '/books'                   => ['controller' => 'App\Controllers\BookController', 'action' => 'index'],
     *     '/books/{id}/edit'         => ['controller' => 'App\Controllers\BookController', 'action' => 'editForm'],
     *     ...
     *   ],
     *   'POST' => [
     *     '/books'                   => ['controller' => 'App\Controllers\BookController', 'action' => 'store'],
     *     '/books/{id}/delete'       => ['controller' => 'App\Controllers\BookController', 'action' => 'delete'],
     *     ...
     *   ]
     * ]
     *
     * @var array<string, array<string, array{controller:string,action:string}>>
     */
    protected static array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Register a new HTTP route.
     *
     * @param string $method     HTTP method (e.g., 'GET', 'POST').
     * @param string $pattern    Path with optional parameters (e.g., '/books/{id}/edit').
     * @param string $controller Full controller class name with namespace, e.g., 'App\Controllers\BookController'.
     * @param string $action     Method in the controller, e.g., 'editForm'.
     */
    public static function add(string $method, string $pattern, string $controller, string $action): void
    {
        $method = strtoupper($method);
        if (!isset(self::$routes[$method])) {
            self::$routes[$method] = [];
        }
        // Store the literal pattern (with braces) and convert to regex later
        self::$routes[$method][$pattern] = [
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    /**
     * Register a GET route.
     */
    public static function get(string $pattern, string $controller, string $action): void
    {
        self::add('GET', $pattern, $controller, $action);
    }

    /**
     * Register a POST route.
     */
    public static function post(string $pattern, string $controller, string $action): void
    {
        self::add('POST', $pattern, $controller, $action);
    }

    /**
     * Convert a {param} style path to a regular expression with named groups.
     *
     * Example:
     *   '/books/{id}/edit'  ⇒ '#^/books/(?P<id>[^/]+)/edit$#'
     *
     * @param string $pattern  Path with braces (e.g., '/members/{id}').
     * @return string          Regex with named groups.
     */
    protected static function patternToRegex(string $pattern): string
    {
        // Escape slashes and search for segments like {param}
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            fn(array $m) => '(?P<' . $m[1] . '>[^/]+)',
            $pattern
        );

        // Add start/end delimiters
        return '#^' . $regex . '$#';
    }

    /**
     * Iterate over all registered routes and try to match the current URI.
     * If a match is found, create the controller instance and call the method with parameters.
     *
     * @param string $requestUri  String like '/books/123/edit'.
     */
    public static function dispatch(string $requestUri): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $routesForMethod = self::$routes[$httpMethod] ?? [];

        foreach ($routesForMethod as $pattern => $handler) {
            $regex = self::patternToRegex($pattern);

            if (preg_match($regex, $requestUri, $matches)) {
                // Extract only named groups because $matches also contains numeric indexes
                $params = array_filter(
                    $matches,
                    fn($k) => is_string($k),
                    ARRAY_FILTER_USE_KEY
                );

                $fullController = $handler['controller'];   
                $action         = $handler['action'];      

                // Load the controller file (assuming structure: app/controllers/<Name>.php)
                $shortClass = substr(strrchr($fullController, '\\'), 1); 
                $filePath   = __DIR__ . '/controllers/' . $shortClass . '.php';

                if (!file_exists($filePath)) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo "Controller file not found: $filePath";
                    exit;
                }

                require_once $filePath;

                if (!class_exists($fullController)) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo "Controller class missing: $fullController";
                    exit;
                }

                $controllerInstance = \App\Container::get($fullController);
                if (!method_exists($controllerInstance, $action)) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo "Missing method '$action' in controller $fullController";
                    exit;
                }

                // If the method expects parameters, pass them in the order of their names
                // (assuming we use named groups in the regex so key => value)
                // Unpack the parameters as arguments in the order defined in the method
                $refMethod = new \ReflectionMethod($fullController, $action);
                $orderedArgs = [];
                foreach ($refMethod->getParameters() as $param) {
                    $pName = $param->getName();
                    if (isset($params[$pName])) {
                        $orderedArgs[] = $params[$pName];
                    }
                }

                // Invocation:
                call_user_func_array([$controllerInstance, $action], $orderedArgs);
                return;
            }
        }

        // If no matching route is found → 404
        header("HTTP/1.1 404 Not Found");
        echo "404 - No route found for '$requestUri'";
        exit;
    }
}

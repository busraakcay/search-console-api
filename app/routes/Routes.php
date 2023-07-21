<?php
$routes = [
    '/' => 'Controller@index',
    '/index' => 'Controller@index',
    '/loginWithGoogle' => 'Controller@loginWithGoogle',
    '/urlResults' => 'Controller@urlResults',
    '/scanUrls' => 'Controller@scanUrls',
    '/analyzeKeywords' => 'Controller@analyzeKeywords',
    '/analyzeKeywordsWeekly' => 'Controller@analyzeKeywordsWeekly',
];

$requestUrl = isset($_GET['url']) ? '/' . trim($_GET['url'], '/') : '/';
$controller = "Controller";
$action = "index";
if (isset($routes[$requestUrl])) {
    list($controller, $action) = explode('@', $routes[$requestUrl]);
}
if ($controller && $action) {
    require_once 'app/controllers/' . $controller . '.php';
    $controllerInstance = new $controller();
}

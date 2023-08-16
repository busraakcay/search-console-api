<?php
$routes = [
    '/' => 'Controller@index',
    '/index' => 'Controller@index',
    '/loginWithGoogle' => 'Controller@loginWithGoogle',
    '/urlResults' => 'Controller@urlResults',
    '/scanUrls' => 'Controller@scanUrls',
    '/analyzeKeywords' => 'Controller@analyzeKeywords',
    '/analyzeKeywordsWeekly' => 'Controller@analyzeKeyw ordsWeekly',
    '/ga4' => 'Controller@ga4',
    '/getActiveUserCount' => 'Controller@getActiveUserCount',
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

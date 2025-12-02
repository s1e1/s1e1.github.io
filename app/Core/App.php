<?php
/**
 * Classe principale de l'application
 */

class App {
    private $controller = 'AuthController';
    private $method = 'index';
    private $params = [];

    public function __construct() {
        $url = $this->parseUrl();
        
        // Vérifier si le contrôleur existe
        if (isset($url[0]) && file_exists('../app/Controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }
        
        require_once '../app/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;
        
        // Vérifier si la méthode existe
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }
        
        // Paramètres restants
        $this->params = $url ? array_values($url) : [];
        
        // Appeler la méthode avec les paramètres
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}


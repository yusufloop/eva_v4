<?php
// Simple Router Class
class SimpleRouter {
    private $routes = [];
    
    public function addRoute($pattern, $file, $requiredRole = null) {
        $this->routes[$pattern] = [
            'file' => $file,
            'role' => $requiredRole
        ];
    }
    
    public function handleRequest() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        // Default route
        if (empty($uri)) {
            $uri = 'dashboard';
        }
        
        foreach ($this->routes as $pattern => $route) {
            if ($this->matchRoute($pattern, $uri)) {
                $this->executeRoute($route, $uri);
                return;
            }
        }
        
        // 404 - Route not found
        $this->show404();
    }
    
    private function matchRoute($pattern, $uri) {
        // Convert pattern to regex
        $pattern = str_replace('*', '([^/]+)', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        return preg_match($pattern, $uri);
    }
    
    private function executeRoute($route, $uri) {
        // Check authentication and role
        if ($route['role']) {
            $this->checkRole($route['role']);
        }
        
        // Extract parameters from URL
        $params = $this->extractParams($uri);
        $_GET = array_merge($_GET, $params);
        
        // Include the file
        if (file_exists($route['file'])) {
            require_once $route['file'];
        } else {
            $this->show404();
        }
    }
    
    private function extractParams($uri) {
        $parts = explode('/', $uri);
        $params = [];
        
        // Simple parameter extraction
        if (count($parts) >= 2) {
            $params['action'] = $parts[1] ?? '';
        }
        if (count($parts) >= 3) {
            $params['id'] = $parts[2] ?? '';
        }
        
        return $params;
    }
    
    private function checkRole($requiredRole) {
        require_once 'helpers/auth_helper.php';
        
        if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
            header('Location: /login');
            exit();
        }
        
        $userRole = getUserRole();
        
        if ($requiredRole === 'admin' && $userRole !== 'admin') {
            header('Location: /dashboard');
            exit();
        }
    }
    
    private function show404() {
        http_response_code(404);
        require_once 'pages/errors/404.php';
        exit();
    }
}
?>
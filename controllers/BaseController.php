<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

abstract class BaseController {
    protected $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }
    
    protected function checkSession() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/public/index.php?controller=auth&action=login');
        }
    }
    
    protected function redirect($url) {
        // No agregar BASE_URL si ya es una URL completa
        if (!preg_match('/^https?:\/\//', $url) && strpos($url, BASE_URL) !== 0) {
            // Si la URL no comienza con /, agregarla
            if (strpos($url, '/') !== 0) {
                $url = '/' . $url;
            }
            $url = BASE_URL . $url;
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    protected function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        $this->redirect($url);
    }
    
    protected function getFlashMessage() {
        $message = $_SESSION['flash_message'] ?? '';
        $type = $_SESSION['flash_type'] ?? 'info';
        
        if ($message) {
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }
        
        return ['message' => $message, 'type' => $type];
    }
    
    protected function setFlashMessage($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    protected function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return $this->userModel->getUserById($_SESSION['user_id']);
        }
        return null;
    }
    
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect(BASE_URL . '/public/index.php?controller=auth&action=login');
        }
    }
    
    public function debuguear($variable) {
        echo '<pre>';
        var_dump($variable);
        echo '</pre>';
        exit;
    }
}
?>
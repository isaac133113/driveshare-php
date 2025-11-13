<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

abstract class BaseController {
    protected $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }

    // -----------------------
    // SESSION & AUTH
    // -----------------------
    protected function checkSession() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login.php');
        }
    }

    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('login.php');
        }
    }

    protected function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return $this->userModel->getUserById($_SESSION['user_id']);
        }
        return null;
    }

    // -----------------------
    // REDIRECCIONES
    // -----------------------
    /**
     * Redirige a una URL específica
     */
    protected function redirect($url) {
        // Si la URL es absoluta (http://...) no concatena BASE_URL
        if (!preg_match('#^https?://#i', $url)) {
            $url = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        }
        header("Location: $url");
        exit();
    }

    /**
     * Redirige con un mensaje flash
     */
    protected function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;

        // Limpiar '?' al inicio si alguien lo pone
        $url = ltrim($url, '?');

        $this->redirect($url);
    }

    // -----------------------
    // MENSAJES FLASH
    // -----------------------
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

    // -----------------------
    // SANITIZACIÓN DE DATOS
    // -----------------------
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // -----------------------
    // JSON RESPONSE
    // -----------------------
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // -----------------------
    // DEBUG
    // -----------------------
    public function debuguear($variable) {
        echo '<pre>';
        var_dump($variable);
        echo '</pre>';
        exit;
    }
}
?>

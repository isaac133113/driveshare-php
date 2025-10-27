<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends BaseController {
    
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    public function login() {
        if ($this->isAuthenticated()) {
            $this->redirect('../../dashboard.php');
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            
            if (!empty($email) && !empty($password)) {
                $user = $this->userModel->authenticate($email, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_cognoms'] = $user['cognoms'];
                    $_SESSION['user_email'] = $user['correu'];
                    $_SESSION['login_time'] = time();
                    
                    // Log del inicio de sesión
                    $this->userModel->logUserActivity($user['id'], 'login', $_SERVER['REMOTE_ADDR']);
                    
                    $this->redirect('../../dashboard.php');
                } else {
                    $error = 'Correu electrònic o contrasenya incorrectes';
                    
                    // Log del intento fallido
                    $this->userModel->logFailedLogin($email, $_SERVER['REMOTE_ADDR']);
                }
            } else {
                $error = 'Si us plau, omple tots els camps';
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/horaris/login.php';
    }
  
       public function register() {
        if ($this->isAuthenticated()) {
            $this->redirect('../../dashboard.php');
        }
        
        $errors = [];
        $success = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $this->sanitizeInput($_POST['nom']),
                'cognoms' => $this->sanitizeInput($_POST['cognom']),
                'email' => $this->sanitizeInput($_POST['email']),
                'password' => $_POST['password']
            ];
            
            // Validaciones
            if (empty($data['nom'])) {
                $errors[] = 'El nom és obligatori.';
            }
            
            if (empty($data['cognoms'])) {
                $errors[] = 'Els cognoms són obligatoris.';
            }
            
            if (empty($data['email'])) {
                $errors[] = 'L\'email és obligatori.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'email no és vàlid.';
            } elseif ($this->userModel->emailExists($data['email'])) {
                $errors[] = 'Aquest email ja està registrat.';
            }
            
            if (empty($data['password'])) {
                $errors[] = 'La contrasenya és obligatòria.';
            } elseif (!$this->userModel->validatePassword($data['password'])) {
                $errors[] = 'La contrasenya ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.';
            }
            
            if (empty($errors)) {
                if ($this->userModel->createUser($data)) {
                    $success = true;
                } else {
                    $errors[] = 'Error al crear l\'usuari. Si us plau, torna-ho a intentar.';
                }
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/horaris/registre.php';
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log del cierre de sesión
            $this->userModel->logUserActivity($_SESSION['user_id'], 'logout', $_SERVER['REMOTE_ADDR']);
        }
        
        session_destroy();
        $this->redirect('login.php');
    }
    
    public function forgotPassword() {
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitizeInput($_POST['email']);
            
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($this->userModel->emailExists($email)) {
                    $token = $this->userModel->generateResetToken($email);
                    if ($token) {
                        // En una aplicación real, enviarías un email aquí
                        $message = 'S\'ha enviat un enllaç de recuperació al teu email.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error al generar el token de recuperació.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Aquest email no està registrat.';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Si us plau, introdueix un email vàlid.';
                $messageType = 'danger';
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/auth/forgot_password.php';
    }
    
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $message = '';
        $messageType = '';
        $validToken = false;
        
        if (!empty($token)) {
            $validToken = $this->userModel->validateResetToken($token);
        }
        
        if (!$validToken) {
            $message = 'Token de recuperació invàlid o caducat.';
            $messageType = 'danger';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (empty($password) || empty($confirmPassword)) {
                $message = 'Si us plau, omple tots els camps.';
                $messageType = 'danger';
            } elseif ($password !== $confirmPassword) {
                $message = 'Les contrasenyes no coincideixen.';
                $messageType = 'danger';
            } elseif (!$this->userModel->validatePassword($password)) {
                $message = 'La contrasenya ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.';
                $messageType = 'danger';
            } else {
                if ($this->userModel->resetPassword($token, $password)) {
                    $message = 'Contrasenya actualitzada correctament.';
                    $messageType = 'success';
                    $validToken = false; // Evitar mostrar el formulario
                } else {
                    $message = 'Error al actualitzar la contrasenya.';
                    $messageType = 'danger';
                }
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/auth/reset_password.php';
    }
    
    public function profile() {
        $this->requireAuth();
        
        $message = '';
        $messageType = '';
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $this->sanitizeInput($_POST['nom']),
                'cognoms' => $this->sanitizeInput($_POST['cognoms']),
                'email' => $this->sanitizeInput($_POST['email'])
            ];
            
            // Validaciones
            $errors = [];
            
            if (empty($data['nom'])) {
                $errors[] = 'El nom és obligatori.';
            }
            
            if (empty($data['cognoms'])) {
                $errors[] = 'Els cognoms són obligatoris.';
            }
            
            if (empty($data['email'])) {
                $errors[] = 'L\'email és obligatori.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'email no és vàlid.';
            } elseif ($data['email'] !== $user['correu'] && $this->userModel->emailExists($data['email'])) {
                $errors[] = 'Aquest email ja està en ús.';
            }
            
            if (empty($errors)) {
                if ($this->userModel->updateProfile($_SESSION['user_id'], $data)) {
                    $_SESSION['user_nom'] = $data['nom'];
                    $_SESSION['user_cognoms'] = $data['cognoms'];
                    $_SESSION['user_email'] = $data['email'];
                    
                    $message = 'Perfil actualitzat correctament.';
                    $messageType = 'success';
                    $user = $this->userModel->getById($_SESSION['user_id']); // Recargar datos
                } else {
                    $message = 'Error al actualitzar el perfil.';
                    $messageType = 'danger';
                }
            } else {
                $message = implode('<br>', $errors);
                $messageType = 'danger';
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/auth/profile.php';
    }
    
    public function changePassword() {
        $this->requireAuth();
        
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $message = 'Si us plau, omple tots els camps.';
                $messageType = 'danger';
            } elseif ($newPassword !== $confirmPassword) {
                $message = 'Les contrasenyes noves no coincideixen.';
                $messageType = 'danger';
            } elseif (!$this->userModel->validatePassword($newPassword)) {
                $message = 'La contrasenya nova ha de tenir mínim 8 caràcters, una majúscula, una minúscula, un número i un caràcter especial.';
                $messageType = 'danger';
            } elseif (!$this->userModel->verifyCurrentPassword($_SESSION['user_id'], $currentPassword)) {
                $message = 'La contrasenya actual és incorrecta.';
                $messageType = 'danger';
            } else {
                if ($this->userModel->updatePassword($_SESSION['user_id'], $newPassword)) {
                    $message = 'Contrasenya canviada correctament.';
                    $messageType = 'success';
                } else {
                    $message = 'Error al canviar la contrasenya.';
                    $messageType = 'danger';
                }
            }
        }
        
        // Cargar la vista
        include __DIR__ . '/../views/auth/change_password.php';
    }
}

// Manejo de rutas simplificado para uso directo
if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
    $controller = new AuthController();
    
    $action = $_GET['action'] ?? 'login';
    
    switch ($action) {
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'forgot-password':
            $controller->forgotPassword();
            break;
        case 'reset-password':
            $controller->resetPassword();
            break;
        case 'profile':
            $controller->profile();
            break;
        case 'change-password':
            $controller->changePassword();
            break;
        default:
            $controller->login();
    }
}
?>
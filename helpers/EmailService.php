<?php
/**
 * Servicio de Email usando PHPMailer
 * DriveShare - Sistema de Gestió d'Horaris
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mail;
    private $config;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        
        // Configuración - PARA OUTLOOK
        $this->config = [
            // Para Outlook/Hotmail:
            'smtp_host' => 'smtp.outlook.com',
            'smtp_port' => 587,
            'smtp_username' => 'isaacbonetolives@mollerussa.lasalle.cat', // ← TU EMAIL
            'smtp_password' => 'TU_CONTRASEÑA_OUTLOOK',    // ← TU CONTRASEÑA NORMAL DE OUTLOOK
            'from_email' => 'isaacbonetolives@mollerussa.lasalle.cat',    // ← TU EMAIL
            'from_name' => 'DriveShare - Sistema d\'Horaris',
            
            // Para desarrollo local (sin SMTP):
            'use_smtp' => true  // ← TRUE para usar Outlook y recibir emails reales
        ];
        
        if ($this->config['use_smtp']) {
            $this->configureSMTP();
        } else {
            $this->configureLocal();
        }
    }

    private function configureSMTP()
    {
        try {
            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['smtp_host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['smtp_username'];
            $this->mail->Password = $this->config['smtp_password'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $this->config['smtp_port'];
            
            // Para debugging (solo en desarrollo)
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            
            // Configuración del remitente
            $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Codificación
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Error configurando SMTP: " . $e->getMessage());
        }
    }
    
    private function configureLocal()
    {
        try {
            // Configuración para desarrollo local
            $this->mail->isMail(); // Usar función mail() de PHP
            $this->mail->setFrom('noreply@driveshare.local', $this->config['from_name']);
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Error configurando email local: " . $e->getMessage());
        }
    }

    /**
     * Enviar email de recuperación de contraseña
     */
    public function sendPasswordReset($toEmail, $toName, $resetToken)
    {
        try {
            // Destinatario
            $this->mail->addAddress($toEmail, $toName);
            
            // Asunto
            $this->mail->Subject = '🔐 Recuperació de Contrasenya - DriveShare';
            
            // Crear enlace de reset
            $resetLink = "http://localhost/PHP/reset_password.php?token=" . $resetToken;
            
            // Cuerpo del email
            $this->mail->Body = $this->getPasswordResetHTML($toName, $resetLink, $resetToken);
            
            // Enviar
            $result = $this->mail->send();
            
            // Limpiar destinatarios para próximo envío
            $this->mail->clearAddresses();
            
            return ['success' => true, 'message' => 'Email enviat correctament'];
            
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error enviant l\'email: ' . $e->getMessage()];
        }
    }

    /**
     * Template HTML para email de recuperación
     */
    private function getPasswordResetHTML($name, $resetLink, $token)
    {
        return "
        <!DOCTYPE html>
        <html lang='ca'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperació de Contrasenya</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚗 DriveShare</h1>
                    <h2>Recuperació de Contrasenya</h2>
                </div>
                
                <div class='content'>
                    <h3>Hola {$name},</h3>
                    <p>Has sol·licitat recuperar la teva contrasenya per al sistema DriveShare.</p>
                    
                    <p>Fes clic al següent botó per crear una nova contrasenya:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetLink}' class='btn'>🔐 Recuperar Contrasenya</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important:</strong>
                        <ul>
                            <li>Aquest enllaç només és vàlid durant <strong>1 hora</strong></li>
                            <li>Si no has sol·licitat aquest canvi, pots ignorar aquest email</li>
                            <li>Per seguretat, no comparteixis aquest enllaç amb ningú</li>
                        </ul>
                    </div>
                    
                    <p>Si el botó no funciona, copia i enganxa aquest enllaç al teu navegador:</p>
                    <p style='word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 5px;'>{$resetLink}</p>
                    
                    <p><strong>Codi de verificació:</strong> <code>{$token}</code></p>
                </div>
                
                <div class='footer'>
                    <p>Aquest email ha estat generat automàticament per DriveShare</p>
                    <p style='font-size: 12px; color: #666;'>Si tens problemes, contacta amb l'administrador del sistema</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Enviar email de bienvenida
     */
    public function sendWelcomeEmail($toEmail, $toName)
    {
        try {
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->Subject = '🎉 Benvingut/da a DriveShare!';
            
            $this->mail->Body = "
            <h2>Hola {$toName}!</h2>
            <p>Benvingut/da al sistema DriveShare!</p>
            <p>Ja pots començar a gestionar els teus horaris de transport.</p>
            <p>Accedeix al sistema: <a href='http://localhost/PHP/views/horaris/login.php'>Iniciar Sessió</a></p>
            ";
            
            $result = $this->mail->send();
            $this->mail->clearAddresses();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
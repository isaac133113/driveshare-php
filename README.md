# 🚗 DriveShare - Plataforma de Car Sharing

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-purple.svg)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen.svg)]()

**DriveShare** es una aplicación web moderna de car sharing y alquiler de vehículos desarrollada en PHP con arquitectura MVC. Permite a los usuarios buscar, reservar y gestionar el alquiler de vehículos de forma sencilla e intuitiva.

## 🌟 Características Principales

### 🔐 Sistema de Autenticación
- **Registro e inicio de sesión** seguro con hash de contraseñas
- **Recuperación de contraseña** vía email con tokens seguros
- **Gestión de perfil** de usuario con validaciones
- **Sistema de sesiones** con protección CSRF

### 🚙 Gestión de Vehículos
- **Catálogo completo** con 8+ tipos de vehículos (Sedan, SUV, Compacto, Eléctrico, etc.)
- **Filtros avanzados** por tipo, precio y disponibilidad
- **Vista detallada** de cada vehículo con especificaciones técnicas
- **Sistema de reservas** por horas o días
- **Cálculo automático** de precios y duraciones

### 🗺️ Mapa Interactivo
- **Geolocalización HTML5** para encontrar vehículos cercanos
- **Mapa interactivo** con Leaflet.js y OpenStreetMap
- **Filtrado en tiempo real** por distancia y tipo
- **Marcadores dinámicos** de vehículos, gasolineras y parkings
- **Reserva rápida** directamente desde el mapa

### 📧 Sistema de Comunicación
- **PHPMailer integrado** para notificaciones por email
- **Configuración SMTP** con soporte para Outlook/Hotmail
- **Confirmaciones automáticas** de reservas
- **Notificaciones de estado** de cuenta

### 📊 Dashboard Administrativo
- **Panel de control** moderno con métricas
- **Gestión de reservas** y historial
- **Configuración de perfil** y preferencias
- **Estadísticas de uso** y actividad

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.0+** - Lenguaje principal
- **MySQLi** - Base de datos con conexiones seguras
- **PHPMailer 6.8+** - Sistema de emails
- **Composer** - Gestión de dependencias

### Frontend
- **Bootstrap 5.3.2** - Framework CSS responsive
- **Bootstrap Icons** - Iconografía moderna
- **Leaflet.js** - Mapas interactivos
- **JavaScript ES6+** - Interactividad del cliente

### Arquitectura
- **Patrón MVC** - Separación clara de responsabilidades
- **POO** - Programación orientada a objetos
- **Autoloading** - Carga automática de clases
- **Routing** - Sistema de rutas limpio

## 📁 Estructura del Proyecto

```
driveshare-php/
├── 📁 config/                 # Configuración de la aplicación
│   ├── config.php             # Configuración general
│   └── Database.php           # Clase de conexión a BD
├── 📁 controllers/            # Controladores MVC
│   ├── AuthController.php     # Autenticación y usuarios
│   ├── VehicleController.php  # Gestión de vehículos
│   ├── MapController.php      # Funcionalidad del mapa
│   ├── DashboardController.php # Panel de control
│   └── BaseController.php     # Controlador base
├── 📁 models/                 # Modelos de datos
│   ├── UserModel.php          # Modelo de usuarios
│   └── HorariModel.php        # Modelo de horarios
├── 📁 views/                  # Vistas de la aplicación
│   ├── 📁 auth/               # Vistas de autenticación
│   ├── 📁 vehicles/           # Vistas de vehículos
│   ├── 📁 map/                # Vista del mapa
│   └── 📁 horaris/            # Dashboard y horarios
├── 📁 helpers/                # Clases auxiliares
│   ├── DatabaseHelper.php     # Utilidades de BD
│   └── EmailService.php       # Servicio de emails
├── 📁 vendor/                 # Dependencias de Composer
├── setup_database.php         # Script de configuración inicial
├── composer.json              # Dependencias del proyecto
└── README.md                  # Este archivo
```

## 🚀 Instalación y Configuración

### Prerrequisitos
- **PHP 8.0+** con extensiones MySQLi y OpenSSL
- **MySQL/MariaDB 5.7+**
- **Composer** para gestión de dependencias
- **Servidor web** (Apache/Nginx) o PHP built-in server

### 1. Clonar el Repositorio
```bash
git clone https://github.com/isaac133113/driveshare-php.git
cd driveshare-php
```

### 2. Instalar Dependencias
```bash
composer install
```

### 3. Configurar Base de Datos
1. Crear una base de datos MySQL:
```sql
CREATE DATABASE aplicaciocompra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Configurar conexión en `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aplicaciocompra');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Configurar Email (Opcional)
Editar `helpers/EmailService.php` con tus credenciales SMTP:
```php
$mail->Host = 'smtp-mail.outlook.com';
$mail->Username = 'tu_email@outlook.com';
$mail->Password = 'tu_contraseña';
```

### 5. Ejecutar Setup Inicial
Acceder a `http://localhost/setup_database.php` para crear las tablas automáticamente.

### 6. Iniciar la Aplicación
```bash
# Usando servidor PHP built-in
php -S localhost:8080

# O configurar virtual host en Apache/Nginx
```

## 👤 Credenciales de Prueba

El setup automático crea un usuario de prueba:
- **Email:** `test@driveshare.com`
- **Contraseña:** `123456`
- **Saldo inicial:** €100.00

## 🎯 Funcionalidades Implementadas

### ✅ Completado
- [x] **Sistema de autenticación** completo
- [x] **Gestión de usuarios** y perfiles
- [x] **Catálogo de vehículos** con filtros
- [x] **Sistema de reservas** avanzado
- [x] **Mapa interactivo** con geolocalización
- [x] **Dashboard administrativo**
- [x] **Sistema de emails** con PHPMailer
- [x] **Diseño responsive** con Bootstrap 5
- [x] **Validación de formularios**
- [x] **Gestión de sesiones** segura

### 🚧 En Desarrollo
- [ ] **Sistema de pagos** con Stripe/PayPal
- [ ] **Notificaciones push** en tiempo real
- [ ] **API REST** para aplicaciones móviles
- [ ] **Sistema de valoraciones** y comentarios
- [ ] **Chat en tiempo real** entre usuarios

## 🔒 Seguridad

### Medidas Implementadas
- ✅ **Hash de contraseñas** con `password_hash()`
- ✅ **Tokens CSRF** en formularios críticos
- ✅ **Validación de entrada** y sanitización
- ✅ **Prepared statements** para prevenir SQL injection
- ✅ **Sesiones seguras** con regeneración de ID
- ✅ **Validación de email** con filtros PHP
- ✅ **Control de acceso** basado en roles

## 🔄 Changelog

### v1.0.0 (Noviembre 2025)
- ✨ **Lanzamiento inicial** de DriveShare
- 🚗 **Catálogo completo** de vehículos
- 🗺️ **Mapa interactivo** con geolocalización
- 🔐 **Sistema de autenticación** seguro
- 📧 **Integración con PHPMailer**
- 🎨 **Diseño responsive** con Bootstrap 5

---

<div align="center">
  <h3>⭐ Si te gusta este proyecto, ¡dale una estrella! ⭐</h3>
  <p>Desarrollado con ❤️ por Isaac Bonet Olives</p>
</div>

<?php
session_start();
require_once 'conexion.php';

$error = '';

// Si ya está logueado, redirigir al admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            // Verificar credenciales usando SHA256 (como lo guardamos en BD)
            $stmt = $conn->prepare("SELECT id FROM administradores WHERE email = :email AND password_hash = SHA2(:password, 256)");
            $stmt->execute([
                ':email' => $email,
                ':password' => $password
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_email'] = $email;
                header("Location: admin.php");
                exit();
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        } catch(Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor ingresa correo y contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-bg);
            padding: 2rem;
        }
        .login-box {
            background-color: var(--color-surface);
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(194, 155, 99, 0.2);
        }
        .login-box h2 {
            font-family: 'Playfair Display', serif;
            color: var(--color-gold);
            text-align: center;
            margin-bottom: 2rem;
        }
        .error-msg {
            color: #ff4d4d;
            background: rgba(255, 77, 77, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Acceso Privado</h2>
            <?php if($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php" class="contacto__form">
                <div class="form__group">
                    <label class="form__label">Correo del Administrador</label>
                    <input type="email" name="email" class="form__input" required placeholder="yefferson.lozada.velez@ngbca.com">
                </div>
                <div class="form__group">
                    <label class="form__label">Contraseña</label>
                    <input type="password" name="password" class="form__input" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn--gold btn--full">Ingresar al Historial</button>
            </form>
        </div>
    </div>
</body>
</html>

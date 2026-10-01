<?php
// Configuración de la conexión a la base de datos (XAMPP por defecto)
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP por defecto no tiene contraseña
$dbname = "cocinas_ng";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}
?>

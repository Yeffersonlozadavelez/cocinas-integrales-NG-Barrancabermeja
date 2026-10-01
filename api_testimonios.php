<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener testimonios aprobados (incluye imagen)
    $stmt = $conn->query("SELECT nombre, ciudad, calificacion, mensaje, imagen FROM testimonios WHERE estado = 'aprobado' ORDER BY fecha_registro DESC");
    $testimonios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $testimonios]);
    exit;

} elseif ($method === 'POST') {
    // El formulario ahora usa multipart/form-data para subir imagen
    $nombre      = trim($_POST['nombre'] ?? '');
    $ciudad      = trim($_POST['ciudad'] ?? '');
    $calificacion = intval($_POST['calificacion'] ?? 5);
    $mensaje     = trim($_POST['mensaje'] ?? '');

    if (empty($nombre) || empty($ciudad) || empty($mensaje)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if ($calificacion < 1 || $calificacion > 5) $calificacion = 5;

    // Manejo de imagen
    $imagenPath = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Solo se permiten imágenes JPG, PNG o WebP.']);
            exit;
        }

        if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'La imagen no puede superar 5 MB.']);
            exit;
        }

        $ext      = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = 'testimonio_' . uniqid() . '.' . strtolower($ext);
        $destDir  = __DIR__ . '/img/testimonios/';

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destDir . $filename)) {
            $imagenPath = 'img/testimonios/' . $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO testimonios (nombre, ciudad, calificacion, mensaje, imagen, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
    $stmt->execute([$nombre, $ciudad, $calificacion, $mensaje, $imagenPath]);

    echo json_encode(['status' => 'success', 'message' => '¡Gracias por tu valoración! Será publicada una vez sea revisada por nuestro equipo.']);
    exit;

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no soportado']);
}
?>

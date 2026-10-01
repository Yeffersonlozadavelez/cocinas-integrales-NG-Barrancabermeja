<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// Obtener los datos JSON enviados desde el frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nombre']) || !isset($data['telefono'])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit();
}

$nombre = $data['nombre'];
$telefono = $data['telefono'];
$email = $data['email'];
$servicio = $data['servicio'];
$mensaje = $data['mensaje'];

try {
    // 1. Guardar en la Base de Datos
    $stmt = $conn->prepare("INSERT INTO cotizaciones (nombre, telefono, email, servicio, mensaje) VALUES (:nombre, :telefono, :email, :servicio, :mensaje)");
    $stmt->execute([
        ':nombre' => $nombre,
        ':telefono' => $telefono,
        ':email' => $email,
        ':servicio' => $servicio,
        ':mensaje' => $mensaje
    ]);
    
    // 2. Enviar Mensaje Automático a WhatsApp en segundo plano
    // Para enviar mensajes en segundo plano necesitas usar la API de un proveedor (Ej: Meta Cloud API, UltraMsg, Twilio, etc.)
    // Aquí dejamos el código listo usando cURL (que es el estándar en PHP)
    
    /* === INICIO DE INTEGRACIÓN WHATSAPP (EJEMPLO ULTRAMSG) === */
    $token_proveedor = "TU_TOKEN_AQUI"; // Debes obtener un token de un proveedor
    $instancia = "TU_INSTANCIA";
    $url_api = "https://api.ultramsg.com/$instancia/messages/chat";
    
    // Mensaje para el cliente confirmando recepción
    $mensaje_cliente = "¡Hola $nombre! Hemos recibido tu solicitud para: $servicio. Te contactaremos pronto. Atte: Cocinas Integrales NG.";
    
    // Aquí haríamos la petición HTTP (está comentado para que no genere error hasta que pongas tus credenciales)
    /*
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url_api,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => http_build_query([
          "token" => $token_proveedor,
          "to" => $telefono, // Al celular del cliente
          "body" => $mensaje_cliente
      ]),
      CURLOPT_HTTPHEADER => array(
        "content-type: application/x-www-form-urlencoded"
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    */
    /* === FIN DE INTEGRACIÓN WHATSAPP === */

    // Respuesta de éxito al Frontend
    echo json_encode([
        "status" => "success", 
        "message" => "Cotización guardada exitosamente. Nos comunicaremos pronto."
    ]);

} catch(Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al guardar: " . $e->getMessage()]);
}
?>

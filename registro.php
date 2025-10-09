<?php
// registro.php - PROCESAMIENTO DE REGISTRO DE USUARIO

// Incluir la conexión
include 'db_config.php'; 

// Definir la respuesta por defecto
$response = ['success' => false, 'message' => 'Método de solicitud no permitido.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar los datos del POST (asumiendo que vienen de un formulario)
    $name = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($name) || empty($email) || empty($password)) {
        $response = ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Formato de correo inválido.'];
    } else {
        // Hashear la contraseña (¡CRÍTICO para la seguridad!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Usar prepared statements
        $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Registro exitoso. Podemos crear una sesión o un token (simulación)
                $user_id = $stmt->insert_id;
                $response = ['success' => true, 'message' => 'Registro exitoso.', 'user_id' => $user_id];
            } else {
                $response = ['success' => false, 'message' => 'Error al registrar: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Error al preparar la consulta.'];
        }
    }
}

// Configurar cabeceras y enviar la respuesta en JSON
header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>
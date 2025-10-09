<?php
// process_order.php - PROCESAMIENTO DE ORDEN Y SIMULACIÓN DE SOBRECARGA

// 1. Incluir la conexión a la base de datos
include 'db_config.php'; 

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty(file_get_contents("php://input"))) {
    
    // --- NUEVA LÓGICA DE SIMULACIÓN DE SOBRECARGA ---
    // A. Contar cuántos usuarios están registrados.
    // Usamos esta cuenta como proxy para simular que la cola virtual está llena.
    $user_count_sql = "SELECT COUNT(*) FROM users";
    $result = $conn->query($user_count_sql);
    
    if ($result) {
        $user_row = $result->fetch_array();
        $current_user_count = (int) $user_row[0];
    } else {
        // En caso de error en la consulta (ej. tabla users no existe), no se cae el sistema
        $current_user_count = 0; 
    }

    $QUEUE_LIMIT = 100; // Límite de "usuarios en cola" que tu sistema puede manejar.
    
    // B. Si el número de usuarios supera el límite (100+), tiramos el error 503.
    if ($current_user_count > $QUEUE_LIMIT) {
        http_response_code(503); // Service Unavailable (código ideal para sobrecarga)
        echo json_encode([
            'success' => false, 
            'message' => 'Servidor saturado. Más de ' . $QUEUE_LIMIT . ' usuarios registrados, la cola está llena.',
            'queue_count' => $current_user_count // Enviamos el conteo real
        ]);
        $conn->close();
        exit; // Detiene toda la ejecución.
    }
    // --- FIN DE LÓGICA DE SIMULACIÓN DE SOBRECARGA ---


    // 2. Si NO hay sobrecarga, procedemos con el procesamiento normal de la orden.
    
    // Recibir y decodificar los datos JSON
    $json_data = file_get_contents("php://input");
    $order_data = json_decode($json_data, true);

    // Validación básica
    if (!isset($order_data['event']['id']) || !isset($order_data['seats']) || !isset($order_data['totalCost'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos o malformados.']);
        $conn->close();
        exit;
    }

    // 3. Extraer y limpiar los datos (Usando ID de usuario fijo para simulación)
    $user_id = 12345; 
    $event_id = $conn->real_escape_string($order_data['event']['id']);
    $event_name = $conn->real_escape_string($order_data['event']['artist']);
    $total_cost = (float) $order_data['totalCost'];
    $seats_json = json_encode($order_data['seats']);

    // 4. Preparar la consulta SQL
    $sql = "INSERT INTO orders (user_id, event_id, event_name, seats_list, total_cost, order_status) VALUES (?, ?, ?, ?, ?, 'EN_COLA')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isssd", $user_id, $event_id, $event_name, $seats_json, $total_cost);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Orden procesada y guardada en la base de datos.',
                'order_id' => $order_id
            ]);
        } else {
            http_response_code(500); 
            echo json_encode(['success' => false, 'message' => 'Error al guardar la orden: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno al preparar la consulta.']);
    }
    
    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
}
?>
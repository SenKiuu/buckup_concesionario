<?php
session_start();
include("includes/conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay usuario logueado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$carrito = $data['carrito'] ?? [];
$usuario_id = $_SESSION['usuario']['id'];

if (empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    foreach ($carrito as $item) {
        $coche_id = intval($item['id']);
        $cantidad = intval($item['cantidad']);
        
        // Verificar que el coche existe
        $stmt = $conn->prepare("SELECT id FROM coches WHERE id = ?");
        $stmt->bind_param("i", $coche_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("El coche con ID $coche_id no existe");
        }
        
        $stmt->close();
        
        // Insertar cada unidad en la compra
        for ($i = 0; $i < $cantidad; $i++) {
            $stmt = $conn->prepare("INSERT INTO compras (usuario_id, coche_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $usuario_id, $coche_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Compra realizada con éxito']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()]);
}
?>
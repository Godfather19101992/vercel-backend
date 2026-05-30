<?php
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($action === 'get_devices') {
    try {
        $stmt = $pdo->query("SELECT *, (last_seen > DATE_SUB(NOW(), INTERVAL 1 MINUTE)) as is_online FROM devices");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($action === 'send_command') {
    $input = json_decode(file_get_contents('php://input'), true);
    $device_id = $input['device_id'] ?? '';
    $type = $input['type'] ?? '';
    $value = $input['value'] ?? '';

    if ($device_id) {
        try {
            // Check if command exists
            $stmt = $pdo->prepare("SELECT * FROM commands WHERE device_id = ?");
            $stmt->execute([$device_id]);
            if (!$stmt->fetch()) {
                $pdo->prepare("INSERT INTO commands (device_id) VALUES (?)")->execute([$device_id]);
            }
            
            $stmt = $pdo->prepare("UPDATE commands SET $type = ? WHERE device_id = ?");
            $stmt->execute([$value, $device_id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }
}
?>

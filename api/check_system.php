<?php
require_once 'db.php';

echo "<h1>GeoTrack Pro: System Health Check</h1>";

// 1. Database Connection
try {
    $pdo->query("SELECT 1");
    echo "✅ Database Connection: SUCCESS<br>";
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED (" . $e->getMessage() . ")<br>";
}

// 2. Schema Verification
$tables = ['devices', 'commands', 'history', 'users', 'file_responses'];
foreach ($tables as $table) {
    $check = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($check->rowCount() > 0) {
        echo "✅ Table '$table': EXISTS<br>";
    } else {
        echo "❌ Table '$table': MISSING (Please import database.sql)<br>";
    }
}

// 3. File System Permissions
$dirs = ['uploads', 'downloads', 'Updateapk'];
foreach ($dirs as $dir) {
    if (is_writable($dir)) {
        echo "✅ Directory '$dir': WRITABLE<br>";
    } else {
        echo "❌ Directory '$dir': NOT WRITABLE (Check permissions)<br>";
    }
}

// 4. Version Integrity
if (file_exists('Updateapk/version.txt')) {
    $v = trim(file_get_contents('Updateapk/version.txt'));
    echo "✅ version.txt: FOUND (v$v)<br>";
} else {
    echo "❌ version.txt: NOT FOUND<br>";
}

// 5. Recent Activity (Debug Log)
echo "<h3>Recent Signal Traffic (debug.log)</h3>";
if (file_exists('debug.log')) {
    $log = file('debug.log');
    $last = array_slice($log, -20);
    echo "<pre style='background:#161b22; color:#3fb950; padding:10px; border-radius:5px; font-size:10px;'>";
    foreach ($last as $line) echo htmlspecialchars($line);
    echo "</pre>";
} else {
    echo "<i>No activity logged yet.</i>";
}

echo "<hr><p>If all items are green, your server is ready. If not, please address the errors above.</p>";
?>

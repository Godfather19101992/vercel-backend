<?php
// GeoTrack Pro APK Download Controller
$version = $_GET['v'] ?? '';
if (empty($version)) {
    // Try multiple path resolutions
    $v_paths = [
        __DIR__ . "/Updateapk/version.txt",
        $_SERVER['DOCUMENT_ROOT'] . "/Updateapk/version.txt",
        "Updateapk/version.txt"
    ];
    foreach ($v_paths as $p) {
        if (file_exists($p)) {
            $version = trim(file_get_contents($p));
            break;
        }
    }
}

// v9.8.5 FIX: Sanitize version string (Keep only numbers, dots, and hyphens)
$version = preg_replace('/[^0-9a-zA-Z\.\-]/', '', $version);

if (empty($version)) {
    die("Error: Invalid or missing version metadata.");
}

$file_name = "ServiceApp-v$version.apk";
$file_path = __DIR__ . "/Updateapk/" . $file_name;

if (file_exists($file_path)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} else {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The requested APK version ($version) was not found on this server.<br>";
    echo "Path checked: " . $file_path;
}
?>

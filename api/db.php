<?php
// GeoTrack Pro Database Configuration
// Updated to use Remote SQL from freesqldatabase.com

$db_host = 'sql10.freesqldatabase.com';
$db_name = 'sql10828709';
$db_user = 'sql10828709';
$db_pass = '7FTxR8DKDN';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

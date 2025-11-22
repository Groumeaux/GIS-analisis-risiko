<?php
// Configuration file for GIS Minahasa app

// Database Configuration (Adjust these if your XAMPP settings differ)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sig_minahasa');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty

// Data file paths
define('DATA_DIR', __DIR__ . '/data/');
define('BANJIR_FILE', DATA_DIR . 'banjir.json');
define('LONGSOR_FILE', DATA_DIR . 'longsor.json');
define('SEKOLAH_FILE', DATA_DIR . 'sekolah.json');
define('RS_FILE', DATA_DIR . 'rs.json');

// Fallback Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'sebas');

session_start();

// --- NEW: Database Connection Function ---
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If DB fails, return null or handle gracefully
        return null;
    }
}
// -----------------------------------------

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loadJsonData($filePath) {
    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

function saveJsonData($filePath, $data) {
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generateId() {
    return 'id_' . time() . '_' . rand(1000, 9999);
}
?>
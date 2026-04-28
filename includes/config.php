<?php
session_start();

$host = 'localhost';
$dbname = 'perpustakaan_digital';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: /perpustakaan-digital/admin/login.php');
        exit;
    }
}

function checkAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /perpustakaan-digital/index.php');
        exit;
    }
}
function getBookCover($cover_image, $title) {
    if ($cover_image && file_exists($cover_image)) {
        return $cover_image;
    } else {
        $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe'];
        $color = $colors[crc32($title) % count($colors)];
        
        return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='300' viewBox='0 0 200 300'>
            <rect width='200' height='300' fill='$color'/>
            <text x='100' y='150' font-family='Arial' font-size='14' fill='white' text-anchor='middle' dominant-baseline='middle'>
                " . htmlspecialchars(substr($title, 0, 20)) . "
            </text>
        </svg>";
    }
}
?>
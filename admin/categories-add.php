<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        header('Location: categories.php?success=Kategori berhasil ditambahkan');
        exit;
    } catch (PDOException $e) {
        header('Location: categories.php?error=Error: ' . urlencode($e->getMessage()));
        exit;
    }
}

// If not POST, redirect back
header('Location: categories.php');
exit;
?>
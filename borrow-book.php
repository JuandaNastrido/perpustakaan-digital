<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

$book_id = $_GET['book_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND available_copies > 0");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: books.php?error=Buku tidak tersedia atau tidak ditemukan');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE member_id = ? AND book_id = ? AND status = 'borrowed'");
$stmt->execute([$member['id'], $book_id]);
if ($stmt->fetchColumn() > 0) {
    header('Location: books.php?error=Anda sudah meminjam buku ini');
    exit;
}

try {
    $pdo->beginTransaction();
    
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+7 days')); 
    
    $stmt = $pdo->prepare("INSERT INTO borrowings (member_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$member['id'], $book_id, $borrow_date, $due_date]);
    
    $stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
    $stmt->execute([$book_id]);
    
    $pdo->commit();
    
    header('Location: dashboard.php?success=Buku berhasil dipinjam. Jatuh tempo: ' . date('d M Y', strtotime($due_date)));
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: books.php?error=Gagal meminjam buku: ' . $e->getMessage());
    exit;
}
?>
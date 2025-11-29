<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT br.*, b.title, b.author, b.isbn
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.member_id = ?
    ORDER BY br.borrow_date DESC
");
$stmt->execute([$member['id']]);
$borrowings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> Perpustakaan Digital
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="books.php">
                    <i class="fas fa-book"></i> Buku
                </a>
                <a class="nav-link active" href="borrowing-history.php">
                    <i class="fas fa-history"></i> Riwayat
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout (<?php echo $_SESSION['username']; ?>)
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">
            <i class="fas fa-history"></i> Riwayat Peminjaman
        </h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <?php if (count($borrowings) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                    <th>Denda</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowings as $borrow): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($borrow['title']); ?></strong><br>
                                        <small class="text-muted">
                                            Oleh: <?php echo htmlspecialchars($borrow['author']); ?>
                                            <?php if ($borrow['isbn']): ?>
                                                • ISBN: <?php echo $borrow['isbn']; ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($borrow['borrow_date'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($borrow['due_date'])); ?></td>
                                    <td>
                                        <?php if ($borrow['return_date']): ?>
                                            <?php echo date('d M Y', strtotime($borrow['return_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = [
                                            'borrowed' => ['warning', 'Dipinjam'],
                                            'returned' => ['success', 'Dikembalikan'],
                                            'overdue' => ['danger', 'Terlambat']
                                        ];
                                        $status = $borrow['status'];
                                        ?>
                                        <span class="badge bg-<?php echo $status_badge[$status][0]; ?>">
                                            <?php echo $status_badge[$status][1]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        Rp <?php echo number_format($borrow['fine_amount'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-4x text-muted mb-3"></i>
                        <h4>Belum ada riwayat peminjaman</h4>
                        <p class="text-muted">Mulai jelajahi koleksi buku kami dan lakukan peminjaman pertama Anda.</p>
                        <a href="books.php" class="btn btn-primary">
                            <i class="fas fa-book"></i> Jelajahi Buku
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2025 Perpustakaan Digital. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
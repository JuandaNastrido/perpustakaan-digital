<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.* 
    FROM members m 
    WHERE m.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

$current_borrowings = $pdo->prepare("
    SELECT br.*, b.title, b.author
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.member_id = ? AND br.status = 'borrowed'
    ORDER BY br.due_date ASC
");
$current_borrowings->execute([$member['id']]);
$borrowings = $current_borrowings->fetchAll();

$history = $pdo->prepare("
    SELECT br.*, b.title, b.author
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.member_id = ? AND br.status = 'returned'
    ORDER BY br.return_date DESC
    LIMIT 5
");
$history->execute([$member['id']]);
$borrowing_history = $history->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
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
                <a class="nav-link" href="borrowing-history.php">
                    <i class="fas fa-history"></i> Riwayat
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout (<?php echo $_SESSION['username']; ?>)
                </a>
            </div>
        </div>
    </nav>

    <section class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Halo, <?php echo htmlspecialchars($member['full_name']); ?>! 👋</h1>
                    <p class="lead">Selamat datang di dashboard perpustakaan digital Anda</p>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light text-dark">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-7 border-end">
                                    <h6 class="border-bottom pb-2">Aksi Cepat</h6>
                                    <div class="d-grid gap-1">
                                        <a href="books.php" class="btn btn-outline-primary btn-sm mb-1">
                                            <i class="fas fa-search"></i> Cari Buku
                                        </a>
                                        <?php if (count($borrowings) > 0): ?>
                                            <a href="borrowing-history.php" class="btn btn-outline-warning btn-sm mb-1">
                                                <i class="fas fa-clock"></i> Tenggat Waktu
                                            </a>
                                        <?php endif; ?>
                                        <a href="borrowing-history.php" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-history"></i> Riwayat
                                        </a>
                                    </div>

                                    <?php 
                                    $upcoming_due = [];
                                    foreach ($borrowings as $borrow) {
                                        $days_left = floor((strtotime($borrow['due_date']) - time()) / (60 * 60 * 24));
                                        if ($days_left <= 3 && $days_left >= 0) {
                                            $upcoming_due[] = $borrow;
                                        }
                                    }
                                    ?>
                                    <?php if (count($upcoming_due) > 0): ?>
                                        <div class="alert alert-warning p-1 mt-2 mb-0">
                                            <small>
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong><?php echo count($upcoming_due); ?> buku</strong> hampir jatuh tempo!
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-5">
                                    <h6>Status Anggota</h6>
                                    <span class="badge bg-success">Aktif</span>
                                    <p class="mb-0 mt-2 small">
                                        <i class="fas fa-calendar"></i> 
                                        Bergabung <?php echo date('d M Y', strtotime($member['membership_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card stat-card border-0 shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                            <h3><?php echo count($borrowings); ?></h3>
                            <p class="text-muted">Sedang Dipinjam</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card border-0 shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h3>
                                <?php 
                                $overdue = 0;
                                foreach ($borrowings as $borrow) {
                                    if (strtotime($borrow['due_date']) < time()) {
                                        $overdue++;
                                    }
                                }
                                echo $overdue;
                                ?>
                            </h3>
                            <p class="text-muted">Terlambat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card border-0 shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h3><?php echo count($borrowing_history); ?></h3>
                            <p class="text-muted">Selesai Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="mb-4">Buku Sedang Dipinjam</h3>
            
            <?php if (count($borrowings) > 0): ?>
                <div class="row">
                    <?php foreach ($borrowings as $borrow): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($borrow['title']); ?></h5>
                                <p class="card-text">Oleh: <?php echo htmlspecialchars($borrow['author']); ?></p>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">
                                        Dipinjam: <?php echo date('d M Y', strtotime($borrow['borrow_date'])); ?>
                                    </small>
                                    <small class="<?php echo strtotime($borrow['due_date']) < time() ? 'text-danger' : 'text-warning'; ?>">
                                        Jatuh Tempo: <?php echo date('d M Y', strtotime($borrow['due_date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Anda tidak memiliki buku yang sedang dipinjam.
                    <a href="books.php" class="alert-link">Jelajahi katalog buku</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h3 class="mb-4">Riwayat Peminjaman Terbaru</h3>
            
            <?php if (count($borrowing_history) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrowing_history as $history): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($history['title']); ?></strong><br>
                                    <small>Oleh: <?php echo htmlspecialchars($history['author']); ?></small>
                                </td>
                                <td><?php echo date('d M Y', strtotime($history['borrow_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($history['return_date'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Dikembalikan</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="borrowing-history.php" class="btn btn-outline-primary">Lihat Semua Riwayat</a>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada riwayat peminjaman.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 Perpustakaan Digital. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
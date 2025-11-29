<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['return'])) {
    $id = $_GET['return'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE borrowings SET return_date = CURDATE(), status = 'returned' WHERE id = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("SELECT book_id FROM borrowings WHERE id = ?");
        $stmt->execute([$id]);
        $borrowing = $stmt->fetch();
        
        if ($borrowing) {
            $stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
            $stmt->execute([$borrowing['book_id']]);
        }
        
        $pdo->commit();
        
        header('Location: borrowings.php?success=Buku berhasil dikembalikan');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: borrowings.php?error=Error: ' . urlencode($e->getMessage()));
        exit;
    }
}

$stmt = $pdo->query("
    SELECT br.*, 
           m.full_name as member_name,
           b.title as book_title,
           b.author as book_author
    FROM borrowings br
    JOIN members m ON br.member_id = m.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrow_date DESC
");
$borrowings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manajemen Peminjaman - Perpustakaan Digital</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Manajemen Peminjaman</h1>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Peminjaman</h6>
                            <a href="borrowings-add.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Peminjaman
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Member</th>
                                            <th>Buku</th>
                                            <th>Tanggal Pinjam</th>
                                            <th>Tanggal Jatuh Tempo</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Status</th>
                                            <th>Denda</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowings as $borrowing): ?>
                                        <tr>
                                            <td><?php echo $borrowing['id']; ?></td>
                                            <td><?php echo htmlspecialchars($borrowing['member_name']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($borrowing['book_title']); ?></strong><br>
                                                <small>Oleh: <?php echo htmlspecialchars($borrowing['book_author']); ?></small>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($borrowing['borrow_date'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($borrowing['due_date'])); ?></td>
                                            <td>
                                                <?php if ($borrowing['return_date']): ?>
                                                    <?php echo date('d/m/Y', strtotime($borrowing['return_date'])); ?>
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
                                                $status = $borrowing['status'];
                                                ?>
                                                <span class="badge badge-<?php echo $status_badge[$status][0]; ?>">
                                                    <?php echo $status_badge[$status][1]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                Rp <?php echo number_format($borrowing['fine_amount'], 0, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <?php if ($borrowing['status'] == 'borrowed'): ?>
                                                    <a href="borrowings.php?return=<?php echo $borrowing['id']; ?>" 
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Konfirmasi pengembalian buku?')">
                                                        <i class="fas fa-undo"></i> Kembalikan
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
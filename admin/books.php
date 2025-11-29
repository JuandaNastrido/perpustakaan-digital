<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $check_borrowings = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status = 'borrowed'");
        $check_borrowings->execute([$id]);
        $has_active_borrowings = $check_borrowings->fetchColumn();
        
        if ($has_active_borrowings > 0) {
            header('Location: books.php?error=Buku tidak bisa dihapus karena masih sedang dipinjam');
            exit;
        }
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        
        $pdo->prepare("DELETE FROM reviews WHERE book_id = ?")->execute([$id]);
        
        $pdo->prepare("DELETE FROM borrowings WHERE book_id = ?")->execute([$id]);
        
        if ($book && $book['cover_image'] && file_exists('../' . $book['cover_image'])) {
            unlink('../' . $book['cover_image']);
        }
        $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
        
        $pdo->commit();
        header('Location: books.php?success=Buku dan semua riwayatnya berhasil dihapus permanent');
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: books.php?error=Error: ' . urlencode($e->getMessage()));
        exit;
    }
}

$stmt = $pdo->query("
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    ORDER BY b.created_at DESC
");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manajemen Buku - Perpustakaan Digital</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Manajemen Buku</h1>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Buku</h6>
                            <a href="books-add.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Buku
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Cover</th>
                                            <th>ID</th>
                                            <th>Judul</th>
                                            <th>Penulis</th>
                                            <th>Kategori</th>
                                            <th>Tahun</th>
                                            <th>Licenses</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td>
                                                <?php if ($book['cover_image'] && file_exists('../' . $book['cover_image'])): ?>
                                                    <img src="../<?php echo $book['cover_image']; ?>" 
                                                         alt="Cover" 
                                                         style="width: 50px; height: 70px; object-fit: cover; border-radius: 3px;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 70px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 3px;">
                                                        <i class="fas fa-book text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $book['id']; ?></td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['category_name'] ?? '-'); ?></td>
                                            <td><?php echo $book['publication_year']; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="books-edit.php?id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="books.php?delete=<?php echo $book['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Hapus buku \'<?php echo htmlspecialchars(addslashes($book['title'])); ?>\'?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

$members = $pdo->query("SELECT * FROM members WHERE status = 'active' ORDER BY full_name")->fetchAll();
$books = $pdo->query("SELECT * FROM books WHERE available_copies > 0 ORDER BY title")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = $_POST['borrow_date'];
    $due_date = $_POST['due_date'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO borrowings (member_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$member_id, $book_id, $borrow_date, $due_date]);
        
        $stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $pdo->commit();
        
        header('Location: borrowings.php?success=Peminjaman berhasil ditambahkan');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tambah Peminjaman - Perpustakaan Digital</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Tambah Peminjaman</h1>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Peminjaman</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="member_id">Member *</label>
                                            <select class="form-control" id="member_id" name="member_id" required>
                                                <option value="">- Pilih Member -</option>
                                                <?php foreach ($members as $member): ?>
                                                    <option value="<?php echo $member['id']; ?>" 
                                                        <?php echo (isset($_POST['member_id']) && $_POST['member_id'] == $member['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="borrow_date">Tanggal Pinjam *</label>
                                            <input type="date" class="form-control" id="borrow_date" name="borrow_date" required
                                                   value="<?php echo isset($_POST['borrow_date']) ? $_POST['borrow_date'] : date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="book_id">Buku *</label>
                                            <select class="form-control" id="book_id" name="book_id" required>
                                                <option value="">- Pilih Buku -</option>
                                                <?php foreach ($books as $book): ?>
                                                    <option value="<?php echo $book['id']; ?>" 
                                                        <?php echo (isset($_POST['book_id']) && $_POST['book_id'] == $book['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($book['title']); ?> 
                                                        (Tersedia: <?php echo $book['available_copies']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="due_date">Tanggal Jatuh Tempo *</label>
                                            <input type="date" class="form-control" id="due_date" name="due_date" required
                                                   value="<?php echo isset($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d', strtotime('+14 days')); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Peminjaman
                                    </button>
                                    <a href="borrowings.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
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
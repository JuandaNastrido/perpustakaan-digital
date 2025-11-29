<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']); 
    
    if ($id <= 0) {
        $_SESSION['error'] = "ID member tidak valid!";
        header('Location: members.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT status FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    
    if ($member) {
        $new_status = $member['status'] == 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE members SET status = ? WHERE id = ?")->execute([$new_status, $id]);
        
        $status_text = $new_status == 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $_SESSION['success'] = "Member berhasil " . $status_text;
    } else {
        $_SESSION['error'] = "Member tidak ditemukan!";
    }
    
    header('Location: members.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); 
    
    if ($id <= 0) {
        $_SESSION['error'] = "ID member tidak valid!";
        header('Location: members.php');
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE member_id = ? AND return_date IS NULL");
        $stmt->execute([$id]);
        $active_borrowings = $stmt->fetchColumn();
        
        if ($active_borrowings > 0) {
            $_SESSION['error'] = "Tidak dapat menghapus member yang masih memiliki peminjaman aktif!";
            $pdo->rollBack();
            header('Location: members.php');
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM borrowings WHERE member_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("SELECT user_id FROM members WHERE id = ?");
        $stmt->execute([$id]);
        $user_id = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Member berhasil dihapus!";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal menghapus member: " . $e->getMessage();
    }
    
    header('Location: members.php');
    exit;
}

$stmt = $pdo->query("
    SELECT m.*, u.username, u.email, u.created_at as user_created 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
");
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manajemen Member - Perpustakaan Digital</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .btn-group .btn {
            margin-right: 2px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .table th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
        }
        .badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Manajemen Member</h1>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Member</h6>
                            <div>
                                <a href="members-add.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Member
                                </a>
                                <button class="btn btn-info btn-sm" onclick="exportToExcel()">
                                    <i class="fas fa-file-export"></i> Export
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($members)): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Belum ada member yang terdaftar.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nama Lengkap</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Telepon</th>
                                                <th>Tanggal Bergabung</th>
                                                <th>Status</th>
                                                <th width="150">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($members as $member): ?>
                                            <tr>
                                                <td><?php echo $member['id']; ?></td>
                                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['username']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($member['membership_date'])); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $member['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo $member['status'] == 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="members-edit.php?id=<?php echo $member['id']; ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Edit Member">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="members.php?toggle_status=<?php echo $member['id']; ?>" 
                                                           class="btn btn-sm <?php echo $member['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>"
                                                           onclick="return confirmStatusChange('<?php echo $member['status']; ?>', '<?php echo htmlspecialchars($member['full_name']); ?>')"
                                                           title="<?php echo $member['status'] == 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?> Member">
                                                            <i class="fas fa-<?php echo $member['status'] == 'active' ? 'ban' : 'check'; ?>"></i>
                                                        </a>
                                                        <a href="members.php?delete=<?php echo $member['id']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirmDelete('<?php echo htmlspecialchars($member['full_name']); ?>')" 
                                                           title="Hapus Member">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Perpustakaan Digital <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    
    <script>
        function confirmDelete(memberName) {
            return confirm(`Apakah Anda yakin ingin menghapus member "${memberName}"?\n\nTindakan ini tidak dapat dibatalkan!`);
        }

        function confirmStatusChange(currentStatus, memberName) {
            const action = currentStatus === 'active' ? 'menonaktifkan' : 'mengaktifkan';
            return confirm(`Apakah Anda yakin ingin ${action} member "${memberName}"?`);
        }

        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        function exportToExcel() {
            alert('Fitur ditahan bea cukai');
        }

        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "pageLength": 25,
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>
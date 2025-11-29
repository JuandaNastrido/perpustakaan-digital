<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.email 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.id = ?
");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: members.php?error=Member tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $membership_date = $_POST['membership_date'];
    
    try {
        $pdo->beginTransaction();
        
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashed_password, $member['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $member['user_id']]);
        }
        
        $stmt = $pdo->prepare("UPDATE members SET full_name = ?, phone = ?, address = ?, membership_date = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $address, $membership_date, $id]);
        
        $pdo->commit();
        
        header('Location: members.php?success=Member berhasil diupdate');
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
    <title>Edit Member - Perpustakaan Digital</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Edit Member</h1>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Form Edit Member</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username *</label>
                                            <input type="text" class="form-control" id="username" name="username" required
                                                   value="<?php echo htmlspecialchars($member['username']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="email">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" required
                                                   value="<?php echo htmlspecialchars($member['email']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="password">Password (kosongkan jika tidak diubah)</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="full_name">Nama Lengkap *</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" required
                                                   value="<?php echo htmlspecialchars($member['full_name']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Telepon</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                   value="<?php echo htmlspecialchars($member['phone']); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="address">Alamat</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($member['address']); ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="membership_date">Tanggal Bergabung *</label>
                                            <input type="date" class="form-control" id="membership_date" name="membership_date" required
                                                   value="<?php echo $member['membership_date']; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Member
                                    </button>
                                    <a href="members.php" class="btn btn-secondary">
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
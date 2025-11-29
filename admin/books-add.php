<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category_id = $_POST['category_id'] ?: NULL;
    $publication_year = $_POST['publication_year'];
    $publisher = $_POST['publisher'];
    $total_copies = $_POST['total_copies'];
    $description = $_POST['description'];
    
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.' . $fileExtension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                $cover_image = 'uploads/covers/' . $filename;
            } else {
                $error = "Gagal mengupload cover image.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.";
        }
    }
    
    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category_id, publication_year, publisher, total_copies, available_copies, cover_image, description) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $isbn, $category_id, $publication_year, $publisher, $total_copies, $total_copies, $cover_image, $description]);
            
            header('Location: books.php?success=Buku berhasil ditambahkan');
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tambah Buku - Perpustakaan Digital</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .cover-preview {
            max-width: 200px;
            max-height: 300px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .cover-preview img {
            max-width: 100%;
            max-height: 250px;
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
                    <h1 class="h3 mb-4 text-gray-800">Tambah Buku</h1>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Buku</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cover_image">Cover Buku</label>
                                            <div class="cover-preview" id="coverPreview">
                                                <i class="fas fa-book fa-3x text-muted mb-2"></i>
                                                <p class="small text-muted">Preview cover akan muncul di sini</p>
                                            </div>
                                            <input type="file" class="form-control-file" id="cover_image" name="cover_image" 
                                                   accept="image/*" onchange="previewCover(this)">
                                            <small class="form-text text-muted">
                                                Format: JPG, PNG, GIF, WebP. Maksimal 2MB.
                                            </small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="title">Judul Buku *</label>
                                            <input type="text" class="form-control" id="title" name="title" required 
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="author">Penulis *</label>
                                            <input type="text" class="form-control" id="author" name="author" required
                                                   value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="isbn">ISBN</label>
                                            <input type="text" class="form-control" id="isbn" name="isbn"
                                                   value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id">Kategori</label>
                                            <select class="form-control" id="category_id" name="category_id">
                                                <option value="">- Pilih Kategori -</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="publication_year">Tahun Terbit</label>
                                            <input type="number" class="form-control" id="publication_year" name="publication_year" 
                                                   min="1900" max="2030" 
                                                   value="<?php echo isset($_POST['publication_year']) ? $_POST['publication_year'] : date('Y'); ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="publisher">Penerbit</label>
                                            <input type="text" class="form-control" id="publisher" name="publisher"
                                                   value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : ''; ?>">
                                        </div>                                 
                                        <div class="form-group">
                                            <label for="total_copies">Jumlah License *</label>
                                            <select class="form-control" id="total_copies" name="total_copies" required>
                                                <option value="1" <?php echo (isset($_POST['total_copies']) && $_POST['total_copies'] == 1) ? 'selected' : ''; ?>>1 License (Exclusive)</option>
                                                <option value="2" <?php echo (isset($_POST['total_copies']) && $_POST['total_copies'] == 2) ? 'selected' : ''; ?>>2 Licenses (Limited)</option>
                                                <option value="3" <?php echo (isset($_POST['total_copies']) && $_POST['total_copies'] == 3) ? 'selected' : (isset($_POST['total_copies']) ? '' : 'selected'); ?>>3 Licenses (Standard)</option>
                                                <option value="5" <?php echo (isset($_POST['total_copies']) && $_POST['total_copies'] == 5) ? 'selected' : ''; ?>>5 Licenses (Popular)</option>
                                                <option value="10" <?php echo (isset($_POST['total_copies']) && $_POST['total_copies'] == 10) ? 'selected' : ''; ?>>10 Licenses (Bestseller)</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Tentukan berapa banyak user yang bisa membaca buku ini secara bersamaan.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Buku
                                    </button>
                                    <a href="books.php" class="btn btn-secondary">
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
    
    <script>
    function previewCover(input) {
        const preview = document.getElementById('coverPreview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Cover Preview" class="img-fluid">`;
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.innerHTML = `
                <i class="fas fa-book fa-3x text-muted mb-2"></i>
                <p class="small text-muted">Preview cover akan muncul di sini</p>
            `;
        }
    }
    
    document.getElementById('cover_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.size > 2 * 1024 * 1024) { 
            alert('File terlalu besar! Maksimal 2MB.');
            this.value = '';
            document.getElementById('coverPreview').innerHTML = `
                <i class="fas fa-book fa-3x text-muted mb-2"></i>
                <p class="small text-muted">Preview cover akan muncul di sini</p>
            `;
        }
    });
    </script>
</body>
</html>
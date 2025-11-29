<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$sort = $_GET['sort'] ?? 'title';

$sql = "SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($category_id)) {
    $sql .= " AND b.category_id = ?";
    $params[] = $category_id;
}

$sort_options = [
    'title' => 'b.title ASC',
    'title_desc' => 'b.title DESC',
    'author' => 'b.author ASC',
    'year' => 'b.publication_year DESC',
    'newest' => 'b.created_at DESC'
];
$sql .= " ORDER BY " . ($sort_options[$sort] ?? 'b.title ASC');

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .book-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .search-box {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .card-img-top {
            border-bottom: 1px solid rgba(0,0,0,0.125);
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
                <a class="nav-link active" href="books.php">
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

    <div class="container mt-4">
        <div class="search-box">
            <h2><i class="fas fa-search"></i> Cari Buku</h2>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Cari judul, penulis, atau deskripsi..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Judul A-Z</option>
                        <option value="title_desc" <?php echo $sort == 'title_desc' ? 'selected' : ''; ?>>Judul Z-A</option>
                        <option value="author" <?php echo $sort == 'author' ? 'selected' : ''; ?>>Penulis</option>
                        <option value="year" <?php echo $sort == 'year' ? 'selected' : ''; ?>>Tahun Terbaru</option>
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru Ditambahkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Hasil Pencarian (<?php echo count($books); ?> buku)</h3>
            <?php if (!empty($search) || !empty($category_id)): ?>
                <a href="books.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Hapus Filter
                </a>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card book-card shadow">
                        <div class="position-relative">
                            <?php 
                            if ($book['cover_image'] && file_exists($book['cover_image'])) {
                                $cover_url = $book['cover_image'];
                            } else {
                                $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe'];
                                $color = $colors[crc32($book['title']) % count($colors)];
                                $cover_url = "data:image/svg+xml;utf8," . rawurlencode('
                                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300">
                                        <rect width="200" height="300" fill="' . $color . '"/>
                                        <text x="100" y="120" font-family="Arial" font-size="14" fill="white" text-anchor="middle" font-weight="bold">'
                                        . htmlspecialchars(substr($book['title'], 0, 20)) . '</text>
                                        <text x="100" y="150" font-family="Arial" font-size="12" fill="white" text-anchor="middle">Oleh: '
                                        . htmlspecialchars(substr($book['author'], 0, 25)) . '</text>
                                        <text x="100" y="280" font-family="Arial" font-size="10" fill="white" text-anchor="middle">Perpustakaan Digital</text>
                                    </svg>
                                ');
                            }
                            ?>
                            <img src="<?php echo $cover_url; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 style="height: 250px; object-fit: cover;"
                                 onerror="this.src='data:image/svg+xml;utf8,<?php echo rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"300\" viewBox=\"0 0 200 300\"><rect width=\"200\" height=\"300\" fill=\"#f8f9fa\"/><text x=\"100\" y=\"150\" font-family=\"Arial\" font-size=\"14\" fill=\"#6c757d\" text-anchor=\"middle\" dominant-baseline=\"middle\">No Cover</text></svg>'); ?>'">
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?> licenses
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($book['author']); ?></h6>
                            
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> <?php echo $book['category_name'] ?? 'Umum'; ?>
                                    • <i class="fas fa-calendar"></i> <?php echo $book['publication_year']; ?>
                                    <?php if ($book['publisher']): ?>
                                        • <i class="fas fa-building"></i> <?php echo htmlspecialchars($book['publisher']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            
                            <p class="card-text small text-muted">
                                <?php 
                                if (!empty($book['description'])) {
                                    echo substr($book['description'], 0, 100);
                                    if (strlen($book['description']) > 100) echo '...';
                                } else {
                                    echo 'Tidak ada deskripsi.';
                                }
                                ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
                                <?php if ($book['available_copies'] > 0): ?>
                                    <a href="borrow-book.php?book_id=<?php echo $book['id']; ?>" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Start reading \'<?php echo htmlspecialchars(addslashes($book['title'])); ?>\'?')">
                                        <i class="fas fa-book-reader"></i> Read Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-clock"></i> Join Waitlist
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Tidak ada buku yang ditemukan</h4>
                        <p class="text-muted">Coba ubah kata kunci pencarian atau filter kategori.</p>
                        <a href="books.php" class="btn btn-primary mt-2">
                            <i class="fas fa-book"></i> Tampilkan Semua Buku
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
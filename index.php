<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }

        .book-card {
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .hero-book-card {
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .stats-section {
            background: #f8f9fa;
            padding: 60px 0;
            position: relative;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> Perpustakaan Digital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'member'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="books.php">
                                    <i class="fas fa-book"></i> Buku
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/index.php">
                                    <i class="fas fa-cog"></i> Admin Panel
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout (<?php echo $_SESSION['username']; ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold text-white">Selamat Datang di Perpustakaan Digital</h1>
                    <p class="lead text-white">Temukan ribuan buku digital berkualitas untuk memperluas wawasan Anda</p>
                    <div class="mt-4">
                        <a href="books.php" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-search"></i> Jelajahi Katalog
                        </a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-user-plus"></i> Daftar Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <?php
                    $featured_book = $pdo->query("
                        SELECT * FROM books 
                        WHERE cover_image IS NOT NULL 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ")->fetch();
                    
                    if ($featured_book):
                    ?>
                    <div class="hero-book-card">
                        <?php if ($featured_book['cover_image'] && file_exists($featured_book['cover_image'])): ?>
                            <img src="<?php echo $featured_book['cover_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($featured_book['title']); ?>" 
                                 class="img-fluid rounded shadow"
                                 style="max-height: 300px; object-fit: cover; border: 3px solid white;">
                        <?php else: ?>
                            <div class="bg-light rounded shadow d-flex align-items-center justify-content-center"
                                 style="height: 300px; border: 3px solid white;">
                                <div class="text-center text-dark p-3">
                                    <i class="fas fa-book fa-3x mb-3"></i>
                                    <h5><?php echo htmlspecialchars($featured_book['title']); ?></h5>
                                    <p class="mb-0">Oleh: <?php echo htmlspecialchars($featured_book['author']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <small class="text-white">
                                <strong>Buku Terbaru:</strong> <?php echo htmlspecialchars($featured_book['title']); ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <?php
                $total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
                $total_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'")->fetchColumn();
                $available_books = $pdo->query("SELECT SUM(available_copies) FROM books")->fetchColumn();
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-book fa-3x text-primary mb-3"></i>
                            <h3><?php echo $total_books; ?></h3>
                            <p class="text-muted">Total Buku</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h3><?php echo $total_members; ?></h3>
                            <p class="text-muted">Anggota Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                            <h3><?php echo $available_books; ?></h3>
                            <p class="text-muted">Stok Buku Tersedia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Buku Terbaru</h2>
            <div class="row">
                <?php
                $featured_books = $pdo->query("
                    SELECT b.*, c.name as category_name 
                    FROM books b 
                    LEFT JOIN categories c ON b.category_id = c.id 
                    ORDER BY b.created_at DESC 
                    LIMIT 6
                ")->fetchAll();
                
                foreach ($featured_books as $book):
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card book-card shadow h-100">
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
                                    <?php echo $book['available_copies']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($book['author']); ?></h6>
                            
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> <?php echo $book['category_name'] ?? 'Umum'; ?>
                                    • <i class="fas fa-calendar"></i> <?php echo $book['publication_year']; ?>
                                </small>
                            </p>
                            
                            <p class="card-text flex-grow-1">
                                <?php 
                                if (!empty($book['description'])) {
                                    echo substr($book['description'], 0, 100);
                                    if (strlen($book['description']) > 100) echo '...';
                                } else {
                                    echo '<span class="text-muted">Tidak ada deskripsi</span>';
                                }
                                ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> Detail
                                </a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'member' && $book['available_copies'] > 0): ?>
                                    <a href="borrow-book.php?book_id=<?php echo $book['id']; ?>" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Pinjam buku \'<?php echo htmlspecialchars(addslashes($book['title'])); ?>\'?')">
                                        <i class="fas fa-bookmark"></i> Pinjam
                                    </a>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Tersedia</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="books.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-book-open"></i> Jelajahi Semua Buku
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 Perpustakaan Digital. start reading books to improve human resources.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
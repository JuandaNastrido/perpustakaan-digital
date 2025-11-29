<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header('Location: login.php');
    exit;
}

$book_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.id = ?
");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: books.php?error=Buku tidak ditemukan');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

$can_review = $pdo->prepare("
    SELECT COUNT(*) FROM borrowings 
    WHERE member_id = ? AND book_id = ? AND status = 'returned'
");
$can_review->execute([$member['id'], $book_id]);
$can_review = $can_review->fetchColumn() > 0;

$reviews = $pdo->prepare("
    SELECT r.*, m.full_name 
    FROM reviews r 
    JOIN members m ON r.member_id = m.id 
    WHERE r.book_id = ? 
    ORDER BY r.review_date DESC
");
$reviews->execute([$book_id]);
$reviews_data = $reviews->fetchAll();

$rating_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
    FROM reviews 
    WHERE book_id = ?
");
$rating_stats->execute([$book_id]);
$stats = $rating_stats->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!$can_review) {
        $error = "Anda harus meminjam dan mengembalikan buku ini sebelum memberikan review";
    } else {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO reviews (book_id, member_id, rating, comment) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = ?, comment = ?, review_date = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$book_id, $member['id'], $rating, $comment, $rating, $comment]);
            
            $stmt = $pdo->prepare("
                UPDATE books SET 
                    average_rating = (SELECT AVG(rating) FROM reviews WHERE book_id = ?),
                    total_ratings = (SELECT COUNT(*) FROM reviews WHERE book_id = ?)
                WHERE id = ?
            ");
            $stmt->execute([$book_id, $book_id, $book_id]);
            
            $pdo->commit();
            header('Location: book-detail.php?id=' . $book_id . '&success=Review berhasil disimpan');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Perpustakaan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .rating-stars {
            color: #ffc107;
        }
        .review-card {
            border-left: 4px solid #007bff;
        }
        .progress {
            height: 8px;
        }
        .book-cover {
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .book-info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
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
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="books.php">Buku</a>
                <a class="nav-link" href="borrowing-history.php">Riwayat</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <?php 
                    if ($book['cover_image'] && file_exists($book['cover_image'])) {
                        $cover_url = $book['cover_image'];
                    } else {
                        $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe'];
                        $color = $colors[crc32($book['title']) % count($colors)];
                        $cover_url = "data:image/svg+xml;utf8," . rawurlencode('
                            <svg xmlns="http://www.w3.org/2000/svg" width="300" height="450" viewBox="0 0 300 450">
                                <rect width="300" height="450" fill="' . $color . '"/>
                                <text x="150" y="180" font-family="Arial" font-size="16" fill="white" text-anchor="middle" font-weight="bold">'
                                . htmlspecialchars($book['title']) . '</text>
                                <text x="150" y="220" font-family="Arial" font-size="12" fill="white" text-anchor="middle">Oleh: '
                                . htmlspecialchars($book['author']) . '</text>
                                <text x="150" y="420" font-family="Arial" font-size="10" fill="white" text-anchor="middle">Perpustakaan Digital</text>
                            </svg>
                        ');
                    }
                    ?>
                    <img src="<?php echo $cover_url; ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>" 
                         class="book-cover img-fluid mb-3"
                         onerror="this.src='data:image/svg+xml;utf8,<?php echo rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"300\" height=\"450\" viewBox=\"0 0 300 450\"><rect width=\"300\" height=\"450\" fill=\"#f8f9fa\"/><text x=\"150\" y=\"225\" font-family=\"Arial\" font-size=\"16\" fill=\"#6c757d\" text-anchor=\"middle\" dominant-baseline=\"middle\">Cover Tidak Tersedia</text></svg>'); ?>'">
                    
                    <div class="availability-badge">
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="badge bg-success fs-6 p-3">
                                <i class="fas fa-check-circle"></i> 
                                <strong><?php echo $book['available_copies']; ?> Copy Tersedia</strong>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6 p-3">
                                <i class="fas fa-times-circle"></i> 
                                <strong>Sedang Dipinjam Semua</strong>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <?php if ($book['available_copies'] > 0): ?>
                            <a href="borrow-book.php?book_id=<?php echo $book['id']; ?>" 
                               class="btn btn-success btn-lg w-100 mb-2"
                               onclick="return confirm('Pinjam buku \'<?php echo htmlspecialchars(addslashes($book['title'])); ?>\'?')">
                                <i class="fas fa-bookmark"></i> Pinjam Buku Ini
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                <i class="fas fa-clock"></i> Menunggu Ketersediaan
                            </button>
                        <?php endif; ?>
                        <a href="books.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-arrow-left"></i> Kembali ke Katalog
                        </a>
                    </div>
                </div>

                <div class="book-info-section">
                    <h5><i class="fas fa-info-circle"></i> Informasi Buku</h5>
                    <hr>
                    <p><strong><i class="fas fa-tag"></i> Kategori:</strong><br>
                       <?php echo $book['category_name'] ?? 'Umum'; ?></p>
                    
                    <p><strong><i class="fas fa-building"></i> Penerbit:</strong><br>
                       <?php echo htmlspecialchars($book['publisher'] ?? '-'); ?></p>
                    
                    <p><strong><i class="fas fa-calendar"></i> Tahun Terbit:</strong><br>
                       <?php echo $book['publication_year']; ?></p>
                    
                    <?php if ($book['isbn']): ?>
                    <p><strong><i class="fas fa-barcode"></i> ISBN:</strong><br>
                       <?php echo $book['isbn']; ?></p>
                    <?php endif; ?>
                    
                    <p><strong><i class="fas fa-copy"></i> Total Copy:</strong><br>
                       <?php echo $book['total_copies']; ?> copy</p>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="card-title display-6"><?php echo htmlspecialchars($book['title']); ?></h1>
                        <h2 class="text-muted h4">Oleh: <?php echo htmlspecialchars($book['author']); ?></h2>
                        
                        <div class="mb-4">
                            <?php if ($stats['total_reviews'] > 0): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rating-stars me-2">
                                        <?php
                                        $avg_rating = round($stats['avg_rating'], 1);
                                        $full_stars = floor($avg_rating);
                                        $half_star = ($avg_rating - $full_stars) >= 0.5;
                                        
                                        for ($i = 1; $i <= 5; $i++): 
                                            if ($i <= $full_stars): ?>
                                                <i class="fas fa-star fa-lg"></i>
                                            <?php elseif ($half_star && $i == $full_stars + 1): ?>
                                                <i class="fas fa-star-half-alt fa-lg"></i>
                                            <?php else: ?>
                                                <i class="far fa-star fa-lg"></i>
                                            <?php endif;
                                        endfor; ?>
                                    </div>
                                    <span class="me-2 fs-5"><strong><?php echo $avg_rating; ?></strong></span>
                                    <span class="text-muted">(<?php echo $stats['total_reviews']; ?> ulasan)</span>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><i class="far fa-star"></i> Belum ada rating</span>
                            <?php endif; ?>
                        </div>

                        <h4 class="mb-3">Deskripsi Buku</h4>
                        <div class="book-description">
                            <?php if (!empty($book['description'])): ?>
                                <p class="card-text fs-6"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                            <?php else: ?>
                                <p class="card-text text-muted">Tidak ada deskripsi yang tersedia untuk buku ini.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-comments"></i> 
                            Ulasan Pembaca (<?php echo $stats['total_reviews'] ?? 0; ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($can_review): ?>
                            <div class="mb-4 p-3 border rounded">
                                <h6 class="mb-3">Beri Ulasan Anda</h6>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Rating</label>
                                        <div class="rating-input">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="rating" 
                                                           id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                                    <label class="form-check-label fs-5" for="rating<?php echo $i; ?>">
                                                        <?php echo $i; ?> <i class="fas fa-star text-warning"></i>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comment" class="form-label fw-bold">Komentar</label>
                                        <textarea class="form-control" id="comment" name="comment" 
                                                  rows="3" placeholder="Bagikan pengalaman membaca Anda..." required></textarea>
                                    </div>
                                    <button type="submit" name="submit_review" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Ulasan
                                    </button>
                                </form>
                            </div>
                            <hr>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <div class="alert alert-info">
                                <a href="login.php" class="alert-link">Login</a> untuk memberikan ulasan.
                            </div>
                        <?php endif; ?>

                        <?php if (count($reviews_data) > 0): ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews_data as $review): ?>
                                <div class="review-card card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($review['full_name']); ?>
                                                </h6>
                                                <div class="rating-stars mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                    <span class="ms-2 text-muted"><?php echo $review['rating']; ?>/5</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('d M Y', strtotime($review['review_date'])); ?>
                                            </small>
                                        </div>
                                        <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada ulasan untuk buku ini.</p>
                                <?php if (!$can_review && isset($_SESSION['user_id'])): ?>
                                    <small class="text-muted">Pinjam dan baca buku ini untuk memberikan ulasan pertama!</small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
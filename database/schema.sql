SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS users, members, categories, books, borrowings, reviews;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin','member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    membership_date DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    category_id INT,
    publication_year YEAR,
    publisher VARCHAR(100),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    cover_image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE borrowings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    book_id INT,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    fine_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT,
    member_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (member_id) REFERENCES members(id)
);

INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@perpustakaan.com', 'admin'),
('member1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member1@email.com', 'member'),
('member2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member2@email.com', 'member');


INSERT INTO members (user_id, full_name, phone, address, membership_date) VALUES
(2, 'John Doe', '08123456789', 'Jl. Contoh No. 123', '2025-01-15'),
(3, 'Jane Smith', '08198765432', 'Jl. Sample No. 456', '2025-01-20');

INSERT INTO categories (name, description) VALUES
('Sains Fiksi', 'Buku-buku fiksi ilmiah dan fantasi'),
('Teknologi', 'Buku programming, IT, dan teknologi'),
('Sejarah', 'Buku sejarah dan biografi'),
('Bisnis', 'Buku manajemen dan kewirausahaan');

INSERT INTO books (title, author, isbn, category_id, publication_year, publisher, total_copies, available_copies, description) VALUES
('Pemrograman PHP Modern', 'Budi Santoso', '9781234567890', 2, 2024, 'Penerbit Informatika', 5, 5, 'Buku panduan lengkap pemrograman PHP modern'),
('Sejarah Indonesia Modern', 'Sri Wahyuni', '9781234567891', 3, 2023, 'Penerbit Sejarah', 3, 3, 'Sejarah Indonesia dari masa kemerdekaan hingga sekarang'),
('Startup Guide', 'Andi Wijaya', '9781234567892', 4, 2024, 'Penerbit Bisnis', 4, 4, 'Panduan membangun startup dari nol'),
('Dunia Parallel', 'Dian Sastro', '9781234567893', 1, 2023, 'Penerbit Fiksi', 2, 2, 'Novel fiksi ilmiah tentang dunia parallel');
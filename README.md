# 📚 Perpustakaan Digital

Sistem manajemen perpustakaan digital lengkap untuk Project UTS Web Lanjut.

## ✨ Features

### 🎯 Admin Panel
- 📊 Dashboard dengan statistics & charts
- 📚 Manajemen buku & kategori
- 👥 Manajemen anggota  
- 📋 Kelola peminjaman & pengembalian
- ⭐ Review moderation
- 📈 Laporan & analytics

### 🎯 Member Area
- 🔍 Advanced search & filtering
- 📖 Digital book lending system
- ⭐ Review & rating system
- 📚 Riwayat peminjaman
- 👤 Personal dashboard

## 🛠️ Tech Stack

- **Frontend**: Bootstrap 5, SB Admin 2, Chart.js
- **Backend**: PHP 8.4.5
- **Database**: MySQL
- **Authentication**: Session-based dengan role management

## 🗃️ Database Schema

6 relational tables:
- `users` - Authentication & roles
- `members` - Member profiles  
- `books` - Book catalog dengan cover upload
- `categories` - Book categories
- `borrowings` - Lending management
- `reviews` - Rating & review system

## 🚀 Installation

1. Clone repository
2. Import `database/schema.sql`
3. Configure `includes/config.php`
4. Access via web server

## 👤 Default Accounts

**Admin Panel**: `http://localhost/perpustakaan-digital/admin/login.php`
- Username: `admin`
- Password: `password`

**Member Area**: `http://localhost/perpustakaan-digital/login.php`  
- Username: `member1`
- Password: `password`

## 📸 Screenshots


## 📄 License

MIT License - feel free to use for educational purposes.

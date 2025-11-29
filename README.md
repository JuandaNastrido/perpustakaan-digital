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
- Book catalog

<img width="1919" height="951" alt="image" src="https://github.com/user-attachments/assets/37e09641-a441-441f-9a66-7f7d3a6b6558" />

- Member area


<img width="1919" height="949" alt="image" src="https://github.com/user-attachments/assets/5b0bd144-7a6b-478d-ac99-8a92b52d51ff" />

- Database relations


<img width="1225" height="804" alt="image" src="https://github.com/user-attachments/assets/364d6789-dd10-4a8f-a468-8488bf449592" />

- Admin dashboard


<img width="1919" height="952" alt="image" src="https://github.com/user-attachments/assets/d4761a7e-b04e-45b0-96c0-8e072942cce1" />





## 📄 License

MIT License - feel free to use for educational purposes.

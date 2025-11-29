<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-text mx-3">Perpustakaan Digital</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">Manajemen</div>
    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="books.php">
            <i class="fas fa-fw fa-book"></i>
            <span>Buku</span>
        </a>
    </li>
    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="categories.php">
            <i class="fas fa-fw fa-tags"></i>
            <span>Kategori</span>
        </a>
    </li>
    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="members.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Anggota</span>
        </a>
    </li>
    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'borrowings.php' ? 'active' : ''; ?>">
        <a class="nav-link" href="borrowings.php">
            <i class="fas fa-fw fa-clipboard-list"></i>
            <span>Peminjaman</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    <li class="nav-item">
        <a class="nav-link" href="logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
</ul>
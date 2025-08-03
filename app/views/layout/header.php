<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'NusantaraLingo - Jelajahi Kekayaan Bahasa Nusantara'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/public/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
<!-- Navbar untuk user yang sudah login - FIXED -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%) !important;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/public/index.php?controller=dashboard&action=index" 
           style="color: white !important;">
            <i class="fas fa-globe-asia" style="color: #fde047 !important;"></i> NusantaraLingo
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/public/index.php?controller=dashboard&action=index"
                       style="color: white !important; background: rgba(255,255,255,0.1) !important; border-radius: 0.5rem; margin: 0 0.25rem; padding: 0.5rem 1rem !important;">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/public/index.php?controller=quiz&action=index"
                       style="color: white !important; background: rgba(255,255,255,0.1) !important; border-radius: 0.5rem; margin: 0 0.25rem; padding: 0.5rem 1rem !important;">
                        <i class="fas fa-brain"></i> Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/public/index.php?controller=dashboard&action=leaderboard"
                       style="color: white !important; background: rgba(255,255,255,0.1) !important; border-radius: 0.5rem; margin: 0 0.25rem; padding: 0.5rem 1rem !important;">
                        <i class="fas fa-trophy"></i> Leaderboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/public/index.php?controller=dashboard&action=achievements"
                       style="color: white !important; background: rgba(255,255,255,0.1) !important; border-radius: 0.5rem; margin: 0 0.25rem; padding: 0.5rem 1rem !important;">
                        <i class="fas fa-medal"></i> Pencapaian
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown"
                       style="color: white !important;">
                        <img src="/public/images/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>" 
                             alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                        <div class="d-flex flex-column align-items-start">
                            <small style="color: white !important;"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></small>
                            <small style="color: #fde047 !important;">
                                <i class="fas fa-star"></i> Level <?php echo $_SESSION['level'] ?? 1; ?> 
                                | <i class="fas fa-coins"></i> <?php echo number_format($_SESSION['total_points'] ?? 0); ?>
                            </small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/public/index.php?controller=dashboard&action=profile">
                            <i class="fas fa-user"></i> Profil Saya
                        </a></li>
                        <li><a class="dropdown-item" href="/public/index.php?controller=quiz&action=history">
                            <i class="fas fa-history"></i> Riwayat Kuis
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/public/index.php?controller=auth&action=logout">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
    
    <div class="<?php echo isset($_SESSION['user_id']) ? 'container mt-4' : ''; ?>">
        <!-- Alert Messages -->
        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php
                switch($_GET['message']) {
                    case 'login_success':
                        echo 'Selamat datang kembali! Login berhasil.';
                        break;
                    case 'register_success':
                        echo 'Pendaftaran berhasil! Silakan login dengan akun Anda.';
                        break;
                    case 'logout_success':
                        echo 'Anda telah berhasil keluar. Sampai jumpa lagi!';
                        break;
                    case 'quiz_completed':
                        echo 'Kuis berhasil diselesaikan! Lihat hasil Anda.';
                        break;
                    default:
                        echo 'Operasi berhasil dilakukan.';
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php
                switch($_GET['error']) {
                    case 'login_required':
                        echo 'Silakan login terlebih dahulu untuk mengakses halaman ini.';
                        break;
                    case 'quiz_not_found':
                        echo 'Kuis tidak ditemukan atau tidak tersedia.';
                        break;
                    case 'no_questions':
                        echo 'Kuis ini belum memiliki soal. Silakan pilih kuis lain.';
                        break;
                    case 'result_not_found':
                        echo 'Hasil kuis tidak ditemukan.';
                        break;
                    default:
                        echo htmlspecialchars($_GET['error']);
                        break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

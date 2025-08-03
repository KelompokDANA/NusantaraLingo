<?php 
$title = 'Dashboard - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<!-- Hero Section - FORCE INLINE STYLES -->
<div class="bg-gradient-primary text-white rounded-4 p-4 mb-4" 
     style="background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%) !important; color: white !important; min-height: 200px;">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2" style="color: white !important;">
                Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 🎉
            </h1>
            <p class="lead mb-3" style="color: rgba(255,255,255,0.9) !important;">
                Siap melanjutkan petualangan belajar bahasa daerah hari ini?
            </p>
            <div class="d-flex flex-wrap gap-3">
                <div class="rounded-3 px-3 py-2" 
                     style="background: rgba(255, 255, 255, 0.2) !important; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="fas fa-star" style="color: #fde047 !important;"></i> 
                    <span style="color: white !important; font-weight: bold;">Level <?php echo $this->user->level; ?></span>
                </div>
                <div class="rounded-3 px-3 py-2" 
                     style="background: rgba(255, 255, 255, 0.2) !important; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="fas fa-coins" style="color: #fde047 !important;"></i> 
                    <span style="color: white !important; font-weight: bold;"><?php echo number_format($this->user->total_points); ?> Poin</span>
                </div>
                <div class="rounded-3 px-3 py-2" 
                     style="background: rgba(255, 255, 255, 0.2) !important; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="fas fa-fire" style="color: #fb923c !important;"></i> 
                    <span style="color: white !important; font-weight: bold;"><?php echo $this->user->streak_days; ?> Hari Berturut</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <img src="/public/images/avatars/<?php echo $this->user->avatar; ?>" 
                 alt="Avatar" class="rounded-circle border border-white border-3" width="120" height="120">
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-brain fa-2x mb-3"></i>
                <h3 class="fw-bold"><?php echo $stats['total_quizzes']; ?></h3>
                <p class="mb-0">Kuis Diselesaikan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-percentage fa-2x mb-3"></i>
                <h3 class="fw-bold"><?php echo $stats['avg_score'] ?? 0; ?>%</h3>
                <p class="mb-0">Rata-rata Skor</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-globe-asia fa-2x mb-3"></i>
                <h3 class="fw-bold"><?php echo $stats['languages_count']; ?></h3>
                <p class="mb-0">Bahasa Dipelajari</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body text-center">
                <i class="fas fa-medal fa-2x mb-3"></i>
                <h3 class="fw-bold"><?php echo $stats['achievements_count']; ?></h3>
                <p class="mb-0">Pencapaian</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Available Quizzes -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-brain"></i> Kuis Tersedia</h5>
            </div>
            <div class="card-body">
                <?php if(empty($available_quizzes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada kuis tersedia.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach(array_slice($available_quizzes, 0, 6) as $quiz): ?>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100 quiz-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                                            <span class="badge bg-<?php echo $quiz['difficulty_level'] == 'pemula' ? 'success' : ($quiz['difficulty_level'] == 'menengah' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($quiz['difficulty_level']); ?>
                                            </span>
                                        </div>
                                        <p class="card-text small text-muted mb-2">
                                            <i class="fas fa-globe"></i> <?php echo htmlspecialchars($quiz['language_name']); ?> - 
                                            <i class="fas fa-<?php echo $quiz['category_icon']; ?>"></i> <?php echo htmlspecialchars($quiz['category_name']); ?>
                                        </p>
                                        <p class="card-text small"><?php echo htmlspecialchars(substr($quiz['description'], 0, 80)) . '...'; ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?>s | 
                                                <i class="fas fa-coins"></i> <?php echo $quiz['points_per_question'] * $quiz['total_questions']; ?> poin
                                            </small>
                                            <a href="/public/index.php?controller=quiz&action=show&id=<?php echo $quiz['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-play"></i> Mulai
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/public/index.php?controller=quiz&action=index" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Semua Kuis
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-history"></i> Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                <?php if(empty($recent_attempts)): ?>
                    <p class="text-muted text-center">Belum ada aktivitas.</p>
                <?php else: ?>
                    <?php foreach($recent_attempts as $attempt): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($attempt['language_name']); ?> - 
                                    <?php echo date('d/m/Y H:i', strtotime($attempt['completed_at'])); ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?php echo $attempt['percentage'] >= 80 ? 'success' : ($attempt['percentage'] >= 60 ? 'warning' : 'danger'); ?>">
                                    <?php echo $attempt['percentage']; ?>%
                                </span>
                                <br>
                                <small class="text-muted"><?php echo $attempt['score']; ?> poin</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-trophy"></i> Top Pemain</h6>
            </div>
            <div class="card-body">
                <?php if(empty($leaderboard)): ?>
                    <p class="text-muted text-center">Belum ada data leaderboard.</p>
                <?php else: ?>
                    <?php foreach($leaderboard as $index => $player): ?>
                        <div class="d-flex align-items-center mb-3 <?php echo $player['id'] == $_SESSION['user_id'] ? 'bg-light rounded p-2' : ''; ?>">
                            <div class="me-3">
                                <?php if($index == 0): ?>
                                    <i class="fas fa-crown text-warning fa-lg"></i>
                                <?php elseif($index == 1): ?>
                                    <i class="fas fa-medal text-secondary fa-lg"></i>
                                <?php elseif($index == 2): ?>
                                    <i class="fas fa-medal text-warning fa-lg"></i>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </div>
                            <img src="/public/images/avatars/<?php echo $player['avatar']; ?>" 
                                 alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?php echo htmlspecialchars($player['full_name']); ?></h6>
                                <small class="text-muted">Level <?php echo $player['level']; ?></small>
                            </div>
                            <div class="text-end">
                                <strong><?php echo number_format($player['total_points']); ?></strong>
                                <br>
                                <small class="text-muted">poin</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="/public/index.php?controller=dashboard&action=leaderboard" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-eye"></i> Lihat Semua
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Achievements -->
<?php if(!empty($achievements)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-medal"></i> Pencapaian Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach(array_slice($achievements, 0, 4) as $achievement): ?>
                        <?php if($achievement['earned_at']): ?>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light text-center achievement-card earned">
                                    <div class="card-body">
                                        <i class="fas fa-<?php echo $achievement['icon']; ?> fa-2x text-<?php echo $achievement['badge_color']; ?> mb-2"></i>
                                        <h6 class="card-title"><?php echo htmlspecialchars($achievement['name']); ?></h6>
                                        <p class="card-text small text-muted"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Diraih <?php echo date('d/m/Y', strtotime($achievement['earned_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="/public/index.php?controller=dashboard&action=achievements" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> Lihat Semua Pencapaian
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

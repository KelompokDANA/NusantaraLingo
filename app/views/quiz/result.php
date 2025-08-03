<?php 
$title = 'Hasil Kuis - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Result Header -->
        <div class="card shadow-lg mb-4">
            <div class="card-body text-center py-5">
                <div class="<?php 
                    if($attempt['percentage'] >= 80) echo 'result-circle result-excellent';
                    elseif($attempt['percentage'] >= 60) echo 'result-circle result-good';
                    else echo 'result-circle result-poor';
                ?> mb-4">
                    <?php echo round($attempt['percentage']); ?>%
                </div>
                
                <h2 class="mb-3">
                    <?php 
                    if($attempt['percentage'] >= 80) echo '🎉 Luar Biasa!';
                    elseif($attempt['percentage'] >= 60) echo '👍 Bagus!';
                    else echo '💪 Tetap Semangat!';
                    ?>
                </h2>
                
                <p class="lead text-muted mb-4">
                    Anda telah menyelesaikan kuis <strong><?php echo htmlspecialchars($attempt['quiz_title']); ?></strong>
                </p>

                <div class="row g-4">
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4><?php echo $attempt['correct_answers']; ?></h4>
                            <small class="text-muted">Benar</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                            <h4><?php echo $attempt['total_questions'] - $attempt['correct_answers']; ?></h4>
                            <small class="text-muted">Salah</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <i class="fas fa-coins fa-2x text-warning mb-2"></i>
                            <h4><?php echo $attempt['score']; ?></h4>
                            <small class="text-muted">Poin</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <i class="fas fa-clock fa-2x text-info mb-2"></i>
                            <h4><?php echo gmdate("i:s", $attempt['time_taken']); ?></h4>
                            <small class="text-muted">Waktu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Achievements -->
        <?php if(!empty($new_achievements)): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Pencapaian Baru!</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach($new_achievements as $achievement): ?>
                        <div class="col-md-6">
                            <div class="card bg-light border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-<?php echo $achievement['icon']; ?> fa-3x text-<?php echo $achievement['badge_color']; ?> mb-3"></i>
                                    <h5><?php echo htmlspecialchars($achievement['name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                    <span class="badge bg-success">+<?php echo $achievement['points_reward']; ?> Poin</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Performance Analysis -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Analisis Performa</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>Tingkat Akurasi</h6>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-<?php echo $attempt['percentage'] >= 80 ? 'success' : ($attempt['percentage'] >= 60 ? 'warning' : 'danger'); ?>" 
                                 style="width: <?php echo $attempt['percentage']; ?>%">
                                <?php echo round($attempt['percentage']); ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            <?php 
                            if($attempt['percentage'] >= 80) echo 'Excellent! Anda menguasai materi dengan baik.';
                            elseif($attempt['percentage'] >= 60) echo 'Good! Masih ada ruang untuk perbaikan.';
                            else echo 'Perlu lebih banyak latihan untuk materi ini.';
                            ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <h6>Kecepatan Mengerjakan</h6>
                        <?php 
                        $time_percentage = ($attempt['time_taken'] / 300) * 100; // Assuming 300s max
                        ?>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: <?php echo min($time_percentage, 100); ?>%">
                                <?php echo gmdate("i:s", $attempt['time_taken']); ?>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?php 
                            if($attempt['time_taken'] < 180) echo 'Sangat cepat! Anda efisien dalam mengerjakan.';
                            elseif($attempt['time_taken'] < 240) echo 'Kecepatan normal, pertahankan!';
                            else echo 'Ambil waktu yang cukup untuk berpikir lebih baik.';
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="/public/index.php?controller=quiz&action=index" class="btn btn-outline-primary w-100">
                            <i class="fas fa-list"></i> Kuis Lain
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/public/index.php?controller=quiz&action=show&id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo"></i> Ulangi Kuis
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/public/index.php?controller=dashboard&action=leaderboard" class="btn btn-outline-warning w-100">
                            <i class="fas fa-trophy"></i> Leaderboard
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="/public/index.php?controller=dashboard&action=index" class="btn btn-primary w-100">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

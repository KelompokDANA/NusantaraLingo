<?php 
$title = 'Pencapaian Saya - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-medal text-info"></i> Pencapaian Saya</h1>
        <p class="text-muted">Lihat semua pencapaian yang telah Anda raih dan yang belum</p>
    </div>
    <div class="text-end">
        <span class="badge bg-success fs-6">
            <i class="fas fa-check-circle"></i> <?php echo count(array_filter($achievements, function($a){ return $a['is_earned']; })); ?> Diraih
        </span>
        <span class="badge bg-secondary fs-6 ms-2">
            <i class="fas fa-hourglass-half"></i> <?php echo count(array_filter($achievements, function($a){ return !$a['is_earned']; })); ?> Belum Diraih
        </span>
    </div>
</div>

<?php if(empty($achievements)): ?>
    <div class="text-center py-5">
        <i class="fas fa-trophy fa-4x text-muted mb-4"></i>
        <h3 class="text-muted">Belum Ada Pencapaian</h3>
        <p class="text-muted">Mulai bermain kuis dan raih pencapaian pertama Anda!</p>
        <a href="/public/index.php?controller=quiz&action=index" class="btn btn-primary">
            <i class="fas fa-brain"></i> Mulai Kuis
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach($achievements as $achievement): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 achievement-card <?php echo $achievement['is_earned'] ? 'earned' : ''; ?>">
                    <div class="card-body text-center">
                        <i class="fas fa-<?php echo htmlspecialchars($achievement['icon']); ?> fa-3x text-<?php echo htmlspecialchars($achievement['badge_color']); ?> mb-3"></i>
                        <h5 class="card-title"><?php echo htmlspecialchars($achievement['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($achievement['description']); ?></p>
                        <div class="mt-3">
                            <?php if($achievement['is_earned']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Diraih pada <?php echo date('d/m/Y', strtotime($achievement['earned_at'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-hourglass-half"></i> Belum Diraih
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-coins"></i> +<?php echo $achievement['points_reward']; ?> Poin
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="text-center mt-5">
    <a href="/public/index.php?controller=dashboard&action=index" class="btn btn-outline-primary">
        <i class="fas fa-home"></i> Kembali ke Dashboard
    </a>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

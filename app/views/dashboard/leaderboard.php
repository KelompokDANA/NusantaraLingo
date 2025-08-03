<?php 
$title = 'Leaderboard - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-trophy text-warning"></i> Leaderboard</h1>
        <p class="text-muted">Lihat ranking pemain terbaik NusantaraLingo</p>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6">
            <i class="fas fa-users"></i> <?php echo count($leaderboard); ?> Pemain Aktif
        </span>
    </div>
</div>

<!-- Current User Rank Card -->
<?php if(isset($user_rank) && isset($_SESSION['user_id'])): // Pastikan user_id ada di session ?>
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user"></i> Posisi Anda</h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="rank-badge rank-<?php echo $user_rank <= 3 ? 'top' : 'normal'; ?>">
                    #<?php echo $user_rank; ?>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <img src="/public/images/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>" 
                     alt="Avatar" class="rounded-circle border border-primary border-3" width="80" height="80">
            </div>
            <div class="col-md-4">
                <h4 class="mb-1"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Pengguna'); ?></h4>
                <p class="text-muted mb-2">@<?php echo htmlspecialchars($_SESSION['username'] ?? 'pengguna'); ?></p>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning">Level <?php echo $_SESSION['level'] ?? 1; ?></span>
                    <span class="badge bg-success"><?php echo number_format($_SESSION['total_points'] ?? 0); ?> Poin</span>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <?php if($user_rank <= 3): ?>
                    <i class="fas fa-crown fa-3x text-warning"></i>
                    <p class="text-warning fw-bold mb-0">TOP 3!</p>
                <?php elseif($user_rank <= 10): ?>
                    <i class="fas fa-medal fa-3x text-info"></i>
                    <p class="text-info fw-bold mb-0">TOP 10!</p>
                <?php else: ?>
                    <i class="fas fa-star fa-3x text-secondary"></i>
                    <p class="text-muted mb-0">Terus semangat!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Leaderboard Table -->
<div class="card shadow-lg">
    <div class="card-header bg-gradient-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Pemain NusantaraLingo</h5>
    </div>
    <div class="card-body p-0">
        <?php if(empty($leaderboard)): ?>
            <div class="text-center py-5">
                <i class="fas fa-trophy fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">Belum Ada Data Leaderboard</h3>
                <p class="text-muted">Mulai bermain kuis untuk masuk ke leaderboard!</p>
                <a href="/public/index.php?controller=quiz&action=index" class="btn btn-primary">
                    <i class="fas fa-brain"></i> Mulai Kuis
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="80">Rank</th>
                            <th width="100">Avatar</th>
                            <th>Nama Pemain</th>
                            <th width="120">Level</th>
                            <th width="150">Total Poin</th>
                            <th width="120">Experience</th>
                            <th width="100">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($leaderboard as $index => $player): ?>
                            <tr class="<?php echo (isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']) ? 'table-primary' : ''; ?> leaderboard-item">
                                <td class="text-center">
                                    <?php if($index == 0): ?>
                                        <div class="rank-badge rank-1">
                                            <i class="fas fa-crown text-warning"></i>
                                            <div class="rank-number">1</div>
                                        </div>
                                    <?php elseif($index == 1): ?>
                                        <div class="rank-badge rank-2">
                                            <i class="fas fa-medal text-secondary"></i>
                                            <div class="rank-number">2</div>
                                        </div>
                                    <?php elseif($index == 2): ?>
                                        <div class="rank-badge rank-3">
                                            <i class="fas fa-medal text-warning"></i>
                                            <div class="rank-number">3</div>
                                        </div>
                                    <?php else: ?>
                                        <div class="rank-badge rank-normal">
                                            <div class="rank-number"><?php echo $index + 1; ?></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <img src="/public/images/avatars/<?php echo $player['avatar'] ?? 'default.png'; ?>" 
                                         alt="Avatar" class="rounded-circle <?php echo (isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']) ? 'border border-primary border-2' : ''; ?>" 
                                         width="50" height="50">
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-1 <?php echo (isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']) ? 'text-primary fw-bold' : ''; ?>">
                                            <?php echo htmlspecialchars($player['full_name']); ?>
                                            <?php if(isset($_SESSION['user_id']) && $player['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge bg-primary ms-2">Anda</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($player['username']); ?></small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $player['level'] >= 5 ? 'danger' : ($player['level'] >= 3 ? 'warning' : 'success'); ?> fs-6">
                                        <i class="fas fa-star"></i> <?php echo $player['level']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-success fs-5">
                                            <?php echo number_format($player['total_points']); ?>
                                        </span>
                                        <small class="text-muted">poin</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="progress mb-1" style="height: 8px;">
                                        <?php 
                                        $exp_progress = ($player['experience'] % 100);
                                        $exp_percentage = ($exp_progress / 100) * 100;
                                        ?>
                                        <div class="progress-bar bg-info" style="width: <?php echo $exp_percentage; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $exp_progress; ?>/100 XP</small>
                                </td>
                                <td class="text-center">
                                    <?php if($index < 3): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-fire"></i> Hot
                                        </span>
                                    <?php elseif($index < 10): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-thumbs-up"></i> Top
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-user"></i> Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x mb-3"></i>
                <h3><?php echo count($leaderboard); ?></h3>
                <p class="mb-0">Total Pemain</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white text-center">
            <div class="card-body">
                <i class="fas fa-trophy fa-2x mb-3"></i>
                <h3><?php echo !empty($leaderboard) ? number_format($leaderboard[0]['total_points']) : '0'; ?></h3>
                <p class="mb-0">Poin Tertinggi</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark text-center">
            <div class="card-body">
                <i class="fas fa-star fa-2x mb-3"></i>
                <h3><?php echo !empty($leaderboard) ? $leaderboard[0]['level'] : '0'; ?></h3>
                <p class="mb-0">Level Tertinggi</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white text-center">
            <div class="card-body">
                <i class="fas fa-fire fa-2x mb-3"></i>
                <h3><?php echo isset($user_rank) ? $user_rank : 'N/A'; ?></h3>
                <p class="mb-0">Posisi Anda</p>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="text-center mt-4">
    <div class="btn-group" role="group">
        <a href="/public/index.php?controller=dashboard&action=index" class="btn btn-outline-primary">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/public/index.php?controller=quiz&action=index" class="btn btn-primary">
            <i class="fas fa-brain"></i> Mulai Kuis
        </a>
        <a href="/public/index.php?controller=dashboard&action=achievements" class="btn btn-outline-warning">
            <i class="fas fa-medal"></i> Pencapaian
        </a>
    </div>
</div>

<style>
.rank-badge {
    position: relative;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    font-weight: bold;
}

.rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #b45309;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
}

.rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #e5e7eb);
    color: #374151;
    box-shadow: 0 4px 15px rgba(192, 192, 192, 0.4);
}

.rank-3 {
    background: linear-gradient(135deg, #cd7f32, #d97706);
    color: white;
    box-shadow: 0 4px 15px rgba(205, 127, 50, 0.4);
}

.rank-normal {
    background: linear-gradient(135deg, #6b7280, #9ca3af);
    color: white;
}

.rank-top {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.rank-number {
    font-size: 1.2rem;
    font-weight: bold;
}

.leaderboard-item:hover {
    background-color: rgba(59, 130, 246, 0.1) !important;
    transform: translateX(5px);
    transition: all 0.3s ease;
}

.table-primary {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>

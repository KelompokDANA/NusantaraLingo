<?php 
$title = 'Riwayat Kuis - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-history text-primary"></i> Riwayat Kuis Anda</h1>
        <p class="text-muted">Lihat semua kuis yang pernah Anda selesaikan</p>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6">
            <i class="fas fa-brain"></i> <?php echo count($quiz_history); ?> Kuis Selesai
        </span>
    </div>
</div>

<?php if(empty($quiz_history)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
        <h3 class="text-muted">Belum Ada Riwayat Kuis</h3>
        <p class="text-muted">Mulai kerjakan kuis untuk melihat riwayat Anda di sini!</p>
        <a href="/public/index.php?controller=quiz&action=index" class="btn btn-primary">
            <i class="fas fa-play"></i> Mulai Kuis Sekarang
        </a>
    </div>
<?php else: ?>
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list-alt"></i> Daftar Riwayat Kuis</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kuis</th>
                            <th>Bahasa & Kategori</th>
                            <th class="text-center">Skor</th>
                            <th class="text-center">Benar</th>
                            <th class="text-center">Waktu</th>
                            <th class="text-center">Persentase</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($quiz_history as $index => $attempt): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($attempt['quiz_title']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($attempt['language_name']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($attempt['category_name']); ?></span>
                                </td>
                                <td class="text-center fw-bold text-success"><?php echo $attempt['score']; ?></td>
                                <td class="text-center"><?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?></td>
                                <td class="text-center"><?php echo gmdate("i:s", $attempt['time_taken']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $attempt['percentage'] >= 80 ? 'success' : ($attempt['percentage'] >= 60 ? 'warning' : 'danger'); ?>">
                                        <?php echo round($attempt['percentage']); ?>%
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($attempt['completed_at'])); ?></td>
                                <td class="text-center">
                                    <a href="/public/index.php?controller=quiz&action=result&attempt_id=<?php echo $attempt['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php 
$title = 'Detail Kuis - ' . htmlspecialchars($quiz_data['title']);
include __DIR__ . '/../layout/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%); color: white;">
                <i class="fas fa-<?php echo $quiz_data['category_icon']; ?> fa-3x mb-3" style="color: white;"></i>
                <h2 class="mb-2" style="color: white;"><?php echo htmlspecialchars($quiz_data['title']); ?></h2>
                <p class="mb-0" style="color: rgba(255,255,255,0.9);"><?php echo htmlspecialchars($quiz_data['description']); ?></p>
            </div>
            
            <div class="card-body p-4">
                <!-- Quiz Info -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <i class="fas fa-globe-asia fa-2x text-info me-3"></i>
                            <div>
                                <h6 class="mb-1" style="color: #1e293b;">Bahasa Daerah</h6>
                                <p class="mb-0" style="color: #64748b;"><?php echo htmlspecialchars($quiz_data['language_name']); ?></p>
                                <small style="color: #64748b;"><?php echo htmlspecialchars($quiz_data['region']); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <i class="fas fa-<?php echo $quiz_data['category_icon']; ?> fa-2x text-success me-3"></i>
                            <div>
                                <h6 class="mb-1" style="color: #1e293b;">Kategori</h6>
                                <p class="mb-0" style="color: #64748b;"><?php echo htmlspecialchars($quiz_data['category_name']); ?></p>
                                <span class="badge bg-<?php echo $quiz_data['difficulty_level'] == 'pemula' ? 'success' : ($quiz_data['difficulty_level'] == 'menengah' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($quiz_data['difficulty_level']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Statistics -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 border rounded" style="background-color: #f8fafc;">
                            <i class="fas fa-question-circle fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1" style="color: #1e293b;"><?php echo $quiz_data['total_questions']; ?></h4>
                            <small style="color: #64748b;">Soal</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 border rounded" style="background-color: #f8fafc;">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1" style="color: #1e293b;"><?php echo $quiz_data['time_limit']; ?></h4>
                            <small style="color: #64748b;">Detik</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 border rounded" style="background-color: #f8fafc;">
                            <i class="fas fa-coins fa-2x text-success mb-2"></i>
                            <h4 class="mb-1" style="color: #1e293b;"><?php echo $quiz_data['points_per_question']; ?></h4>
                            <small style="color: #64748b;">Poin/Soal</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 border rounded" style="background-color: #f8fafc;">
                            <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1" style="color: #1e293b;"><?php echo $quiz_data['points_per_question'] * $quiz_data['total_questions']; ?></h4>
                            <small style="color: #64748b;">Max Poin</small>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="alert alert-info" style="background-color: #dbeafe; border: 1px solid #93c5fd; color: #1e40af;">
                    <h5 style="color: #1e40af;"><i class="fas fa-info-circle"></i> Petunjuk Kuis:</h5>
                    <ul class="mb-0" style="color: #1e40af;">
                        <li>Kuis ini berisi <strong><?php echo $quiz_data['total_questions']; ?> soal</strong> pilihan ganda</li>
                        <li>Waktu yang tersedia: <strong><?php echo $quiz_data['time_limit']; ?> detik</strong></li>
                        <li>Setiap jawaban benar mendapat <strong><?php echo $quiz_data['points_per_question']; ?> poin</strong></li>
                        <li>Jawaban akan otomatis tersimpan setiap 30 detik</li>
                        <li>Kuis akan otomatis selesai jika waktu habis</li>
                        <li>Anda bisa mendapat pencapaian baru dari kuis ini!</li>
                    </ul>
                </div>

                <!-- User Stats (if available) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h5>Level <?php echo $_SESSION['level']; ?></h5>
                                <small>Level Anda Saat Ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-coins fa-2x mb-2"></i>
                                <h5><?php echo number_format($_SESSION['total_points']); ?></h5>
                                <small>Total Poin Anda</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-fire fa-2x mb-2"></i>
                                <h5><?php echo $_SESSION['streak_days'] ?? 0; ?></h5>
                                <small>Hari Berturut</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="/public/index.php?controller=quiz&action=index" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kuis
                    </a>
                    <div class="text-end">
                        <p class="mb-2 text-muted">Siap untuk memulai?</p>
                        <a href="/public/index.php?controller=quiz&action=start&id=<?php echo $quiz_data['id']; ?>" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-play"></i> Mulai Kuis Sekarang!
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

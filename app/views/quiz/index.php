<?php 
$title = 'Daftar Kuis - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-brain text-primary"></i> Daftar Kuis Bahasa Daerah</h1>
        <p class="text-muted">Pilih kuis bahasa daerah yang ingin Anda pelajari</p>
    </div>
    <div class="text-end">
        <span class="badge bg-info fs-6">
            <i class="fas fa-list"></i> <?php echo count($quizzes); ?> Kuis Tersedia
        </span>
    </div>
</div>

<?php if(empty($quizzes)): ?>
    <div class="text-center py-5">
        <i class="fas fa-brain fa-4x text-muted mb-4"></i>
        <h3 class="text-muted">Belum Ada Kuis Tersedia</h3>
        <p class="text-muted">Kuis sedang dalam tahap pengembangan. Silakan kembali lagi nanti!</p>
        <a href="/public/index.php?controller=dashboard&action=index" class="btn btn-primary">
            <i class="fas fa-home"></i> Kembali ke Dashboard
        </a>
    </div>
<?php else: ?>
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter Bahasa:</label>
                    <select class="form-select" id="languageFilter">
                        <option value="">Semua Bahasa</option>
                        <?php 
                        $languages = array_unique(array_column($quizzes, 'language_name'));
                        foreach($languages as $lang): ?>
                            <option value="<?php echo htmlspecialchars($lang); ?>"><?php echo htmlspecialchars($lang); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Kategori:</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">Semua Kategori</option>
                        <?php 
                        $categories = array_unique(array_column($quizzes, 'category_name'));
                        foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Tingkat:</label>
                    <select class="form-select" id="difficultyFilter">
                        <option value="">Semua Tingkat</option>
                        <option value="pemula">Pemula</option>
                        <option value="menengah">Menengah</option>
                        <option value="mahir">Mahir</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Grid -->
    <div class="row g-4" id="quizGrid">
        <?php foreach($quizzes as $quiz): ?>
            <div class="col-lg-4 col-md-6 quiz-item" 
                 data-language="<?php echo htmlspecialchars($quiz['language_name']); ?>"
                 data-category="<?php echo htmlspecialchars($quiz['category_name']); ?>"
                 data-difficulty="<?php echo htmlspecialchars($quiz['difficulty_level']); ?>">
                <div class="card h-100 quiz-card shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-<?php echo $quiz['category_icon']; ?>"></i>
                                <?php echo htmlspecialchars($quiz['title']); ?>
                            </h5>
                            <span class="badge bg-white text-dark">
                                <?php echo ucfirst($quiz['difficulty_level']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-info me-2">
                                <i class="fas fa-globe"></i> <?php echo htmlspecialchars($quiz['language_name']); ?>
                            </span>
                            <span class="badge bg-secondary">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($quiz['region']); ?>
                            </span>
                        </div>
                        
                        <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-question-circle text-primary me-2"></i>
                                    <small><strong><?php echo $quiz['total_questions']; ?></strong> Soal</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <small><strong><?php echo $quiz['time_limit']; ?></strong> Detik</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-coins text-success me-2"></i>
                                    <small><strong><?php echo $quiz['points_per_question']; ?></strong> Poin/Soal</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-trophy text-warning me-2"></i>
                                    <small>Max <strong><?php echo $quiz['points_per_question'] * $quiz['total_questions']; ?></strong> Poin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-grid">
                            <a href="/public/index.php?controller=quiz&action=show&id=<?php echo $quiz['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-play"></i> Mulai Kuis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="text-center py-5" style="display: none;">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Tidak Ada Kuis Ditemukan</h4>
        <p class="text-muted">Coba ubah filter pencarian Anda</p>
    </div>
<?php endif; ?>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const languageFilter = document.getElementById('languageFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const quizItems = document.querySelectorAll('.quiz-item');
    const noResults = document.getElementById('noResults');

    function filterQuizzes() {
        const selectedLanguage = languageFilter.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();
        const selectedDifficulty = difficultyFilter.value.toLowerCase();
        
        let visibleCount = 0;

        quizItems.forEach(item => {
            const language = item.dataset.language.toLowerCase();
            const category = item.dataset.category.toLowerCase();
            const difficulty = item.dataset.difficulty.toLowerCase();

            const languageMatch = !selectedLanguage || language.includes(selectedLanguage);
            const categoryMatch = !selectedCategory || category.includes(selectedCategory);
            const difficultyMatch = !selectedDifficulty || difficulty === selectedDifficulty;

            if (languageMatch && categoryMatch && difficultyMatch) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (visibleCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    languageFilter.addEventListener('change', filterQuizzes);
    categoryFilter.addEventListener('change', filterQuizzes);
    difficultyFilter.addEventListener('change', filterQuizzes);
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

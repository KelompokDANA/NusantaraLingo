<?php 
$title = 'Kuis: ' . htmlspecialchars($quiz_data['title']);
include __DIR__ . '/../layout/header.php'; 
?>

<!-- Quiz Timer (Fixed Position) -->
<div id="quiz-timer" class="quiz-timer">
    <i class="fas fa-clock"></i> <span id="time-display">00:00</span>
</div>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Quiz Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1"><?php echo htmlspecialchars($quiz_data['title']); ?></h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-globe"></i> <?php echo htmlspecialchars($quiz_data['language_name']); ?> - 
                                <i class="fas fa-<?php echo $quiz_data['category_icon']; ?>"></i> <?php echo htmlspecialchars($quiz_data['category_name']); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="quiz-progress"></div>
                            </div>
                            <small class="text-muted">
                                <span id="current-question">1</span> dari <?php echo count($questions); ?> soal
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz Form -->
            <form id="quiz-form" method="POST" action="/public/index.php?controller=quiz&action=submit" 
                  data-quiz-id="<?php echo $quiz_data['id']; ?>" onsubmit="return validateQuizForm()">
                
                <?php foreach($questions as $index => $question): ?>
                    <div class="card mb-4 question-card" data-question="<?php echo $index + 1; ?>">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                <?php echo htmlspecialchars($question['question_text']); ?>
                            </h5>
                            <?php if($question['pronunciation']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-volume-up"></i> Pelafalan: <?php echo htmlspecialchars($question['pronunciation']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if($question['usage_example']): ?>
                                <div class="alert alert-info mb-3">
                                    <small><strong>Contoh penggunaan:</strong> <?php echo htmlspecialchars($question['usage_example']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row g-3">
                                <?php 
                                $options = [
                                    'A' => $question['option_a'],
                                    'B' => $question['option_b'], 
                                    'C' => $question['option_c'],
                                    'D' => $question['option_d']
                                ];
                                foreach($options as $key => $option): 
                                ?>
                                    <div class="col-md-6">
                                        <div class="option-card p-3 border rounded" 
                                             data-question="<?php echo $question['id']; ?>"
                                             onclick="selectOption(<?php echo $question['id']; ?>, '<?php echo htmlspecialchars($option); ?>')">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="answers[<?php echo $question['id']; ?>]" 
                                                       value="<?php echo htmlspecialchars($option); ?>" 
                                                       id="q<?php echo $question['id']; ?>_<?php echo $key; ?>">
                                                <label class="form-check-label w-100" for="q<?php echo $question['id']; ?>_<?php echo $key; ?>">
                                                    <strong><?php echo $key; ?>.</strong> <?php echo htmlspecialchars($option); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Submit Section - OPTIMIZED -->
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">
                            <i class="fas fa-check-circle text-success"></i> 
                            Selesai mengerjakan kuis?
                        </h5>
                        <p class="text-muted mb-4">
                            Pastikan semua soal sudah dijawab sebelum mengirim jawaban Anda.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="/public/index.php?controller=quiz&action=index" 
                               class="btn btn-outline-secondary"
                               onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Membatalkan...'; this.disabled=true;">
                                <i class="fas fa-times"></i> Batalkan Kuis
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="submitQuizBtn">
                                <i class="fas fa-paper-plane"></i> Kirim Jawaban
                            </button>
                        </div>
                        
                        <!-- Loading indicator -->
                        <div id="loadingIndicator" class="mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Sedang memproses jawaban Anda...</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Start quiz timer
let timeLimit = <?php echo $quiz_data['time_limit']; ?>;
startQuizTimer(timeLimit);

// Update progress bar
function updateProgress() {
    const totalQuestions = <?php echo count($questions); ?>;
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    const progress = (answeredQuestions / totalQuestions) * 100;
    
    document.getElementById('quiz-progress').style.width = progress + '%';
    document.getElementById('current-question').textContent = answeredQuestions + 1;
}

// Auto-save answers every 30 seconds
setInterval(autoSaveQuizAnswers, 30000);

// Update progress when answer is selected
document.addEventListener('change', function(e) {
    if (e.target.type === 'radio') {
        updateProgress();
    }
});

// Scroll to next question after selecting answer
function selectOption(questionId, optionValue) {
    // Remove selected class from all options for this question
    const options = document.querySelectorAll(`[data-question="${questionId}"]`);
    options.forEach((option) => {
        option.classList.remove('selected');
    });

    // Add selected class to clicked option
    event.target.closest('.option-card').classList.add('selected');

    // Set the radio button value
    const radioButton = document.querySelector(`input[name="answers[${questionId}]"][value="${optionValue}"]`);
    if (radioButton) {
        radioButton.checked = true;
    }

    // Update progress
    updateProgress();

    // Auto-scroll to next question after 1 second
    setTimeout(() => {
        const currentCard = event.target.closest('.question-card');
        const nextCard = currentCard.nextElementSibling;
        if (nextCard && nextCard.classList.contains('question-card')) {
            nextCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 1000);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

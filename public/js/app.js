// NusantaraLingo JavaScript Functions

// Quiz Timer
let quizTimer
let timeRemaining

function startQuizTimer(duration) {
  timeRemaining = duration
  const timerElement = document.getElementById("quiz-timer")

  quizTimer = setInterval(() => {
    const minutes = Math.floor(timeRemaining / 60)
    const seconds = timeRemaining % 60

    document.getElementById("time-display").innerHTML =
      `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`

    // Warning when less than 1 minute
    if (timeRemaining <= 60) {
      timerElement.classList.add("warning")
    }

    // Auto submit when time is up
    if (timeRemaining <= 0) {
      clearInterval(quizTimer)
      autoSubmitQuiz()
    }

    timeRemaining--
  }, 1000)
}

function autoSubmitQuiz() {
  alert("Waktu habis! Kuis akan otomatis dikirim.")
  document.getElementById("quiz-form").submit()
}

// Quiz Option Selection
function selectOption(questionId, optionValue) {
  // Remove selected class from all options for this question
  const options = document.querySelectorAll(`[data-question="${questionId}"]`)
  options.forEach((option) => {
    option.classList.remove("selected")
  })

  // Add selected class to clicked option
  event.target.closest(".option-card").classList.add("selected")

  // Set the radio button value
  const radioButton = document.querySelector(`input[name="answers[${questionId}]"][value="${optionValue}"]`)
  if (radioButton) {
    radioButton.checked = true
  }
}

// Form Validation
function validateQuizForm() {
  const form = document.getElementById("quiz-form")
  if (!form) return true

  const submitButton = form.querySelector('button[type="submit"]')
  const questions = form.querySelectorAll('[name^="answers"]')
  let answeredCount = 0

  questions.forEach((question) => {
    if (question.checked) {
      answeredCount++
    }
  })

  const totalQuestions = questions.length / 4 // 4 options per question

  if (answeredCount < totalQuestions) {
    const unanswered = totalQuestions - answeredCount
    const confirmSubmit = confirm(`Anda belum menjawab ${unanswered} soal. Yakin ingin mengirim jawaban?`)

    if (!confirmSubmit) {
      return false
    }
  }

  // Show loading state
  if (submitButton) {
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim Jawaban...'
    submitButton.disabled = true

    // Timeout protection
    setTimeout(() => {
      if (submitButton.disabled) {
        submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Jawaban'
        submitButton.disabled = false
        alert("Terjadi timeout. Silakan coba lagi.")
      }
    }, 15000) // 15 second timeout
  }

  return true
}

// Auto-save quiz answers
function autoSaveQuizAnswers() {
  const form = document.getElementById("quiz-form")
  if (!form) return

  const formData = new FormData(form)
  const answers = {}
  let hasAnswers = false

  for (const [key, value] of formData.entries()) {
    if (key.startsWith("answers[")) {
      answers[key] = value
      hasAnswers = true
    }
  }

  // Only save if there are answers
  if (hasAnswers) {
    const quizId = form.dataset.quizId
    try {
      localStorage.setItem(`quiz_${quizId}_answers`, JSON.stringify(answers))
    } catch (e) {
      console.warn("Could not save to localStorage:", e)
    }
  }
}

// Load saved quiz answers
function loadSavedQuizAnswers() {
  const form = document.getElementById("quiz-form")
  if (!form) return

  const quizId = form.dataset.quizId
  const savedAnswers = JSON.parse(localStorage.getItem(`quiz_${quizId}_answers`) || "{}")

  for (const [key, value] of Object.entries(savedAnswers)) {
    const input = form.querySelector(`[name="${key}"][value="${value}"]`)
    if (input) {
      input.checked = true
      // Update UI
      const optionCard = input.closest(".option-card")
      if (optionCard) {
        optionCard.classList.add("selected")
      }
    }
  }
}

// Show Toast Notification
function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`
  toast.style.zIndex = "9999"
  toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `

  document.body.appendChild(toast)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.remove()
    }
  }, 5000)
}

// Initialize functions when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  loadSavedQuizAnswers()

  // Auto-save quiz answers every 60 seconds (reduced from 30)
  if (document.getElementById("quiz-form")) {
    setInterval(autoSaveQuizAnswers, 60000)
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  const bootstrap = window.bootstrap
  if (bootstrap) {
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  }
})

// Keyboard shortcuts
document.addEventListener("keydown", (event) => {
  // Ctrl + Enter to submit quiz
  if (event.ctrlKey && event.key === "Enter") {
    const submitButton = document.querySelector('button[type="submit"]')
    if (submitButton && !submitButton.disabled) {
      submitButton.click()
    }
  }

  // Escape to go back
  if (event.key === "Escape") {
    const backButton = document.querySelector(".btn-secondary")
    if (backButton) {
      backButton.click()
    }
  }
})

// Prevent accidental page refresh during quiz
window.addEventListener("beforeunload", (event) => {
  if (document.getElementById("quiz-form") && timeRemaining > 0) {
    event.preventDefault()
    event.returnValue = "Anda sedang mengerjakan kuis. Yakin ingin meninggalkan halaman?"
    return event.returnValue
  }
})

// Add loading indicator for cancel button
function showCancelLoading() {
  const cancelButtons = document.querySelectorAll(".btn-secondary")
  cancelButtons.forEach((btn) => {
    if (btn.textContent.includes("Batalkan")) {
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membatalkan...'
      btn.disabled = true
    }
  })
}

// Add event listener for cancel buttons
document.addEventListener("click", (e) => {
  if (e.target.closest(".btn-secondary") && e.target.textContent.includes("Batalkan")) {
    showCancelLoading()
  }
})

<?php 
$title = 'Login - NusantaraLingo';
include __DIR__ . '/../layout/header.php'; 
?>

<div class="min-vh-100 d-flex align-items-center bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-globe-asia text-primary" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold text-primary mt-2">NusantaraLingo</h2>
                            <p class="text-muted">Jelajahi Kekayaan Bahasa Nusantara</p>
                        </div>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/public/index.php?controller=auth&action=authenticate">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username atau Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           placeholder="Masukkan username atau email" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Masukkan password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Masuk
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="mb-3">Belum punya akun? 
                                <a href="/public/index.php?controller=auth&action=register" class="text-primary fw-bold">
                                    Daftar Sekarang
                                </a>
                            </p>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <h6 class="text-muted mb-3">Demo Akun:</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button class="btn btn-outline-primary btn-sm w-100" onclick="fillDemo('budi123', 'password123')">
                                        <i class="fas fa-user"></i> Budi
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-success btn-sm w-100" onclick="fillDemo('maya_smart', 'password123')">
                                        <i class="fas fa-crown"></i> Maya
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function fillDemo(username, password) {
    document.getElementById('username').value = username;
    document.getElementById('password').value = password;
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

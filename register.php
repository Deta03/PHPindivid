<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $confirm  =      $_POST['confirm']  ?? '';

    if (strlen($username) < 3)                    $errors[] = 'Имя пользователя минимум 3 символа';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (strlen($password) < 6)                    $errors[] = 'Пароль минимум 6 символов';
    if ($password !== $confirm)                    $errors[] = 'Пароли не совпадают';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) $errors[] = 'Такой логин или email уже существует';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);

        $_SESSION['user_id']  = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'user';
        header('Location: index.php');
        exit;
    }
}

require 'includes/header.php';
?>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <h2 class="mb-4 text-center">Регистрация</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="regForm">
                    <div class="mb-3">
                        <label class="form-label">Имя пользователя</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Повтор пароля</label>
                        <input type="password" name="confirm" id="confirm" class="form-control" required>
                        <div id="confirmError" class="text-danger small mt-1"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    <p class="mt-3 text-center text-white-50">Уже есть аккаунт? <a href="login.php">Войти</a></p>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('regForm').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const conf = document.getElementById('confirm').value;
            const err  = document.getElementById('confirmError');
            if (pass !== conf) {
                err.textContent = 'Пароли не совпадают';
                e.preventDefault();
            } else {
                err.textContent = '';
            }
        });
    </script>

<?php require 'includes/footer.php'; ?>
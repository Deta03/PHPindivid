<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}

require 'includes/header.php';
?>

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4">
                <h2 class="mb-4 text-center">Вход</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                    <p class="mt-3 text-center text-white-50">Нет аккаунта? <a href="register.php">Регистрация</a></p>
                </form>

                <hr class="border-secondary">
                <p class="text-white-50 small text-center">Тест: admin@mail.com / password</p>
            </div>
        </div>
    </div>

<?php require 'includes/footer.php'; ?>
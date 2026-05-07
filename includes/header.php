<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🎬 MovieDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f0f; color: #eee; }
        .navbar { background: #1a1a2e !important; }
        .card { background: #1e1e2e; border: 1px solid #333; color: #eee; }
        .card:hover { border-color: #e50914; transform: translateY(-3px); transition: .2s; }
        .badge-genre { background: #e50914; }
        .btn-primary { background: #e50914; border-color: #e50914; }
        .btn-primary:hover { background: #b20710; border-color: #b20710; }
        .form-control, .form-select { background: #2a2a3e; border-color: #444; color: #eee; }
        .form-control:focus, .form-select:focus { background: #2a2a3e; color: #eee; border-color: #e50914; box-shadow: 0 0 0 .2rem rgba(229,9,20,.25); }
        .table { color: #eee; }
        .star { color: #f5c518; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark px-4 mb-4">
    <a class="navbar-brand fw-bold" href="index.php">🎬 MovieDB</a>
    <div class="d-flex align-items-center gap-2">
        <a href="search.php" class="btn btn-outline-light btn-sm">🔍 Поиск</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-white-50">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="btn btn-warning btn-sm">⚙ Админ</a>
            <?php endif; ?>
            <a href="add_movie.php" class="btn btn-success btn-sm">+ Фильм</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-light btn-sm">Войти</a>
            <a href="register.php" class="btn btn-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">

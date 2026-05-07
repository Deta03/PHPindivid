<?php
session_start();
require 'db.php';

// Топ фильмов по рейтингу
$top = $pdo->query("SELECT * FROM movies ORDER BY rating DESC LIMIT 6")->fetchAll();

// Новинки
$new = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Статистика по жанрам
$genres = $pdo->query("SELECT genre, COUNT(*) as cnt FROM movies GROUP BY genre ORDER BY cnt DESC")->fetchAll();

require 'includes/header.php';
?>

    <!-- Герой -->
    <div class="p-5 mb-4 rounded-3" style="background: linear-gradient(135deg,#1a1a2e,#16213e);">
        <h1 class="display-5 fw-bold">🎬 Каталог фильмов</h1>
        <p class="lead text-white-50">Лучшие фильмы, добавленные пользователями</p>
        <a href="search.php" class="btn btn-primary btn-lg">Найти фильм</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-outline-light btn-lg ms-2">Присоединиться</a>
        <?php endif; ?>
    </div>

    <!-- Жанры -->
    <div class="mb-5">
        <h4 class="mb-3">Жанры</h4>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($genres as $g): ?>
                <a href="search.php?genre=<?= urlencode($g['genre']) ?>" class="btn btn-outline-secondary btn-sm">
                    <?= htmlspecialchars($g['genre']) ?> <span class="badge bg-secondary"><?= $g['cnt'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Топ по рейтингу -->
    <h4 class="mb-3">⭐ Топ по рейтингу</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <?php foreach ($top as $m): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($m['poster_url']): ?>
                        <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-img-top" style="height:200px;object-fit:cover" alt="">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center" style="height:200px;background:#2a2a3e;font-size:4rem;">🎬</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($m['title']) ?></h5>
                        <p class="text-white-50 small"><?= htmlspecialchars($m['director']) ?> · <?= $m['year'] ?></p>
                        <span class="badge badge-genre"><?= htmlspecialchars($m['genre']) ?></span>
                        <span class="ms-2 star">★ <?= $m['rating'] ?>/10</span>
                    </div>
                    <div class="card-footer">
                        <a href="movie.php?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm w-100">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Новинки -->
    <h4 class="mb-3">🆕 Последние добавленные</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <?php foreach ($new as $m): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($m['poster_url']): ?>
                        <img src="<?= htmlspecialchars($m['poster_url']) ?>" class="card-img-top" style="height:200px;object-fit:cover" alt="">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center" style="height:200px;background:#2a2a3e;font-size:4rem;">🎬</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($m['title']) ?></h5>
                        <p class="text-white-50 small"><?= htmlspecialchars($m['director']) ?> · <?= $m['year'] ?></p>
                        <span class="badge badge-genre"><?= htmlspecialchars($m['genre']) ?></span>
                    </div>
                    <div class="card-footer">
                        <a href="movie.php?id=<?= $m['id'] ?>" class="btn btn-primary btn-sm w-100">Подробнее</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php require 'includes/footer.php'; ?>
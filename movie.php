<?php
session_start();
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();
if (!$movie) { header('Location: index.php'); exit; }

$errors = [];

// Добавление отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $text = trim($_POST['text'] ?? '');
    if (strlen($text) < 5) {
        $errors[] = 'Отзыв слишком короткий';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (movie_id, user_id, text) VALUES (?, ?, ?)");
        $stmt->execute([$id, $_SESSION['user_id'], $text]);
        header("Location: movie.php?id=$id");
        exit;
    }
}

// Отзывы к фильму
$reviews = $pdo->prepare("
    SELECT r.*, u.username FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.movie_id = ?
    ORDER BY r.created_at DESC
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

require 'includes/header.php';
?>

    <div class="row">
        <div class="col-md-3 mb-4">
            <?php if ($movie['poster_url']): ?>
                <img src="<?= htmlspecialchars($movie['poster_url']) ?>" class="img-fluid rounded" alt="">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center rounded" style="height:350px;background:#2a2a3e;font-size:6rem;">🎬</div>
            <?php endif; ?>
        </div>
        <div class="col-md-9">
            <h2><?= htmlspecialchars($movie['title']) ?></h2>
            <p class="text-white-50">
                <strong>Режиссёр:</strong> <?= htmlspecialchars($movie['director']) ?> &nbsp;|&nbsp;
                <strong>Год:</strong> <?= $movie['year'] ?> &nbsp;|&nbsp;
                <strong>Страна:</strong> <?= htmlspecialchars($movie['country']) ?> &nbsp;|&nbsp;
                <strong>Жанр:</strong> <span class="badge badge-genre"><?= htmlspecialchars($movie['genre']) ?></span>
            </p>
            <p class="display-6 star">★ <?= $movie['rating'] ?>/10</p>
            <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php?edit=<?= $movie['id'] ?>" class="btn btn-warning btn-sm">✏ Редактировать</a>
            <?php endif; ?>
        </div>
    </div>

    <hr class="border-secondary my-4">
    <h4>Отзывы (<?= count($reviews) ?>)</h4>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors[0]) ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" class="mb-4">
        <div class="mb-2">
            <textarea name="text" class="form-control" rows="3" placeholder="Ваш отзыв..." required></textarea>
        </div>
        <button class="btn btn-primary">Отправить отзыв</button>
    </form>
<?php else: ?>
    <p class="text-white-50"><a href="login.php">Войдите</a>, чтобы оставить отзыв.</p>
<?php endif; ?>

<?php foreach ($reviews as $r): ?>
    <div class="card mb-3">
        <div class="card-body">
            <p class="card-text"><?= nl2br(htmlspecialchars($r['text'])) ?></p>
            <small class="text-white-50">👤 <?= htmlspecialchars($r['username']) ?> · <?= date('d.m.Y', strtotime($r['created_at'])) ?></small>
        </div>
    </div>
<?php endforeach; ?>

<?php require 'includes/footer.php'; ?>
<?php
session_start();
require 'db.php';

// Только для авторизованных
// ТОЛЬКО ДЛЯ ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$errors  = [];
$success = '';
$genres  = ['Боевик','Комедия','Драма','Ужасы','Фантастика','Мультфильм'];

// Сохраняем введённые данные при ошибке
$f = ['title'=>'','director'=>'','genre'=>'','year'=>'','country'=>'','description'=>'','rating'=>5,'poster_url'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['title']       = trim($_POST['title']       ?? '');
    $f['director']    = trim($_POST['director']     ?? '');
    $f['genre']       = trim($_POST['genre']        ?? '');
    $f['year']        = trim($_POST['year']         ?? '');
    $f['country']     = trim($_POST['country']      ?? '');
    $f['description'] = trim($_POST['description']  ?? '');
    $f['rating']      = (int)($_POST['rating']      ?? 5);
    $f['poster_url']  = trim($_POST['poster_url']   ?? '');

    // Валидация на сервере
    if (strlen($f['title']) < 2)                     $errors[] = 'Введите название фильма';
    if (strlen($f['director']) < 2)                  $errors[] = 'Введите имя режиссёра';
    if (!in_array($f['genre'], $genres))             $errors[] = 'Выберите жанр';
    if (!preg_match('/^\d{4}$/', $f['year']) || $f['year'] < 1900 || $f['year'] > 2025)
        $errors[] = 'Укажите корректный год (1900–2025)';
    if ($f['rating'] < 1 || $f['rating'] > 10)      $errors[] = 'Рейтинг от 1 до 10';
    if ($f['poster_url'] && !filter_var($f['poster_url'], FILTER_VALIDATE_URL))
        $errors[] = 'Некорректная ссылка на постер';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, director, genre, year, country, description, rating, poster_url, added_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $f['title'], $f['director'], $f['genre'], $f['year'],
            $f['country'], $f['description'], $f['rating'],
            $f['poster_url'] ?: null, $_SESSION['user_id']
        ]);
        $newId = $pdo->lastInsertId();
        header("Location: movie.php?id=$newId");
        exit;
    }
}

require 'includes/header.php';
?>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4">
                <h2 class="mb-4">🎬 Добавить фильм</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="movieForm" novalidate>

                    <div class="mb-3">
                        <label class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($f['title']) ?>" required>
                        <div class="invalid-feedback">Введите название</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Режиссёр <span class="text-danger">*</span></label>
                        <input type="text" name="director" class="form-control" value="<?= htmlspecialchars($f['director']) ?>" required>
                        <div class="invalid-feedback">Введите режиссёра</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Жанр <span class="text-danger">*</span></label>
                            <select name="genre" class="form-select" required>
                                <option value="">— выберите —</option>
                                <?php foreach ($genres as $g): ?>
                                    <option value="<?= $g ?>" <?= $f['genre'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Год <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($f['year']) ?>" min="1900" max="2025" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Страна</label>
                            <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($f['country']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($f['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Рейтинг: <span id="ratingVal"><?= $f['rating'] ?></span>/10</label>
                        <input type="range" name="rating" id="ratingRange" class="form-range"
                               min="1" max="10" value="<?= $f['rating'] ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Ссылка на постер (URL)</label>
                        <input type="url" name="poster_url" class="form-control" value="<?= htmlspecialchars($f['poster_url']) ?>" placeholder="https://...">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Добавить фильм</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Обновляем число рядом с ползунком
        document.getElementById('ratingRange').addEventListener('input', function() {
            document.getElementById('ratingVal').textContent = this.value;
        });

        // Клиентская валидация перед отправкой
        document.getElementById('movieForm').addEventListener('submit', function(e) {
            let ok = true;
            const required = this.querySelectorAll('[required]');
            required.forEach(el => {
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    ok = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            const year = parseInt(this.querySelector('[name=year]').value);
            if (year < 1900 || year > 2025) {
                this.querySelector('[name=year]').classList.add('is-invalid');
                ok = false;
            }
            if (!ok) e.preventDefault();
        });
    </script>

<?php require 'includes/footer.php'; ?>
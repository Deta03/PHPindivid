<?php
session_start();
require 'db.php';

$q     = trim($_GET['q']     ?? '');
$genre = trim($_GET['genre'] ?? '');
$year  = trim($_GET['year']  ?? '');
$results = [];
$searched = false;

if ($q !== '' || $genre !== '' || $year !== '') {
    $searched = true;
    $sql    = "SELECT * FROM movies WHERE 1=1";
    $params = [];

    if ($q !== '') {
        $sql .= " AND (title LIKE ? OR director LIKE ?)";
        $like = "%$q%";
        $params[] = $like;
        $params[] = $like;
    }
    if ($genre !== '') {
        $sql .= " AND genre = ?";
        $params[] = $genre;
    }
    if ($year !== '') {
        $sql .= " AND year = ?";
        $params[] = (int)$year;
    }

    $sql .= " ORDER BY rating DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

$allGenres = $pdo->query("SELECT DISTINCT genre FROM movies ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

require 'includes/header.php';
?>

    <h2 class="mb-4">🔍 Поиск фильмов</h2>

    <form method="GET" class="card p-4 mb-4" id="searchForm">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Название или режиссёр</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Например: Нолан">
            </div>
            <div class="col-md-3">
                <label class="form-label">Жанр</label>
                <select name="genre" class="form-select">
                    <option value="">Все жанры</option>
                    <?php foreach ($allGenres as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Год</label>
                <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($year) ?>" placeholder="2020" min="1900" max="2025">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
                <a href="search.php" class="btn btn-outline-secondary">✕</a>
            </div>
        </div>
    </form>

<?php if ($searched): ?>
    <p class="text-white-50">Найдено: <strong><?= count($results) ?></strong> фильмов</p>

    <?php if (empty($results)): ?>
        <div class="text-center py-5 text-white-50">
            <p style="font-size:3rem">🎬</p>
            <p>Ничего не найдено. Попробуйте другой запрос.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($results as $m): ?>
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
    <?php endif; ?>
<?php endif; ?>

    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const q     = document.querySelector('[name=q]').value.trim();
            const genre = document.querySelector('[name=genre]').value;
            const year  = document.querySelector('[name=year]').value;
            if (!q && !genre && !year) {
                alert('Введите хотя бы один критерий поиска');
                e.preventDefault();
            }
        });
    </script>

<?php require 'includes/footer.php'; ?>
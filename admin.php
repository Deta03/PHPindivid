<?php
session_start();
require 'db.php';

// Только для админа
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$errors  = [];
$genres  = ['Боевик','Комедия','Драма','Ужасы','Фантастика','Мультфильм'];
$tab     = $_GET['tab'] ?? 'movies'; // movies | users | add_user

// ============================================================
// ДЕЙСТВИЯ С ФИЛЬМАМИ
// ============================================================

// Удалить фильм
if (isset($_GET['delete_movie'])) {
    $id = (int)$_GET['delete_movie'];
    $pdo->prepare("DELETE FROM movies WHERE id = ?")->execute([$id]);
    header('Location: admin.php?tab=movies&ok=movie_deleted');
    exit;
}

// Удалить пользователя
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id !== $_SESSION['user_id']) { // нельзя удалить себя
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header('Location: admin.php?tab=users&ok=user_deleted');
    exit;
}

// Редактировать фильм (загружаем данные)
$editMovie = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editMovie = $stmt->fetch();
    $tab = 'movies';
}

// Сохранить изменения фильма
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // --- Обновить фильм ---
    if ($_POST['action'] === 'update_movie') {
        $id          = (int)$_POST['id'];
        $title       = trim($_POST['title']       ?? '');
        $director    = trim($_POST['director']     ?? '');
        $genre       = trim($_POST['genre']        ?? '');
        $year        = (int)($_POST['year']        ?? 0);
        $country     = trim($_POST['country']      ?? '');
        $description = trim($_POST['description']  ?? '');
        $rating      = (int)($_POST['rating']      ?? 5);
        $poster_url  = trim($_POST['poster_url']   ?? '');

        if (strlen($title) < 2)           $errors[] = 'Введите название';
        if (strlen($director) < 2)        $errors[] = 'Введите режиссёра';
        if (!in_array($genre, $genres))   $errors[] = 'Выберите жанр';
        if ($year < 1900 || $year > 2025) $errors[] = 'Некорректный год';

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE movies SET title=?, director=?, genre=?, year=?, country=?, description=?, rating=?, poster_url=?
                WHERE id=?
            ");
            $stmt->execute([$title, $director, $genre, $year, $country, $description, $rating, $poster_url ?: null, $id]);
            header('Location: admin.php?tab=movies&ok=updated');
            exit;
        }
        // Если ошибки — показать форму снова
        $editMovie = ['id'=>$id,'title'=>$title,'director'=>$director,'genre'=>$genre,
                'year'=>$year,'country'=>$country,'description'=>$description,
                'rating'=>$rating,'poster_url'=>$poster_url];
        $tab = 'movies';
    }

    // --- Создать нового пользователя (в т.ч. admin) ---
    if ($_POST['action'] === 'add_user') {
        $tab      = 'add_user';
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';
        $role     =      $_POST['role']     ?? 'user';

        if (strlen($username) < 3)                      $errors[] = 'Имя минимум 3 символа';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
        if (strlen($password) < 6)                      $errors[] = 'Пароль минимум 6 символов';
        if (!in_array($role, ['user','admin']))          $errors[] = 'Некорректная роль';

        if (empty($errors)) {
            $check = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
            $check->execute([$username, $email]);
            if ($check->fetch()) {
                $errors[] = 'Логин или email уже занят';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)")
                        ->execute([$username, $email, $hash, $role]);
                header('Location: admin.php?tab=users&ok=user_created');
                exit;
            }
        }
    }

    // --- Изменить роль пользователя ---
    if ($_POST['action'] === 'change_role') {
        $uid  = (int)$_POST['user_id'];
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        if ($uid !== $_SESSION['user_id']) {
            $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $uid]);
        }
        header('Location: admin.php?tab=users&ok=role_changed');
        exit;
    }
}

// Данные для таблиц
$movies = $pdo->query("SELECT m.*, u.username FROM movies m LEFT JOIN users u ON u.id = m.added_by ORDER BY m.id DESC")->fetchAll();
$users  = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

require 'includes/header.php';
?>

<h2 class="mb-4">⚙ Админ-панель</h2>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">
        <?php $msgs = ['updated'=>'Фильм обновлён','movie_deleted'=>'Фильм удалён','user_deleted'=>'Пользователь удалён','user_created'=>'Пользователь создан','role_changed'=>'Роль изменена'];
        echo htmlspecialchars($msgs[$_GET['ok']] ?? 'Готово'); ?>
    </div>
<?php endif; ?>

<!-- Вкладки -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab==='movies'?'active':'' ?>" href="admin.php?tab=movies">
            🎬 Фильмы <span class="badge bg-secondary"><?= count($movies) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab==='users'?'active':'' ?>" href="admin.php?tab=users">
            👥 Пользователи <span class="badge bg-secondary"><?= count($users) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab==='add_user'?'active':'' ?>" href="admin.php?tab=add_user">
            ➕ Новый пользователь
        </a>
    </li>
</ul>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>


<!-- ===== ВКЛАДКА: ФИЛЬМЫ ===== -->
<?php if ($tab === 'movies'): ?>

    <?php if ($editMovie): ?>
        <!-- Форма редактирования фильма -->
        <div class="card p-4 mb-4">
            <h5>✏ Редактировать: <?= htmlspecialchars($editMovie['title']) ?></h5>
            <form method="POST">
                <input type="hidden" name="action" value="update_movie">
                <input type="hidden" name="id" value="<?= $editMovie['id'] ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Название</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editMovie['title']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Режиссёр</label>
                        <input type="text" name="director" class="form-control" value="<?= htmlspecialchars($editMovie['director']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Жанр</label>
                        <select name="genre" class="form-select">
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= $g ?>" <?= $editMovie['genre']===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Год</label>
                        <input type="number" name="year" class="form-control" value="<?= $editMovie['year'] ?>" min="1900" max="2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Страна</label>
                        <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($editMovie['country']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Рейтинг: <span id="rVal"><?= $editMovie['rating'] ?></span>/10</label>
                        <input type="range" name="rating" class="form-range" min="1" max="10" value="<?= $editMovie['rating'] ?>"
                               oninput="document.getElementById('rVal').textContent=this.value">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editMovie['description']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Постер (URL)</label>
                        <input type="url" name="poster_url" class="form-control" value="<?= htmlspecialchars($editMovie['poster_url'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success">💾 Сохранить</button>
                    <a href="admin.php?tab=movies" class="btn btn-outline-secondary">Отмена</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Таблица фильмов -->
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead><tr>
                <th>#</th><th>Название</th><th>Режиссёр</th><th>Жанр</th><th>Год</th><th>★</th><th>Добавил</th><th>Действия</th>
            </tr></thead>
            <tbody>
            <?php foreach ($movies as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['title']) ?></td>
                    <td><?= htmlspecialchars($m['director']) ?></td>
                    <td><span class="badge badge-genre"><?= htmlspecialchars($m['genre']) ?></span></td>
                    <td><?= $m['year'] ?></td>
                    <td class="star"><?= $m['rating'] ?></td>
                    <td><?= htmlspecialchars($m['username'] ?? '—') ?></td>
                    <td>
                        <a href="admin.php?edit=<?= $m['id'] ?>" class="btn btn-warning btn-sm">✏</a>
                        <a href="admin.php?delete_movie=<?= $m['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Удалить «<?= htmlspecialchars(addslashes($m['title'])) ?>»?')">🗑</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- ===== ВКЛАДКА: ПОЛЬЗОВАТЕЛИ ===== -->
<?php elseif ($tab === 'users'): ?>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead><tr>
                <th>#</th><th>Логин</th><th>Email</th><th>Роль</th><th>Дата</th><th>Действия</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <!-- Быстрая смена роли -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" class="form-select form-select-sm d-inline w-auto"
                                    onchange="this.form.submit()"
                                    <?= $u['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                <option value="user"  <?= $u['role']==='user' ?'selected':'' ?>>user</option>
                                <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                            </select>
                        </form>
                    </td>
                    <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <a href="admin.php?delete_user=<?= $u['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Удалить пользователя <?= htmlspecialchars(addslashes($u['username'])) ?>?')">🗑</a>
                        <?php else: ?>
                            <span class="text-white-50 small">это вы</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- ===== ВКЛАДКА: НОВЫЙ ПОЛЬЗОВАТЕЛЬ ===== -->
<?php elseif ($tab === 'add_user'): ?>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4">
                <h5 class="mb-3">➕ Создать пользователя</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3">
                        <label class="form-label">Имя пользователя</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Роль</label>
                        <select name="role" class="form-select">
                            <option value="user">user — обычный</option>
                            <option value="admin">admin — администратор</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Создать</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>

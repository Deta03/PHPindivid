<?php
// Настройки подключения
$host    = '127.0.0.1';
$dbname  = 'movies';      // имя базы данных
$user    = 'root';        // логин
$pass    = '';            // пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Включаем отображение ошибок SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Чтобы fetch() возвращал ассоциативный массив
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Если подключение не удалось — показываем ошибку
    die("Ошибка подключения к БД: " . $e->getMessage());
}

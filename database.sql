CREATE DATABASE IF NOT EXISTS movies CHARACTER SET utf8 COLLATE utf8_general_ci;
USE movies;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  director VARCHAR(100) NOT NULL,
  genre ENUM('Боевик','Комедия','Драма','Ужасы','Фантастика','Мультфильм') NOT NULL,
  year INT NOT NULL,
  country VARCHAR(50),
  description TEXT,
  rating TINYINT CHECK (rating BETWEEN 1 AND 10),
  poster_url VARCHAR(255),
  added_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (added_by) REFERENCES users(id)
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movie_id INT,
  user_id INT,
  text TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Тестовый администратор (пароль: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Тестовые фильмы
INSERT INTO movies (title, director, genre, year, country, description, rating, added_by) VALUES
('Начало', 'Кристофер Нолан', 'Фантастика', 2010, 'США', 'Вор, крадущий идеи из снов, получает шанс на искупление.', 9, 1),
('Интерстеллар', 'Кристофер Нолан', 'Фантастика', 2014, 'США', 'Экспедиция сквозь червоточину в поисках нового дома.', 9, 1),
('Зелёная книга', 'Питер Фаррелли', 'Драма', 2018, 'США', 'История дружбы водителя и пианиста на гастрольном туре.', 8, 1),
('Паразиты', 'Пон Джун-хо', 'Драма', 2019, 'Южная Корея', 'Бедная семья проникает в жизнь богатых с неожиданными последствиями.', 9, 1),
('Джокер', 'Тодд Филлипс', 'Драма', 2019, 'США', 'Происхождение самого известного злодея Готэма.', 8, 1);

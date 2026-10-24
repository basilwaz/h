-- Run once on a new database. Does not drop or reset existing data.
CREATE DATABASE IF NOT EXISTS movie_collection CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_collection;
CREATE TABLE genres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;
CREATE TABLE movies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    release_year SMALLINT UNSIGNED NOT NULL,
    genre_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE RESTRICT,
    CHECK (release_year BETWEEN 1888 AND 2100)
) ENGINE=InnoDB;
INSERT INTO genres (name) VALUES ('Science Fiction'), ('Animation');
INSERT INTO movies (title, release_year, genre_id) VALUES
('Interstellar', 2014, 1), ('Spirited Away', 2001, 2);

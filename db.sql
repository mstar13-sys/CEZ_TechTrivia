CREATE DATABASE IF NOT EXISTS quiz_game;
USE quiz_game;

CREATE TABLE Player (
    player_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(45) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('player', 'admin') DEFAULT 'player',
    total_xp INT DEFAULT 0,
    level INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE GameSession (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    date_played DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_score INT DEFAULT 0,
    mode VARCHAR(45),
    xp_earned INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES Player(player_id)
);

CREATE TABLE Question (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text VARCHAR(255) NOT NULL,
    difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
    category VARCHAR(60) DEFAULT 'General',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Choice (
    choice_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    choice_text VARCHAR(100) NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES Question(question_id) ON DELETE CASCADE
);

CREATE TABLE PlayerAnswer (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    question_id INT,
    choice_id INT,
    is_correct TINYINT(1),
    FOREIGN KEY (session_id) REFERENCES GameSession(session_id)
);

CREATE TABLE Achievement (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(45),
    description VARCHAR(100),
    condition_type VARCHAR(45),
    condition_value INT
);

CREATE TABLE PlayerAchievement (
    player_achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    achievement_id INT,
    date_unlocked DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES Player(player_id),
    FOREIGN KEY (achievement_id) REFERENCES Achievement(achievement_id)
);

CREATE TABLE Streak (
    streak_id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNIQUE,
    current_streak INT DEFAULT 0,
    max_streak INT DEFAULT 0,
    last_played_date DATE,
    FOREIGN KEY (player_id) REFERENCES Player(player_id)
);

-- Default admin account (password: Admin@123)
INSERT INTO Player (username, email, password, role)
VALUES ('admin', 'admin@ceztechreviewer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample questions
INSERT INTO Question (question_text, difficulty, category) VALUES
('What does HTML stand for?', 'easy', 'Web Development'),
('Which language is used for styling web pages?', 'easy', 'Web Development'),
('What is the output of: console.log(typeof null)?', 'medium', 'JavaScript'),
('Which sorting algorithm has O(n log n) average time complexity?', 'medium', 'Algorithms'),
('What does DNS stand for?', 'easy', 'Networking');

INSERT INTO Choice (question_id, choice_text, is_correct) VALUES
(1, 'HyperText Markup Language', 1),(1, 'HighText Machine Language', 0),(1, 'Hyperlink and Text Markup Language', 0),(1, 'Home Tool Markup Language', 0),
(2, 'CSS', 1),(2, 'JavaScript', 0),(2, 'Python', 0),(2, 'XML', 0),
(3, '"object"', 1),(3, '"null"', 0),(3, '"undefined"', 0),(3, '"string"', 0),
(4, 'Merge Sort', 1),(4, 'Bubble Sort', 0),(4, 'Insertion Sort', 0),(4, 'Selection Sort', 0),
(5, 'Domain Name System', 1),(5, 'Digital Network Service', 0),(5, 'Data Node Service', 0),(5, 'Domain Node System', 0);

-- ── Migration: add role/email columns if upgrading from old schema ─────────────
-- Safe to run even if columns already exist (will error silently in MySQL with IF NOT EXISTS workaround)
-- If you already ran the NEW db.sql above, ignore these.
ALTER TABLE Player
    MODIFY COLUMN role ENUM('player','admin') NOT NULL DEFAULT 'player';

-- Make sure all existing NULL roles default to 'player'
UPDATE Player SET role = 'player' WHERE role IS NULL OR role = '';

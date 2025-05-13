-- Create database
CREATE DATABASE IF NOT EXISTS football_management;
USE football_management;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'coach', 'player') NOT NULL,
    player_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    coach VARCHAR(100),
    founded_year INT,
    stadium VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Players table
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT,
    position VARCHAR(50),
    team_id INT,
    jersey_number INT,
    height INT,
    weight INT,
    nationality VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Matches table
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team1_id INT,
    team2_id INT,
    match_date DATETIME NOT NULL,
    venue VARCHAR(100),
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Postponed', 'Cancelled') DEFAULT 'Scheduled',
    team1_score INT DEFAULT 0,
    team2_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team1_id) REFERENCES teams(id) ON DELETE SET NULL,
    FOREIGN KEY (team2_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Training Sessions table
CREATE TABLE IF NOT EXISTS training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    session_date DATETIME NOT NULL,
    location VARCHAR(100),
    session_type VARCHAR(50),
    coach VARCHAR(100),
    duration INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Player Statistics table
CREATE TABLE IF NOT EXISTS player_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    matches_played INT DEFAULT 0,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$8KzO1f1VJ1KcEZYSNZQnVeKOKw.VpHnIVfGkEMEtSJbwL7vUP9DCW', 'admin@example.com', 'admin');

-- Insert sample teams
INSERT INTO teams (name, coach, founded_year, stadium) VALUES 
('FC Barcelona', 'Xavi Hernandez', 1899, 'Camp Nou'),
('Real Madrid', 'Carlo Ancelotti', 1902, 'Santiago Bernabeu'),
('Manchester United', 'Erik ten Hag', 1878, 'Old Trafford'),
('Liverpool FC', 'Jurgen Klopp', 1892, 'Anfield');

-- Insert sample players
INSERT INTO players (name, age, position, team_id, jersey_number, nationality) VALUES 
('Lionel Messi', 36, 'Forward', 1, 10, 'Argentina'),
('Cristiano Ronaldo', 38, 'Forward', 2, 7, 'Portugal'),
('Marcus Rashford', 25, 'Forward', 3, 10, 'England'),
('Mohamed Salah', 31, 'Forward', 4, 11, 'Egypt'),
('Frenkie de Jong', 26, 'Midfielder', 1, 21, 'Netherlands'),
('Luka Modric', 38, 'Midfielder', 2, 10, 'Croatia'),
('Bruno Fernandes', 29, 'Midfielder', 3, 8, 'Portugal'),
('Virgil van Dijk', 32, 'Defender', 4, 4, 'Netherlands');

-- Insert sample matches
INSERT INTO matches (team1_id, team2_id, match_date, venue, status) VALUES 
(1, 2, '2023-11-01 20:00:00', 'Camp Nou', 'Scheduled'),
(3, 4, '2023-11-05 16:30:00', 'Old Trafford', 'Scheduled'),
(1, 3, '2023-10-15 19:45:00', 'Camp Nou', 'Completed'),
(2, 4, '2023-10-10 18:00:00', 'Santiago Bernabeu', 'Completed');

-- Update scores for completed matches
UPDATE matches SET team1_score = 2, team2_score = 1 WHERE id = 3;
UPDATE matches SET team1_score = 3, team2_score = 2 WHERE id = 4;

-- Insert sample training sessions
INSERT INTO training_sessions (team_id, session_date, location, session_type, coach, duration) VALUES 
(1, '2023-10-30 10:00:00', 'Training Ground 1', 'Tactical', 'Xavi Hernandez', 90),
(2, '2023-10-31 09:30:00', 'Training Ground 2', 'Fitness', 'Carlo Ancelotti', 120),
(3, '2023-11-01 11:00:00', 'Carrington', 'Technical', 'Erik ten Hag', 90),
(4, '2023-11-02 10:30:00', 'Melwood', 'Match Preparation', 'Jurgen Klopp', 120);

-- Insert sample player statistics
INSERT INTO player_stats (player_id, matches_played, goals, assists, yellow_cards, red_cards, minutes_played) VALUES 
(1, 10, 12, 5, 1, 0, 900),
(2, 10, 10, 2, 2, 0, 900),
(3, 9, 7, 3, 3, 1, 810),
(4, 10, 9, 4, 0, 0, 900),
(5, 10, 2, 6, 2, 0, 900),
(6, 9, 3, 8, 1, 0, 810),
(7, 10, 5, 7, 4, 0, 900),
(8, 10, 1, 0, 2, 0, 900);

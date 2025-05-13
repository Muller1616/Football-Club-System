-- Create database


-- Users table
CREATE TABLE  users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'fan') NOT NULL DEFAULT 'fan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teams table
CREATE TABLE  teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    coach VARCHAR(100) NOT NULL,
    founded_year INT NOT NULL,
    logo VARCHAR(255) DEFAULT 'default_team.png',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Players table
CREATE TABLE  players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    jersey_number INT NOT NULL,
    age INT NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    image VARCHAR(255) DEFAULT 'default_player.png',
    bio TEXT,
    team_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Matches table
CREATE TABLE  matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    match_date DATETIME NOT NULL,
    venue VARCHAR(100) NOT NULL,
    ticket_price DECIMAL(10, 2) NOT NULL,
    available_tickets INT NOT NULL DEFAULT 1000,
    status ENUM('upcoming', 'completed', 'cancelled') DEFAULT 'upcoming',
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- Tickets table
CREATE TABLE  tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@football.com', '$2y$10$8KzO1f1VJ1KcEZYSNZQnVeKOKw.VpHnIVfGkEMEtSJbwL7vUP9DCW', 'admin');

-- Insert sample teams
INSERT INTO teams (name, coach, founded_year, description) VALUES 
('FC Barcelona', 'Xavi Hernandez', 1899, 'FC Barcelona is a professional football club based in Barcelona, Spain.'),
('Real Madrid', 'Carlo Ancelotti', 1902, 'Real Madrid Club de Fútbol is a professional football club based in Madrid, Spain.'),
('Manchester United', 'Erik ten Hag', 1878, 'Manchester United Football Club is a professional football club based in Manchester, England.'),
('Liverpool FC', 'Jurgen Klopp', 1892, 'Liverpool Football Club is a professional football club based in Liverpool, England.');

-- Insert sample players
INSERT INTO players (name, position, jersey_number, age, nationality, bio, team_id) VALUES 
('Lionel Messi', 'Forward', 10, 36, 'Argentina', 'Widely regarded as one of the greatest players of all time.', 1),
('Cristiano Ronaldo', 'Forward', 7, 38, 'Portugal', 'One of the greatest goalscorers of all time.', 2),
('Marcus Rashford', 'Forward', 10, 25, 'England', 'English professional footballer who plays as a forward.', 3),
('Mohamed Salah', 'Forward', 11, 31, 'Egypt', 'Egyptian professional footballer who plays as a forward.', 4),
('Frenkie de Jong', 'Midfielder', 21, 26, 'Netherlands', 'Dutch professional footballer who plays as a midfielder.', 1),
('Luka Modric', 'Midfielder', 10, 38, 'Croatia', 'Croatian professional footballer who plays as a midfielder.', 2),
('Bruno Fernandes', 'Midfielder', 8, 29, 'Portugal', 'Portuguese professional footballer who plays as a midfielder.', 3),
('Virgil van Dijk', 'Defender', 4, 32, 'Netherlands', 'Dutch professional footballer who plays as a centre-back.', 4);

-- Insert sample matches
INSERT INTO matches (home_team_id, away_team_id, match_date, venue, ticket_price, status) VALUES 
(1, 2, '2023-11-25 20:00:00', 'Camp Nou', 99.99, 'upcoming'),
(3, 4, '2023-11-30 16:30:00', 'Old Trafford', 79.99, 'upcoming'),
(1, 3, '2023-10-15 19:45:00', 'Camp Nou', 89.99, 'completed'),
(2, 4, '2023-10-10 18:00:00', 'Santiago Bernabeu', 89.99, 'completed');

-- Update scores for completed matches
UPDATE matches SET home_score = 2, away_score = 1 WHERE id = 3;
UPDATE matches SET home_score = 3, away_score = 2 WHERE id = 4;

-- Insert sample fan user (password: fan123)
INSERT INTO users (username, email, password, role) VALUES 
('fan', 'fan@football.com', '$2y$10$Ot/9Nzw3xQSECGYRZX7Ib.Vh0FBEyKPQjcXd9hF1gxD.6AUOLGcOO', 'fan');

-- Insert sample ticket purchases
INSERT INTO tickets (match_id, user_id, quantity, total_price) VALUES 
(1, 2, 2, 199.98),
(2, 2, 1, 79.99);

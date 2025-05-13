I have built this as my third-year final project for the university course in Software Architecture.

# Football Club Management System
A comprehensive web-based system for managing football clubs, including players, coaches, teams, matches, and ticket sales. This application provides separate interfaces for administrators and fans, allowing for complete club management and fan engagement.

## Features

### Admin Features
- **Dashboard**: Overview of system statistics and recent activities
- **Player Management**: Add, edit, view, and delete player profiles with photos
- **Coach Management**: Manage coach information and team assignments
- **Team Management**: Create and manage teams with detailed information
- **Match Management**: Schedule matches, update scores, and manage venues
- **Ticket Management**: View ticket sales and revenue statistics
- **User Management**: Manage admin and fan accounts

### Fan Features
- **Match Viewing**: Browse upcoming and past matches
- **Ticket Purchasing**: Buy tickets for upcoming matches
- **Player Profiles**: View detailed information about players
- **Team Information**: Access team details and player rosters

## Technologies Used

- **Backend**: PHP 8.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Custom CSS with responsive design
- **JavaScript Libraries**: Vanilla JavaScript
- **Version Control**: Git

## Installation

1. **Clone the repository**
   \`\`\`bash
   git clone 
   cd fmss
   \`\`\`

2. **Set up the database**
   - Create a MySQL database named `football_club`
   - Import the database schema from `database/football_club.sql`
   - Alternatively, use the setup.php file to automatically create and populate the database

3. **Configure database connection**
   - Open `includes/db.php` and `config/database.php`
   - Update the database credentials:
     \`\`\`php
     $host = 'localhost';
     $dbname = 'football_club'; // or 'football_management'
     $username = 'your_username'; // default: 'root'
     $password = 'your_password'; // default: ''
     \`\`\`

4. **Set up the web server**
   - Place the project files in your web server's document root (e.g., htdocs for XAMPP)
   - Ensure PHP is properly configured with PDO and MySQLi extensions

5. **Create upload directories**
   - Create the following directories with write permissions:
     \`\`\`
     uploads/
     ├── players/
     └── coaches/
     \`\`\`

## Usage

### Default Login Credentials

#### Admin Account
- **Username**: admin
- **Password**: admin123

#### Fan/Player Account
- **Username**: fan
- **Password**: fan123

### Admin Panel

1. Navigate to `http://localhost/fmss/auth/login.php` or `http://localhost/fmss/login.php`
2. Log in with admin credentials
3. Use the sidebar navigation to access different management sections

### Fan Interface

1. Navigate to `http://localhost/fmss/auth/login.php` or `http://localhost/fmss/login.php`
2. Log in with fan credentials or register a new account
3. Browse matches, view player profiles, and purchase tickets

## Database Setup

The system uses a MySQL database with the following main tables:

- `users`: Stores user account information
- `teams`: Contains team details
- `players`: Stores player information and team assignments
- `coaches`: Manages coach profiles and team assignments
- `matches`: Tracks match schedules, scores, and venues
- `tickets`: Records ticket purchases by fans

The complete database schema can be found in `database/football_club.sql`.

## Project Structure

\`\`\`
football-club-system/
├── admin/                  # Admin panel pages
├── assets/                 # Static assets
│   ├── css/
│   ├── images/
│   └── js/
├── auth/                   # Authentication pages
├── config/                 # Configuration files
├── database/               # Database schema
├── includes/               # Shared PHP files
├── player/                 # Player interface pages
├── coach/                  # Coach interface pages
├── uploads/                # User uploaded files
├── user/                   # Fan interface pages
├── index.php               # Homepage
└── README.md               # Project documentation
\`\`\`

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `includes/db.php` and `config/database.php`
   - Ensure MySQL service is running
   - Check if the database exists

2. **Missing Directories**
   - Ensure all required directories exist and have proper permissions
   - Create `uploads/players` and `uploads/coaches` directories if missing

3. **File Permission Issues**
   - Set appropriate permissions for upload directories (755 or 775)
   - Ensure web server has write access to these directories

4. **Blank Pages or PHP Errors**
   - Enable error reporting in PHP for debugging
   - Check PHP error logs for detailed information

## Future Enhancements

- Player statistics tracking
- Match live updates
- Fan forum and discussion board
- Email notifications for ticket purchases
- Mobile application integration
- Payment gateway integration for ticket sales
- Advanced reporting and analytics

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License.

## Contact

Mulugeta - mulugetaabeje16@gmail.com

Project Link: 

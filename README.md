# Smart Parking System

A web-based parking management system for New Delhi and Uttar Pradesh regions.

## Features

- User registration and authentication
- Parking slot booking system
- Tourist place information
- Interactive dashboard
- Booking management
- Location-based parking suggestions

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP (recommended)

## Installation

1. Clone the repository to your web server's root directory
2. Create a MySQL database named `parking_db`
3. Import the database schema from `database.sql`
4. Configure database connection in `config/db.php`
5. Ensure proper permissions for file uploads
6. Create an `images` directory and ensure it's writable

## Directory Structure

```
parking1/
├── config/
│   └── db.php
├── css/
│   └── style.css
├── images/
├── includes/
│   ├── header.php
│   └── footer.php
├── js/
│   └── main.js
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── book-slot.php
├── tourist-places.php
├── terms.php
├── policy.php
├── logout.php
├── database.sql
└── README.md
```

## Usage

1. Start your XAMPP server
2. Access the website through your web browser
3. Register an account
4. Login to access all features
5. Book parking slots at various locations
6. View and manage your bookings through the dashboard

## Security Features

- Password hashing
- Session management
- Input validation
- SQL injection prevention
- XSS protection

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

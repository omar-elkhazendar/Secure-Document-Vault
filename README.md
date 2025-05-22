# Secure Authentication System

A secure authentication system that supports both manual login and GitHub OAuth 2.0 authentication.

## Features

- Manual authentication (username/email & password)
- GitHub OAuth 2.0 authentication
- Secure password storage using bcrypt
- Login activity logging
- Session management
- Prevention of back navigation after logout
- Clean and responsive UI

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP or similar web server
- GitHub OAuth App credentials

## Setup Instructions

1. Clone this repository to your web server directory (e.g., XAMPP's htdocs folder)

2. Create a GitHub OAuth App:
   - Go to GitHub Settings > Developer Settings > OAuth Apps
   - Click "New OAuth App"
   - Set Homepage URL to: `http://localhost/Data2`
   - Set Authorization callback URL to: `http://localhost/Data2/github-callback.php`
   - Copy the Client ID and Client Secret

3. Update GitHub OAuth credentials:
   - Open `classes/GithubAuth.php`
   - Replace `YOUR_GITHUB_CLIENT_ID` and `YOUR_GITHUB_CLIENT_SECRET` with your actual credentials

4. Set up the database:
   - Open phpMyAdmin
   - Import the `database.sql` file
   - This will create the necessary tables

5. Configure database connection:
   - Open `config/database.php`
   - Update the database credentials if needed (default is for XAMPP)

6. Access the application:
   - Open your browser and go to: `http://localhost/Data2`
   - You should see the login page

## Security Features

- Password requirements:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character
- Secure password hashing using bcrypt
- Session management with prevention of session fixation
- Protection against back navigation after logout
- Input validation and sanitization
- CSRF protection
- Secure headers

## File Structure

```
├── classes/
│   ├── User.php
│   ├── Session.php
│   └── GithubAuth.php
├── config/
│   └── database.php
├── login.php
├── signup.php
├── dashboard.php
├── logout.php
├── github-callback.php
├── database.sql
└── README.md
```

## Usage

1. Sign up for a new account or use GitHub OAuth
2. Log in with your credentials
3. Access the dashboard
4. Log out when done

## Security Notes

- Never commit your GitHub OAuth credentials
- Keep your database credentials secure
- Regularly update your dependencies
- Monitor login logs for suspicious activity
- Use HTTPS in production 
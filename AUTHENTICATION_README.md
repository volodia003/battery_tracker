# Authentication & Theme System - Implementation Guide

## Overview
This document describes the authentication system and dark theme functionality that has been added to the Battery Tracker application.

## Features Added

### 1. Authentication System
- ✅ User registration page (`register.php`)
- ✅ Login page (`login.php`)
- ✅ Logout functionality (`logout.php`)
- ✅ Session management
- ✅ Password hashing (bcrypt)
- ✅ Authentication middleware (all pages now require login)

### 2. Theme Switching
- ✅ Dark theme support
- ✅ Light theme (default)
- ✅ Toggle button in navigation bar
- ✅ Theme preference stored in session
- ✅ Comprehensive dark theme CSS

## Default User Credentials

**Username:** `user123`  
**Password:** `pas123`

## Setup Instructions

### First Time Setup

1. **Import the database:**
   ```
   Import database.sql into your MySQL database
   ```

2. **Create the default user:**
   Option A: Run the setup script
   ```
   Navigate to: http://localhost/battery_tracker/setup_user.php
   ```
   
   Option B: Register manually
   ```
   Navigate to: http://localhost/battery_tracker/register.php
   Fill in the registration form
   ```

3. **Login:**
   ```
   Navigate to: http://localhost/battery_tracker/login.php
   Use credentials: user123 / pas123
   ```

## File Structure

### New Files Created

```
/config/
  ├── auth.php                 # Authentication helper functions
  
/
  ├── login.php                # Login page
  ├── register.php             # Registration page
  ├── logout.php               # Logout handler
  ├── toggle_theme.php         # Theme toggle handler
  ├── setup_user.php           # Default user setup script
  └── generate_hash.php        # Password hash generator utility
```

### Modified Files

```
/includes/
  ├── header.php               # Added auth check + theme switcher + user menu
  
/assets/css/
  ├── style.css                # Added dark theme styles
  
/config/
  ├── database.php             # Already had necessary functions
  
/
  ├── database.sql             # Updated with user setup instructions
  └── README.txt               # Added authentication documentation
```

## Authentication Functions

Located in `/config/auth.php`:

- `isLoggedIn()` - Check if user is authenticated
- `currentUser()` - Get current user data
- `login($username, $password)` - Authenticate user
- `register($username, $password, $email)` - Create new user account
- `logout()` - End user session
- `requireAuth()` - Middleware to protect pages
- `getTheme()` - Get current theme preference
- `setTheme($theme)` - Set theme preference
- `toggleTheme()` - Switch between light/dark theme

## Theme System

### Usage

The theme is stored in the user's session and persists across page loads.

**Toggle Theme:**
- Click the moon/sun icon in the navigation bar
- Theme changes immediately without page reload

**Available Themes:**
- `light` (default)
- `dark`

### Dark Theme Styling

All components have been styled for dark theme:
- Cards and backgrounds
- Forms and inputs
- Tables
- Modals and dropdowns
- Alerts and notifications
- Buttons and badges
- Charts (via Chart.js)

## Security Features

1. **Password Hashing:** Uses PHP's `password_hash()` with bcrypt
2. **Session Management:** Secure session handling
3. **SQL Injection Protection:** All queries use prepared statements
4. **XSS Protection:** All output is sanitized with `htmlspecialchars()`
5. **Authentication Middleware:** All pages require login (except login/register)

## User Flow

1. User visits any page → Redirected to `login.php`
2. User logs in → Session created → Redirected to `index.php`
3. User navigates site → Session maintained
4. User clicks logout → Session destroyed → Redirected to `login.php`

## Troubleshooting

### Can't Login
- Ensure the database is properly set up
- Run `setup_user.php` to create the default user
- Check that sessions are enabled in PHP

### Theme Not Switching
- Clear browser cache
- Ensure JavaScript is enabled
- Check browser console for errors

### Session Issues
- Ensure PHP sessions are properly configured
- Check file permissions on session directory
- Verify `session.save_path` in php.ini

## Testing Credentials

For testing purposes, you can create additional users through:
1. The registration page at `/register.php`
2. Direct database insertion with hashed passwords

### Generate Password Hash
```php
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```

Or run `/generate_hash.php` to see example hashes.

## Additional Notes

- All existing pages now require authentication
- User preferences (theme) are stored per session
- The system supports multiple users with individual sessions
- No special database migrations needed - the structure was already in place

## Future Enhancements (Optional)

- Password reset functionality
- Email verification
- Remember me functionality
- User profile management
- Admin panel for user management
- Theme preference stored in database (persistent across sessions)
- Two-factor authentication
- OAuth integration (Google, Facebook, etc.)

---

**System Version:** 1.1.0 (with Authentication & Themes)  
**Last Updated:** December 27, 2025

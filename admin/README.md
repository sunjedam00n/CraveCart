# Admin Account Management

## Initial Setup

1. **Run Database Setup**
   - Access `/setup_db.php` to create all database tables
   - No default admin account is created anymore

2. **Create First Admin Account**
   - Go to `/admin/register.php`
   - Fill in username (minimum 3 characters) and password (minimum 6 characters)
   - Click Register

## Admin Registration

Admins can now self-register:
- Visit `/admin/register.php`
- Enter a unique username
- Create a secure password (minimum 6 characters)
- Confirm password
- Click Register

**Security Features:**
- Passwords are hashed with bcrypt (PASSWORD_BCRYPT, cost 12)
- Usernames must be unique
- Password confirmation to prevent typos
- CSRF tokens protect registration

## Admin Login

1. Go to `/admin/login.php` or `/admin/index.html`
2. Enter username and password
3. Click Login

**Security Features:**
- Rate limiting (5 attempts per 5 minutes to prevent brute force)
- Session-based authentication
- Secure cookies (HttpOnly, SameSite=Strict)
- Password verification with bcrypt

## Admin Portal

- **Entry Point:** `/admin/index.html`
- Shows options to:
  - Register new admin account
  - Login with existing credentials

## Access Control

After login:
- Admin can access: Dashboard, Add Food, Manage Food, Orders
- Session prevents unauthorized access
- Logout clears session

## Features

✅ Multiple admin accounts
✅ Secure password hashing (bcrypt)
✅ CSRF protection
✅ Rate limiting on login
✅ Input validation
✅ Error logging
✅ Session security

## Important Notes

- **No hardcoded credentials** - All admins must register
- **Unique usernames** - Each admin needs a different username
- **Password requirements** - Minimum 6 characters
- **Database** - Created via `setup_db.php`

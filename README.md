# API-Groupwork — Sprint 2 (12 Sep)  
**Assignment:** User authentication with 2-Factor Authentication (Email) — 5%

## What this submission contains
- Email-based 2FA after password login (OTP, expires in 10 minutes).
- SQL scripts: `sql/users.sql`, `sql/otp.sql`.
- Files: `conf.php`, `db_connect.php`, `signup.php`, `login.php`, `verify_otp.php`, `dashboard.php`, `logout.php`, `mail.php`.
- PHPMailer wrapper (requires `composer install`).

## Setup (local)
1. Clone repository:
   ```bash
   git clone <your-repo-url>
   cd API-Groupwork

# Final Project - PHP & MySQL (XAMPP)

A simple PHP backend project with MySQL database using XAMPP.

## Project Structure

```
final-project/
├── .gitignore
├── README.md
├── index.php
├── backend/
│   ├── config/          # Database configuration
│   ├── models/          # Data models
│   ├── controllers/     # Business logic
│   └── index.php        # API entry point
├── database/
│   └── schema.sql       # Database schema
└── frontend/            # Frontend files (coming soon)
```

## XAMPP Setup

### 1. Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** and **MySQL** services

### 2. Create MySQL Database

Open XAMPP Control Panel → MySQL → Admin (phpMyAdmin)
Or run from terminal:
```bash
mysql -u root < /Applications/XAMPP/xamppfiles/htdocs/final-project/database/schema.sql
```

### 3. Access Your Project

Visit: `http://localhost/final-project/backend/`

## Database Configuration

Edit `backend/config/Database.php` if needed:
- Host: `localhost`
- Username: `root`
- Password: (empty by default in XAMPP)
- Database: `final_project`

## API Endpoints

- `GET /final-project/backend/` - Welcome message

## Next Steps

1. Create models in `backend/models/`
2. Create controllers in `backend/controllers/`
3. Build API endpoints in `backend/index.php`
4. Create frontend in `frontend/` folder

## Technologies

- **Backend**: PHP 8.2.30
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (via XAMPP)

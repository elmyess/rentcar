# Elayadi Prestige Car - Setup Guide

## Prerequisites
- XAMPP installed (Apache, MySQL, PHP)
- Node.js installed (for any future enhancements)
- Project folder copied to `C:\xampp\htdocs\elayadi-prestigecar`

## Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL
4. Make sure both are running (green status)

## Step 2: Set Up Database
1. Open browser and go to: http://localhost/phpmyadmin
2. Create new database: `elayadi_prestige_car`
3. Import the `database_setup.sql` file:
   - Click "Import" tab
   - Choose file: `database_setup.sql`
   - Click "Go"

## Step 3: Configure Database Connection
The database configuration is already set in `back-end/config/database.php`:
- Host: localhost
- Database: elayadi_prestige_car
- Username: root
- Password: (empty)

## Step 4: Access the Website
1. Open browser
2. Go to: http://localhost/elayadi-prestigecar/
3. The main website should load

## Step 5: Access Admin Panel
1. Go to: http://localhost/elayadi-prestigecar/back-end/admin/
2. Login with:
   - Email: admin@elayadi.com
   - Password: admin123

## Step 6: Test Functionality
1. Browse cars on the main site
2. Make a test reservation
3. Check admin panel for the reservation
4. Confirm/cancel reservations

## Troubleshooting

### If website doesn't load:
- Check if Apache is running
- Verify project is in correct folder: `C:\xampp\htdocs\elayadi-prestigecar`
- Check browser console for errors

### If database connection fails:
- Verify MySQL is running
- Check database name matches in `database.php`
- Run the SQL setup file again

### If images don't show:
- Check image paths in `images/` folder
- Verify image files exist

## Project Structure
```
elayadi-prestigecar/
├── index.html          # Main website
├── style.css           # Styling
├── images/             # Car images
├── back-end/           # PHP backend
│   ├── config/         # Database config
│   ├── models/         # PHP classes
│   ├── api/            # API endpoints
│   └── admin/          # Admin interface
└── database_setup.sql  # Database schema
```

## Features Working
- ✅ Car browsing
- ✅ Reservation system
- ✅ Admin management
- ✅ Email notifications (if configured)
- ✅ Responsive design

## Contact

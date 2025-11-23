# Student Activities CRUD System

A complete CRUD (Create, Read, Update, Delete) system for managing student activities with file uploads, built entirely in plain PHP without frameworks.

## Features

- ✅ Full CRUD operations (Create, Read, Update, Delete)
- ✅ File upload with validation (PDF, DOCX, DOC, JPG, JPEG, PNG)
- ✅ Secure file handling with MIME type validation
- ✅ Responsive and modern UI
- ✅ Multi-delete functionality
- ✅ Search functionality
- ✅ CSRF protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ Flash messages for user feedback
- ✅ File download with secure headers

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Apache web server (or Nginx with PHP-FPM)
- phpMyAdmin (for database management)

## Installation

### 1. Database Setup

1. Open phpMyAdmin
2. Import the `database.sql` file or run the SQL commands manually:
   ```sql
   CREATE DATABASE IF NOT EXISTS student_upload_center;
   USE student_upload_center;
   CREATE TABLE activities (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     description TEXT,
     filename VARCHAR(255) NOT NULL,
     file_type VARCHAR(50),
     file_size BIGINT,
     uploaded_by VARCHAR(100),
     uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

### 2. Configuration

1. Open `config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'student_upload_center');
   ```

2. Adjust upload settings if needed:
   ```php
   define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
   ```

### 3. File Permissions

Ensure the `uploads/` directory has write permissions:
```bash
chmod 755 uploads/
```

On Windows, ensure the folder has write permissions for the web server user.

### 4. PHP Configuration

Make sure your `php.ini` has these settings:
```ini
upload_max_filesize = 5M
post_max_size = 6M
file_uploads = On
```

### 5. Web Server Configuration

If using Apache, ensure `mod_rewrite` is enabled (if needed for clean URLs).

## File Structure

```
ACT3_CC106_BRIGETH/
├── config.php                 # Database connection and configuration
├── index.php                  # Main page (list all activities)
├── add.php                    # Add new activity form
├── edit.php                   # Edit activity form
├── delete.php                 # Delete confirmation page
├── delete_multiple.php        # Multi-delete handler
├── download.php               # Secure file download handler
├── database.sql               # Database schema
├── README.md                  # This file
├── assets/
│   ├── css/
│   │   └── main.css          # Main stylesheet (refactored from ACT_CSS.SERDAN.css)
│   └── js/
│       └── main.js           # Main JavaScript (refactored from ACT3_JAVA.SERDAN.js)
└── uploads/
    ├── .htaccess             # Security configuration
    └── [uploaded files]      # Stored files
```

## Usage

### Adding an Activity

1. Click "Add New Activity" button
2. Fill in the title (required)
3. Optionally add a description
4. Select a file (PDF, DOCX, DOC, JPG, JPEG, PNG - max 5MB)
5. Click "Upload Activity"

### Editing an Activity

1. Click the edit icon (pencil) next to an activity
2. Modify title, description, or uploaded_by
3. Optionally replace the file
4. Click "Update Activity"

### Deleting an Activity

1. Click the delete icon (trash) next to an activity
2. Confirm deletion on the confirmation page

### Multi-Delete

1. Select multiple activities using checkboxes
2. Click "Delete Selected" button
3. Confirm deletion

### Downloading Files

1. Click the download icon or file name link
2. File will be downloaded with secure headers

### Searching

1. Use the search box in the action bar
2. Search by title or description

## Security Features

- **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
- **Output Escaping**: All user input is escaped using `htmlspecialchars()`
- **File Validation**: Files are validated by:
  - Extension check
  - MIME type validation using `finfo_file()`
  - File size limits
  - Unique filename generation to prevent collisions
- **CSRF Protection**: Forms include CSRF tokens
- **Secure File Storage**: Files stored outside web root (or protected by .htaccess)
- **Directory Listing Prevention**: .htaccess prevents directory listing

## Integration Notes

### Changes Made to Existing Files

#### ACT2_HTML.SERDAN.php
- **Original**: Login/registration form with toggle functionality
- **Status**: Kept separate (not integrated into CRUD system)
- **Note**: The CRUD system is standalone and doesn't require the login system

#### ACT_CSS.SERDAN.css → assets/css/main.css
- **Removed**: Duplicate media queries (lines 401-438 were duplicates of 367-399)
- **Removed**: Duplicate gradient definitions
- **Reorganized**: Styles grouped into logical sections:
  - Global reset & base styles
  - Navigation bar
  - Main content layout
  - Welcome section
  - Flash messages
  - Buttons
  - Tables
  - Forms
  - File upload
  - Responsive design
- **Added**: New styles for:
  - Activity table
  - File upload preview
  - Flash messages
  - Action buttons
  - Delete confirmation
  - Empty state
- **Improved**: Reduced specificity, added reusable classes

#### ACT3_JAVA.SERDAN.js → assets/js/main.js
- **Removed**: Login/registration form handling (not needed for CRUD)
- **Removed**: Welcome page generation code
- **Added**: New functionality:
  - Select all checkbox
  - Multi-delete handling
  - File upload preview with image support
  - Form validation
  - Delete confirmation
  - Flash message auto-hide
  - Drag and drop file upload
- **Improved**: Added null checks for all `querySelector` calls
- **Improved**: Consolidated form toggle code
- **Improved**: Better error handling

## Troubleshooting

### File Upload Issues

- **Error: "File size exceeds maximum"**
  - Check `MAX_FILE_SIZE` in `config.php`
  - Check `upload_max_filesize` and `post_max_size` in `php.ini`

- **Error: "Failed to save uploaded file"**
  - Check `uploads/` directory permissions
  - Ensure directory exists and is writable

### Database Connection Issues

- Verify database credentials in `config.php`
- Ensure MySQL service is running
- Check if database exists: `SHOW DATABASES;`

### CSS/JS Not Loading

- Check file paths in HTML (should be `assets/css/main.css`)
- Clear browser cache
- Check browser console for 404 errors

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is for educational purposes.

## Author

Student Activities CRUD System
Built for CC106 - Web Development

---

**Note**: Remember to change default database credentials before deploying to production!


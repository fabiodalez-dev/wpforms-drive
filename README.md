# WPForms Google Drive Integration

WordPress plugin that integrates WPForms with Google Drive to automatically upload files and form submission data to Google Drive with automatic folder organization.

## About

**WPForms Google Drive Integration** is a WordPress plugin designed to seamlessly connect WPForms with Google Drive. When users submit forms through WPForms, all uploaded files and form data are automatically saved to Google Drive in organized folders.

This plugin is ideal for:
- **Businesses** that need to archive form submissions securely in the cloud
- **Organizations** that want automatic backup of uploaded documents
- **Developers** looking for a simple way to integrate WPForms with Google Drive

The plugin uses Google's official OAuth2 authentication, ensuring secure access without storing passwords.

## Features

- **Automatic Upload** - Files submitted through WPForms are automatically saved to Google Drive
- **Automatic Organization** - Each submission is saved in a dedicated folder with a descriptive name
- **Form Data Export** - Complete form content is saved as a text file in the folder
- **OAuth2 Authentication** - Secure connection via Google OAuth2
- **Large File Support** - Support for large file uploads with automatic chunking
- **Admin Interface** - Simple administration page to configure the connection
- **Direct Links** - Quick access to Google Drive folders from the WPForms entries list
- **WPForms Compliant** - Developed following the [official WPForms documentation](https://wpforms.com/developers/)

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WPForms (Lite or Pro)
- Google account with Google Drive access
- Composer (to install dependencies)

## Installation

1. Clone or download the repository to your `wp-content/plugins/` folder
2. Install dependencies with Composer:
   ```bash
   cd wp-content/plugins/wpforms-drive
   composer install
   ```
3. Activate the plugin from the WordPress dashboard
4. Go to **WPForms > Google Drive** to configure the connection

## Google Cloud Configuration

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google Drive API**
4. Go to **Credentials** and create OAuth 2.0 credentials
5. Configure authorized redirect URIs:
   ```
   https://your-site.com/wp-admin/admin.php?page=wpforms-google-drive
   ```
6. Copy Client ID and Client Secret to the plugin settings

## Plugin Configuration

1. Go to **WPForms > Google Drive**
2. Enter the **Client ID** and **Client Secret**
3. Click **Connect to Google Drive**
4. Authorize the application
5. (Optional) Select a root folder on Google Drive
6. Enable the integration

## How It Works

When a user submits a WPForms form:

1. The plugin creates a folder on Google Drive named: `Form_{form_name}_{date}_{submission_id}`
2. Uploads all attached files to the folder
3. Creates a `form-data.txt` file with all form data
4. Saves the folder link in the entry metadata

## Folder Structure

```
Google Drive
└── WPForms Submissions (root folder)
    ├── Form_Contact_2026-01-28_123/
    │   ├── document.pdf
    │   ├── photo.jpg
    │   └── form-data.txt
    └── Form_Registration_2026-01-28_124/
        ├── cv.pdf
        └── form-data.txt
```

## Development

### Plugin Structure

```
wpforms-drive/
├── classes/
│   ├── class-google-drive-manager.php
│   ├── class-wpforms-handler.php
│   └── class-admin.php
├── assets/
│   ├── css/
│   └── js/
├── views/
│   └── admin/
├── languages/
├── vendor/
├── wpforms-google-drive.php
├── composer.json
└── README.md
```

### Technical Integration with WPForms

The plugin integrates with WPForms using official hooks and APIs:

#### Hooks Used

- **`wpforms_process_complete`** - Main hook triggered after a successful submission
  - Receives: `$fields`, `$entry`, `$form_data`, `$entry_id`
  - Documentation: [wpforms_process_complete](https://wpforms.com/developers/wpforms_process_complete/)

#### File Upload Handling

According to [WPForms documentation](https://wpforms.com/docs/a-complete-guide-to-the-file-upload-field/):
- Files are in `$fields[field_id]['value']` as URL string
- Multiple files are separated by newline (`\n`)
- The plugin automatically converts URLs to filesystem paths

#### Metadata Storage

The plugin saves submission metadata in two ways:
1. `wp_wpforms_entry_meta` table (primary method)
2. WordPress options (fallback)

Saved metadata:
- `google_drive_folder_url` - Drive folder URL
- `google_drive_uploaded_at` - Upload timestamp
- `google_drive_files_count` - Number of uploaded files
- `google_drive_files` - Array with file details

#### References

- [WPForms Developer Documentation](https://wpforms.com/developers/)
- [Custom Integrations Guide](https://www.billerickson.net/contact-form-integration/)
- [File Upload Field Documentation](https://wpforms.com/docs/a-complete-guide-to-the-file-upload-field/)

## Security

- OAuth credentials are securely stored in the WordPress database
- Access tokens are automatically refreshed
- No credentials are exposed in frontend code
- All SQL queries use prepared statements
- Nonce verification on all forms

## Support

For issues or feature requests, open an issue on GitHub.

## License

GPL v3 or later - [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)

## Credits

Developed by Fabio D'Alessandro

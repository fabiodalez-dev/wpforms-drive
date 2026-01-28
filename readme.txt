=== WPForms Google Drive Integration ===
Contributors: fabiodalez
Tags: wpforms, google drive, forms, upload, integration
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrates WPForms with Google Drive to automatically upload files and submissions to Google Drive.

== Description ==

WPForms Google Drive Integration is a plugin that seamlessly connects WPForms with Google Drive, automatically uploading all files and form submission data to Google Drive in organized folders.

= About =

This plugin is designed for businesses and organizations that need to:
* Archive form submissions securely in the cloud
* Automatically backup uploaded documents
* Access form data from anywhere via Google Drive

The plugin uses Google's official OAuth2 authentication, ensuring secure access without storing passwords.

= Key Features =

* **Automatic Upload** - Files submitted through WPForms are automatically saved to Google Drive
* **Automatic Organization** - Each submission is saved in a dedicated folder with a descriptive name
* **Form Data Export** - Complete form content is saved as a text file in the folder
* **OAuth2 Authentication** - Secure connection via Google OAuth2
* **Large File Support** - Support for large file uploads with automatic chunking
* **Intuitive Admin Interface** - Simple administration page to configure the connection
* **Direct Links** - Quick access to Google Drive folders from the WPForms entries list

= How It Works =

When a user submits a WPForms form:

1. The plugin automatically creates a folder on Google Drive
2. Uploads all attached files to the folder
3. Creates a text file with all form data
4. Saves the folder link in the submission metadata

= Folder Structure =

```
Google Drive
└── WPForms Submissions
    ├── Form_Contact_2026-01-28_123/
    │   ├── document.pdf
    │   ├── photo.jpg
    │   └── form-data.txt
    └── Form_Registration_2026-01-28_124/
        ├── cv.pdf
        └── form-data.txt
```

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher
* WPForms (Lite or Pro)
* Google account with Google Drive access
* Composer (to install dependencies)

== Installation ==

= Automatic Installation =

1. Download the plugin from the repository or GitHub
2. Upload the zip file via WordPress > Plugins > Add New
3. Activate the plugin

= Manual Installation =

1. Download or clone the repository to `wp-content/plugins/`
2. Navigate to the plugin folder: `cd wp-content/plugins/wpforms-drive`
3. Install dependencies with Composer: `composer install`
4. Activate the plugin from WordPress dashboard

= Google Cloud Configuration =

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google Drive API**
4. Go to **Credentials** and create OAuth 2.0 credentials
5. Configure authorized redirect URIs (available in plugin settings)
6. Copy Client ID and Client Secret

= Plugin Configuration =

1. Go to **WPForms > Google Drive**
2. Enter the **Client ID** and **Client Secret**
3. Click **Save Settings**
4. Click **Connect to Google Drive**
5. Authorize the application via Google
6. Enable the integration

== Frequently Asked Questions ==

= Does the plugin work with WPForms Lite? =

Yes, the plugin works with both WPForms Lite and WPForms Pro.

= Are files deleted from WordPress? =

No, files remain on the WordPress server. The plugin only creates a copy on Google Drive.

= Can I choose which folder to save files to? =

Yes, you can specify a root folder on Google Drive in the plugin settings.

= What happens if the Google Drive connection fails? =

The submission is still saved on WordPress normally. The Google Drive upload is handled in the background and does not block the form process.

= Does the plugin support large files? =

Yes, the plugin uses automatic chunking to upload large files to Google Drive.

= How can I view folders on Google Drive? =

In the WPForms entries list, you'll find a "Google Drive" column with an "Open in Drive" button for each uploaded submission.

== Screenshots ==

1. Plugin settings page
2. Google Drive connection status
3. Google Drive column in WPForms entries list
4. Example folder on Google Drive
5. Contents of form-data.txt file

== Changelog ==

= 1.0.1 =
* Fixed: Undefined variable in settings template
* Improved: Copy to clipboard function (JS)
* Updated: License to GPL v3
* Optimized: composer.json configuration
* Removed: Unused PSR-4 namespace

= 1.0.0 =
* Initial release
* Full WPForms integration
* OAuth2 authentication
* Automatic file upload
* Automatic folder creation
* Form data export

== Upgrade Notice ==

= 1.0.1 =
Bug fixes and improvements. Recommended update for all users.

= 1.0.0 =
Initial plugin release.

== Privacy Policy ==

This plugin:
* Does not collect user data
* Does not send data to external services except Google Drive (with user consent)
* Stores OAuth tokens securely in the WordPress database
* Does not use cookies or tracking

== Credits ==

Developed by Fabio D'Alessandro
GitHub Repository: https://github.com/fabiodalez-dev/wpforms-drive

== Support ==

For support or bug reports, open an issue on GitHub:
https://github.com/fabiodalez-dev/wpforms-drive/issues

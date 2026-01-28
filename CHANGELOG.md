# Changelog

All notable changes to this project will be documented in this file.

## [1.0.2] - 2026-01-28

### Added

- **Debug logging system** - Added comprehensive logging for troubleshooting (enabled when WP_DEBUG is true)
- **Error handling** - Added try-catch blocks around all initialization code
- **Admin error notices** - Shows helpful error messages in WordPress admin when something goes wrong
- **Dependency checks** - Validates Composer dependencies and Google API Client before loading

### Fixed

- **Fatal error handling** - Plugin now gracefully handles missing dependencies instead of causing critical errors
- **Better error messages** - Clear instructions when `composer install` hasn't been run

---

## [1.0.1] - 2026-01-28

### Fixed

- **Undefined variable in settings template** - Fixed `$google_drive` variable not being passed to the settings template, which caused a fatal error when trying to connect to Google Drive.

### Improved

- **Copy to clipboard function** - Improved JavaScript copy function with modern Clipboard API support and fallback for older browsers.

### Changed

- **License updated to GPL v3** - Updated license from GPL v2 to GPL v3 or later across all files.
- **Optimized composer.json** - Removed unused PSR-4 autoload namespace configuration.

### Removed

- **INSTALL.md** - Installation instructions consolidated into README.md.

---

## [1.0.0] - 2026-01-28

### Initial Release

First version of the plugin with the following features:

#### Features

- OAuth2 authentication with Google Drive
- Automatic file upload from WPForms to Google Drive
- Automatic folder creation for submissions
- Form data saved as text file
- Large file support with chunking (1MB chunks)
- WordPress administration interface
- Google Drive column in WPForms entries list

#### Components

- `wpforms-google-drive.php`: Main plugin file
- `classes/class-google-drive-manager.php`: Google Drive API management
- `classes/class-wpforms-handler.php`: WPForms integration
- `classes/class-admin.php`: Administration interface
- `views/admin/settings.php`: Settings template
- `assets/`: CSS and JavaScript
- `composer.json`: Google API Client dependencies

#### Documentation

- README.md: General documentation
- readme.txt: WordPress standard format
- CHANGELOG.md: Version history

---

## Format

This changelog follows the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format.

### Types of Changes

- **Added** for new features
- **Changed** for changes in existing functionality
- **Deprecated** for soon-to-be removed features
- **Removed** for now removed features
- **Fixed** for any bug fixes
- **Security** for vulnerability fixes

<?php
/**
 * Plugin Name: WPForms Google Drive Integration
 * Plugin URI: https://github.com/fabiodalez-dev/wpforms-drive
 * Description: Integrates WPForms with Google Drive to automatically upload files and submission data with automatic folder organization.
 * Version: 1.0.2
 * Author: Fabio D'Alessandro
 * Author URI: https://github.com/fabiodalez-dev
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wpforms-google-drive
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('WPFORMS_GDRIVE_VERSION', '1.0.2');
define('WPFORMS_GDRIVE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPFORMS_GDRIVE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPFORMS_GDRIVE_PLUGIN_FILE', __FILE__);

/**
 * Debug logger function
 */
function wpforms_gdrive_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = '[WPForms GDrive] ' . $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

/**
 * Main plugin class
 */
class WPForms_Google_Drive {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Google Drive manager
     */
    public $google_drive = null;

    /**
     * Admin manager
     */
    public $admin = null;

    /**
     * WPForms handler
     */
    public $wpforms_handler = null;

    /**
     * Dependencies loaded flag
     */
    private $dependencies_loaded = false;

    /**
     * Error messages
     */
    private $errors = array();

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load dependencies
        $this->load_dependencies();

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'), 20);

        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Admin notices for errors
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }

    /**
     * Load dependencies
     */
    private function load_dependencies() {
        wpforms_gdrive_log('Loading dependencies...');

        // Check for Composer autoloader
        $autoloader = WPFORMS_GDRIVE_PLUGIN_DIR . 'vendor/autoload.php';

        if (!file_exists($autoloader)) {
            $this->errors[] = sprintf(
                __('WPForms Google Drive: Composer dependencies not installed. Please run <code>composer install</code> in the plugin directory: %s', 'wpforms-google-drive'),
                WPFORMS_GDRIVE_PLUGIN_DIR
            );
            wpforms_gdrive_log('ERROR: vendor/autoload.php not found at: ' . $autoloader);
            return;
        }

        try {
            require_once $autoloader;
            wpforms_gdrive_log('Composer autoloader loaded successfully');
        } catch (Exception $e) {
            $this->errors[] = __('WPForms Google Drive: Error loading Composer autoloader: ', 'wpforms-google-drive') . $e->getMessage();
            wpforms_gdrive_log('ERROR loading autoloader: ' . $e->getMessage());
            return;
        }

        // Check if Google Client class exists
        if (!class_exists('Google_Client')) {
            $this->errors[] = __('WPForms Google Drive: Google API Client library not found. Please run <code>composer install</code>.', 'wpforms-google-drive');
            wpforms_gdrive_log('ERROR: Google_Client class not found');
            return;
        }

        wpforms_gdrive_log('Google_Client class found');

        // Load plugin classes
        $class_files = array(
            'class-google-drive-manager.php',
            'class-wpforms-handler.php',
            'class-admin.php',
        );

        foreach ($class_files as $file) {
            $file_path = WPFORMS_GDRIVE_PLUGIN_DIR . 'classes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
                wpforms_gdrive_log('Loaded class file: ' . $file);
            } else {
                $this->errors[] = sprintf(__('WPForms Google Drive: Required file not found: %s', 'wpforms-google-drive'), $file);
                wpforms_gdrive_log('ERROR: Class file not found: ' . $file_path);
                return;
            }
        }

        $this->dependencies_loaded = true;
        wpforms_gdrive_log('All dependencies loaded successfully');
    }

    /**
     * Initialize plugin
     */
    public function init() {
        wpforms_gdrive_log('Initializing plugin...');

        // Check if dependencies are loaded
        if (!$this->dependencies_loaded) {
            wpforms_gdrive_log('ERROR: Dependencies not loaded, aborting init');
            return;
        }

        // Check if WPForms is active
        if (!$this->is_wpforms_active()) {
            add_action('admin_notices', array($this, 'wpforms_missing_notice'));
            wpforms_gdrive_log('WPForms not active');
            return;
        }

        wpforms_gdrive_log('WPForms is active');

        // Load translations
        load_plugin_textdomain('wpforms-google-drive', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize components with error handling
        try {
            wpforms_gdrive_log('Creating WPForms_GDrive_Manager...');
            $this->google_drive = new WPForms_GDrive_Manager();
            wpforms_gdrive_log('WPForms_GDrive_Manager created successfully');

            wpforms_gdrive_log('Creating WPForms_GDrive_Handler...');
            $this->wpforms_handler = new WPForms_GDrive_Handler($this->google_drive);
            wpforms_gdrive_log('WPForms_GDrive_Handler created successfully');

            // Initialize admin only in backend
            if (is_admin()) {
                wpforms_gdrive_log('Creating WPForms_GDrive_Admin...');
                $this->admin = new WPForms_GDrive_Admin($this->google_drive);
                wpforms_gdrive_log('WPForms_GDrive_Admin created successfully');
            }

            wpforms_gdrive_log('Plugin initialized successfully');

        } catch (Exception $e) {
            $this->errors[] = __('WPForms Google Drive initialization error: ', 'wpforms-google-drive') . $e->getMessage();
            wpforms_gdrive_log('ERROR during initialization: ' . $e->getMessage());
            wpforms_gdrive_log('Stack trace: ' . $e->getTraceAsString());
        } catch (Error $e) {
            $this->errors[] = __('WPForms Google Drive fatal error: ', 'wpforms-google-drive') . $e->getMessage();
            wpforms_gdrive_log('FATAL ERROR during initialization: ' . $e->getMessage());
            wpforms_gdrive_log('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Check if WPForms is active
     */
    private function is_wpforms_active() {
        return class_exists('WPForms');
    }

    /**
     * Display admin notices for errors
     */
    public function display_admin_notices() {
        if (empty($this->errors)) {
            return;
        }

        foreach ($this->errors as $error) {
            ?>
            <div class="notice notice-error">
                <p><?php echo wp_kses_post($error); ?></p>
            </div>
            <?php
        }
    }

    /**
     * WPForms missing notice
     */
    public function wpforms_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo sprintf(
                    __('The <strong>WPForms Google Drive Integration</strong> plugin requires <strong>WPForms</strong> to work. Please <a href="%s" target="_blank">install WPForms</a>.', 'wpforms-google-drive'),
                    'https://wordpress.org/plugins/wpforms-lite/'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Plugin activation
     */
    public function activate() {
        wpforms_gdrive_log('Activating plugin...');

        // Create default options
        $default_options = array(
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => admin_url('admin.php?page=wpforms-google-drive'),
            'access_token' => '',
            'refresh_token' => '',
            'root_folder_id' => '',
            'enabled' => false,
        );

        add_option('wpforms_gdrive_settings', $default_options);

        // Flush rewrite rules
        flush_rewrite_rules();

        wpforms_gdrive_log('Plugin activated');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        wpforms_gdrive_log('Deactivating plugin...');
        flush_rewrite_rules();
        wpforms_gdrive_log('Plugin deactivated');
    }

    /**
     * Get errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Check if plugin is ready
     */
    public function is_ready() {
        return $this->dependencies_loaded && empty($this->errors) && $this->google_drive !== null;
    }
}

/**
 * Get plugin instance
 */
function wpforms_google_drive() {
    return WPForms_Google_Drive::get_instance();
}

// Initialize plugin
wpforms_google_drive();

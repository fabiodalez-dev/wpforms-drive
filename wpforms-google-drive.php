<?php
/**
 * Plugin Name: WPForms Google Drive Integration
 * Plugin URI: https://github.com/fabiodalez-dev/wpforms-drive
 * Description: Integrazione tra WPForms e Google Drive per caricare file e dati delle submission su Google Drive con organizzazione automatica in cartelle.
 * Version: 1.0.1
 * Author: Fabio D'Alessandro
 * Author URI: https://github.com/fabiodalez-dev
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wpforms-google-drive
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definizione costanti
define('WPFORMS_GDRIVE_VERSION', '1.0.1');
define('WPFORMS_GDRIVE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPFORMS_GDRIVE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPFORMS_GDRIVE_PLUGIN_FILE', __FILE__);

/**
 * Classe principale del plugin
 */
class WPForms_Google_Drive {

    /**
     * Istanza singleton
     */
    private static $instance = null;

    /**
     * Gestore Google Drive
     */
    public $google_drive = null;

    /**
     * Gestore Admin
     */
    public $admin = null;

    /**
     * Gestore WPForms
     */
    public $wpforms_handler = null;

    /**
     * Ottiene l'istanza singleton
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Costruttore
     */
    private function __construct() {
        // Carica l'autoloader di Composer
        $this->load_dependencies();

        // Inizializza il plugin
        add_action('plugins_loaded', array($this, 'init'));

        // Hook di attivazione e disattivazione
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Carica le dipendenze
     */
    private function load_dependencies() {
        // Autoloader Composer
        if (file_exists(WPFORMS_GDRIVE_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once WPFORMS_GDRIVE_PLUGIN_DIR . 'vendor/autoload.php';
        }

        // Carica le classi del plugin
        require_once WPFORMS_GDRIVE_PLUGIN_DIR . 'classes/class-google-drive-manager.php';
        require_once WPFORMS_GDRIVE_PLUGIN_DIR . 'classes/class-wpforms-handler.php';
        require_once WPFORMS_GDRIVE_PLUGIN_DIR . 'classes/class-admin.php';
    }

    /**
     * Inizializza il plugin
     */
    public function init() {
        // Verifica che WPForms sia attivo
        if (!$this->is_wpforms_active()) {
            add_action('admin_notices', array($this, 'wpforms_missing_notice'));
            return;
        }

        // Carica la traduzione
        load_plugin_textdomain('wpforms-google-drive', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Inizializza i componenti
        $this->google_drive = new WPForms_GDrive_Manager();
        $this->wpforms_handler = new WPForms_GDrive_Handler($this->google_drive);

        // Inizializza l'admin solo nel backend
        if (is_admin()) {
            $this->admin = new WPForms_GDrive_Admin($this->google_drive);
        }
    }

    /**
     * Verifica se WPForms è attivo
     */
    private function is_wpforms_active() {
        return class_exists('WPForms');
    }

    /**
     * Mostra avviso se WPForms non è installato
     */
    public function wpforms_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo sprintf(
                    __('Il plugin <strong>WPForms Google Drive Integration</strong> richiede <strong>WPForms</strong> per funzionare. Per favore <a href="%s" target="_blank">installa WPForms</a>.', 'wpforms-google-drive'),
                    'https://wordpress.org/plugins/wpforms-lite/'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Attivazione del plugin
     */
    public function activate() {
        // Crea le opzioni di default
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
    }

    /**
     * Disattivazione del plugin
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Inizializza il plugin
 */
function wpforms_google_drive() {
    return WPForms_Google_Drive::get_instance();
}

// Avvia il plugin
wpforms_google_drive();

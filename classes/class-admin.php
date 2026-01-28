<?php
/**
 * Classe per gestire l'interfaccia di amministrazione
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPForms_GDrive_Admin {

    /**
     * Gestore Google Drive
     */
    private $google_drive;

    /**
     * Costruttore
     */
    public function __construct($google_drive) {
        $this->google_drive = $google_drive;

        // Registra i hooks
        $this->init_hooks();
    }

    /**
     * Inizializza gli hooks
     */
    private function init_hooks() {
        // Aggiunge la pagina al menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Registra le impostazioni
        add_action('admin_init', array($this, 'register_settings'));

        // Gestisce il callback OAuth
        add_action('admin_init', array($this, 'handle_oauth_callback'));

        // Enqueue scripts e styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // Aggiunge link alle impostazioni nella pagina dei plugin
        add_filter('plugin_action_links_' . plugin_basename(WPFORMS_GDRIVE_PLUGIN_FILE), array($this, 'add_action_links'));

        // Aggiunge colonna nella lista entries di WPForms
        add_filter('wpforms_entries_table_columns', array($this, 'add_drive_column'), 10, 2);
        add_action('wpforms_entries_table_column_value', array($this, 'render_drive_column'), 10, 3);
    }

    /**
     * Aggiunge la pagina al menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'wpforms-overview',
            __('Google Drive', 'wpforms-google-drive'),
            __('Google Drive', 'wpforms-google-drive'),
            'manage_options',
            'wpforms-google-drive',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Registra le impostazioni
     */
    public function register_settings() {
        register_setting('wpforms_gdrive_settings_group', 'wpforms_gdrive_settings');
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets($hook) {
        // Carica solo nella nostra pagina
        if ($hook !== 'wpforms_page_wpforms-google-drive') {
            return;
        }

        wp_enqueue_style(
            'wpforms-gdrive-admin',
            WPFORMS_GDRIVE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WPFORMS_GDRIVE_VERSION
        );

        wp_enqueue_script(
            'wpforms-gdrive-admin',
            WPFORMS_GDRIVE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WPFORMS_GDRIVE_VERSION,
            true
        );
    }

    /**
     * Aggiunge link alle impostazioni
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=wpforms-google-drive'),
            __('Impostazioni', 'wpforms-google-drive')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Gestisce il callback OAuth
     */
    public function handle_oauth_callback() {
        // Verifica se è un callback OAuth
        if (!isset($_GET['page']) || $_GET['page'] !== 'wpforms-google-drive') {
            return;
        }

        if (!isset($_GET['code'])) {
            return;
        }

        // Verifica il nonce
        if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'wpforms_gdrive_oauth')) {
            wp_die(__('Richiesta non valida.', 'wpforms-google-drive'));
        }

        // Gestisce il callback
        $code = sanitize_text_field($_GET['code']);
        $result = $this->google_drive->handle_oauth_callback($code);

        if ($result) {
            wp_redirect(admin_url('admin.php?page=wpforms-google-drive&oauth=success'));
        } else {
            wp_redirect(admin_url('admin.php?page=wpforms-google-drive&oauth=error'));
        }
        exit;
    }

    /**
     * Renderizza la pagina di amministrazione
     */
    public function render_admin_page() {
        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.', 'wpforms-google-drive'));
        }

        // Gestisce il salvataggio delle impostazioni
        if (isset($_POST['wpforms_gdrive_save_settings']) && check_admin_referer('wpforms_gdrive_settings')) {
            $this->save_settings();
        }

        // Gestisce la disconnessione
        if (isset($_GET['action']) && $_GET['action'] === 'disconnect' && check_admin_referer('wpforms_gdrive_disconnect')) {
            $this->google_drive->disconnect();
            wp_redirect(admin_url('admin.php?page=wpforms-google-drive&disconnected=1'));
            exit;
        }

        // Ottiene le impostazioni
        $settings = $this->google_drive->get_settings();
        $is_authenticated = $this->google_drive->is_authenticated();

        // Include il template
        include WPFORMS_GDRIVE_PLUGIN_DIR . 'views/admin/settings.php';
    }

    /**
     * Salva le impostazioni
     */
    private function save_settings() {
        $settings = array();

        // Salva le credenziali OAuth
        if (isset($_POST['client_id'])) {
            $settings['client_id'] = sanitize_text_field($_POST['client_id']);
        }

        if (isset($_POST['client_secret'])) {
            $settings['client_secret'] = sanitize_text_field($_POST['client_secret']);
        }

        // Salva la cartella radice
        if (isset($_POST['root_folder_id'])) {
            $settings['root_folder_id'] = sanitize_text_field($_POST['root_folder_id']);
        }

        // Salva lo stato enabled
        $settings['enabled'] = isset($_POST['enabled']) ? true : false;

        // Aggiorna le impostazioni
        $this->google_drive->update_settings($settings);

        // Redirect con messaggio di successo
        wp_redirect(admin_url('admin.php?page=wpforms-google-drive&settings-updated=1'));
        exit;
    }

    /**
     * Aggiunge colonna Google Drive nella lista entries
     */
    public function add_drive_column($columns, $form_id) {
        $columns['google_drive'] = __('Google Drive', 'wpforms-google-drive');
        return $columns;
    }

    /**
     * Renderizza la colonna Google Drive
     */
    public function render_drive_column($value, $entry, $column_name) {
        if ($column_name !== 'google_drive') {
            return;
        }

        // Ottiene l'URL della cartella Google Drive
        $handler = wpforms_google_drive()->wpforms_handler;
        if (!$handler) {
            return;
        }

        $drive_url = $handler->get_entry_drive_url($entry->entry_id);

        if ($drive_url) {
            printf(
                '<a href="%s" target="_blank" class="button button-small">%s</a>',
                esc_url($drive_url),
                __('Apri su Drive', 'wpforms-google-drive')
            );
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}

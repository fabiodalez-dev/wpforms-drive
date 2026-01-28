<?php
/**
 * Classe per gestire l'integrazione con WPForms
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPForms_GDrive_Handler {

    /**
     * Gestore Google Drive
     */
    private $google_drive;

    /**
     * Impostazioni
     */
    private $settings;

    /**
     * Costruttore
     */
    public function __construct($google_drive) {
        $this->google_drive = $google_drive;
        $this->settings = get_option('wpforms_gdrive_settings', array());

        // Registra i hooks
        $this->init_hooks();
    }

    /**
     * Inizializza gli hooks
     */
    private function init_hooks() {
        // Hook dopo il processo della submission
        add_action('wpforms_process_complete', array($this, 'process_submission'), 10, 4);

        // Hook per aggiungere metadati alla submission
        add_filter('wpforms_entry_save_data', array($this, 'add_drive_metadata'), 10, 3);
    }

    /**
     * Processa la submission e carica su Google Drive
     */
    public function process_submission($fields, $entry, $form_data, $entry_id) {
        // Verifica se l'integrazione è abilitata
        if (empty($this->settings['enabled']) || !$this->google_drive->is_authenticated()) {
            return;
        }

        // Verifica se il form è abilitato per Google Drive
        if (!$this->is_form_enabled($form_data)) {
            return;
        }

        try {
            // Crea la cartella per questa submission
            $folder = $this->create_submission_folder($form_data, $entry_id);

            if (!$folder) {
                error_log('WPForms Google Drive - Impossibile creare la cartella per la submission ' . $entry_id);
                return;
            }

            // Salva i dati del form come file di testo
            $this->save_form_data($fields, $entry, $form_data, $folder['id']);

            // Carica i file allegati
            $uploaded_files = $this->upload_attached_files($fields, $form_data, $folder['id']);

            // Salva l'URL della cartella nei metadati dell'entry
            $this->save_drive_url($entry_id, $folder['url'], $uploaded_files);

            // Log successo
            error_log('WPForms Google Drive - Submission ' . $entry_id . ' caricata su Google Drive: ' . $folder['url']);
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore processamento submission ' . $entry_id . ': ' . $e->getMessage());
        }
    }

    /**
     * Verifica se il form è abilitato per Google Drive
     */
    private function is_form_enabled($form_data) {
        // Per ora abilita tutti i form
        // In futuro si può aggiungere una logica per abilitare solo alcuni form
        return true;
    }

    /**
     * Crea la cartella per la submission
     */
    private function create_submission_folder($form_data, $entry_id) {
        // Crea il nome della cartella
        $form_name = !empty($form_data['settings']['form_title']) ? $form_data['settings']['form_title'] : 'Form';
        $date = date('Y-m-d_H-i-s');
        $folder_name = sprintf('Form_%s_%s_%d', $form_name, $date, $entry_id);

        // Crea la cartella radice "WPForms Submissions" se non esiste
        $root_folder_id = $this->get_or_create_root_folder();

        // Crea la cartella per questa submission
        return $this->google_drive->create_folder($folder_name, $root_folder_id);
    }

    /**
     * Ottiene o crea la cartella radice
     */
    private function get_or_create_root_folder() {
        // Se è già impostata nelle impostazioni, usa quella
        if (!empty($this->settings['root_folder_id'])) {
            return $this->settings['root_folder_id'];
        }

        // Altrimenti crea la cartella "WPForms Submissions"
        $folder = $this->google_drive->create_folder('WPForms Submissions');

        if ($folder) {
            $this->settings['root_folder_id'] = $folder['id'];
            update_option('wpforms_gdrive_settings', $this->settings);
            return $folder['id'];
        }

        return null;
    }

    /**
     * Salva i dati del form come file di testo
     */
    private function save_form_data($fields, $entry, $form_data, $folder_id) {
        // Prepara il contenuto del file
        $content = $this->format_form_data($fields, $entry, $form_data);

        // Carica il file su Google Drive
        $file_name = 'form-data.txt';
        return $this->google_drive->upload_content($content, $file_name, $folder_id, 'text/plain');
    }

    /**
     * Formatta i dati del form in formato leggibile
     */
    private function format_form_data($fields, $entry, $form_data) {
        $content = "=================================================\n";
        $content .= "WPFORMS SUBMISSION DATA\n";
        $content .= "=================================================\n\n";

        // Informazioni generali
        $content .= "Form: " . (!empty($form_data['settings']['form_title']) ? $form_data['settings']['form_title'] : 'N/A') . "\n";
        $content .= "Entry ID: " . $entry['id'] . "\n";
        $content .= "Date: " . date('Y-m-d H:i:s', strtotime($entry['date'])) . "\n";

        if (!empty($entry['ip_address'])) {
            $content .= "IP Address: " . $entry['ip_address'] . "\n";
        }

        if (!empty($entry['user_agent'])) {
            $content .= "User Agent: " . $entry['user_agent'] . "\n";
        }

        $content .= "\n=================================================\n";
        $content .= "FORM FIELDS\n";
        $content .= "=================================================\n\n";

        // Campi del form
        foreach ($fields as $field_id => $field) {
            // Ottiene il nome del campo
            $field_name = !empty($form_data['fields'][$field_id]['label']) ? $form_data['fields'][$field_id]['label'] : 'Field ' . $field_id;

            // Ottiene il valore del campo
            $field_value = $this->get_field_value($field);

            $content .= $field_name . ":\n";
            $content .= $field_value . "\n\n";
        }

        $content .= "=================================================\n";
        $content .= "END OF SUBMISSION\n";
        $content .= "=================================================\n";

        return $content;
    }

    /**
     * Ottiene il valore di un campo in formato stringa
     */
    private function get_field_value($field) {
        if (empty($field)) {
            return '(empty)';
        }

        // Se è un array (es. checkbox, indirizzo, ecc.)
        if (is_array($field)) {
            // Se ha una chiave 'value', usa quella
            if (isset($field['value'])) {
                if (is_array($field['value'])) {
                    return '  - ' . implode("\n  - ", array_filter($field['value']));
                }

                // Per i campi file upload, formatta gli URL in modo leggibile
                if (isset($field['type']) && $field['type'] === 'file-upload') {
                    $file_urls = explode("\n", trim($field['value']));
                    if (count($file_urls) > 1) {
                        return "  - " . implode("\n  - ", array_map('trim', $file_urls));
                    }
                }

                return $field['value'];
            }

            // Altrimenti formatta l'array
            $values = array();
            foreach ($field as $key => $value) {
                if (!is_numeric($key) && !in_array($key, array('type', 'id', 'name'))) {
                    $values[] = ucfirst($key) . ': ' . $value;
                } elseif (is_numeric($key)) {
                    $values[] = $value;
                }
            }
            return '  ' . implode("\n  ", array_filter($values));
        }

        return $field;
    }

    /**
     * Carica i file allegati
     */
    private function upload_attached_files($fields, $form_data, $folder_id) {
        $uploaded_files = array();

        foreach ($fields as $field_id => $field) {
            // Verifica se è un campo file
            if (!isset($form_data['fields'][$field_id]) || $form_data['fields'][$field_id]['type'] !== 'file-upload') {
                continue;
            }

            // Ottiene i file caricati
            $files = $this->get_field_files($field);

            if (empty($files)) {
                continue;
            }

            // Carica ogni file
            foreach ($files as $file_path) {
                if (!file_exists($file_path)) {
                    continue;
                }

                $file_name = basename($file_path);
                $result = $this->google_drive->upload_file($file_path, $file_name, $folder_id);

                if ($result) {
                    $uploaded_files[] = array(
                        'name' => $result['name'],
                        'url' => $result['url'],
                        'id' => $result['id']
                    );
                }
            }
        }

        return $uploaded_files;
    }

    /**
     * Ottiene i percorsi dei file da un campo
     *
     * Secondo la documentazione WPForms, i file upload sono salvati come:
     * - Stringa singola con URL per un file
     * - Più URL separati da newline (\n) per file multipli
     */
    private function get_field_files($field) {
        $files = array();

        if (empty($field)) {
            return $files;
        }

        // Il valore del campo è sempre in $field['value']
        if (!isset($field['value']) || empty($field['value'])) {
            return $files;
        }

        $value = $field['value'];

        // Se è una stringa, potrebbe contenere uno o più URL separati da newline
        if (is_string($value)) {
            // Separa gli URL per newline (per file multipli)
            $file_urls = explode("\n", trim($value));

            foreach ($file_urls as $url) {
                $url = trim($url);
                if (empty($url)) {
                    continue;
                }

                // Converte l'URL in percorso filesystem
                $file_path = $this->url_to_path($url);

                if ($file_path && file_exists($file_path)) {
                    $files[] = $file_path;
                } else {
                    error_log('WPForms Google Drive - File non trovato: ' . $url . ' (Path: ' . $file_path . ')');
                }
            }
        }

        return $files;
    }

    /**
     * Converte un URL di file in percorso filesystem
     *
     * @param string $url URL del file
     * @return string|false Percorso del file o false se non valido
     */
    private function url_to_path($url) {
        // Se è già un percorso filesystem, ritorna così com'è
        if (file_exists($url)) {
            return $url;
        }

        // Rimuove il dominio per ottenere il percorso relativo
        $site_url = site_url();
        $upload_dir = wp_upload_dir();

        // Se l'URL inizia con il site URL, rimuovilo
        if (strpos($url, $site_url) === 0) {
            $relative_path = str_replace($site_url, '', $url);
        } else {
            // Prova a estrarre il percorso dall'URL
            $parsed_url = parse_url($url);
            $relative_path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        }

        // Rimuove lo slash iniziale
        $relative_path = ltrim($relative_path, '/');

        // Prova diversi percorsi possibili
        $possible_paths = array(
            // Percorso completo da ABSPATH
            ABSPATH . $relative_path,
            // Percorso nella cartella uploads
            $upload_dir['basedir'] . '/' . basename($url),
            // Percorso relativo a wp-content
            WP_CONTENT_DIR . '/' . $relative_path,
            // Se l'URL contiene wp-content, prende tutto dopo
            WP_CONTENT_DIR . '/' . substr($relative_path, strpos($relative_path, 'wp-content/') + 11)
        );

        // Prova ogni percorso
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Se nessun percorso funziona, ritorna false
        return false;
    }

    /**
     * Salva l'URL di Google Drive nei metadati dell'entry
     */
    private function save_drive_url($entry_id, $folder_url, $uploaded_files) {
        global $wpdb;

        // Prepara i metadati
        $metadata = array(
            'google_drive_folder_url' => $folder_url,
            'google_drive_uploaded_at' => current_time('mysql'),
            'google_drive_files_count' => count($uploaded_files),
            'google_drive_files' => $uploaded_files
        );

        // Verifica se la tabella wpforms_entry_meta esiste
        $table_name = $wpdb->prefix . 'wpforms_entry_meta';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;

        if ($table_exists) {
            // Usa wpdb per inserire i metadati nella tabella WPForms
            foreach ($metadata as $meta_key => $meta_value) {
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'entry_id' => $entry_id,
                        'meta_key' => $meta_key,
                        'meta_value' => is_array($meta_value) ? wp_json_encode($meta_value) : $meta_value
                    ),
                    array('%d', '%s', '%s')
                );

                if ($result === false) {
                    error_log('WPForms Google Drive - Errore salvataggio metadata: ' . $wpdb->last_error);
                }
            }
        } else {
            error_log('WPForms Google Drive - Tabella wpforms_entry_meta non trovata');
        }

        // Salva anche come opzione WordPress di backup per facile recupero
        update_option('wpforms_gdrive_entry_' . $entry_id, $metadata, false);
    }

    /**
     * Aggiunge metadati alla submission
     */
    public function add_drive_metadata($entry_data, $entry, $form_data) {
        // Questo hook permette di aggiungere dati personalizzati alla submission
        // Per ora ritorna i dati così come sono
        return $entry_data;
    }

    /**
     * Ottiene l'URL della cartella Google Drive per un'entry
     */
    public function get_entry_drive_url($entry_id) {
        global $wpdb;

        // Prova prima dalla tabella wpforms_entry_meta
        $table_name = $wpdb->prefix . 'wpforms_entry_meta';
        $url = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$table_name} WHERE entry_id = %d AND meta_key = %s",
            $entry_id,
            'google_drive_folder_url'
        ));

        if ($url) {
            return $url;
        }

        // Fallback: prova dalle opzioni WordPress
        $metadata = get_option('wpforms_gdrive_entry_' . $entry_id);

        if ($metadata && isset($metadata['google_drive_folder_url'])) {
            return $metadata['google_drive_folder_url'];
        }

        return null;
    }
}

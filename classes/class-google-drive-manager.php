<?php
/**
 * Classe per gestire Google Drive API
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPForms_GDrive_Manager {

    /**
     * Client Google
     */
    private $client;

    /**
     * Service Google Drive
     */
    private $service;

    /**
     * Impostazioni
     */
    private $settings;

    /**
     * Costruttore
     */
    public function __construct() {
        $this->settings = get_option('wpforms_gdrive_settings', array());
        $this->init_client();
    }

    /**
     * Inizializza il client Google
     */
    private function init_client() {
        if (!class_exists('Google_Client')) {
            return;
        }

        try {
            $this->client = new Google_Client();
            $this->client->setApplicationName('WPForms Google Drive Integration');
            $this->client->setScopes([
                Google_Service_Drive::DRIVE_FILE,
                Google_Service_Drive::DRIVE
            ]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');

            // Configura le credenziali se disponibili
            if (!empty($this->settings['client_id']) && !empty($this->settings['client_secret'])) {
                $this->client->setClientId($this->settings['client_id']);
                $this->client->setClientSecret($this->settings['client_secret']);
                $this->client->setRedirectUri($this->settings['redirect_uri']);

                // Imposta il token di accesso se disponibile
                if (!empty($this->settings['access_token'])) {
                    $this->client->setAccessToken($this->settings['access_token']);

                    // Verifica se il token è scaduto e lo aggiorna
                    if ($this->client->isAccessTokenExpired() && !empty($this->settings['refresh_token'])) {
                        $this->client->fetchAccessTokenWithRefreshToken($this->settings['refresh_token']);
                        $this->save_token($this->client->getAccessToken());
                    }
                }

                // Inizializza il servizio Drive
                $this->service = new Google_Service_Drive($this->client);
            }
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore inizializzazione client: ' . $e->getMessage());
        }
    }

    /**
     * Ottiene l'URL di autorizzazione
     */
    public function get_auth_url() {
        if (!$this->client) {
            return false;
        }

        return $this->client->createAuthUrl();
    }

    /**
     * Gestisce il callback OAuth
     */
    public function handle_oauth_callback($code) {
        if (!$this->client) {
            return false;
        }

        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                error_log('WPForms Google Drive - Errore OAuth: ' . $token['error']);
                return false;
            }

            // Salva il token
            $this->save_token($token);

            // Salva il refresh token
            if (isset($token['refresh_token'])) {
                $this->settings['refresh_token'] = $token['refresh_token'];
                update_option('wpforms_gdrive_settings', $this->settings);
            }

            return true;
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore callback OAuth: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva il token di accesso
     */
    private function save_token($token) {
        $this->settings['access_token'] = $token;
        update_option('wpforms_gdrive_settings', $this->settings);
    }

    /**
     * Verifica se è autenticato
     */
    public function is_authenticated() {
        return $this->client && !empty($this->settings['access_token']) && !$this->client->isAccessTokenExpired();
    }

    /**
     * Disconnette l'account
     */
    public function disconnect() {
        if ($this->client && !empty($this->settings['access_token'])) {
            try {
                $this->client->revokeToken();
            } catch (Exception $e) {
                error_log('WPForms Google Drive - Errore revoca token: ' . $e->getMessage());
            }
        }

        // Rimuove i token
        $this->settings['access_token'] = '';
        $this->settings['refresh_token'] = '';
        update_option('wpforms_gdrive_settings', $this->settings);
    }

    /**
     * Crea una cartella su Google Drive
     */
    public function create_folder($folder_name, $parent_id = null) {
        if (!$this->is_authenticated() || !$this->service) {
            return false;
        }

        try {
            $file_metadata = new Google_Service_Drive_DriveFile(array(
                'name' => $this->sanitize_filename($folder_name),
                'mimeType' => 'application/vnd.google-apps.folder'
            ));

            // Se c'è un parent ID, imposta la cartella parent
            if ($parent_id) {
                $file_metadata->setParents(array($parent_id));
            } elseif (!empty($this->settings['root_folder_id'])) {
                $file_metadata->setParents(array($this->settings['root_folder_id']));
            }

            $folder = $this->service->files->create($file_metadata, array(
                'fields' => 'id, name, webViewLink'
            ));

            return array(
                'id' => $folder->id,
                'name' => $folder->name,
                'url' => $folder->webViewLink
            );
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore creazione cartella: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Carica un file su Google Drive
     */
    public function upload_file($file_path, $file_name, $folder_id = null, $mime_type = null) {
        if (!$this->is_authenticated() || !$this->service) {
            return false;
        }

        if (!file_exists($file_path)) {
            error_log('WPForms Google Drive - File non trovato: ' . $file_path);
            return false;
        }

        try {
            // Determina il MIME type se non fornito
            if (!$mime_type) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_path);
                finfo_close($finfo);
            }

            $file_metadata = new Google_Service_Drive_DriveFile(array(
                'name' => $this->sanitize_filename($file_name)
            ));

            // Imposta la cartella parent
            if ($folder_id) {
                $file_metadata->setParents(array($folder_id));
            } elseif (!empty($this->settings['root_folder_id'])) {
                $file_metadata->setParents(array($this->settings['root_folder_id']));
            }

            // Carica il file con chunking per file grandi
            $this->client->setDefer(true);

            $request = $this->service->files->create($file_metadata);

            $media = new Google_Http_MediaFileUpload(
                $this->client,
                $request,
                $mime_type,
                null,
                true,
                1048576 // Chunk size: 1MB
            );

            $media->setFileSize(filesize($file_path));

            $status = false;
            $handle = fopen($file_path, 'rb');

            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1048576);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);
            $this->client->setDefer(false);

            if ($status) {
                return array(
                    'id' => $status->id,
                    'name' => $status->name,
                    'url' => 'https://drive.google.com/file/d/' . $status->id . '/view'
                );
            }

            return false;
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore upload file: ' . $e->getMessage());
            $this->client->setDefer(false);
            return false;
        }
    }

    /**
     * Carica il contenuto di un file da stringa
     */
    public function upload_content($content, $file_name, $folder_id = null, $mime_type = 'text/plain') {
        if (!$this->is_authenticated() || !$this->service) {
            return false;
        }

        try {
            $file_metadata = new Google_Service_Drive_DriveFile(array(
                'name' => $this->sanitize_filename($file_name)
            ));

            // Imposta la cartella parent
            if ($folder_id) {
                $file_metadata->setParents(array($folder_id));
            } elseif (!empty($this->settings['root_folder_id'])) {
                $file_metadata->setParents(array($this->settings['root_folder_id']));
            }

            $file = $this->service->files->create($file_metadata, array(
                'data' => $content,
                'mimeType' => $mime_type,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink'
            ));

            return array(
                'id' => $file->id,
                'name' => $file->name,
                'url' => $file->webViewLink
            );
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore upload contenuto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista le cartelle in Google Drive
     */
    public function list_folders($parent_id = 'root') {
        if (!$this->is_authenticated() || !$this->service) {
            return array();
        }

        try {
            $query = "mimeType='application/vnd.google-apps.folder'";

            if ($parent_id && $parent_id !== 'root') {
                $query .= " and '{$parent_id}' in parents";
            }

            $query .= " and trashed=false";

            $response = $this->service->files->listFiles(array(
                'q' => $query,
                'fields' => 'files(id, name)',
                'orderBy' => 'name',
                'pageSize' => 100
            ));

            $folders = array();
            foreach ($response->files as $file) {
                $folders[] = array(
                    'id' => $file->id,
                    'name' => $file->name
                );
            }

            return $folders;
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore lista cartelle: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Ottiene informazioni su un file/cartella
     */
    public function get_file_info($file_id) {
        if (!$this->is_authenticated() || !$this->service) {
            return false;
        }

        try {
            $file = $this->service->files->get($file_id, array(
                'fields' => 'id, name, mimeType, webViewLink'
            ));

            return array(
                'id' => $file->id,
                'name' => $file->name,
                'mime_type' => $file->mimeType,
                'url' => $file->webViewLink
            );
        } catch (Exception $e) {
            error_log('WPForms Google Drive - Errore info file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sanifica il nome del file
     */
    private function sanitize_filename($filename) {
        // Rimuove caratteri non validi per Google Drive
        $filename = preg_replace('/[<>:"|?*\/\\\\]/', '_', $filename);
        return $filename;
    }

    /**
     * Ottiene le impostazioni
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Aggiorna le impostazioni
     */
    public function update_settings($new_settings) {
        $this->settings = array_merge($this->settings, $new_settings);
        update_option('wpforms_gdrive_settings', $this->settings);

        // Reinizializza il client
        $this->init_client();
    }
}

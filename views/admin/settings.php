<?php
/**
 * Template per la pagina di impostazioni
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wpforms-gdrive-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    // Messaggi di successo/errore
    if (isset($_GET['settings-updated'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Impostazioni salvate con successo!', 'wpforms-google-drive'); ?></p>
        </div>
        <?php
    }

    if (isset($_GET['oauth']) && $_GET['oauth'] === 'success') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Connessione a Google Drive riuscita!', 'wpforms-google-drive'); ?></p>
        </div>
        <?php
    }

    if (isset($_GET['oauth']) && $_GET['oauth'] === 'error') {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Errore durante la connessione a Google Drive. Riprova.', 'wpforms-google-drive'); ?></p>
        </div>
        <?php
    }

    if (isset($_GET['disconnected'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Disconnesso da Google Drive.', 'wpforms-google-drive'); ?></p>
        </div>
        <?php
    }
    ?>

    <div class="wpforms-gdrive-container">
        <div class="wpforms-gdrive-main">
            <!-- Stato della connessione -->
            <div class="wpforms-gdrive-card">
                <h2><?php _e('Stato Connessione', 'wpforms-google-drive'); ?></h2>

                <?php if ($is_authenticated): ?>
                    <div class="wpforms-gdrive-status wpforms-gdrive-status-connected">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <p><?php _e('Connesso a Google Drive', 'wpforms-google-drive'); ?></p>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wpforms-google-drive&action=disconnect'), 'wpforms_gdrive_disconnect'); ?>" class="button">
                            <?php _e('Disconnetti', 'wpforms-google-drive'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="wpforms-gdrive-status wpforms-gdrive-status-disconnected">
                        <span class="dashicons dashicons-warning"></span>
                        <p><?php _e('Non connesso a Google Drive', 'wpforms-google-drive'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Configurazione OAuth -->
            <div class="wpforms-gdrive-card">
                <h2><?php _e('Configurazione OAuth', 'wpforms-google-drive'); ?></h2>

                <form method="post" action="">
                    <?php wp_nonce_field('wpforms_gdrive_settings'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="client_id"><?php _e('Client ID', 'wpforms-google-drive'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="client_id" name="client_id" value="<?php echo esc_attr($settings['client_id'] ?? ''); ?>" class="regular-text" <?php echo $is_authenticated ? 'readonly' : ''; ?>>
                                <p class="description">
                                    <?php _e('Client ID ottenuto da Google Cloud Console', 'wpforms-google-drive'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="client_secret"><?php _e('Client Secret', 'wpforms-google-drive'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="client_secret" name="client_secret" value="<?php echo esc_attr($settings['client_secret'] ?? ''); ?>" class="regular-text" <?php echo $is_authenticated ? 'readonly' : ''; ?>>
                                <p class="description">
                                    <?php _e('Client Secret ottenuto da Google Cloud Console', 'wpforms-google-drive'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="redirect_uri"><?php _e('Redirect URI', 'wpforms-google-drive'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="redirect_uri" value="<?php echo esc_attr($settings['redirect_uri'] ?? ''); ?>" class="regular-text" readonly>
                                <button type="button" class="button button-secondary" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
                                    <?php _e('Copia', 'wpforms-google-drive'); ?>
                                </button>
                                <p class="description">
                                    <?php _e('Usa questo URL come Redirect URI nella configurazione OAuth di Google Cloud Console', 'wpforms-google-drive'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php if (!$is_authenticated): ?>
                        <p>
                            <input type="submit" name="wpforms_gdrive_save_settings" class="button button-primary" value="<?php _e('Salva Impostazioni', 'wpforms-google-drive'); ?>">
                        </p>

                        <?php if (!empty($settings['client_id']) && !empty($settings['client_secret'])): ?>
                            <p>
                                <a href="<?php echo esc_url($google_drive->get_auth_url() . '&state=' . wp_create_nonce('wpforms_gdrive_oauth')); ?>" class="button button-secondary button-large">
                                    <span class="dashicons dashicons-google" style="margin-top: 3px;"></span>
                                    <?php _e('Connetti a Google Drive', 'wpforms-google-drive'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Impostazioni Generali -->
            <?php if ($is_authenticated): ?>
                <div class="wpforms-gdrive-card">
                    <h2><?php _e('Impostazioni Generali', 'wpforms-google-drive'); ?></h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('wpforms_gdrive_settings'); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enabled"><?php _e('Abilita Integrazione', 'wpforms-google-drive'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked(!empty($settings['enabled'])); ?>>
                                        <?php _e('Carica automaticamente le submission su Google Drive', 'wpforms-google-drive'); ?>
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="root_folder_id"><?php _e('Cartella Radice (opzionale)', 'wpforms-google-drive'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="root_folder_id" name="root_folder_id" value="<?php echo esc_attr($settings['root_folder_id'] ?? ''); ?>" class="regular-text">
                                    <p class="description">
                                        <?php _e('ID della cartella su Google Drive dove verranno salvate le submission. Lascia vuoto per usare la cartella radice.', 'wpforms-google-drive'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p>
                            <input type="submit" name="wpforms_gdrive_save_settings" class="button button-primary" value="<?php _e('Salva Impostazioni', 'wpforms-google-drive'); ?>">
                        </p>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="wpforms-gdrive-sidebar">
            <!-- Guida rapida -->
            <div class="wpforms-gdrive-card">
                <h3><?php _e('Guida Rapida', 'wpforms-google-drive'); ?></h3>

                <ol>
                    <li><?php _e('Crea un progetto su <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Abilita la Google Drive API', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Crea credenziali OAuth 2.0', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Copia il Redirect URI e aggiungilo agli URI autorizzati', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Inserisci Client ID e Client Secret', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Connetti a Google Drive', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Abilita l\'integrazione', 'wpforms-google-drive'); ?></li>
                </ol>
            </div>

            <!-- Informazioni -->
            <div class="wpforms-gdrive-card">
                <h3><?php _e('Informazioni', 'wpforms-google-drive'); ?></h3>

                <p>
                    <strong><?php _e('Versione:', 'wpforms-google-drive'); ?></strong> <?php echo WPFORMS_GDRIVE_VERSION; ?>
                </p>

                <p>
                    <strong><?php _e('Autore:', 'wpforms-google-drive'); ?></strong> Fabio D'Alessandro
                </p>

                <p>
                    <a href="https://github.com/fabiodalez-dev/wpforms-drive" target="_blank" class="button button-secondary">
                        <?php _e('GitHub', 'wpforms-google-drive'); ?>
                    </a>
                </p>
            </div>

            <!-- Come funziona -->
            <div class="wpforms-gdrive-card">
                <h3><?php _e('Come Funziona', 'wpforms-google-drive'); ?></h3>

                <p><?php _e('Quando un utente invia un form WPForms:', 'wpforms-google-drive'); ?></p>

                <ol>
                    <li><?php _e('Viene creata una cartella su Google Drive', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('I file allegati vengono caricati nella cartella', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Un file di testo con i dati del form viene creato', 'wpforms-google-drive'); ?></li>
                    <li><?php _e('Il link alla cartella viene salvato nei metadati', 'wpforms-google-drive'); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

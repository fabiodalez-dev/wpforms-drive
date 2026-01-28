# WPForms Google Drive Integration

Plugin WordPress che integra WPForms con Google Drive per caricare automaticamente file e dati delle submission su Google Drive.

## Caratteristiche

- **Upload automatico** - I file caricati tramite WPForms vengono automaticamente salvati su Google Drive
- **Organizzazione automatica** - Ogni submission viene salvata in una cartella dedicata
- **Dati del form** - Il contenuto del form viene salvato come file di testo nella stessa cartella
- **Autenticazione OAuth2** - Connessione sicura tramite OAuth2 di Google
- **File di grandi dimensioni** - Supporto per upload di file di grandi dimensioni con chunking automatico
- **Interfaccia admin** - Pagina di amministrazione per configurare facilmente la connessione
- **Conforme alle specifiche WPForms** - Sviluppato seguendo la [documentazione ufficiale WPForms](https://wpforms.com/developers/)

## Requisiti

- WordPress 5.8 o superiore
- PHP 7.4 o superiore
- WPForms (gratuito o Pro)
- Account Google con accesso a Google Drive
- Composer (per installare le dipendenze)

## Installazione

1. Clona o scarica il repository nella cartella `wp-content/plugins/`
2. Installa le dipendenze con Composer:
   ```bash
   cd wp-content/plugins/wpforms-drive
   composer install
   ```
3. Attiva il plugin dal pannello WordPress
4. Vai su **WPForms > Google Drive** per configurare la connessione

## Configurazione Google Cloud

1. Vai su [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuovo progetto o selezionane uno esistente
3. Abilita la **Google Drive API**
4. Vai su **Credenziali** e crea credenziali OAuth 2.0
5. Configura gli URL di reindirizzamento autorizzati:
   ```
   https://tuo-sito.com/wp-admin/admin.php?page=wpforms-google-drive
   ```
6. Copia Client ID e Client Secret nelle impostazioni del plugin

## Configurazione Plugin

1. Vai su **WPForms > Google Drive**
2. Inserisci il **Client ID** e il **Client Secret**
3. Clicca su **Connetti a Google Drive**
4. Autorizza l'applicazione
5. (Opzionale) Seleziona una cartella radice su Google Drive
6. Abilita l'integrazione

## Come Funziona

Quando un utente invia un form WPForms:

1. Il plugin crea una cartella su Google Drive con il nome: `Form_{nome_form}_{data}_{id_submission}`
2. Carica tutti i file allegati nella cartella
3. Crea un file `form-data.txt` con tutti i dati del form
4. (Opzionale) Organizza le cartelle in base alla data o altri criteri

## Struttura Cartelle

```
Google Drive
└── WPForms Submissions (cartella radice)
    ├── Form_Contatti_2026-01-28_123/
    │   ├── documento.pdf
    │   ├── foto.jpg
    │   └── form-data.txt
    └── Form_Iscrizioni_2026-01-28_124/
        ├── cv.pdf
        └── form-data.txt
```

## Sviluppo

### Struttura del Plugin

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

### Integrazione Tecnica con WPForms

Il plugin si integra con WPForms utilizzando gli hook e le API ufficiali:

#### Hook Utilizzati

- **`wpforms_process_complete`** - Hook principale che si attiva dopo una submission riuscita
  - Riceve: `$fields`, `$entry`, `$form_data`, `$entry_id`
  - Documentazione: [wpforms_process_complete](https://wpforms.com/developers/wpforms_process_complete/)

#### Gestione File Upload

Secondo la [documentazione WPForms](https://wpforms.com/docs/a-complete-guide-to-the-file-upload-field/):
- I file sono in `$fields[field_id]['value']` come stringa URL
- File multipli sono separati da newline (`\n`)
- Il plugin converte automaticamente URL in percorsi filesystem

#### Salvataggio Metadati

Il plugin salva i metadati delle submission in due modi:
1. Tabella `wp_wpforms_entry_meta` (metodo principale)
2. Opzioni WordPress (fallback)

Metadati salvati:
- `google_drive_folder_url` - URL della cartella Drive
- `google_drive_uploaded_at` - Timestamp upload
- `google_drive_files_count` - Numero file caricati
- `google_drive_files` - Array con dettagli file

#### Riferimenti

- [WPForms Developer Documentation](https://wpforms.com/developers/)
- [Custom Integrations Guide](https://www.billerickson.net/contact-form-integration/)
- [File Upload Field Documentation](https://wpforms.com/docs/a-complete-guide-to-the-file-upload-field/)

## Sicurezza

- Le credenziali OAuth sono salvate in modo sicuro nel database WordPress
- I token di accesso vengono aggiornati automaticamente
- Nessuna credenziale viene esposta nel codice frontend

## Supporto

Per problemi o richieste di funzionalità, apri una issue su GitHub.

## Licenza

GPL v3 or later - [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)

## Crediti

Sviluppato da Fabio D'Alessandro

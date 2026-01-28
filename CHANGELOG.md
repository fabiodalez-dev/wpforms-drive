# Changelog

Tutte le modifiche significative al progetto saranno documentate in questo file.

## [1.0.1] - 2026-01-28

### Correzioni Basate su Documentazione Ufficiale WPForms

Dopo aver verificato la [documentazione ufficiale di WPForms](https://wpforms.com/developers/), sono state apportate le seguenti correzioni:

#### Gestione File Upload Migliorata

**Problema**: La gestione iniziale dei file upload era troppo generica e non seguiva esattamente il formato utilizzato da WPForms.

**Soluzione**: Implementata gestione corretta secondo la documentazione:
- I file sono sempre in `$fields[field_id]['value']` come stringa
- File multipli sono separati da newline (`\n`)
- Aggiunta conversione URL → percorso filesystem con fallback multipli
- Migliorata gestione errori con logging dettagliato

**File modificato**: `classes/class-wpforms-handler.php`
- Funzione `get_field_files()` completamente riscritta
- Aggiunta funzione `url_to_path()` per conversione URL

**Riferimenti**:
- [wpforms_process_complete Hook](https://wpforms.com/developers/wpforms_process_complete/)
- [File Upload Field Guide](https://wpforms.com/docs/a-complete-guide-to-the-file-upload-field/)

#### Gestione Metadati Entry Migliorata

**Problema**: La gestione dei metadati non usava prepared statements e non aveva fallback robusti.

**Soluzione**:
- Uso di `$wpdb->prepare()` per sicurezza SQL injection
- Doppio salvataggio: tabella `wpforms_entry_meta` + opzioni WordPress
- Recupero metadati con fallback automatico
- Uso di `wp_json_encode()` invece di `json_encode()`
- Migliorata gestione errori con logging

**File modificato**: `classes/class-wpforms-handler.php`
- Funzione `save_drive_url()` migliorata con prepared statements
- Funzione `get_entry_drive_url()` con doppio fallback

#### Formattazione Dati Form

**Miglioramento**: Gestione speciale per campi file upload nel file di testo generato.

**Soluzione**:
- Rilevamento automatico campi file upload
- Formattazione URL multipli su righe separate
- Miglior leggibilità del file `form-data.txt`

**File modificato**: `classes/class-wpforms-handler.php`
- Funzione `get_field_value()` aggiornata

### Sicurezza

- Tutti i query SQL ora usano prepared statements
- Validazione esistenza file prima dell'upload
- Logging esteso per debugging senza esporre dati sensibili

### Riferimenti Documentazione

- [WPForms Developer Documentation](https://wpforms.com/developers/)
- [Custom Integrations Guide by Bill Erickson](https://www.billerickson.net/contact-form-integration/)
- [WPForms Database Structure](https://wpforms.com/docs/where-does-wpforms-data-go/)

---

## [1.0.0] - 2026-01-28

### Rilascio Iniziale

Prima versione del plugin con le seguenti funzionalità:

#### Funzionalità

- Autenticazione OAuth2 con Google Drive
- Upload automatico file da WPForms a Google Drive
- Creazione automatica cartelle per submission
- Salvataggio dati form come file di testo
- Supporto file grandi con chunking (1MB chunks)
- Interfaccia amministrazione WordPress
- Colonna Google Drive in lista entries WPForms

#### Componenti

- `wpforms-google-drive.php`: File principale plugin
- `classes/class-google-drive-manager.php`: Gestione Google Drive API
- `classes/class-wpforms-handler.php`: Integrazione WPForms
- `classes/class-admin.php`: Interfaccia amministrazione
- `views/admin/settings.php`: Template impostazioni
- `assets/`: CSS e JavaScript
- `composer.json`: Dipendenze Google API Client

#### Documentazione

- README.md: Documentazione generale
- INSTALL.md: Guida installazione completa
- readme.txt: Formato WordPress standard

---

## Formato

Questo changelog segue il formato [Keep a Changelog](https://keepachangelog.com/it/1.0.0/).

### Tipi di Modifiche

- **Aggiunte** per nuove funzionalità
- **Modifiche** per modifiche a funzionalità esistenti
- **Deprecate** per funzionalità che saranno rimosse
- **Rimosse** per funzionalità rimosse
- **Correzioni** per bug fix
- **Sicurezza** per vulnerabilità corrette

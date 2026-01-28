# Guida all'Installazione

Questa guida ti aiuterà a installare e configurare il plugin WPForms Google Drive Integration.

## Prerequisiti

Prima di iniziare, assicurati di avere:

- WordPress 5.8 o superiore
- PHP 7.4 o superiore
- WPForms installato e attivo (Lite o Pro)
- Account Google
- Composer installato (per installare le dipendenze)
- Accesso SSH al server (raccomandato) o accesso FTP

## Passo 1: Installazione del Plugin

### Opzione A: Installazione via Git (Raccomandato)

```bash
# Naviga nella cartella dei plugin
cd wp-content/plugins/

# Clona il repository
git clone https://github.com/fabiodalez-dev/wpforms-drive.git

# Entra nella cartella del plugin
cd wpforms-drive

# Installa le dipendenze con Composer
composer install
```

### Opzione B: Installazione Manuale

1. Scarica il codice sorgente da GitHub
2. Estrai l'archivio nella cartella `wp-content/plugins/`
3. Rinomina la cartella in `wpforms-drive`
4. Apri il terminale nella cartella del plugin
5. Esegui `composer install`

### Opzione C: Installazione via FTP

1. Scarica il codice sorgente
2. Sul tuo computer locale, naviga nella cartella ed esegui `composer install`
3. Carica l'intera cartella (inclusa la cartella `vendor/`) su `wp-content/plugins/` via FTP

## Passo 2: Attivazione del Plugin

1. Accedi al pannello di amministrazione di WordPress
2. Vai su **Plugin** > **Plugin installati**
3. Trova **WPForms Google Drive Integration**
4. Clicca su **Attiva**

## Passo 3: Configurazione Google Cloud

### 3.1 Crea un Progetto Google Cloud

1. Vai su [Google Cloud Console](https://console.cloud.google.com/)
2. Clicca su **Seleziona un progetto** in alto
3. Clicca su **Nuovo progetto**
4. Inserisci un nome (es. "WPForms Google Drive")
5. Clicca su **Crea**

### 3.2 Abilita Google Drive API

1. Nel progetto appena creato, vai su **API e servizi** > **Libreria**
2. Cerca "Google Drive API"
3. Clicca sulla Google Drive API
4. Clicca su **Abilita**

### 3.3 Configura la Schermata di Consenso OAuth

1. Vai su **API e servizi** > **Schermata consenso OAuth**
2. Seleziona **Esterno** (o **Interno** se hai un account Google Workspace)
3. Clicca su **Crea**
4. Compila i campi obbligatori:
   - **Nome applicazione**: WPForms Google Drive
   - **Email di supporto utente**: la tua email
   - **Dominio autorizzato**: il tuo dominio (es. `tuosito.com`)
   - **Email sviluppatore**: la tua email
5. Clicca su **Salva e continua**
6. In **Ambiti**, clicca su **Aggiungi o rimuovi ambiti**
7. Cerca e seleziona:
   - `https://www.googleapis.com/auth/drive.file`
   - `https://www.googleapis.com/auth/drive`
8. Clicca su **Aggiorna** e poi su **Salva e continua**
9. Completa il resto della configurazione

### 3.4 Crea Credenziali OAuth 2.0

1. Vai su **API e servizi** > **Credenziali**
2. Clicca su **Crea credenziali** > **ID client OAuth**
3. Seleziona **Applicazione web**
4. Inserisci un nome (es. "WPForms Integration")
5. In **URI di reindirizzamento autorizzati**, clicca su **Aggiungi URI**
6. **IMPORTANTE**: Vai sul tuo sito WordPress > **WPForms** > **Google Drive**
7. Copia il **Redirect URI** mostrato nella pagina (sarà tipo: `https://tuosito.com/wp-admin/admin.php?page=wpforms-google-drive`)
8. Incolla questo URI in Google Cloud Console
9. Clicca su **Crea**
10. **IMPORTANTE**: Copia il **Client ID** e il **Client Secret** (li userai nel prossimo passo)

## Passo 4: Configurazione del Plugin

1. Vai su **WPForms** > **Google Drive** nel pannello WordPress
2. Incolla il **Client ID** nel campo apposito
3. Incolla il **Client Secret** nel campo apposito
4. Clicca su **Salva Impostazioni**
5. Clicca sul pulsante **Connetti a Google Drive**
6. Autorizza l'applicazione tramite Google (potrebbe richiedere di accedere con il tuo account Google)
7. Verrai reindirizzato al pannello WordPress con un messaggio di successo

## Passo 5: Configurazione Finale

1. Nella sezione **Impostazioni Generali**:
   - Abilita la casella **Abilita Integrazione**
   - (Opzionale) Inserisci l'**ID della cartella radice** su Google Drive dove vuoi salvare le submission
2. Clicca su **Salva Impostazioni**

## Passo 6: Test

1. Vai su uno dei tuoi form WPForms
2. Compila e invia il form (assicurati che contenga almeno un campo file)
3. Vai su **WPForms** > **Entries**
4. Controlla che nella colonna "Google Drive" ci sia un pulsante "Apri su Drive"
5. Clicca sul pulsante per verificare che la cartella sia stata creata correttamente

## Risoluzione Problemi

### Errore: "Impossibile caricare le dipendenze"

**Soluzione**: Assicurati di aver eseguito `composer install` nella cartella del plugin.

### Errore OAuth: "Redirect URI mismatch"

**Soluzione**: Verifica che il Redirect URI in Google Cloud Console corrisponda esattamente a quello mostrato nelle impostazioni del plugin (incluso http/https).

### I file non vengono caricati su Google Drive

**Verifica**:
1. Che l'integrazione sia abilitata nelle impostazioni
2. Che la connessione a Google Drive sia attiva (stato "Connesso")
3. I log di WordPress per eventuali errori (attiva WP_DEBUG se necessario)

### Errore: "Access token expired"

**Soluzione**: Il plugin dovrebbe aggiornare automaticamente il token. Se l'errore persiste, disconnetti e riconnetti l'account Google Drive.

### Come trovare l'ID di una cartella su Google Drive

1. Apri Google Drive
2. Naviga nella cartella che vuoi usare
3. Guarda l'URL nella barra degli indirizzi
4. L'ID è la stringa dopo `/folders/`

   Esempio: `https://drive.google.com/drive/folders/1A2B3C4D5E6F7G8H9I0J`

   L'ID è: `1A2B3C4D5E6F7G8H9I0J`

## Supporto

Per ulteriore supporto o per segnalare bug:

- Apri una issue su GitHub: https://github.com/fabiodalez-dev/wpforms-drive/issues
- Consulta la documentazione: https://github.com/fabiodalez-dev/wpforms-drive

## Sicurezza

- Le credenziali OAuth sono memorizzate in modo sicuro nel database WordPress
- I token di accesso vengono aggiornati automaticamente quando scadono
- Nessuna credenziale viene esposta nel codice frontend

## Note Importanti

- Assicurati che il tuo sito WordPress sia accessibile via HTTPS (richiesto da Google OAuth)
- Durante lo sviluppo, puoi usare localhost, ma dovrai configurare un nuovo progetto OAuth per la produzione
- Google limita il numero di richieste API. Il plugin gestisce automaticamente il rate limiting, ma con volumi molto alti potrebbe essere necessario richiedere quote superiori a Google

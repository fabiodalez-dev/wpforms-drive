=== WPForms Google Drive Integration ===
Contributors: fabiodalez
Tags: wpforms, google drive, forms, upload, integration
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integra WPForms con Google Drive per caricare automaticamente file e submission su Google Drive.

== Description ==

WPForms Google Drive Integration è un plugin che permette di integrare facilmente WPForms con Google Drive, caricando automaticamente tutti i file e i dati delle submission su Google Drive in cartelle organizzate.

= Caratteristiche Principali =

* **Upload Automatico** - I file caricati tramite WPForms vengono automaticamente salvati su Google Drive
* **Organizzazione Automatica** - Ogni submission viene salvata in una cartella dedicata con nome descrittivo
* **Dati del Form** - Il contenuto completo del form viene salvato come file di testo nella cartella
* **Autenticazione OAuth2** - Connessione sicura tramite OAuth2 di Google
* **File di Grandi Dimensioni** - Supporto per upload di file di grandi dimensioni con chunking automatico
* **Interfaccia Admin Intuitiva** - Pagina di amministrazione semplice per configurare la connessione
* **Link Diretti** - Accesso rapido alle cartelle Google Drive dalla lista entries di WPForms

= Come Funziona =

Quando un utente invia un form WPForms:

1. Il plugin crea automaticamente una cartella su Google Drive
2. Carica tutti i file allegati nella cartella
3. Crea un file di testo con tutti i dati del form
4. Salva il link alla cartella nei metadati della submission

= Struttura Cartelle =

```
Google Drive
└── WPForms Submissions
    ├── Form_Contatti_2026-01-28_123/
    │   ├── documento.pdf
    │   ├── foto.jpg
    │   └── form-data.txt
    └── Form_Iscrizioni_2026-01-28_124/
        ├── cv.pdf
        └── form-data.txt
```

= Requisiti =

* WordPress 5.8 o superiore
* PHP 7.4 o superiore
* WPForms (gratuito o Pro)
* Account Google con accesso a Google Drive
* Composer (per installare le dipendenze)

== Installation ==

= Installazione Automatica =

1. Scarica il plugin dalla repository o da GitHub
2. Carica il file zip tramite il pannello WordPress > Plugin > Aggiungi nuovo
3. Attiva il plugin

= Installazione Manuale =

1. Scarica o clona il repository nella cartella `wp-content/plugins/`
2. Naviga nella cartella del plugin: `cd wp-content/plugins/wpforms-drive`
3. Installa le dipendenze con Composer: `composer install`
4. Attiva il plugin dal pannello WordPress

= Configurazione Google Cloud =

1. Vai su [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuovo progetto o selezionane uno esistente
3. Abilita la **Google Drive API**
4. Vai su **Credenziali** e crea credenziali OAuth 2.0
5. Configura gli URL di reindirizzamento autorizzati (disponibile nelle impostazioni del plugin)
6. Copia Client ID e Client Secret

= Configurazione Plugin =

1. Vai su **WPForms > Google Drive**
2. Inserisci il **Client ID** e il **Client Secret**
3. Clicca su **Salva Impostazioni**
4. Clicca su **Connetti a Google Drive**
5. Autorizza l'applicazione tramite Google
6. Abilita l'integrazione

== Frequently Asked Questions ==

= Il plugin funziona con WPForms Lite? =

Sì, il plugin funziona sia con WPForms Lite che con WPForms Pro.

= I file vengono eliminati da WordPress? =

No, i file rimangono sul server WordPress. Il plugin crea solo una copia su Google Drive.

= Posso scegliere in quale cartella salvare i file? =

Sì, puoi specificare una cartella radice su Google Drive nelle impostazioni del plugin.

= Cosa succede se la connessione a Google Drive fallisce? =

La submission viene comunque salvata su WordPress normalmente. L'upload su Google Drive viene gestito in background e non blocca il processo del form.

= Il plugin supporta file di grandi dimensioni? =

Sì, il plugin utilizza il chunking automatico per caricare file di grandi dimensioni su Google Drive.

= Come posso vedere le cartelle su Google Drive? =

Nella lista entries di WPForms troverai una colonna "Google Drive" con un pulsante "Apri su Drive" per ogni submission caricata.

== Screenshots ==

1. Pagina di impostazioni del plugin
2. Stato della connessione a Google Drive
3. Colonna Google Drive nella lista entries di WPForms
4. Esempio di cartella su Google Drive
5. Contenuto del file form-data.txt

== Changelog ==

= 1.0.0 =
* Prima release
* Integrazione completa con WPForms
* Autenticazione OAuth2
* Upload automatico file
* Creazione automatica cartelle
* Salvataggio dati form

== Upgrade Notice ==

= 1.0.0 =
Prima release del plugin.

== Privacy Policy ==

Questo plugin:
* Non raccoglie dati degli utenti
* Non invia dati a servizi esterni eccetto Google Drive (previo consenso)
* Memorizza i token OAuth in modo sicuro nel database WordPress
* Non utilizza cookie o tracking

== Credits ==

Sviluppato da Fabio D'Alessandro
Repository GitHub: https://github.com/fabiodalez-dev/wpforms-drive

== Support ==

Per supporto o segnalazione bug, apri una issue su GitHub:
https://github.com/fabiodalez-dev/wpforms-drive/issues

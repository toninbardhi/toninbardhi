# DirCMS — Directory web in stile DMOZ

CMS per creare una **directory di siti organizzata in categorie gerarchiche**,
ispirato all'Open Directory Project (DMOZ). Scritto in **PHP puro + MySQL**,
senza dipendenze esterne né passaggi di build: si carica via FTP e funziona
su qualsiasi hosting condiviso (testato pensando a **Netsons**).

## Funzionalità

- 🌳 **Categorie ad albero** (categorie e sottocategorie illimitate)
- 🔗 **Link/siti** dentro le categorie (titolo, URL, descrizione)
- 🔎 **Ricerca** full-text su titoli, descrizioni e categorie
- 📥 **Suggerisci un sito**: form pubblico (fino a **3 categorie** per proposta) che crea proposte da approvare
- 📦 **Importazione RDF** in stile DMOZ/Curlie: script CLI per popolare categorie (ed eventualmente siti) dai dump dell'Open Directory
- ✅ **Coda di moderazione** dei suggerimenti (approva / rifiuta)
- ⭐ **Posizionamento a pagamento**: metti un link "in evidenza" (badge
  *Sponsorizzato*) tra i primi 5 della categoria, con posizione e scadenza
  (precompilata a 1 anno); alla scadenza torna normale automaticamente
- ✨ **Descrizione automatica dall'URL**: un clic legge titolo e descrizione dal sito (gratis); con una chiave Anthropic opzionale, Claude genera la descrizione quando manca (~0,002 € a sito)
- 🛡️ **Antispam** sul form pubblico: honeypot + domanda matematica + controllo tempo (nessun servizio esterno, niente reCAPTCHA)
- 💰 **Banner Google AdSense** opzionale in fondo alle pagine pubbliche
- 👥 **Utenti con ruoli**: `admin` (gestisce anche gli utenti) ed `editor`
- 👤 **Profilo personale**: ogni utente cambia nome/email/password (con verifica della password attuale)
- 🔐 Login sicuro, password con hash bcrypt, protezione CSRF, escaping output
- 🔍 **SEO**: sitemap XML (`/sitemap.xml`), `robots.txt`, meta description, canonical, Open Graph
- 🎨 Favicon e logo SVG inclusi

## Requisiti

- PHP 8.0 o superiore
- MySQL / MariaDB
- Apache con `mod_rewrite` (attivo di default su Netsons)

## Installazione su Netsons (hosting condiviso)

1. **Crea il database**
   - Dal pannello Netsons → *Database MySQL* → crea un nuovo database e un
     utente, e annota **nome db, utente, password, host** (di solito `localhost`).

2. **Importa le tabelle**
   - Apri *phpMyAdmin* dal pannello, seleziona il tuo database e importa il
     file [`database/schema.sql`](database/schema.sql).
   - Questo crea le tabelle e alcuni dati di esempio, incluso l'utente admin.

3. **Configura l'applicazione**
   - Copia `app/config.example.php` in `app/config.php`.
   - Inserisci i dati del database del punto 1.
   - Se il sito è nella radice del dominio lascia `base_url` a `''`.
   - Imposta `url` sul dominio reale (es. `https://webdirectory.link`):
     è usato per sitemap, canonical e Open Graph.

4. **Carica i file**
   - Via FTP (o File Manager) copia **tutto il contenuto** del progetto dentro
     `public_html/` (la cartella pubblica del tuo dominio).
   - Il file `.htaccess` nella radice si occupa del routing e protegge le
     cartelle `app/` e `database/`.

5. **Primo accesso**
   - Vai su `https://iltuodominio.it/login`
   - Utente: **admin@example.com** — Password: **admin123**
   - ⚠️ **Cambia subito la password**: Utenti → modifica il tuo account.

## Posizionamento a pagamento (link in evidenza)

Chi vuole comparire in cima alla propria categoria ti paga (bonifico, PayPal,
come preferisci) e tu lo metti in evidenza dal pannello:

1. **Link** → apri il link (o creane uno) → sezione **★ In evidenza**
2. Attiva la spunta, scegli la **posizione** (1–5) e la **scadenza**
   (precompilata a 1 anno)
3. Il link appare per primo nella categoria e nella ricerca, con il badge
   **Sponsorizzato**
4. Alla scadenza torna automaticamente un link normale

La pagina **★ In evidenza** dell'admin elenca tutti gli sponsor attivi con i
giorni mancanti alla scadenza e permette di rimuovere l'evidenza con un clic.

> Il pagamento è gestito manualmente (nessuna commissione). In futuro si può
> aggiungere PayPal o Stripe per l'acquisto self-service.

**Aggiorni da una versione precedente?** Esegui la migrazione
[`database/migrations/2026_07_add_featured.sql`](database/migrations/2026_07_add_featured.sql)
da phpMyAdmin per aggiungere le colonne necessarie.

## Pubblicità (Google AdSense)

Per mostrare un banner in fondo alle pagine pubbliche, in `app/config.php`
imposta i dati del tuo account AdSense:

```php
'adsense' => [
    'client' => 'ca-pub-1234567890123456', // il tuo ID publisher
    'slot'   => '1234567890',              // ID dell'unità annuncio (facoltativo)
],
```

Lasciando `client` vuoto non viene caricato nulla (nessuno script esterno).
L'area riservata non mostra mai pubblicità.

## Descrizione automatica dall'URL

Nei form "Nuovo link" (admin) e "Suggerisci un sito" c'è il pulsante
**↓ Recupera dal sito**: inserisci l'URL, premilo e i campi titolo/descrizione
si compilano da soli.

- **Gratis**: legge il `<title>` e la meta description (o Open Graph) del sito.
  Nessun costo, nessuna chiave. Attivo di default.
- **AI (opzionale)**: se metti una chiave API Anthropic in
  `app/config.php → describe`, quando il sito non fornisce una descrizione
  Claude ne genera una in italiano. Costo con **Claude Haiku**: circa
  **0,002 € a sito** (~2,5 € ogni 1000). L'AI si attiva solo per gli utenti
  autenticati, così il form pubblico non genera costi.

```php
'describe' => [
    'meta_enabled' => true,                 // lettura meta tag (gratis)
    'ai_enabled'   => true,                 // abilita l'AI
    'ai_api_key'   => 'sk-ant-...',         // la tua chiave Anthropic
    'ai_model'     => 'claude-haiku-4-5',
],
```

> 🔒 Il recupero è protetto contro gli abusi (SSRF): rifiuta indirizzi locali
> o privati e scarica al massimo ~400 KB per pagina.

## Antispam

Il form "Suggerisci un sito" è protetto **senza servizi esterni** (quindi
niente reCAPTCHA e nessun problema di privacy/GDPR):

- **honeypot**: un campo invisibile che i bot compilano → l'invio viene
  scartato in silenzio;
- **domanda matematica**: es. "quanto fa 3 + 5?";
- **controllo tempo**: invii troppo rapidi vengono bloccati.

Si configura in `app/config.php` → `antispam` (`enabled`, `min_secs`).

## Importare le categorie da DMOZ / Curlie (RDF)

Il CMS include uno script CLI (`bin/import_rdf.php`) che legge i dump RDF
dell'Open Directory e popola le categorie (ed eventualmente i siti).

### 1. Procurarsi il dump

DMOZ ha chiuso nel 2017 e **Curlie non pubblica più un dump ufficiale**
scaricabile. Si usano quindi le **copie archiviate** del dump DMOZ 2017:

- `structure.rdf.u8` → l'albero delle categorie
- `content.rdf.u8` → i siti (titolo, URL, descrizione, categoria)

Si trovano su archivi come *archive.org* cercando "dmoz rdf dump" o sui vari
mirror `dmoztools`. Sono file grandi (centinaia di MB compressi), da
scompattare prima dell'uso.

> ⚠️ **Attenzione ai limiti dell'hosting.** Il dump completo contiene ~1M di
> categorie e ~3,8M di siti: su un hosting condiviso (Netsons) rischi di
> superare la quota del database. Conviene importare **solo la struttura** o
> **un ramo** (es. il ramo italiano). I link del 2017 sono spesso obsoleti.

### 2. Eseguire l'import (via SSH / riga di comando)

```bash
# Tutte le categorie (solo struttura, nessun sito)
php bin/import_rdf.php --structure=dump/structure.rdf.u8

# Solo il ramo italiano, che diventa la radice dell'albero
php bin/import_rdf.php --structure=dump/structure.rdf.u8 \
     --branch=Top/World/Italiano --strip=Top/World/Italiano

# Struttura + siti
php bin/import_rdf.php --structure=dump/structure.rdf.u8 \
     --content=dump/content.rdf.u8 --with-sites

# Prova rapida (solo 100 elementi) o azzeramento preventivo
php bin/import_rdf.php --structure=dump/structure.rdf.u8 --limit=100
php bin/import_rdf.php --structure=dump/structure.rdf.u8 --fresh
```

Lo script usa `XMLReader` in streaming, quindi gestisce file di grandi
dimensioni senza esaurire la memoria, ed è idempotente (le categorie già
presenti non vengono duplicate). Se il tuo piano Netsons non ha accesso SSH,
esegui l'import **in locale** e poi carica il database.

## Struttura del progetto

```
.
├── .htaccess              # routing + protezione cartelle (radice)
├── public/
│   ├── index.php          # front controller (unico ingresso)
│   └── .htaccess
├── app/
│   ├── config.php         # credenziali (da creare)
│   ├── config.example.php
│   ├── Database.php       # connessione PDO
│   ├── helpers.php        # utility (escaping, CSRF, auth, view…)
│   ├── controllers/       # PublicController, AuthController, AdminController
│   ├── models/            # Category, Link, User
│   └── views/             # template PHP (pubblici + admin)
├── assets/css/style.css   # stile
└── database/schema.sql    # schema + dati di esempio
```

## Sviluppo in locale

Con PHP installato e un MySQL locale:

```bash
# 1. crea il database e importa lo schema
mysql -u root -e "CREATE DATABASE dircms CHARACTER SET utf8mb4"
mysql -u root dircms < database/schema.sql

# 2. imposta le credenziali via variabili d'ambiente o in app/config.php
export DB_NAME=dircms DB_USER=root DB_PASS=

# 3. avvia il server integrato puntando alla cartella public/
php -S localhost:8000 -t public
```

Apri http://localhost:8000

## Note di sicurezza

- Le password sono salvate con `password_hash()` (bcrypt).
- Tutti i form dell'area riservata usano un token **CSRF**.
- L'output è sempre passato per `htmlspecialchars()` per evitare XSS.
- Le query usano **prepared statement** (PDO) contro le SQL injection.
- Ricordati di cambiare la password admin di default al primo accesso.

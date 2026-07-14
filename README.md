# DirCMS — Directory web in stile DMOZ

CMS per creare una **directory di siti organizzata in categorie gerarchiche**,
ispirato all'Open Directory Project (DMOZ). Scritto in **PHP puro + MySQL**,
senza dipendenze esterne né passaggi di build: si carica via FTP e funziona
su qualsiasi hosting condiviso (testato pensando a **Netsons**).

## Funzionalità

- 🌳 **Categorie ad albero** (categorie e sottocategorie illimitate)
- 🔗 **Link/siti** dentro le categorie (titolo, URL, descrizione)
- 🔎 **Ricerca** full-text su titoli, descrizioni e categorie
- 📥 **Suggerisci un sito**: form pubblico che crea proposte da approvare
- ✅ **Coda di moderazione** dei suggerimenti (approva / rifiuta)
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

# Οδηγός Deploy — Client Manager

Deployment σε server με Virtualmin/Webmin μέσω GitHub.

---

## Απαιτήσεις Server

| Απαίτηση | Ελάχιστο |
|---|---|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Composer | 2.x |
| Git | οποιαδήποτε έκδοση |
| PHP Extensions | BCMath, Ctype, cURL, DOM, Fileinfo, GD, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, ZIP |

Έλεγχος extensions: `php -m`

---

## Φάση 1 — Προετοιμασία Local (πριν ανεβάσεις)

```bash
# 1. Build των compiled assets (JS + CSS)
npm run build

# 2. Βεβαιώσου ότι τα αρχεία public/build/ είναι committed στο git
git add public/build/
git commit -m "build assets for production"
git push
```

> **Σημείωση:** Το `node_modules/` δεν ανεβαίνει στον server.
> Το `public/build/` ΠΡΕΠΕΙνα είναι committed γιατί δεν τρέχουμε npm στον server.

---

## Φάση 2 — Ρύθμιση Virtualmin

### 2.1 Δημιουργία Virtual Server

1. Virtualmin → **Create Virtual Server**
2. Domain: π.χ. `clients.example.com`
3. Ενεργοποίησε: Apache/Nginx website, MySQL database
4. Αποθήκευσε credentials MySQL (database name, user, password)

### 2.2 Document Root — κρίσιμο

Το Laravel σερβίρεται από τον `public/` υποφάκελο, ΟΧΙ από το root.

**Virtualmin → Server Configuration → Website Options**
- Document Root: `/home/yourdomain/public_html/public`

Ή εναλλακτικά, βάλε τα αρχεία στο `/home/yourdomain/client-manager/` και δείξε το document root στο `client-manager/public/`.

### 2.3 PHP Version

**Virtualmin → Server Configuration → PHP Options**
- Επέλεξε PHP 8.2 ή 8.3
- Mode: **PHP-FPM** (προτιμητέο για performance)

---

## Φάση 3 — Πρώτο Deploy (clone)

Σύνδεση μέσω SSH (Webmin → System → Shell Command ή terminal client).

```bash
# Πήγαινε στο φάκελο όπου θέλεις το project
cd /home/yourdomain/

# Clone από GitHub
git clone https://github.com/YOUR_USERNAME/client-manager.git public_html
# Αν το document root είναι /public_html/public, το παραπάνω είναι σωστό
# Αν χρησιμοποιείς υποφάκελο:
# git clone https://github.com/YOUR_USERNAME/client-manager.git client-manager

cd public_html
# (ή cd client-manager)

# Εγκατάσταση PHP dependencies (χωρίς dev packages)
composer install --no-dev --optimize-autoloader
```

---

## Φάση 4 — Αρχείο .env

Το `.env` δεν βγαίνει στο git. Το φτιάχνεις χειροκίνητα στον server:

```bash
cp .env.example .env
nano .env
```

Συμπλήρωσε τα παρακάτω:

```env
APP_NAME="Client Manager"
APP_ENV=production
APP_KEY=                              # θα γεμίσει παρακάτω
APP_DEBUG=false
APP_URL=https://clients.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yourdomain_clientmanager  # από το Virtualmin
DB_USERNAME=yourdomain_dbuser
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=mail.example.com            # ο SMTP server σου
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=you@example.com           # για τον πρώτο admin user
ADMIN_PASSWORD=strong_password_here   # αλλαξε αυτό!

QUEUE_CONNECTION=sync                 # sync = χωρίς queue worker
SESSION_DRIVER=file
CACHE_STORE=file
```

---

## Φάση 5 — Αρχικοποίηση Εφαρμογής

```bash
# 1. Δημιουργία APP_KEY
php artisan key:generate

# 2. Εκτέλεση migrations (δημιουργία tables)
php artisan migrate --force

# 3. Δημιουργία admin user
php artisan db:seed --class=AdminUserSeeder

# 4. Production optimizations (cache config/routes/views)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Permissions — το Laravel πρέπει να γράφει σε αυτούς τους φακέλους
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Αν ο Apache τρέχει ως "apache" αντί "www-data":
# chown -R apache:apache storage bootstrap/cache
```

> Μπορείς να βρεις τον σωστό user με: `ps aux | grep apache` ή `ps aux | grep php-fpm`

---

## Φάση 6 — Cron Job (Laravel Scheduler)

Ένα μόνο cron entry καλύπτει **όλες** τις αυτόματες εργασίες.

**Webmin → System → Scheduled Cron Jobs → Add a cron job**

| Πεδίο | Τιμή |
|---|---|
| Execute cron job as | `www-data` (ή `apache`) |
| Command | `/usr/bin/php /home/yourdomain/public_html/artisan schedule:run >> /dev/null 2>&1` |
| Minutes | `*` |
| Hours | `*` |
| Days | `*` |
| Months | `*` |
| Weekdays | `*` |

Αυτό τρέχει κάθε λεπτό και ο Laravel Scheduler αποφασίζει εσωτερικά τι χρειάζεται:

| Εντολή | Χρονοδιάγραμμα | Λειτουργία |
|---|---|---|
| `charges:generate` | Καθημερινά 07:00 | Δημιουργεί charges 60 μέρες πριν |
| `reminders:overdue` | Καθημερινά 08:00 | Emails για ληξιπρόθεσμα |
| `charges:remind-clients` | Καθημερινά 09:00 | Emails πελατών για επερχόμενες/ληξιπρόθεσμες |
| `reminders:renewals` | Κάθε Δευτέρα 08:00 | Εβδομαδιαία υπενθύμιση ανανεώσεων |

Έλεγχος scheduler: `php artisan schedule:list`

---

## Φάση 7 — Τελικός Έλεγχος

```bash
# Έλεγχος ότι η εφαρμογή απαντά
curl -I https://clients.example.com

# Τεστ αποστολής email
php artisan tinker
>>> Mail::raw('Test email', fn($m) => $m->to('you@example.com')->subject('Test'));

# Παρακολούθηση logs για errors
tail -f storage/logs/laravel.log
```

---

## Updates — Πώς ανεβάζεις αλλαγές

Κάθε φορά που κάνεις αλλαγές, από local:

```bash
# Local: build assets αν άλλαξες JS/CSS
npm run build
git add .
git commit -m "description of changes"
git push
```

Στον server (SSH):

```bash
cd /home/yourdomain/public_html

# 1. Pull αλλαγές από GitHub
git pull

# 2. Update dependencies (αν άλλαξε το composer.json)
composer install --no-dev --optimize-autoloader

# 3. Νέα migrations (αν υπάρχουν)
php artisan migrate --force

# 4. Ανανέωση cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **Αν έχεις αλλάξει μόνο Blade templates:** αρκεί `php artisan view:cache`
> **Αν έχεις αλλάξει .env:** `php artisan config:cache`

---

## Αντιμετώπιση Προβλημάτων

### 500 Error στη σελίδα
```bash
tail -f storage/logs/laravel.log
# Και βεβαιώσου ότι APP_DEBUG=false στο .env (για production)
# Αν θες να δεις το error temporarily: APP_DEBUG=true, μετά ξανά false
```

### Permission denied στο storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### CSS/JS δεν φορτώνουν (404)
- Βεβαιώσου ότι το `public/build/` είναι committed στο git
- Έλεγχος: `ls public/build/` να περιέχει `manifest.json`

### Migrations δεν τρέχουν
```bash
php artisan migrate:status   # δες ποιες έχουν τρέξει
php artisan migrate --force  # το --force χρειάζεται για production
```

### Cron δεν λειτουργεί
```bash
# Τεστ χειροκίνητα
php artisan schedule:run

# Έλεγχος cron log
grep CRON /var/log/syslog | tail -20
```

---

## Private GitHub Repo — Authentication

Αν το repo είναι private, ο server χρειάζεται πρόσβαση:

**Επιλογή Α — Deploy Key (συνιστάται):**
```bash
# Στον server, δημιούργησε SSH key
ssh-keygen -t ed25519 -C "server-deploy" -f ~/.ssh/deploy_key

# Αντέγραψε το public key
cat ~/.ssh/deploy_key.pub
```
Μετά: GitHub repo → Settings → Deploy Keys → Add deploy key → επικόλλησε το key.

**Επιλογή Β — Personal Access Token:**
```bash
git clone https://YOUR_TOKEN@github.com/YOUR_USERNAME/client-manager.git
```

---

*Τελευταία ενημέρωση: Μάιος 2026*

# Client Manager — Usage & Testing Guide

## Login

Navigate to `http://client-manager.test`. You will be redirected to `/login`.

| Field    | Value                          |
|----------|-------------------------------|
| Email    | `charisvaltzis.work@gmail.com` |
| Password | `change_me_now` ← **change this** |

To change the password, run:
```
php artisan tinker
>>> \App\Models\User::first()->update(['password' => bcrypt('your-new-password')]);
```

---

## Features & How to Use

### Dashboard (`/`)
- Total unpaid amount, overdue charge count, 5 most recently added clients.

### Clients (`/clients`)
- **List**: search by name, email, or contact person. Paginated (20/page).
- **Add**: `/clients/create` — name is required, all other fields optional.
- **View** (`/clients/{id}`): shows the client's projects, charges, and offers.
- **Edit** (`/clients/{id}/edit`): update details, change status (Active / Inactive / Archived).
- **Delete**: on the edit page. Cascades to projects, charges, and offers.

### Projects
- Created from a client's detail page (`/clients/{id}` → Add Project).
- Tracks: name, start date, renewal date, status, monthly fee.
- Renewals within 30 days appear in the upcoming renewals email digest.

### Charges (`/charges`)
- **List**: filter by Unpaid / Overdue / All. Search by title or client name.
- **Add** (`/charges/create`): pick a client (optionally a project), set title, amount, due date.
- **Edit** (`/charges/{id}/edit`): change any field, mark due date, delete charge.
- **Payments** (`/charges/{id}/payments/create`): record a partial or full payment.
  - Status updates automatically: Unpaid → Partially Paid → Paid.
- Overdue rows are highlighted in red.

### Offers (`/offers`)
- **List**: filter by Draft / Sent / Accepted / Rejected. Search by title or client name.
- **Add** (`/offers/create`): pick client/project, add line items (title, description, qty, unit price). Total calculates live.
- **Edit** (`/offers/{id}/edit`): update items, change status (Draft → Sent → Accepted / Rejected), record sent date.

### Email Log (`/email-logs`)
- Read-only log of every automated digest sent (or written to `laravel.log` during development).

---

## Automated Reminders

Two Artisan commands, scheduled automatically:

| Command                     | Schedule         | What it does                                    |
|-----------------------------|------------------|-------------------------------------------------|
| `reminders:overdue`         | Daily at 08:00   | Sends a digest of all unpaid + overdue charges  |
| `reminders:renewals`        | Every Monday 08:00 | Sends projects whose renewal date is ≤ 30 days away |

### Run manually (for testing)

```bash
php artisan reminders:overdue
php artisan reminders:renewals
php artisan reminders:renewals --days=90
```

During development, `MAIL_MAILER=log` in `.env` means emails are **written to `storage/logs/laravel.log`** instead of actually sent. Search for `To:` in that file to see the output.

To actually send emails via SMTP:
1. Set `MAIL_MAILER=smtp` in `.env`
2. Fill in `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
3. Or use `MAIL_MAILER=ses` with AWS SES credentials.

### Enable the scheduler (for production)

Add this to your server's crontab:
```
* * * * * cd /path/to/client-manager && php artisan schedule:run >> /dev/null 2>&1
```

On Laragon locally, you can test it by running:
```bash
php artisan schedule:work
```

---

## Testing Checklist

### Auth
- [ ] Visit `/` → redirected to `/login`
- [ ] Wrong credentials → error shown, stays on login
- [ ] Correct credentials → lands on Dashboard
- [ ] "Remember me" → session persists after browser restart
- [ ] Logout → redirected to `/login`, back-button does not re-enter

### Clients
- [ ] Add a client (name only) → appears in list
- [ ] Add a client (all fields) → all fields saved correctly
- [ ] Search by name, email, contact — results filter live
- [ ] Edit client → changes saved
- [ ] Delete client → client and all its data removed

### Projects
- [ ] Add project from client detail page → appears under that client
- [ ] Edit project → renewal date change reflected in reminder logic
- [ ] Delete project → project removed, charges with that project show `—` for project

### Charges
- [ ] Add charge with due date in the past → shows in Overdue tab, red highlight
- [ ] Record a partial payment → status becomes Partially Paid
- [ ] Record remaining payment → status becomes Paid, disappears from Unpaid tab
- [ ] Edit payment amount → status recalculates
- [ ] Delete payment → status recalculates
- [ ] Search by title and by client name

### Offers
- [ ] Add offer with 3 line items → total calculates correctly
- [ ] Add/remove items dynamically
- [ ] Edit offer → items updated (delete-and-recreate, order preserved)
- [ ] Change status to Sent → set sent date, appears in Sent tab
- [ ] Change status to Accepted/Rejected → badge updates

### Reminders
- [ ] Run `php artisan reminders:overdue` → check `storage/logs/laravel.log`
- [ ] Run `php artisan reminders:renewals --days=365` (use a wide window to catch all projects) → check log
- [ ] Entry appears in Email Log at `/email-logs`

---

## Missing Critical Features (Suggested)

These were intentionally out of scope but are worth considering once the app is in regular use:

### 1. Offer PDF export
A "Download PDF" button on the offer edit/view page.
- Package: `barryvdh/laravel-dompdf`
- Route: `GET /offers/{offer}/pdf` → returns a PDF rendered from a Blade template.
- Most useful for sending to clients.

### 2. Invoice / receipt generation
Currently charges are internal only. A simple printable invoice (PDF) per charge would let you send a formal document to the client.

### 3. Notes / activity log per client
A free-text notes field (or a simple timeline of events) on the client detail page. Useful for tracking calls, agreements, and custom context.

### 4. Soft deletes
Currently deletes are hard (cascading). Adding `SoftDeletes` to `Client`, `Charge`, and `Offer` means you can recover accidentally deleted records.

### 5. Offer "clone" action
A quick "Duplicate" button on an offer to copy its items into a new draft. Saves time when offers are similar across clients.

### 6. Dashboard charge aging buckets
Group unpaid charges by age: 0–30 days, 31–60, 61–90, 90+. Gives an instant receivables snapshot.

### 7. Basic CSV export
A "Export CSV" button on the charges list filtered to the current view. Useful for accountants or end-of-year reporting.

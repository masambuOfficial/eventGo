# Deploying Event Go to Laravel Cloud

## How this maps to Laravel Cloud's model

Event Go is a **monolith** — Livewire renders the UI server-side, Tailwind/JS
is compiled by Vite and served by the same Laravel app. There is no separate
frontend to deploy. On Laravel Cloud that means:

| Concern | Laravel Cloud resource |
|---|---|
| Frontend (Blade/Livewire/Vite build) + backend (PHP) | One **Application** environment |
| Database | One **Database** add-on (MySQL), attached to the app |
| Provider media & booking-workspace file uploads | One **Object Storage** add-on (S3-compatible), attached to the app |
| Nightly jobs (`bookings:auto-close`, `subscriptions:expire`, etc.) | A **Scheduler** toggle on the environment's App compute cluster |

So "frontend, backend, and database" is really one Application deploy plus
one attached Database — not three separate things to configure.

---

## 1. Required changes before the first deploy

These are things about the current codebase that only work because the app
is running on a single machine (XAMPP) with a persistent local disk. Cloud
compute is ephemeral and can run multiple instances, so they need fixing
first.

### 1a. Move file uploads off local disk — done

`app/Livewire/Providers/Media.php` and `app/Livewire/Bookings/Workspace.php`
used to write directly to the hardcoded **`public`** disk
(`driver: local`, `storage/app/public`, served via the `storage:link`
symlink) — a disk that's never reachable from more than one instance and
doesn't survive a redeploy. This has been fixed:

- `composer require league/flysystem-aws-s3-v3` is installed
  (`composer.json`/`composer.lock`).
- `Media.php`, `Workspace.php`, and the two Blade views that build display
  URLs (`media.blade.php`, `workspace.blade.php`) now call
  `Storage::disk(config('filesystems.default'))` instead of the hardcoded
  `'public'` string.
- Local `.env` / `.env.example` set `FILESYSTEM_DISK=public` so local dev
  behaves exactly as before (still writes to `storage/app/public`).

Nothing left to do here for the code — in Cloud's dashboard, attaching an
Object Storage bucket as the environment's *default* disk auto-injects a
`FILESYSTEM_DISK` env var pointing at it (see §3.3), so production doesn't
need this set by hand either.

### 1b. Database driver name

Local `.env` has `DB_CONNECTION=mariadb`. Laravel Cloud provisions managed
**MySQL** databases. `mariadb` is just Laravel's PDO-MySQL variant, so this
is a Cloud environment-variable change (`DB_CONNECTION=mysql`), not a schema
change — nothing in `db/01-schema.sql` uses MariaDB-only syntax (that's a
constraint `CLAUDE.md` already holds the schema to).

### 1c. Decide: one object storage bucket, or two?

This one only turned up after reading Cloud's actual docs, not something I'd
flagged before: **Laravel Cloud's object storage (Cloudflare R2 under the
hood) sets file visibility at the bucket level, and a single bucket cannot
mix public and private files.** You pick "Public" (world-readable URLs) or
"Private" (files only reachable via `Storage::temporaryUrl()` signed links)
when you create the bucket, for every file in it.

Right now both upload features point at the same disk:
- **Provider media** (`Media.php`) — profile photos, meant to be publicly
  viewable. Wants a **public** bucket.
- **Booking-workspace files** (`Workspace.php`) — whatever an organiser and
  provider exchange once booked (could be a contract, an invoice, a floor
  plan). These are only meant to be seen by the two parties to that booking,
  not the open internet. Wants a **private** bucket with signed URLs, not a
  world-readable one.

If you attach one bucket and mark it public (the obvious choice, since
that's what provider media needs), booking files become directly,
permanently, guessably-URL-accessible to anyone — a real privacy regression
for whatever gets uploaded there. Two options:

- **Ship one public bucket now, fix booking-file privacy later** — fine if
  nobody's uploaded anything sensitive to a booking yet (check: is
  `storage/app/public/booking-files` empty locally?), and you're OK
  revisiting this before real users start attaching real documents.
- **Do it properly now**: attach two buckets — a public one as the default
  disk (`Storage::disk(...)` calls in `Media.php` keep working unchanged),
  and a second, named, private one for booking files. That needs a small
  code change: `Workspace.php` and `workspace.blade.php` would target the
  named private disk explicitly instead of `config('filesystems.default')`,
  and the file link would switch from `Storage::disk(...)->url()` to
  `Storage::disk(...)->temporaryUrl($path, now()->addMinutes(5))`.

Not done yet — flagging it here so it's a decision, not an accident.

### 1d. Nothing else to change

`SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` are already
`database` — correct for a stateless, possibly multi-instance environment.
`config/session.php` already flips `secure` cookies on automatically when
`APP_ENV=production`. No code changes needed for either.

---

## 2. Prerequisites

- A [Laravel Cloud](https://cloud.laravel.com) account with billing set up.
- The repo already lives at `github.com/masambuOfficial/eventGo` — Cloud
  deploys straight from a connected GitHub repo, so just authorize Cloud's
  GitHub App on that repo (or your fork) when prompted.
- PHP 8.2+ (per `composer.json`'s `"php": "^8.2"` requirement) — select this
  in Cloud's environment settings.
- A domain, if you want one other than the free `*.laravel.cloud` subdomain
  Cloud assigns automatically.

---

## 3. Step-by-step

### 3.1 Create the Application
1. In the Cloud dashboard, **+ New application**.
2. Choose **From existing repository**, connect GitHub if you haven't yet
   (Cloud opens GitHub's own auth flow in a new tab), and select
   `masambuOfficial/eventGo`.
3. Name the application, pick a **region** (pick whichever is closest to
   your users — Uganda has no region of its own, so `eu-central-1`
   (Frankfurt) or `eu-west-2` (London) will generally beat a US region on
   latency; keep every attached resource in this same region, Cloud won't
   let a database attach across regions). Click **Create Application**.
4. This creates the Application plus one default **environment** (Cloud's
   unit of "a deployed instance of this app" — you can have more than one,
   e.g. `production` and `staging`, each with its own compute/resources).
   You land on that environment's infrastructure canvas.
5. In the environment's **General Settings**, confirm the PHP runtime is
   set to 8.2 or newer (8.5 is the current default for new environments).
6. In **Settings → Deployments**, set:
   - **Build command**: `composer install --no-dev && npm run build`
   - **Deploy command**: `php artisan migrate --force` (see §3.5 — this runs
     just before the new deploy goes live, on every deploy, not only the
     first)

   Do **not** put `php artisan storage:link`, `queue:restart`, or
   `optimize:clear` in either command — Cloud's own docs call these out
   specifically: the environment filesystem is ephemeral so a `storage:link`
   symlink never survives past that one deploy step, queue workers already
   restart automatically after every deploy, and `optimize:clear` can put
   your queue into an inconsistent state. `php artisan optimize` /
   `config:cache`, if you want them, belong in the **build** command, not
   the deploy command.

### 3.2 Attach a Database
1. On the environment's infrastructure canvas, **Add database**.
2. Create a new cluster: type **Laravel MySQL**, pick an instance size (a
   Flex size is fine to start — it can scale to zero when idle, so it's
   cheap for a pre-launch app; 4 users / 7 events of local dev data is a
   good proxy for how small this currently is), storage (5 GB minimum), and
   the **same region** as the Application.
3. Give it a database name, then attach it to this environment.
4. Cloud injects `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE` into
   the environment automatically — visible under General Settings. It does
   **not** inject `DB_CONNECTION`; add `DB_CONNECTION=mysql` yourself (§1b).
5. Re-deploy for the attachment to take effect (Cloud stages resource
   changes until you deploy — see the "staged changes" banner).

### 3.3 Attach Object Storage
1. On the infrastructure canvas, **Add bucket**.
2. Create a new bucket: type **Laravel Object Storage**. You'll be asked
   for:
   - **Disk name** — the key `Storage::disk('...')` will use. Set this to
     `s3` so it matches the disk block already sitting unused in
     `config/filesystems.php` — no config file changes needed.
   - **Default disk?** — say yes. This is what makes Cloud auto-inject
     `FILESYSTEM_DISK` (pointing at this bucket) plus the matching
     `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` / `AWS_DEFAULT_REGION` /
     `AWS_BUCKET` / `AWS_ENDPOINT` — the exact env var names
     `config/filesystems.php`'s `s3` block already reads. You won't need to
     type these in yourself.
   - **Visibility** — Public or Private, applies to every file in the
     bucket. See §1c above before picking.
3. Re-deploy for the attachment to take effect.

If you go with **two buckets** per §1c, repeat this with a second bucket —
give it a distinct disk name (e.g. `s3-private`), leave "default disk"
unchecked, and add a matching disk block to `config/filesystems.php`
pointing at Cloud's per-bucket env vars (Cloud shows you the exact variable
names for a non-default bucket under that bucket's settings once attached).

### 3.4 Set the remaining environment variables
In the environment's **Environment Variables** settings, set:

```
APP_NAME="Event Go"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-cloud-or-custom-domain>
APP_KEY=                      # generate fresh — see below, do NOT reuse local .env's key

DB_CONNECTION=mysql

# FILESYSTEM_DISK — do not set this by hand. Cloud injects it (and the
# matching AWS_* vars) automatically once the object storage bucket from
# §3.3 is attached as the default disk. A custom var you set here would
# override that injected value (Cloud lets custom vars win), so leave it
# out entirely and let the bucket attachment drive it.

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<your-sending-address>
MAIL_PASSWORD=<app-password>   # generate a new one for prod, don't copy the local .env value
MAIL_FROM_ADDRESS="<your-sending-address>"
MAIL_FROM_NAME="Event Go"

GOOGLE_CLIENT_ID=<same-or-new-oauth-client>
GOOGLE_CLIENT_SECRET=<matching-secret>
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Generate a fresh `APP_KEY` for production rather than copying the one in
your local `.env` — run `php artisan key:generate --show` once, either
locally or in Cloud's console, and paste only the output.

**Google sign-in note:** the OAuth redirect URI is tied to your production
domain. In the [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
for this project's OAuth client, add
`https://<your-cloud-or-custom-domain>/auth/google/callback` to the
**Authorized redirect URIs** list — Google will reject the callback
otherwise, even though the app code is unchanged.

Never commit real values for any of the above — set them only in Cloud's
environment variable UI.

### 3.5 Seed the reference data once
The `php artisan migrate --force` deploy command (set in §3.1) runs on every
deploy, which is correct for migrations but wrong for the reference-data
seeders — `EventTypeSeeder`/`ServiceCategorySeeder`/etc. insert fixed IDs
and would error on a second run. So seed manually, once, after the first
successful deploy: open the environment's **Commands** tab and run
```bash
php artisan db:seed --force
```
one time. (Commands run non-interactively and must finish in 30 minutes —
plenty for this.)

### 3.6 Turn on the Scheduler
`routes/console.php` defines seven daily commands (`bookings:auto-close`,
`subscriptions:expire`, `providers:aggregate-response-metrics`, etc.) —
this is how the provider ROI dashboard's `response_rate`/`bookings_won`
stay populated. This isn't an app-level or "Application" setting — it's a
toggle on the environment's **App compute cluster**, in the infrastructure
canvas: click the App cluster, enable **Scheduler**, save, and re-deploy.
Once live, Cloud invokes `schedule:run` on your behalf every minute; no
cron setup needed.

One thing worth knowing given the app's compute might scale to zero when
idle (cheap for a pre-launch product): Cloud reads your schedule via
`php artisan schedule:list` at *deploy* time and wakes the environment
automatically when a task is due, even from asleep — so the nightly jobs
still fire on schedule. But if you ever scale to multiple replicas, add
`->onOneServer()` to these seven schedule entries in `routes/console.php`,
or each replica will run them independently and you'll get e.g. duplicate
renewal-prompt emails.

### 3.7 Domain and SSL
Cloud gives you a working `*.laravel.cloud` URL automatically after the
first successful deploy (it's tagged `noindex, nofollow` so it won't get
indexed by search engines — fine, since it's not your real domain).

To use `eventgo.ug` (per `BRAND.md`'s naming decision) once it's registered:
open the environment's **Network settings → Add domain**. Cloud will ask a
few questions (wildcard subdomains? redirect `www` either direction?
tolerate a brief downtime window during cutover, or need a gap-free
transition?) and then show you the exact DNS records to add at your
registrar — no guessing which record type. Add them, click refresh until
the dashboard shows **Connected** (usually under 15 minutes), and Cloud
issues and auto-renews the SSL certificate for you. Update `APP_URL` and
`GOOGLE_REDIRECT_URI` to the real domain once it's live, and re-deploy.

---

## 4. Post-deploy smoke test

- [ ] Home page loads over HTTPS, no mixed-content warnings
- [ ] Register with email, and with Google sign-in
- [ ] Provider onboarding: upload a media file, confirm it's reachable after
      a second deploy (proves object storage is actually wired up, not
      silently falling back to local disk)
- [ ] Post an event through the wizard, confirm the requirements engine runs
- [ ] Submit an offer, accept it, confirm the booking workspace loads
- [ ] Check Cloud's logs for the scheduler firing (`schedule:run` entries)

## 5. Ongoing deploys

"Push to deploy" is on by default: every push to the branch this environment
tracks (`main`, per §3.1) triggers a new build, runs the deploy command
(`php artisan migrate --force`), and swaps traffic over with zero downtime,
automatically. No manual redeploy step needed for routine changes.

You can also trigger a deploy manually from the dashboard, or via a "deploy
hook" URL (Settings → Deployments) if you'd rather drive deploys from your
own CI — e.g. a GitHub Actions workflow that `curl -X POST`s the hook after
its own checks pass, instead of relying on push-to-deploy directly. Not
needed to get started; worth knowing if you later want CI gating.

## 6. Rollback

Laravel Cloud keeps prior deploys — use **Rollback** from the environment's
Deployments page if a release breaks something. Database migrations are
not automatically reversed by a rollback, so a deploy command that includes
a destructive migration should be reviewed carefully before it ships.

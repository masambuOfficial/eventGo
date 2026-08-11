# Event Go

Event coordination and service marketplace for the Ugandan market. An organiser
turns a vague idea ("500-guest wedding in Kampala on 30 August") into a costed,
structured requirements list, sources providers against it, compares offers,
and runs the job. Providers get qualified, well-specified opportunities instead
of cold inbound.

Event Go connects and equips — it is not a party to the deal.

## What makes it different

| | |
|---|---|
| **No commission** | Zero cut of the organiser↔provider transaction. Ever. |
| **No escrow, no custody** | Money moves between organiser and provider directly. |
| **No dispute resolution** | No adjudication, no refund rulings, no cancellation engine. |
| **Requirements matrix engine** | Scope answers → a costed line-item requirements list, not a vague brief. |
| **Provider ROI dashboard** | Providers can see the leads and connections Event Go generated for them. |
| **Revenue** | Provider subscriptions, featured placement, organiser premium — not a transaction cut. |

Full reasoning behind these decisions lives in
[`EventGo-System-Architecture.md`](EventGo-System-Architecture.md), the source
of truth for the product. Brand palette, type, logo rules and voice are in
[`BRAND.md`](BRAND.md).

## Stack

Laravel 12 + MySQL/MariaDB + Livewire 3 + Tailwind. Responsive web/PWA,
modular monolith under `app/Domain/*` (Identity, Catalog, Providers, Events,
Sourcing, Bookings, Messaging, Reputation, Attribution, Billing,
Notifications, Trust).

## Local setup

Requires PHP 8.2+, Composer, and MariaDB (XAMPP on Windows works — verify
with `php -v` first). Node for the frontend build.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set `DB_CONNECTION=mysql`, `DB_DATABASE=eventgo` (charset `utf8mb4`,
collation `utf8mb4_unicode_ci`) in `.env`, then:

```bash
php artisan migrate --seed
npm install
npm run dev
```

## License

Proprietary — all rights reserved. Not open source.

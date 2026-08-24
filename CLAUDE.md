# Event Go

Event coordination and service marketplace for the Ugandan market. An organiser
turns a vague idea ("500-guest wedding in Kampala on 30 August") into a costed,
structured requirements list, sources providers against it, compares offers, and
runs the job. Providers get qualified, well-specified opportunities.

**Read `EventGo-System-Architecture.md` before making design decisions.** It is
the source of truth. This file is the summary that stops you re-deriving it.

**Read `BRAND.md` before writing any UI.** Colours, typography, logo rules and
voice. The palette avoids yellow and red for reasons that are political, not
aesthetic — do not "improve" it.

---

## Decisions already made — do not relitigate

These were argued through and settled. If something here looks wrong, raise it
with the user rather than quietly building the alternative.

| Decision | Detail |
|---|---|
| **Neutral platform** | Event Go connects and equips. It is **not** a party to the deal. |
| **No commission** | Zero cut of the organiser↔provider transaction. Ever. |
| **No escrow, no custody** | Holding funds requires a Bank of Uganda licence under the NPS Act. |
| **No dispute resolution** | No adjudication, no refund rulings, no cancellation policy engine. |
| **No platform terms on deals** | Each provider states their own terms in `offers.terms`. |
| **Revenue** | Provider subscriptions (prepaid packages), featured placement, organiser premium. |
| **Self-registration only** | No admin-created or scraped provider profiles — DPPA lawful basis. |
| **No SMS at signup** | Email + Google sign-in. Identity verified via linked social business page. |
| **Payments between parties** | Out of scope. Organiser records what they paid as a note. |
| **Stack** | Laravel + MySQL/MariaDB + Livewire 3 + Tailwind. Responsive web/PWA. |

### Things a fresh agent will reach for and must not

- Adding a `commission_rate` or `platform_fee` column anywhere
- Building escrow, milestone payment gates, or fund-holding of any kind
- A disputes table, arbitration flow, or platform-imposed cancellation policy
- Postgres-only features (`JSONB` operators, partial indexes, `pg_trgm`, `tsvector`)
- Contact-masking as a lock-in mechanism — contacts release on offer acceptance
- Inventing benchmark prices for the budget engine (see below)

---

## Current state

```
event_GO/                            repo root — the Laravel app itself
  CLAUDE.md                          this file
  EventGo-System-Architecture.md     source of truth, ~9k words
  BRAND.md                           palette, type, logo, voice
  EVENT GO.docx                      original concept brief (superseded in places)
  db/
    01-schema.sql                    43 tables, validated against MariaDB 10.6
    02-seed-reference.sql            districts, taxonomy, templates, plans
  app/ database/ routes/ ...         standard Laravel 12 layout
```

The Laravel app was originally scaffolded in a nested `eventgo/` subfolder and
has since been flattened into the repo root — `app/`, `database/`, `routes/`,
`composer.json` etc. now sit alongside the planning docs and `db/`. One repo,
one history, one `.gitignore` (root-level, excludes `vendor/`, `node_modules/`,
`.env`).

`composer.json` still names the project `laravel/laravel` — rename when the
app is further along. What exists:

- `database/migrations/` — `db/01-schema.sql` has been converted, one migration
  per domain area (identity, catalog, providers, events, sourcing, bookings,
  attribution, billing, messaging, reputation, notifications) plus a final
  deferred-foreign-keys migration, and is applied against the local `eventgo`/
  `eventgo_test` databases.
- `database/seeders/` — districts, event types, service categories, plans,
  scope questions, requirement templates, roles. Mirrors
  `db/02-seed-reference.sql`.
- `.env` — configured for local MariaDB via XAMPP, database `eventgo`.
- `app/Domain/{Identity,Catalog,Providers,Events,Sourcing,Bookings,Messaging,
  Reputation,Attribution,Notifications}` — built out through Phase 4 (see
  Build order below). `Billing` and `Trust` don't exist yet — that's Phase 5.
- Livewire 3, Socialite (Google), roles (spatie/laravel-permission), and an
  admin shell all exist.

---

## Conventions

**Architecture** — modular monolith. `app/Domain/{Identity,Catalog,Providers,
Events,Sourcing,Bookings,Messaging,Reputation,Attribution,Billing,Notifications,
Trust}`, each with `Models/ Actions/ Events/ Policies/`. Enforce boundaries with
Pest `arch()` tests: nothing in `Domain\*` may reference `Http\*`.

**Actions, not services.** One invokable class per business operation, in the
owning domain. Cross-context reactions go through domain events and listeners,
never direct calls into another module's internals.

**Database**

- Money is `BIGINT`, whole Uganda Shillings. No minor units — UGX has no
  circulating subunit. Column names end `_ugx`.
- `DATETIME`, never `TIMESTAMP`. Events are booked years ahead; TIMESTAMP dies
  in 2038.
- Status columns are `VARCHAR` + `CHECK`, never native `ENUM`. Statuses change
  often and ENUM changes rebuild the table.
- State transitions are guarded in code, not implied by a nullable column.
- MySQL has no partial indexes. The "one accepted offer per requirement" rule
  uses a `STORED` generated column that is `NULL` unless accepted, under a
  unique key. Do not remove it — it is the only thing enforcing that invariant.

**Frontend** — Livewire 3 with strict latency discipline: `wire:model.blur` or
`.live.debounce.500ms`, never bare `wire:model.live` on text inputs. Every
network-bound interaction gets a `wire:loading` state. Target is a low-end
Android on 3G: page weight under 300 KB, LCP under 3 s.

**Frozen assets** — the logotype is a drawn SVG, not live webfont text.

---

## The two things that carry the product

**1. The requirements matrix engine** (architecture §6). Turns scope answers
into a costed requirements list. `requirement_templates.quantity_expression` is
evaluated with `symfony/expression-language` against a whitelisted variable set,
functions limited to `ceil floor round min max`. Never `eval()`.

The benchmark unit costs currently in `02-seed-reference.sql` are **placeholders
I invented**. They produce a plausible UGX 37.7m for a 500-guest wedding, but
they are not researched Kampala rates. Do not present them to a user as real,
and do not invent more. They come from planner interviews.

**2. The provider ROI dashboard** (architecture §8.3). With no commission, a
provider renews only if they can see business that arrived through Event Go.
The `provider_impressions`, `provider_leads` and `connections` tables exist for
this. Populate them from Phase 3 onward even though the dashboard ships later —
you cannot reconstruct retroactively which opportunities a provider saw.

**On `connections`:** knowing who connected with whom, and who is working with
whom, is the product. Ruling on what they agreed is not. Recording the graph is
entirely consistent with the neutral posture — Facebook knows who talks to whom
without adjudicating the conversation. Create a connection at first direct
interaction, not at booking, so relationships that formed and then went offline
are still visible. That set is the leakage measure.

---

## Build order

| Phase | What |
|---|---|
| 0 | Laravel scaffold, migrations from `db/01-schema.sql`, seeders, auth (email + Google), roles, admin shell |
| 1 | Provider self-registration, progressive profiling, social-page verification, services, areas, availability, media |
| 2 | Event wizard, scope questions, **requirements engine**, budget, dashboard |
| 3 | Opportunities, invitations, offers with line items, comparison, shortlist, accept → booking |
| 4 | Booking workspace, messaging, files, amendments, reviews, notifications |
| 5 | Attribution, provider ROI dashboard, plans and entitlements, prepaid billing, featured placement |
| 6 | PDPO compliance, security review, data-saver mode, pilot hardening |

Phases 0–5 are functionally complete: auth, roles, admin shell, provider
onboarding, the event wizard and requirements engine, sourcing through
accept → booking, the booking workspace (tasks, files, amendments,
messaging, two-way reviews, in-app notifications), and revenue (a
`Billing` domain with plans/subscriptions/entitlements enforced on offer
submission, capped `plan_boost`/`featured_multiplier` ranking terms, a
provider ROI dashboard, and staff-activated prepaid billing over mobile
money — no real PSP integration, per architecture §16's own timeline for
that). Provider `response_rate`/`median_response_minutes`/`bookings_won`
are now aggregated nightly from `provider_leads` rather than sitting dead.
Organiser-premium plans and real PSP/SMS delivery remain unbuilt by design
(architecture sequences them later / requires infra this project doesn't
have yet). The admin console (architecture line 75's full scope —
verification queue, taxonomy, user management, reports) is also complete:
`event_types`/`service_categories`/`districts`/`scope_questions`/
`requirement_templates` are editable in-app rather than requiring a
seeder-and-redeploy; `requirement_templates`' expressions are validated by
actually evaluating them before save; user management can grant/revoke
admin access and suspend accounts (enforced at Fortify's
`authenticateUsing`, so a suspended user cannot start a new session); and a
reports page covers liquidity, operational health, and raw funnel counts,
with revenue metrics gated behind a minimum-data floor rather than showing
misleading near-zero numbers. Phase 6 has not started.

---

## Local environment

Windows with XAMPP (MariaDB). Laravel 12 needs PHP 8.2+ — verify with
`C:\xampp\php\php.exe -v` before scaffolding. Database name `eventgo`,
charset `utf8mb4`, collation `utf8mb4_unicode_ci`.

## House style

Ask before adding a dependency. Prefer the boring option. When a decision has a
trade-off, say so plainly rather than picking silently — the user wants to be
told when something is a judgement call.

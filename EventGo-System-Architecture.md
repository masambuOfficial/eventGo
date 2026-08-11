# Event Go — System Architecture

**Version:** 0.3
**Date:** 9 August 2026
**Target market:** Uganda (Kampala metro pilot)
**Stack:** PHP / Laravel, PostgreSQL, responsive web + PWA
**Positioning:** Neutral platform — Event Go connects and equips; it does not intermediate the transaction

---

## 0. What Event Go is, and what it is not

Event Go is a **planning tool with a marketplace attached**. An organiser turns a vague idea ("a 500-guest wedding in Kampala on 30 August") into a costed, structured requirements list, finds providers against it, collects comparable offers, and runs the job from one place. Providers get qualified, well-specified opportunities they would otherwise chase by phone.

Event Go is **not** a party to the deal. It does not hold the money, set the terms, police the conversation, or rule on who was right when something goes wrong. The two parties agree their own terms and settle their own account. This is the Facebook Marketplace posture, and it is a deliberate choice with consequences that run through every section below.

### What this decision buys

- **No dispute desk.** Adjudicating event-quality disputes — "the décor wasn't what I pictured" — is far harder than "the parcel never arrived", and it is subjective, emotional, and unstaffable for a small team. Staying out is the single biggest operational cost avoided.
- **No custody, no licence.** Event Go never holds funds, so the Bank of Uganda licensing question in §7 largely disappears.
- **Credible neutrality.** Because Event Go takes **no cut of the transaction**, "we are not party to your agreement" is honest rather than a disclaimer. This matters: a platform that takes 7% and disclaims responsibility is not believed. A platform that takes nothing is.
- **A much smaller build.** No escrow, no milestone gating, no cancellation policy engine, no refund adjudication, no formal change-order workflow.

### What this decision costs, and where the risk now sits

Commission is payment for bearing risk and enforcing terms. Giving up the former means giving up the latter. Revenue therefore comes entirely from **provider subscriptions, featured placement, and organiser premium plans** (§8) — none of which are transaction-linked.

That relocates the central risk. The old failure mode was *leakage*: users meet here and transact elsewhere. Under this model leakage is irrelevant — Event Go earns nothing from the transaction either way.

**The new failure mode is irrelevance.** Providers pay a subscription only if they can point at real business that arrived through Event Go and judge it worth the money. If they cannot, they will not renew, and the platform becomes a busy free directory with no revenue. Every architectural priority below follows from that single sentence.

Three consequences worth stating plainly, because they invert earlier thinking:

1. **Lock-in mechanisms are now counterproductive.** Contact masking, on-platform-completion ranking penalties, leakage tracking — all of it is friction that reduces perceived value and earns nothing. Remove it. Release contact details freely once an offer is accepted.
2. **The provider ROI dashboard becomes the most commercially important screen in the product** (§8.3). It is what converts free accounts into paying ones. It should be built with the same care as the requirements engine.
3. **Organiser volume is the product being sold.** Featured placement is only worth buying if people are searching. Provider subscriptions are only worth paying if opportunities arrive. Both are downstream of organiser demand, so organiser experience is the growth engine — not a side of the marketplace to be balanced against supply.

---

## 1. Architectural principles

| # | Principle | Consequence |
|---|---|---|
| 1 | **The requirement line is the spine.** Sourcing, offers, budget, tasks, messages and reviews all hang off one `requirements` row. | One clean lifecycle instead of several parallel half-features. §4 |
| 2 | **Neutral by construction.** The system records what parties agreed; it never enforces it. No policy engine, no adjudication state, no platform-imposed terms. | Whole categories of code and operations do not exist. §5 |
| 3 | **Verify identity where it is free.** Linked social business pages, not paid SMS OTP. | Zero per-signup cost, and a stronger signal than an OTP. §10.1, §11.1 |
| 4 | **Every account is created by its owner.** No admin-created or scraped profiles. Staff assist; the provider registers. | Lawful basis under the DPPA; providers own their data. §2 |
| 5 | **Attribution is a first-class concern.** Every opportunity, view, offer and booking is traceable to a provider so ROI can be proven. | This is the revenue mechanism, not analytics garnish. §8.3, §15 |
| 6 | **The taxonomy is data, not code.** Event types, scope questions, categories and requirement templates are admin-editable rows. | New event types ship without a deploy. §6 |
| 7 | **Modular monolith.** One deployable, one database, module boundaries by convention and static analysis. | §9 |
| 8 | **Bandwidth is a currency.** Page weight budgets, AVIF/WebP, zero-egress object storage, no heavy JS bundles. | §13 |
| 9 | **Boring, cheap infrastructure.** Single VPS, Postgres, database queue, Cloudflare. Scale when the numbers demand it. | §14 |

---

## 2. Scope

### In (pilot)

- Self-service registration for organisers and providers — email + Google sign-in, no SMS cost
- Progressive profiling: four fields to register, profile completed over time, completeness gates capability
- Admin-managed service taxonomy and event-type templates
- Provider profiles: services, districts served, portfolio, indicative pricing, availability
- Free verification tiers via linked social business page (§11.1)
- Event creation → scope questions → **generated requirements matrix** → budget
- Two sourcing paths: public opportunity, and direct invitation
- Structured offers with line items; side-by-side comparison
- Shortlist → accept → booking record, with contact details released on acceptance
- **Light shared workspace** per booking: messaging, files, simple checklist, agreed-amount history
- Two-way reviews, gated on bookings both sides marked complete
- **Provider ROI dashboard** — opportunities received, offers submitted, bookings won, value
- **Subscription billing** via prepaid packages over mobile money (§8.2)
- Featured placement inventory and admin tooling to sell it
- In-app + email + web push notifications
- Admin console: verification queue, taxonomy, user management, reports

### Out

Payments between organiser and provider (§7) · escrow · cancellation and refund policy · dispute resolution · formal change-order workflow · contract generation · Event Day Mode · ticketing · native apps · artist booking · live-streaming coordination · corporate procurement · multi-event portfolios · USSD · WhatsApp bot · Luganda localisation

### Cold start with self-registration only

Admin-created provider profiles are out, and rightly so: under the **Data Protection and Privacy Act, 2019**, building a directory of providers' names, phone numbers and business details without their consent is processing personal data without a lawful basis, and a PDPO complaint waiting to happen.

The replacement is **assisted onboarding**. Staff go to where providers already are — wedding expos, supply markets, venue networks, category WhatsApp groups — and sit with the provider while *the provider* registers, on their own phone or a staff tablet. Same liquidity outcome, correct legally, and the provider leaves holding credentials they control.

Requirements this imposes:

- **Four-field registration.** Business name, email, password, one service category. Assisted signups happen in a noisy market in five minutes; every extra field costs conversions.
- **Progressive profiling.** `providers.profile_completeness` drives prompts and gates capability: below 40% you do not appear in search, below 60% you cannot submit offers. Profile-building becomes something the provider wants.
- **Attribution without ownership.** `onboarding_channel` and `referred_by_staff_id` measure which acquisition channel works. Neither grants staff any access to the account.
- **Immediate visible value.** Registration ends on a screen showing real open opportunities in the provider's category and district. A provider who registers and sees an empty board does not come back.

**Sequencing:** recruit a small number of real organisers with real events *first*, then recruit providers into visible demand. Roughly 100 providers across 10 categories is enough supply to be useful; past that, effort belongs on the organiser side. The reverse order produces providers watching an empty board, and they churn inside two weeks.

---

## 3. Bounded contexts

```
┌──────────────────────────────────────────────────────────────────┐
│                           Event Go                                │
│                                                                   │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐ │
│  │  Identity  │  │  Catalog   │  │ Providers  │  │   Events   │ │
│  │ users      │  │ event_types│  │ providers  │  │ events     │ │
│  │ orgs       │  │ categories │  │ services   │  │ scope_ans  │ │
│  │ roles      │  │ questions  │  │ areas      │  │ requirement│ │
│  │            │  │ templates  │  │ availabil. │  │ budget     │ │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘ │
│         │              │               │               │         │
│  ┌──────┴──────────────┴───────────────┴───────────────┴───────┐ │
│  │                        Sourcing                              │ │
│  │   opportunities · invitations · offers · offer_items         │ │
│  │   shortlists · clarifications                                │ │
│  └──────┬───────────────────────────────────────────────┬──────┘ │
│         │                                               │         │
│  ┌──────┴───────┐  ┌────────────┐  ┌────────────┐  ┌────┴─────┐ │
│  │   Bookings   │  │ Messaging  │  │ Reputation │  │Attribution│ │
│  │ = light      │  │ threads    │  │ reviews    │  │ impressions│ │
│  │   workspace  │  │ messages   │  │ ratings    │  │ leads     │ │
│  │ tasks·files  │  │ attachments│  │            │  │ outcomes  │ │
│  │ amendments   │  │            │  │            │  │           │ │
│  └──────────────┘  └────────────┘  └────────────┘  └───────────┘ │
│                                                                   │
│  ┌───────────────────────┐  ┌──────────────────────────────────┐ │
│  │  Billing (revenue)    │  │  Notifications · Trust · Admin   │ │
│  │ plans · packages      │  │  channel routing · verification  │ │
│  │ entitlements·payments │  │  moderation · reports            │ │
│  └───────────────────────┘  └──────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

Two contexts are new relative to a conventional marketplace design, and both exist because of the revenue model: **Attribution** (proving provider ROI) and **Billing** (collecting subscriptions). Note what is *absent*: no Payments context for organiser↔provider money, no Disputes context.

Contexts communicate through **domain events**, not direct cross-module calls. `OfferAccepted` is raised by Sourcing; Bookings, Attribution, Providers and Notifications each listen.

---

## 4. Domain model

### 4.1 The spine

```
Event
 └── Requirement  ("Catering for 500 guests, UGX 12–15m")
      ├── BudgetLine        (estimated → quoted → agreed)
      ├── Sourcing
      │    ├── Opportunity  (public listing)  ──┐
      │    └── Invitation   (direct)          ──┤
      │                                        ├──> Offer ──> OfferItem[]
      │                                        │      └──> Clarification[]
      ├── Booking  ── light workspace, §5.3
      │    ├── Task[]        (shared checklist)
      │    ├── File[]        (floor plans, menus, run sheets)
      │    ├── Amendment[]   (agreed-amount history)
      │    └── Review (×2, organiser↔provider)
      └── Thread            (messaging, scoped here)
```

The brief treats the requirements matrix, the budget, the procurement workflow, messaging and the task list as five separate features. They are five views of one object. Modelled separately, the budget says one thing and the dashboard says another and nobody trusts either. Modelled as projections of `requirements`, the dashboard is a query rather than a synchronisation problem.

### 4.2 Core tables (abbreviated)

```sql
-- ============ IDENTITY ============
users
  id, email UNIQUE NOT NULL, email_verified_at, password_hash NOT NULL,
  phone_e164 UNIQUE NULL, phone_verified_at NULL,
  full_name, preferred_language DEFAULT 'en',
  status, created_at, ...

organisations              id, name, ursb_number NULL, tin NULL, type
organisation_user          organisation_id, user_id, role

-- ============ CATALOG (admin-editable) ============
event_types
  id, slug, name, icon, is_active, sort_order

scope_questions
  id, event_type_id, key, label, input_type,   -- number|bool|select|multiselect|text
  options JSONB, is_required, sort_order, help_text

service_categories                             -- two levels
  id, parent_id NULL, slug, name, icon,
  unit_label, requires_capacity BOOL, is_active

requirement_templates                          -- generation rules, §6
  id, event_type_id, service_category_id,
  condition JSONB, quantity_expression TEXT,
  benchmark_unit_cost_ugx BIGINT NULL,
  default_notes, priority, sort_order

-- ============ PROVIDERS ============
providers
  id, owner_user_id NOT NULL,
  business_name, slug, about,
  primary_phone_e164 NULL, whatsapp_phone NULL,
  ursb_number NULL, tin NULL,
  base_district_id, verification_tier DEFAULT 0,
  profile_completeness SMALLINT DEFAULT 0,
  onboarding_channel,                          -- self_serve | assisted
  referred_by_staff_id NULL,
  rating_avg NUMERIC(3,2), rating_count INT,
  bookings_won INT, response_rate NUMERIC(5,2),
  median_response_minutes INT,
  plan_id NULL, plan_expires_at NULL,          -- §8.2
  is_featured_until NULL,
  is_active, created_at, ...

provider_social_accounts                       -- free verification signal, §11.1
  id, provider_id, platform,                   -- facebook | instagram | tiktok
  handle, profile_url, follower_count,
  page_created_at, verified_at, raw_snapshot JSONB

provider_services
  id, provider_id, service_category_id,
  min_capacity, max_capacity,
  price_min, price_max, price_unit, lead_time_days, description

provider_service_areas       provider_id, district_id      -- §6.3
provider_availability        id, provider_id, date, capacity_total, capacity_used,
                             is_blackout, note
provider_media               id, provider_id, type, path, variants JSONB,
                             caption, sort_order, event_date NULL
provider_verifications       id, provider_id, tier, evidence_type, evidence_path,
                             status, reviewed_by, reviewed_at, notes

-- ============ EVENTS ============
events
  id, owner_user_id, organisation_id NULL,
  name, event_type_id, custom_type_label NULL,
  starts_at, ends_at, timezone DEFAULT 'Africa/Kampala',
  district_id, venue_name NULL, venue_notes,
  guest_count_expected INT,
  budget_total_ugx BIGINT NULL, currency DEFAULT 'UGX',
  visibility, status, planning_progress SMALLINT, created_at, ...

event_scope_answers    event_id, scope_question_id, value JSONB
event_members          event_id, user_id, role, invited_by, accepted_at

requirements
  id, event_id, service_category_id,
  title, description, quantity NUMERIC, unit,
  budget_estimate_ugx BIGINT NULL, needed_by DATE NULL,
  priority, status,                            -- §5.1
  selected_offer_id NULL, booking_id NULL,
  source, created_at, ...

-- ============ SOURCING ============
opportunities
  id, requirement_id UNIQUE, published_at, closes_at,
  budget_visible BOOL, budget_min, budget_max,
  view_count, offer_count, status

invitations
  id, requirement_id, provider_id, invited_by_user_id,
  message, sent_at, viewed_at, responded_at, status

offers
  id, requirement_id, provider_id, submitted_by_user_id,
  total_ugx BIGINT, currency,
  scope_summary, inclusions TEXT, exclusions TEXT,
  terms TEXT,                                  -- the PROVIDER's terms, not ours
  valid_until DATE, availability_confirmed BOOL,
  status, submitted_at, withdrawn_at, ...

offer_items
  id, offer_id, description, quantity NUMERIC, unit,
  unit_price_ugx BIGINT, line_total_ugx BIGINT, sort_order

clarifications      id, requirement_id, offer_id NULL, asked_by_user_id,
                    question, answer, answered_at, is_public
shortlist_entries   requirement_id, offer_id, rank, note

-- ============ BOOKINGS = LIGHT WORKSPACE (§5.3) ============
bookings
  id, requirement_id UNIQUE, offer_id, provider_id, event_id,
  agreed_total_ugx BIGINT,
  status,                                      -- §5.3
  contacts_released_at,
  organiser_completed_at NULL, provider_completed_at NULL,
  cancelled_at, cancelled_by_side, cancellation_note

booking_tasks      id, booking_id, title, description, owner_side,
                   assigned_user_id NULL, due_at, status, completed_at
booking_files      id, booking_id, uploaded_by_user_id, label, path,
                   mime, size_bytes, created_at
booking_amendments id, booking_id, changed_by_user_id,
                   previous_total_ugx, new_total_ugx, note, created_at
                   -- a record of what the parties told us they agreed.
                   -- No approval state machine: we are not enforcing it.

-- ============ ATTRIBUTION (the revenue mechanism, §8.3) ============
provider_impressions   id, provider_id, context, requirement_id NULL,
                       viewer_user_id NULL, was_featured BOOL, created_at
provider_leads         id, provider_id, requirement_id, source,
                       -- opportunity_match | direct_invitation | search | featured
                       notified_at, viewed_at, offered_at, outcome, outcome_at

-- ============ BILLING (§8.2) ============
plans              id, code, name, audience, price_ugx, duration_days,
                   entitlements JSONB, is_active
subscriptions      id, subscriber_type, subscriber_id, plan_id,
                   starts_at, expires_at, status, auto_prompt BOOL
billing_payments   id, subscription_id, amount_ugx, channel, gateway,
                   gateway_ref UNIQUE, status, paid_at, raw_callback JSONB
featured_placements id, provider_id, service_category_id NULL, district_id NULL,
                   starts_at, ends_at, price_ugx, impressions, clicks

-- ============ MESSAGING / REPUTATION / NOTIFICATIONS ============
threads             id, subject_type, subject_id, created_at
thread_participants thread_id, user_id, role, last_read_at
messages            id, thread_id, sender_user_id, body, created_at
message_attachments id, message_id, path, mime, size_bytes

reviews             id, booking_id, direction, rating SMALLINT,
                    punctuality, quality, communication, value,
                    comment, is_published, published_at

notifications           id, user_id, type, payload JSONB, read_at
notification_deliveries id, notification_id, channel, status,
                        provider_ref, cost_ugx, sent_at, failed_reason
```

### 4.3 Three modelling decisions worth defending

**Offers carry line items.** The brief gives offers a single price field. That makes comparison impossible — a caterer quoting UGX 12m "all inclusive" against one quoting UGX 9m "excluding drinks and service staff" looks like a 25% saving that is not. `offer_items` plus explicit inclusions and exclusions is what makes the comparison screen honest, and it is the single feature most likely to make an organiser prefer Event Go to a WhatsApp group.

**`offers.terms` are the provider's terms.** Since Event Go publishes no standard terms, each provider states their own — deposit expectations, cancellation, what happens if guest numbers move. Surface these prominently in comparison. This is how neutrality is made workable rather than merely absent: the organiser is not left guessing, they are shown what each provider is offering and left to choose.

**Availability is capacity per date, not a boolean.** A caterer can serve three weddings on a Saturday; a DJ can serve one; a venue can serve one. `capacity_total` per provider per date handles all three and prevents the double-booking that destroys trust during the November–December wedding peak.

---

## 5. State machines

Explicit in code (`spatie/laravel-model-states` or guarded enum transitions), never implied by a nullable column.

### 5.1 Requirement — the important one

```
                    ┌──────────────────────────────────────┐
                    ▼                                      │
draft ──> open ──> sourcing ──> offers_received ──> shortlisted ──> awarded
  │                   │                                              │
  │                   └──> no_offers (auto after closes_at)          ▼
  └──> dropped                                                    booked
                                                                     │
                                                                     ▼
                                                                 fulfilled
```

`sourcing` is entered by publishing an opportunity **or** sending the first invitation. The brief's Option A and Option B differ only in how offers arrive, so they converge here rather than forking the model — a significant simplification.

### 5.2 Offer

```
draft ──> submitted ──> under_review ──> shortlisted ──> accepted
              │              │                │
              │              ├──> rejected    │
              │              └──> expired (valid_until passed)
              └──> withdrawn
```

One accepted offer per requirement, enforced in the database:

```sql
CREATE UNIQUE INDEX one_accepted_offer_per_requirement
  ON offers (requirement_id) WHERE status = 'accepted';
```

### 5.3 Booking — light workspace, no gates

```
confirmed ──> in_progress ──> completed ──> closed
    │              │
    └──────────────┴──> cancelled
```

| Transition | Trigger | Side effects |
|---|---|---|
| → `confirmed` | Organiser accepts an offer | **Contact details released immediately**; availability committed; workspace opens; tasks seeded from category template; `provider_leads.outcome` set to `won` |
| → `in_progress` | Scheduler at T-7 days | Run-sheet view activates |
| → `completed` | Both sides mark complete (or 14-day timeout after the event date) | Reviews unlock for both parties |
| → `cancelled` | Either side, with a note | Availability released. **No policy applied, no refund calculated** — the parties settle between themselves |

Note what is not here: no deposit gate, no payment state, no dispute branch, no policy engine. Cancellation records a fact and releases a date. That is the whole of it.

**Contacts release on acceptance, not later.** Under the previous model, masking was a lock-in mechanism. With no commission it is pure friction that reduces the value providers are being asked to pay for. Mask during browsing and offer submission — which protects against bulk scraping of the directory, a real concern — then release on acceptance and stop worrying about it.

### 5.4 Amendments, not variations

Guest counts move. A wedding for 500 becomes 650 six weeks out. The organiser's budget must follow, or the budget feature is worthless by week three.

But a formal propose/accept change-order workflow is contract governance, which is out of scope. So: `booking_amendments` is an **append-only log**. Either party edits the agreed amount, states why, and both see the history. Event Go records what they say they agreed and does not adjudicate whether they agreed it. Roughly a day of work rather than a week, and it keeps the budget honest without pretending to enforce anything.

---

## 6. The requirements matrix engine

The brief calls this a key feature and specifies it in two sentences. It is the actual differentiator — the one thing here that a Facebook group cannot do — and it needs real design.

### 6.1 Generation

1. Organiser picks an event type → system loads that type's `scope_questions`
2. Answers stored as `event_scope_answers`
3. Engine evaluates every `requirement_template` for the type: `condition` checked against the answers, `quantity_expression` evaluated, budget estimated from `benchmark_unit_cost_ugx` × quantity
4. Suggested requirements presented **as editable drafts**, never auto-committed
5. Organiser adds, removes, adjusts, commits

### 6.2 Expression evaluation — not `eval()`

`quantity_expression` is a restricted arithmetic language over answer keys. Use `symfony/expression-language` with a whitelisted variable set and no function access beyond `ceil`, `floor`, `round`, `min`, `max`.

| Category | Expression | Rationale |
|---|---|---|
| Chairs | `ceil(guests * 1.05)` | 5% buffer |
| Catering | `guests` | one plate per guest |
| Ushers | `ceil(guests / 50)` | local staffing norm |
| Tents | `ceil(guests / 150)` | typical marquee capacity |
| Toilets | `ceil(guests / 75)` | outdoor events only, via `condition` |

**These ratios and the benchmark unit costs are the platform's proprietary knowledge and its most defensible asset.** Seed them from interviews with 10–15 experienced Kampala planners, version them, and refine against actual offer data — every offer submitted teaches you what a category really costs, which no competitor can copy. An organiser getting a credible requirements list and budget in 90 seconds, where the alternative is a week of phone calls, is the reason anyone visits. Treat the seed data as a deliverable, not a config file.

### 6.3 Geography: districts, not radii

Providers here think in administrative units — "I serve Kampala, Wakiso and Mukono" — not kilometre radii. Model `districts` (with region and optional centroid) as reference data and let providers tick them. Exact, explainable matching with a simple join, no PostGIS in the pilot, and it matches how providers describe themselves.

Seed from the official UBOS list with an `effective_from` column — Uganda creates new districts regularly, so this is slowly-changing reference data, not a hardcoded array.

---

## 7. Payments between organiser and provider

**Out of the MVP. Build it later, if at all.**

The optional payment rail is a reasonable long-term convenience, but with no commission attached it is **pure cost with no revenue**, and it is by far the longest lead-time item on the plan: PSP contracting in Uganda routinely takes six to ten weeks, plus integration, reconciliation and support. Spending that on a feature that earns nothing and — with direct mobile money being free, instant and universally understood — will see low uptake is the wrong trade for a pilot.

Deferring it removes the single largest schedule risk from the plan (§16).

**What to do instead, in the MVP:**

- The organiser records what they paid, in the budget, as a note. Two fields, no integration. This preserves the budget-tracking feature, which is what organisers actually wanted.
- `booking_amendments` keeps the agreed total honest.

**When to revisit.** If pilot data shows organisers repeatedly asking for it, or if a PSP offers terms good enough that the rail becomes a selling point for provider subscriptions ("get paid faster through Event Go"), build it then. Note that this would mean charging providers for a payment feature via subscription rather than commission — which keeps the neutral posture intact.

**One thing to preserve now:** the `PaymentGateway` interface described in §8.2 is being built anyway for subscription billing. Design it generically enough that a future collections use case is an implementation rather than a redesign.

---

## 8. Revenue architecture

This section carries the same weight the payments section would in a commission model. It is where the money is.

### 8.1 Streams

| Stream | Mechanism | Realistic timing |
|---|---|---|
| **Featured placement** | Providers pay for prominence in category and district search results | **Earliest.** Sells hope, not proven results — viable as soon as organiser search volume is non-trivial |
| **Provider subscriptions** | Prepaid packages unlocking visibility, unlimited offers, analytics, portfolio promotion | 6–12 months post-launch, once providers can see ROI |
| **Organiser premium** | Advanced budgeting, multiple events, team collaboration, exports | Later, and modest — most organisers plan one event every few years, so recurring dynamics are weak |

Be clear-eyed about the ordering. Featured placement can be sold before subscriptions because a provider will gamble UGX 50,000 on visibility long before they will commit to a monthly fee. It is also the stream that requires organiser traffic, which reinforces §0: **organiser volume is the product.**

**Featured placement must always be labelled "Sponsored" and capped in the ranking multiplier (§9.6).** An unlabelled pay-to-win ranking destroys organiser trust, and organiser trust is the entire inventory being sold. Protecting it is a commercial decision, not an ethical footnote.

### 8.2 Subscription billing over mobile money — harder than it looks

True recurring billing barely works in this market. There is no reliable card-on-file equivalent for mobile money; each renewal typically requires a customer approval prompt, and completion rates on those prompts are poor. A subscription model designed as if it were Stripe will show alarming involuntary churn.

**Use prepaid packages, not subscriptions.** The provider buys access for a fixed period — 1, 3, 6 or 12 months — pays once, and is prompted to renew before expiry. Discount longer packages aggressively; a 12-month package at 40% off a monthly rate is worth far more than the nominal discount because it removes eleven renewal failures.

```
plans.duration_days       -- 30 | 90 | 180 | 365
subscriptions.expires_at  -- hard expiry, no grace beyond a configured window
```

Mechanics:

- **Entitlements as data**, not code. `plans.entitlements` JSONB — `{"max_offers_per_month": null, "analytics": true, "portfolio_slots": 30, "search_boost": 1.2}` — checked through a single `Entitlements::for($provider)` service. Plans change constantly in the first year; a deploy per pricing experiment is unacceptable.
- **Graceful degradation, never deletion.** On expiry the provider drops to free-tier limits. Their profile, portfolio and reviews stay. A provider who loses their history will not return; one who loses their boost might renew.
- **Renewal prompts** at T-14, T-7, T-1 and expiry, across web push, email and in-app. This is the one place a targeted SMS is worth its cost — a renewal prompt has direct revenue attached, unlike an opportunity alert.
- **Idempotent callbacks.** Unique index on `billing_payments.gateway_ref`; the handler must tolerate the same webhook three times.
- **Manual activation path.** Support must be able to activate a package against a mobile money reference the customer read over the phone. Payments will fail in ways the API does not report, and the alternative is database surgery.

This is a much simpler PSP integration than marketplace split payments: Event Go is charging its own customers for its own service. That is ordinary merchant collection, not third-party fund handling, and it does not raise the licensing question in the way holding transaction money would. Confirm the treatment with counsel, but the exposure is materially lower.

### 8.3 The provider ROI dashboard — the conversion engine

This is what turns free accounts into paying ones, and it is the reason the Attribution context exists. A provider deciding whether to renew asks one question: *did this make me money?* The product must answer it with specifics.

```
Your last 90 days on Event Go

  Opportunities matched to you           47
  Viewed                                 31
  Offers submitted                       12
  Bookings won                            3
  Value of bookings won        UGX 8,400,000
  Your plan cost                  UGX 150,000

  Profile views                         284   (↑ 22% vs previous 90 days)
  Search appearances                    1,102
  Median response time              4h 20m    (top 25% in Catering)
```

Design notes that matter:

- **Track the full chain** — impression → notification → view → offer → outcome — in `provider_leads`. Without it the dashboard is guesswork and the renewal conversation has no evidence.
- **Show value even when it is unflattering.** A provider who won nothing needs to see that they responded to 3 of 47 opportunities, with a median response time of two days. That is a coachable, renewable relationship. Hiding it produces silent churn.
- **Benchmark against category peers.** "Top 25% in Catering" is far more motivating than a raw number, and it is free to compute.
- **Booking value is self-reported** and will be imperfect, since no money flows through. Accept the imprecision; directionally correct is enough to sell a renewal. Prompt for confirmation at booking completion to improve it.

The uncomfortable corollary: this dashboard will also tell providers when Event Go is *not* working for them, and some will leave. That is correct. A subscription business built on customers who cannot tell whether they are getting value is one bad quarter from collapse.

---

## 9. Laravel application architecture

### 9.1 Modular monolith

```
app/
  Domain/
    Identity/  Catalog/  Providers/  Events/  Sourcing/
    Bookings/  Messaging/  Reputation/  Attribution/  Billing/
    Notifications/  Trust/
      {Models, Actions, Events, Policies, Support}
  Http/
    Controllers/   thin
    Livewire/      grouped by domain
    Middleware/
  Support/         Money, Phone, Districts, Expression, Entitlements
database/
  migrations/
  seeders/         taxonomy, districts, benchmark ratios — versioned and meaningful
tests/
  Feature/         one folder per domain
  Unit/
```

Enforce boundaries with `deptrac` or Pest `arch()` assertions: `Domain\Sourcing` may depend on `Domain\Providers` models but not the reverse; nothing in `Domain\*` may reference `Http\*`.

### 9.2 Actions, not services

One invokable class per business operation:

```php
final class AcceptOffer
{
    public function __construct(
        private DatabaseManager $db,
        private Dispatcher $events,
    ) {}

    public function __invoke(Offer $offer, User $actor): Booking
    {
        return $this->db->transaction(function () use ($offer, $actor) {
            $requirement = $offer->requirement()->lockForUpdate()->first();

            $requirement->guardTransitionTo(RequirementStatus::Awarded);
            $offer->guardTransitionTo(OfferStatus::Accepted);

            $offer->requirement->offers()
                ->whereKeyNot($offer->id)
                ->whereIn('status', [OfferStatus::Submitted, OfferStatus::Shortlisted])
                ->update(['status' => OfferStatus::Rejected]);

            $booking = Booking::createFromOffer($offer, $actor);

            $requirement->update([
                'status'            => RequirementStatus::Booked,
                'selected_offer_id' => $offer->id,
                'booking_id'        => $booking->id,
            ]);

            $this->events->dispatch(new OfferAccepted($offer, $booking));

            return $booking;
        });
    }
}
```

`OfferAccepted` listeners then, asynchronously: reserve availability, release contact details, seed workspace tasks, close the `provider_leads` row as `won`, update the budget projection, open the thread, queue notifications. None of that sits in the action.

For a small team each action is one file, one test, one responsibility — it resists the fat-controller and god-service failure modes without requiring anyone to learn CQRS.

### 9.3 Concurrency hazards

| Hazard | Mitigation |
|---|---|
| Two organisers book the same provider for the same date | `SELECT … FOR UPDATE` on `provider_availability` + `CHECK (capacity_used <= capacity_total)` |
| Double acceptance on one requirement | Partial unique index (§5.2) + row lock on requirement |
| Duplicate billing webhooks | Unique index on `billing_payments.gateway_ref`; idempotent handler |
| Duplicate notification on retry | Idempotency key on `notification_deliveries` |

### 9.4 Frontend: Livewire 3, with eyes open

**Livewire 3 + Alpine + Tailwind, with `wire:navigate`.**

The honest trade-off: one language, no separate API layer, small initial payload, far less work for a small team — against Livewire's server round-trips, which on a 150–400ms Ugandan mobile link feel sluggish if implemented naively.

Non-negotiable conventions:

- `wire:model.blur` or `.live.debounce.500ms` — never bare `wire:model.live` on text inputs
- Client-side validation in Alpine for anything that does not need the server
- `wire:loading` skeletons on every network-bound interaction, so latency is visible rather than mysterious
- Multi-step wizards hold state client-side and post once per step

If the pilot shows latency hurting conversion on the event-creation wizard, that one flow can be rewritten as an Alpine component posting to a JSON endpoint without touching the rest of the app. Note the escape hatch now.

### 9.5 Queues, scheduling, search

- **Queue driver:** `database` for the pilot. One less service to run; adequate at these volumes. Move to Redis + Horizon past roughly 50k jobs/day.
- **Priority queues:** `notifications`, `media`, `default`.
- **Scheduler:** opportunity closing, offer expiry, booking auto-complete timeout, T-30/T-7/T-1 event reminders, **subscription expiry and renewal prompts**, provider digest, nightly attribution rollups.
- **Search:** PostgreSQL full-text (`tsvector` GIN) plus `pg_trgm` for fuzzy business-name matching. No Meilisearch or Elasticsearch for hundreds of providers — a service to run, secure and back up for no measurable gain. Revisit past ~50k providers.

### 9.6 Provider ranking

A single, testable, explainable function — and now also a revenue lever, which makes discipline more important, not less:

```
score =  0.30 × category_and_capacity_fit
       + 0.20 × verification_tier_normalised
       + 0.20 × rating_bayesian          (shrunk toward the mean for low counts)
       + 0.20 × responsiveness           (response_rate, median_response_minutes)
       + 0.10 × recency_of_activity
       × plan_boost                      (from entitlements, capped at 1.3)
       × featured_multiplier             (capped, always labelled "Sponsored")
```

Use Bayesian-shrunk ratings — `(v/(v+m))·R + (m/(v+m))·C` — so a provider with one 5-star review does not outrank one with forty at 4.6.

**Cap the paid multipliers and hold the cap.** Every uncapped point of paid boost is borrowed against organiser trust, and organiser trust is the inventory. There will be pressure to raise it during slow revenue months; the cap should be a config value with a written rationale so the argument only has to be had once.

Note that **on-platform completion is deliberately absent** from the ranking. Under a commission model it belonged there. Under this one it would penalise providers for behaviour Event Go has no stake in.

---

## 10. Identity and access

### 10.1 Authentication without SMS

```
users.email          UNIQUE, NOT NULL      -- login identifier
users.password_hash  NOT NULL
users.phone_e164     UNIQUE, NULLABLE      -- collected, not verified at signup
```

- **Google sign-in via Laravel Socialite** as the primary button. Most Android phones here are already signed into a Google account, so this removes password creation for a large share of users and costs nothing. Email + password as fallback.
- Phone collected at registration, hidden from other users until booking confirmation, never SMS-verified.
- Normalise phone on write with `giggsey/libphonenumber-for-php`; store E.164 only. Ugandan numbers arrive as `0772…`, `+256772…`, `256772…` and `772…`; normalising at the boundary prevents a class of duplicate-account bugs regardless of verification.

**On the SMS decision, for the record.** The direct cost was smaller than it feels — roughly UGX 30 per message, about $5 a month at 500 signups. The better arguments for dropping it are that it loses signups and breaks when a provider registers on a staff tablet away from their own phone. What matters is that the identity floor is relocated rather than removed (§11.1).

Keep `SmsChannel` implemented in the notification layer. Renewal prompts (§8.2) justify it commercially even when opportunity alerts do not.

### 10.2 Two authorisation layers — do not merge them

1. **Global roles** (`spatie/laravel-permission`): `admin`, `support`, `organiser`, `provider`. A user can be both organiser and provider — many Kampala event professionals are.
2. **Per-event membership** (`event_members.role`): `owner`, `planner`, `coordinator`, `viewer`, scoped to one event.

Policies consult both. Conflating them — a common shortcut — makes the "planner managing five clients' weddings" role impossible to express later.

### 10.3 Data protection

Under the **Data Protection and Privacy Act, 2019**, registration with the **Personal Data Protection Office** is required for data collectors, processors and controllers, renewed annually, with no small-business exemption. Enforcement has been tightening. This is a launch blocker; budget for legal support.

Consequences:

- Explicit, logged consent at signup, with marketing separated from service delivery
- A working data subject access / export / erasure path — export as a queued job producing a JSON + media archive; erasure as anonymisation
- Retention policy per table, enforced by a scheduled job
- Audit log for every admin action touching personal data
- A processor register with data-processing agreements for the SMS gateway, billing PSP, object storage and error tracking

**Neutrality does not extend to data protection.** Event Go is a data controller regardless of its posture on business terms, and "the parties sort it out between themselves" is not a defence to a PDPO complaint. This is the one area where staying out of it is not an option.

---

## 11. Trust and verification

Without commission, escrow, or adjudication, **reputation and verification carry the entire trust load.** They are also, conveniently, the things providers will pay to improve.

### 11.1 Verification tiers — all free to operate at Tier 1

| Tier | Requirement | Cost to us | Badge | Effect |
|---|---|---|---|---|
| 0 | Email verified, profile ≥40% | Free | — | Appears in search |
| 1 | **Linked social business page** with history (below) | Free | Profile Verified | Can submit offers |
| 2 | URSB certificate + TIN checked | Staff time | Business Verified | Ranking boost; higher-value opportunities |
| 3 | Physical visit or two verified references, plus 3 completed bookings | Staff time | Event Go Verified | Top tier; featured eligibility |

**Tier 1 replaces SMS OTP with something both free and more informative.** Essentially every event provider in Kampala already runs a Facebook or Instagram business page — it is their existing shopfront. Requiring them to link it via OAuth gives you, at no cost:

- proof the business exists and has operated over time (page creation date, posting history)
- an audience-size signal that is hard to fake cheaply (follower count)
- a portfolio the provider has already built, which they can import rather than re-upload — removing the largest single obstacle to profile completion
- public accountability: a business with 8,000 followers and three years of posted work has far more to lose from a scam than a phone number does

Snapshot the metrics at verification time into `raw_snapshot` rather than calling the platform APIs live — API terms and rate limits change, and you need the historical value for the audit trail. Re-verify periodically as a background job.

A provider with no social presence is not blocked; they route to Tier 2 by uploading URSB documents instead. Both paths exist because the goal is a credible signal, not a specific one.

**A useful side effect of the billing design:** when a provider buys a package (§8.2) they pay from a mobile money account, and the PSP returns the name registered against it. That is a free identity check on exactly the providers who matter most — the paying ones. Capture and store it; it costs nothing and strengthens the badge on your most valuable accounts.

Tier 3 requires human work and does not scale — which is exactly why it is valuable. Restrict it to categories where a bad provider costs the most: venues, large-scale catering, production.

### 11.2 What makes the platform worth using

With lock-in mechanisms removed, only real value remains. Honestly, that is a healthier position — but it means these must be genuinely good:

1. **The requirements matrix and budget.** Nothing else in the market turns "a 500-guest wedding" into a costed 12-line plan in 90 seconds. This is the reason organisers arrive.
2. **Structured offer comparison.** Line items, inclusions, exclusions, and each provider's own terms, side by side. This is the reason organisers stay through procurement.
3. **Verified reputation.** A rating built from real bookings, which exists nowhere else and cannot be moved to WhatsApp.
4. **Qualified opportunities.** For providers, a well-specified brief with guest count, date, district and budget band beats an "are you available?" DM by a wide margin. This is what the subscription actually buys.
5. **The shared workspace.** Files, checklist and messages in one place instead of six chat threads. Modest, but it keeps the booking visible in the product, which keeps attribution accurate.

### 11.3 Fraud vectors

| Vector | Mitigation |
|---|---|
| Fake provider takes a deposit and disappears | Tier 1 before offers; ratings; **and honest expectation-setting** — Event Go cannot recover the money and must not imply otherwise |
| Bulk scraping of the provider directory | Contacts masked during browsing; rate limits; require login for full profiles |
| Review farming via fake bookings | Reviews published only when both sides mark complete; velocity checks on new accounts; social-page age as a signal |
| Duplicate profiles to reset a bad rating | `pg_trgm` similarity check on business name at creation; social account uniqueness; admin merge tool |
| Bulk fake accounts (signup is free) | Registration rate limits per IP; email verification before search visibility; Tier 1 before offers. Accounts that cannot get verified are inert |
| Organiser harvests quotes with no intent to book | Quote-request rate limits; organiser reputation; require event completeness before publishing |

**Set expectations explicitly in the product, not just the terms.** At the point an organiser accepts an offer, say plainly that the agreement is between them and the provider and that Event Go does not hold funds or mediate disputes. Users who understand the model do not feel deceived when it behaves as designed — and a platform that quietly lets people assume protection it does not offer will be blamed for the first failure regardless of what its terms say.

---

## 12. Non-functional requirements

| Concern | Target | Approach |
|---|---|---|
| Page weight (key screens) | < 300 KB, < 500 KB with images | Tailwind purge, no heavy JS, AVIF/WebP |
| LCP on simulated 3G | < 3.0 s | Server-rendered HTML, critical CSS, lazy images |
| p95 server time | < 400 ms | Eager-loading discipline; N+1 detection in CI |
| Portfolio images | 3 variants, AVIF with WebP fallback | Queued `spatie/laravel-medialibrary`; originals never served |
| Offline | Shell + last-viewed event cached | Service worker, cache-first assets, network-first data |
| Browser floor | Android 8 / Chrome 90+ | No bleeding-edge CSS/JS; test on a real low-end device |
| Uptime | 99.5% pilot | Single node is acceptable now; be honest about it |
| Backups | Nightly full + WAL, 30-day retention, **restore tested monthly** | An untested backup is not a backup |

**Data-saver mode** — user-toggleable, serving lower-resolution images and suppressing non-essential media — is worth building. Mobile data is a real household cost and competitors will not bother.

---

## 13. Deployment and running cost

```
                 Cloudflare (free: CDN, TLS, WAF, caching)
                           │
                 ┌─────────┴─────────┐
                 │   App VPS         │  Ubuntu 24.04 LTS
                 │   4 vCPU / 8 GB   │  nginx + PHP-FPM 8.3+
                 │   PostgreSQL 16   │  Laravel + Livewire
                 │   Queue + cron    │  supervisor
                 └─────────┬─────────┘
                           │
           ┌───────────────┼───────────────┐
    Object storage    Email (Postmark   Billing PSP
    (Cloudflare R2)    / Resend / SES)  (subscriptions only)
```

Deploy via **Laravel Forge** or **Ploi** — zero-downtime deploys, TLS, queue and scheduler supervision, without anyone writing Ansible. Highest-leverage $15/month in the budget.

**Hosting location.** Hetzner (Germany, ~150–190ms, ~$25/mo) versus AWS Cape Town (~90–110ms, ~$90+/mo) versus local Kampala datacentres (~10–30ms, varies — verify uptime and support before committing). Start on Hetzner, **measure real Livewire interaction latency in the pilot**, and relocate if the data justifies it. Keep the app stateless — sessions and cache in Postgres, media in R2 — so relocation stays a config change.

| Item | USD/mo |
|---|---|
| App + DB VPS | ~25 |
| Object storage (R2, zero egress) | ~5 |
| Cloudflare | 0 |
| Forge or Ploi | ~15 |
| Transactional email | ~15 |
| SMS (renewal prompts only) | ~10 |
| Error tracking | ~10–25 |
| Monitoring, offsite backups | ~10 |
| **Total** | **≈ $90–105** |

Roughly UGX 330,000–390,000/month at ~UGX 3,700/USD — verify the current rate. The point of this number is that **infrastructure is not the constraint.** Staff time and provider acquisition are. Do not spend architecture effort optimising a $100 bill.

---

## 14. Build sequence

Sized for one to two developers plus you.

| Phase | Weeks | Deliverable | Exit criterion |
|---|---|---|---|
| **0 — Foundations** | 1–3 | Repo, CI, deploy pipeline, email + Google auth, roles, districts, taxonomy, admin shell | Staff can log in and create a service category |
| **1 — Supply** | 4–7 | Self-service provider registration, progressive profiling, social-page verification, services, areas, availability, media pipeline | 100 providers self-registered across 10 categories; median completeness > 60% |
| **2 — Demand** | 8–12 | Event wizard, scope questions, **requirements engine**, budget, event dashboard | An organiser gets a credible 12-line requirements matrix and budget in under 5 minutes |
| **3 — Sourcing** | 13–17 | Opportunities, invitations, offers with line items, clarifications, comparison, shortlist, accept → booking | A real event sourced end-to-end on the platform |
| **4 — Workspace & reputation** | 18–20 | Light booking workspace, messaging, files, amendments, two-way reviews, notifications | A booking runs from acceptance to completed reviews inside the app |
| **5 — Revenue** | 21–24 | **Attribution tracking, provider ROI dashboard, plans and entitlements, prepaid billing over mobile money, featured placement** | First provider pays for a package, and can see why |
| **6 — Harden & pilot** | 25–27 | PDPO compliance, security review, load test, data-saver mode, pilot onboarding | 20 events and 100 providers active; free→paid conversion measured |

**≈ 6½ months to a live pilot.**

Two notes on the shape of this plan. First, attribution tracking must be **instrumented from Phase 3** even though the dashboard is built in Phase 5 — you cannot retroactively reconstruct which opportunities a provider saw. Second, Phase 5 is now the phase that must not be cut. Under a commission model, revenue mechanics came free with the transaction; here they are a distinct build, and a pilot that ships without the ROI dashboard has no path from free users to paying ones.

Start in **week 1** for external lead time: **PDPO registration**, **email domain warm-up with SPF/DKIM/DMARC**, and **billing PSP conversations** (simpler than marketplace split payments, but still six-plus weeks of contracting).

---

## 15. Metrics

**Liquidity**

- Requirements published → % receiving ≥1 offer within 48h — **the single most important number**
- Median time-to-first-offer; offers per requirement (target ≥3)
- Provider response rate and median response time

**Conversion funnels**

- Organiser: event created → requirements committed → sourcing started → offer accepted → booking confirmed
- Provider: registered → email verified → profile ≥60% → Tier 1 verified → first offer submitted
- Event-creation wizard drop-off per step (where Livewire latency will show)
- Split every provider step by `onboarding_channel` — assisted and self-serve behave very differently and each has a real cost

**Revenue — the ones this model lives or dies on**

- **Free → paid conversion rate**, and time from registration to first purchase
- **Package renewal rate** by duration, and by whether the provider won a booking in the prior period
- Featured placement sell-through and impression yield
- ARPU per active provider; revenue per active organiser
- **Provider-perceived ROI:** booking value won ÷ plan cost, distributed across the paying base. If the median is below roughly 10×, renewals will not hold

**Engagement — leading indicators for renewals**

- Opportunities matched per provider per month, and the view rate on them
- Share of confirmed bookings with workspace activity after week one
- Web push opt-in rate; email deliverability (bounce, complaint, open on opportunity notifications)

**Operational**

- Verification queue depth and median clearance time
- Fake-account and duplicate-profile detection rate

Build these as an admin reporting page over SQL views. Do not buy an analytics product yet.

---

## 16. Open decisions

| # | Decision | Recommendation | Settle by |
|---|---|---|---|
| 1 | Package pricing | Start low and single-tier — one package, three durations. Multi-tier pricing before you know what providers value is guesswork with a maintenance cost | Before Phase 5 |
| 2 | Billing PSP | Choose on settlement reliability, webhook quality, and manual-reference lookup for support, not headline rate | **Week 4** — contracting takes 6+ weeks |
| 3 | Featured placement pricing and inventory limits | Cap slots per category per district so it stays scarce and stays credible | Before Phase 5 |
| 4 | Benchmark ratios and price bands | Interview 10–15 Kampala planners during Phase 1. This is the product's core asset | Before Phase 2 |
| 5 | Terms of service and the neutrality disclaimer | Draft with a lawyer. Must be clear about what Event Go does not do, and surfaced in-product at offer acceptance, not buried | Before pilot |
| 6 | PDPO registration and legal entity | Start week 1 | Before pilot launch |
| 7 | Hosting region | Hetzner, measure, relocate if pilot data justifies it | Week 1 (reversible) |
| 8 | Livewire vs Inertia | Livewire with §9.4 conventions and the documented escape hatch | Week 1 (partly reversible) |
| 9 | Whether to build the optional payment rail at all | Defer. Revisit only if pilot data shows organiser demand or a PSP offers terms that make it a subscription selling point | Post-pilot, on evidence |

---

## 17. Where this document disagrees with the concept brief

Recorded plainly, so disagreements are decisions rather than drift.

1. **Commission is gone.** The brief calls transaction commission "one of the most important revenue streams". It is incompatible with a neutral platform — commission is payment for bearing risk and enforcing terms, and Event Go does neither. Revenue is subscriptions, featured placement and organiser premium. The brief's financial projections need rewriting on that basis.
2. **Escrow is gone.** It requires BoU licensing or a bank partnership, and it contradicts the neutral posture besides.
3. **Lead-generation fees.** The brief is right to be sceptical. Charging for leads before conversion is proven will collapse supply. Featured placement is the version of this that works, because it is optional and priced as a gamble rather than a toll.
4. **MVP scope.** The brief's MVP is roughly three times what a small team can validate in six months. §2 cuts it.
5. **The requirements matrix is the product.** The brief treats it as one feature among many. It is the differentiator, the reason organisers arrive, and — through accumulated offer data — the only durable moat here. It deserves the dedicated engine in §6.
6. **Offers need line items.** Single-price offers make comparison impossible, which removes the main reason to use Event Go over a WhatsApp group (§4.3).
7. **The trust model is thinner than the brief implies.** The brief promises transparency, accountability and consumer confidence. A neutral platform delivers those through reputation and verification alone — real, but weaker than the brief's language suggests. Do not oversell it to users or to funders.
8. **Timeline.** 11 months for the full scope is optimistic. Six and a half for a hard-cut pilot is realistic, and a pilot is what unlocks funding.
9. **The funder narrative needs adjusting.** The brief's positioning paragraph leans on "transparent procurement, digital payments and reputation management". Payments are out. Lead with what is true and still compelling: Event Go formalises a fragmented informal industry by giving SMEs discoverability, structured demand and portable reputation. Funders will ask for the liquidity and conversion metrics in §15, not a feature list — instrument early so those numbers exist when you need them.

---

## Sources

- [PDPO registration of data controllers — Cliffe Dekker Hofmeyr](https://www.cliffedekkerhofmeyr.com/en/news/publications/2025/Sectors/Technology-Communications/technology-and-communications-alert-13-august-to-register-or-not-to-register-the-ugandan-personal-data-protection-offices-decision-on-the-registration-of-data-controllers)
- [Uganda Data Protection and Privacy Act overview — Securiti](https://securiti.ai/uganda-data-protection-and-privacy-act/)
- [PDPO compliance for offshore entities — DLA Piper Privacy Matters](https://privacymatters.dlapiper.com/2025/08/uganda-data-protection-regulator-clarifies-compliance-requirements-for-offshore-entities/)
- [National Payment Systems Act, Cap 59 — ULII](https://ulii.org/en/akn/ug/act/2020/15/eng@2023-12-31)
- [BoU FAQs on the NPS Act and Regulations](https://bou.or.ug/uploads/Frequently_asked_Questions_on_the_NPS_ACT_2020_and_the_NPS_Regulations_2021_5818aec7f5.pdf)
- [Key features of Uganda's National Payment Systems Act — ENSafrica](https://www.ensafrica.com/news/detail/3330/key-features-of-ugandas-national-payment-syst)
- [Payment gateways in Uganda: fees, APIs, Laravel support — Trophy Developers](https://www.trophydevelopers.com/payment-gateways-in-uganda/)
- [Pesapal Uganda](https://www.pesapal.com/ug)
- [Uganda VAT registration and filing 2026 — Basket Advisory](https://basketadvisory.com/blog-vat-uganda.html)
- [EFRIS compliance — PwC Uganda](https://www.pwc.com/ug/en/press-room/efris-compliance.html)

-- =====================================================================
-- Event Go — database schema
-- Target: MySQL 8.0+ / MariaDB 10.6+ (XAMPP ships MariaDB)
-- Engine: InnoDB, utf8mb4
--
-- Conventions
--   * All money is BIGINT, whole Uganda Shillings. UGX has no circulating
--     subunit, so no minor-unit multiplier. Column names end in _ugx.
--   * All datetimes are DATETIME (not TIMESTAMP) to avoid the 2038 limit —
--     events are booked years ahead. Application stores UTC.
--   * Status columns are VARCHAR + CHECK, not native ENUM. ENUM changes
--     require a table rebuild; statuses will change often.
--   * Laravel-compatible: bigint unsigned auto-increment PKs, created_at
--     and updated_at on mutable tables.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- REFERENCE
-- =====================================================================

CREATE TABLE districts (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(80)  NOT NULL,
  region          VARCHAR(20)  NOT NULL,
  centroid_lat    DECIMAL(9,6) NULL,
  centroid_lng    DECIMAL(9,6) NULL,
  effective_from  DATE         NULL,
  is_active       TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_districts_name (name),
  KEY idx_districts_region (region),
  CONSTRAINT chk_districts_region
    CHECK (region IN ('central','eastern','northern','western'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- IDENTITY
-- =====================================================================

CREATE TABLE users (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email              VARCHAR(190) NOT NULL,
  email_verified_at  DATETIME     NULL,
  password           VARCHAR(255) NULL,
  google_id          VARCHAR(64)  NULL,
  phone_e164         VARCHAR(20)  NULL,
  phone_verified_at  DATETIME     NULL,
  full_name          VARCHAR(120) NOT NULL,
  preferred_language VARCHAR(8)   NOT NULL DEFAULT 'en',
  status             VARCHAR(20)  NOT NULL DEFAULT 'active',
  last_seen_at       DATETIME     NULL,
  remember_token     VARCHAR(100) NULL,
  created_at         DATETIME     NULL,
  updated_at         DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone_e164),
  UNIQUE KEY uq_users_google (google_id),
  CONSTRAINT chk_users_status
    CHECK (status IN ('active','suspended','deleted'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organisations (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(150) NOT NULL,
  type        VARCHAR(30)  NOT NULL DEFAULT 'company',
  ursb_number VARCHAR(60)  NULL,
  tin         VARCHAR(30)  NULL,
  created_at  DATETIME     NULL,
  updated_at  DATETIME     NULL,
  PRIMARY KEY (id),
  CONSTRAINT chk_org_type
    CHECK (type IN ('company','ngo','church','school','government','planner'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organisation_user (
  organisation_id BIGINT UNSIGNED NOT NULL,
  user_id         BIGINT UNSIGNED NOT NULL,
  role            VARCHAR(20) NOT NULL DEFAULT 'member',
  created_at      DATETIME NULL,
  PRIMARY KEY (organisation_id, user_id),
  KEY idx_orguser_user (user_id),
  CONSTRAINT fk_orguser_org  FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
  CONSTRAINT fk_orguser_user FOREIGN KEY (user_id)         REFERENCES users(id)         ON DELETE CASCADE,
  CONSTRAINT chk_orguser_role CHECK (role IN ('owner','admin','member'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- CATALOG  (admin-editable taxonomy — no deploys to add an event type)
-- =====================================================================

CREATE TABLE event_types (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug       VARCHAR(60)  NOT NULL,
  name       VARCHAR(80)  NOT NULL,
  icon       VARCHAR(40)  NULL,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order SMALLINT     NOT NULL DEFAULT 0,
  created_at DATETIME     NULL,
  updated_at DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_event_types_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE scope_questions (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_type_id BIGINT UNSIGNED NOT NULL,
  `key`         VARCHAR(50)  NOT NULL,
  label         VARCHAR(200) NOT NULL,
  help_text     VARCHAR(255) NULL,
  input_type    VARCHAR(20)  NOT NULL,
  options       JSON         NULL,
  default_value VARCHAR(120) NULL,
  is_required   TINYINT(1)   NOT NULL DEFAULT 0,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  created_at    DATETIME     NULL,
  updated_at    DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_scope_q (event_type_id, `key`),
  CONSTRAINT fk_scope_q_type FOREIGN KEY (event_type_id) REFERENCES event_types(id) ON DELETE CASCADE,
  CONSTRAINT chk_scope_q_input
    CHECK (input_type IN ('number','bool','select','multiselect','text','date'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_categories (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id         BIGINT UNSIGNED NULL,
  slug              VARCHAR(80) NOT NULL,
  name              VARCHAR(100) NOT NULL,
  icon              VARCHAR(40)  NULL,
  unit_label        VARCHAR(40)  NULL,
  requires_capacity TINYINT(1)   NOT NULL DEFAULT 0,
  is_active         TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order        SMALLINT     NOT NULL DEFAULT 0,
  created_at        DATETIME     NULL,
  updated_at        DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_svc_cat_slug (slug),
  KEY idx_svc_cat_parent (parent_id),
  CONSTRAINT fk_svc_cat_parent FOREIGN KEY (parent_id) REFERENCES service_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rules that turn scope answers into a requirements matrix. See architecture §6.
CREATE TABLE requirement_templates (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_type_id          BIGINT UNSIGNED NOT NULL,
  service_category_id    BIGINT UNSIGNED NOT NULL,
  condition_expr         VARCHAR(255) NULL,
  quantity_expression    VARCHAR(255) NOT NULL DEFAULT '1',
  benchmark_unit_cost_ugx BIGINT      NULL,
  default_title          VARCHAR(150) NULL,
  default_notes          VARCHAR(500) NULL,
  priority               VARCHAR(20)  NOT NULL DEFAULT 'important',
  sort_order             SMALLINT     NOT NULL DEFAULT 0,
  is_active              TINYINT(1)   NOT NULL DEFAULT 1,
  created_at             DATETIME     NULL,
  updated_at             DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_req_tpl (event_type_id, service_category_id),
  CONSTRAINT fk_req_tpl_type FOREIGN KEY (event_type_id)       REFERENCES event_types(id)        ON DELETE CASCADE,
  CONSTRAINT fk_req_tpl_cat  FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE CASCADE,
  CONSTRAINT chk_req_tpl_priority
    CHECK (priority IN ('essential','important','optional'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PROVIDERS
-- =====================================================================

CREATE TABLE providers (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_user_id          BIGINT UNSIGNED NOT NULL,
  business_name          VARCHAR(150) NOT NULL,
  -- lowercased, punctuation and suffixes stripped. Duplicate detection
  -- leans on this because MySQL has no pg_trgm. See architecture §11.3.
  normalised_name        VARCHAR(150) NOT NULL,
  slug                   VARCHAR(170) NOT NULL,
  about                  TEXT         NULL,
  primary_phone_e164     VARCHAR(20)  NULL,
  whatsapp_phone         VARCHAR(20)  NULL,
  ursb_number            VARCHAR(60)  NULL,
  tin                    VARCHAR(30)  NULL,
  base_district_id       BIGINT UNSIGNED NULL,
  verification_tier      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  profile_completeness   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  onboarding_channel     VARCHAR(20)  NOT NULL DEFAULT 'self_serve',
  referred_by_staff_id   BIGINT UNSIGNED NULL,
  rating_avg             DECIMAL(3,2) NULL,
  rating_count           INT UNSIGNED NOT NULL DEFAULT 0,
  bookings_won           INT UNSIGNED NOT NULL DEFAULT 0,
  response_rate          DECIMAL(5,2) NULL,
  median_response_minutes INT UNSIGNED NULL,
  plan_id                BIGINT UNSIGNED NULL,
  plan_expires_at        DATETIME     NULL,
  featured_until         DATETIME     NULL,
  payout_msisdn          VARCHAR(20)  NULL,
  payout_registered_name VARCHAR(150) NULL,
  is_active              TINYINT(1)   NOT NULL DEFAULT 1,
  created_at             DATETIME     NULL,
  updated_at             DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_providers_slug (slug),
  UNIQUE KEY uq_providers_owner (owner_user_id),
  KEY idx_providers_district (base_district_id),
  KEY idx_providers_norm_name (normalised_name),
  KEY idx_providers_tier_active (verification_tier, is_active),
  KEY idx_providers_payout (payout_msisdn),
  FULLTEXT KEY ft_providers (business_name, about),
  CONSTRAINT fk_providers_owner    FOREIGN KEY (owner_user_id)    REFERENCES users(id)     ON DELETE CASCADE,
  CONSTRAINT fk_providers_district FOREIGN KEY (base_district_id) REFERENCES districts(id) ON DELETE SET NULL,
  CONSTRAINT chk_providers_tier         CHECK (verification_tier BETWEEN 0 AND 3),
  CONSTRAINT chk_providers_completeness CHECK (profile_completeness BETWEEN 0 AND 100),
  CONSTRAINT chk_providers_channel      CHECK (onboarding_channel IN ('self_serve','assisted')),
  CONSTRAINT chk_providers_rating       CHECK (rating_avg IS NULL OR (rating_avg >= 1 AND rating_avg <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_social_accounts (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id    BIGINT UNSIGNED NOT NULL,
  platform       VARCHAR(20)  NOT NULL,
  handle         VARCHAR(120) NOT NULL,
  profile_url    VARCHAR(255) NOT NULL,
  follower_count INT UNSIGNED NULL,
  page_created_at DATE        NULL,
  raw_snapshot   JSON         NULL,
  verified_at    DATETIME     NULL,
  created_at     DATETIME     NULL,
  updated_at     DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_social_platform_handle (platform, handle),
  KEY idx_social_provider (provider_id),
  CONSTRAINT fk_social_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT chk_social_platform CHECK (platform IN ('facebook','instagram','tiktok','x','linkedin'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_services (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id         BIGINT UNSIGNED NOT NULL,
  service_category_id BIGINT UNSIGNED NOT NULL,
  min_capacity        INT UNSIGNED NULL,
  max_capacity        INT UNSIGNED NULL,
  price_min_ugx       BIGINT       NULL,
  price_max_ugx       BIGINT       NULL,
  price_unit          VARCHAR(40)  NULL,
  lead_time_days      SMALLINT UNSIGNED NULL,
  description         VARCHAR(500) NULL,
  created_at          DATETIME     NULL,
  updated_at          DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_provider_service (provider_id, service_category_id),
  KEY idx_psvc_category (service_category_id),
  CONSTRAINT fk_psvc_provider FOREIGN KEY (provider_id)         REFERENCES providers(id)          ON DELETE CASCADE,
  CONSTRAINT fk_psvc_category FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE CASCADE,
  CONSTRAINT chk_psvc_capacity CHECK (min_capacity IS NULL OR max_capacity IS NULL OR min_capacity <= max_capacity),
  CONSTRAINT chk_psvc_price    CHECK (price_min_ugx IS NULL OR price_max_ugx IS NULL OR price_min_ugx <= price_max_ugx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_service_areas (
  provider_id BIGINT UNSIGNED NOT NULL,
  district_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (provider_id, district_id),
  KEY idx_psa_district (district_id),
  CONSTRAINT fk_psa_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_psa_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Capacity per date, not a boolean. A caterer can serve three weddings on
-- a Saturday; a venue can serve one. Architecture §4.3.
CREATE TABLE provider_availability (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id    BIGINT UNSIGNED NOT NULL,
  date           DATE         NOT NULL,
  capacity_total SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  capacity_used  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_blackout    TINYINT(1)   NOT NULL DEFAULT 0,
  note           VARCHAR(200) NULL,
  created_at     DATETIME     NULL,
  updated_at     DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_avail (provider_id, date),
  KEY idx_avail_date (date),
  CONSTRAINT fk_avail_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT chk_avail_capacity CHECK (capacity_used <= capacity_total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_media (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id BIGINT UNSIGNED NOT NULL,
  type        VARCHAR(15)  NOT NULL DEFAULT 'image',
  path        VARCHAR(255) NOT NULL,
  variants    JSON         NULL,
  caption     VARCHAR(200) NULL,
  event_date  DATE         NULL,
  sort_order  SMALLINT     NOT NULL DEFAULT 0,
  created_at  DATETIME     NULL,
  updated_at  DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_media_provider (provider_id, sort_order),
  CONSTRAINT fk_media_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT chk_media_type CHECK (type IN ('image','video'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_verifications (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id   BIGINT UNSIGNED NOT NULL,
  tier          TINYINT UNSIGNED NOT NULL,
  evidence_type VARCHAR(30)  NOT NULL,
  evidence_path VARCHAR(255) NULL,
  status        VARCHAR(20)  NOT NULL DEFAULT 'pending',
  notes         VARCHAR(500) NULL,
  reviewed_by   BIGINT UNSIGNED NULL,
  reviewed_at   DATETIME     NULL,
  created_at    DATETIME     NULL,
  updated_at    DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_verif_provider (provider_id),
  KEY idx_verif_status (status, created_at),
  CONSTRAINT fk_verif_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_verif_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)     ON DELETE SET NULL,
  CONSTRAINT chk_verif_status CHECK (status IN ('pending','approved','rejected')),
  CONSTRAINT chk_verif_evidence CHECK (evidence_type IN ('social_page','ursb','tin','national_id','reference','site_visit'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EVENTS
-- =====================================================================

CREATE TABLE events (
  id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id            CHAR(26)     NOT NULL,
  owner_user_id        BIGINT UNSIGNED NOT NULL,
  organisation_id      BIGINT UNSIGNED NULL,
  name                 VARCHAR(150) NOT NULL,
  event_type_id        BIGINT UNSIGNED NOT NULL,
  custom_type_label    VARCHAR(80)  NULL,
  starts_at            DATETIME     NOT NULL,
  ends_at              DATETIME     NULL,
  district_id          BIGINT UNSIGNED NULL,
  venue_name           VARCHAR(150) NULL,
  venue_notes          VARCHAR(500) NULL,
  guest_count_expected INT UNSIGNED NULL,
  budget_total_ugx     BIGINT       NULL,
  description          TEXT         NULL,
  visibility           VARCHAR(10)  NOT NULL DEFAULT 'private',
  status               VARCHAR(20)  NOT NULL DEFAULT 'draft',
  planning_progress    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at           DATETIME     NULL,
  updated_at           DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_events_public (public_id),
  KEY idx_events_owner (owner_user_id),
  KEY idx_events_date (starts_at),
  KEY idx_events_status (status),
  KEY idx_events_district (district_id),
  CONSTRAINT fk_events_owner    FOREIGN KEY (owner_user_id)   REFERENCES users(id)         ON DELETE CASCADE,
  CONSTRAINT fk_events_org      FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE SET NULL,
  CONSTRAINT fk_events_type     FOREIGN KEY (event_type_id)   REFERENCES event_types(id)   ON DELETE RESTRICT,
  CONSTRAINT fk_events_district FOREIGN KEY (district_id)     REFERENCES districts(id)     ON DELETE SET NULL,
  CONSTRAINT chk_events_visibility CHECK (visibility IN ('private','public')),
  CONSTRAINT chk_events_status     CHECK (status IN ('draft','planning','procuring','confirmed','in_progress','completed','cancelled')),
  CONSTRAINT chk_events_progress   CHECK (planning_progress BETWEEN 0 AND 100),
  CONSTRAINT chk_events_dates      CHECK (ends_at IS NULL OR ends_at >= starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_scope_answers (
  event_id          BIGINT UNSIGNED NOT NULL,
  scope_question_id BIGINT UNSIGNED NOT NULL,
  value             JSON NOT NULL,
  updated_at        DATETIME NULL,
  PRIMARY KEY (event_id, scope_question_id),
  KEY idx_answers_question (scope_question_id),
  CONSTRAINT fk_answers_event    FOREIGN KEY (event_id)          REFERENCES events(id)          ON DELETE CASCADE,
  CONSTRAINT fk_answers_question FOREIGN KEY (scope_question_id) REFERENCES scope_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_members (
  event_id    BIGINT UNSIGNED NOT NULL,
  user_id     BIGINT UNSIGNED NOT NULL,
  role        VARCHAR(20) NOT NULL DEFAULT 'viewer',
  invited_by  BIGINT UNSIGNED NULL,
  accepted_at DATETIME NULL,
  created_at  DATETIME NULL,
  PRIMARY KEY (event_id, user_id),
  KEY idx_members_user (user_id),
  CONSTRAINT fk_members_event   FOREIGN KEY (event_id)   REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_members_user    FOREIGN KEY (user_id)    REFERENCES users(id)  ON DELETE CASCADE,
  CONSTRAINT fk_members_inviter FOREIGN KEY (invited_by) REFERENCES users(id)  ON DELETE SET NULL,
  CONSTRAINT chk_members_role CHECK (role IN ('owner','planner','coordinator','viewer'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The spine. Everything downstream hangs off a requirement. Architecture §4.1.
CREATE TABLE requirements (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id           CHAR(26)     NOT NULL,
  event_id            BIGINT UNSIGNED NOT NULL,
  service_category_id BIGINT UNSIGNED NOT NULL,
  title               VARCHAR(150) NOT NULL,
  description         TEXT         NULL,
  quantity            DECIMAL(12,2) NULL,
  unit                VARCHAR(40)  NULL,
  budget_estimate_ugx BIGINT       NULL,
  needed_by           DATE         NULL,
  priority            VARCHAR(20)  NOT NULL DEFAULT 'important',
  status              VARCHAR(25)  NOT NULL DEFAULT 'draft',
  source              VARCHAR(15)  NOT NULL DEFAULT 'generated',
  selected_offer_id   BIGINT UNSIGNED NULL,
  booking_id          BIGINT UNSIGNED NULL,
  created_at          DATETIME     NULL,
  updated_at          DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_requirements_public (public_id),
  KEY idx_req_event (event_id),
  KEY idx_req_category (service_category_id),
  KEY idx_req_status (status),
  CONSTRAINT fk_req_event    FOREIGN KEY (event_id)            REFERENCES events(id)             ON DELETE CASCADE,
  CONSTRAINT fk_req_category FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE RESTRICT,
  CONSTRAINT chk_req_priority CHECK (priority IN ('essential','important','optional')),
  CONSTRAINT chk_req_source   CHECK (source IN ('generated','manual')),
  CONSTRAINT chk_req_status
    CHECK (status IN ('draft','open','sourcing','offers_received','shortlisted','awarded','booked','fulfilled','no_offers','dropped'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SOURCING
-- =====================================================================

CREATE TABLE opportunities (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  requirement_id BIGINT UNSIGNED NOT NULL,
  published_at   DATETIME     NULL,
  closes_at      DATETIME     NULL,
  budget_visible TINYINT(1)   NOT NULL DEFAULT 0,
  budget_min_ugx BIGINT       NULL,
  budget_max_ugx BIGINT       NULL,
  view_count     INT UNSIGNED NOT NULL DEFAULT 0,
  offer_count    INT UNSIGNED NOT NULL DEFAULT 0,
  status         VARCHAR(15)  NOT NULL DEFAULT 'open',
  created_at     DATETIME     NULL,
  updated_at     DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_opp_requirement (requirement_id),
  KEY idx_opp_status_closes (status, closes_at),
  CONSTRAINT fk_opp_requirement FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT chk_opp_status CHECK (status IN ('open','closed','cancelled')),
  CONSTRAINT chk_opp_budget CHECK (budget_min_ugx IS NULL OR budget_max_ugx IS NULL OR budget_min_ugx <= budget_max_ugx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invitations (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  requirement_id     BIGINT UNSIGNED NOT NULL,
  provider_id        BIGINT UNSIGNED NOT NULL,
  invited_by_user_id BIGINT UNSIGNED NOT NULL,
  message            VARCHAR(1000) NULL,
  sent_at            DATETIME NULL,
  viewed_at          DATETIME NULL,
  responded_at       DATETIME NULL,
  status             VARCHAR(15) NOT NULL DEFAULT 'sent',
  created_at         DATETIME NULL,
  updated_at         DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invitation (requirement_id, provider_id),
  KEY idx_inv_provider (provider_id, status),
  CONSTRAINT fk_inv_requirement FOREIGN KEY (requirement_id)     REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_provider    FOREIGN KEY (provider_id)        REFERENCES providers(id)    ON DELETE CASCADE,
  CONSTRAINT fk_inv_inviter     FOREIGN KEY (invited_by_user_id) REFERENCES users(id)        ON DELETE CASCADE,
  CONSTRAINT chk_inv_status CHECK (status IN ('sent','viewed','responded','declined','expired'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE offers (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  requirement_id         BIGINT UNSIGNED NOT NULL,
  provider_id            BIGINT UNSIGNED NOT NULL,
  submitted_by_user_id   BIGINT UNSIGNED NOT NULL,
  total_ugx              BIGINT       NOT NULL,
  scope_summary          VARCHAR(1000) NULL,
  inclusions             TEXT         NULL,
  exclusions             TEXT         NULL,
  -- The PROVIDER's terms. Event Go publishes none of its own. Architecture §4.3.
  terms                  TEXT         NULL,
  valid_until            DATE         NULL,
  availability_confirmed TINYINT(1)   NOT NULL DEFAULT 0,
  status                 VARCHAR(15)  NOT NULL DEFAULT 'draft',
  submitted_at           DATETIME     NULL,
  withdrawn_at           DATETIME     NULL,
  created_at             DATETIME     NULL,
  updated_at             DATETIME     NULL,
  -- MySQL has no partial indexes. A STORED generated column that is NULL
  -- unless accepted, combined with a unique key, gives the same guarantee:
  -- unique indexes ignore NULLs, so at most one accepted offer per requirement.
  accepted_marker TINYINT UNSIGNED
    GENERATED ALWAYS AS (CASE WHEN status = 'accepted' THEN 1 ELSE NULL END) STORED,
  PRIMARY KEY (id),
  UNIQUE KEY uq_offer_one_accepted (requirement_id, accepted_marker),
  UNIQUE KEY uq_offer_per_provider (requirement_id, provider_id),
  KEY idx_offers_provider (provider_id, status),
  KEY idx_offers_status (status),
  CONSTRAINT fk_offers_requirement FOREIGN KEY (requirement_id)       REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT fk_offers_provider    FOREIGN KEY (provider_id)          REFERENCES providers(id)    ON DELETE CASCADE,
  CONSTRAINT fk_offers_submitter   FOREIGN KEY (submitted_by_user_id) REFERENCES users(id)        ON DELETE CASCADE,
  CONSTRAINT chk_offers_total  CHECK (total_ugx >= 0),
  CONSTRAINT chk_offers_status CHECK (status IN ('draft','submitted','under_review','shortlisted','accepted','rejected','withdrawn','expired'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Line items are what make offers comparable. Architecture §4.3.
CREATE TABLE offer_items (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  offer_id       BIGINT UNSIGNED NOT NULL,
  description    VARCHAR(255) NOT NULL,
  quantity       DECIMAL(12,2) NOT NULL DEFAULT 1,
  unit           VARCHAR(40)  NULL,
  unit_price_ugx BIGINT       NOT NULL,
  line_total_ugx BIGINT       NOT NULL,
  sort_order     SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_offer_items_offer (offer_id, sort_order),
  CONSTRAINT fk_offer_items_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
  CONSTRAINT chk_offer_items_amounts CHECK (unit_price_ugx >= 0 AND line_total_ugx >= 0 AND quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clarifications (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  requirement_id    BIGINT UNSIGNED NOT NULL,
  offer_id          BIGINT UNSIGNED NULL,
  asked_by_user_id  BIGINT UNSIGNED NOT NULL,
  question          VARCHAR(1000) NOT NULL,
  answer            VARCHAR(1000) NULL,
  answered_by_user_id BIGINT UNSIGNED NULL,
  answered_at       DATETIME   NULL,
  is_public         TINYINT(1) NOT NULL DEFAULT 1,
  created_at        DATETIME   NULL,
  PRIMARY KEY (id),
  KEY idx_clar_requirement (requirement_id),
  CONSTRAINT fk_clar_requirement FOREIGN KEY (requirement_id)      REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT fk_clar_offer       FOREIGN KEY (offer_id)            REFERENCES offers(id)       ON DELETE CASCADE,
  CONSTRAINT fk_clar_asker       FOREIGN KEY (asked_by_user_id)    REFERENCES users(id)        ON DELETE CASCADE,
  CONSTRAINT fk_clar_answerer    FOREIGN KEY (answered_by_user_id) REFERENCES users(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shortlist_entries (
  requirement_id BIGINT UNSIGNED NOT NULL,
  offer_id       BIGINT UNSIGNED NOT NULL,
  rank_order     SMALLINT     NOT NULL DEFAULT 0,
  note           VARCHAR(500) NULL,
  created_at     DATETIME     NULL,
  PRIMARY KEY (requirement_id, offer_id),
  KEY idx_shortlist_offer (offer_id),
  CONSTRAINT fk_shortlist_requirement FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT fk_shortlist_offer       FOREIGN KEY (offer_id)       REFERENCES offers(id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- BOOKINGS  (light shared workspace — no payment gates, no adjudication)
-- =====================================================================

CREATE TABLE bookings (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id              CHAR(26) NOT NULL,
  requirement_id         BIGINT UNSIGNED NOT NULL,
  offer_id               BIGINT UNSIGNED NOT NULL,
  provider_id            BIGINT UNSIGNED NOT NULL,
  event_id               BIGINT UNSIGNED NOT NULL,
  agreed_total_ugx       BIGINT      NOT NULL,
  original_total_ugx     BIGINT      NOT NULL,
  status                 VARCHAR(20) NOT NULL DEFAULT 'confirmed',
  contacts_released_at   DATETIME    NULL,
  organiser_completed_at DATETIME    NULL,
  provider_completed_at  DATETIME    NULL,
  cancelled_at           DATETIME    NULL,
  cancelled_by_side      VARCHAR(12) NULL,
  cancellation_note      VARCHAR(500) NULL,
  created_at             DATETIME    NULL,
  updated_at             DATETIME    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bookings_public (public_id),
  UNIQUE KEY uq_bookings_requirement (requirement_id),
  KEY idx_bookings_provider (provider_id, status),
  KEY idx_bookings_event (event_id),
  CONSTRAINT fk_bookings_requirement FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_offer       FOREIGN KEY (offer_id)       REFERENCES offers(id)       ON DELETE RESTRICT,
  CONSTRAINT fk_bookings_provider    FOREIGN KEY (provider_id)    REFERENCES providers(id)    ON DELETE RESTRICT,
  CONSTRAINT fk_bookings_event       FOREIGN KEY (event_id)       REFERENCES events(id)       ON DELETE CASCADE,
  CONSTRAINT chk_bookings_status CHECK (status IN ('confirmed','in_progress','completed','closed','cancelled')),
  CONSTRAINT chk_bookings_side   CHECK (cancelled_by_side IS NULL OR cancelled_by_side IN ('organiser','provider')),
  CONSTRAINT chk_bookings_totals CHECK (agreed_total_ugx >= 0 AND original_total_ugx >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_tasks (
  id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id           BIGINT UNSIGNED NOT NULL,
  title                VARCHAR(200) NOT NULL,
  description          VARCHAR(1000) NULL,
  owner_side           VARCHAR(12)  NOT NULL,
  assigned_user_id     BIGINT UNSIGNED NULL,
  due_at               DATETIME     NULL,
  status               VARCHAR(15)  NOT NULL DEFAULT 'open',
  completed_at         DATETIME     NULL,
  completed_by_user_id BIGINT UNSIGNED NULL,
  sort_order           SMALLINT     NOT NULL DEFAULT 0,
  created_at           DATETIME     NULL,
  updated_at           DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_btasks_booking (booking_id, sort_order),
  KEY idx_btasks_due (due_at, status),
  CONSTRAINT fk_btasks_booking   FOREIGN KEY (booking_id)           REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_btasks_assignee  FOREIGN KEY (assigned_user_id)     REFERENCES users(id)    ON DELETE SET NULL,
  CONSTRAINT fk_btasks_completer FOREIGN KEY (completed_by_user_id) REFERENCES users(id)    ON DELETE SET NULL,
  CONSTRAINT chk_btasks_side   CHECK (owner_side IN ('organiser','provider','both')),
  CONSTRAINT chk_btasks_status CHECK (status IN ('open','done','cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_files (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id          BIGINT UNSIGNED NOT NULL,
  uploaded_by_user_id BIGINT UNSIGNED NOT NULL,
  label               VARCHAR(150) NOT NULL,
  path                VARCHAR(255) NOT NULL,
  mime                VARCHAR(100) NULL,
  size_bytes          BIGINT UNSIGNED NULL,
  created_at          DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_bfiles_booking (booking_id),
  CONSTRAINT fk_bfiles_booking  FOREIGN KEY (booking_id)          REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bfiles_uploader FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only log of what the parties say they agreed. No approval state
-- machine: Event Go records, it does not enforce. Architecture §5.4.
CREATE TABLE booking_amendments (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id         BIGINT UNSIGNED NOT NULL,
  changed_by_user_id BIGINT UNSIGNED NOT NULL,
  previous_total_ugx BIGINT       NOT NULL,
  new_total_ugx      BIGINT       NOT NULL,
  note               VARCHAR(500) NULL,
  created_at         DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_bamend_booking (booking_id, created_at),
  CONSTRAINT fk_bamend_booking FOREIGN KEY (booking_id)         REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bamend_user    FOREIGN KEY (changed_by_user_id) REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT chk_bamend_totals CHECK (previous_total_ugx >= 0 AND new_total_ugx >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ATTRIBUTION  (the revenue mechanism — proves provider ROI, §8.3)
-- =====================================================================

CREATE TABLE provider_impressions (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id    BIGINT UNSIGNED NOT NULL,
  context        VARCHAR(20) NOT NULL,
  requirement_id BIGINT UNSIGNED NULL,
  viewer_user_id BIGINT UNSIGNED NULL,
  was_featured   TINYINT(1) NOT NULL DEFAULT 0,
  created_at     DATETIME   NOT NULL,
  PRIMARY KEY (id),
  KEY idx_impr_provider_date (provider_id, created_at),
  CONSTRAINT fk_impr_provider    FOREIGN KEY (provider_id)    REFERENCES providers(id)    ON DELETE CASCADE,
  CONSTRAINT fk_impr_requirement FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE SET NULL,
  CONSTRAINT fk_impr_viewer      FOREIGN KEY (viewer_user_id) REFERENCES users(id)        ON DELETE SET NULL,
  CONSTRAINT chk_impr_context CHECK (context IN ('search','category','profile','opportunity','featured'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Who is connected to whom, and who is working with whom.
--
-- Observing the relationship graph is the product; governing the relationship
-- is not. Event Go records that these two parties connected and roughly what
-- came of it. It does not touch their terms.
--
-- Partly derivable from bookings, deliberately not left that way:
--   * a connection can exist with NO booking (they talked, then went offline).
--     That case is invisible in bookings and is the best leakage signal we get.
--   * the "my network" screens need this without aggregating bookings each time.
CREATE TABLE connections (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  organiser_user_id  BIGINT UNSIGNED NOT NULL,
  provider_id        BIGINT UNSIGNED NOT NULL,
  origin             VARCHAR(25) NOT NULL,
  first_event_id     BIGINT UNSIGNED NULL,
  first_connected_at DATETIME    NOT NULL,
  last_interaction_at DATETIME   NULL,
  bookings_count     INT UNSIGNED NOT NULL DEFAULT 0,
  bookings_value_ugx BIGINT      NOT NULL DEFAULT 0,
  status             VARCHAR(15) NOT NULL DEFAULT 'contacted',
  created_at         DATETIME    NULL,
  updated_at         DATETIME    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_connection (organiser_user_id, provider_id),
  KEY idx_conn_provider (provider_id, status, last_interaction_at),
  KEY idx_conn_organiser (organiser_user_id, status),
  CONSTRAINT fk_conn_organiser FOREIGN KEY (organiser_user_id) REFERENCES users(id)     ON DELETE CASCADE,
  CONSTRAINT fk_conn_provider  FOREIGN KEY (provider_id)       REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_conn_event     FOREIGN KEY (first_event_id)    REFERENCES events(id)    ON DELETE SET NULL,
  CONSTRAINT chk_conn_origin CHECK (origin IN ('opportunity_match','direct_invitation','search','featured')),
  -- contacted = they spoke, no booking yet.  working = at least one live booking.
  -- past = booked before, nothing active now.
  CONSTRAINT chk_conn_status CHECK (status IN ('contacted','working','past')),
  CONSTRAINT chk_conn_totals CHECK (bookings_value_ugx >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_leads (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id    BIGINT UNSIGNED NOT NULL,
  requirement_id BIGINT UNSIGNED NOT NULL,
  source         VARCHAR(25) NOT NULL,
  notified_at    DATETIME    NULL,
  viewed_at      DATETIME    NULL,
  offered_at     DATETIME    NULL,
  outcome        VARCHAR(15) NOT NULL DEFAULT 'pending',
  outcome_at     DATETIME    NULL,
  value_ugx      BIGINT      NULL,
  created_at     DATETIME    NULL,
  updated_at     DATETIME    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lead (provider_id, requirement_id),
  KEY idx_leads_provider_outcome (provider_id, outcome, created_at),
  CONSTRAINT fk_leads_provider    FOREIGN KEY (provider_id)    REFERENCES providers(id)    ON DELETE CASCADE,
  CONSTRAINT fk_leads_requirement FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE,
  CONSTRAINT chk_leads_source  CHECK (source IN ('opportunity_match','direct_invitation','search','featured')),
  CONSTRAINT chk_leads_outcome CHECK (outcome IN ('pending','no_bid','lost','won','expired'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- BILLING  (prepaid packages over mobile money — §8.2)
-- =====================================================================

CREATE TABLE plans (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code          VARCHAR(40)  NOT NULL,
  name          VARCHAR(80)  NOT NULL,
  audience      VARCHAR(15)  NOT NULL DEFAULT 'provider',
  price_ugx     BIGINT       NOT NULL,
  duration_days SMALLINT UNSIGNED NOT NULL,
  entitlements  JSON         NOT NULL,
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order    SMALLINT     NOT NULL DEFAULT 0,
  created_at    DATETIME     NULL,
  updated_at    DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plans_code (code),
  CONSTRAINT chk_plans_audience CHECK (audience IN ('provider','organiser')),
  CONSTRAINT chk_plans_price    CHECK (price_ugx >= 0 AND duration_days > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  subscriber_type VARCHAR(20) NOT NULL,
  subscriber_id   BIGINT UNSIGNED NOT NULL,
  plan_id         BIGINT UNSIGNED NOT NULL,
  starts_at       DATETIME    NOT NULL,
  expires_at      DATETIME    NOT NULL,
  status          VARCHAR(15) NOT NULL DEFAULT 'pending',
  created_at      DATETIME    NULL,
  updated_at      DATETIME    NULL,
  PRIMARY KEY (id),
  KEY idx_subs_subscriber (subscriber_type, subscriber_id, status),
  KEY idx_subs_expiry (expires_at, status),
  CONSTRAINT fk_subs_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
  CONSTRAINT chk_subs_status CHECK (status IN ('pending','active','expired','cancelled')),
  CONSTRAINT chk_subs_dates  CHECK (expires_at > starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE billing_payments (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  subscription_id BIGINT UNSIGNED NULL,
  amount_ugx      BIGINT       NOT NULL,
  channel         VARCHAR(20)  NOT NULL,
  gateway         VARCHAR(30)  NOT NULL,
  gateway_ref     VARCHAR(120) NOT NULL,
  payer_msisdn    VARCHAR(20)  NULL,
  payer_name      VARCHAR(150) NULL,
  status          VARCHAR(15)  NOT NULL DEFAULT 'pending',
  raw_callback    JSON         NULL,
  initiated_at    DATETIME     NULL,
  paid_at         DATETIME     NULL,
  created_at      DATETIME     NULL,
  updated_at      DATETIME     NULL,
  PRIMARY KEY (id),
  -- Idempotency: gateways resend webhooks. Architecture §9.3.
  UNIQUE KEY uq_billing_gateway_ref (gateway, gateway_ref),
  KEY idx_billing_subscription (subscription_id),
  KEY idx_billing_status (status, created_at),
  CONSTRAINT fk_billing_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
  CONSTRAINT chk_billing_status  CHECK (status IN ('pending','settled','failed','refunded')),
  CONSTRAINT chk_billing_channel CHECK (channel IN ('mtn_momo','airtel_money','card','bank','cash','manual')),
  CONSTRAINT chk_billing_amount  CHECK (amount_ugx >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE featured_placements (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id         BIGINT UNSIGNED NOT NULL,
  service_category_id BIGINT UNSIGNED NULL,
  district_id         BIGINT UNSIGNED NULL,
  starts_at           DATETIME NOT NULL,
  ends_at             DATETIME NOT NULL,
  price_ugx           BIGINT   NOT NULL,
  impressions         INT UNSIGNED NOT NULL DEFAULT 0,
  clicks              INT UNSIGNED NOT NULL DEFAULT 0,
  created_at          DATETIME NULL,
  updated_at          DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_featured_window (starts_at, ends_at),
  KEY idx_featured_slot (service_category_id, district_id, starts_at, ends_at),
  CONSTRAINT fk_featured_provider FOREIGN KEY (provider_id)         REFERENCES providers(id)          ON DELETE CASCADE,
  CONSTRAINT fk_featured_category FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE CASCADE,
  CONSTRAINT fk_featured_district FOREIGN KEY (district_id)         REFERENCES districts(id)          ON DELETE CASCADE,
  CONSTRAINT chk_featured_window CHECK (ends_at > starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- MESSAGING / REPUTATION / NOTIFICATIONS
-- =====================================================================

CREATE TABLE threads (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  subject_type VARCHAR(30) NOT NULL,
  subject_id   BIGINT UNSIGNED NOT NULL,
  created_at   DATETIME NULL,
  updated_at   DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_thread_subject (subject_type, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE thread_participants (
  thread_id    BIGINT UNSIGNED NOT NULL,
  user_id      BIGINT UNSIGNED NOT NULL,
  role         VARCHAR(15) NOT NULL DEFAULT 'member',
  last_read_at DATETIME NULL,
  PRIMARY KEY (thread_id, user_id),
  KEY idx_tp_user (user_id),
  CONSTRAINT fk_tp_thread FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_tp_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_id      BIGINT UNSIGNED NOT NULL,
  sender_user_id BIGINT UNSIGNED NOT NULL,
  body           TEXT       NOT NULL,
  -- Flag, do not block: false positives cost more than leakage. §11.2.
  contains_contact_info TINYINT(1) NOT NULL DEFAULT 0,
  created_at     DATETIME   NOT NULL,
  PRIMARY KEY (id),
  KEY idx_messages_thread (thread_id, created_at),
  CONSTRAINT fk_messages_thread FOREIGN KEY (thread_id)      REFERENCES threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_messages_sender FOREIGN KEY (sender_user_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_attachments (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  message_id BIGINT UNSIGNED NOT NULL,
  path       VARCHAR(255) NOT NULL,
  mime       VARCHAR(100) NULL,
  size_bytes BIGINT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY idx_msg_att_message (message_id),
  CONSTRAINT fk_msg_att_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id    BIGINT UNSIGNED NOT NULL,
  direction     VARCHAR(25) NOT NULL,
  author_user_id BIGINT UNSIGNED NOT NULL,
  rating        TINYINT UNSIGNED NOT NULL,
  punctuality   TINYINT UNSIGNED NULL,
  quality       TINYINT UNSIGNED NULL,
  communication TINYINT UNSIGNED NULL,
  value_rating  TINYINT UNSIGNED NULL,
  comment       VARCHAR(2000) NULL,
  is_published  TINYINT(1) NOT NULL DEFAULT 0,
  published_at  DATETIME   NULL,
  created_at    DATETIME   NULL,
  updated_at    DATETIME   NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_review_direction (booking_id, direction),
  KEY idx_reviews_published (is_published, published_at),
  CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id)     REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_author  FOREIGN KEY (author_user_id) REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT chk_reviews_direction CHECK (direction IN ('organiser_to_provider','provider_to_organiser')),
  CONSTRAINT chk_reviews_rating    CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    BIGINT UNSIGNED NOT NULL,
  type       VARCHAR(60) NOT NULL,
  payload    JSON        NOT NULL,
  read_at    DATETIME    NULL,
  created_at DATETIME    NOT NULL,
  PRIMARY KEY (id),
  KEY idx_notif_user_unread (user_id, read_at, created_at),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notification_deliveries (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  notification_id BIGINT UNSIGNED NOT NULL,
  channel         VARCHAR(15) NOT NULL,
  status          VARCHAR(15) NOT NULL DEFAULT 'queued',
  provider_ref    VARCHAR(120) NULL,
  idempotency_key VARCHAR(120) NOT NULL,
  cost_ugx        BIGINT      NULL,
  sent_at         DATETIME    NULL,
  failed_reason   VARCHAR(255) NULL,
  created_at      DATETIME    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_delivery_idem (idempotency_key),
  KEY idx_delivery_notification (notification_id),
  KEY idx_delivery_channel_status (channel, status),
  CONSTRAINT fk_delivery_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  CONSTRAINT chk_delivery_channel CHECK (channel IN ('inapp','email','push','sms')),
  CONSTRAINT chk_delivery_status  CHECK (status IN ('queued','sent','delivered','failed'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DEFERRED FOREIGN KEYS  (circular references resolved after creation)
-- =====================================================================

ALTER TABLE requirements
  ADD CONSTRAINT fk_req_selected_offer FOREIGN KEY (selected_offer_id) REFERENCES offers(id)   ON DELETE SET NULL,
  ADD CONSTRAINT fk_req_booking        FOREIGN KEY (booking_id)        REFERENCES bookings(id) ON DELETE SET NULL;

ALTER TABLE providers
  ADD CONSTRAINT fk_providers_plan     FOREIGN KEY (plan_id)              REFERENCES plans(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_providers_referrer FOREIGN KEY (referred_by_staff_id) REFERENCES users(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- Event Go — reference seed data
-- Run after 01-schema.sql
--
-- TWO WARNINGS, both important:
--
-- 1. DISTRICTS are a starter set covering the Kampala pilot area plus
--    major regional centres. Uganda creates and splits districts
--    regularly. Before national launch, reconcile this list against the
--    official UBOS district list and set effective_from where relevant.
--    Do not treat this as authoritative.
--
-- 2. BENCHMARK UNIT COSTS are PLACEHOLDERS for development only. They
--    are not researched Kampala market rates. Architecture §6.2 and open
--    decision 4 both say these must come from interviewing 10-15
--    experienced Kampala planners. They exist here purely so the budget
--    engine has something to compute with while we build. Every value
--    marked -- PLACEHOLDER must be replaced before any organiser sees a
--    generated budget.
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- DISTRICTS
-- =====================================================================

INSERT INTO districts (name, region) VALUES
-- Central
('Kampala','central'),('Wakiso','central'),('Mukono','central'),('Mpigi','central'),
('Butambala','central'),('Gomba','central'),('Kalangala','central'),('Kalungu','central'),
('Kayunga','central'),('Kiboga','central'),('Kyankwanzi','central'),('Luweero','central'),
('Lwengo','central'),('Lyantonde','central'),('Masaka','central'),('Mityana','central'),
('Mubende','central'),('Nakaseke','central'),('Nakasongola','central'),('Rakai','central'),
('Sembabule','central'),('Buikwe','central'),('Buvuma','central'),('Bukomansimbi','central'),
('Kyotera','central'),('Kassanda','central'),
-- Eastern
('Jinja','eastern'),('Iganga','eastern'),('Kamuli','eastern'),('Mbale','eastern'),
('Tororo','eastern'),('Busia','eastern'),('Bugiri','eastern'),('Mayuge','eastern'),
('Soroti','eastern'),('Kumi','eastern'),('Pallisa','eastern'),('Sironko','eastern'),
('Kapchorwa','eastern'),('Bukwo','eastern'),('Budaka','eastern'),('Butaleja','eastern'),
('Namutumba','eastern'),('Kaliro','eastern'),('Buyende','eastern'),('Luuka','eastern'),
('Namayingo','eastern'),('Bulambuli','eastern'),('Kween','eastern'),('Serere','eastern'),
('Ngora','eastern'),('Bukedea','eastern'),('Amuria','eastern'),('Katakwi','eastern'),
('Kaberamaido','eastern'),('Manafwa','eastern'),('Bududa','eastern'),('Butebo','eastern'),
('Kibuku','eastern'),('Namisindwa','eastern'),('Bugweri','eastern'),('Kapelebyong','eastern'),
-- Northern
('Gulu','northern'),('Lira','northern'),('Kitgum','northern'),('Pader','northern'),
('Apac','northern'),('Arua','northern'),('Nebbi','northern'),('Moyo','northern'),
('Adjumani','northern'),('Yumbe','northern'),('Koboko','northern'),('Maracha','northern'),
('Zombo','northern'),('Amuru','northern'),('Nwoya','northern'),('Agago','northern'),
('Lamwo','northern'),('Otuke','northern'),('Alebtong','northern'),('Dokolo','northern'),
('Amolatar','northern'),('Oyam','northern'),('Kole','northern'),('Kaabong','northern'),
('Kotido','northern'),('Abim','northern'),('Moroto','northern'),('Napak','northern'),
('Nakapiripirit','northern'),('Amudat','northern'),('Pakwach','northern'),('Obongi','northern'),
('Madi-Okollo','northern'),('Kwania','northern'),('Terego','northern'),('Karenga','northern'),
('Nabilatuk','northern'),
-- Western
('Mbarara','western'),('Bushenyi','western'),('Ntungamo','western'),('Kabale','western'),
('Kisoro','western'),('Rukungiri','western'),('Kanungu','western'),('Kasese','western'),
('Kabarole','western'),('Kamwenge','western'),('Kyenjojo','western'),('Hoima','western'),
('Masindi','western'),('Buliisa','western'),('Kiryandongo','western'),('Kibaale','western'),
('Kyegegwa','western'),('Ibanda','western'),('Isingiro','western'),('Kiruhura','western'),
('Mitooma','western'),('Rubirizi','western'),('Sheema','western'),('Buhweju','western'),
('Rukiga','western'),('Rubanda','western'),('Bunyangabu','western'),('Kagadi','western'),
('Kakumiro','western'),('Kikuube','western'),('Rwampara','western'),('Kazo','western'),
('Kitagwenda','western'),('Ntoroko','western');

-- =====================================================================
-- SERVICE CATEGORIES  (two levels, from the concept brief §7)
-- =====================================================================

INSERT INTO service_categories (id, parent_id, slug, name, unit_label, requires_capacity, sort_order) VALUES
(100, NULL, 'audio-entertainment', 'Audio & Entertainment', NULL, 0, 1),
(200, NULL, 'food-beverages',      'Food & Beverages',      NULL, 1, 2),
(300, NULL, 'venue-infrastructure','Venue & Infrastructure',NULL, 1, 3),
(400, NULL, 'decoration',          'Decoration & Experience',NULL,0, 4),
(500, NULL, 'media-technology',    'Media & Technology',    NULL, 0, 5),
(600, NULL, 'guest-management',    'Guest Management',      NULL, 1, 6),
(700, NULL, 'logistics',           'Logistics',             NULL, 0, 7);

INSERT INTO service_categories (parent_id, slug, name, unit_label, requires_capacity, sort_order) VALUES
-- Audio & Entertainment
(100,'public-address','Public Address Systems','per day',0,1),
(100,'dj','DJs','per event',0,2),
(100,'live-band','Live Bands','per event',0,3),
(100,'artists','Artists & Performers','per performance',0,4),
(100,'mc','Master of Ceremony','per event',0,5),
(100,'choreography','Dance & Choreography','per event',0,6),
-- Food & Beverages
(200,'catering','Catering','per plate',1,1),
(200,'food-supply','Food Suppliers','per order',0,2),
(200,'drinks','Drinks & Beverages','per crate',0,3),
(200,'cakes','Cakes & Desserts','per cake',0,4),
-- Venue & Infrastructure
(300,'venue','Event Venues','per day',1,1),
(300,'tents','Tents & Marquees','per tent',1,2),
(300,'chairs-tables','Chairs & Tables','per unit',1,3),
(300,'staging','Staging','per event',0,4),
(300,'led-screens','LED Screens','per screen',0,5),
-- Decoration
(400,'event-decor','Event Decoration','per event',0,1),
(400,'floral','Floral Design','per event',0,2),
(400,'lighting','Lighting','per event',0,3),
(400,'styling','Event Styling & Theming','per event',0,4),
-- Media & Technology
(500,'photography','Photography','per event',0,1),
(500,'videography','Videography','per event',0,2),
(500,'live-streaming','Live Streaming','per event',0,3),
(500,'social-coverage','Social Media Coverage','per event',0,4),
-- Guest Management
(600,'ushers','Ushers','per usher',1,1),
(600,'security','Security','per guard',1,2),
(600,'registration','Registration & Guest Management','per event',1,3),
(600,'ticketing','Ticketing','per event',0,4),
-- Logistics
(700,'transport','Transportation','per vehicle',0,1),
(700,'equipment-transport','Equipment Transportation','per trip',0,2),
(700,'car-hire','Car Hire','per vehicle per day',0,3),
(700,'toilets','Portable Toilets','per unit',1,4);

-- =====================================================================
-- EVENT TYPES
-- =====================================================================

INSERT INTO event_types (id, slug, name, sort_order) VALUES
(1,'wedding','Wedding',1),
(2,'introduction','Introduction Ceremony',2),
(3,'birthday','Birthday Party',3),
(4,'graduation','Graduation Party',4),
(5,'corporate-conference','Conference or Seminar',5),
(6,'product-launch','Product Launch',6),
(7,'concert','Concert or Festival',7),
(8,'church-conference','Church Conference',8),
(9,'funeral','Funeral or Memorial',9),
(10,'custom','Other / Custom Event',99);

-- =====================================================================
-- SCOPE QUESTIONS  (wedding worked through fully; others are starters)
-- =====================================================================

INSERT INTO scope_questions (event_type_id, `key`, label, input_type, options, is_required, sort_order, help_text) VALUES
(1,'guests','How many guests are you expecting?','number',NULL,1,1,'A rough number is fine — you can change it later.'),
(1,'outdoor','Will the event be outdoors?','bool',NULL,1,2,'Outdoor events need tents and portable toilets.'),
(1,'venue_needed','Do you still need to find a venue?','bool',NULL,1,3,NULL),
(1,'vips','How many VIP guests need special seating?','number',NULL,0,4,NULL),
(1,'catering_style','What kind of catering?','select','["Buffet","Plated service","Cocktail","Not needed"]',1,5,NULL),
(1,'entertainment','What entertainment do you want?','multiselect','["DJ","Live band","MC","Traditional dancers","None"]',0,6,NULL),
(1,'photography','Photography and video?','multiselect','["Photography","Videography","Live streaming","None"]',0,7,NULL),
(1,'transport_needed','Do you need transport for the party?','bool',NULL,0,8,NULL),

(2,'guests','How many guests are you expecting?','number',NULL,1,1,NULL),
(2,'outdoor','Will the ceremony be outdoors?','bool',NULL,1,2,NULL),
(2,'catering_style','What kind of catering?','select','["Buffet","Plated service","Not needed"]',1,3,NULL),
(2,'gifts_display','Do you need a gift display setup?','bool',NULL,0,4,NULL),

(7,'audience','Expected audience size','number',NULL,1,1,NULL),
(7,'outdoor','Is the venue outdoors?','bool',NULL,1,2,NULL),
(7,'stage_required','Do you need a stage built?','bool',NULL,1,3,NULL),
(7,'ticketed','Is this a ticketed event?','bool',NULL,1,4,NULL),
(7,'broadcast','Do you need broadcast or streaming?','bool',NULL,0,5,NULL),

(5,'delegates','How many delegates?','number',NULL,1,1,NULL),
(5,'days','How many days?','number',NULL,1,2,NULL),
(5,'meals_included','Are meals included?','bool',NULL,1,3,NULL),
(5,'av_required','Do you need AV and projection?','bool',NULL,1,4,NULL);

-- =====================================================================
-- REQUIREMENT TEMPLATES
--
-- quantity_expression is evaluated by symfony/expression-language against
-- the scope answers, with a whitelisted variable set and no function
-- access beyond ceil/floor/round/min/max. Architecture §6.2.
--
-- ALL benchmark_unit_cost_ugx VALUES BELOW ARE PLACEHOLDERS.
-- =====================================================================

INSERT INTO requirement_templates
  (event_type_id, service_category_id, condition_expr, quantity_expression, benchmark_unit_cost_ugx, default_title, priority, sort_order)
SELECT 1, sc.id, t.cond, t.qty, t.cost, t.title, t.prio, t.ord
FROM (
  SELECT 'catering'      AS slug, 'catering_style != "Not needed"' AS cond, 'guests'              AS qty,   35000 AS cost, 'Catering'                 AS title, 'essential' AS prio, 1 AS ord UNION ALL
  SELECT 'venue',              'venue_needed == true',              '1',                                  3000000,      'Venue',                          'essential', 2 UNION ALL
  SELECT 'chairs-tables',      NULL,                                'ceil(guests * 1.05)',                   3000,      'Chairs and tables',              'essential', 3 UNION ALL
  SELECT 'tents',              'outdoor == true',                   'ceil(guests / 150)',                 450000,      'Tents',                          'essential', 4 UNION ALL
  SELECT 'toilets',            'outdoor == true',                   'ceil(guests / 75)',                  120000,      'Portable toilets',               'important', 5 UNION ALL
  SELECT 'event-decor',        NULL,                                '1',                                 2000000,      'Event decoration',               'essential', 6 UNION ALL
  SELECT 'public-address',     NULL,                                '1',                                  600000,      'Public address system',          'essential', 7 UNION ALL
  SELECT 'mc',                 NULL,                                '1',                                  500000,      'Master of Ceremony',             'important', 8 UNION ALL
  SELECT 'photography',        NULL,                                '1',                                 1200000,      'Photography',                    'important', 9 UNION ALL
  SELECT 'videography',        NULL,                                '1',                                 1500000,      'Videography',                    'important',10 UNION ALL
  SELECT 'ushers',             NULL,                                'ceil(guests / 50)',                   50000,      'Ushers',                         'important',11 UNION ALL
  SELECT 'cakes',              NULL,                                '1',                                  700000,      'Wedding cake',                   'important',12 UNION ALL
  SELECT 'drinks',             NULL,                                'ceil(guests / 12)',                   80000,      'Drinks',                         'important',13 UNION ALL
  SELECT 'dj',                 NULL,                                '1',                                  800000,      'DJ',                             'optional', 14 UNION ALL
  SELECT 'lighting',           'outdoor == true',                   '1',                                  700000,      'Lighting',                       'optional', 15 UNION ALL
  SELECT 'security',           NULL,                                'ceil(guests / 100)',                  60000,      'Security',                       'optional', 16 UNION ALL
  SELECT 'transport',          'transport_needed == true',          '2',                                  400000,      'Transport for the bridal party', 'optional', 17
) AS t
JOIN service_categories sc ON sc.slug = t.slug;

INSERT INTO requirement_templates
  (event_type_id, service_category_id, condition_expr, quantity_expression, benchmark_unit_cost_ugx, default_title, priority, sort_order)
SELECT 7, sc.id, t.cond, t.qty, t.cost, t.title, t.prio, t.ord
FROM (
  SELECT 'venue'         AS slug, 'true'               AS cond, '1'                    AS qty,  5000000 AS cost, 'Venue'                AS title, 'essential' AS prio, 1 AS ord UNION ALL
  SELECT 'public-address',       'true',                        '1',                            2500000,        'Sound system',                  'essential', 2 UNION ALL
  SELECT 'staging',              'stage_required == true',      '1',                            3500000,        'Stage',                         'essential', 3 UNION ALL
  SELECT 'lighting',             'true',                        '1',                            2000000,        'Stage lighting',                'essential', 4 UNION ALL
  SELECT 'security',             'true',                        'ceil(audience / 100)',           60000,        'Security',                      'essential', 5 UNION ALL
  SELECT 'led-screens',          'audience > 1000',             '2',                            1800000,        'LED screens',                   'important', 6 UNION ALL
  SELECT 'artists',              'true',                        '1',                            5000000,        'Artist lineup',                 'essential', 7 UNION ALL
  SELECT 'ticketing',            'ticketed == true',            '1',                             800000,        'Ticketing',                     'important', 8 UNION ALL
  SELECT 'live-streaming',       'broadcast == true',           '1',                            2500000,        'Live streaming',                'optional',  9 UNION ALL
  SELECT 'toilets',              'outdoor == true',             'ceil(audience / 75)',           120000,        'Portable toilets',              'essential',10 UNION ALL
  SELECT 'ushers',               'true',                        'ceil(audience / 100)',           50000,        'Ushers',                        'important',11
) AS t
JOIN service_categories sc ON sc.slug = t.slug;

-- =====================================================================
-- BILLING PLANS  (prices are launch hypotheses, not researched — §16 dec. 1)
-- =====================================================================

INSERT INTO plans (code, name, audience, price_ugx, duration_days, entitlements, sort_order) VALUES
('free','Free','provider',0,36500,
 '{"max_offers_per_month":5,"analytics":false,"portfolio_slots":6,"search_boost":1.0,"featured_eligible":false}',1),
('pro-30','Professional — 1 month','provider',60000,30,
 '{"max_offers_per_month":null,"analytics":true,"portfolio_slots":30,"search_boost":1.2,"featured_eligible":true}',2),
('pro-90','Professional — 3 months','provider',150000,90,
 '{"max_offers_per_month":null,"analytics":true,"portfolio_slots":30,"search_boost":1.2,"featured_eligible":true}',3),
('pro-365','Professional — 12 months','provider',430000,365,
 '{"max_offers_per_month":null,"analytics":true,"portfolio_slots":50,"search_boost":1.2,"featured_eligible":true}',4);

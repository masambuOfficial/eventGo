# Event Go — brand

Working spec. Enough to build the UI against; not yet a full brand book.

---

## 1. The constraint that decided the palette

Uganda held a general election in January 2026. Political colour associations
are unusually live, and two of them rule out the colours event brands normally
reach for:

- **Saturated yellow** reads as NRM. It is also MTN's brand colour.
- **Saturated red** reads as NUP. It is also Airtel's.
- Plain corporate blue is politically safer but generic and cold for a product
  people plan weddings with.

Deep green avoids all of it, reads as growth and trust, doubles naturally as the
"verified" colour, and nobody in this category is using it.

**Do not "brighten" the palette by introducing yellow or red as a primary.**
This is not an aesthetic preference. It is the reason the palette exists.

---

## 2. Palette

### Ceremony green — primary

| Token | Hex | Use |
|---|---|---|
| `green-50`  | `#F0F8F5` | Page tint, hover fills |
| `green-100` | `#D3EBE3` | Badges, selected rows, empty grid cells |
| `green-400` | `#2E9C82` | Charts, secondary fills |
| `green-600` | `#14705C` | **Primary action, links, body-safe text** |
| `green-700` | `#0F5A4B` | Hover on primary |
| `green-900` | `#08302A` | Text on green fills, dark surfaces |

### Marquee amber — accent

| Token | Hex | Use |
|---|---|---|
| `amber-100` | `#FAE9CE` | Celebration tint, highlight rows |
| `amber-500` | `#E0952F` | **Fill only — never text** |
| `amber-700` | `#A8641A` | Amber that must carry words |

### Warm neutral

| Token | Hex | Use |
|---|---|---|
| `surface` | `#FBFAF7` | Page background — warm, not cold grey |
| `line`    | `#D5DCD8` | Borders, dividers |
| `slate`   | `#55635D` | Secondary text |
| `ink`     | `#16211D` | Primary text |

### Contrast — verified, do not guess

- `#14705C` on white = **5.9:1** — passes AA for body text and buttons
- `#A8641A` on white = **4.6:1** — passes AA
- `#E0952F` on white = **2.5:1** — **fails.** Fill colour only. If amber must
  carry text, use `amber-700`.

Neutral greys are deliberately warm. Cold grey UI reads as institutional, and
first-time users in this market read institutional as impersonal.

---

## 3. The two-temperature rule

Two audiences with opposite emotional needs. An organiser planning her wedding
wants warmth; a tent supplier checking leads wants a business tool and finds
confetti unserious. Most event platforms pick one and lose the other.

One palette, two weightings:

| | Organiser surfaces | Provider surfaces |
|---|---|---|
| Dominant | Amber accents, generous white space | Green, dense, minimal decoration |
| Imagery | Photography-forward | Almost none |
| Feel | Celebration | Instrument panel |

Same components, same tokens. Only the weighting changes, so it still reads as
one product.

---

## 4. Logo

**Direction: the letter `t` in "event" becomes a flagpole flying a pennant.**

The `t` is the only letter in the name already shaped like a pole with a
horizontal element, so the flag is not a pun that needs explaining. The pennant
points right, into `GO`, giving the word forward motion.

Construction:

- Stem rises above normal ascender height — this is deliberate and is what makes
  the mark distinctive
- Crossbar sits at x-height, normal weight
- Pennant is a right-pointing triangle from the upper stem, in `amber-500`
- `even` and the underscore in `ink`; `GO` in `green-600`, medium weight
- The pennant is the **only** amber in the logo. One warm accent reads as
  celebration; two reads as a mango brand.

**Icon:** the pennant alone — no letter — in a rounded square. At 16px a flagged
`t` turns to mush; a bare pennant survives.

**Rules**

- The logotype is a **drawn SVG, frozen as outlines**. Never set live in a
  webfont. Change the font and the `t` changes width and the flag detaches.
- Clear space on all sides = cap height of the `G`.
- Minimum width 90px for the full lockup; below that, switch to the icon.
- The icon always sits in a rounded square, never floating — Android home
  screens crop unpredictably otherwise.
- Single-colour version: flag goes solid `ink`. Tested, still reads.

---

## 5. On the underscore

`event_GO` is how the project is written internally. For the product it is a
liability, in three specific ways:

- Search engines treat hyphens as word separators but **not** underscores, so
  `event_go` is worse for SEO than `eventgo`
- Nobody can say it aloud, so word of mouth collapses to "Event Go" anyway
- It reads as a filename — fine on a provider dashboard, wrong for a wedding

**Decision:** the name is **Event Go**, the domain is `eventgo.ug`, and that is
how it is spoken and written in all copy.

Keep the stroke in the logotype only, as the ground line the flagpole plants
into — and reuse that same stroke in the product as the planning progress bar
(`events.planning_progress`). The underscore stops being punctuation nobody can
pronounce and becomes the shape that ties the logo to the interface.

---

## 6. Typography

Architecture §12 caps key pages at 300 KB. That decides this.

- **One variable font**, self-hosted, subset to Latin, `woff2`. Not two.
- Hierarchy comes from **weight, not family**.
- Recommended: **Plus Jakarta Sans** or **Figtree** — both carry more warmth
  than Inter while staying clean at 14px on a low-end Android.
- Never load fonts from a third-party CDN. Self-host: one less DNS lookup, one
  less connection, and it works when the CDN is slow from Kampala.

| Role | Size | Weight |
|---|---|---|
| Page title | 24px | 600 |
| Section | 18px | 600 |
| Body | 16px | 400 |
| UI label | 14px | 500 |
| Caption | 13px | 400 |

Minimum body size 14px. Never below.

---

## 7. Semantic colour

Tie status colour to the state machines so the UI stays consistent.

**Verification tiers** (architecture §11.1)

| Tier | Badge | Colour |
|---|---|---|
| 0 | none | — |
| 1 | Profile Verified | `green-100` fill, `green-900` text |
| 2 | Business Verified | `green-600` fill, white text |
| 3 | Event Go Verified | `green-900` fill, `amber-500` check |

**Requirement status**

| Status | Colour |
|---|---|
| `draft`, `open` | `line` / `slate` |
| `sourcing`, `offers_received` | `amber-100` fill, `amber-700` text |
| `shortlisted`, `awarded` | `green-100` fill, `green-900` text |
| `booked`, `fulfilled` | `green-600` fill, white text |
| `no_offers`, `dropped` | `slate`, muted |

**Featured placement** must always carry a visible "Sponsored" label and use
`amber-100` — never green. Paid placement dressed as organic ranking destroys
the organiser trust that is the actual inventory being sold (architecture §9.6).

---

## 8. Tailwind

Tailwind 4, CSS-first, in `resources/css/app.css`:

```css
@theme {
  --color-green-50:  #F0F8F5;
  --color-green-100: #D3EBE3;
  --color-green-400: #2E9C82;
  --color-green-600: #14705C;
  --color-green-700: #0F5A4B;
  --color-green-900: #08302A;

  --color-amber-100: #FAE9CE;
  --color-amber-500: #E0952F;
  --color-amber-700: #A8641A;

  --color-surface: #FBFAF7;
  --color-line:    #D5DCD8;
  --color-slate:   #55635D;
  --color-ink:     #16211D;

  --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
}
```

Tailwind 3 equivalent goes in `theme.extend.colors` in `tailwind.config.js`
with the same values.

---

## 9. Voice

- Sentence case everywhere. Never Title Case, never ALL CAPS.
- Plain English. The audience includes people whose second language is English
  and providers who left school early. "Send your offer", not "Submit proposal".
- No exclamation marks in system copy.
- Never imply protection Event Go does not provide. At offer acceptance, state
  plainly that the agreement is between the two parties and Event Go does not
  hold funds or mediate disputes (architecture §11.3). Users who understand the
  model do not feel deceived when it behaves as designed.

---

## Open

- Logo SVG not yet drawn as production assets (lockup, stacked, icon, mono)
- Trademark not yet checked against the URSB register
- `eventgo.ug` availability not yet confirmed

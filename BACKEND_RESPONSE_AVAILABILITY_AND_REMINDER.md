# Backend response — availability badges + "Your order" banner

Reply to two handoffs:

- *Seed: availability badge + sold-out card test cases*
- *Check: `GET /user/orders/active-reminder`*

Both are done and verified by actually running the API. Please read the
**"What changed for you"** sections — a few things do not match what the
handoffs assumed, and one of them changes what you will see on screen.

---

## 1. Availability badges / sold-out card

### Status: done

The 8 QA bundles are seeded and every state renders. But **seeding alone
was never going to work.** `BundleResource` hard-coded `time_left_text`
to either `"{n} دقيقة"` or `"انتهت"`, so:

- cases 1–6 would have shown `240 دقيقة` instead of an empty string
- case 8 would have shown `انتهت`, which contains no `"sold"`, so the card
  would have stayed **white** instead of going grey

The resource now implements the exact rules from your handoff:

```
stock <= 0         -> "Sold out at HH:mm today"   (and minutes_left forced to 0)
minutes_left == 0  -> "انتهت"
minutes_left <= 60 -> "{n} min left"
otherwise          -> ""                          (app falls back to the stock badge)
```

### Verified output (real API responses)

| # | name | stock | minutes_left | time_left_text |
|---|---|---:|---:|---|
| 1 | `[qa] 5+ left` | 8 | 359 | `""` |
| 2 | `[qa] 5 left` | 5 | 359 | `""` |
| 3 | `[qa] 4 left` | 4 | 359 | `""` |
| 4 | `[qa] 3 left` | 3 | 359 | `""` |
| 5 | `[qa] 2 left` | 2 | 359 | `""` |
| 6 | `[qa] 1 left` | 1 | 359 | `""` |
| 7 | `[qa] 14 min left` | 3 | 13 | `"13 min left"` |
| 8 | `[qa] Sold out` | 0 | 0 | `"Sold out at 02:45 today"` |
| — | `[qa] Ended Arabic` | 5 | 0 | `"انتهت"` |

Endpoints confirmed returning these rows:

| Endpoint | Contains |
|---|---|
| `GET /user/bundles` | cases 1–7 |
| `GET /user/bundles/indexlasthour` | cases 7 and 8 |
| `GET /user/bundles/indexlastchance` | case 6 (`stock = 1`) |
| `GET /user/bundles?name=qa` | cases 1–7 |
| `GET /user/bundles?category_id=1` | cases 1–7 |
| `GET /user/bundles/{id}` | any case, including 8 |

### What changed for you

**a) Case 7 reports `13`, not `14`.**
`minutes_left` is floored, exactly as your spec says. One second after
seeding, 14 minutes becomes 13. This is correct behaviour — just do not
use a literal `14` as your pass condition. The badge and timer icon still
show, because the value is in `1..60` and the text contains `"min"`.

**b) Cases 1–6 have a pickup window that is already open.**
Your table asked for `opening_time = now + 2h` *and* for these rows to
appear in `user/bundles`. Those two are mutually exclusive: `index`
filters `opening_time <= now`, so a window opening in two hours is
excluded from the list entirely. The seed now uses `now - 1h` to
`now + 6h` — the window is open so the rows appear, and
`minutes_left = 359 > 60` so you still get the stock badge, which is what
these cases are actually testing.

**c) Case 8's clock time is derived, not literal.**
There is no `sold_out_at` column and one was not added. The time inside
`"Sold out at HH:mm today"` comes from the bundle's `ended_time`, so it
will not read `15:32` — it reads whatever that bundle's window closes at.
The wording, the `"sold"` substring, and the grey card are unaffected.

**d) `[qa] Ended Arabic` behaves as you predicted** — `stock = 5` and no
`"sold"` in the text, so the card stays white. Useful only to confirm the
old expired string.

---

## 2. `GET /user/orders/active-reminder`

### Status: already existed, and now correct

The endpoint was already implemented and its JSON already matched your
contract: not double-wrapped, snake_case, registered **before** `/{id}`
so nothing swallows the route, and behind the same `auth:sanctum` as the
rest of `user/orders`. Items 1, 2 and 7 on your failure list were never
the problem.

Four real bugs were found and fixed.

### Verified with curl

| Case | Result |
|---|---|
| No token | **401** `{"message":"Unauthenticated."}` — not 404, not 500 |
| Logged in, no order | **200** `{"status":true,"data":null}` — `status` is a real boolean |
| Order tomorrow | **200**, `seconds_until_collection = 93588`, non-empty `display_text` |
| Order later today | `seconds_until_collection = 26329` → `Collection starts in 07:18:49` |
| After cancelling | `{"status":true,"data":null}` |

Your assertion script was run verbatim against the tomorrow case and
printed **`SHAPE OK`**.

Sample response:

```json
{
  "status": true,
  "data": {
    "order_id": 2,
    "bundle_id": 11,
    "bundle_name": "[qa] Countdown Bundle",
    "bundle_image": "https://images.unsplash.com/photo-1519378058457-4c29a0a2efac?w=800",
    "collection_start": "2026-09-01T09:02:24+03:00",
    "collection_end": "2026-09-01T23:30:00+03:00",
    "status": "pending",
    "display_text": "Collection starts at 09:02 today",
    "seconds_until_collection": 26329
  }
}
```

### What was fixed

**a) The wrong order was returned when a user had more than one.**
The query used `latest()` — most recently *created* — then took the first
one with a future collection. With two pending orders it could return the
one further away. It now sorts by `opening_time` and returns the
**nearest collection**, as your handoff requires. Confirmed: with orders
at +26h and +7h19m, the endpoint returns the +7h19m one.

**b) `bundle_image` could be `null`.**
Your contract says string/required, and your script asserts
`img.startswith("http")`, which throws on `null`. It now returns `""`
when there is no image, and absolute `https://` URLs pass through
untouched instead of being mangled by `asset()`.

**c) Types are explicitly cast.**
`order_id`, `bundle_id` and `seconds_until_collection` are cast to `int`
so no DB driver can hand you a string. This was your failure item 4.

**d) `collection_end` was returning the wrong date.** See section 3.

### What changed for you

**The reminder no longer disappears when the pickup window opens.**
The old code required `opening_time` to be in the future, so the moment
collection started the order dropped out and the banner vanished. Your
`seconds_until_collection <= 0` case could therefore never happen. Now
the order stays and `seconds` becomes `0`.

Two consequences worth knowing:

- **There is no expiry.** Nothing removes the order after the window
  ends, so the banner persists until the status stops being `pending`.
  If you want it to disappear at window close, say so and it can be added.
- **`display_text` during an open window still reads
  `"Collection starts at HH:mm today"`**, not `"Collection in progress"`.
  Tell me the copy you want and it will be changed.

---

## 3. Schema fix that affects more than QA

This is the most important item in this document.

`bundles.ended_time` was a **`time`** column — a clock time with no date,
e.g. `01:56:15`. The list endpoints compare it against a full datetime:

```php
where('ended_time', '>',  now())   // indexlasthour
where('ended_time', '>=', now())   // index, indexlastchance
```

That comparison happens as **strings**, and
`'01:56:15' > '2026-09-01 01:44:12'` is always `false`. The practical
effect: **`indexlasthour` returned nothing, ever** — not just for QA rows.
All three list endpoints came back empty on a clean database.

A migration now converts the column to `datetime`, rebuilding each value
from the bundle's `opening_time` date and rolling to the next day when
the close time is earlier than the open time. After it ran, the endpoints
returned data immediately.

**This migration rewrites a column holding live data. It should be
reviewed before being run on the server.**

---

## 4. Nothing to change in the app

No API field was renamed, removed, or retyped in a way that breaks
existing parsing. Everything above either corrects a wrong value or fills
an empty one. No client release should be needed.

What to check once deployed:

- [ ] Cases 1–6 show the coloured stock badge, white card
- [ ] Case 7 shows a coral badge with a timer icon (the number will be 13
      or lower depending on when you look)
- [ ] Case 8 shows a grey badge **and a grey card body**
- [ ] Home banner appears for a user with a pending order
- [ ] With two pending orders, the banner shows the **nearer** one
- [ ] Under 24h the subtitle ticks every second
- [ ] Over 24h it shows the static `display_text`

---

## 5. Open items on the backend side

Not blocking, but you should know:

1. **Timezone is `Africa/Cairo`, not `Asia/Beirut`.** Your reminder
   handoff asks for Beirut. That is a one-hour shift in both
   `display_text` and the countdown. Changing it affects the whole
   project, not just these endpoints, so it was left alone — please
   confirm which is correct.
2. **Sold-out rows appear in `indexlasthour` but not in `index` or
   `indexlastchance`**, which filter `stock > 0` and `stock = 1`. You
   asked not to hide sold-out items for this QA pass, but relaxing those
   filters changes production list behaviour, so it was not done. Case 8
   is reachable via `indexlasthour` and `/{id}`.
3. **Cleanup:** delete bundles whose `name` starts with `[qa]`.

Seed with:

```bash
php artisan db:seed --class=AvailabilityTestBundleSeeder
```

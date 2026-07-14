# Backend Requirements for UI Follow-Up

This document lists the backend support needed to make the newly mapped UI
affordances fully functional. Requirements are based on the current Flutter code
and the reference images in `images/`.

> **Priority order** — see [Priority Order](#priority-order) at the end.

---

## 1. Cancel Reservation

**Current frontend state**

- Order details can fetch an order with `GET user/orders/{id}`.
- Reservation creation exists through `POST user/orders`.
- The UI now shows **Cancel reservation** for pending orders, but it is not
  wired because no cancel API exists.

**Required backend** — add an endpoint to cancel a pending order/reservation.

Preferred option:

```http
POST /user/orders/{id}/cancel
Authorization: Bearer <token>
```

Alternative option:

```http
DELETE /user/orders/{id}
Authorization: Bearer <token>
```

**Expected behavior**

- Only pending orders can be cancelled.
- Return a clear validation error if the order is already collected, expired,
  cancelled, or not owned by the user.
- Return the updated order or a success envelope.

Suggested success response:

```json
{
  "status": true,
  "message": "Reservation cancelled successfully",
  "data": { "id": 10, "status": "cancelled" }
}
```

Suggested error response:

```json
{
  "status": false,
  "message": "Only pending reservations can be cancelled"
}
```

**Frontend files waiting for this**

- `lib/features/order_details/ui/order_details_screen.dart`
- `lib/features/order_details/data/services/order_details_service.dart`
- `lib/features/order_details/data/repo/order_details_repo.dart`
- `lib/features/order_details/logic/order_details_cubit.dart`

---

## 2. Home Active Order Reminder

**Reference images**

- `images/01-home-feed-suggestions-order-reminders.jpeg`
- `images/16-home-order-starts-tomorrow-banner.jpeg`
- `images/17-home-order-countdown-banner.jpeg`

**Current frontend state**

- Orders exist in the Orders feature.
- Home does not currently receive active/current order reminder data.
- The desired UI is a compact banner: image, `Your order`, and either
  `Collection starts at 18:00 tomorrow` or `Collection starts in 07:19:25`.

**Required backend** — add a lightweight endpoint for the active order reminder
on Home, or include it in the existing Home response if the backend prefers.

Preferred endpoint:

```http
GET /user/orders/active-reminder
Authorization: Bearer <token>
```

Suggested response when an active reminder exists:

```json
{
  "status": true,
  "data": {
    "order_id": 10,
    "bundle_id": 33,
    "bundle_name": "3 Coffee + 1 Donuts",
    "bundle_image": "https://example.com/image.jpg",
    "collection_start": "2026-06-02T18:00:00Z",
    "collection_end": "2026-06-02T18:30:00Z",
    "status": "pending",
    "display_text": "Collection starts at 18:00 tomorrow",
    "seconds_until_collection": 26365
  }
}
```

Suggested response when no reminder exists:

```json
{ "status": true, "data": null }
```

**Important details**

- `seconds_until_collection` lets the app render a live countdown without
  recalculating timezone-sensitive values incorrectly.
- `display_text` lets the backend control wording if business rules are specific.
- `order_id` is needed so tapping the banner can navigate to Order Details.

**Frontend files waiting for this**

- `lib/features/home/ui/home_screen.dart`
- `lib/features/home/logic/home_cubit.dart`
- `lib/features/home/data/services/home_service.dart`
- `lib/features/home/data/repo/home_repo.dart`

---

## 3. Store Recommendation Submission

**Reference images**

- `images/05-home-suggestions-card.jpeg`
- `images/18-home-suggestions-card-closeup.jpeg`

**Current frontend state**

- The Suggestions card is visible on Home.
- The CTA currently cannot submit anything because no route/API/data model exists.

**Required backend** — add an endpoint for users to recommend a missing store.

```http
POST /user/store-recommendations
Authorization: Bearer <token>
Content-Type: application/json
```

Suggested request:

```json
{
  "store_name": "Wooden Bakery",
  "location_hint": "Metn",
  "notes": "Customer wants this store on Waste-less"
}
```

Suggested success response:

```json
{
  "status": true,
  "message": "Store recommendation submitted successfully"
}
```

**Frontend files waiting for this**

- `lib/features/home/ui/home_screen.dart`
- New recommendation form screen or modal.
- New service/repo/cubit methods if the flow becomes stateful.

---

## 4. Multiple Outlets for Bundle Details

**Reference image** — `images/12-bundle-details-location-view-outlets.jpeg`

**Current frontend state**

- Bundle details exposes one `branch`.
- The UI now shows **View all outlets**, but it can only open the existing
  branch map.

**Required backend** — include all available outlets/branches for a bundle or
company.

Option A — include in bundle details response:

```json
{
  "id": 33,
  "name": "3 Coffee + 1 Donuts",
  "branch": {
    "id": 1,
    "name": "Hamra",
    "address": "Hamra str.",
    "lat": "33.895",
    "lng": "35.478"
  },
  "branches": [
    {
      "id": 1,
      "name": "Hamra",
      "address": "Hamra str.",
      "lat": "33.895",
      "lng": "35.478",
      "distance_text": "2.3 km"
    }
  ]
}
```

Option B — separate endpoint:

```http
GET /user/bundles/{id}/branches
Authorization: Bearer <token>
```

**Frontend files waiting for this**

- `lib/features/bundle_details/data/models/get_bundle_details_response.dart`
- `lib/features/bundle_details/ui/bundle_details_screen.dart`
- New outlets list modal/screen if multiple branches are returned.

---

## 5. Review Highlights and Full Review List

**Reference image** — `images/09-bundle-details-review-actions.jpeg`

**Current frontend state**

- Bundle details already has `latest_reviews`.
- **See all** now navigates to the existing Bundle Reviews screen using the
  available `latest_reviews` list.
- This only shows the latest reviews, not necessarily all reviews.

**Required backend** — if product expects a real full reviews list, add pagination.

```http
GET /user/bundles/{id}/reviews?page=1&per_page=20
Authorization: Bearer <token>
```

Suggested response:

```json
{
  "status": true,
  "data": [
    {
      "id": 88,
      "rating": 5,
      "comment": "Fresh and easy pickup",
      "customer": { "id": 7, "name": "Sarah" },
      "created_at": "2026-06-01T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 42
  }
}
```

**Optional improvement**

- Add a separate `review_highlights` array for short chips such as `Fresh`,
  `Good value`, `Fast pickup`.
- This would avoid using raw review comments like `Test` as highlight chips.

Suggested bundle details fields:

```json
{
  "review_highlights": ["Fresh", "Good value", "Fast pickup"]
}
```

**Frontend files waiting for this**

- `lib/features/bundle_details/data/models/get_bundle_details_response.dart`
- `lib/features/bundle_details/ui/bundle_details_screen.dart`
- `lib/features/bundle_details/ui/bundle_reviews_screen.dart`

---

## 6. Time and Availability Fields

**Reference images**

- `images/11-sold-out-badge.jpeg`
- `images/14-countdown-time-left-badge.jpeg`

**Current frontend state**

- Home bundle model has `time_left_text` and `minutes_left`.
- Bundle details model has `time_left_text` and `minutes_left`.
- The UI now reads those fields through `AvailabilityBadge`.

**Required backend** — keep these fields consistent across all bundle endpoints.

Required fields:

```json
{
  "stock": 1,
  "minutes_left": 14,
  "time_left_text": "14min left"
}
```

For sold-out / expired:

```json
{
  "stock": 0,
  "minutes_left": 0,
  "time_left_text": "Sold out at 15:32 today"
}
```

**Important details**

- `stock = 0` should be returned for sold out.
- `time_left_text` should be user-ready text if the backend owns this wording.
- If the backend wants the app to localize wording, return structured fields
  such as `availability_status`, `sold_out_at`, and `seconds_left`.

---

## Priority Order

| # | Requirement | Status |
|---|-------------|--------|
| 1 | Cancel reservation endpoint | ⬜ |
| 2 | Home active order reminder endpoint | ⬜ |
| 3 | Store recommendation submission endpoint | ⬜ |
| 4 | Multiple outlets/branches support | ⬜ |
| 5 | Paginated full reviews and optional review highlights | ⬜ |
| 6 | Consistent availability/time-left fields across all bundle endpoints | ⬜ |

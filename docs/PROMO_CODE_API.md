# Promo Code API — Flutter Integration

All endpoints below require the user token:

```
Authorization: Bearer <token>
Accept: application/json
```

Base URL: `{{base_url}}/api/user`

---

## Flow

1. User types a code on the checkout screen → call **Apply** (`POST /promo-codes/apply`) to validate it and show the discount.
2. On "Pay", send the same code in **Create Order** (`POST /orders`) as `promo_code`.
3. The backend validates the code again and applies the discount to the payment amount.
4. The code is counted as used **only after payment succeeds**. If the user leaves the payment page, the code isn't consumed.

> Apply is a preview only. It doesn't reserve or consume the code.

---

## 1. Apply promo code

`POST /promo-codes/apply`

### Request

Send the cart items (recommended, same shape as Create Order):

```json
{
  "code": "SAVE20",
  "items": [
    { "bundle_id": 12, "quantity": 2 },
    { "bundle_id": 5,  "quantity": 1 }
  ]
}
```

Or a plain amount instead of `items`:

```json
{
  "code": "SAVE20",
  "amount": 45.50
}
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `code` | string | yes | Case-insensitive (`save20` = `SAVE20`) |
| `items` | array | if no `amount` | `bundle_id` + `quantity` |
| `amount` | number | if no `items` | Amount the discount is calculated on |

### Success — `200`

```json
{
  "status": true,
  "message": "Promo code applied successfully",
  "data": {
    "code": "SAVE20",
    "type": "percentage",
    "value": 20,
    "max_discount": 10,
    "sub_total": 60.00,
    "bundle_discount": 15.00,
    "amount": 45.00,
    "promo_discount": 9.00,
    "total": 36.00,
    "end_date": "2026-12-31T23:59:00+00:00"
  }
}
```

| Field | Meaning |
|---|---|
| `type` | `percentage` or `fixed` |
| `value` | `20` means 20% when `type = percentage`, or 20 USD when `type = fixed` |
| `max_discount` | Cap on a percentage discount in USD, or `null` |
| `sub_total` | Original price × quantity |
| `bundle_discount` | Discount already on the bundles |
| `amount` | `sub_total - bundle_discount` (the promo is calculated on this) |
| `promo_discount` | Discount from the promo code (USD) |
| `total` | `amount - promo_discount` |
| `end_date` | ISO 8601 or `null` (no expiry) |

> `total` here doesn't include the loyalty discount. The final amount, with loyalty, is returned by Create Order in `summary.total`.

### Errors — `422`

Validation error (missing or invalid fields):

```json
{
  "status": false,
  "errors": { "code": ["The code field is required."] }
}
```

Promo code rejected:

```json
{
  "status": false,
  "message": "This promo code has expired"
}
```

Possible `message` values (show it to the user as-is):

| Message |
|---|
| `Invalid promo code` |
| `This promo code is inactive` |
| `This promo code is not active yet` |
| `This promo code has expired` |
| `Minimum order amount for this promo code is 20.00` |
| `This promo code has reached its usage limit` |
| `You have already used this promo code the maximum number of times` |

`404` → `Bundle not found or inactive`

---

## 2. Create order with promo code

`POST /orders` (existing endpoint, with one new optional field)

```json
{
  "items": [
    { "bundle_id": 12, "quantity": 2 }
  ],
  "payment_type": "whish_money",
  "promo_code": "SAVE20"
}
```

| New field | Type | Required |
|---|---|---|
| `promo_code` | string | no |

If the code is invalid at this point, the order is **not** created and you get `422` with the same `message` values as Apply.

### Success — `201`

The response now includes a `summary` object:

```json
{
  "status": true,
  "message": "Payment initiated successfully",
  "transaction_id": 345,
  "payment_type": "whish_money",
  "collect_url": "https://...",
  "summary": {
    "sub_total": 60.00,
    "bundle_discount": 15.00,
    "promo_code": "SAVE20",
    "promo_type": "percentage",
    "promo_value": 20,
    "promo_discount": 9.00,
    "loyalty_discount": 0,
    "total": 36.00
  }
}
```

If no code is sent, `promo_code` / `promo_type` / `promo_value` are `null` and `promo_discount` is `0`.

**Calculation order:**

```
amount        = sub_total - bundle_discount
after_promo   = amount - promo_discount
total         = after_promo - loyalty_discount   // loyalty % is applied on after_promo
```

`summary.total` is the amount that is actually charged.

---

## 3. Order list / details

`GET /orders` and `GET /orders/{id}` now include these fields on each order:

```json
{
  "id": 101,
  "status": "pending",
  "total": 36.00,
  "sub_total": 60.00,
  "total_discount": 15.00,
  "loyalty_discount": 0,
  "promo_code": "SAVE20",
  "promo_type": "percentage",
  "promo_value": 20,
  "promo_discount": 9.00,
  "...": "other existing fields unchanged"
}
```

- `promo_code` is `null` when no code was used.
- `total` now subtracts both `promo_discount` and `loyalty_discount`.

---

## UI suggestions

- Show the discount label from `type` + `value`: `20% OFF` or `5.00 USD OFF`.
- Re-call Apply whenever the cart changes, because the discount depends on the cart amount.
- Show `message` from error responses directly under the promo field.
- Add a "Remove" button: just stop sending `promo_code` in Create Order.

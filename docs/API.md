# API Documentation

Base URL (local): `http://127.0.0.1:8000/api`

All endpoints accept and return `application/json`. Authenticated endpoints require a
[Sanctum](https://laravel.com/docs/sanctum) bearer token:

```
Authorization: Bearer <token>
```

## Table of Contents

- [Authentication](#authentication)
  - [Register](#post-apiregister)
  - [Login](#post-apilogin)
  - [Logout](#post-apilogout)
  - [Current User](#get-apime)
- [Payment Requests](#payment-requests)
  - [List Payment Requests](#get-apipayment-requests)
  - [Create Payment Request](#post-apipayment-requests)
  - [Show Payment Request](#get-apipayment-requestsid)
  - [Approve Payment Request](#patch-apipayment-requestsidapprove)
  - [Reject Payment Request](#patch-apipayment-requestsidreject)
- [Error Responses](#error-responses)

---

## Authentication

### `POST /api/register`

Registers a new user and returns an API token.

**Request body**

| Field      | Type   | Rules                                                              |
|------------|--------|---------------------------------------------------------------------|
| `name`     | string | required, max 255                                                  |
| `email`    | string | required, valid email, unique                                     |
| `password` | string | required, min 8                                                    |
| `country`  | string | required, max 255                                                  |
| `currency` | string | required, 3 letters, one of the [supported currencies](#supported-currencies) |
| `role`     | string | optional, `employee` (default) or `finance`                       |

**Example request**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "country": "Portugal",
  "currency": "EUR"
}
```

**Example response — `201 Created`**

```json
{
  "message": "User registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "role": "employee",
      "country": "Portugal",
      "currency": "EUR"
    },
    "token": "1|abcdef123456..."
  }
}
```

---

### `POST /api/login`

Authenticates a user and returns a new API token.

**Request body**

| Field      | Type   | Rules    |
|------------|--------|----------|
| `email`    | string | required |
| `password` | string | required |

**Example response — `200 OK`**

```json
{
  "message": "Logged in successfully.",
  "data": {
    "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com", "role": "employee", "country": "Portugal", "currency": "EUR" },
    "token": "2|abcdef123456..."
  }
}
```

**Invalid credentials — `422 Unprocessable Entity`**

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### `POST /api/logout`

🔒 Requires authentication.

Revokes the current API token.

**Example response — `200 OK`**

```json
{ "message": "Logged out successfully." }
```

---

### `GET /api/me`

🔒 Requires authentication.

Returns the authenticated user.

**Example response — `200 OK`**

```json
{
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "employee",
    "country": "Portugal",
    "currency": "EUR"
  }
}
```

---

## Payment Requests

### `GET /api/payment-requests`

🔒 Requires authentication.

Lists payment requests. **Employees** only see their own requests; **finance** users see
requests from everyone. Results are paginated (15 per page).

**Query parameters**

| Param    | Type   | Rules                                                       |
|----------|--------|--------------------------------------------------------------|
| `status` | string | optional, one of `pending`, `approved`, `rejected`, `expired` |

**Example request**

```
GET /api/payment-requests?status=pending
```

**Example response — `200 OK`**

```json
{
  "data": [
    {
      "id": 1,
      "user": { "id": 2, "name": "Maria Costa", "email": "maria.costa@buzzvel.com", "country": "Brazil" },
      "amount": 500,
      "currency": "BRL",
      "exchange_rate": 5.886181,
      "exchange_rate_source": "https://open.er-api.com/v6/latest",
      "exchange_rate_fetched_at": "2026-06-15T17:49:38+00:00",
      "amount_eur": 84.95,
      "description": "Office supplies",
      "status": "pending",
      "approver": null,
      "approved_at": null,
      "expires_at": "2026-06-17T17:49:38+00:00",
      "created_at": "2026-06-15T17:49:38+00:00",
      "updated_at": "2026-06-15T17:49:38+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### `POST /api/payment-requests`

🔒 Requires authentication.

Creates a payment request in the authenticated user's local currency. The
EUR → `currency` exchange rate is fetched automatically from the configured
exchange rate provider, stored alongside the request (rate, source and
timestamp), and used to compute `amount_eur`. The stored rate is **immutable**
once created. The request expires automatically after **48 hours** if it
remains `pending`.

**Request body**

| Field         | Type   | Rules                                                              |
|---------------|--------|---------------------------------------------------------------------|
| `amount`      | number | required, numeric, min `0.01`, max `999999999.99`                  |
| `currency`    | string | required, 3 letters, one of the [supported currencies](#supported-currencies) |
| `description` | string | optional, max 255                                                  |

**Example request**

```json
{
  "amount": 500,
  "currency": "BRL",
  "description": "Office supplies"
}
```

**Example response — `201 Created`**

```json
{
  "message": "Payment request created successfully.",
  "data": {
    "id": 1,
    "user": { "id": 2, "name": "Maria Costa", "email": "maria.costa@buzzvel.com", "country": "Brazil" },
    "amount": 500,
    "currency": "BRL",
    "exchange_rate": 5.886181,
    "exchange_rate_source": "https://open.er-api.com/v6/latest",
    "exchange_rate_fetched_at": "2026-06-15T17:49:38+00:00",
    "amount_eur": 84.95,
    "description": "Office supplies",
    "status": "pending",
    "approver": null,
    "approved_at": null,
    "expires_at": "2026-06-17T17:49:38+00:00",
    "created_at": "2026-06-15T17:49:38+00:00",
    "updated_at": "2026-06-15T17:49:38+00:00"
  }
}
```

**Exchange rate provider unavailable — `503 Service Unavailable`**

```json
{ "message": "Unable to fetch exchange rate for currency [BRL]. Reason: HTTP 500" }
```

---

### `GET /api/payment-requests/{id}`

🔒 Requires authentication. Only the **owner** of the request or a **finance** user can view it.

**Example response — `200 OK`**

Same shape as a single item from the [list endpoint](#get-apipayment-requests).

**Not the owner / not finance — `403 Forbidden`**

```json
{ "message": "This action is unauthorized." }
```

---

### `PATCH /api/payment-requests/{id}/approve`

🔒 Requires authentication and the **finance** role. The target request must
still be `pending`.

**Example response — `200 OK`**

```json
{
  "message": "Payment request approved successfully.",
  "data": {
    "id": 1,
    "status": "approved",
    "approver": { "id": 6, "name": "Anna Mueller" },
    "approved_at": "2026-06-15T18:00:00+00:00"
  }
}
```

**Not a finance user — `403 Forbidden`**

```json
{ "message": "Only finance users can review payment requests." }
```

**Already reviewed / expired — `409 Conflict`**

```json
{ "message": "This payment request has already been approved and can no longer be reviewed." }
```

---

### `PATCH /api/payment-requests/{id}/reject`

Same as [approve](#patch-apipayment-requestsidapprove), but sets `status` to `rejected`.

---

## Error Responses

| Status | Meaning                                              |
|--------|------------------------------------------------------|
| 401    | Missing/invalid authentication token                 |
| 403    | Authenticated but not authorized for this action     |
| 404    | Resource not found                                   |
| 409    | Conflict (e.g. reviewing an already-reviewed request)|
| 422    | Validation error (`errors` object included)          |
| 503    | Exchange rate provider unavailable                   |

**Validation error example — `422 Unprocessable Entity`**

```json
{
  "message": "The amount field is required. (and 1 more error)",
  "errors": {
    "amount": ["The amount field is required."],
    "currency": ["The currency field is required."]
  }
}
```

## Supported Currencies

`EUR`, `USD`, `GBP`, `BRL`, `MXN`, `JPY`, `CAD`, `AUD`, `CHF`, `PLN`

(configurable in [`config/currencies.php`](../config/currencies.php))

# TXTAPI

## Vision

A tiny,  developer-first SMS API modeled after Textbelt’s simplicity.
One endpoint to send, one to check quota, one to check delivery status.

## Reference

POST `/text` to send an SMS with `{ phone, message, key }`

we will support API keys with quotas.

Quota lookup endpoint.

Status lookup endpoint for a text ID.

## Public API (v0)

### Send a text

POST `/text`

#### Request (application/x-www-form-urlencoded or JSON)
```
phone=+15555555555
message=Hello world
key=demo-or-user-key
```

#### Example JSON
```json
{
    "phone": "+15555555555",
    "message": "Hello world",
    "key": "demo-or-user-key"
}
```

#### Response 200
```json
{
    "success": true,
    "quotaRemaining": 40,
    "textId": "msg_01HZX3…"
}
```

#### Response 4xx (examples)
```json
{ "success": false, "error": "invalid_key" }
{ "success": false, "error": "insufficient_quota" }
{ "success": false, "error": "rate_limited" }
{ "success": false, "error": "invalid_phone" }
```

#### Semantics

Idempotency: if the client sends `Idempotency-Key` header, duplicates within 24h return the first `textId`.

Max message length: 160 GSM-7 (v0). Longer: reject with `message_too_long`.

### Quota lookup

GET `/quota/:key`

#### Response 200
```json
{
    "success": true,
    "status": "queued|sent|delivered|failed",
    "provider": "mock|twilio|sns|smtp-gateway",
    "errorCode": null,
    "updatedAt": "2025-09-26T17:12:03Z"
}
```

### Delivery status lookup

GET `/status/:textId`

#### Response 200
```json
{
    "success": true,
    "status": "queued|sent|delivered|failed",
    "provider": "mock|twilio|sns|smtp-gateway",
    "errorCode": null,
    "updatedAt": "2025-09-26T17:12:03Z"
}
```

## Auth, Quotas, and Limits

Auth: API key supplied via `key` parameter or `Authorization: Bearer <key>`.

Quota: per‑key allowance. Decrement on “accepted for delivery” (when queued), refund if provider hard‑fails.

## Error Model (stable across endpoints)
```json
{
    "success": false,
    "error": "<machine_code>",
    "message": "Human-readable explanation",
    "hint": "Optional short remediation tip"
}
```

**Common error codes**: `invalid_key`, `key_disabled`, `insufficient_quota`, `invalid_phone`, `message_too_long`, `rate_limited`, `provider_unavailable`, `delivery_failed`

## Architecture (v0)

// todo:

## Docs Plan

// todo

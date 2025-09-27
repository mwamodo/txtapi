# txtAPI

## Vision

A tiny, boring, developer-first SMS API modeled after Textbelt’s simplicity. One endpoint to send, one to check quota, one to check delivery status, plus an optional inbound webhook.

### Tenets

1. Simple first: curl-able in one line.
2. Predictable: stable request/response shapes, explicit error codes.
3. Portable: run locally or self-host. Pluggable SMS backends.
4. Honest performance: document limits and delivery expectations.

### Non‑goals (for now)

1. MMS
2. Group Messaging
3. Short codes
4. Unicode segmentation edge-cases
5. Message scheduling UI,
6. Phone number renting/porting flows.

## Reference

POST `/text` to send an SMS with `{ phone, message, key }`.

Free/paid distinction is out of scope; we will support API keys with quotas and document a conservative soft limit (~1 msg/sec) per key.

Quota lookup endpoint.

Status lookup endpoint for a text ID.

## Public API

### Send a text

##### POST `/text`

Request (application/x-www-form-urlencoded or JSON)

```
phone=+15555555555
message=Hello world
key=demo-or-user-key
```

##### Example JSON

```
{
    "phone": "+15555555555",
    "message": "Hello world",
    "key": "demo-or-user-key"
}
```

##### Response 200

```
{
    "success": true,
    "quotaRemaining": 40,
    "textId": "msg_01HZX3…"
}
```

##### Response 4xx (examples)

```
{ "success": false, "error": "invalid_key" }
{ "success": false, "error": "insufficient_quota" }
{ "success": false, "error": "rate_limited" }
{ "success": false, "error": "invalid_phone" }
```

#### Semantics

Idempotency: if the client sends `Idempotency-Key` header, duplicates within 24h return the first `textId`.

Max message length: 160 GSM-7. Longer: reject with `message_too_long`.

### Quota lookup

#### GET `/quota/:key`

##### Response 200

```
{
    "success": true,
    "quotaRemaining": 40,
    "per": "daily"
}
```

### Delivery status lookup

#### GET `/status/:textId`

##### Response 200

```
{
    "success": true,
    "status": "queued|sent|delivered|failed",
    "provider": "mock|twilio|sns|smtp-gateway",
    "errorCode": null,
    "updatedAt": "2025-09-26T17:12:03Z"
}
```

### Inbound webhook

#### POST `/webhooks/incoming`

##### Payload

```
{
    "from": "+15555555555",
    "to": "+15558675309",
    "message": "Hi",
    "provider": "twilio",
    "timestamp": "2025-09-26T17:12:03Z"
}
```

Response: `200 { "ok": true }`

Security: HMAC signature header `X-TXTAPI-Signature: sha256=...`

## Auth, Quotas, and Limits

Auth: API `key` supplied via key parameter or `Authorization: Bearer <key>`.

Soft rate limit: ~1 request/sec per key; burst=5. Return `429 rate_limited` with `Retry-After`.

Quota: per‑key daily allowance. Decrement on “accepted for delivery” (when queued), refund if provider hard‑fails.

Abuse protections: simple phone allowlist/denylist per key, country gating on keys.

## Error Model (stable across endpoints)

```
{
    "success": false,
    "error": "<machine_code>",
    "message": "Human-readable explanation",
    "hint": "Optional short remediation tip"
}
```

##### Common error codes

`invalid_key`, `key_disabled`, `insufficient_quota`

`invalid_phone`, `message_too_long`

`rate_limited`, `provider_unavailable`, `delivery_failed`

## Architecture

## Docs Plan

One-page quickstart with curl.

Endpoints reference with exact request/response.

Provider adapter guide.

Webhook verification guide.

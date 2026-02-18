# Transaction Webhook Integration

## Overview

Express Bank sends webhook notifications when a transaction's `paid_status` changes from `false` to `true`.

**Endpoint**: `POST` to your configured URL  
**Content-Type**: `application/json`  
**Delivery**: Asynchronous via queue  
**Retry**: 3 attempts (5min, 15min, 30min intervals)

---

## Request Format

### Headers
```
Content-Type: application/json
X-Signature: <hmac-sha256-hex>
X-Timestamp: <unix-timestamp>
```

### Payload

```json
{
  "transaction_id": 123,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "user_id": 456,
  "amount": 1000.50,
  "currency": "TRY",
  "status": 3,
  "paid_status": true,
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+905551234567",
  "receiver_iban": "TR330006100519786457841326",
  "receiver_name": "ACC-8000-9400",
  "bank_id": 1,
  "bank_name": "Ziraat Bankası",
  "wallet_id": 2,
  "site_id": 1,
  "site_name": "Express Bank",
  "order_id": "ORDER-12345",
  "payment_method": "manual",
  "created_at": "2025-02-11T10:00:00+00:00",
  "updated_at": "2025-02-11T10:05:00+00:00",
  "accepted_at": "2025-02-11T10:05:00+00:00",
  "timestamp": 1707654300
}
```

---

## Signature Verification

**Algorithm**: HMAC SHA256

**Formula**:
```
signature_string = timestamp + sorted_json_payload + secret_key
signature = HMAC_SHA256(signature_string, secret_key)
```

**Implementation**:
1. Sort payload keys alphabetically
2. Serialize to JSON (no escaped slashes)
3. Concatenate: `timestamp + json_string + secret_key`
4. Compute HMAC SHA256
5. Compare with `X-Signature` header (constant-time comparison)

**Note**: `timestamp` is included in payload and used in signature generation.

---

## Response

**Success**: `200 OK`  
**Error**: Non-2xx status codes trigger retry

---

## Idempotency

Webhooks may be delivered multiple times. Use `transaction_id` or `uuid` to prevent duplicate processing. Return `200 OK` even if already processed.

---

## Retry Schedule

- Attempt 1: Immediate
- Attempt 2: +5 minutes
- Attempt 3: +15 minutes
- Attempt 4: +30 minutes (final)

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Betron API Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f5f7;
            color: #111827;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.9em;
        }

        .page {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 20px 16px;
            position: sticky;
            top: 0;
            align-self: flex-start;
            max-height: 100vh;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 24px;
        }

        .brand-name {
            font-weight: 600;
            font-size: 18px;
        }

        .brand-sub {
            font-size: 12px;
            color: #6b7280;
        }

        .nav-group-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin: 16px 0 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
        }

        .nav-link:hover {
            background: #f3f4f6;
        }

        .nav-link.active {
            background: #eef2ff;
            color: #3730a3;
        }

        .nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            color: #9ca3af;
        }

        .nav-link.active .nav-icon {
            color: #4f46e5;
        }

        .nav-version {
            margin-top: 24px;
            font-size: 11px;
            color: #9ca3af;
        }

        .content {
            flex: 1;
            padding: 24px 32px;
            max-width: 980px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .header-title {
            font-size: 26px;
            font-weight: 600;
            margin: 0 0 4px;
        }

        .header-subtitle {
            margin: 0;
            font-size: 14px;
            color: #4b5563;
        }

        .badge-row {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            font-size: 12px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            font-size: 11px;
            color: #4b5563;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #22c55e;
        }

        .badge-strong {
            font-weight: 500;
            color: #111827;
        }

        h2 {
            font-size: 20px;
            margin: 32px 0 8px;
        }

        h3 {
            font-size: 16px;
            margin: 20px 0 6px;
        }

        p {
            margin: 4px 0 10px;
            font-size: 14px;
            line-height: 1.5;
            color: #4b5563;
        }

        ul {
            margin: 4px 0 10px 20px;
            padding: 0;
            font-size: 14px;
            color: #4b5563;
        }

        li + li {
            margin-top: 2px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .card {
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
        }

        .card-title {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-text {
            font-size: 13px;
            color: #4b5563;
        }

        .endpoint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #f3f4f6;
            font-size: 12px;
            margin-top: 4px;
        }

        .method {
            font-weight: 600;
            font-size: 11px;
        }

        .method-get {
            color: #16a34a;
        }

        .method-post {
            color: #0284c7;
        }

        .method-put {
            color: #7c3aed;
        }

        .method-status {
            color: #ea580c;
        }

        .path {
            color: #111827;
        }

        .code-block {
            background: #111827;
            color: #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 8px;
            font-size: 12px;
            overflow-x: auto;
        }

        .code-label {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 13px;
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }

        .table th {
            background: #f9fafb;
            font-weight: 500;
            font-size: 12px;
        }

        .small {
            font-size: 12px;
            color: #6b7280;
        }

        @media (max-width: 900px) {
            .page {
                flex-direction: column;
            }

            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                flex-wrap: wrap;
                gap: 8px 16px;
            }

            .brand {
                margin-bottom: 8px;
            }

            .nav-group-title,
            .nav-version {
                flex-basis: 100%;
            }

            .content {
                padding: 16px 16px 32px;
            }

            .header {
                flex-direction: column;
                gap: 8px;
            }

            .badge-row {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <nav class="sidebar">
            <div class="brand">
                <div class="brand-name">Betron</div>
                <div class="brand-sub">API Documentation</div>
            </div>

            <div class="nav-group-title">Introduction</div>
            <a href="#introduction" class="nav-link">
                <span class="nav-icon" data-lucide="compass"></span>
                <span>Overview</span>
            </a>
            <a href="#quickstart" class="nav-link">
                <span class="nav-icon" data-lucide="zap"></span>
                <span>Quickstart</span>
            </a>
            <a href="#authentication" class="nav-link">
                <span class="nav-icon" data-lucide="shield-check"></span>
                <span>Authentication</span>
            </a>

            <div class="nav-group-title">Resources</div>
            <a href="#banks" class="nav-link">
                <span class="nav-icon" data-lucide="banknote"></span>
                <span>Banks</span>
            </a>
            <a href="#transactions" class="nav-link">
                <span class="nav-icon" data-lucide="arrow-up-right"></span>
                <span>Transactions (Deposit)</span>
            </a>
            <a href="#withdrawals" class="nav-link">
                <span class="nav-icon" data-lucide="arrow-down-left"></span>
                <span>Withdrawals</span>
            </a>
            <a href="#wallets" class="nav-link">
                <span class="nav-icon" data-lucide="wallet-cards"></span>
                <span>Wallets</span>
            </a>

            <div class="nav-group-title">Lifecycle</div>
            <a href="#callbacks" class="nav-link">
                <span class="nav-icon" data-lucide="webhook"></span>
                <span>Callbacks</span>
            </a>
            <a href="#errors" class="nav-link">
                <span class="nav-icon" data-lucide="triangle-alert"></span>
                <span>Errors</span>
            </a>

            <div class="nav-version">
                API Version: <strong>v1.0.0</strong><br>
                Base URL: <code>https://betron.org/api/v1</code>
            </div>
        </nav>

        <main class="content">
            <header class="header" id="introduction">
                <div>
                    <h1 class="header-title">Betron API Documentation</h1>
                    <p class="header-subtitle">
                        Welcome to the Betron API documentation. This guide explains how to integrate Betron’s payment
                        services into your application using simple, REST-style endpoints.
                    </p>
                </div>
                <div class="badge-row">
                    <div class="badge">
                        <span class="badge-dot"></span>
                        <span class="badge-strong">Production</span>
                    </div>
                    <div class="badge">
                        <span class="badge-strong">Base URL</span>
                        <span>https://betron.org/api/v1</span>
                    </div>
                </div>
            </header>

            <section id="quickstart">
                <h2>Quickstart</h2>
                <p><strong>Before you begin:</strong> contact the Betron team to obtain your API Key and API Secret, configure
                    your callback URLs, and whitelist your server IP addresses.</p>

                <div class="card-grid">
                    <div class="card">
                        <div class="card-title">1. Get your credentials</div>
                        <div class="card-text">
                            - API Key and API Secret from Betron<br>
                            - Callback URLs for transactions and withdrawals
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">2. Fetch available banks</div>
                        <div class="card-text">
                            Use the <code>/bank</code> endpoints to list supported banks and their limits before creating transactions.
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">3. Create a transaction</div>
                        <div class="card-text">
                            Create a deposit using <code>POST /transaction</code>, redirect your customer to the provided
                            <code>checkout_url</code>, and wait for callbacks.
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">4. Handle callbacks</div>
                        <div class="card-text">
                            Use signed webhook callbacks to track transaction and withdrawal status changes in real time.
                        </div>
                    </div>
                </div>
            </section>

            <section id="authentication">
                <h2>Authentication</h2>
                <p>All requests to the Betron API must be made over HTTPS and include a bearer token in the
                    <code>Authorization</code> header.</p>

                <h3>Bearer token</h3>
                <p class="small">Example headers:</p>
                <div class="code-block">
                    <div class="code-label">Headers</div>
<pre>GET /api/v1/bank HTTP/1.1
Host: betron.org
Authorization: Bearer YOUR_API_TOKEN
Accept: application/json</pre>
                </div>

                <p class="small">
                    The token value is provided by the Betron team and must match the configured API token on the server.
                    If the header is missing, empty, or invalid, the API returns a <code>401 Unauthorized</code> response.
                </p>

                <h3>Optional HMAC Signature</h3>
                <p>For additional security, requests and callbacks can be signed using an HMAC built from your API Secret and the payload.</p>
                <div class="code-block">
                    <div class="code-label">Headers (example)</div>
<pre>Authorization: Bearer YOUR_API_TOKEN
X-Signature: HMAC_SIGNATURE_HERE</pre>
                </div>
                <p class="small">The exact signing algorithm and string-to-sign format will be provided during integration.</p>
            </section>

            <section id="banks">
                <h2>Available Banks</h2>
                <p>Use these endpoints to retrieve the list of banks that are currently available for processing deposit and withdrawal transactions.</p>

                <h3>List banks for deposit</h3>
                <div class="endpoint">
                    <span class="method method-get">GET</span>
                    <span class="path">/api/v1/bank</span>
                </div>
                <div class="endpoint" style="margin-left:4px;">
                    <span class="method method-get">GET</span>
                    <span class="path">/api/v1/bank/transaction</span>
                </div>
                <p class="small">Both endpoints return the same response and are suitable for deposit flows.</p>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "success": true,
  "message": "Banks retrieved successfully",
  "code": 200,
  "total": 1,
  "data": [
    {
      "id": 1,
      "name": "Bank A",
      "image": "https://example.com/bank-a.png",
      "transaction_status": 1,
      "withdrawal_status": 1,
      "status": 1
    }
  ]
}</pre>
                </div>

                <h3>List banks for withdrawal</h3>
                <div class="endpoint">
                    <span class="method method-get">GET</span>
                    <span class="path">/api/v1/bank/withdrawal</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "success": true,
  "message": "Banks retrieved successfully",
  "code": 200,
  "total": 1,
  "data": [
    {
      "id": 5,
      "name": "Bank W",
      "image": "https://example.com/bank-w.png",
      "transaction_status": 1,
      "withdrawal_status": 1,
      "status": 1
    }
  ]
}</pre>
                </div>
            </section>

            <section id="transactions">
                <h2>Transactions (Deposit)</h2>
                <p>Deposit transactions allow you to accept payments from your customers.</p>

                <h3>Create transaction</h3>
                <div class="endpoint">
                    <span class="method method-post">POST</span>
                    <span class="path">/api/v1/transaction</span>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Required</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>first_name</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Customer first name.</td>
                        </tr>
                        <tr>
                            <td><code>last_name</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Customer last name.</td>
                        </tr>
                        <tr>
                            <td><code>phone</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Customer phone number.</td>
                        </tr>
                        <tr>
                            <td><code>amount</code></td>
                            <td>number</td>
                            <td>yes</td>
                            <td>Payment amount.</td>
                        </tr>
                        <tr>
                            <td><code>bank_id</code></td>
                            <td>integer</td>
                            <td>yes</td>
                            <td>Bank identifier obtained from the banks endpoint.</td>
                        </tr>
                        <tr>
                            <td><code>client_ip</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Client IP address of the user initiating the payment.</td>
                        </tr>
                        <tr>
                            <td><code>order_id</code></td>
                            <td>integer</td>
                            <td>yes</td>
                            <td>Your internal order reference.</td>
                        </tr>
                        <tr>
                            <td><code>user_id</code></td>
                            <td>integer</td>
                            <td>yes</td>
                            <td>Your internal user identifier.</td>
                        </tr>
                        <tr>
                            <td><code>site_id</code>, <code>site_name</code>, <code>transaction_fee</code></td>
                            <td>mixed</td>
                            <td>set by Betron</td>
                            <td>These values are injected by Betron based on your API token; you do not need to send them.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="code-block">
                    <div class="code-label">Request body · JSON</div>
<pre>{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+905551112233",
  "amount": 250.0,
  "bank_id": 1,
  "client_ip": "203.0.113.10",
  "order_id": 12345,
  "user_id": 42
}</pre>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 201 Created</div>
<pre>{
  "success": true,
  "code": 200,
  "message": "Transaction created",
  "data": {
    "transaction_uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
    "receiver_iban": "TR000000000000000000000000",
    "receiver_name": "Betron Payment Account"
  }
}</pre>
                </div>

                <p class="small">Redirect the customer to <code>checkout_url</code> to complete the payment.</p>

                <h3>Update transaction</h3>
                <div class="endpoint">
                    <span class="method method-put">PUT</span>
                    <span class="path">/api/v1/transaction/{uuid}</span>
                </div>
                <p>Use this endpoint to update an existing transaction, for example to cancel it.</p>

                <div class="code-block">
                    <div class="code-label">Request body · JSON</div>
<pre>{
  "status": "canceled",
  "reason": "Customer canceled the payment"
}</pre>
                </div>

                <h3>Get transaction status</h3>
                <div class="endpoint">
                    <span class="method method-status">GET</span>
                    <span class="path">/api/v1/transaction/{uuid}/status</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "success": true,
  "code": 201,
  "message": "Transaction details",
  "data": {
    "id": 1,
    "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
    "user_id": 42,
    "first_name": "John",
    "last_name": "Doe",
    "sender": "John Doe",
    "phone": "+905551112233",
    "amount": 250.0,
    "currency": "TRY",
    "order_id": 12345,
    "receiver_iban": "TR000000000000000000000000",
    "receiver_name": "Betron Payment Account",
    "receiver": "Betron Payment Account",
    "bank_id": 1,
    "bank_name": "Bank A",
    "status": "success",
    "paid_status": true,
    "client_ip": "203.0.113.10",
    "created_at": "2026-02-24T12:34:56Z",
    "updated_at": "2026-02-24T12:35:56Z"
  }
}</pre>
                </div>
            </section>

            <section id="withdrawals">
                <h2>Withdrawals</h2>
                <p>Withdrawal endpoints are used to send funds to a customer or payout account.</p>

                <h3>Create withdrawal</h3>
                <div class="endpoint">
                    <span class="method method-post">POST</span>
                    <span class="path">/api/v1/withdrawal</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Request body · JSON</div>
<pre>{
  "user_id": 42,
  "first_name": "John",
  "last_name": "Doe",
  "iban": "TR000000000000000000000000",
  "bank_id": 5,
  "amount": 1000.0,
  "order_id": "WITHDRAW-98765"
}</pre>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 201 Created</div>
<pre>{
  "success": true,
  "code": 201,
  "message": "Withdrawal created successfully",
  "data": {
    "id": 10,
    "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
    "first_name": "John",
    "last_name": "Doe",
    "receiver": "John Doe",
    "iban": "TR000000000000000000000000",
    "bank_id": 5,
    "bank_name": "Bank W",
    "amount": 1000.0,
    "order_id": "WITHDRAW-98765",
    "site_id": 1,
    "site_name": "Betron",
    "sender_name": null,
    "sender_iban": null,
    "status": 0,
    "paid_status": false,
    "created_at": "2026-02-24T13:40:00Z",
    "updated_at": "2026-02-24T13:40:00Z"
  }
}</pre>
                </div>

                <h3>Get withdrawal status</h3>
                <div class="endpoint">
                    <span class="method method-status">GET</span>
                    <span class="path">/api/v1/withdrawal/{uuid}/status</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "success": true,
  "code": 201,
  "message": "Withdrawal created successfully",
  "data": {
    "id": 10,
    "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
    "first_name": "John",
    "last_name": "Doe",
    "receiver": "John Doe",
    "iban": "TR000000000000000000000000",
    "bank_id": 5,
    "bank_name": "Bank W",
    "amount": 1000.0,
    "order_id": "WITHDRAW-98765",
    "site_id": 1,
    "site_name": "Betron",
    "sender_name": "Betron",
    "sender_iban": "TR000000000000000000000000",
    "status": 1,
    "paid_status": true,
    "created_at": "2026-02-24T13:40:00Z",
    "updated_at": "2026-02-24T13:45:00Z"
  }
}</pre>
                </div>
            </section>

            <section id="wallets">
                <h2>Wallets</h2>
                <p>Wallet endpoints allow you to view your Betron balances.</p>

                <h3>Get wallet</h3>
                <div class="endpoint">
                    <span class="method method-get">GET</span>
                    <span class="path">/api/v1/wallet</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "success": true,
  "message": "Account retrieved successfully",
  "code": 200,
  "data": {
    "id": 1,
    "name": "Main Wallet",
    "iban": "TR000000000000000000000000"
  }
}</pre>
                </div>
            </section>

            <section id="callbacks">
                <h2>Callbacks</h2>
                <p>Betron sends webhook callbacks when a transaction’s <code>paid_status</code> becomes <code>true</code>.
                    Callbacks are sent to one or more URLs configured on your side.</p>

                <h3>Configuring webhook URLs & secret</h3>
                <p>In your Betron environment, the following variables control webhook delivery:</p>
                <ul>
                    <li><code>TRANSACTION_WEBHOOK_URL</code> – single callback URL (backwards compatible)</li>
                    <li><code>TRANSACTION_WEBHOOK_URLS</code> – comma‑separated list of URLs (e.g. <code>https://a.com/hook,https://b.com/hook</code>)</li>
                    <li><code>TRANSACTION_WEBHOOK_SECRET_KEY</code> – shared HMAC secret used to sign payloads</li>
                    <li><code>TRANSACTION_WEBHOOK_ENABLED</code> – enable / disable callbacks (default: <code>true</code>)</li>
                </ul>

                <h3>HTTP request</h3>
                <p>For each successful transaction update, Betron will perform an HTTP <strong>POST</strong> request to every configured URL.</p>

                <div class="code-block">
                    <div class="code-label">Headers</div>
<pre>POST /your/webhook/endpoint HTTP/1.1
Host: merchant.example.com
Content-Type: application/json
X-Signature: &lt;HMAC_SHA256_SIGNATURE&gt;
X-Timestamp: 1737654321</pre>
                </div>

                <h3>Transaction webhook payload</h3>
                <div class="code-block">
                    <div class="code-label">Body · JSON</div>
<pre>{
  "transaction_id": 1,
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "user_id": 42,
  "amount": 250.0,
  "currency": "TRY",
  "status": "success",
  "paid_status": true,
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+905551112233",
  "receiver_iban": "TR000000000000000000000000",
  "receiver_name": "Betron Payment Account",
  "bank_id": 1,
  "bank_name": "Bank A",
  "wallet_id": 10,
  "site_id": 1,
  "site_name": "Betron",
  "order_id": 12345,
  "payment_method": "manual",
  "created_at": "2026-02-24T12:34:56Z",
  "updated_at": "2026-02-24T12:35:10Z",
  "accepted_at": "2026-02-24T12:35:00Z",
  "timestamp": 1737654321
}</pre>
                </div>

                <h3>How the HMAC signature is generated</h3>
                <p>Betron signs each callback using HMAC‑SHA256 with your secret key.</p>
                <ol>
                    <li>Sort the JSON payload by keys (ascending).</li>
                    <li>Build the <em>signature string</em>:
                        <code>signature_string = timestamp + json_encode(sorted_payload) + secret_key</code>
                    </li>
                    <li>Compute the signature:
                        <code>signature = HMAC_SHA256(signature_string, secret_key)</code>.
                    </li>
                    <li>Send this value in the <code>X-Signature</code> header and the Unix timestamp in <code>X-Timestamp</code>.</li>
                </ol>

                <div class="code-block">
                    <div class="code-label">Example (pseudo-code)</div>
<pre>// payload is the JSON body as an object/array
sortedPayload = sortKeysAscending(payload)
jsonPayload   = jsonEncode(sortedPayload, withoutEscapingSlashes)
signatureStr  = payload["timestamp"] + jsonPayload + SECRET_KEY

signature = HMAC_SHA256(signatureStr, SECRET_KEY)</pre>
                </div>

                <h3>Verifying the webhook on your server</h3>
                <p>On your side you should recompute the signature and compare it to the <code>X-Signature</code> header.</p>

                <div class="code-block">
                    <div class="code-label">PHP verification example</div>
<pre>$secret   = 'YOUR_TRANSACTION_WEBHOOK_SECRET_KEY';
$body     = file_get_contents('php://input');        // raw JSON
$payload  = json_decode($body, true);               // associative array
$timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? null;
$received  = $_SERVER['HTTP_X_SIGNATURE'] ?? null;

ksort($payload);
$jsonPayload  = json_encode($payload, JSON_UNESCAPED_SLASHES);
$signatureStr = $timestamp . $jsonPayload . $secret;
$expected     = hash_hmac('sha256', $signatureStr, $secret);

if (!hash_equals($expected, $received)) {
    http_response_code(401);
    exit('Invalid signature');
}

// Signature is valid – process the webhook safely.</pre>
                </div>
            </section>

            <section id="errors">
                <h2>Errors</h2>
                <p>Betron uses standard HTTP status codes and a consistent JSON structure for error responses.</p>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>200</code>, <code>201</code></td>
                            <td>Successful request / resource created.</td>
                        </tr>
                        <tr>
                            <td><code>400</code></td>
                            <td>Bad request (invalid parameters).</td>
                        </tr>
                        <tr>
                            <td><code>401</code></td>
                            <td>Unauthorized (missing or invalid bearer token).</td>
                        </tr>
                        <tr>
                            <td><code>403</code></td>
                            <td>Forbidden (e.g. blacklisted).</td>
                        </tr>
                        <tr>
                            <td><code>404</code></td>
                            <td>Resource not found.</td>
                        </tr>
                        <tr>
                            <td><code>422</code></td>
                            <td>Validation error.</td>
                        </tr>
                        <tr>
                            <td><code>500</code></td>
                            <td>Internal server error.</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Error response format</h3>
                <div class="code-block">
                    <div class="code-label">Body · JSON (domain / business errors)</div>
<pre>{
  "success": false,
  "code": 400,
  "message": "Minimum amount is 2000",
  "data": []
}</pre>
                </div>
            </section>
        </main>
    </div>
<script>
    // Simple active-state handling for sidebar navigation
    document.addEventListener('DOMContentLoaded', function () {
        var links = Array.prototype.slice.call(document.querySelectorAll('.sidebar .nav-link'));

        function setActiveByHash(hash) {
            if (!hash) {
                hash = window.location.hash || '#introduction';
            }
            links.forEach(function (link) {
                link.classList.remove('active');
            });
            var target = links.find(function (link) {
                return link.getAttribute('href') === hash;
            });
            if (target) {
                target.classList.add('active');
            }
        }

        // Initial state
        setActiveByHash(window.location.hash);

        // Render lucide icons if available
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // On click
        links.forEach(function (link) {
            link.addEventListener('click', function () {
                var hash = this.getAttribute('href');
                setActiveByHash(hash);
            });
        });

        // If hash changes via browser controls
        window.addEventListener('hashchange', function () {
            setActiveByHash(window.location.hash);
        });
    });
</script>
</body>
</html>


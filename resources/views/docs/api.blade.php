<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Betron API Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
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
            height: 100vh;
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
            display: block;
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
            <a href="#introduction" class="nav-link active">Overview</a>
            <a href="#quickstart" class="nav-link">Quickstart</a>
            <a href="#authentication" class="nav-link">Authentication</a>

            <div class="nav-group-title">Resources</div>
            <a href="#banks" class="nav-link">Banks</a>
            <a href="#transactions" class="nav-link">Transactions (Deposit)</a>
            <a href="#withdrawals" class="nav-link">Withdrawals</a>
            <a href="#wallets" class="nav-link">Wallets</a>

            <div class="nav-group-title">Lifecycle</div>
            <a href="#callbacks" class="nav-link">Callbacks</a>
            <a href="#errors" class="nav-link">Errors</a>

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
                <p>All requests to the Betron API must be made over HTTPS and include your API key in the headers.</p>

                <h3>API Key</h3>
                <p class="small">Example headers:</p>
                <div class="code-block">
                    <div class="code-label">Headers</div>
<pre>GET /api/v1/bank HTTP/1.1
Host: betron.org
X-Api-Key: YOUR_API_KEY_HERE
Accept: application/json</pre>
                </div>

                <h3>Optional HMAC Signature</h3>
                <p>For additional security, requests and callbacks can be signed using an HMAC built from your API Secret and the payload.</p>
                <div class="code-block">
                    <div class="code-label">Headers (example)</div>
<pre>X-Api-Key: YOUR_API_KEY_HERE
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
  "data": [
    {
      "id": 1,
      "name": "Bank A",
      "code": "BANKA",
      "min_limit": 10.0,
      "max_limit": 10000.0,
      "currency": "TRY",
      "status": "active"
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
  "data": [
    {
      "id": 5,
      "name": "Bank W",
      "code": "BANKW",
      "min_limit": 50.0,
      "max_limit": 5000.0,
      "currency": "TRY",
      "status": "active"
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
                            <td><code>external_id</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Your internal order or transaction reference.</td>
                        </tr>
                        <tr>
                            <td><code>amount</code></td>
                            <td>number</td>
                            <td>yes</td>
                            <td>Payment amount.</td>
                        </tr>
                        <tr>
                            <td><code>currency</code></td>
                            <td>string</td>
                            <td>yes</td>
                            <td>Currency code, e.g. <code>TRY</code>.</td>
                        </tr>
                        <tr>
                            <td><code>customer_name</code></td>
                            <td>string</td>
                            <td>optional</td>
                            <td>Customer full name.</td>
                        </tr>
                        <tr>
                            <td><code>customer_email</code></td>
                            <td>string</td>
                            <td>optional</td>
                            <td>Customer email address.</td>
                        </tr>
                        <tr>
                            <td><code>bank_id</code></td>
                            <td>integer</td>
                            <td>yes</td>
                            <td>Bank identifier obtained from the banks endpoint.</td>
                        </tr>
                        <tr>
                            <td><code>callback_url</code></td>
                            <td>string</td>
                            <td>recommended</td>
                            <td>URL to receive transaction status updates.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="code-block">
                    <div class="code-label">Request body · JSON</div>
<pre>{
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "bank_id": 1,
  "callback_url": "https://merchant.example.com/betron/transaction-callback"
}</pre>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 201 Created</div>
<pre>{
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "status": "pending",
  "checkout_url": "https://pay.betron.org/checkout/d3a6b9f0-1234-5678-9abc-def012345678"
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
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "status": "success",
  "paid_at": "2026-02-24T12:34:56Z"
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
  "external_id": "WITHDRAW-98765",
  "amount": 1000.0,
  "currency": "TRY",
  "bank_id": 5,
  "account_name": "John Doe",
  "iban": "TR000000000000000000000000",
  "callback_url": "https://merchant.example.com/betron/withdrawal-callback"
}</pre>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 201 Created</div>
<pre>{
  "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
  "external_id": "WITHDRAW-98765",
  "amount": 1000.0,
  "currency": "TRY",
  "status": "processing"
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
  "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
  "external_id": "WITHDRAW-98765",
  "amount": 1000.0,
  "currency": "TRY",
  "status": "completed",
  "completed_at": "2026-02-24T13:45:00Z"
}</pre>
                </div>
            </section>

            <section id="wallets">
                <h2>Wallets</h2>
                <p>Wallet endpoints allow you to view your Betron balances.</p>

                <h3>List wallets</h3>
                <div class="endpoint">
                    <span class="method method-get">GET</span>
                    <span class="path">/api/v1/wallet</span>
                </div>

                <div class="code-block">
                    <div class="code-label">Response · 200 OK</div>
<pre>{
  "data": [
    {
      "id": 1,
      "currency": "TRY",
      "balance": 50000.0,
      "available_balance": 48000.0
    },
    {
      "id": 2,
      "currency": "USD",
      "balance": 10000.0,
      "available_balance": 9500.0
    }
  ]
}</pre>
                </div>
            </section>

            <section id="callbacks">
                <h2>Callbacks</h2>
                <p>Betron sends webhook callbacks to notify you when a transaction or withdrawal status changes.</p>

                <h3>Example callback URLs</h3>
                <ul>
                    <li><code>https://merchant.example.com/betron/transaction-callback</code></li>
                    <li><code>https://merchant.example.com/betron/withdrawal-callback</code></li>
                </ul>

                <h3>Example transaction callback payload</h3>
                <div class="code-block">
                    <div class="code-label">Body · JSON</div>
<pre>{
  "event": "transaction.success",
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "status": "success",
  "signature": "HMAC_SIGNATURE_HERE"
}</pre>
                </div>

                <h3>Verifying callbacks</h3>
                <ul>
                    <li>Read the <code>signature</code> value from the header or payload.</li>
                    <li>Recompute the HMAC using your callback secret key and the received body.</li>
                    <li>Compare your computed signature with the one sent by Betron.</li>
                    <li>Process the callback only if the signatures match.</li>
                </ul>
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
                            <td>Unauthorized (missing or invalid API key).</td>
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
                    <div class="code-label">Body · JSON</div>
<pre>{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The amount field is required."
  }
}</pre>
                </div>
            </section>
        </main>
    </div>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Betron API Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #060714;
            --bg-elevated: #0d1020;
            --accent: #4f46e5;
            --accent-soft: rgba(79, 70, 229, 0.15);
            --accent-border: rgba(129, 140, 248, 0.5);
            --accent-alt: #22c55e;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --border: #1f2933;
            --code-bg: #020617;
            --danger: #f97373;
            --radius-lg: 14px;
            --radius-md: 10px;
            --radius-pill: 999px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", sans-serif;
            background: radial-gradient(circle at top left, #111827, #020617 45%, #000 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .docs-shell {
            display: flex;
            flex: 1;
            max-width: 1240px;
            margin: 24px auto 32px;
            gap: 20px;
            padding: 0 16px;
        }

        header {
            max-width: 1240px;
            margin: 24px auto 0;
            padding: 0 16px;
        }

        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow:
                0 18px 45px rgba(15, 23, 42, 0.8),
                0 0 0 1px rgba(15, 23, 42, 0.8);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: radial-gradient(circle at 0 0, #4f46e5, #22c55e 60%, #0ea5e9 100%);
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.4),
                0 10px 25px rgba(37, 99, 235, 0.6);
            position: relative;
            overflow: hidden;
        }

        .brand-logo::before {
            content: "";
            position: absolute;
            inset: 18%;
            border-radius: 10px;
            border: 2px solid rgba(15, 23, 42, 0.6);
            box-shadow: inset 0 0 10px rgba(15, 23, 42, 0.7);
        }

        .brand-text-main {
            font-weight: 600;
            font-size: 17px;
            letter-spacing: 0.03em;
        }

        .brand-text-sub {
            font-size: 12px;
            color: var(--muted);
        }

        .env-pill {
            border-radius: var(--radius-pill);
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: radial-gradient(circle at 0 0, rgba(79, 70, 229, 0.28), rgba(15, 23, 42, 0.96));
            padding: 4px 10px;
            font-size: 11px;
            color: #e5e7eb;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .env-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.28);
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pill {
            border-radius: var(--radius-pill);
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.96));
            color: var(--muted);
            padding: 6px 12px;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .pill strong {
            color: var(--text);
            font-weight: 500;
        }

        .pill-badge {
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #c7d2fe;
            border: 1px solid var(--accent-border);
        }

        .btn-primary {
            border-radius: var(--radius-pill);
            padding: 7px 14px;
            border: none;
            font-size: 12px;
            font-weight: 500;
            background: radial-gradient(circle at 0 0, #4f46e5, #22c55e);
            color: white;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow:
                0 14px 30px rgba(79, 70, 229, 0.55),
                0 0 0 1px rgba(15, 23, 42, 0.9);
        }

        .btn-primary span.icon {
            font-size: 14px;
        }

        .btn-primary:hover {
            filter: brightness(1.05);
        }

        .layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 20px;
            width: 100%;
        }

        .sidebar {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.99));
            border-radius: var(--radius-lg);
            border: 1px solid rgba(30, 64, 175, 0.5);
            padding: 14px 12px 16px;
            position: sticky;
            top: 16px;
            height: fit-content;
            box-shadow:
                0 18px 40px rgba(15, 23, 42, 0.9),
                0 0 0 1px rgba(15, 23, 42, 1);
        }

        .sidebar-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .nav-section {
            margin-bottom: 12px;
        }

        .nav-section > .nav-heading {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin: 10px 8px 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 9px;
            border-radius: 9px;
            color: #d1d5db;
            font-size: 12px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .nav-link small {
            font-size: 11px;
            color: var(--muted);
        }

        .nav-link:hover {
            background: rgba(31, 41, 55, 0.9);
            border-color: rgba(37, 99, 235, 0.6);
        }

        .nav-link.active {
            background: radial-gradient(circle at 0 0, rgba(79, 70, 229, 0.55), rgba(15, 23, 42, 1));
            border-color: rgba(129, 140, 248, 0.8);
            box-shadow:
                0 8px 20px rgba(79, 70, 229, 0.65),
                0 0 0 1px rgba(15, 23, 42, 0.8);
        }

        .nav-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.9);
        }

        .nav-link.active .nav-dot {
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.28);
        }

        .content {
            background: linear-gradient(150deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.99));
            border-radius: var(--radius-lg);
            border: 1px solid rgba(30, 64, 175, 0.55);
            padding: 18px 22px 22px;
            box-shadow:
                0 20px 50px rgba(15, 23, 42, 0.95),
                0 0 0 1px rgba(15, 23, 42, 1);
            overflow: hidden;
        }

        .hero-eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .hero-title {
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin: 0 0 6px;
        }

        .hero-subtitle {
            font-size: 13px;
            color: var(--muted);
            max-width: 560px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 1.1fr);
            gap: 14px;
            margin-top: 16px;
            margin-bottom: 18px;
        }

        .hero-panel {
            border-radius: var(--radius-md);
            border: 1px solid rgba(31, 41, 55, 0.9);
            background: radial-gradient(circle at 0 0, rgba(79, 70, 229, 0.22), rgba(15, 23, 42, 0.96));
            padding: 10px 12px;
            position: relative;
            overflow: hidden;
        }

        .hero-panel.secondary {
            background: radial-gradient(circle at 100% 0, rgba(16, 185, 129, 0.22), rgba(15, 23, 42, 0.96));
        }

        .hero-panel-title {
            font-size: 12px;
            color: #e5e7eb;
            margin-bottom: 4px;
        }

        .hero-panel-body {
            font-size: 11px;
            color: var(--muted);
        }

        .tag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .tag {
            font-size: 10px;
            padding: 3px 7px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            color: #cbd5f5;
            background: rgba(15, 23, 42, 0.9);
        }

        .tag.accent {
            border-color: var(--accent-border);
            background: var(--accent-soft);
            color: #e0e7ff;
        }

        .section {
            margin: 18px 0 0;
            padding-top: 18px;
            border-top: 1px solid rgba(31, 41, 55, 0.9);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 500;
        }

        .section-subtitle {
            font-size: 12px;
            color: var(--muted);
        }

        .section-kicker {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.3fr);
            gap: 16px;
        }

        .card {
            border-radius: var(--radius-md);
            border: 1px solid rgba(31, 41, 55, 0.9);
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 1));
            padding: 10px 11px 11px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .card-title {
            font-size: 13px;
            font-weight: 500;
        }

        .badge {
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            color: #e5e7eb;
        }

        .badge.method-get {
            border-color: #22c55e;
            color: #bbf7d0;
        }

        .badge.method-post {
            border-color: #38bdf8;
            color: #bae6fd;
        }

        .badge.method-put {
            border-color: #a855f7;
            color: #e9d5ff;
        }

        .badge.method-status {
            border-color: #f97316;
            color: #fed7aa;
        }

        .card-body {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .endpoint {
            font-family: ui-monospace, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 11px;
            padding: 5px 7px;
            border-radius: 7px;
            background: var(--code-bg);
            border: 1px solid rgba(15, 23, 42, 1);
            color: #e5e7eb;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .endpoint span.method {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 999px;
            font-weight: 500;
        }

        .endpoint span.method.get {
            background: rgba(34, 197, 94, 0.1);
            color: #bbf7d0;
        }

        .endpoint span.method.post {
            background: rgba(56, 189, 248, 0.1);
            color: #bae6fd;
        }

        .endpoint span.method.put {
            background: rgba(168, 85, 247, 0.1);
            color: #e9d5ff;
        }

        .endpoint span.method.status {
            background: rgba(249, 115, 22, 0.12);
            color: #fed7aa;
        }

        .endpoint-path {
            color: #e5e7eb;
        }

        .code-block {
            margin-top: 7px;
            border-radius: 9px;
            background: radial-gradient(circle at 0 0, rgba(15, 23, 42, 1), rgba(3, 7, 18, 1));
            border: 1px solid rgba(15, 23, 42, 1);
            padding: 8px 9px;
            font-family: ui-monospace, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 11px;
            color: #e5e7eb;
            overflow-x: auto;
        }

        pre {
            margin: 0;
            white-space: pre;
        }

        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 10px;
        }

        .muted {
            color: var(--muted);
        }

        .pill-inline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            font-size: 10px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(15, 23, 42, 0.9);
        }

        .pill-inline strong {
            color: #e5e7eb;
        }

        .list {
            margin: 6px 0 0 0;
            padding-left: 16px;
            font-size: 11px;
            color: var(--muted);
        }

        .list li + li {
            margin-top: 2px;
        }

        .badge-status {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            color: #e5e7eb;
        }

        .badge-status.green {
            border-color: #22c55e;
            color: #bbf7d0;
        }

        .badge-status.amber {
            border-color: #facc15;
            color: #fef3c7;
        }

        .badge-status.red {
            border-color: #f97373;
            color: #fecaca;
        }

        .http-table {
            margin-top: 6px;
            font-size: 11px;
            border-radius: 8px;
            border: 1px solid rgba(31, 41, 55, 0.9);
            overflow: hidden;
        }

        .http-row {
            display: grid;
            grid-template-columns: 80px minmax(0, 1fr);
            border-top: 1px solid rgba(31, 41, 55, 0.9);
        }

        .http-row:first-child {
            border-top: none;
        }

        .http-cell-label {
            padding: 6px 8px;
            background: rgba(15, 23, 42, 0.96);
            color: #9ca3af;
            border-right: 1px solid rgba(31, 41, 55, 0.9);
        }

        .http-cell-value {
            padding: 6px 8px;
            color: #e5e7eb;
        }

        @media (max-width: 1000px) {
            .docs-shell {
                margin-top: 18px;
            }

            .layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .sidebar {
                position: static;
                order: -1;
            }

            .hero-grid,
            .grid-2 {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 640px) {
            .top-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .top-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="top-nav">
            <div class="brand">
                <div class="brand-logo"></div>
                <div>
                    <div class="brand-text-main">Betron</div>
                    <div class="brand-text-sub">Unified Payments API · v1</div>
                </div>
            </div>
            <div class="top-actions">
                <div class="pill">
                    <span class="pill-badge">Base URL</span>
                    <span>https://betron.org/api/v1</span>
                </div>
                <div class="env-pill">
                    <span class="env-dot"></span>
                    <span>Production</span>
                </div>
                <button class="btn-primary" type="button">
                    <span class="icon">⚡</span>
                    <span>Contact Integration Support</span>
                </button>
            </div>
        </div>
    </header>

    <main class="docs-shell">
        <div class="layout">
            <aside class="sidebar">
                <div class="sidebar-title">Documentation</div>

                <div class="nav-section">
                    <div class="nav-heading">Overview</div>
                    <a href="#quickstart" class="nav-link active">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Quickstart</div>
                            <small>How to start integrating Betron</small>
                        </div>
                    </a>
                    <a href="#authentication" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Authentication & Security</div>
                            <small>API keys and signatures</small>
                        </div>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-heading">API Resources</div>
                    <a href="#banks" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Banks</div>
                            <small>Available banks for deposit & withdrawal</small>
                        </div>
                    </a>
                    <a href="#transactions" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Transactions (Deposit)</div>
                            <small>Create and track deposits</small>
                        </div>
                    </a>
                    <a href="#withdrawals" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Withdrawals</div>
                            <small>Send funds and track status</small>
                        </div>
                    </a>
                    <a href="#wallets" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Wallets</div>
                            <small>View your Betron balances</small>
                        </div>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-heading">Lifecycle</div>
                    <a href="#callbacks" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Callbacks</div>
                            <small>Webhook events and verification</small>
                        </div>
                    </a>
                    <a href="#errors" class="nav-link">
                        <span class="nav-dot"></span>
                        <div>
                            <div>Errors</div>
                            <small>HTTP status codes & payloads</small>
                        </div>
                    </a>
                </div>
            </aside>

            <section class="content">
                <div id="quickstart">
                    <div class="hero-eyebrow">Betron API · v1</div>
                    <h1 class="hero-title">Unified Payments API Documentation</h1>
                    <p class="hero-subtitle">
                        Integrate card and bank payments, withdrawals, and wallet operations using a single, consistent API.
                        This guide covers all public endpoints exposed under <strong>https://betron.org/api/v1</strong>.
                    </p>

                    <div class="hero-grid">
                        <div class="hero-panel">
                            <div class="hero-panel-title">Quickstart Flow</div>
                            <div class="hero-panel-body">
                                <ol class="list">
                                    <li>Obtain your <strong>API Key</strong> and <strong>API Secret</strong> from the Betron team.</li>
                                    <li>Whitelist your server IPs and configure your <strong>callback URLs</strong>.</li>
                                    <li>Use <code>/bank</code> to fetch available banks and limits.</li>
                                    <li>Create a <strong>deposit transaction</strong> with <code>POST /transaction</code>.</li>
                                    <li>Redirect the customer to the returned <code>checkout_url</code>.</li>
                                    <li>Listen for status updates via webhook <strong>callbacks</strong>.</li>
                                </ol>
                                <div class="tag-row">
                                    <span class="tag accent">Base URL · https://betron.org/api/v1</span>
                                    <span class="tag">Format · JSON over HTTPS</span>
                                </div>
                            </div>
                        </div>
                        <div class="hero-panel secondary">
                            <div class="hero-panel-title">Requirements</div>
                            <div class="hero-panel-body">
                                <ul class="list">
                                    <li>Stable HTTPS endpoint to receive callbacks.</li>
                                    <li>Server clock synchronized (for signature validation).</li>
                                    <li>Ability to securely store API credentials.</li>
                                </ul>
                                <div class="tag-row">
                                    <span class="tag">Authentication · API Key</span>
                                    <span class="tag">Security · Optional HMAC signatures</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="authentication" class="section">
                    <div class="section-kicker">Security</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Authentication & Request Signing</div>
                            <div class="section-subtitle">
                                All endpoints under <code>/api/v1</code> are protected by API key authentication and additional security checks.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">API Key Authentication</div>
                                <span class="badge">Required</span>
                            </div>
                            <div class="card-body">
                                Every request must include your Betron API key in the headers.
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>HTTP Headers</span>
                                    <span class="muted">Example</span>
                                </div>
<pre>GET /api/v1/bank HTTP/1.1
Host: betron.org
X-Api-Key: YOUR_API_KEY_HERE
Accept: application/json</pre>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Optional HMAC Signatures</div>
                                <span class="badge">Recommended</span>
                            </div>
                            <div class="card-body">
                                For higher security, requests and callbacks can be signed using an HMAC built from your secret key and payload.
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>HTTP Headers</span>
                                    <span class="muted">Example</span>
                                </div>
<pre>X-Api-Key: YOUR_API_KEY_HERE
X-Signature: HMAC_SIGNATURE_HERE</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="banks" class="section">
                    <div class="section-kicker">Resource</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Banks · Available Funding & Payout Banks</div>
                            <div class="section-subtitle">
                                Fetch active banks and their limits for both deposit and withdrawal operations.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">List Banks for Deposit</div>
                                <span class="badge method-get">GET</span>
                            </div>
                            <div class="card-body">
                                Returns all active banks that can be used when creating <strong>deposit transactions</strong>.
                            </div>
                            <div class="endpoint">
                                <span class="method get">GET</span>
                                <span class="endpoint-path">/api/v1/bank</span>
                            </div>
                            <div class="endpoint" style="margin-top:4px;">
                                <span class="method get">GET</span>
                                <span class="endpoint-path">/api/v1/bank/transaction</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Response · 200 OK</span>
                                </div>
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
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">List Banks for Withdrawal</div>
                                <span class="badge method-get">GET</span>
                            </div>
                            <div class="card-body">
                                Returns all banks available for <strong>withdrawal</strong> operations.
                            </div>
                            <div class="endpoint">
                                <span class="method get">GET</span>
                                <span class="endpoint-path">/api/v1/bank/withdrawal</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Response · 200 OK</span>
                                </div>
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
                        </div>
                    </div>
                </div>

                <div id="transactions" class="section">
                    <div class="section-kicker">Resource</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Transactions · Deposit</div>
                            <div class="section-subtitle">
                                Create and manage deposit transactions for your customers.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Create Transaction</div>
                                <span class="badge method-post">POST</span>
                            </div>
                            <div class="card-body">
                                Starts a new <strong>deposit transaction</strong>. Use the returned <code>uuid</code> to track the status.
                            </div>
                            <div class="endpoint">
                                <span class="method post">POST</span>
                                <span class="endpoint-path">/api/v1/transaction</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Request Body</span>
                                    <span class="muted">JSON</span>
                                </div>
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
                            <div class="code-block" style="margin-top:6px;">
                                <div class="code-header">
                                    <span>Response · 201 Created</span>
                                </div>
<pre>{
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "status": "pending",
  "checkout_url": "https://pay.betron.org/checkout/d3a6b9f0-1234-5678-9abc-def012345678"
}</pre>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Update & Status</div>
                                <span class="badge">PUT / GET</span>
                            </div>
                            <div class="card-body">
                                Update a transaction (for example to cancel) or query its current status.
                            </div>
                            <div class="endpoint">
                                <span class="method put">PUT</span>
                                <span class="endpoint-path">/api/v1/transaction/{uuid}</span>
                            </div>
                            <div class="endpoint" style="margin-top:4px;">
                                <span class="method status">GET</span>
                                <span class="endpoint-path">/api/v1/transaction/{uuid}/status</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Update Request (Cancel)</span>
                                </div>
<pre>{
  "status": "canceled",
  "reason": "Customer canceled the payment"
}</pre>
                            </div>
                            <div class="code-block" style="margin-top:6px;">
                                <div class="code-header">
                                    <span>Status Response · 200 OK</span>
                                </div>
<pre>{
  "uuid": "d3a6b9f0-1234-5678-9abc-def012345678",
  "external_id": "ORDER-12345",
  "amount": 250.0,
  "currency": "TRY",
  "status": "success",
  "paid_at": "2026-02-24T12:34:56Z"
}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="withdrawals" class="section">
                    <div class="section-kicker">Resource</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Withdrawals</div>
                            <div class="section-subtitle">
                                Create withdrawal requests to send funds and track their lifecycle.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Create Withdrawal</div>
                                <span class="badge method-post">POST</span>
                            </div>
                            <div class="card-body">
                                Initiates a payout to a customer or destination account.
                            </div>
                            <div class="endpoint">
                                <span class="method post">POST</span>
                                <span class="endpoint-path">/api/v1/withdrawal</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Request Body</span>
                                    <span class="muted">JSON</span>
                                </div>
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
                            <div class="code-block" style="margin-top:6px;">
                                <div class="code-header">
                                    <span>Response · 201 Created</span>
                                </div>
<pre>{
  "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
  "external_id": "WITHDRAW-98765",
  "amount": 1000.0,
  "currency": "TRY",
  "status": "processing"
}</pre>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Withdrawal Status</div>
                                <span class="badge method-status">GET</span>
                            </div>
                            <div class="card-body">
                                Query the current status of a withdrawal using its UUID.
                            </div>
                            <div class="endpoint">
                                <span class="method status">GET</span>
                                <span class="endpoint-path">/api/v1/withdrawal/{uuid}/status</span>
                            </div>
                            <div class="code-block">
                                <div class="code-header">
                                    <span>Status Response · 200 OK</span>
                                </div>
<pre>{
  "uuid": "a1b2c3d4-5678-90ab-cdef-1234567890ab",
  "external_id": "WITHDRAW-98765",
  "amount": 1000.0,
  "currency": "TRY",
  "status": "completed",
  "completed_at": "2026-02-24T13:45:00Z"
}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="wallets" class="section">
                    <div class="section-kicker">Resource</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Wallets</div>
                            <div class="section-subtitle">
                                View your Betron wallets and available balances.
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">List Wallets</div>
                            <span class="badge method-get">GET</span>
                        </div>
                        <div class="card-body">
                            Returns all wallets and their balances linked to your merchant account.
                        </div>
                        <div class="endpoint">
                            <span class="method get">GET</span>
                            <span class="endpoint-path">/api/v1/wallet</span>
                        </div>
                        <div class="code-block">
                            <div class="code-header">
                                <span>Response · 200 OK</span>
                            </div>
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
                    </div>
                </div>

                <div id="callbacks" class="section">
                    <div class="section-kicker">Lifecycle</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Callbacks · Webhook Notifications</div>
                            <div class="section-subtitle">
                                Receive real-time notifications when transaction or withdrawal statuses change.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Callback Setup</div>
                                <span class="badge">HTTPS</span>
                            </div>
                            <div class="card-body">
                                Provide Betron with your HTTPS callback URLs during onboarding.
                            </div>
                            <ul class="list">
                                <li><code>https://merchant.example.com/betron/transaction-callback</code></li>
                                <li><code>https://merchant.example.com/betron/withdrawal-callback</code></li>
                            </ul>
                            <div class="tag-row" style="margin-top:8px;">
                                <span class="tag">Retries on failure</span>
                                <span class="tag">Signed payloads</span>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Example Callback Payload</div>
                                <span class="badge-status green">transaction.success</span>
                            </div>
                            <div class="card-body">
                                Example payload for a successful transaction webhook.
                            </div>
                            <div class="code-block">
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
                            <div class="code-block" style="margin-top:6px;">
                                <div class="code-header">
                                    <span>Verification Steps</span>
                                </div>
<pre>1. Read the <signature> field or header.
2. Recompute HMAC(payload, CALLBACK_SECRET_KEY).
3. Compare computed signature with received signature.
4. Accept the callback only if they match.</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="errors" class="section">
                    <div class="section-kicker">Reference</div>
                    <div class="section-header">
                        <div>
                            <div class="section-title">Errors & HTTP Status Codes</div>
                            <div class="section-subtitle">
                                Standardized HTTP status codes and error response format used across all endpoints.
                            </div>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">HTTP Status Codes</div>
                            </div>
                            <div class="http-table">
                                <div class="http-row">
                                    <div class="http-cell-label">2xx</div>
                                    <div class="http-cell-value">
                                        <span class="badge-status green">200</span> Success responses<br>
                                        <span class="badge-status green">201</span> Resource created
                                    </div>
                                </div>
                                <div class="http-row">
                                    <div class="http-cell-label">4xx</div>
                                    <div class="http-cell-value">
                                        <span class="badge-status amber">400</span> Bad request (invalid parameters)<br>
                                        <span class="badge-status amber">401</span> Unauthorized (missing/invalid API key)<br>
                                        <span class="badge-status amber">403</span> Forbidden (blacklisted / not allowed)<br>
                                        <span class="badge-status amber">404</span> Not found<br>
                                        <span class="badge-status amber">422</span> Validation error
                                    </div>
                                </div>
                                <div class="http-row">
                                    <div class="http-cell-label">5xx</div>
                                    <div class="http-cell-value">
                                        <span class="badge-status red">500</span> Internal server error
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Error Response Body</div>
                            </div>
                            <div class="card-body">
                                All error responses follow a consistent JSON structure:
                            </div>
                            <div class="code-block">
<pre>{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The amount field is required."
  }
}</pre>
                            </div>
                            <p class="card-body" style="margin-top:6px;">
                                Use <code>error.code</code> for programmatic handling and <code>error.message</code> for logging or user-facing messages.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>


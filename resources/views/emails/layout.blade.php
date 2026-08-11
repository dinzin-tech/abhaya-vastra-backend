<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $emailTitle ?? 'Abhaya Vastra' }}</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      background-color: #f5f0eb;
      font-family: 'Jost', Arial, sans-serif;
      color: #1a1a1a;
      -webkit-font-smoothing: antialiased;
    }

    .email-wrapper {
      max-width: 620px;
      margin: 40px auto;
      background: #ffffff;
    }

    /* ── HEADER ── */
    .email-header {
      background: #1a1a1a;
      padding: 36px 40px 28px;
      text-align: center;
    }
    .email-header .brand-name {
      font-family: 'Cormorant Garamond', Georgia, serif;
      font-size: 28px;
      font-weight: 600;
      letter-spacing: 0.25em;
      color: #c9a96e;
      text-transform: uppercase;
      text-decoration: none;
    }
    .email-header .brand-tagline {
      font-family: 'Jost', sans-serif;
      font-size: 10px;
      letter-spacing: 0.3em;
      color: #888;
      margin-top: 6px;
      text-transform: uppercase;
    }

    /* ── HERO BANNER ── */
    .email-hero {
      background: {{ $heroBg ?? '#1a1a1a' }};
      padding: 40px 40px 36px;
      text-align: center;
      border-bottom: 3px solid #c9a96e;
    }
    .email-hero .status-icon {
      font-size: 42px;
      margin-bottom: 14px;
      display: block;
    }
    .email-hero .email-title {
      font-family: 'Cormorant Garamond', Georgia, serif;
      font-size: 30px;
      font-weight: 600;
      color: #ffffff;
      letter-spacing: 0.05em;
      line-height: 1.3;
    }
    .email-hero .email-subtitle {
      font-size: 13px;
      color: #aaa;
      margin-top: 8px;
      letter-spacing: 0.05em;
      font-weight: 300;
    }

    /* ── BODY ── */
    .email-body {
      padding: 40px 40px 32px;
    }

    .greeting {
      font-size: 15px;
      color: #444;
      margin-bottom: 20px;
      line-height: 1.6;
    }
    .greeting strong {
      color: #1a1a1a;
      font-weight: 600;
    }

    /* ── ORDER ID BADGE ── */
    .order-badge {
      display: inline-block;
      background: #fdf6ec;
      border: 1px solid #c9a96e;
      border-left: 4px solid #c9a96e;
      padding: 12px 20px;
      margin: 4px 0 28px;
      border-radius: 2px;
    }
    .order-badge .label {
      font-size: 10px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: #888;
      font-weight: 500;
    }
    .order-badge .value {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      font-weight: 600;
      color: #1a1a1a;
      letter-spacing: 0.08em;
    }

    /* ── SECTION HEADING ── */
    .section-title {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: #c9a96e;
      margin-bottom: 14px;
      padding-bottom: 8px;
      border-bottom: 1px solid #f0e8dc;
    }

    /* ── ITEMS TABLE ── */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 28px;
    }
    .items-table .item-row {
      border-bottom: 1px solid #f5f0eb;
    }
    .items-table .item-row:last-child {
      border-bottom: none;
    }
    .items-table .item-name {
      padding: 12px 0;
      font-size: 13px;
      color: #1a1a1a;
      font-weight: 500;
    }
    .items-table .item-meta {
      font-size: 11px;
      color: #888;
      font-weight: 300;
      margin-top: 2px;
    }
    .items-table .item-price {
      padding: 12px 0;
      text-align: right;
      font-size: 13px;
      font-weight: 600;
      color: #1a1a1a;
      white-space: nowrap;
    }

    /* ── TOTALS ── */
    .totals-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 28px;
    }
    .totals-table td {
      padding: 7px 0;
      font-size: 13px;
    }
    .totals-table .label-col {
      color: #666;
      font-weight: 400;
    }
    .totals-table .value-col {
      text-align: right;
      color: #1a1a1a;
      font-weight: 500;
    }
    .totals-table .total-row td {
      padding-top: 12px;
      border-top: 1px solid #1a1a1a;
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
    }
    .totals-table .discount-row .value-col {
      color: #4caf7d;
    }

    /* ── INFO GRID ── */
    .info-grid {
      display: table;
      width: 100%;
      margin-bottom: 28px;
    }
    .info-cell {
      display: table-cell;
      width: 50%;
      vertical-align: top;
      padding-right: 20px;
    }
    .info-cell:last-child { padding-right: 0; }
    .info-cell .info-label {
      font-size: 10px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: #c9a96e;
      font-weight: 600;
      margin-bottom: 6px;
    }
    .info-cell .info-value {
      font-size: 13px;
      color: #444;
      line-height: 1.6;
      font-weight: 400;
    }

    /* ── STATUS BADGE ── */
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .status-processing { background: #fff3cd; color: #856404; }
    .status-shipped    { background: #cfe2ff; color: #084298; }
    .status-delivered  { background: #d1e7dd; color: #0f5132; }
    .status-cancelled  { background: #f8d7da; color: #842029; }
    .status-pending    { background: #e2e3e5; color: #41464b; }

    /* ── TRACKING BOX ── */
    .tracking-box {
      background: #f5f9ff;
      border: 1px solid #cfe2ff;
      border-left: 4px solid #3b82f6;
      padding: 16px 20px;
      margin-bottom: 28px;
      border-radius: 2px;
    }
    .tracking-box .tracking-label {
      font-size: 10px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: #3b82f6;
      font-weight: 600;
      margin-bottom: 6px;
    }
    .tracking-box .awb {
      font-size: 15px;
      font-weight: 600;
      color: #1a1a1a;
      font-family: monospace;
      letter-spacing: 0.1em;
    }
    .tracking-box .track-link {
      display: inline-block;
      margin-top: 10px;
      color: #3b82f6;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      letter-spacing: 0.05em;
    }

    /* ── CTA BUTTON ── */
    .cta-wrapper {
      text-align: center;
      margin: 28px 0;
    }
    .cta-button {
      display: inline-block;
      background: #1a1a1a;
      color: #c9a96e !important;
      text-decoration: none;
      padding: 14px 36px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      border: 1px solid #1a1a1a;
    }
    .cta-button:hover { background: #333; }

    /* ── DIVIDER ── */
    .divider {
      border: none;
      border-top: 1px solid #f0e8dc;
      margin: 24px 0;
    }

    /* ── ALERT BOX ── */
    .alert-box {
      padding: 16px 20px;
      margin-bottom: 24px;
      border-radius: 2px;
    }
    .alert-success { background: #ecfdf5; border-left: 4px solid #10b981; }
    .alert-warning { background: #fef9ec; border-left: 4px solid #f59e0b; }
    .alert-danger  { background: #fef2f2; border-left: 4px solid #ef4444; }
    .alert-box p { font-size: 13px; color: #444; line-height: 1.6; }

    /* ── FOOTER ── */
    .email-footer {
      background: #1a1a1a;
      padding: 32px 40px;
      text-align: center;
    }
    .email-footer .footer-brand {
      font-family: 'Cormorant Garamond', serif;
      font-size: 16px;
      color: #c9a96e;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      margin-bottom: 12px;
    }
    .email-footer .footer-links {
      margin-bottom: 14px;
    }
    .email-footer .footer-links a {
      color: #888;
      text-decoration: none;
      font-size: 11px;
      letter-spacing: 0.1em;
      margin: 0 10px;
    }
    .email-footer .footer-address {
      font-size: 11px;
      color: #555;
      line-height: 1.7;
      letter-spacing: 0.02em;
    }
    .email-footer .footer-note {
      font-size: 10px;
      color: #444;
      margin-top: 14px;
      line-height: 1.6;
    }
    .gold-line {
      height: 2px;
      background: linear-gradient(to right, transparent, #c9a96e, transparent);
      margin: 20px 0;
    }

    @media only screen and (max-width: 600px) {
      .email-wrapper { margin: 0; }
      .email-body { padding: 28px 24px; }
      .email-header { padding: 28px 24px 20px; }
      .email-hero { padding: 28px 24px; }
      .email-footer { padding: 24px; }
      .info-grid { display: block; }
      .info-cell { display: block; width: 100%; padding-right: 0; margin-bottom: 18px; }
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    @yield('content')

    <!-- FOOTER -->
    <div class="email-footer">
      <div class="footer-brand">Abhaya Vastra</div>
      <div class="gold-line"></div>
      <div class="footer-links">
        <a href="{{ config('app.frontend_url') }}">Shop</a>
        <a href="{{ config('app.frontend_url') }}/orders">My Orders</a>
        <a href="{{ config('app.frontend_url') }}/contact">Contact</a>
      </div>
      <div class="footer-address">
        Abhaya Vastra<br/>
        Bengaluru, Karnataka, India<br/>
        <a href="mailto:info@abhayavastra.store" style="color:#c9a96e;text-decoration:none;">info@abhayavastra.store</a>
      </div>
      <div class="footer-note">
        You are receiving this email because you placed an order with Abhaya Vastra.<br/>
        © {{ date('Y') }} Abhaya Vastra. All rights reserved.
      </div>
    </div>
  </div>
</body>
</html>

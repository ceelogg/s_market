<?php
// Include database configuration
require_once 'config.php';

// Your PHP logic here (you can add your existing database queries)

// Close connection (will be handled automatically at script end)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>S-Market - AI Recommendations</title>
  <!-- Icons & Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Charts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="airecnav.css">
</head>
<body>
  <div class="container">
    <?php include 'globalsidebar.php'; ?>

    <div class="main-content">
      <header class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
          </svg>
          <input id="q" placeholder="Search product, branch, or reason…" aria-label="Search" />
        </div>
        <button class="btn ghost" id="retrainBtn" title="Retrain model">Retrain</button>
        <button class="btn" id="refreshBtn" title="Refresh data">Refresh</button>
      </header>

      <!-- KPI Cards -->
      <section class="grid kpis" aria-label="Key metrics">
        <article class="card kpi" aria-live="polite">
          <div class="split">
            <h3>Total Sales (30d)</h3>
            <span class="chip">₱</span>
          </div>
          <div class="value" id="kpiSales">—</div>
          <div class="delta up" id="kpiSalesDelta">—</div>
        </article>
        <article class="card kpi">
          <div class="split">
            <h3>Units Sold (30d)</h3>
            <span class="chip">Qty</span>
          </div>
          <div class="value" id="kpiUnits">—</div>
          <div class="delta up" id="kpiUnitsDelta">—</div>
        </article>
        <article class="card kpi">
          <div class="split">
            <h3>Recommendation Lift</h3>
            <span class="chip">AI</span>
          </div>
          <div class="value" id="kpiLift">—</div>
          <div class="delta up" id="kpiLiftDelta">—</div>
        </article>
        <article class="card kpi">
          <div class="split">
            <h3>Stock Risk (7d)</h3>
            <span class="chip">⚠︎</span>
          </div>
          <div class="value" id="kpiRisk">—</div>
          <div class="delta down" id="kpiRiskDelta">—</div>
        </article>
      </section>

      <!-- Panels: Chart + Recommendations -->
      <section class="grid panels" style="margin-top:18px;">
        <article class="card">
          <div class="split" style="margin-bottom: 12px;">
            <h3>Sales Overview</h3>
            <div class="legend">
              <span><span class="dot" style="background:#3b82f6"></span>Sales</span>
              <span><span class="dot" style="background:#06b6d4"></span>Units</span>
            </div>
          </div>
          <canvas id="salesChart" height="120"></canvas>
        </article>

        <article class="card">
          <div class="split" style="margin-bottom: 12px;">
            <h3>Top Products (Forecast)</h3>
            <span class="chip">Next 7 days</span>
          </div>
          <canvas id="topChart" height="120"></canvas>
        </article>
      </section>

      <section class="card" style="margin-top:18px;">
        <div class="split" style="margin-bottom: 12px; align-items: end;">
          <h3>AI Recommendations</h3>
          <div class="reco-toolbar">
            <div class="field">
              <label for="branchSel">Branch</label>
              <select id="branchSel" aria-label="Branch filter">
                <option value="">All</option>
                <option>CTA</option>
                <option>DM</option>
                <option>North</option>
                <option>East</option>
                <option>West</option>
              </select>
            </div>
            <div class="field">
              <label for="sizeSel">Product</label>
              <select id="sizeSel" aria-label="Product size">
                <option value="">All</option>
                <option>Small Tub</option>
                <option>Big Tub</option>
              </select>
            </div>
            <div class="field">
              <label for="topupModeSel">Top‑Up</label>
              <select id="topupModeSel" aria-label="Top-up mode">
                <option value="product" selected>Per Product</option>
                <option value="wholesale">Per Wholesale</option>
              </select>
            </div>
            <div class="field">
              <label for="monthSel">Month</label>
              <select id="monthSel" aria-label="Month selector"></select>
            </div>
            <div class="field">
              <label for="minScore">Min Score</label>
              <input id="minScore" type="number" step="1" min="0" max="100" value="60" style="width:90px" />
            </div>
            <button class="btn ghost" id="exportBtn">Export CSV</button>
          </div>
        </div>

        <div style="overflow:auto;">
          <table aria-label="AI recommendations table" id="recoTable">
            <thead>
              <tr>
                <th>Branch</th>
                <th>Product</th>
                <th>Base Price</th>
                <th>Top‑Up</th>
                <th>Final Price</th>
                <th>Pred Units</th>
                <th>Pred Revenue</th>
                <th>Score</th>
                <th>Reason</th>
                <th style="text-align:right">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </section>

      <footer>
        <div>© <span id="yr"></span> S-Market AI. All rights reserved.</div>
        <div>Blue & white theme • Built for XGBoost integration</div>
      </footer>
    </div>
  </div>

  <script src="airecnav.js"></script>
</body>
</html>
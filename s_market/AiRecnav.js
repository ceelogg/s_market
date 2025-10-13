// ------- Mock Data & Helpers (replace with real API calls) -------
const currency = n => new Intl.NumberFormat('en-PH',{style:'currency', currency:'PHP', maximumFractionDigits:0}).format(n);
const pct = x => `${(x*100).toFixed(1)}%`;

function randomWalk(len, start=200000) {
  let v = start; const out = [];
  for (let i=0;i<len;i++){ v += (Math.random()-0.4)*15000; out.push(Math.max(0, Math.round(v))); }
  return out;
}

function getRecommendations() {
  // Mama Coco wholesale yogurt logic
  const PRICES = { 'Small Tub': 150, 'Big Tub': 280 };
  const BRANCHES = ['CTA','DM','North','East','West'];
  const TOPUP = { 'CTA': 0.12, 'DM': 0.15, 'default': 0.10 }; // others 10%

  // Seasonal multipliers by month (1..12)
  const season = {1:0.95,2:0.96,3:1.00,4:1.04,5:1.08,6:1.12,7:1.10,8:1.02,9:0.98,10:1.03,11:1.10,12:1.20};
  // Branch demand bias
  const branchBias = { 'CTA':1.05, 'DM':1.10, 'North':0.98, 'East':1.00, 'West':0.97 };

  const month = Number(document.getElementById('monthSel').value || (new Date().getMonth()+1));
  const sizeFilter = document.getElementById('sizeSel')?.value || '';
  const topupMode = document.getElementById('topupModeSel')?.value || 'product'; // 'product' | 'wholesale'

  // Baseline units before adjustments (mock; replace with model output)
  const baseUnits = { 'Small Tub': 90, 'Big Tub': 60 };

  const recs = [];
  for (const b of BRANCHES) {
    for (const size of ['Small Tub','Big Tub']) {
      if (sizeFilter && size !== sizeFilter) continue;

      const basePrice = PRICES[size];
      const t = (TOPUP[b] ?? TOPUP.default);

      // Per-product top-up raises item price; wholesale applies to the whole order value
      const perProductPrice = Math.round(basePrice * (1 + (topupMode === 'product' ? t : 0)));
      const finalPrice = perProductPrice;

      // Price elasticity (higher price → slightly fewer units)
      const priceElasticity = 0.15;
      const priceFactor = Math.max(0.7, 1 - ((perProductPrice - basePrice)/basePrice) * priceElasticity);

      const daily = baseUnits[size] * (season[month] || 1) * (branchBias[b] || 1) * priceFactor;
      const units = Math.max(0, Math.round(daily));

      const gross = finalPrice * units;
      const revenue = Math.round(topupMode === 'wholesale' ? gross * (1 + t) : gross);

      const marginRate = (topupMode === 'product') ? t : ((revenue - (basePrice*units)) / Math.max(1, revenue));
      const score = Math.min(100, Math.round(55 + 25*(season[month]||1) + 12*marginRate));
      const reason = `${(season[month]*100).toFixed(0)}% seasonal • ${(branchBias[b]*100).toFixed(0)}% branch demand • ${Math.round(t*100)}% top‑up (${topupMode})`;

      recs.push({ branch:b, product:size, basePrice, topup:t, finalPrice, units, revenue, score, reason });
    }
  }
  return recs.sort((a,b)=> b.score - a.score);
}

// ------- KPIs -------
function loadKpis() {
  const sales = randomWalk(30, 250000).reduce((a,b)=>a+b,0);
  const units = Math.round(sales/280);
  const lift = 0.124; // 12.4% uplift (mock)
  const risk = 0.08;  // 8% at-risk SKUs
  document.getElementById('kpiSales').textContent = currency(sales);
  document.getElementById('kpiSalesDelta').textContent = '+3.1% vs prev 30d';
  document.getElementById('kpiUnits').textContent = units.toLocaleString();
  document.getElementById('kpiUnitsDelta').textContent = '+2.2% vs prev 30d';
  document.getElementById('kpiLift').textContent = pct(lift);
  document.getElementById('kpiLiftDelta').textContent = 'Model uplift estimate';
  document.getElementById('kpiRisk').textContent = pct(risk);
  document.getElementById('kpiRiskDelta').textContent = 'Low stock risk';
  document.getElementById('lastTrained').textContent = new Date().toLocaleString();
}

// ------- Charts -------
let salesChart, topChart;
function loadCharts() {
  const labels = Array.from({length: 14}, (_,i) => new Date(Date.now()- (13-i)*86400000)).map(d=>d.toLocaleDateString());
  const seriesSales = randomWalk(14, 220000);
  const seriesUnits = seriesSales.map(v => Math.round(v/300));

  const ctx1 = document.getElementById('salesChart').getContext('2d');
  if (salesChart) salesChart.destroy();
  salesChart = new Chart(ctx1, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label:'Sales', 
          data: seriesSales, 
          borderWidth: 2, 
          tension: .35,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)'
        },
        {
          label:'Units', 
          data: seriesUnits, 
          borderWidth: 2, 
          tension: .35,
          borderColor: '#06b6d4',
          backgroundColor: 'rgba(6, 182, 212, 0.1)'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true }},
      scales: { y: { ticks: { callback: v => currency(v)} } }
    }
  });

  const top = getRecommendations().slice(0,5);
  const ctx2 = document.getElementById('topChart').getContext('2d');
  if (topChart) topChart.destroy();
  topChart = new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: top.map(x=>`${x.product}\n(${x.branch})`),
      datasets: [{ 
        label: 'Predicted Sales', 
        data: top.map(x=>x.revenue),
        backgroundColor: '#0b63ff',
        borderColor: '#0a57df',
        borderWidth: 1
      }]
    },
    options: { 
      indexAxis:'y', 
      plugins:{ legend:{display:false} }, 
      scales: { 
        x:{ 
          ticks:{ callback:v=>currency(v) } 
        } 
      } 
    }
  });
}

// ------- Recommendations Table -------
function renderTable() {
  const tbody = document.querySelector('#recoTable tbody');
  const q = document.getElementById('q').value.toLowerCase();
  const branch = document.getElementById('branchSel').value;
  const size = document.getElementById('sizeSel')?.value || ''; 
  const minScore = Number(document.getElementById('minScore').value || 0);

  const data = getRecommendations()
    .filter(r => !branch || r.branch === branch)
    .filter(r => !size || r.product === size)
    .filter(r => r.score >= minScore)
    .filter(r => !q || (r.product+" "+r.branch+" "+r.reason).toLowerCase().includes(q));

  tbody.innerHTML = '';
  for (const r of data) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.branch}</td>
      <td>${r.product}</td>
      <td>${currency(r.basePrice)}</td>
      <td>${Math.round(r.topup*100)}%</td>
      <td>${currency(r.finalPrice)}</td>
      <td>${r.units.toLocaleString()}</td>
      <td>${currency(r.revenue)}</td>
      <td class="score">${r.score}</td>
      <td>${r.reason}</td>
      <td style="text-align:right">
        <button class="btn ghost" onclick='approve(${JSON.stringify(r).replace(/'/g, "&apos;")})'>Approve</button>
      </td>
    `;
    tbody.appendChild(tr);
  }
}

function approve(item) {
  // Here you would POST to your backend to create a PO or promo action
  alert(`Approved: ${item.product} for ${item.branch}`);
}

// ------- Export CSV -------
function exportCsv() {
  const rows = [["Branch","Product","Base Price","Top-Up","Final Price","Pred Units","Pred Revenue","Score","Reason"]];
  const q = document.getElementById('q').value.toLowerCase();
  const branch = document.getElementById('branchSel').value;
  const size = document.getElementById('sizeSel')?.value || '';
  const minScore = Number(document.getElementById('minScore').value || 0);
  const data = getRecommendations()
    .filter(r => !branch || r.branch === branch)
    .filter(r => !size || r.product === size)
    .filter(r => r.score >= minScore)
    .filter(r => !q || (r.product+" "+r.branch+" "+r.reason).toLowerCase().includes(q));
  for (const r of data) rows.push([r.branch, r.product, r.basePrice, Math.round(r.topup*100)+"%", r.finalPrice, r.units, r.revenue, r.score, r.reason]);
  const csv = rows.map(r=>r.join(',')).join('\n');
  const blob = new Blob([csv], {type:'text/csv'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'ai_recommendations.csv'; a.click();
  URL.revokeObjectURL(url);
}

// ------- Buttons & Init -------
document.getElementById('refreshBtn').addEventListener('click', ()=>{ loadKpis(); loadCharts(); renderTable(); });
document.getElementById('exportBtn').addEventListener('click', exportCsv);
document.getElementById('q').addEventListener('input', renderTable);
document.getElementById('branchSel').addEventListener('change', renderTable);
document.getElementById('sizeSel')?.addEventListener('change', renderTable);
document.getElementById('topupModeSel')?.addEventListener('change', ()=>{ renderTable(); loadCharts(); });
document.getElementById('monthSel')?.addEventListener('change', ()=>{ renderTable(); loadCharts(); });
document.getElementById('minScore').addEventListener('change', renderTable);
document.getElementById('retrainBtn').addEventListener('click', async ()=>{
  // Example integration to your Flask endpoint
  try {
    const res = await fetch('http://127.0.0.1:5000/retrain', {method:'POST', headers:{'Content-Type':'application/json'}, body:'{}'});
    const json = await res.json();
    alert(json.ok ? 'Retrained successfully' : ('Failed: '+json.msg));
    document.getElementById('lastTrained').textContent = new Date().toLocaleString();
  } catch (e) {
    alert('Retrain endpoint not reachable. Please start your Flask API.');
  }
});

function populateMonths(){
  const mSel = document.getElementById('monthSel');
  if(!mSel) return;
  const names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  mSel.innerHTML='';
  const cur = new Date().getMonth()+1;
  for(let i=1;i<=12;i++){
    const opt = document.createElement('option');
    opt.value = i; opt.textContent = `${i.toString().padStart(2,'0')} - ${names[i-1]}`;
    if(i===cur) opt.selected = true;
    mSel.appendChild(opt);
  }
}

// Initialize
document.getElementById('yr').textContent = new Date().getFullYear();
populateMonths();
loadKpis(); 
loadCharts(); 
renderTable();
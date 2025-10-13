<?php
$servername = "localhost";
$username = "root";
$password = "smarket";
$database = "s_market";
$conn = mysqli_connect($servername, $username, $password, $database);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>S-Market - Analytics</title>
<link rel="stylesheet" href="newdashboard.css">
<link rel="stylesheet" href="globalsidebar.css">

<style>
  html, body { margin:0; height:100%; overflow:hidden; background:#0f172a; }

  /* Layout container */
  .layout { height:100vh; width:100vw; }

  /* Keep your sidebar styling as-is; we’ll measure it and offset the content */
  .sidebar { /* your existing styles in dashboard.css */ }

  /* The area the iframe will fill */
  .report-area {
    position: fixed;         /* sit beside a fixed/absolute sidebar */
    top: 0;
    right: 0;
    height: 100vh;
    /* left is set by JS to match sidebar width */
    background:#2C3E50;      /* same dark bg as report edges */
    overflow: hidden;
  }

  /* The Power BI iframe */
  .powerbi {
  width: 99%;
  height: 100.5%;
  border: 4px solid #2C3E50;  /* ✅ visible dark blue-gray border */
  border-radius: 5px;         /* optional: slightly rounded corners */
  display: block;
  box-sizing: border-box;    /* keeps the border from affecting layout */
}
  
</style>
</head>
<body>

<div class="layout">
  <!-- Your sidebar (unchanged markup/styles) -->
  <div class="sidebar">
    <div class="logo">
      <img src="logo.png" alt="S-Market Logo">
      <h2>S-Market</h2>
    </div>
    <ul class="nav-links">
      <li class="nav-item"><a href="userpage.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
      <li class="nav-item active"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
      <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
      <li class="nav-item"><a href="userpage.php"><i class="fas fa-bullhorn"></i> Marketing</a></a></li>
      <li class="nav-item"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
    </ul>
  </div>

  <!-- Report fills the remaining space exactly -->
  <div id="reportArea" class="report-area">
    <iframe
      class="powerbi"
      src="https://app.powerbi.com/reportEmbed?reportId=8a970f37-63b7-49bb-b876-a0a2d44006f9&autoAuth=true&ctid=230efac0-932f-4bb5-ab75-fbd736b468f9&filterPaneEnabled=true&navContentPaneEnabled=false&bookmarkPaneEnabled=false&pageView=FitToWidth"
      allowfullscreen="true"
      sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox">
    </iframe>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
  // Fit the report area to the exact width remaining beside the sidebar
  (function fitToSidebar(){
    const sidebar = document.querySelector('.sidebar');
    const report  = document.getElementById('reportArea');

    function sync(){
      if(!sidebar || !report) return;
      const w = sidebar.getBoundingClientRect().width || 0;
      report.style.left  = w + 'px';
      report.style.width = `calc(100vw - ${w}px)`;
    }

    window.addEventListener('load', sync);
    window.addEventListener('resize', sync);

    // If the sidebar width changes (collapse/expand), keep in sync
    let prev = 0;
    setInterval(() => {
      const w = sidebar ? sidebar.getBoundingClientRect().width : 0;
      if (w !== prev){ prev = w; sync(); }
    }, 250);
  })();
</script>

</body>
</html>

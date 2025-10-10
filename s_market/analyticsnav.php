<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - Analytics</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="analyticscss.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
        overflow: hidden;
        background-color: #f4f6f8;
    }

    .container {
        display: flex;
        height: 50vh;
        width: 110vw;
        overflow: hidden;
    }


    .main-content {
        flex-grow: 5;
        display: flex;
        justify-content: center;  /* Centers the analytics horizontally */
        align-items: center;      /* Centers it vertically */
        background-color: #f4f6f8;
        padding: 0;
    }

    .chart-title {
        display: none; /* hide title to give full space to Power BI */
    }

    .powerbi-container {
        width: 95%;      /* makes report centered with slight margins */
        height: 95vh;    /* fills most of the viewport height */
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    /* Optional: make iframe adjust well on smaller screens */
    @media (max-width: 1000px) {
        .powerbi-container {
            width: 100%;
            height: 90vh;
        }
    }
</style>

</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item"><a href="userpage.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item active"><i class="fas fa-chart-bar"></i> Analytics</li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </div>

    <!-- Power BI Embedded Report -->
    
      <div class="main-content">
    <iframe 
        class="powerbi-container"
        src="https://app.powerbi.com/reportEmbed?reportId=8a970f37-63b7-49bb-b876-a0a2d44006f9&autoAuth=true&ctid=230efac0-932f-4bb5-ab75-fbd736b468f9&navContentPaneEnabled=false&filterPaneEnabled=false" 
        allowFullScreen="true">
    </iframe>
</div>

    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</body>
</html>

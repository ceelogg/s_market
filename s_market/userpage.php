<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";  

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch Capital (unit price * total stock)
$capitalQuery = "SELECT SUM(unit_price * product_quantity) AS total_capital FROM product";
$capitalResult = mysqli_query($conn, $capitalQuery);
$capitalRow = mysqli_fetch_assoc($capitalResult);
$capital = $capitalRow['total_capital'] ?? 0;

// Fetch Product Sales (unit price * quantity sold)
$productSalesQuery = "SELECT SUM(unit_price * quantity_sold) AS total_sales FROM product";
$productSalesResult = mysqli_query($conn, $productSalesQuery);
$productSalesRow = mysqli_fetch_assoc($productSalesResult);
$productSales = $productSalesRow['total_sales'] ?? 0;

// Fetch Profit (sales - capital)
$profitQuery = "SELECT SUM((unit_price * quantity_sold) - (unit_price * product_quantity)) AS total_profit FROM product";
$profitResult = mysqli_query($conn, $profitQuery);
$profitRow = mysqli_fetch_assoc($profitResult);
$profit = $profitRow['total_profit'] ?? 0;

// Fetch Capital Loss (unsold stock value)
$capitalLossQuery = "SELECT SUM((product_quantity - quantity_sold) * unit_price) AS total_loss 
                     FROM product WHERE quantity_sold < product_quantity";
$resultLoss = mysqli_query($conn, $capitalLossQuery);
$rowLoss = mysqli_fetch_assoc($resultLoss);
$capitalLoss = $rowLoss['total_loss'] ?? 0;

// Save Product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $product_type = mysqli_real_escape_string($conn, $_POST['productType']);
    $product_name = mysqli_real_escape_string($conn, $_POST['productName']);
    $product_quantity = intval($_POST['productQuantity']);
    $quantity_sold = intval($_POST['quantitySold']);
    $unit_price = floatval($_POST['unitPrice']);
    $total_sales = $quantity_sold * $unit_price;
    $date_of_sales = mysqli_real_escape_string($conn, $_POST['saleDate']);
    $month_of_sales = mysqli_real_escape_string($conn, $_POST['saleMonth']);

    $sql = "INSERT INTO product (branch, product_type, product_name, product_quantity, quantity_sold, unit_price, total_sales, date_of_sales, month_of_sales) 
            VALUES ('$branch', '$product_type', '$product_name', $product_quantity, $quantity_sold, $unit_price, $total_sales, '$date_of_sales', '$month_of_sales')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "✅ Product successfully added!";
    } else {
        $_SESSION['error'] = "❌ Error: " . mysqli_error($conn);
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - AI Marketing Decision Modeling System</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="upload.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>
    body {
        font-family: "Segoe UI", sans-serif;
        background-color: #f4f6f9;
        color: #003366;
        margin: 0;
        padding: 0;
    }

    .product-form-container {
        max-width: 700px;
        margin: 50px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .product-form-container h3 {
        margin-bottom: 25px;
        color: #0055aa;
        text-align: center;
        font-size: 1.8rem;
    }

    .product-form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
    }

    .product-form-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #0055aa;
    }

    .product-form-group input,
    .product-form-group select {
        padding: 12px;
        border: 1px solid #007bff;
        border-radius: 6px;
        font-size: 1rem;
        outline: none;
        transition: 0.3s border-color, 0.3s background-color;
    }

    .product-form-group input:focus,
    .product-form-group select:focus {
        border-color: #003366;
        background-color: #e6f0ff;
    }

    .product-form-row {
        display: flex;
        gap: 20px;
    }

    .product-form-row .product-form-group {
        flex: 1;
    }

    .form-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 25px;
    }

    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        color: #fff;
        transition: 0.3s background-color;
    }

    .btn-save {
        background-color: #007bff;
    }

    .btn-save:hover {
        background-color: #0056b3;
    }

    .btn-cancel {
        background-color: #dc3545;
    }

    .btn-cancel:hover {
        background-color: #b30000;
    }

    @media (max-width: 768px) {
        .product-form-row {
            flex-direction: column;
        }
    }
</style>
</head>
<body>

<div class="container">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item active"><i class="fas fa-home"></i> Dashboard</li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search products, or reports...">
            </div>
        </div>

        <!-- Metrics -->
        <div class="dashboard-grid">
            <div class="metric-card">
                <div class="title">Capital</div>
                <div class="value">₱<?= number_format($capital, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Product Sales</div>
                <div class="value">₱<?= number_format($productSales, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Profit</div>
                <div class="value">₱<?= number_format($profit, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Capital Loss</div>
                <div class="value">₱<?= number_format($capitalLoss, 2) ?></div>
            </div>
        </div>

        <!-- Upload & Modal Trigger -->
        <div class="upload-content">
            <div class="file-upload">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Drag and drop your CSV file here or click to browse</p>
                <button class="btn btn-primary">Choose File</button>
            </div>
            <div class="action-buttons">
                <button id="uploadBtn" class="btn btn-primary">Upload Products</button>
            </div>
        </div>

        <!-- Modal -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <span class="closeBtn">&times;</span>
                <div class="product-form-container">
                    <h3>Add Product Details</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" oninput="calculateTotal()">
                        <div class="product-form-group">
                            <label for="branch">Branch*</label>
                            <select id="branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="CTA Zandueta">CTA Zandueta</option>
                                <option value="DM Foodmart">DM Foodmart</option>
                                <option value="CTA Camp 7">CTA Camp 7</option>
                                <option value="BGH - OPD">BGH - OPD</option>
                            </select>
                        </div>

                        <div class="product-form-group">
                            <label for="productType">Product Type*</label>
                            <select id="productType" name="productType" required>
                                <option value="">Select Product Type</option>
                                <option value="Small Tub">Small Tub</option>
                                <option value="Big Tub">Big Tub</option>
                            </select>
                        </div>

                        <div class="product-form-group">
                            <label for="productName">Product Name*</label>
                            <input type="text" id="productName" name="productName" placeholder="Enter product name" required>
                        </div>

                        <div class="product-form-group">
                            <label for="productQuantity">Quantity of Product*</label>
                            <input type="number" id="productQuantity" name="productQuantity" min="0" placeholder="0" required>
                        </div>

                        <div class="product-form-row">
                            <div class="product-form-group">
                                <label for="quantitySold">Quantity Sold*</label>
                                <input type="number" id="quantitySold" name="quantitySold" min="0" placeholder="0" required>
                            </div>
                            <div class="product-form-group">
                                <label for="unitPrice">Unit Price*</label>
                                <input type="number" id="unitPrice" name="unitPrice" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="product-form-group">
                            <label for="totalSales">Total Sales</label>
                            <input type="number" id="totalSales" name="totalSales" step="0.01" placeholder="0.00" readonly>
                        </div>

                        <div class="product-form-group">
                            <label for="saleDate">Date of Sale*</label>
                            <input type="date" id="saleDate" name="saleDate" required onchange="updateMonth()">
                        </div>

                        <div class="product-form-group">
                            <label for="saleMonth">Month of Sale</label>
                            <input type="text" id="saleMonth" name="saleMonth" readonly>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-save">Save Product</button>
                            <button type="button" class="btn btn-cancel" onclick="window.location.href='productnav.php'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantitySold').value) || 0;
    const price = parseFloat(document.getElementById('unitPrice').value) || 0;
    document.getElementById('totalSales').value = (quantity * price).toFixed(2);
}

function updateMonth() {
    const date = document.getElementById('saleDate').value;
    if (date) {
        const monthNames = [
            "January","February","March","April","May","June",
            "July","August","September","October","November","December"
        ];
        const d = new Date(date);
        document.getElementById('saleMonth').value = monthNames[d.getMonth()] + " " + d.getFullYear();
    } else {
        document.getElementById('saleMonth').value = '';
    }
}

// Modal handling
const modal = document.getElementById('productModal');
const btn = document.getElementById('uploadBtn');
const closeBtn = document.querySelector('.closeBtn');

btn.addEventListener('click', () => { modal.style.display = 'block'; });
closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
window.addEventListener('click', (event) => { if (event.target === modal) modal.style.display = 'none'; });
</script>

</body>
</html>

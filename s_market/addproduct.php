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

// Save Product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $product_type = mysqli_real_escape_string($conn, $_POST['productType']);
    $product_name = mysqli_real_escape_string($conn, $_POST['productName']);
    $product_quantity = intval($_POST['productQuantity']); // NEW FIELD
    $quantity_sold = intval($_POST['quantitySold']);
    $unit_price = floatval($_POST['unitPrice']);
    $total_sales = $quantity_sold * $unit_price;
    $date_of_sales = mysqli_real_escape_string($conn, $_POST['saleDate']);
    $month_of_sales = mysqli_real_escape_string($conn, $_POST['saleMonth']);

    $sql = "INSERT INTO product (branch, product_type, product_name, product_quantity, quantity_sold, unit_price, total_sales, date_of_sales, month_of_sales) 
            VALUES ('$branch', '$product_type', '$product_name', $product_quantity, $quantity_sold, $unit_price, $total_sales, '$date_of_sales', '$month_of_sales')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "✅ Product successfully added!";
        header("Location: productnav.php");
        exit();
    } else {
        $_SESSION['error'] = "❌ Error: " . mysqli_error($conn);
        header("Location: productnav.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product</title>
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
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const d = new Date(date);
        const month = monthNames[d.getMonth()] + " " + d.getFullYear();
        document.getElementById('saleMonth').value = month;
    } else {
        document.getElementById('saleMonth').value = '';
    }
}
</script>
</body>
</html>

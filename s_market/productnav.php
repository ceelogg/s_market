<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";  

$conn = mysqli_connect($servername, $username, $password, $database);

// ==============================
// Handle product update 
// ==============================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $id = intval($_POST['id']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_quantity = intval($_POST['product_quantity']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $unit_price = floatval($_POST['unit_price']);
    $total_sales = $quantity_sold * $unit_price;
    $date_of_sales = mysqli_real_escape_string($conn, $_POST['date_of_sales']);
    $month_of_sales = mysqli_real_escape_string($conn, $_POST['month_of_sales']);

    $sql = "UPDATE product SET 
            branch = '$branch',
            product_type = '$product_type',
            product_name = '$product_name',
            product_quantity = $product_quantity,
            quantity_sold = $quantity_sold,
            unit_price = $unit_price,
            total_sales = $total_sales,
            date_of_sales = '$date_of_sales',
            month_of_sales = '$month_of_sales'
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// ==============================
// Product Delete
// ==============================
if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $delete_sql = "DELETE FROM product WHERE id = $product_id";
    
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

// ==============================
// Handle filters (branch + date)
// ==============================
$branchFilter = isset($_GET['branch']) ? $_GET['branch'] : 'All Branches';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// ==============================
// Summary Cards Data
// ==============================
$summary_sql = "SELECT 
    COUNT(*) AS total_products,
    SUM(total_sales) AS total_sales,
    SUM(quantity_sold) AS items_sold,
    SUM(product_quantity) AS items_in_stock
    FROM product WHERE 1=1";

// Apply same filters
if ($branchFilter != "All Branches") {
    $summary_sql .= " AND branch = '" . mysqli_real_escape_string($conn, $branchFilter) . "'";
}
if (!empty($startDate) && !empty($endDate)) {
    $summary_sql .= " AND date_of_sales BETWEEN '" . mysqli_real_escape_string($conn, $startDate) . "' 
                      AND '" . mysqli_real_escape_string($conn, $endDate) . "'";
}

$summary_result = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_result);

$total_products = $summary['total_products'] ?? 0;
$total_sales = $summary['total_sales'] ?? 0;
$items_sold = $summary['items_sold'] ?? 0;
$items_in_stock = $summary['items_in_stock'] ?? 0;

// ==============================
// Product Table Query
// ==============================
$sql = "SELECT * FROM product WHERE 1=1";

// Branch filter
if ($branchFilter != "All Branches") {
    $sql .= " AND branch = '" . mysqli_real_escape_string($conn, $branchFilter) . "'";
}

// Date filter
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND date_of_sales BETWEEN '" . mysqli_real_escape_string($conn, $startDate) . "' 
              AND '" . mysqli_real_escape_string($conn, $endDate) . "'";
}

$result = mysqli_query($conn, $sql);

// ==============================
// Export CSV
// ==============================
if (isset($_GET['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Branch', 'Product Type', 'Product Name', 'Stock', 'Quantity Sold', 'Unit Price', 'Total Sales', 'Date of Sales', 'Month of Sales']);

    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - Products</title>
    <link rel="stylesheet" href="productnav.css">
    <link rel="stylesheet" href="producttest.css">
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
                <li class="nav-item active"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
                <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
                <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
                <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
            </ul>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="card">
                    <h3><?= $total_products ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="card">
                    <h3>₱<?= number_format($total_sales, 2) ?></h3>
                    <p>Total Sales</p>
                </div>
                <div class="card">
                    <h3><?= $items_sold ?></h3>
                    <p>Items Sold</p>
                </div>
                <div class="card">
                    <h3><?= $items_in_stock ?></h3>
                    <p>Items in Stock</p>
                </div>
            </div>

            <!-- Active Filters Info -->
            <div class="filter-info">
                <p><strong>Active Filters:</strong> 
                    Branch: <?= htmlspecialchars($branchFilter) ?> | 
                    Date: <?= (!empty($startDate) && !empty($endDate)) ? $startDate . " → " . $endDate : "All Dates" ?>
                </p>
            </div>

            <div class="products-section">
                <div class="products-header">
                    <h2>Product Inventory</h2>
                    <div>
                        <!-- Filter Form -->
                        <form method="GET" action="" class="filter-form">
                            <label for="branch">Branch</label>
                            <select id="branch" name="branch" class="branch-select">
                                <option value="All Branches" <?= ($branchFilter == 'All Branches') ? 'selected' : ''; ?>>All Branches</option>
                                <option value="CTA Zandueta" <?= ($branchFilter == 'CTA Zandueta') ? 'selected' : ''; ?>>CTA Zandueta</option>
                                <option value="DM Foodmart" <?= ($branchFilter == 'DM Foodmart') ? 'selected' : ''; ?>>DM Foodmart</option>
                                <option value="CTA Camp7" <?= ($branchFilter == 'CTA Camp7') ? 'selected' : ''; ?>>CTA Camp7</option>
                                <option value="BGH-OPD" <?= ($branchFilter == 'BGH-OPD') ? 'selected' : ''; ?>>BGH-OPD</option>
                            </select>

                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">

                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">

                            <button type="submit">Apply Filter</button>
                            <a href="productnav.php"><button type="button">Reset</button></a>
                            <button type="submit" name="export_csv" value="1">Export CSV</button>
                        </form>
                    </div>
                </div>
                
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Product Type</th>
                            <th>Product Name</th>
                            <th>Stock</th>
                            <th>Quantity Sold</th>
                            <th>Unit Price</th>
                            <th>Total Sales</th>
                            <th>Date of Sales</th>
                            <th>Month of Sales</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['product_type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                echo "<td>" . intval($row['product_quantity']) . "</td>";
                                echo "<td>" . intval($row['quantity_sold']) . "</td>";
                                echo "<td>₱" . number_format($row['unit_price'], 2) . "</td>";
                                echo "<td>₱" . number_format($row['total_sales'], 2) . "</td>";
                                echo "<td>" . htmlspecialchars($row['date_of_sales']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['month_of_sales']) . "</td>";
                                echo "<td>
                                        <button class='action-btn edit-btn' onclick='editProduct(" . json_encode($row) . ")'><i class='fas fa-edit'></i></button>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='product_id' value='" . intval($row['id']) . "'>
                                            <button type='submit' name='delete_product' class='action-btn delete-btn' onclick='return confirm(\"Are you sure you want to delete this product?\")'><i class='fas fa-trash'></i></button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No products found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Product</h3>
            <form id="editProductForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" id="edit_id" name="id">

                <div class="form-group">
                    <label for="edit_branch">Branch</label>
                    <input type="text" id="edit_branch" name="branch" required>
                </div>
                <div class="form-group">
                    <label for="edit_product_type">Product Type</label>
                    <input type="text" id="edit_product_type" name="product_type" required>
                </div>
                <div class="form-group">
                    <label for="edit_product_name">Product Name</label>
                    <input type="text" id="edit_product_name" name="product_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_product_quantity">Stock</label>
                    <input type="number" id="edit_product_quantity" name="product_quantity" required>
                </div>
                <div class="form-group">
                    <label for="edit_quantity_sold">Quantity Sold</label>
                    <input type="number" id="edit_quantity_sold" name="quantity_sold" required>
                </div>
                <div class="form-group">
                    <label for="edit_unit_price">Unit Price</label>
                    <input type="number" id="edit_unit_price" name="unit_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_date_of_sales">Date of Sales</label>
                    <input type="date" id="edit_date_of_sales" name="date_of_sales" required>
                </div>
                <div class="form-group">
                    <label for="edit_month_of_sales">Month of Sales</label>
                    <input type="text" id="edit_month_of_sales" name="month_of_sales" required>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="productnav.js"></script>
</body>
</html>
<?php
mysqli_close($conn);
?>

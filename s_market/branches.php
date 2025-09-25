<?php
// Database connection (replace with your actual database credentials)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "inventory_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get filter parameters
$selected_branch = isset($_GET['branch']) ? $_GET['branch'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build SQL query based on filters
$sql = "SELECT * FROM product_inventory WHERE 1=1";
$params = [];

if ($selected_branch != 'all') {
    $sql .= " AND branch = :branch";
    $params['branch'] = $selected_branch;
}

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND date_of_sales BETWEEN :start_date AND :end_date";
    $params['start_date'] = $start_date;
    $params['end_date'] = $end_date;
}

$sql .= " ORDER BY date_of_sales DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all unique branches for dropdown
$branch_sql = "SELECT DISTINCT branch FROM product_inventory ORDER BY branch";
$branch_stmt = $pdo->prepare($branch_sql);
$branch_stmt->execute();
$branches = $branch_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5em;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            gap: 20px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 0.9em;
        }

        .filter-group select,
        .filter-group input {
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table th,
        .table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .table th {
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .table tbody tr:nth-child(even):hover {
            background: #f1f3f4;
        }

        .stock-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-align: center;
            min-width: 60px;
            display: inline-block;
        }

        .stock-high {
            background: #d4edda;
            color: #155724;
        }

        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }

        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: #28a745;
            color: white;
        }

        .edit-btn:hover {
            background: #218838;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #6c757d;
            font-size: 1.1em;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card h3 {
            font-size: 2em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .summary-card p {
            color: #6c757d;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Product Inventory Management</h1>
            <p>Track and manage your product inventory across all branches</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" id="filterForm">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="branch">Branch</label>
                        <select name="branch" id="branch">
                            <option value="all" <?php echo $selected_branch == 'all' ? 'selected' : ''; ?>>All Branches</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?php echo htmlspecialchars($branch); ?>" 
                                        <?php echo $selected_branch == $branch ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($branch); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <a href="?" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // Calculate summary statistics
        $total_products = count($products);
        $total_sales = array_sum(array_column($products, 'total_sales'));
        $total_quantity_sold = array_sum(array_column($products, 'quantity_sold'));
        $total_stock = array_sum(array_column($products, 'stock'));
        ?>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3><?php echo $total_products; ?></h3>
                <p>Total Products</p>
            </div>
            <div class="summary-card">
                <h3>₱<?php echo number_format($total_sales, 2); ?></h3>
                <p>Total Sales</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $total_quantity_sold; ?></h3>
                <p>Items Sold</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $total_stock; ?></h3>
                <p>Items in Stock</p>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-container">
            <?php if (count($products) > 0): ?>
                <table class="table">
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
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['branch']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['product_type']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td>
                                    <span class="stock-badge <?php 
                                        if ($product['stock'] > 20) echo 'stock-high';
                                        elseif ($product['stock'] > 5) echo 'stock-medium';
                                        else echo 'stock-low';
                                    ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td><?php echo $product['quantity_sold']; ?></td>
                                <td>₱<?php echo number_format($product['unit_price'], 2); ?></td>
                                <td><strong>₱<?php echo number_format($product['total_sales'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($product['date_of_sales'])); ?></td>
                                <td><?php echo htmlspecialchars($product['month_of_sales']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="action-btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">✎</button>
                                        <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">🗑</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <h3>No products found</h3>
                    <p>Try adjusting your filter criteria or add new products to the inventory.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-apply filters when branch selection changes
        document.getElementById('branch').addEventListener('change', function() {
            // Auto-submit form when branch changes
            document.getElementById('filterForm').submit();
        });

        // Date range validation
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            
            if (startDate) {
                endDateInput.min = startDate;
            }
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = this.value;
            const startDateInput = document.getElementById('start_date');
            
            if (endDate) {
                startDateInput.max = endDate;
            }
        });

        // Product management functions
        function editProduct(productId) {
            // Implement edit functionality
            alert('Edit product ID: ' + productId);
            // You can redirect to edit page or open a modal
            // window.location.href = 'edit_product.php?id=' + productId;
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                // Implement delete functionality
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting product');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + R to reset filters
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                window.location.href = '?';
            }
            
            // Ctrl + F to focus on branch filter
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('branch').focus();
            }
        });

        // Initialize tooltips and enhance UX
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to filter form
            const form = document.getElementById('filterForm');
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Loading...';
                submitBtn.disabled = true;
            });

            // Auto-focus on first filter input
            document.getElementById('branch').focus();

            // Add smooth scrolling to table when filters change
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('branch') || urlParams.has('start_date') || urlParams.has('end_date')) {
                document.querySelector('.table-container').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });

        // Export functionality (optional)
        function exportToCSV() {
            const table = document.querySelector('.table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length - 1; j++) { // Exclude actions column
                    row.push(cols[j].innerText);
                }
                
                csv.push(row.join(','));
            }
            
            const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
            const downloadLink = document.createElement('a');
            downloadLink.download = 'inventory_' + new Date().toISOString().slice(0,10) + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        // Add export button dynamically
        if (<?php echo count($products); ?> > 0) {
            const filterSection = document.querySelector('.filter-section');
            const exportBtn = document.createElement('button');
            exportBtn.textContent = 'Export CSV';
            exportBtn.className = 'btn btn-secondary';
            exportBtn.style.marginLeft = '10px';
            exportBtn.onclick = exportToCSV;
            filterSection.querySelector('.filter-row').appendChild(exportBtn);
        }
    </script>
</body>
</html>

<?php

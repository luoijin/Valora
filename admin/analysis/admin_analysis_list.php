<?php
// admin/analysis/admin_analysis_list.php - Analytics Dashboard with Functional Trends
session_start();

// Database connection
require_once '../../config.php';

// Fetch analytics data
try {
    // Revenue metrics - Current Period
    $revenueQuery = "SELECT 
        SUM(total_amount) as total_revenue,
        COUNT(*) as total_orders,
        AVG(total_amount) as avg_order_value
        FROM orders WHERE status = 'completed'";
    $revenueStmt = $db->query($revenueQuery);
    $revenueData = $revenueStmt->fetch(PDO::FETCH_ASSOC);
    
    // Revenue metrics - Previous Period (for comparison)
    $prevRevenueQuery = "SELECT 
        SUM(total_amount) as total_revenue,
        COUNT(*) as total_orders,
        AVG(total_amount) as avg_order_value
        FROM orders 
        WHERE status = 'completed'
        AND created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)";
    $prevRevenueStmt = $db->query($prevRevenueQuery);
    $prevRevenueData = $prevRevenueStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate percentage changes
    $revenueChange = calculatePercentageChange(
        $prevRevenueData['total_revenue'] ?? 0, 
        $revenueData['total_revenue'] ?? 0
    );
    $ordersChange = calculatePercentageChange(
        $prevRevenueData['total_orders'] ?? 0, 
        $revenueData['total_orders'] ?? 0
    );
    $avgOrderChange = calculatePercentageChange(
        $prevRevenueData['avg_order_value'] ?? 0, 
        $revenueData['avg_order_value'] ?? 0
    );
    
    // Customer metrics - Current
    $customerQuery = "SELECT 
        COUNT(DISTINCT user_id) as total_customers
        FROM orders";
    $customerStmt = $db->query($customerQuery);
    $customerData = $customerStmt->fetch(PDO::FETCH_ASSOC);
    
    // Customer metrics - Previous Period
    $prevCustomerQuery = "SELECT 
        COUNT(DISTINCT user_id) as total_customers
        FROM orders
        WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)";
    $prevCustomerStmt = $db->query($prevCustomerQuery);
    $prevCustomerData = $prevCustomerStmt->fetch(PDO::FETCH_ASSOC);
    
    $customerChange = calculatePercentageChange(
        $prevCustomerData['total_customers'] ?? 0,
        $customerData['total_customers'] ?? 0
    );
    
    // Product inventory count - Current and Previous
    $productCountQuery = "SELECT COUNT(*) as total_products FROM products WHERE is_active = 1";
    $productStmt = $db->query($productCountQuery);
    $productData = $productStmt->fetch(PDO::FETCH_ASSOC);
    
    $prevProductQuery = "SELECT COUNT(*) as total_products 
        FROM products 
        WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $prevProductStmt = $db->query($prevProductQuery);
    $prevProductData = $prevProductStmt->fetch(PDO::FETCH_ASSOC);
    
    $productChange = calculatePercentageChange(
        $prevProductData['total_products'] ?? 0,
        $productData['total_products'] ?? 0
    );
    
    // Conversion Rate Calculation
    // Current period: orders / total site visits (simplified: using total customers as proxy)
    $currentConversionQuery = "SELECT 
        COUNT(DISTINCT o.user_id) as converting_customers,
        (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_users
        FROM orders o
        WHERE o.status = 'completed'
        AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $conversionStmt = $db->query($currentConversionQuery);
    $conversionData = $conversionStmt->fetch(PDO::FETCH_ASSOC);
    
    $currentConversionRate = ($conversionData['total_users'] > 0) 
        ? ($conversionData['converting_customers'] / $conversionData['total_users']) * 100 
        : 0;
    
    // Previous conversion rate
    $prevConversionQuery = "SELECT 
        COUNT(DISTINCT o.user_id) as converting_customers,
        (SELECT COUNT(*) FROM users WHERE role = 'customer' 
         AND created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as total_users
        FROM orders o
        WHERE o.status = 'completed'
        AND o.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)";
    $prevConversionStmt = $db->query($prevConversionQuery);
    $prevConversionData = $prevConversionStmt->fetch(PDO::FETCH_ASSOC);
    
    $prevConversionRate = ($prevConversionData['total_users'] > 0)
        ? ($prevConversionData['converting_customers'] / $prevConversionData['total_users']) * 100
        : 0;
    
    $conversionChange = calculatePercentageChange($prevConversionRate, $currentConversionRate);
    
    // Monthly revenue trend (last 6 months)
    $monthlyRevenueQuery = "SELECT 
        DATE_FORMAT(created_at, '%b %Y') as month,
        SUM(total_amount) as revenue,
        COUNT(*) as orders
        FROM orders 
        WHERE status = 'completed' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY created_at ASC";
    $monthlyStmt = $db->query($monthlyRevenueQuery);
    $monthlyRevenue = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 10 products
    $topProductsQuery = "SELECT 
        p.name as product_name,
        p.category,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.id
        ORDER BY revenue DESC
        LIMIT 10";
    $topProductsStmt = $db->query($topProductsQuery);
    $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Order status distribution
    $orderStatusQuery = "SELECT 
        status,
        COUNT(*) as count
        FROM orders
        GROUP BY status";
    $orderStatusStmt = $db->query($orderStatusQuery);
    $orderStatus = $orderStatusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Category sales breakdown
    $categorySalesQuery = "SELECT 
        p.category,
        SUM(oi.quantity * oi.price) as revenue,
        SUM(oi.quantity) as items_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.category";
    $categorySalesStmt = $db->query($categorySalesQuery);
    $categorySales = $categorySalesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Collection sales breakdown
    $collectionSalesQuery = "SELECT 
        p.collection,
        SUM(oi.quantity * oi.price) as revenue,
        SUM(oi.quantity) as items_sold,
        COUNT(DISTINCT p.id) as product_count
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.collection
        ORDER BY revenue DESC";
    $collectionSalesStmt = $db->query($collectionSalesQuery);
    $collectionSales = $collectionSalesStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle error gracefully
    $error = "Error fetching analytics data: " . $e->getMessage();
}

// Helper function to calculate percentage change
function calculatePercentageChange($oldValue, $newValue) {
    if ($oldValue == 0) {
        return $newValue > 0 ? 100 : 0;
    }
    return (($newValue - $oldValue) / $oldValue) * 100;
}

// Helper function to format trend display
function getTrendClass($percentage) {
    if ($percentage > 0) return 'positive';
    if ($percentage < 0) return 'negative';
    return 'neutral';
}

function getTrendIcon($percentage) {
    if ($percentage > 0) return 'fa-arrow-up';
    if ($percentage < 0) return 'fa-arrow-down';
    return 'fa-minus';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Valora Admin</title>
    <link rel="stylesheet" href="../../assets/css/analysis.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container analytics-page">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <h1>Analytics Dashboard</h1>
                    <div class="header-actions">
                        <select class="date-filter" id="dateFilter">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                        <button class="btn-export" onclick="exportReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php else: ?>

                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon revenue">
                            <i class="fa-solid fa-peso-sign"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Total Revenue</h3>
                            <p class="kpi-value">₱<?php echo number_format($revenueData['total_revenue'] ?? 0, 2); ?></p>
                            <span class="kpi-trend <?php echo getTrendClass($revenueChange); ?>">
                                <i class="fas <?php echo getTrendIcon($revenueChange); ?>"></i> 
                                <?php echo number_format(abs($revenueChange), 1); ?>%
                            </span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Total Orders</h3>
                            <p class="kpi-value"><?php echo number_format($revenueData['total_orders'] ?? 0); ?></p>
                            <span class="kpi-trend <?php echo getTrendClass($ordersChange); ?>">
                                <i class="fas <?php echo getTrendIcon($ordersChange); ?>"></i> 
                                <?php echo number_format(abs($ordersChange), 1); ?>%
                            </span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon avg-order">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Avg Order Value</h3>
                            <p class="kpi-value">₱<?php echo number_format($revenueData['avg_order_value'] ?? 0, 2); ?></p>
                            <span class="kpi-trend <?php echo getTrendClass($avgOrderChange); ?>">
                                <i class="fas <?php echo getTrendIcon($avgOrderChange); ?>"></i> 
                                <?php echo number_format(abs($avgOrderChange), 1); ?>%
                            </span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Total Customers</h3>
                            <p class="kpi-value"><?php echo number_format($customerData['total_customers'] ?? 0); ?></p>
                            <span class="kpi-trend <?php echo getTrendClass($customerChange); ?>">
                                <i class="fas <?php echo getTrendIcon($customerChange); ?>"></i> 
                                <?php echo number_format(abs($customerChange), 1); ?>%
                            </span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Total Products</h3>
                            <p class="kpi-value"><?php echo number_format($productData['total_products'] ?? 0); ?></p>
                            <span class="kpi-trend <?php echo getTrendClass($productChange); ?>">
                                <i class="fas <?php echo getTrendIcon($productChange); ?>"></i> 
                                <?php echo number_format(abs($productChange), 1); ?>%
                            </span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon conversion">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="kpi-content">
                            <h3>Conversion Rate</h3>
                            <p class="kpi-value"><?php echo number_format($currentConversionRate, 1); ?>%</p>
                            <span class="kpi-trend <?php echo getTrendClass($conversionChange); ?>">
                                <i class="fas <?php echo getTrendIcon($conversionChange); ?>"></i> 
                                <?php echo number_format(abs($conversionChange), 1); ?>%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <!-- Revenue Trend Chart -->
                    <div class="chart-card large">
                        <div class="chart-header">
                            <h2>Revenue Trend</h2>
                            <div class="chart-legend">
                                <span class="legend-item">
                                    <span class="legend-color" style="background: #065f46;"></span>
                                    Revenue
                                </span>
                            </div>
                        </div>
                        <canvas id="revenueTrendChart"></canvas>
                    </div>

                    <!-- Order Status Distribution -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2>Order Status</h2>
                        </div>
                        <canvas id="orderStatusChart"></canvas>
                    </div>

                    <!-- Category Sales -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2>Sales by Category</h2>
                        </div>
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                    
                    <!-- Collection Sales -->
                    <div class="chart-card large">
                        <div class="chart-header">
                            <h2>Sales by Collection</h2>
                        </div>
                        <canvas id="collectionSalesChart"></canvas>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h2>Top 10 Best-Selling Products</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Units Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topProducts)): ?>
                                    <?php foreach ($topProducts as $index => $product): ?>
                                        <tr>
                                            <td>
                                                <span class="rank-badge rank-<?php echo $index + 1; ?>">
                                                    #<?php echo $index + 1; ?>
                                                </span>
                                            </td>
                                            <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td>
                                                <span class="category-badge">
                                                    <?php echo htmlspecialchars($product['category']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($product['total_sold']); ?></td>
                                            <td class="revenue-cell">₱<?php echo number_format($product['revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-data">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        const monthlyRevenueData = <?php echo json_encode($monthlyRevenue ?? []); ?>;
        const orderStatusData = <?php echo json_encode($orderStatus ?? []); ?>;
        const categorySalesData = <?php echo json_encode($categorySales ?? []); ?>;
        const collectionSalesData = <?php echo json_encode($collectionSales ?? []); ?>;

        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueTrendChart');
        if (revenueCtx && monthlyRevenueData.length > 0) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: monthlyRevenueData.map(d => d.month),
                    datasets: [{
                        label: 'Revenue',
                        data: monthlyRevenueData.map(d => d.revenue),
                        borderColor: '#065f46',
                        backgroundColor: 'rgba(6, 95, 70, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#065f46',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            borderRadius: 8,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Order Status Doughnut Chart
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (orderStatusCtx && orderStatusData.length > 0) {
            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: orderStatusData.map(d => d.status.toUpperCase()),
                    datasets: [{
                        data: orderStatusData.map(d => d.count),
                        backgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                },
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            borderRadius: 8
                        }
                    }
                }
            });
        }

        // Category Sales Bar Chart
        const categorySalesCtx = document.getElementById('categorySalesChart');
        if (categorySalesCtx && categorySalesData.length > 0) {
            new Chart(categorySalesCtx, {
                type: 'bar',
                data: {
                    labels: categorySalesData.map(d => d.category.toUpperCase()),
                    datasets: [{
                        label: 'Revenue',
                        data: categorySalesData.map(d => d.revenue),
                        backgroundColor: [
                            '#065f46',
                            '#10b981',
                            '#34d399'
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            borderRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Collection Sales Horizontal Bar Chart
        const collectionSalesCtx = document.getElementById('collectionSalesChart');
        if (collectionSalesCtx && collectionSalesData.length > 0) {
            new Chart(collectionSalesCtx, {
                type: 'bar',
                data: {
                    labels: collectionSalesData.map(d => d.collection),
                    datasets: [{
                        label: 'Revenue',
                        data: collectionSalesData.map(d => d.revenue),
                        backgroundColor: [
                            '#065f46',
                            '#10b981',
                            '#34d399',
                            '#6ee7b7',
                            '#a7f3d0'
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            borderRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₱' + context.parsed.x.toLocaleString();
                                },
                                afterLabel: function(context) {
                                    const index = context.dataIndex;
                                    const items = collectionSalesData[index].items_sold;
                                    const products = collectionSalesData[index].product_count;
                                    return 'Items Sold: ' + items + '\nProducts: ' + products;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Export report function
        function exportReport() {
            alert('Export functionality will be implemented with PDF/Excel generation');
        }

        // Date filter change
        document.getElementById('dateFilter')?.addEventListener('change', function() {
            // Implement date filtering logic
            console.log('Filter changed to:', this.value);
        });
    </script>
</body>
</html>
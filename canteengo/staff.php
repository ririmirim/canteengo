<?php
session_start();
// Debug: Check session status
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verify staff authentication
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    // Debug output
    echo "<script>console.log('Access Denied - Session Data:', " . json_encode($_SESSION) . ");</script>";
    header("Location: index.php");
    exit();
}

require 'config.php';

// Fetch orders with error handling
try {
    $result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CanteenGo! - Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-badge { min-width: 90px; }
        @media (max-width: 992px) {
            .navbar-collapse {
                padding-top: 1rem;
            }
            .nav-item {
                border-bottom: 1px solid rgba(255,255,255,0.1);
                padding: 0.5rem 0;
            }
            .nav-link {
                padding-left: 1rem !important;
            }
        }
        @media (max-width: 768px) {
            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }
            .form-select-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            .status-badge {
                font-size: 0.75rem;
                min-width: 70px;
            }
        }
        .status-badge {
            transition: background-color 0.3s ease;
        }
        tr {
            transition: all 0.3s ease;
        }
        tr.new-order {
            background-color: rgba(25, 135, 84, 0.1);
            animation: highlight 2s;
        }
        @keyframes highlight {
            from { background-color: rgba(25, 135, 84, 0.2); }
            to { background-color: transparent; }
        }
        .status-badge {
            transition: all 0.3s ease;
        }
        tr {
            transition: background-color 0.3s ease;
        }
        .online-users {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 5px 15px !important;
            margin: 0 10px;
        }
        
        .online-users .bi {
            margin-right: 8px;
        }
        
        #onlineCount {
            font-weight: 700;
            color: #fff;
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -1px;
        }

        .navbar-brand .go-white {
            padding: 0.25rem 0.5rem;
            margin-left: 0.25rem;
            transition: all 0.3s ease;
        }

        /* Mobile responsiveness */
        @media (max-width: 992px) {
            .navbar-brand {
                font-size: 1.2rem !important;
            }
            .go-white {
                font-size: 1.1rem !important;
                padding: 0.2rem 0.4rem !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success py-2">
        <div class="container-fluid">
            <!-- Updated Brand Logo with mobile sizing -->
            <a class="navbar-brand" href="#" style="font-size: 1.5rem; letter-spacing: -1px;">
                <span style="color:rgb(0, 87, 20); font-weight: 800;">CANTEEN</span>
                <span class="go-white bg-dark rounded px-2 py-1" style="font-size: 1.3rem;">GO!</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggler" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible Menu Items -->
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link online-users">
                            <i class="bi bi-people-fill"></i>
                            <span id="onlineCount">0</span> Users Online
                        </span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Orders Dashboard</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th class="d-none d-md-table-cell">User</th>
                                <th>Details</th>
                                <th class="d-none d-sm-table-cell">Payment</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Timestamp</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['student_username']) ?></td>
                                <td><?= htmlspecialchars($row['order_details']) ?></td>
                                <td class="d-none d-sm-table-cell"><?= ucfirst($row['payment_method']) ?></td>
                                <td>
                                    <span class="badge status-badge <?= match($row['status']) {
                                        'pending' => 'bg-warning',
                                        'processing' => 'bg-info',
                                        'claimable' => 'bg-primary', // Added 'claimable'
                                        'complete' => 'bg-success'
                                    } ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell"><?= date('M j, g:i a', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <form method="post" action="update_status.php">
                                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $row['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="claimable" <?= $row['status'] === 'claimable' ? 'selected' : '' ?>>Ready for Claim</option>
                                            <option value="complete" <?= $row['status'] === 'complete' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentOrders = {};

        function fetchOrders() {
            fetch('get_orders.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(orders => {
                    // Only update if orders array length changed
                    if (orders.length !== Object.keys(currentOrders).length) {
                        updateOrderTable(orders);
                        currentOrders = orders;
                    }
                })
                .catch(error => console.error('Fetch error:', error));
        }

        function updateOrderTable(orders) {
            const tbody = document.querySelector('tbody');
            const newRows = orders.map(order => {
                return `
                    <tr data-order-id="${order.id}">
                        <td>${order.id}</td>
                        <td class="d-none d-md-table-cell">${order.student_username}</td>
                        <td>${order.order_details.replace(/,/g, ', ')}</td>
                        <td class="d-none d-sm-table-cell">${order.payment_method}</td>
                        <td>
                            <span class="badge status-badge ${getStatusClass(order.status)}">
                                ${order.status}
                            </span>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            ${new Date(order.created_at).toLocaleString()}
                        </td>
                        <td>
                            <form method="post" action="update_status.php">
                                <input type="hidden" name="order_id" value="${order.id}">
                                <select name="status" class="form-select form-select-sm" 
                                    onchange="this.form.submit()">
                                    ${['pending', 'processing', 'claimable', 'complete'].map(opt => 
                                        `<option value="${opt}" ${opt === order.status ? 'selected' : ''}>
                                            ${opt.charAt(0).toUpperCase() + opt.slice(1)}
                                        </option>`
                                    ).join('')}
                                </select>
                            </form>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = newRows.join('');
        }

        // Add proper status class mapping
        function getStatusClass(status) {
            return {
                'pending': 'bg-warning',
                'processing': 'bg-info',
                'claimable': 'bg-primary', // Added 'claimable'
                'complete': 'bg-success'
            }[status] || '';
        }

        // Modified updateStatus function
        function updateStatus(event, orderId) {
            event.preventDefault();
            event.stopPropagation();

            const form = event.target;
            const status = form.querySelector('select').value;
            const row = form.closest('tr');

            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&status=${status}`
            })
            .then(response => {
                if(!response.ok) throw new Error('Network error');
                return response.json();  // Remove this line
            })
            .then(data => {  // Remove this entire block
                // Delete all code here
            })
            .catch(error => {
                console.error('Error:', error);
                // Remove alert if desired
            });
        
            // Add visual feedback directly without waiting for response
            const badge = row.querySelector('.status-badge');
            badge.textContent = status;
            badge.className = `badge status-badge ${getStatusClass(status)}`;
            row.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
            setTimeout(() => row.style.backgroundColor = '', 1000);
        }

        // Update the refresh interval at the bottom of your script
        setInterval(() => {
            fetchOrders();
        }, 5000); // Refresh every 5 seconds instead of real-time
        
        // Online users counter
        function updateOnlineUsers() {
            fetch('get_online_users.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('onlineCount').textContent = data.count;
                })
                .catch(error => console.error('Error:', error));
        }

        // Update every 3 seconds
        setInterval(updateOnlineUsers, 3000);
        updateOnlineUsers(); // Initial load

        // Session heartbeat
        setInterval(() => {
            fetch('online_check.php');
        }, 60000); // Every minute
    </script>
</body>
</html>
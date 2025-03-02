<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

require 'config.php';

$message = '';
$all_orders = [];

// Process order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];
        $cart_items = json_decode($_POST['cart_items'], true) ?? [];
        
        if (!empty($cart_items) && in_array($payment_method, ['cash', 'cashless'])) {
            $order_summary = [];
            $total = 0;
            
            foreach ($cart_items as $item) {
                $order_summary[] = "{$item['name']} (Qty: {$item['quantity']})";
                $total += $item['price'] * $item['quantity'];
            }
            
            $order_details = implode(", ", $order_summary) . " | Total: ₱" . number_format($total, 2);
            $stmt = $conn->prepare("INSERT INTO orders 
                (student_username, order_details, payment_method, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())");
            
            $stmt->bind_param("sss", 
                $_SESSION['username'], 
                $order_details, 
                $payment_method
            );
            
            if ($stmt->execute()) {
                $message = "Order #" . $conn->insert_id . " placed successfully! Status: Pending";
            } else {
                $message = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get all orders
$order_stmt = $conn->prepare("SELECT id, order_details, status, created_at FROM orders 
                            WHERE student_username = ? 
                            ORDER BY created_at DESC");
if (!$order_stmt) {
    die("Prepare failed: " . $conn->error);
}

$order_stmt->bind_param("s", $_SESSION['username']);
if (!$order_stmt->execute()) {
    die("Execute failed: " . $order_stmt->error);
}

$order_result = $order_stmt->get_result();
$all_orders = [];
while ($row = $order_result->fetch_assoc()) {
    $all_orders[] = $row;
}
$order_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CanteenGo! - Student</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success py-3">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="#" style="font-size: 2rem; letter-spacing: -2px;">
            <span style="color:rgb(0, 87, 20); font-weight: 800;">CANTEEN</span>
            <span class="go-white bg-dark rounded px-3 py-1" style="font-size: 1.8rem; margin-left: 2px;">GO!</span>
        </a>

        <!-- Mobile Menu Toggle -->
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Menu Items -->
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-truncate" style="max-width: 150px;">
                        Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-block d-lg-none" href="logout.php">Logout</a>
                </li>
            </ul>
            
            <!-- Desktop Logout (hidden on mobile) -->
            <div class="d-none d-lg-block">
                <a class="btn btn-outline-light" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <button class="btn btn-outline-success mb-3" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#orderHistory">
        <i class="bi bi-clock-history"></i> Toggle Order History
    </button>

    <div class="row">
        <div class="col-lg-8 order-1 order-lg-1" id="mainContent">
            <div class="container mt-4">
                <h2 class="mb-4">Place Your Order</h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="meals-tab" data-bs-toggle="tab" 
                            data-bs-target="#meals" type="button" role="tab" 
                            aria-controls="meals" aria-selected="true">Meals</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="drinks-tab" data-bs-toggle="tab"
                            data-bs-target="#drinks" type="button" role="tab"
                            aria-controls="drinks" aria-selected="false">Drinks</button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="meals" role="tabpanel" aria-labelledby="meals-tab">
                        <div class="products-container" id="mealsContainer"></div>
                    </div>
                    <div class="tab-pane fade" id="drinks" role="tabpanel" aria-labelledby="drinks-tab">
                        <div class="products-container" id="drinksContainer"></div>
                    </div>
                </div>

                <h3 class="mt-5">Your Cart</h3>
                <div class="table-responsive">
                    <table class="table table-bordered cart-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price (₱)</th>
                                <th>Quantity</th>
                                <th>Subtotal (₱)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th id="cartTotal">0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button class="btn btn-danger" onclick="clearCart()">Clear Cart</button>
                    <button class="btn btn-success" onclick="showPaymentModal()">
                        Proceed to Checkout (₱<span id="cartTotalPreview">0.00</span>)
                    </button>
                </div>

                <form action="student.php" method="post" id="checkoutForm">
                    <input type="hidden" name="cart_items" id="cartItemsInput">
                    <input type="hidden" name="payment_method" id="paymentMethodInput">
                </form>
            </div>
        </div>
        <!-- Right Column - Order History -->
        <div class="col-lg-4 order-2 order-lg-2 collapse show" id="orderHistoryCol">
            <div class="collapse show" id="orderHistory">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-list-task"></i> Order History</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($all_orders)): ?>
                            <div class="list-group list-group-flush" style="max-height: 60vh; overflow-y: auto;">
                                <?php foreach ($all_orders as $order): ?>
                                    <div class="list-group-item order-history-item <?= $order['status'] === 'complete' ? 'list-group-item-success' : '' ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderDetailModal"
                                        data-order-id="<?= $order['id'] ?>"
                                        data-order-date="<?= date('M j, g:i a', strtotime($order['created_at'])) ?>"
                                        data-order-details="<?= htmlspecialchars($order['order_details']) ?>"
                                        data-order-status="<?= $order['status'] ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="me-3">
                                                <small class="text-muted">#<?= $order['id'] ?></small>
                                                <div class="fw-bold">
                                                    <?= ucfirst($order['status']) ?>
                                                </div>
                                                <small>
                                                    <?= date('M j, g:i a', strtotime($order['created_at'])) ?>
                                                </small>
                                            </div>
                                            
                                            <?php if($order['status'] === 'claimable'): ?>
                                                <form action="claim_order.php" method="post">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-check-circle"></i> Claim
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-<?= match($order['status']) {
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'complete' => 'success',
                                                    'claimable' => 'primary'
                                                } ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-3 text-muted">No order history found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Payment Modal -->
<div class="modal fade" id="paymentModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Cash Card -->
                    <div class="col-md-6">
                        <div class="card payment-card h-100" data-payment="cash">
                            <div class="card-body text-center">
                                <i class="bi bi-cash-coin fs-1 text-success"></i>
                                <h5 class="mt-2">Cash</h5>
                                <small>Pay with physical money</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cashless Card -->
                    <div class="col-md-6">
                        <div class="card payment-card h-100" data-payment="cashless">
                            <div class="card-body text-center">
                                <i class="bi bi-credit-card fs-1 text-primary"></i>
                                <h5 class="mt-2">Cashless</h5>
                                <small>Digital payment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="proceedToCheckout()">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="orderDetailModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">CanteenGo! - Order #<span id="modalOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Order Date:</small>
                        <p class="mb-0" id="modalOrderDate"></p>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">Status:</small>
                        <span class="badge" id="modalOrderStatus"></span>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Order Details:</h6>
                <div class="bg-light p-3 rounded" id="modalOrderItems"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Product data
    const meals = [
        { id: 1, name: 'Burger', price: 20, image: 'images/burger.jpg' },
        { id: 2, name: 'Pastil', price: 25, image: 'images/pastil.jpg' },
        { id: 3, name: 'Bicol Express', price: 46, image: 'images/bicol-express.jpg' },
        { id: 7, name: 'Fried Chicken', price: 55, image: 'images/fried-chicken.jpg' },
        { id: 8, name: 'Adobo', price: 45, image: 'images/adobo.jpg' },
        { id: 9, name: 'Ampalaya', price: 59, image: 'images/ampalaya.jpg' },
        { id: 10, name: 'Giniling', price: 50, image: 'images/giniling.jpg' },
        { id: 11, name: 'Hotdog', price: 25, image: 'images/hotdog.jpg' },
        { id: 12, name: 'Egg with Rice', price: 35, image: 'images/egg.jpg' }
    ];
    
    const drinks = [
        { id: 4, name: 'Coke', price: 45, image: 'images/coke.jpg' },
        { id: 5, name: 'Juice', price: 20, image: 'images/juice.jpg' },
        { id: 6, name: 'Water', price: 15, image: 'images/water.jpg' },
        { id: 13, name: 'Iced Tea', price: 25, image: 'images/ice-tea.jpg' },
        { id: 14, name: 'C2 Yellow', price: 35, image: 'images/c2-yellow.jpg' },
        { id: 15, name: 'C2 Green', price: 35, image: 'images/c2-green.jpg' },
        { id: 16, name: 'C2 Red', price: 35, image: 'images/c2-red.jpg' },
        { id: 17, name: 'Coffee', price: 35, image: 'images/coffee.jpg' },
        { id: 18, name: 'Sprite', price: 45, image: 'images/sprite.jpg' }
    ];

    let cart = {};

    // Cart functions
    function renderProducts(products, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = products.map(product => `
            <div class="product-card">
                <div class="card h-100">
                    <img src="${product.image}" class="card-img-top" alt="${product.name}">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">₱${product.price.toFixed(2)}</p>
                        <button class="btn btn-success mt-auto" 
                            onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateCartDisplay() {
        const cartBody = document.getElementById('cartBody');
        let total = 0;
        
        cartBody.innerHTML = Object.values(cart).map(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            return `
                <tr>
                    <td>${item.name}</td>
                    <td>₱${item.price.toFixed(2)}</td>
                    <td>
                        <input type="number" min="1" value="${item.quantity}" 
                            style="width: 60px;" 
                            onchange="updateQuantity(${item.id}, this.value)">
                    </td>
                    <td>₱${subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" 
                            onclick="removeFromCart(${item.id})">Remove</button>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('cartTotal').textContent = total.toFixed(2);
        document.getElementById('cartTotalPreview').textContent = total.toFixed(2);
        document.getElementById('cartItemsInput').value = JSON.stringify(Object.values(cart));
    }

    function addToCart(id, name, price) {
        cart[id] = cart[id] || { id, name, price, quantity: 0 };
        cart[id].quantity++;
        updateCartDisplay();
    }

    function removeFromCart(id) {
        delete cart[id];
        updateCartDisplay();
    }

    function updateQuantity(id, quantity) {
        if (quantity >= 1) cart[id].quantity = parseInt(quantity);
        updateCartDisplay();
    }

    function clearCart() {
        cart = {};
        updateCartDisplay();
    }

    let selectedPayment = null;

    function showPaymentModal() {
        if (Object.keys(cart).length === 0) {
            alert('Your cart is empty!');
            return;
        }
        selectedPayment = null; // Reset selection
        document.querySelectorAll('.payment-card').forEach(card => {
            card.classList.remove('active');
        });
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    // Add event listeners for payment cards
    document.querySelectorAll('.payment-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('active'));

            // Add active class to selected card
            this.classList.add('active');
            selectedPayment = this.dataset.payment;
        });
    });

    function proceedToCheckout() {
        if (!selectedPayment) {
            alert('Please select a payment method!');
            return;
        }
        document.getElementById('paymentMethodInput').value = selectedPayment;
        document.getElementById('checkoutForm').submit();
    }

    // Initialize
    renderProducts(meals, 'mealsContainer');
    renderProducts(drinks, 'drinksContainer');
    document.getElementById('orderHistoryCol').addEventListener('shown.bs.collapse', function() {
        if(window.innerWidth >= 992) {
            document.getElementById('mainContent').classList.remove('col-lg-12');
            document.getElementById('mainContent').classList.add('col-lg-8');
        }
    });

    document.getElementById('orderHistoryCol').addEventListener('hidden.bs.collapse', function() {
        if(window.innerWidth >= 992) {
            document.getElementById('mainContent').classList.remove('col-lg-8');
            document.getElementById('mainContent').classList.add('col-lg-12');
        }
    });
    let currentOrderHash = '';

    function fetchStudentOrders() {
        // CORRECT THE ENDPOINT URL (replace space with underscore)
        fetch(`get_student_orders.php?t=${Date.now()}`) // Proper endpoint name
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(orders => {
            const newHash = orders.map(o => `${o.id}-${o.status}`).join('|');
            if (newHash !== currentOrderHash) {
                updateOrderStatus(orders);
                updateOrderHistory(orders); // This now uses proper Bootstrap modal binding
                currentOrderHash = newHash;
            }
        })
        .catch(error => console.error('Fetch error:', error));
    }

    function updateOrderStatus(orders) {
        const latestOrder = orders[0];
        if(!latestOrder) return;
    
        const statusBadge = document.querySelector('.order-status .badge');
        if(!statusBadge) return;
    
        const newStatus = latestOrder.status.toLowerCase();
        if(statusBadge.textContent !== newStatus) {
            statusBadge.textContent = newStatus;
            statusBadge.className = `badge bg-${newStatus === 'complete' ? 'success' : 
                                   newStatus === 'processing' ? 'info' : 'warning'}`;

            // Add visual feedback
            statusBadge.parentElement.animate([
                { backgroundColor: 'rgba(25, 135, 84, 0.2)' },
                { backgroundColor: 'transparent' }
            ], 500);
        }
    }

    // Update polling to 2 seconds
    setInterval(fetchStudentOrders, 2000);
    function getStudentStatusClass(status) {
        return {
            'pending': 'warning',
            'processing': 'info',
            'complete': 'success'
        }[status] || 'secondary';
    }
    
    function updateOrderHistory(orders) {
        const container = document.querySelector('#orderHistory .list-group');
        if (!container) return;

        container.innerHTML = orders.map(order => {
            const isClaimable = order.status.toLowerCase() === 'claimable';
            return `
                <div class="list-group-item order-history-item ${order.status === 'complete' ? 'list-group-item-success' : ''}"
                    data-bs-toggle="modal"
                    data-bs-target="#orderDetailModal"
                    data-order-id="${order.id}"
                    data-order-date="${new Date(order.created_at).toLocaleString()}"
                    data-order-details="${order.order_details}"
                    data-order-status="${order.status}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <small class="text-muted">#${order.id}</small>
                            <div class="fw-bold">${order.status}</div>
                            <small>${new Date(order.created_at).toLocaleString()}</small>
                        </div>
                        ${isClaimable ? 
                            `<form action="claim_order.php" method="post">
                                <input type="hidden" name="order_id" value="${order.id}">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-circle"></i> Claim
                                </button>
                            </form>` :
                            `<span class="badge bg-${getStudentStatusClass(order.status.toLowerCase())}">
                                ${order.status}
                            </span>`
                        }
                    </div>
                </div>
            `;
        }).join('');
    }

    function getStudentStatusClass(status) {
        return {
            'pending': 'warning',
            'processing': 'info',
            'complete': 'success',
            'claimable': 'primary' // Added 'claimable'
        }[status] || 'secondary';
    }
    const orderDetailModal = document.getElementById('orderDetailModal');

    orderDetailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // The element that triggered the modal
        const item = button.closest('.list-group-item');

        const details = item.dataset.orderDetails;
        const orderId = item.dataset.orderId;
        const orderDate = item.dataset.orderDate;
        const status = item.querySelector('.badge').textContent.trim();
    
        // Format details with proper line breaks
        const [itemsPart, totalPart] = details.split('|');
        const formattedDetails = `CanteenGo! Order #${orderId}\n\n${
            itemsPart.split(', ')
                .map(item => item.trim())
                .join('\n')
        }\n\n${totalPart.trim()}`;
    
        // Update modal elements
        document.getElementById('modalOrderId').textContent = orderId;
        document.getElementById('modalOrderDate').textContent = orderDate;
        document.getElementById('modalOrderStatus').textContent = status;
        document.getElementById('modalOrderItems').textContent = formattedDetails;
    
        // Update status badge color
        const statusBadge = document.getElementById('modalOrderStatus');
        statusBadge.className = `badge bg-${status === 'complete' ? 'success' : 
                               status === 'processing' ? 'info' : 'warning'}`;
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Proper modal handling
        const orderModal = document.getElementById('orderDetailModal');

        orderModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.dataset.orderId;
            const orderDate = button.dataset.orderDate;
            const status = button.dataset.orderStatus;
            const details = button.dataset.orderDetails;
        
            // Format details with line breaks
            const formattedDetails = details.split('|')[0].split(', ')
                .map(item => item.trim())
                .join('\n') + 
                '\n\n' + 
                details.split('|')[1].trim();
        
            // Update modal content
            document.getElementById('modalOrderId').textContent = orderId;
            document.getElementById('modalOrderDate').textContent = orderDate;
            document.getElementById('modalOrderStatus').textContent = status;
            document.getElementById('modalOrderItems').textContent = formattedDetails;

            // Style status badge
            const statusBadge = document.getElementById('modalOrderStatus');
            statusBadge.className = `badge bg-${
                status === 'complete' ? 'success' :
                status === 'processing' ? 'info' : 'warning'
            }`;
        });
    });
    // Single modal handler at the bottom of your script
    document.getElementById('orderDetailModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.dataset.orderId;
        const orderDate = button.dataset.orderDate;
        const status = button.dataset.orderStatus;
        const details = button.dataset.orderDetails;
    
        // Format details with line breaks
        const [items, total] = details.split('|');
        const formattedDetails = `CanteenGo! Order #${orderId}\n\n${
            items.split(', ')
                .map(item => item.trim())
                .join('\n')
        }\n\n${total.trim()}`;
    
        // Update modal elements
        const modal = this;
        modal.querySelector('#modalOrderId').textContent = orderId;
        modal.querySelector('#modalOrderDate').textContent = orderDate;
        modal.querySelector('#modalOrderStatus').textContent = status;
        modal.querySelector('#modalOrderItems').textContent = formattedDetails;
        
        // Update badge color
        const statusBadge = modal.querySelector('#modalOrderStatus');
        statusBadge.className = `badge bg-${status === 'complete' ? 'success' : 
                               status === 'processing' ? 'info' :
                               status === 'claimable' ? 'primary' : 'warning'}`;
    });
</script>
</body>
</html>
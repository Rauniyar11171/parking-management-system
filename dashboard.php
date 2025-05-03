<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's bookings
$stmt = $pdo->prepare("SELECT 
    id,
    booking_reference,
    location,
    booking_date,
    booking_time,
    duration,
    vehicle_number,
    amount,
    booking_status,
    payment_status,
    payment_method,
    created_at
FROM bookings 
WHERE user_id = ? 
ORDER BY created_at DESC");

// For debugging
try {

    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output
    error_log('User ID: ' . $_SESSION['user_id']);
    error_log('Found bookings: ' . count($bookings));
    if (count($bookings) > 0) {
        error_log('Latest booking: ' . json_encode($bookings[0]));
    }
} catch (PDOException $e) {
    error_log('Dashboard Error: ' . $e->getMessage());
    $bookings = [];
}

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3">
                <!-- Sidebar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['email']); ?></h5>
                                <small class="text-muted">Member since <?php echo date('M Y'); ?></small>
                            </div>
                        </div>
                        <hr>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="#bookings">
                                    <i class="fas fa-ticket-alt me-2"></i> My Bookings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="book-slot.php">
                                    <i class="fas fa-plus-circle me-2"></i> New Booking
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <!-- Bookings Section -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">My Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                <h5>No Bookings Yet</h5>
                                <p class="text-muted">You haven't made any parking bookings yet.</p>
                                <a href="book-slot.php" class="btn btn-primary">Book Now</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Location</th>
                                            <th>Date & Time</th>
                                            <th>Duration</th>
                                            <th>Vehicle</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($booking['booking_reference']); ?>
                                                    </span>
                                                </td>

                                                <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                                <td>
                                                    <?php 
                                                        if ($booking['booking_date'] && $booking['booking_time']) {
                                                            echo date('d M Y', strtotime($booking['booking_date'])) . '<br>';
                                                            echo date('h:i A', strtotime($booking['booking_time']));
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                    ?>
                                                </td>
                                                <td><?php echo $booking['duration'] ? $booking['duration'] . ' hr(s)' : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($booking['vehicle_number']); ?></td>
                                                <td>₹<?php echo number_format($booking['amount'] ?? 0, 2); ?></td>
                                                <td>
                                                    <?php
                                                        $statusClass = '';
                                                        $status = $booking['booking_status'] ?? 'Pending';
                                                        switch ($status) {
                                                            case 'Active':
                                                                $statusClass = 'success';
                                                                break;
                                                            case 'Completed':
                                                                $statusClass = 'info';
                                                                break;
                                                            case 'Cancelled':
                                                                $statusClass = 'danger';
                                                                break;
                                                            default:
                                                                $statusClass = 'warning';
                                                        }
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars($status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($booking['booking_status'] == 'Active'): ?>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <button type="submit" name="cancel_booking" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-times"></i> Cancel
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            onclick="viewBookingDetails('<?php echo $booking['id']; ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBookingDetails(bookingId) {
            fetch(`get_booking_details.php?id=${bookingId}`)
                .then(response => response.json())
                .then(booking => {
                    const paymentDetails = JSON.parse(booking.payment_details || '{}');
                    let paymentInfo = '';
                    
                    if (booking.payment_method === 'upi') {
                        paymentInfo = `UPI ID: ${paymentDetails.upiId}`;
                    } else if (booking.payment_method === 'card') {
                        const cardNumber = paymentDetails.cardNumber;
                        const lastFour = cardNumber.slice(-4);
                        paymentInfo = `Card ending in ${lastFour}`;
                    }

                    const content = `
                        <div class="booking-details">
                            <div class="row mb-3">
                                <div class="col-5">Booking Reference:</div>
                                <div class="col-7 fw-bold">${booking.booking_reference}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Location:</div>
                                <div class="col-7">${booking.location}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Date & Time:</div>
                                <div class="col-7">${new Date(booking.booking_date + ' ' + booking.booking_time).toLocaleString()}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Duration:</div>
                                <div class="col-7">${booking.duration} hour(s)</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Vehicle Number:</div>
                                <div class="col-7">${booking.vehicle_number}</div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-5">Amount Paid:</div>
                                <div class="col-7">₹${parseFloat(booking.amount).toFixed(2)}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Payment Method:</div>
                                <div class="col-7">${booking.payment_method.toUpperCase()}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5">Payment Details:</div>
                                <div class="col-7">${paymentInfo}</div>
                            </div>
                            <div class="row">
                                <div class="col-5">Status:</div>
                                <div class="col-7">
                                    <span class="badge bg-${getStatusClass(booking.booking_status)}">
                                        ${booking.booking_status}
                                    </span>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('bookingDetailsContent').innerHTML = content;
                    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Error loading booking details');
                });
        }

        function getStatusClass(status) {
            switch (status) {
                case 'Active': return 'success';
                case 'Completed': return 'info';
                case 'Cancelled': return 'danger';
                default: return 'warning';
            }
        }
    </script>
</body>
</html>

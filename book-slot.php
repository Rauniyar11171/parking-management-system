<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables for form messages
$success = '';
$error = '';

// AJAX booking from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['district']) && isset($_POST['date']) && isset($_POST['time']) && isset($_POST['duration']) && isset($_POST['vehicle']) && isset($_POST['payment_method'])) {
    header('Content-Type: application/json');
    try {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please login again.']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Verify user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid user account. Please login again.']);
            exit();
        }
        $location = trim($_POST['district']);
        $date = trim($_POST['date']);
        $time = trim($_POST['time']);
        $duration = intval($_POST['duration']);
        $vehicle = trim($_POST['vehicle']);
        $payment_method = trim($_POST['payment_method']);
        $amount = 50 * $duration; // Example: Rs.50/hr
        if (empty($location) || empty($date) || empty($time) || $duration <= 0 || empty($vehicle) || empty($payment_method)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all fields.']);
            exit();
        }
        $booking_date = date('Y-m-d', strtotime($date));
        $booking_time = date('H:i:s', strtotime($time));
        if (strtotime($booking_date . ' ' . $booking_time) < time()) {
            echo json_encode(['success' => false, 'message' => 'Please select a future date and time.']);
            exit();
        }
        // Check for duplicate booking
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE location = ? AND booking_date = ? AND booking_time = ? AND booking_status != 'Cancelled'");
        $stmt->execute([$location, $booking_date, $booking_time]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'This slot is already booked. Please select a different time.']);
            exit();
        }
        $booking_reference = 'BK' . date('ymd') . strtoupper(substr(uniqid(), -6));
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, booking_reference, location, booking_date, booking_time, duration, vehicle_number, amount, payment_method, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', 'Active', NOW())");
        $success = $stmt->execute([
            $user_id,
            $booking_reference,
            $location,
            $booking_date,
            $booking_time,
            $duration,
            $vehicle,
            $amount,
            $payment_method
        ]);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Booking confirmed!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not save booking. Try again.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

// Only set JSON header for AJAX requests
if (isset($_POST['process_payment'])) {
    header('Content-Type: application/json');
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    try {
        // Get and validate form data
        $user_id = $_SESSION['user_id'];
        $location = trim($_POST['location'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $vehicle_number = trim($_POST['vehicle_number'] ?? '');
        $amount = floatval(str_replace(['₹', ','], '', $_POST['amount'] ?? '0'));
        $payment_method = trim($_POST['payment_method'] ?? '');

        // Debug log
        error_log('Payment Data: ' . json_encode([
            'user_id' => $user_id,
            'location' => $location,
            'date' => $date,
            'time' => $time,
            'duration' => $duration,
            'vehicle_number' => $vehicle_number,
            'amount' => $amount,
            'payment_method' => $payment_method
        ]));

        // Validate required fields
        if (empty($location) || empty($date) || empty($time) || $duration <= 0 || 
            empty($vehicle_number) || $amount <= 0 || empty($payment_method)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
            exit();
        }

        // Validate date and time
        $booking_date = date('Y-m-d', strtotime($date));
        $booking_time = date('H:i:s', strtotime($time));
        $booking_datetime = $booking_date . ' ' . $booking_time;

        if (strtotime($booking_datetime) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a future date and time']);
            exit();
        }

        // Generate unique booking reference
        $booking_reference = 'BK' . date('ymd') . strtoupper(substr(uniqid(), -6));

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Check for duplicate booking
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE location = ? AND booking_date = ? AND booking_time = ? AND booking_status != 'Cancelled'");
            $stmt->execute([$location, $booking_date, $booking_time]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('This slot is already booked. Please select a different time.');
            }

            // Insert booking
            $stmt = $pdo->prepare("INSERT INTO bookings 
                (user_id, booking_reference, location, booking_date, booking_time, 
                 duration, vehicle_number, amount, payment_method, payment_status, booking_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', 'Active', NOW())");

            $success = $stmt->execute([
                $user_id,
                $booking_reference,
                $location,
                $booking_date,
                $booking_time,
                $duration,
                $vehicle_number,
                $amount,
                $payment_method
            ]);

            if (!$success) {
                throw new Exception('Failed to save booking');
            }

            // Commit the transaction
            $pdo->commit();

            // Debug log
            error_log('Booking saved successfully: ' . $booking_reference);

            echo json_encode([
                'status' => 'success',
                'message' => 'Booking confirmed successfully!',
                'booking_reference' => $booking_reference
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Booking Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Database Error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'A database error occurred. Please try again.']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred. Please try again.']);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['process_payment'])) {
    // Handle regular form submission
    $location = trim($_POST['location'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $vehicle_number = trim($_POST['vehicle_number'] ?? '');
    
    if (empty($location) || empty($date) || empty($time) || $duration <= 0 || empty($vehicle_number)) {
        $error = 'Please fill in all required fields';
    } else {
        // Validate date and time
        $booking_datetime = strtotime($date . ' ' . $time);
        if ($booking_datetime === false || $booking_datetime < time()) {
            $error = 'Please select a future date and time';
        } else {
            try {
                // Check for duplicate booking
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE location = ? AND booking_date = ? AND booking_time = ? AND booking_status != 'cancelled'");
                $stmt->execute([$location, $date, $time]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'This slot is already booked. Please select a different time.';
                } else {
                    // Generate booking reference
                    $booking_reference = 'BK' . date('ymd') . strtoupper(substr(uniqid(), -6));
                    
                    // Insert booking
                    $stmt = $pdo->prepare("INSERT INTO bookings 
                        (user_id, booking_reference, location, booking_date, booking_time, duration, 
                         vehicle_number, booking_status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    
                    if ($stmt->execute([
                        $_SESSION['user_id'],
                        $booking_reference,
                        $location,
                        $date,
                        $time,
                        $duration,
                        $vehicle_number
                    ])) {
                        $success = 'Please proceed with payment to confirm your booking.';
                    } else {
                        $error = 'Unable to create booking. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                error_log('Booking Error: ' . $e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

// Get location from URL if provided
$selected_location = $_GET['location'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Parking Slot - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid py-5">
        <h2 class="text-center mb-4">Book Parking Slot</h2>
        <div class="alert alert-info text-center mb-4" style="font-size:1.1rem;">
            <i class="fas fa-info-circle me-2"></i>Parking Rates: <strong>₹30–₹60/hour</strong> (varies by location)
        </div>

        <!-- Region Selection Tabs -->
        <ul class="nav nav-pills justify-content-center mb-4" id="regionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#delhi" type="button">
                    New Delhi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#up" type="button">
                    Uttar Pradesh
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- New Delhi Section -->
            <div class="tab-pane fade show active" id="delhi">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/red fort.jpg" class="card-img-top" alt="Red Fort" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Red Fort Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹50/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Red Fort Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/india gate.jpg" class="card-img-top" alt="India Gate" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">India Gate Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹60/hour</div>
                                <div class="availability text-warning">
                                    <i class="fas fa-circle"></i> Medium Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('India Gate Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/qutub minar.jpg" class="card-img-top" alt="Qutub Minar" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Qutub Minar Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹40/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Qutub Minar Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/lotus temple.jpg" class="card-img-top" alt="Lotus Temple" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Lotus Temple Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹45/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Lotus Temple Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/akshardham temple.jpg" class="card-img-top" alt="Akshardham Temple" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Akshardham Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹40/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Akshardham Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card delhi">
                            <span class="location-badge delhi">New Delhi</span>
                            <img src="images/Humayun's Tomb.jpg" class="card-img-top" alt="Humayun's Tomb" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Humayun's Tomb Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹45/hour</div>
                                <div class="availability text-warning">
                                    <i class="fas fa-circle"></i> Medium Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Humayun\'s Tomb Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uttar Pradesh Section -->
            <div class="tab-pane fade" id="up">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/taj mahal.jpg" class="card-img-top" alt="Taj Mahal" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Taj Mahal Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹60/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Taj Mahal Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/fatehpur sikri.jpg" class="card-img-top" alt="Fatehpur Sikri" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Fatehpur Sikri Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹40/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Fatehpur Sikri Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/Bara Imambara.jpg" class="card-img-top" alt="Bara Imambara" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Bara Imambara Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹35/hour</div>
                                <div class="availability text-warning">
                                    <i class="fas fa-circle"></i> Medium Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Bara Imambara Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/Bhool Bhulaiya.jpg" class="card-img-top" alt="Bhool Bhulaiya" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Bhool Bhulaiya Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹35/hour</div>
                                <div class="availability text-warning">
                                    <i class="fas fa-circle"></i> Medium Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Bhool Bhulaiya Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/Rumi Darwaza.jpg" class="card-img-top" alt="Rumi Darwaza" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Rumi Darwaza Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹30/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Rumi Darwaza Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card booking-card up">
                            <span class="location-badge up">Uttar Pradesh</span>
                            <img src="images/sarnath.jpg" class="card-img-top" alt="Sarnath" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Sarnath Parking</h5>
                                <div class="price-tag" style="background-color: white;">₹35/hour</div>
                                <div class="availability text-success">
                                    <i class="fas fa-circle"></i> High Availability
                                </div>
                                <button class="btn btn-book w-100" onclick="selectLocation('Sarnath Parking')">Select Spot</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Booking Summary -->
                        <div class="booking-summary mb-4">
                            <h6 class="fw-bold mb-3">Booking Summary</h6>
                            <div class="row mb-2">
                                <div class="col-6">Location:</div>
                                <div class="col-6 fw-bold" id="selectedLocation"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">Date & Time:</div>
                                <div class="col-6 fw-bold" id="selectedDateTime"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">Duration:</div>
                                <div class="col-6 fw-bold" id="selectedDuration"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">Vehicle Number:</div>
                                <div class="col-6 fw-bold" id="selectedVehicle"></div>
                            </div>
                            <hr>
                            <div class="row mb-2">
                                <div class="col-6">Parking Charges:</div>
                                <div class="col-6 fw-bold">₹<span id="parkingCharges">0</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">GST (18%):</div>
                                <div class="col-6 fw-bold">₹<span id="gstAmount">0</span></div>
                            </div>
                            <div class="row">
                                <div class="col-6">Total Amount:</div>
                                <div class="col-6 fw-bold">₹<span id="totalAmount">0</span></div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form id="paymentForm">
                            <h6 class="fw-bold mb-3">Select Payment Method</h6>
                            <div class="payment-methods">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="upiPayment" value="upi" checked>
                                    <label class="form-check-label" for="upiPayment">
                                        UPI <i class="fas fa-mobile-alt"></i>
                                    </label>
                                    <div class="payment-method-form mt-2" id="upiForm">
                                        <input type="text" class="form-control" placeholder="Enter UPI ID" required>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="cardPayment" value="card">
                                    <label class="form-check-label" for="cardPayment">
                                        Credit/Debit Card <i class="fas fa-credit-card"></i>
                                    </label>
                                    <div class="payment-method-form mt-2" id="cardForm" style="display: none;">
                                        <input type="text" class="form-control mb-2" placeholder="Card Number" required>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="text" class="form-control" placeholder="MM/YY" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control" placeholder="CVV" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-4" id="processPaymentBtn">
                                Pay Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Confirmation Modal -->
        <div class="modal fade" id="paymentConfirmationModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-shield-alt text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-center mb-4">Please confirm your payment</h5>
                        <div class="confirmation-details p-3 bg-light rounded mb-4">
                            <div class="row mb-3">
                                <div class="col-6">Payment Method:</div>
                                <div class="col-6 fw-bold" id="confirmPaymentMethod"></div>
                            </div>
                            <div class="row">
                                <div class="col-6">Total Amount:</div>
                                <div class="col-6 fw-bold">₹<span id="confirmAmount"></span></div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> By clicking "Confirm Payment", you agree to proceed with the payment.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Payment Successful</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Content will be dynamically inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="form-container">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form id="bookingForm" method="POST" action="book-slot.php" onsubmit="return validateForm(event)">
                        <div class="mb-3">
                            <label for="location" class="form-label">Selected Location</label>
                            <input type="text" class="form-control" id="selected_location" name="location" readonly required>
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>

                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" name="time" required 
                                   min="06:00" max="22:00">
                            <small class="text-muted">Booking available from 6:00 AM to 10:00 PM</small>
                        </div>

                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (hours)</label>
                            <select class="form-select" id="duration" name="duration" required>
                                <option value="">Select Duration</option>
                                <option value="1">1 hour</option>
                                <option value="2">2 hours</option>
                                <option value="3">3 hours</option>
                                <option value="4">4 hours</option>
                                <option value="5">5 hours</option>
                                <option value="6">6 hours</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="vehicle_number" class="form-label">Vehicle Number</label>
                            <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Proceed to Payment</button>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'includes/footer.php'; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            function selectLocation(locationName) {
                document.getElementById('selected_location').value = locationName;
                document.getElementById('selected_location').scrollIntoView({ behavior: 'smooth' });
            }
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('bookingForm');
                const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                const paymentConfirmationModal = new bootstrap.Modal(document.getElementById('paymentConfirmationModal'));
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));

                // Handle payment method switching
                const upiPayment = document.getElementById('upiPayment');
                const cardPayment = document.getElementById('cardPayment');
                const upiForm = document.getElementById('upiForm');
                const cardForm = document.getElementById('cardForm');

                upiPayment.addEventListener('change', function() {
                    upiForm.style.display = 'block';
                    cardForm.style.display = 'none';
                });

                cardPayment.addEventListener('change', function() {
                    upiForm.style.display = 'none';
                    cardForm.style.display = 'block';
                });

                // Handle booking form submission
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Update payment modal with booking details
                    document.getElementById('selectedLocation').textContent = document.getElementById('selected_location').value;
                    document.getElementById('selectedDateTime').textContent = 
                        document.getElementById('date').value + ' ' + document.getElementById('time').value;
                    document.getElementById('selectedDuration').textContent = 
                        document.getElementById('duration').value + ' hour(s)';
                    document.getElementById('selectedVehicle').textContent = 
                        document.getElementById('vehicle_number').value;

                    // Calculate charges
                    const duration = parseInt(document.getElementById('duration').value);
                    const baseRate = 50; // Base rate per hour
                    const parkingCharges = baseRate * duration;
                    const gst = parkingCharges * 0.18;
                    const total = parkingCharges + gst;

                    document.getElementById('parkingCharges').textContent = parkingCharges.toFixed(2);
                    document.getElementById('gstAmount').textContent = gst.toFixed(2);
                    document.getElementById('totalAmount').textContent = total.toFixed(2);

                    paymentModal.show();
                });

                // Handle payment form submission
                document.getElementById('processPaymentBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get payment method and amount
                    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                    const totalAmount = document.getElementById('totalAmount').textContent;
                    
                    // Validate payment details
                    if (paymentMethod === 'upi') {
                        const upiId = document.querySelector('#upiForm input').value;
                        if (!upiId) {
                            alert('Please enter UPI ID');
                            return;
                        }
                        if (!upiId.includes('@')) {
                            alert('Please enter a valid UPI ID (e.g., username@upi)');
                            return;
                        }
                    } else if (paymentMethod === 'card') {
                        const cardNumber = document.querySelector('#cardForm input[placeholder="Card Number"]').value;
                        const expiry = document.querySelector('#cardForm input[placeholder="MM/YY"]').value;
                        const cvv = document.querySelector('#cardForm input[placeholder="CVV"]').value;
                        
                        if (!cardNumber || !expiry || !cvv) {
                            alert('Please fill in all card details');
                            return;
                        }
                        
                        // Basic card validation
                        if (cardNumber.replace(/\s/g, '').length !== 16) {
                            alert('Please enter a valid 16-digit card number');
                            return;
                        }
                        if (!expiry.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) {
                            alert('Please enter expiry date in MM/YY format');
                            return;
                        }
                        if (cvv.length !== 3) {
                            alert('Please enter a valid 3-digit CVV');
                            return;
                        }
                    }
                    
                    // Update confirmation modal
                    document.getElementById('confirmPaymentMethod').textContent = 
                        paymentMethod === 'upi' ? 'UPI' : 'Credit/Debit Card';
                    document.getElementById('confirmAmount').textContent = totalAmount;
                    
                    // Hide payment modal and show confirmation modal
                    paymentModal.hide();
                    paymentConfirmationModal.show();
                });

                // Handle payment confirmation
                document.getElementById('confirmPaymentBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
                    
                    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                    const formData = new FormData();
                    
                    // Add booking details
                    formData.append('process_payment', '1');
                    formData.append('location', document.getElementById('selectedLocation').textContent);
                    formData.append('date', document.getElementById('date').value);
                    formData.append('time', document.getElementById('time').value);
                    formData.append('duration', document.getElementById('duration').value);
                    formData.append('vehicle_number', document.getElementById('vehicle_number').value);
                    formData.append('amount', document.getElementById('totalAmount').textContent);
                    formData.append('payment_method', paymentMethod);
                    
                    // Add payment details
                    const paymentDetails = {};
                    if (paymentMethod === 'upi') {
                        paymentDetails.upiId = document.querySelector('#upiForm input').value;
                    } else {
                        paymentDetails.cardNumber = document.querySelector('#cardForm input[placeholder="Card Number"]').value;
                        paymentDetails.expiry = document.querySelector('#cardForm input[placeholder="MM/YY"]').value;
                        paymentDetails.cvv = document.querySelector('#cardForm input[placeholder="CVV"]').value;
                    }
                    formData.append('payment_details', JSON.stringify(paymentDetails));
                    
                    // Disable the confirm button and show loading state
                    const confirmBtn = document.getElementById('confirmPaymentBtn');
                    const originalBtnText = confirmBtn.innerHTML;
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

                    // Process payment
                    fetch('book-slot.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            // Hide confirmation modal
                            paymentConfirmationModal.hide();
                            
                            // Show success message
                            const successModalBody = document.querySelector('#successModal .modal-body');
                            successModalBody.innerHTML = `
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="mb-4">Booking Confirmed!</h4>
                                    <div class="booking-details p-3 bg-light rounded mb-4">
                                        <p class="mb-2"><strong>Booking Reference:</strong><br>${data.booking_reference}</p>
                                        <p class="mb-2"><strong>Location:</strong><br>${document.getElementById('selectedLocation').textContent}</p>
                                        <p class="mb-2"><strong>Date & Time:</strong><br>${document.getElementById('date').value} ${document.getElementById('time').value}</p>
                                        <p class="mb-2"><strong>Duration:</strong><br>${document.getElementById('duration').value} hour(s)</p>
                                        <p class="mb-2"><strong>Vehicle Number:</strong><br>${document.getElementById('vehicle_number').value}</p>
                                        <p class="mb-2"><strong>Amount Paid:</strong><br>₹${document.getElementById('totalAmount').textContent}</p>
                                        <p class="mb-0"><strong>Payment Method:</strong><br>${paymentMethod === 'upi' ? 'UPI' : 'Credit/Debit Card'}</p>
                                    </div>
                                    <div class="text-center mt-4">
                                        <div class="spinner-border text-primary mb-2"></div>
                                        <p class="text-muted">Redirecting to your bookings...</p>
                                    </div>
                                </div>
                            `;
                            
                            successModal.show();
                            
                            // Reset forms
                            document.getElementById('bookingForm').reset();
                            document.getElementById('paymentForm').reset();

                            // Redirect to dashboard after 2 seconds
                            setTimeout(() => {
                                window.location.href = 'dashboard.php';
                            }, 2000);
                        } else {
                            // Show error message
                            alert(data.message || 'Payment failed. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing your payment. Please try again.');
                    })
                    .finally(() => {
                        // Always reset the confirm button state
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = originalBtnText;
                    });
                });
            });
        </script>
</body>
</html>

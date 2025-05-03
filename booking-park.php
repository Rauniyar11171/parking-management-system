<?php
session_start();
// If user not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Park - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container py-5">
        <h2 class="text-center mb-4">Booking Park</h2>
        <!-- My Location: New Delhi (District-wise) -->
        <h4 class="mb-3 text-primary">My Location: New Delhi (District-wise)</h4>
        <div class="row g-4 mb-5">
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">Central Delhi</h5>
                        <p class="card-text text-muted">Connaught Place, Karol Bagh, Daryaganj</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Central+Delhi" class="btn btn-primary">Book slot </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">North Delhi</h5>
                        <p class="card-text text-muted">Civil Lines, Model Town, Mukherjee Nagar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="North+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">South Delhi</h5>
                        <p class="card-text text-muted">Saket, Hauz Khas, Greater Kailash</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="South+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">East Delhi</h5>
                        <p class="card-text text-muted">Preet Vihar, Laxmi Nagar, Mayur Vihar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="East+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">West Delhi</h5>
                        <p class="card-text text-muted">Rajouri Garden, Janakpuri, Punjabi Bagh</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="West+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">South West Delhi</h5>
                        <p class="card-text text-muted">Dwarka, Vasant Kunj, Najafgarh</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="South+West+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">North East Delhi</h5>
                        <p class="card-text text-muted">Yamuna Vihar, Seelampur, Shahdara</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="North+East+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">North West Delhi</h5>
                        <p class="card-text text-muted">Rohini, Pitampura, Shalimar Bagh</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="North+West+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">South East Delhi</h5>
                        <p class="card-text text-muted">Kalkaji, Okhla, Sarita Vihar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="South+East+Delhi" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">Shahdara</h5>
                        <p class="card-text text-muted">Dilshad Garden, Vivek Vihar, Jhilmil Colony</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Shahdara" class="btn btn-primary">Book slot</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- My Location: Uttar Pradesh (District-wise) -->
        <h4 class="mb-3 text-success">My Location: Uttar Pradesh (District-wise)</h4>
        <div class="row g-4 mb-4">
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Lucknow</h5>
                        <p class="card-text text-muted">Gomti Nagar, Hazratganj, Alambagh</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Lucknow" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Kanpur</h5>
                        <p class="card-text text-muted">Swaroop Nagar, Kakadeo, Kidwai Nagar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Kanpur" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Varanasi</h5>
                        <p class="card-text text-muted">Assi Ghat, Dashashwamedh Ghat</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Varanasi" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Agra</h5>
                        <p class="card-text text-muted">Sadar Bazaar, Fatehabad Road</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Agra" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ghaziabad</h5>
                        <p class="card-text text-muted">Indirapuram, Vaishali, Raj Nagar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Ghaziabad" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Noida</h5>
                        <p class="card-text text-muted">Sector 18, Sector 62, Greater Noida</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Noida" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Meerut</h5>
                        <p class="card-text text-muted">Shastri Nagar, Modipuram, Cantonment</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Meerut" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Allahabad (Prayagraj)</h5>
                        <p class="card-text text-muted">Civil Lines, Katra, Naini</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Allahabad" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Aligarh</h5>
                        <p class="card-text text-muted">Civil Lines, Ramghat Road, Quarsi</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Aligarh" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card shadow h-100 border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Moradabad</h5>
                        <p class="card-text text-muted">Budh Bazaar, Civil Lines, Katghar</p>
                        <a href="#" class="btn btn-primary book-slot-btn" data-district="Moradabad" class="btn btn-success">Book slot</a>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="bookingForm">
            <div class="modal-header">
              <h5 class="modal-title" id="bookingModalLabel">Book Slot for <span id="modal-district-label"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="district" id="modal-district">
              <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" name="date" required>
              </div>
              <div class="mb-3">
                <label for="time" class="form-label">Time</label>
                <input type="time" class="form-control" name="time" required>
              </div>
              <div class="mb-3">
                <label for="duration" class="form-label">Duration (hours)</label>
                <input type="number" min="1" max="24" class="form-control" name="duration" required>
              </div>
              <div class="mb-3">
                <label for="vehicle" class="form-label">Vehicle Number</label>
                <input type="text" class="form-control" name="vehicle" required>
              </div>
              <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                  <option value="card">Credit/Debit Card</option>
                  <option value="upi">UPI</option>
                  <option value="netbanking">Net Banking</option>
                  <option value="qr">QR Code</option>
                </select>
              </div>
              <div id="qr-section" class="mb-3 d-none text-center">
                <p>Scan the QR Code below to pay:</p>
                <img src="images/QR Code.jpg" alt="QR Code" style="max-width:200px;" class="mb-2">
                <br>
                <button type="button" id="qr-paid-btn" class="btn btn-success">I have paid</button>
              </div>
              <div class="alert alert-success d-none" id="booking-success"></div>
              <div class="alert alert-danger d-none" id="booking-error"></div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Pay & Confirm</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/booking-modal.js"></script>
</body>
</html>

<?php
session_start();

$tourist_places = [
    'New Delhi' => [
        [
            'name' => 'Red Fort',
            'description' => 'Historic fort complex built in red sandstone, a UNESCO World Heritage site',
            'image' => 'images/red fort.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Qutub Minar',
            'description' => 'UNESCO World Heritage site featuring a 73-meter tall minaret',
            'image' => 'images/qutub minar.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'India Gate',
            'description' => 'War memorial dedicated to Indian soldiers',
            'image' => 'images/india gate.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Lotus Temple',
            'description' => 'Magnificent modern architecture in the shape of a lotus flower',
            'image' => 'images/lotus temple.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Akshardham Temple',
            'description' => 'Grand Hindu temple complex showcasing Indian culture',
            'image' => 'images/akshardham temple.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Humayun\'s Tomb',
            'description' => 'Beautiful Mughal architecture and UNESCO World Heritage site',
            'image' => 'images/Humayun\'s Tomb.jpg',
            'parking_available' => true
        ]
    ],
    'Uttar Pradesh' => [
        [
            'name' => 'Taj Mahal',
            'description' => 'Iconic marble mausoleum and UNESCO World Heritage site',
            'image' => 'images/taj mahal.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Fatehpur Sikri',
            'description' => 'Ancient city built by Mughal Emperor Akbar',
            'image' => 'images/fatehpur sikri.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Bara Imambara',
            'description' => 'Historical monument known for its unique architecture',
            'image' => 'images/Bara Imambara.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Bhool Bhulaiya',
            'description' => 'Famous labyrinth in Lucknow\'s Bara Imambara complex',
            'image' => 'images/Bhool Bhulaiya.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Rumi Darwaza',
            'description' => 'Magnificent gateway and iconic landmark of Lucknow',
            'image' => 'images/Rumi Darwaza.jpg',
            'parking_available' => true
        ],
        [
            'name' => 'Sarnath',
            'description' => 'Ancient Buddhist pilgrimage site near Varanasi',
            'image' => 'images/sarnath.jpg',
            'parking_available' => true
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Places - Smart Parking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-5">Tourist Places</h1>

        <?php foreach ($tourist_places as $region => $places): ?>
            <h2 class="mb-4"><?php echo $region; ?></h2>
            <div class="row">
                <?php foreach ($places as $place): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card tourist-card h-100">
                            <img src="<?php echo $place['image']; ?>" class="card-img-top" alt="<?php echo $place['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $place['name']; ?></h5>
                                <p class="card-text"><?php echo $place['description']; ?></p>
                                <?php if ($place['parking_available']): ?>
                                    <p class="text-success">
                                        <i class="fas fa-parking"></i> Parking Available
                                    </p>
                                    <a href="book-slot.php?location=<?php echo urlencode($place['name']); ?>" 
                                       class="btn btn-primary">Book Parking</a>
                                <?php else: ?>
                                    <p class="text-danger">
                                        <i class="fas fa-times-circle"></i> No Parking Available
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>

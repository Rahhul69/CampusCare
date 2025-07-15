<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['email']);

// Get user email from session if logged in
$userEmail = $isLoggedIn ? $_SESSION['email'] : '';
// Database Connection
$host = "localhost";
$user = "root"; // Default XAMPP user
$password = ""; // Default XAMPP password (leave empty)
$database = "campuscare";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Define holidays - You can add more holiday dates as needed
$holidays = [
    '2025-01-01', // New Year's Day  
    '2025-01-14', // Makar Sankranti  
    '2025-01-26', // Republic Day  
    '2025-03-14', // Maha Shivratri  
    '2025-03-29', // Holi  
    '2025-04-11', // Good Friday  
    '2025-04-14', // Ambedkar Jayanti  
    '2025-04-18', // Ram Navami  
    '2025-05-01', // Labour Day  
    '2025-06-05', // Eid al-Fitr (Ramzan Eid) *  
    '2025-07-06', // Bakrid (Eid al-Adha) *  
    '2025-08-15', // Independence Day  
    '2025-08-25', // Janmashtami  
    '2025-09-07', // Ganesh Chaturthi  
    '2025-10-02', // Gandhi Jayanti  
    '2025-10-03', // Dussehra  
    '2025-10-31', // Deepavali  
    '2025-12-25', // Christmas  
    // Add more holidays as needed
];

// Handle Form Submission
$message = "";
$reset_form = false;

// Initialize validation error array
$errors = array(
    'name' => '',
    'email' => '',
    'hall' => '',
    'date' => '',
    'time_slots' => '',
    'purpose' => '',
    'seats' => ''
);

// Function to check if a hall is already booked for the selected date and time slots
function checkHallAvailability($conn, $hall, $date, $time_slots) {
    $unavailable_slots = [];
    
    // Convert array of time slots to string for comparison
    $time_slots_str = implode("', '", $time_slots);
    
    // Query to check if the hall is already booked for the selected date and time slots
    $sql = "SELECT time_slot FROM hall_bookings WHERE hall = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hall, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Each row could contain multiple time slots separated by commas
        $booked_slots = explode(", ", $row['time_slot']);
        
        // Check if any of the requested time slots are already booked
        foreach ($time_slots as $requested_slot) {
            if (in_array($requested_slot, $booked_slots) && !in_array($requested_slot, $unavailable_slots)) {
                $unavailable_slots[] = $requested_slot;
            }
        }
    }
    
    return $unavailable_slots;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_booking'])) {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $hall = isset($_POST['hall']) ? $_POST['hall'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $time_slots = isset($_POST['time_slots']) ? $_POST['time_slots'] : [];
    $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';
    $seats_required = isset($_POST['seats_required']) ? $_POST['seats_required'] : '';
    
    $has_errors = false;
    
    // Validate each field
    if (empty($hall)) {
        $errors['hall'] = "Please select a hall";
        $has_errors = true;
    }
    
    if (empty($hall)) {
        $errors['hall'] = "Please select a hall";
        $has_errors = true;
    }
    
    if (empty($date)) {
        $errors['date'] = "Please select a booking date";
        $has_errors = true;
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $errors['date'] = "You cannot book dates in the past";
        $has_errors = true;
    } elseif (in_array($date, $holidays)) {
        $errors['date'] = "Booking is not available on holidays";
        $has_errors = true;
    } elseif (date('w', strtotime($date)) == 0) { // 0 is Sunday
        $errors['date'] = "Booking is not available on Sundays";
        $has_errors = true;
    }
    
    if (empty($time_slots)) {
        $errors['time_slots'] = "Please select at least one time slot";
        $has_errors = true;
    }
    
    if (empty($purpose)) {
        $errors['purpose'] = "Please enter the purpose of booking";
        $has_errors = true;
    }
    
    if (empty($seats_required)) {
        $errors['seats'] = "Please enter the number of seats required";
        $has_errors = true;
    } elseif ($seats_required < 1 || $seats_required > 300) {
        $errors['seats'] = "Number of seats must be between 1 and 300";
        $has_errors = true;
    }
    
    // Add this to your PHP validation section
    if (empty($_POST['seats_required'])) {
        $errors['seats'] = "Please enter the number of seats required";
        $has_errors = true;
    } elseif (!is_numeric($_POST['seats_required']) || $_POST['seats_required'] <= 0) {
        $errors['seats'] = "Please enter a valid number of seats";
        $has_errors = true;
    } elseif ($_POST['seats_required'] > 300) {
        $errors['seats'] = "Maximum capacity exceeded (300 seats)";
        $has_errors = true;
    }
    
    // Check hall availability only if all other validations pass
    if (!$has_errors) {
        $unavailable_slots = checkHallAvailability($conn, $hall, $date, $time_slots);
        
        if (!empty($unavailable_slots)) {
            $errors['time_slots'] = "The following time slots are already booked: " . implode(", ", $unavailable_slots);
            $has_errors = true;
        }
    }
    
    // If any validation errors, display combined message
    if ($has_errors) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> ";
        foreach($errors as $field => $error) {
            if(!empty($error)) {
                $message .= $error . "<br>";
            }
        }
        $message .= "</div>";
    } else {
        // All validations passed, proceed with booking
        $time_slot_str = implode(", ", $time_slots);
        
        $stmt = $conn->prepare("INSERT INTO hall_bookings (name, email, hall, date, time_slot, purpose, seats_required) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $name, $email, $hall, $date, $time_slot_str, $purpose, $seats_required);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Booking request submitted successfully!</div>";
            
            // Clear form data for new entry after successful submission
            $name = "";
            $email = "";
            $hall = "";
            $date = "";
            $time_slots = [];
            $purpose = "";
            $seats_required = "";
            
            // Set flag to reset form and show confirmation
            $reset_form = true;
        } else {
            $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Error submitting request. Try again!</div>";
        }
        $stmt->close();
    }
}

// Function to check hall availability for a specific date and hall (for client-side validation)
function getBookedTimeSlots($conn, $hall, $date) {
    $booked_slots = [];
    
    $sql = "SELECT time_slot FROM hall_bookings WHERE hall = ? AND date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hall, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $slots = explode(", ", $row['time_slot']);
        foreach ($slots as $slot) {
            if (!in_array($slot, $booked_slots)) {
                $booked_slots[] = $slot;
            }
        }
    }
    
    return $booked_slots;
}

// Get all booked time slots for JSON encoding (to be used in JavaScript)
$all_booked_slots = [];
$sql = "SELECT hall, date, time_slot FROM hall_bookings";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hall = $row['hall'];
        $date = $row['date'];
        $slots = explode(", ", $row['time_slot']);
        
        if (!isset($all_booked_slots[$hall])) {
            $all_booked_slots[$hall] = [];
        }
        
        if (!isset($all_booked_slots[$hall][$date])) {
            $all_booked_slots[$hall][$date] = [];
        }
        
        foreach ($slots as $slot) {
            if (!in_array($slot, $all_booked_slots[$hall][$date])) {
                $all_booked_slots[$hall][$date][] = $slot;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
   <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4776E6;
            --accent-dark: #3a61bb;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --neutral-light: #e9ecef;
            --neutral-medium: #ced4da;
            --neutral-dark: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--light-color);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            position: relative;
            padding-bottom: 60px;
            background-attachment: fixed;
        }
        
        .header-section {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 18px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 40px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header-section h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 1.8rem;
            color: white;
            margin: 0;
            text-align: center;
            letter-spacing: 0.5px;
        }
        
        .header-section .logo {
            max-height: 50px;
            margin-right: 15px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto 50px auto;
            background: rgba(255, 255, 255, 0.97);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: var(--dark-color);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-color), var(--accent-dark));
            border-radius: 4px 0 0 4px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 35px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 18px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 3px;
            background: linear-gradient(to right, var(--accent-color), var(--accent-dark));
            border-radius: 2px;
        }
        
        .form-section {
            background-image: url('images/booking-bg.png');
            background-position: right bottom;
            background-repeat: no-repeat;
            background-size: 150px;
            position: relative;
            /* Remove invalid background-opacity */
            /* Add a semi-transparent background color overlay */
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .form-section::after {
            content: '\f274';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            bottom: -50px;
            right: -20px;
            font-size: 180px;
            color: rgba(30, 60, 114, 0.03);
            z-index: -1;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        
        .form-label i {
            margin-right: 10px;
            color: var(--accent-color);
            width: 22px;
            text-align: center;
            font-size: 16px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--neutral-medium);
            padding: 13px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(74, 118, 230, 0.2);
            background-color: white;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            color: var(--neutral-dark);
            z-index: 10;
        }
        
        .icon-input {
            padding-left: 45px !important;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.4s ease;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 82, 152, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 200px;
            background: rgba(255, 255, 255, 0.2);
            top: -50px;
            left: -100px;
            transform: rotate(35deg);
            transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }
        
        .btn-primary:hover::after {
            left: 120%;
        }
        
        .alert {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 25px;
            border: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .alert i {
            margin-right: 12px;
            font-size: 22px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: var(--success-color);
            border-left: 5px solid var(--success-color);
        }
        
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--warning-color);
            border-left: 5px solid var(--warning-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: var(--danger-color);
            border-left: 5px solid var(--danger-color);
        }
        
        .booking-info {
            background-color: rgba(74, 118, 230, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(74, 118, 230, 0.1);
            position: relative;
            margin-top: 40px;
        }
        
        .booking-info-title {
            position: absolute;
            top: -15px;
            left: 20px;
            background: white;
            padding: 5px 15px;
            border-radius: 30px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-list {
            padding-left: 5px;
            list-style-type: none;
            margin-bottom: 0;
        }
        
        .info-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            color: var(--neutral-dark);
            font-size: 14px;
        }
        
        .info-list li:last-child {
            margin-bottom: 0;
        }
        
        .info-list li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--accent-color);
        }
        
        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 18px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            background-color: rgba(0, 0, 0, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        footer p {
            margin: 0;
        }
        
        .social-icons {
            margin-top: 12px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        /* Animation for success message */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert {
            animation: fadeInUp 0.5s ease forwards;
        }
        
        /* Hall card styles */
        .hall-cards {
            margin-top: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .hall-card {
            position: relative;
            width: 120px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
            border: 2px solid transparent;
        }
        
        .hall-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hall-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            font-size: 11px;
            text-align: center;
            font-weight: 500;
        }
        
        .hall-card.selected {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Tag pills for purpose input */
        .tag-container {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            background-color: rgba(74, 118, 230, 0.1);
            border-radius: 20px;
            font-size: 13px;
            color: var(--accent-color);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .tag:hover {
            background-color: rgba(74, 118, 230, 0.2);
        }
        
        .tag i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        /* Steps indicator */
        .booking-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .booking-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 2px;
            background-color: var(--neutral-medium);
            z-index: 1;
        }
        
        .step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--neutral-medium);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background-color: var(--accent-color);
            box-shadow: 0 0 0 5px rgba(74, 118, 230, 0.2);
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
        }
        
        .step.completed .step-number::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        
        .step-label {
            font-size: 12px;
            color: var(--neutral-dark);
            text-align: center;
            transition: all 0.3s;
        }
        
        .step.active .step-label {
            color: var(--accent-color);
            font-weight: 600;
        }
        
        .step.completed .step-label {
            color: var(--success-color);
        }
        
        /* Form field enhancements */
        .custom-form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .custom-form-group input,
        .custom-form-group select,
        .custom-form-group textarea {
            padding-left: 45px;
        }
        
        .form-icon {
            position: absolute;
            top: 45px;
            left: 15px;
            color: var(--accent-color);
            font-size: 18px;
        }
        
        /* Special features section */
        .features-section {
            margin-top: 30px;
            background-color: rgba(233, 236, 239, 0.4);
            border-radius: 12px;
            padding: 20px;
        }
        
        .features-header {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .features-header i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--dark-color);
        }
        
        .feature-item i {
            margin-right: 8px;
            color: var(--accent-color);
            font-size: 15px;
        }
        
        /* Form Steps */
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Review Section */
        .review-section {
            background-color: rgba(248, 249, 250, 0.7);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid var(--neutral-light);
        }
        
        .review-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .review-label {
            min-width: 120px;
            font-weight: 600;
            color: var(--neutral-dark);
            font-size: 14px;
        }
        
        .review-value {
            color: var(--dark-color);
            font-size: 15px;
            flex-grow: 1;
        }
        
        .review-divider {
            height: 1px;
            background-color: var (--neutral-light);
            margin: 10px 0;
        }
        
        .booking-summary {
            background-color: rgba(30, 60, 114, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .summary-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        /* Confirmation Section */
        .confirmation-section {
            text-align: center;
            padding: 20px 0;
        }
        
        .confirmation-icon {
            font-size: 60px;
            color: var(--success-color);
            margin-bottom: 20px;
        }
        
        .confirmation-message {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .confirmation-details {
            max-width: 400px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--neutral-light);
        }
        
        .booking-reference {
            display: inline-block;
            padding: 5px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-nav {
            min-width: 120px;
        }
        
        .btn-secondary {
            background-color: var(--neutral-light);
            color: var(--dark-color);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: var(--neutral-medium);
        }
        
        /* Time slot checkboxes */
        .time-slots-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .time-slot-checkbox {
            display: none;
        }
        
        .time-slot-label {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--neutral-medium);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: white;
            font-size: 14px;
        }
        
        .time-slot-label:hover {
            border-color: var(--accent-color);
            background-color: rgba(74, 118, 230, 0.05);
        }
        
        .time-slot-checkbox:checked + .time-slot-label {
            background-color: rgba(74, 118, 230, 0.1);
            border-color: var(--accent-color);
            border-color: var(--neutral-medium);
            color: var(--neutral-dark);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .time-slot-check {
            display: none;
            margin-right: 8px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
                max-width: 100%;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .header-section h1 {
                font-size: 1.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .booking-steps {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            
            .booking-steps::before {
                display: none;
            }
            
            .time-slots-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .form-navigation {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-nav {
                width: 100%;
            }
            
            .time-slots-container {
                grid-template-columns: 1fr;
            }
        }
    </style> 

</head>
<body>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-12 d-flex justify-content-center align-items-center">
               
                <h1><i class="fas fa-graduation-cap me-2"></i>Campus Care</h1>
            </div>
        </div>
    </div>
</div>

<div class="user-nav">
    <div class="container">
        <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($_SESSION['name']) ?>
                </span>
            </div>
            <div class="nav-buttons">
                <a href="my_bookings.php" class="btn btn-outline-light active-bookings-btn">
                    <i class="fas fa-calendar-check"></i> My Bookings
                </a>     
                <a href="faculty_dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="student_login.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.user-nav {
    background: rgba(0, 0, 0, 0.1);
    padding: 10px 0;
    margin-bottom: 20px;
}

.user-nav .container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    color: white;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}
</style>
<div class="container">
    <h2><i class="fas fa-calendar-check me-2"></i>College Hall Booking</h2>
    
    <?= $message; // Show success or error message ?>
    
    <!-- Booking Steps Indicator -->
    <div class="booking-steps">
        <div class="step active" id="step1-indicator">
            <div class="step-number">1</div>
            <div class="step-label">Enter Details</div>
        </div>
        <div class="step" id="step2-indicator">
            <div class="step-number">2</div>
            <div class="step-label">Review Request</div>
        </div>
        <div class="step" id="step3-indicator">
            <div class="step-number">3</div>
            <div class="step-label">Confirmation</div>
        </div>
    </div>
    
    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="POST" id="bookingForm">
        <!-- Step 1: Enter Details -->
        <div class="form-step active" id="step1">
            <div class="custom-form-group">
                <label for="name" class="form-label"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : ''; ?>" 
                       id="name" 
                       name="name" 
                       value="<?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" 
                       readonly>
                <small class="form-text text-muted">Name from your account</small>
            </div>

            <div class="custom-form-group">
                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                       id="email" 
                       name="email" 
                       value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" 
                       readonly>
                <small class="form-text text-muted">Email from your account</small>
            </div>

            <div class="custom-form-group">
                <label for="seats_required" class="form-label"><i class="fas fa-users"></i> Number of Seats Required</label>
                <input type="number" class="form-control <?= !empty($errors['seats']) ? 'is-invalid' : ''; ?>" 
                       id="seats_required" name="seats_required" 
                       min="1" max="300" 
                       placeholder="Enter number of seats needed"
                       value="<?= isset($seats_required) ? $seats_required : ''; ?>" 
                       required>
                <small class="form-text text-muted">Maximum capacity: 300 seats</small>
                <?php if(!empty($errors['seats'])): ?>
                    <div class="invalid-feedback"><?= $errors['seats']; ?></div>
                <?php endif; ?>
            </div>

            <div class="custom-form-group">
                <label for="hall" class="form-label"><i class="fas fa-building"></i> Available Halls</label>
                <select class="form-select <?= !empty($errors['hall']) ? 'is-invalid' : ''; ?>" 
                        id="hall" name="hall" required disabled>
                    <option value="" disabled selected>Select number of seats first</option>
                </select>
                <?php if(!empty($errors['hall'])): ?>
                    <div class="invalid-feedback"><?= $errors['hall']; ?></div>
                <?php endif; ?>
            </div>

            <div class="custom-form-group">
                <label for="date" class="form-label"><i class="fas fa-calendar-alt"></i> Booking Date</label>
                <input type="date" class="form-control <?= !empty($errors['date']) ? 'is-invalid' : ''; ?>" id="date" name="date" min="<?= date('Y-m-d'); ?>" value="<?= isset($date) ? $date : ''; ?>" required>
                <?php if(!empty($errors['date'])): ?>
                    <div class="invalid-feedback"><?= $errors['date']; ?></div>
                <?php else: ?>
                    <small class="text-muted">Note: Bookings are not available on Sundays and holidays.</small>
                <?php endif; ?>
            </div>

            <div class="custom-form-group">
                <label class="form-label"><i class="fas fa-clock"></i> Select Time Slots</label>
                <div id="availability-notice" class="alert alert-info" style="display: none;">
                    <i class="fas fa-info-circle"></i> Please select a hall and date to see available time slots.
                </div>
                <?php if(!empty($errors['time_slots'])): ?>
                    <div class="alert alert-warning"><?= $errors['time_slots']; ?></div>
                <?php endif; ?>
                <div class="time-slots-container">
                    <input type="checkbox" class="time-slot-checkbox" id="slot9" name="time_slots[]" value="9:00 - 10:00" <?= (isset($time_slots) && in_array("9:00 - 10:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot9" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        9:00 - 10:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot10" name="time_slots[]" value="10:00 - 11:00" <?= (isset($time_slots) && in_array("10:00 - 11:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot10" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        10:00 - 11:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot11" name="time_slots[]" value="11:00 - 12:00" <?= (isset($time_slots) && in_array("11:00 - 12:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot11" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        11:00 - 12:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot12" name="time_slots[]" value="12:00 - 13:00" <?= (isset($time_slots) && in_array("12:00 - 13:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot12" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        12:00 - 13:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot13" name="time_slots[]" value="13:00 - 14:00" <?= (isset($time_slots) && in_array("13:00 - 14:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot13" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        13:00 - 14:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot14" name="time_slots[]" value="14:00 - 15:00" <?= (isset($time_slots) && in_array("14:00 - 15:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot14" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        14:00 - 15:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot15" name="time_slots[]" value="15:00 - 16:00" <?= (isset($time_slots) && in_array("15:00 - 16:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot15" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        15:00 - 16:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot16" name="time_slots[]" value="16:00 - 17:00" <?= (isset($time_slots) && in_array("16:00 - 17:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot16" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        16:00 - 17:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot17" name="time_slots[]" value="17:00 - 18:00" <?= (isset($time_slots) && in_array("17:00 - 18:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot17" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        17:00 - 18:00
                    </label>

                    
                </div>
            </div>


            <div class="custom-form-group">
                <label for="purpose" class="form-label"><i class="fas fa-clipboard-list"></i> Purpose of Booking</label>
                <textarea class="form-control <?= !empty($errors['purpose']) ? 'is-invalid' : ''; ?>" id="purpose" name="purpose" rows="3" placeholder="Briefly describe the purpose of your booking"><?= isset($purpose) ? $purpose : ''; ?></textarea>
                <?php if(!empty($errors['purpose'])): ?>
                    <div class="invalid-feedback"><?= $errors['purpose']; ?></div>
                <?php endif; ?>
                
                <div class="tag-container">
                    <div class="tag" onclick="fillPurpose('Conference')"><i class="fas fa-users"></i> Conference</div>
                    <div class="tag" onclick="fillPurpose('Workshop')"><i class="fas fa-chalkboard-teacher"></i> Workshop</div>
                    <div class="tag" onclick="fillPurpose('Cultural Event')"><i class="fas fa-music"></i> Cultural Event</div>
                    <div class="tag" onclick="fillPurpose('Seminar')"><i class="fas fa-microphone"></i> Seminar</div>
                </div>
            </div>

            <div class="booking-info">
                <div class="booking-info-title"><i class="fas fa-info-circle"></i> Important Information</div>
                <ul class="info-list">
                    <li>Your booking request will be reviewed by the administration.</li>
                    <li>Admin can reject your booking if needed.</li>
                    <li>Additional equipment requests should be made separately.</li>
                </ul>
            </div>

            <div class="form-navigation">
                <div></div>
                <button type="button" class="btn btn-primary btn-nav" id="nextToReview">Next <i class="fas fa-arrow-right ms-2"></i></button>
            </div>
        </div>

        <!-- Step 2: Review Request -->
        <div class="form-step" id="step2">
            <div class="review-section">
                <h3 class="mb-4">Booking Request Summary</h3>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-user me-2"></i> Name:</div>
                    <div class="review-value" id="review-name"></div>
                </div>
                
                <div class="review-divider"></div>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-envelope me-2"></i> Email:</div>
                    <div class="review-value" id="review-email"></div>
                </div>
                
                <div class="review-divider"></div>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-building me-2"></i> Hall:</div>
                    <div class="review-value" id="review-hall"></div>
                </div>
                
                <div class="review-divider"></div>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-calendar me-2"></i> Date:</div>
                    <div class="review-value" id="review-date"></div>
                </div>
                
                <div class="review-divider"></div>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-clock me-2"></i> Time Slots:</div>
                    <div class="review-value" id="review-time-slots"></div>
                </div>
                
                <div class="review-divider"></div>
                
                <div class="review-item">
                    <div class="review-label"><i class="fas fa-clipboard-list me-2"></i> Purpose:</div>
                    <div class="review-value" id="review-purpose"></div>
                </div>
            </div>

            <div class="form-navigation">
                <button type="button" class="btn btn-secondary btn-nav" id="backToDetails"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <button type="submit" name="submit_booking" class="btn btn-primary btn-nav">Submit Request <i class="fas fa-paper-plane ms-2"></i></button>
            </div>
        </div>

        <!-- Step 3: Confirmation (Only shown after successful submission) -->
        <div class="form-step" id="step3">
            <div class="confirmation-section">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="confirmation-message">Booking Request Submitted!</div>
                <div class="confirmation-details">
                    <p>Thank you for your booking request. We have received your details and will review your request shortly.</p>
                    <div class="booking-reference" style="display: none;">Reference: <span id="booking-ref"></span></div>
                </div>
                <button type="button" class="btn btn-primary mt-4" id="bookAnotherBtn">Book Another Hall</button>
            </div>
        </div>
    </form>

    <!-- Hall Features Section -->
    <div class="features-section">
        <h3 class="features-header"><i class="fas fa-star"></i> Hall Amenities</h3>
        <div class="features-grid">
            <div class="feature-item"><i class="fas fa-wifi"></i> High-Speed Wi-Fi</div>
            <div class="feature-item"><i class="fas fa-volume-up"></i> Sound System</div>
            <div class="feature-item"><i class="fas fa-desktop"></i> HD Projector</div>            
            <div class="feature-item"><i class="fas fa-snowflake"></i> Air Conditioning</div>
           
            <div class="feature-item"><i class="fas fa-lightbulb"></i> Dynamic Lighting</div>
        </div>
    </div>
</div><br>
<footer>
    <p>&copy; <?= date('Y'); ?> Campus Care System | All Rights Reserved</p>    
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Store all booked slots from PHP to JavaScript
    const allBookedSlots = <?= json_encode($all_booked_slots); ?>;
    
    // Set today's date as minimum date
    document.addEventListener('DOMContentLoaded', function() {
        // Format date in YYYY-MM-DD
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${yyyy}-${mm}-${dd}`;
        
        document.getElementById('date').min = formattedDate;
        
        // Generate random booking reference for demonstration but keep it hidden
        const bookingRef = 'BK' + Math.floor(100000 + Math.random() * 900000);
      document.getElementById('booking-ref').textContent = bookingRef;
        
        // If form was successfully submitted, show confirmation step
        <?php if ($reset_form): ?>
            showStep(3);
        <?php endif; ?>
    });
    
    // Function to fill purpose field with predefined options
    function fillPurpose(purposeText) {
        document.getElementById('purpose').value = purposeText;
    }
    
    // Function to show a specific step
    function showStep(stepNumber) {
        // Hide all steps
        const steps = document.getElementsByClassName('form-step');
        for (let i = 0; i < steps.length; i++) {
            steps[i].classList.remove('active');
        }
        
        // Show the selected step
        document.getElementById('step' + stepNumber).classList.add('active');
        
        // Update step indicators
        const indicators = document.getElementsByClassName('step');
        for (let i = 0; i < indicators.length; i++) {
            indicators[i].classList.remove('active');
        }
        for (let i = 0; i < stepNumber; i++) {
            indicators[i].classList.add('active');
        }
    }
    
    // Next button click event
    document.getElementById('nextToReview').addEventListener('click', function() {
        // Validate form fields
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const hall = document.getElementById('hall').value;
        const date = document.getElementById('date').value;
        const purpose = document.getElementById('purpose').value.trim();
        
        let timeSlots = [];
        const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox:checked');
        timeSlotCheckboxes.forEach(checkbox => {
            timeSlots.push(checkbox.value);
        });
        
        let isValid = true;
        let errorMessage = '';
        
        // Validate name
        if (name === '') {
            document.getElementById('name').classList.add('is-invalid');
            errorMessage += 'Please enter your name<br>';
            isValid = false;
        } else {
            document.getElementById('name').classList.remove('is-invalid');
        }
        
        // Validate email
        if (email === '') {
            document.getElementById('email').classList.add('is-invalid');
            errorMessage += 'Please enter your email address<br>';
            isValid = false;
        } else if (!email.match(/@gmail\.com$/i)) {
            document.getElementById('email').classList.add('is-invalid');
            errorMessage += 'Only Gmail addresses are accepted<br>';
            isValid = false;
        } else {
            document.getElementById('email').classList.remove('is-invalid');
        }
        
        // Validate hall
        if (hall === '' || hall === null) {
            document.getElementById('hall').classList.add('is-invalid');
            errorMessage += 'Please select a hall<br>';
            isValid = false;
        } else {
            document.getElementById('hall').classList.remove('is-invalid');
        }
        
        // Validate date
        if (date === '') {
            document.getElementById('date').classList.add('is-invalid');
            errorMessage += 'Please select a booking date<br>';
            isValid = false;
        } else {
            document.getElementById('date').classList.remove('is-invalid');
        }
        
        // Validate time slots
        if (timeSlots.length === 0) {
            errorMessage += 'Please select at least one time slot<br>';
            isValid = false;
        }
        
        // Validate purpose
        if (purpose === '') {
            document.getElementById('purpose').classList.add('is-invalid');
            errorMessage += 'Please enter the purpose of booking<br>';
            isValid = false;
        } else {
            document.getElementById('purpose').classList.remove('is-invalid');
        }
        
        if (!isValid) {
            // Display error message
            alert('Please fill in all required fields before proceeding.');
            return;
        }
        
        // Update review section
        document.getElementById('review-name').textContent = name;
        document.getElementById('review-email').textContent = email;
        document.getElementById('review-hall').textContent = hall;
        
        // Format date for display
        const dateObj = new Date(date);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('review-date').textContent = dateObj.toLocaleDateString('en-US', options);
        
        // Display time slots
        document.getElementById('review-time-slots').textContent = timeSlots.join(', ');
        
        // Display purpose
        document.getElementById('review-purpose').textContent = purpose;
        
        // Show step 2
        showStep(2);
    });
    
    // Back button click event
    document.getElementById('backToDetails').addEventListener('click', function() {
        showStep(1);
    });
    
    // Book another button click event
    document.getElementById('bookAnotherBtn').addEventListener('click', function() {
        // Reset form
        document.getElementById('bookingForm').reset();
        
        // Show step 1
        showStep(1);
    });
    
    // Update available time slots when hall and date are selected
    function updateAvailableTimeSlots() {
        const hall = document.getElementById('hall').value;
        const date = document.getElementById('date').value;
        
        if (hall && date) {
            // Check if the selected date is a Sunday
            const selectedDate = new Date(date);
            if (selectedDate.getDay() === 0) { // 0 is Sunday
                // Disable all time slots
                const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');
                timeSlotCheckboxes.forEach(checkbox => {
                    checkbox.disabled = true;
                    checkbox.checked = false;
                    checkbox.parentElement.classList.add('unavailable');
                });
                
                document.getElementById('availability-notice').style.display = 'block';
                document.getElementById('availability-notice').innerHTML = '<i class="fas fa-info-circle"></i> Bookings are not available on Sundays.';
                return;
            }
            
            // Check if the selected date is a holiday
            const holidays = <?= json_encode($holidays); ?>;
            if (holidays.includes(date)) {
                // Disable all time slots
                const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');
                timeSlotCheckboxes.forEach(checkbox => {
                    checkbox.disabled = true;
                    checkbox.checked = false;
                    checkbox.parentElement.classList.add('unavailable');
                });
                
                document.getElementById('availability-notice').style.display = 'block';
                document.getElementById('availability-notice').innerHTML = '<i class="fas fa-info-circle"></i> Bookings are not available on holidays.';
                return;
            }
            
            // Reset all time slots
            const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');
            timeSlotCheckboxes.forEach(checkbox => {
                checkbox.disabled = false;
                checkbox.parentElement.classList.remove('unavailable');
            });
            
            // Check if there are any booked slots for this hall and date
            if (allBookedSlots[hall] && allBookedSlots[hall][date]) {
                const bookedSlots = allBookedSlots[hall][date];
                
                // Disable booked time slots
                timeSlotCheckboxes.forEach(checkbox => {
                    if (bookedSlots.includes(checkbox.value)) {
                        checkbox.disabled = true;
                        checkbox.checked = false;
                        checkbox.parentElement.classList.add('unavailable');
                    }
                });
                
                document.getElementById('availability-notice').style.display = 'block';
                document.getElementById('availability-notice').innerHTML = '<i class="fas fa-info-circle"></i> Gray time slots are already booked.';
            } else {
                document.getElementById('availability-notice').style.display = 'block';
                document.getElementById('availability-notice').innerHTML = '<i class="fas fa-info-circle"></i> All time slots are available for booking.';
            }
        } else {
            document.getElementById('availability-notice').style.display = 'block';
            document.getElementById('availability-notice').innerHTML = '<i class="fas fa-info-circle"></i> Please select a hall and date to see available time slots.';
        }
    }
    
    // Add event listeners to hall and date inputs
    document.getElementById('hall').addEventListener('change', updateAvailableTimeSlots);
    document.getElementById('date').addEventListener('change', updateAvailableTimeSlots);
    
    // Call updateAvailableTimeSlots initially to set the correct state
    updateAvailableTimeSlots();

    // Add this to your existing JavaScript section
    const halls = [
        { name: 'Eric Mathias Hall', capacity: 200 },
        { name: 'Lcri Hall', capacity: 300 },
        { name: 'Gelge Hall', capacity: 150 },
        { name: 'Arupe Hall', capacity: 220 },
        { name: 'Joseph Willy Hall', capacity: 100 },
        { name: 'Sanidhya Hall', capacity: 50 }
    ];

    document.getElementById('seats_required').addEventListener('input', function() {
        const seatsNeeded = parseInt(this.value);
        const hallSelect = document.getElementById('hall');
        const existingMessage = hallSelect.parentElement.querySelector('.form-text');
        
        // Remove any existing message
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Clear current options
        hallSelect.innerHTML = '<option value="" disabled selected>Choose a hall</option>';
        
        if (seatsNeeded > 0) {
            // Filter and add available halls
            const availableHalls = halls.filter(hall => hall.capacity >= seatsNeeded);
            
            if (availableHalls.length > 0) {
                availableHalls.forEach(hall => {
                    const option = document.createElement('option');
                    option.value = hall.name;
                    option.textContent = `${hall.name} (${hall.capacity} Seats)`;
                    hallSelect.appendChild(option);
                });
                hallSelect.disabled = false;
                
                // Add message only if filtering occurred
                if (availableHalls.length < halls.length) {
                    const smallText = document.createElement('small');
                    smallText.className = 'form-text text-muted mt-2';
                    smallText.innerHTML = `<i class="fas fa-info-circle"></i> Showing halls that can accommodate ${seatsNeeded} seats`;
                    hallSelect.parentElement.appendChild(smallText);
                }
            } else {
                // No halls available for this capacity
                const option = document.createElement('option');
                option.value = "";
                option.disabled = true;
                option.selected = true;
                option.textContent = `No halls available for ${seatsNeeded} seats`;
                hallSelect.appendChild(option);
                
                const smallText = document.createElement('small');
                smallText.className = 'form-text text-danger mt-2';
                smallText.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please select a smaller number of seats';
                hallSelect.parentElement.appendChild(smallText);
                hallSelect.disabled = true;
            }
        } else {
            hallSelect.disabled = true;
        }
    });
</script>
<style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #4776E6;
            --accent-dark: #3a61bb;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --neutral-light: #e9ecef;
            --neutral-medium: #ced4da;
            --neutral-dark: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--light-color);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            position: relative;
            padding-bottom: 60px;
            background-attachment: fixed;
        }
        
        .header-section {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 18px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 40px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header-section h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 1.8rem;
            color: white;
            margin: 0;
            text-align: center;
            letter-spacing: 0.5px;
        }
        
        .header-section .logo {
            max-height: 50px;
            margin-right: 15px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto 50px auto;
            background: rgba(255, 255, 255, 0.97);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: var(--dark-color);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-color), var(--accent-dark));
            border-radius: 4px 0 0 4px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 35px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 18px;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 3px;
            background: linear-gradient(to right, var (--accent-color), var(--accent-dark));
            border-radius: 2px;
        }
        
        .form-section {
            background-image: url('images/booking-bg.png');
            background-position: right bottom;
            background-repeat: no-repeat;
            background-size: 150px;
            position: relative;
            /* Remove invalid background-opacity */
            /* Add a semi-transparent background color overlay */
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .form-section::after {
            content: '\f274';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            bottom: -50px;
            right: -20px;
            font-size: 180px;
            color: rgba(30, 60, 114, 0.03);
            z-index: -1;
        }
        
        .form-label {
            font-weight: 600;
            color: var (--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        
        .form-label i {
            margin-right: 10px;
            color: var(--accent-color);
            width: 22px;
            text-align: center;
            font-size: 16px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--neutral-medium);
            padding: 13px 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var (--accent-color);
            box-shadow: 0 0 0 4px rgba(74, 118, 230, 0.2);
            background-color: white;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            color: var(--neutral-dark);
            z-index: 10;
        }
        
        .icon-input {
            padding-left: 45px !important;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.4s ease;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 82, 152, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 200px;
            background: rgba(255, 255, 255, 0.2);
            top: -50px;
            left: -100px;
            transform: rotate(35deg);
            transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }
        
        .btn-primary:hover::after {
            left: 120%;
        }
        
        .alert {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 25px;
            border: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .alert i {
            margin-right: 12px;
            font-size: 22px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: var (--success-color);
            border-left: 5px solid var(--success-color);
        }
        
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--warning-color);
            border-left: 5px solid var(--warning-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: var(--danger-color);
            border-left: 5px solid var (--danger-color);
        }
        
        .booking-info {
            background-color: rgba(74, 118, 230, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(74, 118, 230, 0.1);
            position: relative;
            margin-top: 40px;
        }
        
        .booking-info-title {
            position: absolute;
            top: -15px;
            left: 20px;
            background: white;
            padding: 5px 15px;
            border-radius: 30px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-list {
            padding-left: 5px;
            list-style-type: none;
            margin-bottom: 0;
        }
        
        .info-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            color: var (--neutral-dark);
            font-size: 14px;
        }
        
        .info-list li:last-child {
            margin-bottom: 0;
        }
        
        .info-list li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--accent-color);
        }
        
        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 18px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            background-color: rgba(0, 0, 0, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        footer p {
            margin: 0;
        }
        
        .social-icons {
            margin-top: 12px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        /* Animation for success message */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert {
            animation: fadeInUp 0.5s ease forwards;
        }
        
        /* Hall card styles */
        .hall-cards {
            margin-top: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .hall-card {
            position: relative;
            width: 120px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
            border: 2px solid transparent;
        }
        
        .hall-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hall-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            font-size: 11px;
            text-align: center;
            font-weight: 500;
        }
        
        .hall-card.selected {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Tag pills for purpose input */
        .tag-container {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            background-color: rgba(74, 118, 230, 0.1);
            border-radius: 20px;
            font-size: 13px;
            color: var(--accent-color);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .tag:hover {
            background-color: rgba(74, 118, 230, 0.2);
        }
        
        .tag i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        /* Steps indicator */
        .booking-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .booking-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 2px;
            background-color: var(--neutral-medium);
            z-index: 1;
        }
        
        .step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--neutral-medium);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background-color: var(--accent-color);
            box-shadow: 0 0 0 5px rgba(74, 118, 230, 0.2);
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
        }
        
        .step.completed .step-number::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        
        .step-label {
            font-size: 12px;
            color: var(--neutral-dark);
            text-align: center;
            transition: all 0.3s;
        }
        
        .step.active .step-label {
            color: var(--accent-color);
            font-weight: 600;
        }
        
        .step.completed .step-label {
            color: var(--success-color);
        }
        
        /* Form field enhancements */
        .custom-form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .custom-form-group input,
        .custom-form-group select,
        .custom-form-group textarea {
            padding-left: 45px;
        }
        
        .form-icon {
            position: absolute;
            top: 45px;
            left: 15px;
            color: var(--accent-color);
            font-size: 18px;
        }
        
        /* Special features section */
        .features-section {
            margin-top: 30px;
            background-color: rgba(233, 236, 239, 0.4);
            border-radius: 12px;
            padding: 20px;
        }
        
        .features-header {
            align-items: center;
        }
        
        .features-header i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--dark-color);
        }
        
        .feature-item i {
            margin-right: 8px;
            color: var(--accent-color);
            font-size: 15px;
        }
        
        /* Form Steps */
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Review Section */
        .review-section {
            background-color: rgba(248, 249, 250, 0.7);
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid var(--neutral-light);
        }
        
        .review-item {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .review-label {
            min-width: 120px;
            font-weight: 600;
            color: var(--neutral-dark);
            font-size: 14px;
        }
        
        .review-value {
            color: var (--dark-color);
            font-size: 15px;
            flex-grow: 1;
        }
        
        .review-divider {
            height: 1px;
            background-color: var(--neutral-light);
            margin: 10px 0;
        }
        
        .booking-summary {
            background-color: rgba(30, 60, 114, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .summary-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        /* Confirmation Section */
        .confirmation-section {
            text-align: center;
            padding: 20px 0;
        }
        
        .confirmation-icon {
            font-size: 60px;
            color: var(--success-color);
            margin-bottom: 20px;
        }
        
        .confirmation-message {
            font-size: 24px;
            font-weight: 600;
            color: var (--primary-color);
            margin-bottom: 15px;
        }
        
        .confirmation-details {
            max-width: 400px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--neutral-light);
        }
        
        .booking-reference {
            display: inline-block;
            padding: 5px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-nav {
            min-width: 120px;
        }
        
        .btn-secondary {
            background-color: var(--neutral-light);
            color: var(--dark-color);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: var(--neutral-medium);
        }
        
        /* Time slot checkboxes */
        .time-slots-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .time-slot-checkbox {
            display: none;
        }
        
        .time-slot-label {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var (--neutral-medium);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: white;
            font-size: 14px;
        }
        
        .time-slot-label:hover {
            border-color: var(--accent-color);
            background-color: rgba(74, 118, 230, 0.05);
        }
        
        .time-slot-checkbox:checked + .time-slot-label {
            background-color: rgba(74, 118, 230, 0.1);
            border-color: var(--accent-color);
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }
        
        .time-slot-checkbox:checked + .time-slot-label .time-slot-check {
            display: inline-block;
            color: var(--accent-color);
        }
        
        .time-slot-checkbox:disabled + .time-slot-label {
            background-color: var(--neutral-light);
            border-color: var(--neutral-medium);
            color: var(--neutral-dark);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .time-slot-check {
            display: none;
            margin-right: 8px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
                max-width: 100%;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .header-section h1 {
                font-size: 1.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .booking-steps {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            
            .booking-steps::before {
                display: none;
            }
            
            .time-slots-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .form-navigation {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-nav {
                width: 100%;
            }
            
            .time-slots-container {
                grid-template-columns: 1fr;
            }
        }
    </style> 

    <style>
body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f5f5f5;
    color: #333;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

.header-section {
    background: linear-gradient(to right, #1a237e, #283593);
    color: white;
    padding: 20px 0;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-section h1 {
    font-family: 'Playfair Display', serif;
    margin: 0;
    font-size: 2.2rem;
}

.logo {
    height: 60px;
    margin-right: 15px;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    background-color: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    margin-bottom: 50px;
}

h2 {
    color: #1a237e;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-family: 'Playfair Display', serif;
}

/* Booking Steps Indicator */
.booking-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.booking-steps::before {
    content: '';
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #e0e0e0;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    width: 33.33%;
    text-align: center;
}

.step-number {
    width: 50px;
    height: 50px;
    background-color: white;
    border: 2px solid #e0e0e0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: bold;
    color: #757575;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background-color: #1a237e;
    border-color: #1a237e;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #757575;
            display: none;
            margin-right: 8px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
                max-width: 100%;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .header-section h1 {
                font-size: 1.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .booking-steps {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            
            .booking-steps::before {
                display: none;
            }
            
            .time-slots-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .form-navigation {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-nav {
                width: 100%;
            }
            
            .time-slots-container {
                grid-template-columns: 1fr;
            }
        }
    </style> 

    <style>
body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f5f5f5;
    color: #333;
    margin: 0;
    padding: 0;
    line-height: 1.6;
}

.header-section {
    background: linear-gradient(to right, #1a237e, #283593);
    color: white;
    padding: 20px 0;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-section h1 {
    font-family: 'Playfair Display', serif;
    margin: 0;
    font-size: 2.2rem;
}

.logo {
    height: 60px;
    margin-right: 15px;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    background-color: white;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    margin-bottom: 50px;
}

h2 {
    color: #1a237e;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-family: 'Playfair Display', serif;
}

/* Booking Steps Indicator */
.booking-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.booking-steps::before {
    content: '';
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #e0e0e0;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    width: 33.33%;
    text-align: center;
}

.step-number {
    width: 50px;
    height: 50px;
    background-color: white;
    border: 2px solid #e0e0e0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: bold;
    color: #757575;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background-color: #1a237e;
    border-color: #1a237e;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #757575;
    transition: color 0.3s ease;
}

.step.active .step-label {
    color: #1a237e;
    font-weight: 600;
}

/* Form Styling */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.custom-form-group {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 500;
    color: #424242;
    margin-bottom: 8px;
    display: block;
}

.form-control, .form-select {
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    transition: border 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1a237e;
    box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
    outline: none;
}

/* Time Slots Styling */
.time-slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.time-slot-checkbox {
    display: none;
}

.time-slot-label {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: white;
}

.time-slot-label:hover {
    background-color: #f5f5f5;
}

.time-slot-checkbox:checked + .time-slot-label {
    background-color: #e8eaf6;
    border-color: #3f51b5;
    color: #3f51b5;
}

.time-slot-label.unavailable {
    background-color: #eeeeee;
    color: #9e9e9e;
    cursor: not-allowed;
    text-decoration: line-through;
}

.time-slot-check {
    margin-right: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.time-slot-checkbox:checked + .time-slot-label .time-slot-check {
    opacity: 1;
}

/* Purpose Tags */
.tag-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.tag {
    padding: 5px 12px;
    background-color: #e8eaf6;
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tag:hover {
    background-color: #c5cae9;
}

/* Booking Info Box */
.booking-info {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 30px;
    border-left: 4px solid #3f51b5;
}

.booking-info-title {
    font-weight: 600;
    color: #3f51b5;
    margin-bottom: 10px;
}

.info-list {
    margin: 0;
    padding-left: 25px;
}

.info-list li {
    margin-bottom: 5px;
}

/* Navigation buttons */
.form-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
}

.btn-nav {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #1a237e;
    border-color: #1a237e;
}

.btn-primary:hover {
    background-color: #151e69;
    border-color: #151e69;
}

.btn-secondary {
    background-color: #e0e0e0;
    border-color: #e0e0e0;
    color: #424242;
}

.btn-secondary:hover {
    background-color: #d5d5d5;
    border-color: #d5d5d5;
    color: #212121;
}

/* Review Section */
.review-section {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 25px;
}

.review-item {
    display: flex;
    margin-bottom: 15px;
}

.review-label {
    width: 120px;
    font-weight: 600;
    color: #424242;
}

.review-value {
    flex-grow: 1;
}

.review-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 15px 0;
}

/* Confirmation Section */
.confirmation-section {
    text-align: center;
    padding: 40px 20px;
}

.confirmation-icon {
    font-size: 5rem;
    color: #4caf50;
    margin-bottom: 20px;
}

.confirmation-message {
    font-size: 1.8rem;
    color: #1a237e;
    font-weight: 600;
    margin-bottom: 20px;
}

.confirmation-details {
    color: #616161;
    margin-bottom: 30px;
}

.booking-reference {
    background-color: #e8eaf6;
    display: inline-block;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: 500;
}

/* Features Section */
.features-section {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e0e0e0;
}

.features-header {
    text-align: center;
    color: #1a237e;
    margin-bottom: 25px;
    font-family: 'Playfair Display', serif;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.feature-item {
    background-color: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    transition: all 0.3s ease;
}

.feature-item:hover {
    background-color: #e8eaf6;
    transform: translateY(-3px);
}

.feature-item i {
    display: block;
    font-size: 1.5rem;
    color: #3f51b5;
    margin-bottom: 8px;
}

/* Footer */
footer {
    background: linear-gradient(to right, #1a237e, #283593);
    color: white;
    text-align: center;
    padding: 20px 0;
}

.social-icons {
    margin-top: 10px;
}

.social-icons a {
    display: inline-block;
    color: white;
    margin: 0 10px;
    font-size: 1.2rem;
}

/* Alert Styling */
.alert {
    padding: 12px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #e8f5e9;
    color: #1b5e20;
    border-left: 4px solid #4caf50;
}

.alert-warning {
    background-color: #fff8e1;
    color: #f57f17;
    border-left: 4px solid #ffc107;
}

.alert-danger {
    background-color: #fbe9e7;
    color: #bf360c;
    border-left: 4px solid #f44336;
}

.alert-info {
    background-color: #e3f2fd;
    color: #0d47a1;
    border-left: 4px solid #2196f3;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .time-slots-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .review-item {
        flex-direction: column;
    }
    
    .review-label {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .form-navigation {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-nav {
        width: 100%;
    }
}

.user-nav {
    background-color: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    padding: 15px 0;
    margin-bottom: 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.user-nav .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: transparent;
    box-shadow: none;
    padding: 0 20px;
    margin: 0 auto;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-name {
    color: white;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-buttons {
    display: flex;
    gap: 15px;
}

.btn-outline-light {
    border: 1px solid rgba(255, 255, 255, 0.5);
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.active-bookings-btn {
    background: rgba(255, 255, 255, 0.15);
    border: none;
}
.nav-buttons .btn-outline-light {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    text-decoration: none;
    color: white;
}

.nav-buttons .btn-outline-light i {
    font-size: 1.1rem;
}

.nav-buttons .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transform: translateY(-2px);
}
</style>
</body>
</html>
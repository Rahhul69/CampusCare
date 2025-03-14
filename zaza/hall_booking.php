<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_booking'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $hall = $_POST['hall'];
    $date = $_POST['date'];
    $time_slots = isset($_POST['time_slots']) ? $_POST['time_slots'] : [];
    $purpose = $_POST['purpose'];
    
    // Validate name (only alphabets and spaces)
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> Name should contain only alphabets and spaces!</div>";
    }
    // Validate email is from Gmail
    else if (!preg_match("/@gmail\.com$/i", $email)) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> Only Gmail addresses are accepted!</div>";
    }
    // Check if date is valid (not in the past, not a holiday, not a Sunday)
    else if (strtotime($date) < strtotime(date('Y-m-d'))) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> You cannot book dates in the past!</div>";
    } 
    else if (in_array($date, $holidays)) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> Booking is not available on holidays!</div>";
    }
    else if (date('w', strtotime($date)) == 0) { // 0 is Sunday
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> Booking is not available on Sundays!</div>";
    }
    // Validate if at least one time slot is selected
    else if (empty($time_slots)) {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> Please select at least one time slot!</div>";
    }
    // All validations passed
    else if (!empty($name) && !empty($email) && !empty($hall) && !empty($date) && !empty($purpose)) {
        // Convert time slots array to string for storage
        $time_slot_str = implode(", ", $time_slots);
        
        $stmt = $conn->prepare("INSERT INTO hall_bookings (name, email, hall, date, time_slot, purpose) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hall, $date, $time_slot_str, $purpose);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Booking request submitted successfully!</div>";
            
            // Clear form data for new entry after successful submission
            $name = "";
            $email = "";
            $hall = "";
            $date = "";
            $time_slots = [];
            $purpose = "";
            
            // Set flag to reset form and show confirmation
            $reset_form = true;
        } else {
            $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Error submitting request. Try again!</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> All fields are required!</div>";
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

</head>
<body>

<div class="header-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-12 d-flex justify-content-center align-items-center">
                <img src="images/college-logo.png" alt="College Logo" class="logo">
                <h1><i class="fas fa-graduation-cap me-2"></i>Campus Care System</h1>
            </div>
        </div>
    </div>
</div>

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
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" value="<?= isset($name) ? $name : ''; ?>" required>
            </div>

            <div class="custom-form-group">
                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email Address (Gmail only)</label>
                <input type="email" class="form-control" id="email" name="email" 
                    placeholder="Enter your Gmail address" 
                    pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                    title="Please enter a valid Gmail address"
                    value="<?= isset($email) ? $email : ''; ?>" 
                    required>
                <small class="form-text text-muted">Only Gmail addresses are accepted</small>
            </div>

            <div class="custom-form-group">
                <label for="hall" class="form-label"><i class="fas fa-building"></i> Select Hall</label>
                <select class="form-select" id="hall" name="hall" required>
                    <option value="" disabled <?= !isset($hall) ? 'selected' : ''; ?>>Choose a hall</option>
                    <option value="Eric Mathias Hall" <?= (isset($hall) && $hall == 'Eric Mathias Hall') ? 'selected' : ''; ?>>Eric Mathias Hall (350 seats)</option>
                    <option value="Lcri Hall" <?= (isset($hall) && $hall == 'Lcri Hall') ? 'selected' : ''; ?>>Lcri Hall</option>
                    <option value="Gelge Hall" <?= (isset($hall) && $hall == 'Gelge Hall') ? 'selected' : ''; ?>>Gelge Hall</option>
                    <option value="Arupe Hall" <?= (isset($hall) && $hall == 'Arupe Hall') ? 'selected' : ''; ?>>Arupe Hall </option>
                    <option value="Joseph Willy Hall" <?= (isset($hall) && $hall == 'Joseph Willy Hall') ? 'selected' : ''; ?>>Joseph Willy Hall</option>
                    <option value="Sanidhya Hall" <?= (isset($hall) && $hall == 'Sanidhya Hall') ? 'selected' : ''; ?>>Sanidhya Hall (50 seats)</option>
                </select>
            </div>

            <div class="custom-form-group">
                <label for="date" class="form-label"><i class="fas fa-calendar-alt"></i> Booking Date</label>
                <input type="date" class="form-control" id="date" name="date" min="<?= date('Y-m-d'); ?>" value="<?= isset($date) ? $date : ''; ?>" required>
                <small class="text-muted">Note: Bookings are not available on Sundays and holidays.</small>
            </div>

            <div class="custom-form-group">
                <label class="form-label"><i class="fas fa-clock"></i> Select Time Slots</label>
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

                    <input type="checkbox" class="time-slot-checkbox" id="slot18" name="time_slots[]" value="18:00 - 19:00" <?= (isset($time_slots) && in_array("18:00 - 19:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot18" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        18:00 - 19:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot19" name="time_slots[]" value="19:00 - 20:00" <?= (isset($time_slots) && in_array("19:00 - 20:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot19" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        19:00 - 20:00
                    </label>

                    <input type="checkbox" class="time-slot-checkbox" id="slot20" name="time_slots[]" value="20:00 - 21:00" <?= (isset($time_slots) && in_array("20:00 - 21:00", $time_slots)) ? 'checked' : ''; ?>>
                    <label for="slot20" class="time-slot-label">
                        <span class="time-slot-check"><i class="fas fa-check-circle"></i></span>
                        20:00 - 21:00
                    </label>
                </div>
            </div>


            <div class="custom-form-group">
                <label for="purpose" class="form-label"><i class="fas fa-clipboard-list"></i> Purpose of Booking</label>
                <textarea class="form-control" id="purpose" name="purpose" rows="3" placeholder="Briefly describe the purpose of your booking"><?= isset($purpose) ? $purpose : ''; ?></textarea>
                
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
                    <li>Cancellations must be made at least 48 hours in advance.</li>
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
            <div class="feature-item"><i class="fas fa-desktop"></i> HD Projector</div>            <div class="feature-item"><i class="fas fa-snowflake"></i> Air Conditioning</div>
            <div class="feature-item"><i class="fas fa-chair"></i> Comfortable Seating</div>
            <div class="feature-item"><i class="fas fa-lightbulb"></i> Dynamic Lighting</div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Campus Care System | All Rights Reserved</p>
    <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
        
        <?php if(isset($reset_form) && $reset_form === true): ?>
        // If form was successfully submitted, show step 3
        showStep(3);
        <?php endif; ?>
    });
    
    // Function to fill purpose field when clicking a tag
    function fillPurpose(purpose) {
        document.getElementById('purpose').value = purpose;
    }
    
    // Step navigation
    const nextToReviewBtn = document.getElementById('nextToReview');
    const backToDetailsBtn = document.getElementById('backToDetails');
    const bookAnotherBtn = document.getElementById('bookAnotherBtn');
    
    nextToReviewBtn.addEventListener('click', function() {
        // Validate the form before proceeding
        if (validateStep1()) {
            // Update review page with values
            document.getElementById('review-name').textContent = document.getElementById('name').value;
            document.getElementById('review-email').textContent = document.getElementById('email').value;
            document.getElementById('review-hall').textContent = document.getElementById('hall').value;
            
            // Format date for better readability
            const selectedDate = new Date(document.getElementById('date').value);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('review-date').textContent = selectedDate.toLocaleDateString('en-US', options);
            
            // Get selected time slots
            const timeSlots = [];
            const timeSlotCheckboxes = document.querySelectorAll('input[name="time_slots[]"]:checked');
            timeSlotCheckboxes.forEach(function(checkbox) {
                timeSlots.push(checkbox.value);
            });
            document.getElementById('review-time-slots').textContent = timeSlots.join(', ');
            
            document.getElementById('review-purpose').textContent = document.getElementById('purpose').value;
            
            showStep(2);
        }
    });
    
    backToDetailsBtn.addEventListener('click', function() {
        showStep(1);
    });
    
    // Add event listener for the "Book Another Hall" button
    bookAnotherBtn.addEventListener('click', function() {
        // Reset the form
        document.getElementById('bookingForm').reset();
        // Show step 1
        showStep(1);
    });
    
    function validateStep1() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const hall = document.getElementById('hall').value;
        const date = document.getElementById('date').value;
        const purpose = document.getElementById('purpose').value;
        const timeSlots = document.querySelectorAll('input[name="time_slots[]"]:checked');
        
        if (!name || !email || !hall || !date || !purpose) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        if (timeSlots.length === 0) {
            alert('Please select at least one time slot.');
            return false;
        }
        
        // Validate name (only alphabets and spaces)
        if (!/^[a-zA-Z ]*$/.test(name)) {
            alert('Name should contain only alphabets and spaces.');
            return false;
        }
        
        // Validate email format and Gmail domain
        if (!/^[^\s@]+@gmail\.com$/.test(email)) {
            alert('Please enter a valid Gmail address. Only Gmail addresses are accepted.');
            return false;
        }
        
        return true;
    }
    
    function showStep(stepNumber) {
        // Hide all steps
        const steps = document.querySelectorAll('.form-step');
        steps.forEach(function(step) {
            step.classList.remove('active');
        });
        
        // Reset all step indicators
        const indicators = document.querySelectorAll('.step');
        indicators.forEach(function(indicator, index) {
            indicator.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                indicator.classList.add('completed');
            }
        });
        
        // Show the selected step
        document.getElementById('step' + stepNumber).classList.add('active');
        document.getElementById('step' + stepNumber + '-indicator').classList.add('active');
    }
    
    // Check for holidays and Sundays when selecting a date
    document.getElementById('date').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const holidays = <?php echo json_encode($holidays); ?>;
        
        // Check if selected date is a Sunday
        if (selectedDate.getDay() === 0) {
            alert('Bookings are not available on Sundays. Please select another date.');
            this.value = '';
            return;
        }
        
        // Check if selected date is a holiday
        if (holidays.includes(this.value)) {
            alert('Bookings are not available on holidays. Please select another date.');
            this.value = '';
            return;
        }
    });
</script>

</body>
</html>
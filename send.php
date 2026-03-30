<?php
// Database configuration
$host = "localhost"; 
$dbname = "ilinksm3_foodtruck";
$username = "ilinksm3_foodtruck";
$password = "IlinkFoodTruck";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $company_name = isset($_POST['company_name']) ? $conn->real_escape_string($_POST['company_name']) : '';
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $email_id = $conn->real_escape_string($_POST['email_id']);
    // For contact form, coverage might not be set, default to 'other' or handle accordingly
    $coverage_required = isset($_POST['coverage_required']) ? $conn->real_escape_string($_POST['coverage_required']) : 'other';
    
    // SQL query to insert data
    $sql = "INSERT INTO food_quote (full_name, company_name, contact_number, email_id, coverage_required)
            VALUES ('$full_name', '$company_name', '$contact_number', '$email_id', '$coverage_required')";

    if ($conn->query($sql) === TRUE) {
        // Send Email Notification
        $to = "info@ilinkinsurance.com.au , madhkunchala@gmail.com"; // Replace with admin email
        $subject = "New Quote Request from " . $full_name;
        $message = "You have received a new quote request:\n\n";
        $message .= "Name: " . $full_name . "\n";
        $message .= "Company: " . $company_name . "\n";
        $message .= "Phone: " . $contact_number . "\n";
        $message .= "Email: " . $email_id . "\n";
        $message .= "Coverage: " . $coverage_required . "\n";
        
        // Additional message content if from contact form (optional, if you add a message field to DB or email)
        if (isset($_POST['message'])) {
             $message .= "Message: " . $_POST['message'] . "\n";
        }

        $headers = "From: no-reply@ilinkinsurance.com.au" . "\r\n" .
                   "Reply-To: " . $email_id . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Send email
        mail($to, $subject, $message, $headers);

        // Redirect to thank you page
        header("Location: thankyou.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

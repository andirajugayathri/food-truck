<?php
$host = "localhost"; 
$dbname = "ilinksm3_foodtruck";
$username = "ilinksm3_foodtruck";
$password = "IlinkFoodTruck";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $full_name = $_POST["full_name"] ?? "";
        $email    = $_POST["email"] ?? "";
        $subject  = $_POST["subject"] ?? "New Submission";
        $message  = $_POST["message"] ?? "";

        // Insert into DB
        $sql = "INSERT INTO CONTACT (full_name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $email, $subject, $message]);

        // -------- PHP MAIL FUNCTION (YOUR FORMAT) --------

       $to = "info@ilinkinsurance.com.au, smartsolutions.designstudio@gmail.com, quotes@ilinkinsurance.com.au, smartsolutions.designstudio1@gmail.com, madhu@smartsolutionsdigi.com";

        $mail_subject = "New Submission For Food Truck Insurance";

        $safe_name = htmlspecialchars($full_name);
        $safe_email = htmlspecialchars($email);
        $safe_subject = htmlspecialchars($subject);
        $safe_message = nl2br(htmlspecialchars($message));

        $mail_message = "
        <html>
        <head>
          <title>Insurance Inquiry</title>
          <style>
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #f2f2f2; }
          </style>
        </head>
        <body>
          <h2>New Contact Form Submission</h2>
          <table>
            <tr><th>Full Name</th><td>{$safe_name}</td></tr>
            <tr><th>Email</th><td>{$safe_email}</td></tr>
            <tr><th>Subject</th><td>{$safe_subject}</td></tr>
            <tr><th>Message</th><td>{$safe_message}</td></tr>
          </table>
        </body>
        </html>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Food Truck Insurance <mail@foodtruckinsurance.com.au>\r\n";
        $headers .= "Reply-To: {$safe_name} <{$safe_email}>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        mail($to, $mail_subject, $mail_message, $headers);

        // Redirect after success
        header("Location: thankyou.html");
        exit;
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
?>

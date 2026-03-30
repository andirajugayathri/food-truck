<?php
// Start Output Buffer & Include Database + Zoho Access Token Loader
ob_start();
include 'db.php';
include '../access-token.php';

/*
 * PROCESS FLOW:
 * 1. Map Fields from Form → Zoho/DB
 * 2. Insert into Database
 * 3. Push Lead to Zoho CRM
 * 4. Send Email Notification
 */

// ------------------------------
// 1. Map Form Fields
// ------------------------------
function mapFormFields($formData) {

    // Handle Coverage Required (Multi-select)
    $input_coverage = array_filter($formData['coverage_required'] ?? []);

    
    // DEBUG: Log the received coverage data
    error_log("Received POST data: " . print_r($formData, true));
    error_log("Coverage Input: " . print_r($input_coverage, true));

    // Ensure it's an array for Zoho (it comes as array from index.html, but good to be safe)
    $coverage_array = is_array($input_coverage) ? $input_coverage : (!empty($input_coverage) ? [$input_coverage] : []);
    
    // String for DB (Comma separated)
    $coverage_db = implode(', ', $coverage_array);

    return [
        // Form → Zoho
        'Last_Name'  => $formData['full_name'] ?? '',
        'Phone'      => $formData['contact_number'] ?? '',
        'Email'      => $formData['email_id'] ?? '',
        'Company'    => $formData['company_name'] ?? '',

        // Zoho FORMAT (Array)
        'Coverage_Required' => $coverage_array,

        // DB FORMAT (String)
        'Coverage_Required_DB' => $coverage_db,

        // Static Zoho Fields
        'Product_Inquiry' => 'Food Truck',
        'Sales_Team'      => 'Shalin Shah - AR: 418137',
        'Service_Team'    => 'Shalin Shah',

        'Layout' => [
            'name' => 'Website',
            'id'   => '62950000001318018'
        ],

        'Owner' => [
            'name'  => 'Shalin Shah',
            'id'    => '62950000000229001',
            'email' => 'shalin@ilinkinsurance.com.au'
        ]
    ];
}

// ------------------------------
// 2. Insert Into Database
// ------------------------------
function insertDataIntoDatabase($mappedData, $pdo) {
    try {
        $sql = "INSERT INTO food_quote(
                    full_name,
                    company_name,
                    contact_number,
                    email_id,
                    coverage_required,
                    submitted_at
                ) VALUES (
                    :full_name,
                    :company_name,
                    :contact_number,
                    :email_id,
                    :coverage_required,
                    NOW()
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name'        => $mappedData['Last_Name'],
            ':company_name'     => $mappedData['Company'],
            ':contact_number'   => $mappedData['Phone'],
            ':email_id'         => $mappedData['Email'],
            ':coverage_required'=> $mappedData['Coverage_Required_DB'],
        ]);

        return true;

    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        return false;
    }
}

// ------------------------------
// 3. Send to Zoho CRM
// ------------------------------
function addRecordToZoho($mappedData, $pdo) {

    getAccessToken($pdo);
    $accessToken = $_SESSION['access_token'] ?? null;

    if (!$accessToken) {
        error_log("Zoho Token Missing");
        return false;
    }

    $module = 'Leads';
    $apiUrl = "https://www.zohoapis.com.au/crm/v2/{$module}";
    $payload = json_encode(['data' => [$mappedData]]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Zoho-oauthtoken {$accessToken}",
            "Content-Type: application/json"
        ]
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 201) {
        return true;
    }

    error_log("Zoho API Error ($httpCode): " . $response);
    return false;
}

// ------------------------------
// 4. MAIN PROCESS
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mappedData = mapFormFields($_POST);

    // Mandatory fields validation
    if (
        empty($mappedData['Last_Name']) ||
        empty($mappedData['Email']) ||
        empty($mappedData['Phone'])
    ) {
        header("Location: ../../error.html");
        exit;
    }

    // ------------------------------
    // Send Email Notification
    // ------------------------------
    $to = "info@ilinkinsurance.com.au, smartsolutions.designstudio@gmail.com, quotes@ilinkinsurance.com.au, smartsolutions.designstudio1@gmail.com, madhu@smartsolutionsdigi.com";
    $subject = "New Submission For Food Truck Insurance";

    $coverage_display = htmlspecialchars($mappedData['Coverage_Required_DB']);

    $message = "
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
      <h2>New Insurance Inquiry For Food Truck</h2>
      <table>
        <tr><th>Full Name</th><td>{$mappedData['Last_Name']}</td></tr>
        <tr><th>Email</th><td>{$mappedData['Email']}</td></tr>
        <tr><th>Phone</th><td>{$mappedData['Phone']}</td></tr>
        <tr><th>Company</th><td>{$mappedData['Company']}</td></tr>
        <tr><th>Coverage Required</th><td>{$coverage_display}</td></tr>
       
      </table>
    </body>
    </html>
    ";

 $headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Food Truck Insurance <mail@foodtruckinsurance.com.au>\r\n";
$headers .= "Reply-To: {$mappedData['Last_Name']} <{$mappedData['Email']}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";


    mail($to, $subject, $message, $headers);

    // ------------------------------
    // DB + Zoho CRM Flow
    // ------------------------------
    if (insertDataIntoDatabase($mappedData, $pdo)) {
        if (addRecordToZoho($mappedData, $pdo)) {
            header("Location: ../../thankyou.html");
            exit;
        }
    }

    // Fail safe redirect
    header("Location: ../../error.html");
    exit;
}
?>

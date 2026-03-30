<?php
// Start session and include DB connection
session_start();
include 'forms/db.php';

// Path for custom error log
$log_file = __DIR__ . '/zoho_error.log';

// OAuth credentials
$client_id = '1000.GXOZUWKPS29YUFS6N1VXJQCIKZM86L';
$client_secret = 'd8fe0f50ff3790c34f3285e35c486284df2b1e437b';
$redirect_uri = 'https://foodtruckinsurance.com.au/zoho/authcode.php';
$grant_token = '1000.a672037b1e77c8bf18c11e3e5d9aab08.fd523ef5fe2f98ff802833dc3e337955';

// Token endpoint
$token_url = "https://accounts.zoho.com.au/oauth/v2/token";

// Build POST fields
$post_fields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $grant_token,
]);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);

// Check for cURL error
if (curl_errno($ch)) {
    $err = 'cURL Error: ' . curl_error($ch);
    error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $log_file);
    echo $err;
    curl_close($ch);
    exit;
}

curl_close($ch);

// Log full Zoho response
error_log(date('[Y-m-d H:i:s] ') . "Zoho Raw Response: " . $response . PHP_EOL, 3, $log_file);

// Decode response
$tokens = json_decode($response, true);

// Handle errors from Zoho
if (isset($tokens['error'])) {
    $err = "Zoho Error: " . ($tokens['error_description'] ?? $tokens['error']);
    error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $log_file);
    echo $err;
    exit;
}

// Ensure access and refresh tokens are present
if (!isset($tokens['access_token'], $tokens['refresh_token'])) {
    $err = "Missing tokens. Response: " . $response;
    error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $log_file);
    echo "Failed to receive tokens.";
    exit;
}

// Calculate expiry timestamp
$expiry_time = time() + $tokens['expires_in'];

// Insert tokens into database
try {
    $sql = "INSERT INTO oauthtoken (client_id, refresh_token, access_token, grant_token, expiry_time) 
            VALUES (:client_id, :refresh_token, :access_token, :grant_token, :expiry_time)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'client_id' => $client_id,
        'refresh_token' => $tokens['refresh_token'],
        'access_token' => $tokens['access_token'],
        'grant_token' => $grant_token,
        'expiry_time' => $expiry_time
    ]);

    echo "Token saved successfully.";
    error_log(date('[Y-m-d H:i:s] ') . "Token saved successfully." . PHP_EOL, 3, $log_file);

} catch (PDOException $e) {
    $err = "Database Error: " . $e->getMessage();
    error_log(date('[Y-m-d H:i:s] ') . $err . PHP_EOL, 3, $log_file);
    echo "Database Error.";
}
?>

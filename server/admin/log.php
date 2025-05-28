<?php
require_once "config.php";
header("Content-Type: application/json");

// XOR decryption
function xor_decrypt($data) {
    $key = PRESHARED_KEY;
    $result = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $result .= chr(ord($data[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return $result;
}

// Validate pre-shared key and source IP
if (!isset($_SERVER["HTTP_X_AUTH"]) || $_SERVER["HTTP_X_AUTH"] !== PRESHARED_KEY) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
$client_ip = $_SERVER["REMOTE_ADDR"];
if (!in_array($client_ip, $pico_ips)) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid client IP"]);
    exit;
}

// Connect to MySQL (localhost, secure)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    $conn->close();
    exit;
}

// Process POST data
$input = file_get_contents("php://input");
$data = json_decode($input, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
    $conn->close();
    exit;
}

// Handle fingerprint data
foreach ($data as $type => $encrypted) {
    $decrypted = json_decode(xor_decrypt($encrypted), true);
    if (!$decrypted) {
        continue;
    }
    if ($type === "fingerprint_id") {
        $id = (int)$decrypted["id"];
        $time = (int)$decrypted["time"];
        $stmt = $conn->prepare("INSERT INTO attendance (fingerprint_id, timestamp) VALUES (?, FROM_UNIXTIME(?))");
        $stmt->bind_param("ii", $id, $time);
        $stmt->execute();
        $stmt->close();
    } elseif ($type === "template") {
        $id = (int)$decrypted["id"];
        $template = hex2bin($decrypted["template"]);
        $stmt = $conn->prepare("INSERT INTO users (fingerprint_id, template) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $template);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
http_response_code(200);
echo json_encode(["status" => "success"]);
?>
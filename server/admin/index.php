<?php
require_once "config.php";
header("Content-Type: text/html");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Admin authentication (simplified, use proper auth in production)
    if (!isset($_POST["admin_pass"]) || $_POST["admin_pass"] !== "admin_secure_123") {
        echo "Unauthorized";
        exit;
    }
    // Update Pico IPs
    $new_ips = array_filter(array_map("trim", explode(",", $_POST["pico_ips"])));
    $config_content = "<?php\n";
    $config_content .= "define(\"PRESHARED_KEY\", \"" . PRESHARED_KEY . "\");\n";
    $config_content .= "define(\"DB_HOST\", \"" . DB_HOST . "\");\n";
    $config_content .= "define(\"DB_USER\", \"" . DB_USER . "\");\n";
    $config_content .= "define(\"DB_PASS\", \"" . DB_PASS . "\");\n";
    $config_content .= "define(\"DB_NAME\", \"" . DB_NAME . "\");\n";
    $config_content .= "\$pico_ips = [" . implode(", ", array_map(function ($ip) {
        return "\"$ip\""; }, $new_ips)) . "];\n";
    $config_content .= "?>";
    file_put_contents("config.php", $config_content);
    echo "Pico IPs updated";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>
</head>

<body>
    <h2>Update Pico IPs</h2>
    <form method="POST">
        <label>Pico IPs (comma-separated):</label><br>
        <input type="text" name="pico_ips" value="<?php echo implode(", ", $pico_ips); ?>" required><br>
        <label>Admin Password:</label><br>
        <input type="password" name="admin_pass" required><br>
        <input type="submit" value="Update IPs">
    </form>
</body>

</html>
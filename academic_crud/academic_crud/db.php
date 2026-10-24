<?php
/*
 * Database configuration for local XAMPP, Render, and Aiven.
 * Local defaults keep the original project working with XAMPP.
 * Set DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, and DB_SSL_CA
 * as environment variables on Render.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$database = getenv('DB_NAME') ?: 'academic_db';
$port = (int)(getenv('DB_PORT') ?: 3306);
$sslCa = getenv('DB_SSL_CA') ?: '';

try {
    $conn = mysqli_init();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    // Aiven requires TLS. In Render, add the downloaded Aiven CA certificate
    // as a Secret File and set DB_SSL_CA to its mounted path (e.g. /etc/secrets/ca.pem).
    if ($sslCa !== '') {
        if (!is_readable($sslCa)) {
            throw new RuntimeException('DB_SSL_CA is set but the CA certificate file is not readable.');
        }
        $conn->ssl_set(null, null, $sslCa, null, null);
        $conn->real_connect($host, $user, $password, $database, $port, null, MYSQLI_CLIENT_SSL);
    } else {
        $conn->real_connect($host, $user, $password, $database, $port);
    }

    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check the Render environment variables and Aiven database connection settings.');
}
?>

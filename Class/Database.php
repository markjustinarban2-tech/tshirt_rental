<?php
class Database {
    protected $conn;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . "/../.env");
        $host = $env['HOST'];
        $port = 21203;
        $db   = $env['DB'];
        $user = $env['USER'];
        $pass = $env['PASS'];

        $caCertPath = __DIR__ . '/ca.pem';
        $this->conn = mysqli_init();
        $this->conn->ssl_set(NULL, NULL, $caCertPath, NULL, NULL);
        $this->conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);
        
        if ($this->conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }
}
?>
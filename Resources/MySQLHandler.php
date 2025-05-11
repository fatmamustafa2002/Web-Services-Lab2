<?php
class MySQLHandler {
    private $conn;

    public function __construct() {
        $host = 'localhost';
        $user = 'root';
        $pass = ''; 
        $db = 'glass_shop';
        $this->conn = new mysqli($host, $user, $pass, $db);
        if ($this->conn->connect_error) {
            throw new Exception('Database connection failed');
        }
    }

    public function select($table, $conditions) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = '" . $this->conn->real_escape_string($value) . "'";
        }
        $query = "SELECT * FROM $table";
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        $result = $this->conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return false;
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_map([$this->conn, 'real_escape_string'], array_values($data));
        $query = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES ('" . implode("', '", $values) . "')";
        return $this->conn->query($query);
    }

    public function update($table, $data, $conditions) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = '" . $this->conn->real_escape_string($value) . "'";
        }
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = '" . $this->conn->real_escape_string($value) . "'";
        }
        $query = "UPDATE $table SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        return $this->conn->query($query);
    }

    public function delete($table, $conditions) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = '" . $this->conn->real_escape_string($value) . "'";
        }
        $query = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        return $this->conn->query($query);
    }
}
?>
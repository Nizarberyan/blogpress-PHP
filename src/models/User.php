<?php

require_once __DIR__ . '/../../config/database.php';

class User
{
    private $conn;
    private $table_name = 'users';

    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $role;
    public $created_at;
    public $last_login;

    public function __construct()
    {
        $this->conn = Database::getInstance();
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    public function register()
    {
        // First check if username already exists
        $check_query = "SELECT user_id FROM " . $this->table_name . " WHERE username = :username OR email = :email LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":username", $this->username);
        $check_stmt->bindParam(":email", $this->email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return ['error' => true, 'message' => 'Username or email already exists'];
        }

        // Proceed with registration if username and email are unique
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      password_hash = :password_hash,
                      role = 'reader'";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($this->password_hash, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $hashed_password);

        if ($stmt->execute()) {
            return ['error' => false, 'message' => 'Registration successful'];
        }

        return ['error' => true, 'message' => 'Registration failed'];
    }

    public function login()
    {
        $query = "SELECT user_id, username, password_hash, role FROM " . $this->table_name . " 
                  WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password_hash, $row['password_hash'])) {
            // Update last login
            $update_query = "UPDATE " . $this->table_name . " 
                           SET last_login = CURRENT_TIMESTAMP 
                           WHERE user_id = :user_id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(":user_id", $row['user_id']);
            $update_stmt->execute();

            return $row;
        }

        return false;
    }

    public function updateRole($user_id, $new_role)
    {
        $valid_roles = ['admin', 'editor', 'writer', 'reader'];
        if (!in_array($new_role, $valid_roles)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                 SET role = :role
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $new_role);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }
}

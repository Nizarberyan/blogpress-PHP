<?php
// src/controllers/AuthController.php

// Change this line
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function register($username, $email, $password)
    {
        $this->user->username = $username;
        $this->user->email = $email;
        $this->user->password_hash = $password;

        $result = $this->user->register();
        if (!$result['error']) {
            return true;
        }
        return false;
    }

    public function login($username, $password)
    {
        $this->user->username = $username;
        $this->user->password_hash = $password;

        $logged_in_user = $this->user->login();

        if ($logged_in_user) {
            $_SESSION['user_id'] = $logged_in_user['user_id'];
            $_SESSION['username'] = $logged_in_user['username'];
            $_SESSION['role'] = $logged_in_user['role'];
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_destroy();
        return true;
    }
}

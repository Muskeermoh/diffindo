<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function is_admin() {
    return is_logged_in() && $_SESSION['user']['email'] === 'admin@diffindo.com';
}

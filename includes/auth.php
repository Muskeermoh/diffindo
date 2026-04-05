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
    return is_logged_in() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function is_support_staff() {
    return is_logged_in() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'support_staff';
}

function is_customer() {
    return is_logged_in() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'customer';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: ../index.php");
        exit;
    }
}

function require_support_staff() {
    require_login();
    if (!is_support_staff()) {
        header("Location: ../index.php");
        exit;
    }
}

function get_user_role() {
    return is_logged_in() ? $_SESSION['user']['role'] : null;
}

<?php
function check_login($user_type = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    if ($user_type && $_SESSION['user_type'] !== $user_type) {
        header("Location: ../login.php");
        exit();
    }
}

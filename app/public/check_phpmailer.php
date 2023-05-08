<?php
require_once 'wp-load.php';

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo 'PHPMailer is installed and available on your WordPress site.';
} else {
    echo 'PHPMailer is not installed or not available on your WordPress site.';
}

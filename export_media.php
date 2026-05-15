<?php
require_once __DIR__ . '/includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    exit("Unauthorized access.");
}

#compromised version
// if (isset($_GET['file'])) {
//     $file_name = $_GET['file'];
    
//     $filepath = __DIR__ . '/uploads/properties/' . $file_name;

//     if (file_exists($filepath)) {
//         header('Content-Description: File Transfer');
//         header('Content-Type: application/octet-stream');
//         header('Content-Disposition: attachment; filename="' . $file_name . '"');
//         header('Expires: 0');
//         header('Cache-Control: must-revalidate');
//         header('Content-Length: ' . filesize($filepath));
        
//         readfile($filepath);
//         exit;
//     } else {
//         die("Eroare: Fișierul nu a fost găsit pe server.");
//     }
// }
#http://localhost/booking_website/export_media.php?file=../../includes/init.php

if (isset($_GET['file'])) {
    $file_name = basename($_GET['file']);

    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed_extensions)) {
        die("Only images can be downloaded.");
    }

    $file_path = __DIR__ . '/uploads/properties/' . $file_name;

    $real_path = realpath($file_path);
    $allowed_folder = realpath(__DIR__ . '/uploads/properties/');

    if ($real_path === false || strpos($real_path, $allowed_folder) !== 0) {
        exit;
    }

    if (file_exists($file_path)) {
        $mime_type = mime_content_type($file_path);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime_type, $allowed_mimes)) {
            exit("Only images can be downloaded.");
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($file_path));
        
        if (ob_get_level()) {
            ob_end_clean();
        }
        flush();

        readfile($file_path);
    }
    exit;
}
?>
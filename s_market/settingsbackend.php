<?php
session_start();

// Handle name
if (isset($_POST['name'])) {
    $_SESSION['user_name'] = $_POST['name']; // Example save (replace with DB update)
}

// Handle profile picture
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);
    $uploadPath = $uploadDir . $fileName;
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath);

    $_SESSION['user_photo'] = $uploadPath; // Example save (replace with DB update)
}

header("Location: settings.php");
exit;
?>
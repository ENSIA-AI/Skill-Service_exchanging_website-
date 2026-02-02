<?php
session_start();
require_once 'includes/dbh.inc.php';

if (isset($_POST['save-btn'])) {
    if (!empty($_FILES['profile_picture_desktop']['name'])) {
        $file = $_FILES['profile_picture_desktop'];
    } elseif (!empty($_FILES['profile_picture_mobile']['name'])) {
        $file = $_FILES['profile_picture_mobile'];
    } else {
        header("Location: editprofile.php?error=nofile");
        exit();
    }

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 4000000) { 

                $fileNameNew = "profile" . uniqid('', true) . "." . $fileActualExt;
                $fileDestination = '../../assets/uploads/profile_pics/' . $fileNameNew;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {

                        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] :
                                  (isset($_GET['id']) ? (int)$_GET['id'] : 0);
                    
                    $username = $_SESSION['username'];
                    $dbPath = '../../assets/uploads/profile_pics/' . $fileNameNew;

                    $sql = "UPDATE Users SET ProfilePicture = :pfp WHERE UserId = :id";
                    $stmt = $connection->prepare($sql);
                    $stmt->execute([
                        ':pfp' => $dbPath,
                        ':id'  => $userId
                    ]);

                    header("Location: editprofile.php?uploadsuccess");
                    exit();
                }
            } else {
                echo "Your file is too big!";
            }
        } else {
            echo "There was an error uploading your file!";
        }
    } else {
        echo "You cannot upload files of this type! Detected: " . $fileActualExt;
    }
}

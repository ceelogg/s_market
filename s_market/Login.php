<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market"; 

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['loginButton'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM login WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            header("Location: userpage.php");
            exit();
        } else {
            echo "<script>
                    alert('Invalid email or password!');
                    window.location.href = 'login.php'; 
                  </script>";
        }
    } else {
        echo "<script>
                alert('Invalid email or password!');
                window.location.href = 'login.php'; 
              </script>";
    }
}

mysqli_close($conn);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Login&Register.css">
</head>
<body>

    <div class="container">

        
        <!-- Login Form -->
        <div class="form-section" id="login-container">
            <h2>Login</h2>
            <form id="login-form" action="Login.php" method="POST">
                <div class="input-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="input-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                    <div class="error-message">Password is required</div>
                </div>
                <button type="submit" name="loginButton">Login</button>
                <div class="alternative">
                    Don't have an account? <a href="Register.php">Register</a>
                </div>
            </form>
        </div>
 

</body>
</html>
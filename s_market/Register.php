<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";  

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle Registration Form Submission
if(isset($_POST['registerButton'])) {
    $name = $_POST['name']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];



    // Check if passwords match
    if ($password != $confirmPassword) {
        echo "<script>
                alert('Passwords do not match!');
                window.location.href = 'Register.php'; 
              </script>";
    } else {

        $sql = "INSERT INTO login (name, email, password) VALUES ('$name', '$email', '$password')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>
                    alert('Registration successful!');
                    window.location.href = 'Login.php'; // Redirect to login page
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . mysqli_error($conn) . "');
                    window.location.href = 'Register.php'; // Redirect back to homepage
                  </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Login&Register.css">
</head>
<body>

    <div class="container">
        <div class="form-section" id="register-container">
            <h2>Register</h2>
            <form id="register-form" action="register.php" method="POST">
                <div class="input-group">
                    <label for="register-name">Full Name</label>
                    <input type="text" id="register-name" name="name" required>
                    <div class="error-message">Name is required</div>
                </div>
                <div class="input-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" required>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="input-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required>
                    <div class="error-message">Password must be at least 8 characters</div>
                </div>
                <div class="input-group">
                    <label for="register-confirm">Confirm Password</label>
                    <input type="password" id="register-confirm" name="confirm-password" required>
                    <div class="error-message">Passwords do not match</div>
                </div>
                <button type="submit" name="registerButton">Register</button>
                <div class="alternative">
                    Already have an account? <a href="Login.php">Login</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

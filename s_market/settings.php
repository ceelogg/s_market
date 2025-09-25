<?php
session_start();

// Example pre-filled values (you can fetch from DB instead)
$userName = "Sophie Chamberlain";
$userPhoto = "profile.png"; // Default profile photo
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $_SESSION['theme'] ?? 'light'; ?>">

<div class="settings-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item"><a href="userpage.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><a href="#"><i class="fas fa-bullhorn"></i> Marketing</a></li>
            <li class="nav-item active"><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="settings-main">
    

        <!-- Profile Settings -->
        <div class="card">
            <h3>Profile Settings</h3>
            <form method="POST" enctype="multipart/form-data" action="update_profile.php" class="profile-form">
                <div class="profile-box">
                    <img src="<?php echo $userPhoto; ?>" alt="Profile Picture" class="profile-img">
                    <div>
                        <label>Change Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/*">
                    </div>
                </div>

                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo $userName; ?>" required>

                <button type="submit">Update Profile</button>
            </form>
        </div>

        <!-- Theme Section -->
        <div class="card">
            <h3>Theme Selection</h3>
            <form id="themeForm">
                <label class="switch">
                    <input type="checkbox" id="themeToggle" <?php echo ($_SESSION['theme'] ?? 'light') === 'dark' ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
                <span id="themeLabel"><?php echo ($_SESSION['theme'] ?? 'light') === 'dark' ? 'Dark Mode' : 'Light Mode'; ?></span>
            </form>
        </div>

        <!-- Password Management -->
        <div class="card">
            <h3>Password Management</h3>
            <form method="POST" action="update_password.php">
                <label>Current Password</label>
                <input type="password" name="current_password" required>

                <label>New Password</label>
                <input type="password" name="new_password" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>

            <div class="rules-box">
                <p><b>Password Rules:</b></p>
                <ul>
                    <li>At least 8 characters</li>
                    <li>Include one number</li>
                    <li>Include one special character</li>
                    <li>Cannot match previous password</li>
                </ul>
            </div>
        </div>
    </main>
</div>

<script>
const toggle = document.getElementById('themeToggle');
const label = document.getElementById('themeLabel');

toggle.addEventListener('change', () => {
    document.body.classList.toggle('dark', toggle.checked);
    document.body.classList.toggle('light', !toggle.checked);
    label.textContent = toggle.checked ? "Dark Mode" : "Light Mode";

    // Save preference in backend
    let formData = new FormData();
    formData.append("theme", toggle.checked ? "dark" : "light");

    fetch("save_theme.php", { method: "POST", body: formData });
});
</script>

</body>
</html>

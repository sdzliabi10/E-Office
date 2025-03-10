<?php
session_start();
include 'db.php';

// Check if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: " . ($_SESSION['level'] == 'admin' ? "admin.php" : "user.php"));
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Sanitize and validate inputs
    $username = filter_var(trim($_POST["username"]), FILTER_SANITIZE_STRING);
    $password = $_POST["password"]; // Keep password raw for password_verify()

    // Prepare the SQL statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['login'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['level'] = $user['level'];

            // Redirect user based on role
            header("Location: " . ($user['level'] == 'admin' ? "admin.php" : "user.php"));
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magang/new/images/bps.png" />
    <title>Login - E-Office</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-control {
            margin-bottom: 20px;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .error-message {
            font-size: 14px;
            margin-bottom: 15px;
            color: red;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            text-align: center;
            width: 100%;
            font-size: 12px;
            color: #aaa;
        }

        @media (max-width: 480px) {
            .login-container {
                width: 90%;
            }
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group position-relative">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <i class="fas fa-eye eye-icon" id="togglePassword"></i> <!-- Eye icon for show/hide password -->
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onload = function() {
            // Toggle show/hide password
            document.getElementById('togglePassword').addEventListener('click', function() {
                var passwordField = document.getElementById('password');
                var icon = this;

                if (passwordField.type === "password") {
                    passwordField.type = "text"; // Show password
                    icon.classList.remove("fa-eye"); // Change icon to 'open eye'
                    icon.classList.add("fa-eye-slash"); // Change icon to 'closed eye'
                } else {
                    passwordField.type = "password"; // Hide password
                    icon.classList.remove("fa-eye-slash"); // Change icon to 'closed eye'
                    icon.classList.add("fa-eye"); // Change icon to 'open eye'
                }
            });

            // Disable scroll when showing error message
            function disableScroll() {
                document.body.style.overflow = 'hidden';
            }

            function enableScroll() {
                document.body.style.overflow = '';
            }

            <?php if ($error == "Password salah!"): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: 'Password salah, silakan coba lagi.',
                    heightAuto: false 
                }).then(() => enableScroll());
            <?php elseif ($error == "Username tidak ditemukan!"): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: 'Username tidak ditemukan, silakan coba lagi.',
                    heightAuto: false 
                }).then(() => enableScroll());
                disableScroll();
            <?php endif; ?>
        };
    </script>
</body>

</html>
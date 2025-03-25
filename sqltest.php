<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
</head>
<body>
    <h1>Database Connection Test</h1>

    <?php
    $errorMsg = '';
    $success = true;

    // Define the config file path
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        echo "<p style='color:red;'>Database configuration file not found.</p>";
        $success = false;
    } else {
        // Read database config
        $config = parse_ini_file($configFile);
        if ($config === false) {
            echo "<p style='color:red;'>Failed to parse database config file.</p>";
            $success = false;
        } else {
            // Attempt to connect
            $conn = new mysqli(
                $config['servername'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );

            if ($conn->connect_error) {
                echo "<p style='color:red;'>Host Connection failed: " . $conn->connect_error . "</p>";
                $success = false;
            } else {
                echo "<p style='color:green;'>Successfully connected to the database!</p>";

                // Handle registration
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
                    $name = $conn->real_escape_string($_POST['name']);
                    $email = $conn->real_escape_string($_POST['email']);
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

                    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
                    if ($conn->query($sql) === TRUE) {
                        echo "<p style='color:green;'>Registration successful!</p>";
                    } else {
                        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
                    }
                }

                // Handle login
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
                    $email = $conn->real_escape_string($_POST['email']);
                    $password = $_POST['password'];

                    $sql = "SELECT * FROM users WHERE email='$email'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        if (password_verify($password, $user['password'])) {
                            echo "<p style='color:green;'>Login successful!</p>";
                        } else {
                            echo "<p style='color:red;'>Invalid email or password.</p>";
                        }
                    } else {
                        echo "<p style='color:red;'>No user found with that email.</p>";
                    }
                }

                // Fetch data from the 'users' table as an example
                $sql = "SELECT id, name, email FROM users";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<h2>Users Data:</h2>";
                    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["name"]. "</td><td>" . $row["email"]. "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No records found in the 'users' table.</p>";
                }

                $conn->close();
            }
        }
    }
    ?>

    <!-- Registration Form -->
    <h2>Register</h2>
    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" name="register" value="Register">
    </form>

    <!-- Login Form -->
    <h2>Login</h2>
    <form method="POST" action="">
        <label for="login-email">Email:</label>
        <input type="email" id="login-email" name="email" required><br><br>
        <label for="login-password">Password:</label>
        <input type="password" id="login-password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>

</body>
</html>

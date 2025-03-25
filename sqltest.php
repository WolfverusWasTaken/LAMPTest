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
    // Define the config file path
    $configFile = '/var/www/private/db-config.ini';

    // Function to establish database connection
    function connectToDatabase($configFile) {
        if (!file_exists($configFile)) {
            echo "<p style='color:red;'>Database configuration file not found.</p>";
            return null;
        }

        $config = parse_ini_file($configFile);
        if ($config === false) {
            echo "<p style='color:red;'>Failed to parse database config file.</p>";
            return null;
        }

        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        if ($conn->connect_error) {
            echo "<p style='color:red;'>Host Connection failed: " . $conn->connect_error . "</p>";
            return null;
        }

        echo "<p style='color:green;'>Successfully connected to the database!</p>";
        return $conn;
    }

    // Function to get venues for dropdown
    function getVenuesDropdown($conn) {
        $options = '<option value="">Select Venue</option>';
        $sql = "SELECT id, name FROM venues";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $options .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
        }
        return $options;
    }

    // Register function
    function handleRegister() {
        global $configFile;
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
            $conn = connectToDatabase($configFile);
            if ($conn) {
                $name = $conn->real_escape_string($_POST['name']);
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
                if ($conn->query($sql) === TRUE) {
                    echo "<p style='color:green;'>Registration successful!</p>";
                } else {
                    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
                }
                $conn->close();
            }
        }
    }

    // Login function
    function handleLogin() {
        global $configFile;
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
            $conn = connectToDatabase($configFile);
            if ($conn) {
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
                $conn->close();
            }
        }
    }

    // Add Booking function
    function handleAddBooking() {
        global $configFile;
        $output = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_booking'])) {
            $conn = connectToDatabase($configFile);
            if ($conn) {
                $userId = $conn->real_escape_string($_POST['user_id']);
                $venueId = $conn->real_escape_string($_POST['venue_id']);
                $bookingDate = $conn->real_escape_string($_POST['booking_date']);
                $details = $conn->real_escape_string($_POST['details']);

                $sql = "INSERT INTO bookings (user_id, venue_id, booking_date, details) VALUES ('$userId', '$venueId', '$bookingDate', '$details')";
                if ($conn->query($sql) === TRUE) {
                    $output .= "<p style='color:green;'>Booking added successfully!</p>";
                } else {
                    $output .= "<p style='color:red;'>Error: " . $conn->error . "</p>";
                }
                $conn->close();
            }
        }
        return $output;
    }

    // List Current Bookings function
    function handleListCurrentBookings() {
        global $configFile;
        $output = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_current_bookings'])) {
            $conn = connectToDatabase($configFile);
            if ($conn) {
                $userId = $conn->real_escape_string($_POST['uId']);

                $sql = "SELECT * FROM bookings WHERE user_id = '$userId'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $output .= "<h2>Bookings for User ID: $userId</h2>";
                    $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        $output .= "<tr><td>" . $row["id"] . "</td><td>" . $row["booking_date"] . "</td><td>" . $row["details"] . "</td></tr>";
                    }
                    $output .= "</table>";
                } else {
                    $output .= "<p style='color:red;'>No bookings found for User ID: $userId.</p>";
                }
                $conn->close();
            } else {
                $output .= "<h4 style='color:red;'>DB Connection failed: generating sample table</h4>";
                $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                $output .= "<tr><td>01</td><td>01-01-1995</td><td>Badminton Court</td></tr>";
                $output .= "</table>";
            }
        }
        return $output;
    }

    // List Available Bookings function
    function handleListAvailableBookings() {
        global $configFile;
        $output = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_available_bookings'])) {
            $conn = connectToDatabase($configFile);
            if ($conn) {
                $venueId = $conn->real_escape_string($_POST['vId']);

                // Example: Show bookings for a specific venue (adjust logic as needed)
                $sql = "SELECT * FROM bookings WHERE venue_id = '$venueId'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $output .= "<h2>Bookings for Venue ID: $venueId</h2>";
                    $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        $output .= "<tr><td>" . $row["id"] . "</td><td>" . $row["booking_date"] . "</td><td>" . $row["details"] . "</td></tr>";
                    }
                    $output .= "</table>";
                } else {
                    $output .= "<p style='color:red;'>No bookings found for Venue ID: $venueId.</p>";
                }
                $conn->close();
            } else {
                $output .= "<h4 style='color:red;'>DB Connection failed: generating sample table</h4>";
                $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                $output .= "<tr><td>01</td><td>01-01-1995</td><td>Badminton Court</td></tr>";
                $output .= "</table>";
            }
        }
        return $output;
    }

    // Call the handler functions
    handleRegister();
    handleLogin();
    $addBookingOutput = handleAddBooking();
    $currentBookingsOutput = handleListCurrentBookings();
    $availableBookingsOutput = handleListAvailableBookings();

    // Fetch and display users data on initial load
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $sql = "SELECT id, name, email FROM users";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<h2>Users Data:</h2>";
                echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . $row["id"] . "</td><td>" . $row["name"] . "</td><td>" . $row["email"] . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No records found in the 'users' table.</p>";
            }
            $venuesDropdown = getVenuesDropdown($conn); // Get venues for dropdown
            $conn->close();
        }
    } else {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $venuesDropdown = getVenuesDropdown($conn); // Get venues for dropdown on POST
            $conn->close();
        } else {
            $venuesDropdown = '<option value="">No venues available (DB error)</option>';
        }
    }
    ?>

    <!-- Container for forms -->
    <div style="display: flex; justify-content: flex-end; gap: 20px; width: 90%; margin-top: 30px;">

        <!-- Registration Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Register</h2>
            <form method="POST" action="">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="register" value="Register" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
        </div>

        <!-- Login Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Login</h2>
            <form method="POST" action="">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" name="email" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="login-password">Password:</label>
                <input type="password" id="login-password" name="password" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="login" value="Login" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
        </div>

        <!-- Add Booking Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Add Booking</h2>
            <form method="POST" action="">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="venue_id">Venue:</label>
                <select id="venue_id" name="venue_id" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;">
                    <?php echo $venuesDropdown; ?>
                </select><br>
                <label for="booking_date">Booking Date:</label>
                <input type="date" id="booking_date" name="booking_date" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="details">Details:</label>
                <input type="text" id="details" name="details" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="add_booking" value="Add Booking" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $addBookingOutput; ?>
        </div>

        <!-- List Available Bookings Form -->
        <div style="width: 60%; border: 1px solid #ccc; padding: 20px;">
            <h2>List Available Bookings</h2>
            <form method="POST" action="">
                <label for="vId">Select Venue:</label>
                <select id="vId" name="vId" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;">
                    <?php echo $venuesDropdown; ?>
                </select><br>
                <input type="submit" name="show_available_bookings" value="Show Available Bookings" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $availableBookingsOutput; ?>
        </div>

        <!-- List Current Bookings Form -->
        <div style="width: 60%; border: 1px solid #ccc; padding: 20px;">
            <h2>List Current Bookings</h2>
            <form method="POST" action="">
                <label for="uId">Enter User ID:</label>
                <input type="number" id="uId" name="uId" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="show_current_bookings" value="Show Current Bookings" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $currentBookingsOutput; ?>
        </div>

    </div>

</body>
</html>
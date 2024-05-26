<?php
session_start();
include('server.php');


// Initialize an array to store errors
$errors = array();

// Check if the table exists and create it if it doesn't
$tableExistsQuery = "SHOW TABLES LIKE 'users'";
$result = $connection->query($tableExistsQuery);

if ($result->num_rows == 0) {
    // Table does not exist, create it
    $createTableQuery = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        );
    ";

    if ($connection->query($createTableQuery) === TRUE) {
        echo "Table 'users' created successfully.";
    } else {
        die("Error creating table: " . $connection->error);
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data and validate/sanitize
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $confirmpassword = filter_input(INPUT_POST, 'confirmpassword', FILTER_SANITIZE_STRING);

    // Check if passwords match
    if ($password !== $confirmpassword) {
        die("Passwords do not match.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $stmt = $connection->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("ss", $username, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Registration successful";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_user'])) {
    // Sanitize and validate input
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    $confirmpassword = mysqli_real_escape_string($connection, $_POST['confirmpassword']);

    // Check for empty fields
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
    if ($password != $confirmpassword) {
        array_push($errors, "Passwords do not match");
    }

    // If no errors, proceed to check if the username already exists
    if (count($errors) == 0) {
        $user_check_query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $result = mysqli_query($connection, $user_check_query);

        if ($result) {
            $user = mysqli_fetch_assoc($result);

            if ($user) { // if user exists
                if ($user['username'] === $username) {
                    array_push($errors, "Username already exists");
                }
            }
        } else {
            array_push($errors, "Database query failed: " . mysqli_error($connection));
        }

        // If no errors, proceed to register the user
        if (count($errors) == 0) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES('$username', '$hashedPassword')";
            
            if (mysqli_query($connection, $query)) {
                $_SESSION['username'] = $username;
                $_SESSION['success'] = "You are now logged in";
                header('Location: signin.html');
                exit();
            } else {
                array_push($errors, "Error inserting data: " . mysqli_error($connection));
            }
        }
    }

    // Display errors
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='error'>$error</div>";
        }
    }
}

// Close the database connection
if ($connection && $connection->ping()) {
    $connection->close();
}
?> 
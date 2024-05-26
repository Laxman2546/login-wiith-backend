<?php
session_start();
include('server.php');



$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_user'])) {
    // Check for connection errors
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
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

    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);

    // Validate form input
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    // Proceed if there are no errors
    if (count($errors) == 0) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($connection, $query);

        if (!$result) {
            // Output detailed error message
            die("Error running query: " . mysqli_error($connection));
        }

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['success'] = "You are now logged in";
                header('Location: signin.html'); // Redirect to a page after login
                exit();
            } else {
                array_push($errors, "Wrong username/password");
            }
        } else {
            array_push($errors, "User not found");
        }
    }

    // Display errors
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='error'>$error</div>";
        }
    }
}

// Close the connection at the very end
if ($connection && $connection->ping()) {
    $connection->close();
}
?>

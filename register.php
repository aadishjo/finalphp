<?php
$host = 'DESKTOP-83FBAU4';
$database = 'your_database_name'; 
$username = 'root'; 
$password = 'ajithkavi@123'; 

try {
    // Connect to the database using PDO
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to validate input data
    function validateInput($data) {
        return htmlspecialchars(trim($data));
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = validateInput($_POST['username']);
        $email = validateInput($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if the email is unique
        $checkEmailQuery = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // Email is unique, proceed with user registration
            $insertUserQuery = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($insertUserQuery);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                // Upload profile image if provided
                if (!empty($_FILES['profile_image']['name'])) {
                    $uploadDirectory = 'uploads/';
                    $uploadFile = $uploadDirectory . basename($_FILES['profile_image']['name']);
                    move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile);
                    // Update the user's profile image path in the database
                    $updateImageQuery = "UPDATE users SET profile_image = :profile_image WHERE email = :email";
                    $stmt = $conn->prepare($updateImageQuery);
                    $stmt->bindParam(':profile_image', $uploadFile);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                }

                echo "User registered successfully!";
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "Email already exists. Choose a different email.";
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} finally {
    // Close the database connection
    $conn = null;
}
?>

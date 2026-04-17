<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['designer_id'])) {
    header("Location: 202project1.html");
    exit();
}

$designerID = $_SESSION['designer_id'];
$designerName = $_SESSION['designer_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $clientFirst = trim($_POST['clientFirst']);
    $clientLast = trim($_POST['clientLast']);
    $clientID = trim($_POST['clientID']);

    if ($clientFirst === "" || $clientLast === "" || $clientID === "") {
        echo "<script>
                alert('All fields are required.');
                window.location.href='create_client.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z]+$/', $clientFirst)) {
        echo "<script>
                alert('Client first name must contain letters only.');
                window.location.href='create_client.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z]+$/', $clientLast)) {
        echo "<script>
                alert('Client last name must contain letters only.');
                window.location.href='create_client.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{3}$/', $clientID)) {
        echo "<script>
                alert('Client ID must be exactly 3 digits.');
                window.location.href='create_client.php';
              </script>";
        exit();
    }

    $checkSQL = "SELECT client_id FROM clients WHERE client_id = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("s", $clientID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>
                alert('An account already exists for this client.');
                window.location.href='create_client.php';
              </script>";
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    $insertClientSQL = "INSERT INTO clients (client_id, first_name, last_name) VALUES (?, ?, ?)";
    $insertClientStmt = $conn->prepare($insertClientSQL);
    $insertClientStmt->bind_param("sss", $clientID, $clientFirst, $clientLast);

    if ($insertClientStmt->execute()) {
        $linkSQL = "INSERT INTO designer_clients (designer_id, client_id) VALUES (?, ?)";
        $linkStmt = $conn->prepare($linkSQL);
        $linkStmt->bind_param("ss", $designerID, $clientID);
        $linkStmt->execute();
        $linkStmt->close();

        echo "<script>
                alert('New client account created.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "&clientFirst=" . urlencode($clientFirst) . "&clientLast=" . urlencode($clientLast) . "';
              </script>";
    } else {
        echo "<script>
                alert('There was an error creating the client account.');
                window.location.href='create_client.php';
              </script>";
    }

    $insertClientStmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Luxe Living - Create A New Client Account</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url("https://images.unsplash.com/photo-1618220179428-22790b461013") no-repeat center center fixed;
            background-size: cover;
        }

        .page-wrapper {
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.80);
            padding: 20px;
        }

        .nav-bar {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            background: rgba(255,255,255,0.95);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .nav-bar a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        .content-box {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.25);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .welcome {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .return-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="nav-bar">
            <a href="search_account.php">Search A Designer's Accounts</a>
            <a href="book_meeting.php">Book A Client's Design Project</a>
            <a href="cancel_meeting.php">Cancel A Client's Design Project</a>
            <a href="request_specs.php">Design Services Specs</a>
            <a href="additional_specs.php">Request Additional Design Specs</a>
            <a href="create_client.php">Create A New Client Account</a>
        </div>

        <div class="content-box">
            <h1>Create A New Client Account</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <form action="create_client.php" method="post">
                <input type="text" name="clientFirst" placeholder="Client First Name">
                <input type="text" name="clientLast" placeholder="Client Last Name">
                <input type="text" name="clientID" placeholder="Client ID (3 digits)">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="search_account.php">Return to Designer Account</a>
        </div>
    </div>
</body>
</html>
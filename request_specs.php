<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['designer_id'])) {
    header("Location: index.html");
    exit();
}

$designerID = $_SESSION['designer_id'];
$designerName = $_SESSION['designer_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $clientID = trim($_POST['clientID']);
    $designType = trim($_POST['designType']);
    $cost = trim($_POST['cost']);

    if ($clientID === "" || $designType === "" || $cost === "") {
        echo "<script>
                alert('All fields are required.');
                window.location.href='request_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{3}$/', $clientID)) {
        echo "<script>
                alert('Client ID must be exactly 3 digits.');
                window.location.href='request_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z0-9 ]+$/', $designType)) {
        echo "<script>
                alert('Design type must contain letters and numbers only.');
                window.location.href='request_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $cost)) {
        echo "<script>
                alert('Cost must be a valid number.');
                window.location.href='request_specs.php';
              </script>";
        exit();
    }

    $meetingSQL = "SELECT meeting_id FROM meetings WHERE client_id = ? AND designer_id = ?";
    $meetingStmt = $conn->prepare($meetingSQL);
    $meetingStmt->bind_param("ss", $clientID, $designerID);
    $meetingStmt->execute();
    $meetingStmt->store_result();

    if ($meetingStmt->num_rows === 0) {
        echo "<script>
                alert('Meeting information cannot be found. Recheck the information. You need to make sure the client has booked an event.');
                window.location.href='request_specs.php';
              </script>";
        $meetingStmt->close();
        $conn->close();
        exit();
    }
    $meetingStmt->close();

    $specCheckSQL = "SELECT spec_id FROM design_specs WHERE client_id = ?";
    $specCheckStmt = $conn->prepare($specCheckSQL);
    $specCheckStmt->bind_param("s", $clientID);
    $specCheckStmt->execute();
    $specCheckStmt->store_result();

    if ($specCheckStmt->num_rows > 0) {
        echo "<script>
                if (confirm('You already have design Project Specs. Do you want to update the specs?')) {
                    window.location.href='additional_specs.php?clientID=" . urlencode($clientID) . "';
                } else {
                    window.location.href='request_specs.php';
                }
              </script>";
        $specCheckStmt->close();
        $conn->close();
        exit();
    }
    $specCheckStmt->close();

    echo "<script>
            if (confirm('You are about to REQUEST Design Specs for your Client. Are you sure you want to do so?')) {
                window.location.href='request_specs.php?confirmSpecs=1&clientID=" . urlencode($clientID) . "&designType=" . urlencode($designType) . "&cost=" . urlencode($cost) . "';
            } else {
                window.location.href='request_specs.php';
            }
          </script>";

    $conn->close();
    exit();
}

if (isset($_GET['confirmSpecs']) && $_GET['confirmSpecs'] == 1) {
    $clientID = trim($_GET['clientID']);
    $designType = trim($_GET['designType']);
    $cost = trim($_GET['cost']);

    $insertSQL = "INSERT INTO design_specs (client_id, design_type, cost) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("ssd", $clientID, $designType, $cost);

    if ($insertStmt->execute()) {
        echo "<script>
                alert('Design Project Specs were added.');
                window.location.href='request_specs.php';
              </script>";
    } else {
        echo "<script>
                alert('There was an error adding the design specs.');
                window.location.href='request_specs.php';
              </script>";
    }

    $insertStmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Luxe Living - Design Services Specs</title>
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
            <h1>Design Services Specs</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <form action="request_specs.php" method="post">
                <input type="text" name="clientID" placeholder="Client ID (3 digits)">
                <input type="text" name="designType" placeholder="Type of Design Services Requested">
                <input type="text" name="cost" placeholder="Cost of Design Specs">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="search_account.php">Return to Designer Account</a>
        </div>
    </div>
</body>
</html>
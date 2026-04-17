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
    $meetingID = trim($_POST['meetingID']);

    if ($meetingID === "") {
        echo "<script>
                alert('Meeting ID is required.');
                window.location.href='cancel_meeting.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d+$/', $meetingID)) {
        echo "<script>
                alert('Meeting ID must be numeric.');
                window.location.href='cancel_meeting.php';
              </script>";
        exit();
    }

    $checkSQL = "SELECT meeting_id, client_id 
                 FROM meetings 
                 WHERE meeting_id = ? AND designer_id = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("is", $meetingID, $designerID);
    $checkStmt->execute();
    $checkStmt->store_result();
    $checkStmt->bind_result($foundMeetingID, $clientID);

    if ($checkStmt->num_rows === 0) {
        echo "<script>
                alert('Meeting ID does not exist. Please check the information and try again.');
                window.location.href='cancel_meeting.php';
              </script>";
        $checkStmt->close();
        $conn->close();
        exit();
    }

    $checkStmt->fetch();
    $checkStmt->close();

    echo "<script>
            if (confirm('Do you want to cancel this design meeting?')) {
                window.location.href='cancel_meeting.php?confirmCancel=1&meetingID=" . urlencode($meetingID) . "&clientID=" . urlencode($clientID) . "';
            } else {
                window.location.href='cancel_meeting.php';
            }
          </script>";

    $conn->close();
    exit();
}

if (isset($_GET['confirmCancel']) && $_GET['confirmCancel'] == 1) {
    $meetingID = intval($_GET['meetingID']);
    $clientID = trim($_GET['clientID']);

    $deleteSQL = "DELETE FROM meetings WHERE meeting_id = ? AND designer_id = ?";
    $deleteStmt = $conn->prepare($deleteSQL);
    $deleteStmt->bind_param("is", $meetingID, $designerID);

    if ($deleteStmt->execute()) {
        echo "<script>
                alert('Meeting cancelled for Designer ID: " . $designerID . ", Client ID: " . $clientID . ", Meeting ID: " . $meetingID . "');
                window.location.href='cancel_meeting.php';
              </script>";
    } else {
        echo "<script>
                alert('There was an error cancelling the meeting.');
                window.location.href='cancel_meeting.php';
              </script>";
    }

    $deleteStmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Luxe Living - Cancel A Design Meeting</title>
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
            <h1>Cancel A Design Meeting</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <form action="cancel_meeting.php" method="post">
                <input type="text" name="meetingID" placeholder="Meeting ID">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="search_account.php">Return to Designer Account</a>
        </div>
    </div>
</body>
</html>
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
    $clientFirst = trim($_POST['clientFirst']);
    $clientLast = trim($_POST['clientLast']);
    $clientID = trim($_POST['clientID']);
    $meetingDate = trim($_POST['meetingDate']);
    $meetingTime = trim($_POST['meetingTime']);

    if ($clientFirst === "" || $clientLast === "" || $clientID === "" || $meetingDate === "" || $meetingTime === "") {
        echo "<script>
                alert('All fields are required.');
                window.location.href='book_meeting.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z]+$/', $clientFirst)) {
        echo "<script>
                alert('Client first name must contain letters only.');
                window.location.href='book_meeting.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z]+$/', $clientLast)) {
        echo "<script>
                alert('Client last name must contain letters only.');
                window.location.href='book_meeting.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{3}$/', $clientID)) {
        echo "<script>
                alert('Client ID must be exactly 3 digits.');
                window.location.href='book_meeting.php';
              </script>";
        exit();
    }

    $verifyClientSQL = "SELECT client_id FROM clients WHERE first_name = ? AND last_name = ? AND client_id = ?";
    $verifyStmt = $conn->prepare($verifyClientSQL);
    $verifyStmt->bind_param("sss", $clientFirst, $clientLast, $clientID);
    $verifyStmt->execute();
    $verifyStmt->store_result();

    if ($verifyStmt->num_rows === 0) {
        echo "<script>
                if (confirm('Client does not exist. Press OK to re-enter the data or Cancel to create a new client account.')) {
                    window.location.href='book_meeting.php';
                } else {
                    window.location.href='create_client.php';
                }
              </script>";
        $verifyStmt->close();
        $conn->close();
        exit();
    }
    $verifyStmt->close();

    $meetingID = rand(1000, 9999);

    $checkMeetingSQL = "SELECT meeting_id FROM meetings WHERE meeting_id = ?";
    $checkStmt = $conn->prepare($checkMeetingSQL);

    do {
        $checkStmt->bind_param("i", $meetingID);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $meetingID = rand(1000, 9999);
        } else {
            break;
        }
    } while (true);

    $checkStmt->close();

    echo "<script>
            if (confirm('You are about to book a design meeting. Do you want to continue?')) {
                window.location.href='book_meeting.php?confirmBooking=1&clientFirst=" . urlencode($clientFirst) . "&clientLast=" . urlencode($clientLast) . "&clientID=" . urlencode($clientID) . "&meetingDate=" . urlencode($meetingDate) . "&meetingTime=" . urlencode($meetingTime) . "&meetingID=" . urlencode($meetingID) . "';
            } else {
                window.location.href='book_meeting.php';
            }
          </script>";

    $conn->close();
    exit();
}

if (isset($_GET['confirmBooking']) && $_GET['confirmBooking'] == 1) {
    $clientFirst = trim($_GET['clientFirst']);
    $clientLast = trim($_GET['clientLast']);
    $clientID = trim($_GET['clientID']);
    $meetingDate = trim($_GET['meetingDate']);
    $meetingTime = trim($_GET['meetingTime']);
    $meetingID = intval($_GET['meetingID']);

    $insertSQL = "INSERT INTO meetings (meeting_id, designer_id, client_id, meeting_date, meeting_time)
                  VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("issss", $meetingID, $designerID, $clientID, $meetingDate, $meetingTime);

    if ($insertStmt->execute()) {
        echo "<script>
                alert('Design meeting booked. Meeting ID: " . $meetingID . "');
                window.location.href='book_meeting.php';
              </script>";
    } else {
        echo "<script>
                alert('There was an error booking the meeting.');
                window.location.href='book_meeting.php';
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
    <title>Luxe Living - Book A Design Meeting</title>
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
            <h1>Book A Design Meeting</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <form action="book_meeting.php" method="post">
                <input type="text" name="clientFirst" placeholder="Client First Name">
                <input type="text" name="clientLast" placeholder="Client Last Name">
                <input type="text" name="clientID" placeholder="Client ID (3 digits)">
                <input type="date" name="meetingDate">
                <input type="time" name="meetingTime">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="search_account.php">Return to Designer Account</a>
        </div>
    </div>
</body>
</html>
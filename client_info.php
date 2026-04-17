<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['designer_id'])) {
    header("Location: 202project1.html");
    exit();
}

$designerName = $_SESSION['designer_name'];

$clientID = isset($_GET['clientID']) ? trim($_GET['clientID']) : "";
$clientFirst = isset($_GET['clientFirst']) ? trim($_GET['clientFirst']) : "";
$clientLast = isset($_GET['clientLast']) ? trim($_GET['clientLast']) : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $clientID = trim($_POST['clientID']);
    $streetNumber = trim($_POST['streetNumber']);
    $streetName = trim($_POST['streetName']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipCode = trim($_POST['zipCode']);
    $phone = trim($_POST['phone']);

    if ($clientID === "" || $streetNumber === "" || $streetName === "" || $city === "" || $state === "" || $zipCode === "" || $phone === "") {
        echo "<script>
                alert('All fields are required.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^\d+$/', $streetNumber)) {
        echo "<script>
                alert('Street number must be numeric.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z0-9 ]+$/', $streetName)) {
        echo "<script>
                alert('Street name contains invalid characters.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z ]+$/', $city)) {
        echo "<script>
                alert('City must contain letters only.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z]{2}$/', $state)) {
        echo "<script>
                alert('State must be 2 letters.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{5}$/', $zipCode)) {
        echo "<script>
                alert('Zip code must be 5 digits.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
        echo "<script>
                alert('Phone must be in format 123-456-7890.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        exit();
    }

    $checkSQL = "SELECT client_id FROM client_personal_info WHERE client_id = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("s", $clientID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>
                alert('A personal information record already exists for this client.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
              </script>";
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    echo "<script>
            if (confirm('Do you want to create this client information record?')) {
                window.location.href='client_info.php?confirmInfo=1&clientID=" . urlencode($clientID) . "&streetNumber=" . urlencode($streetNumber) . "&streetName=" . urlencode($streetName) . "&city=" . urlencode($city) . "&state=" . urlencode($state) . "&zipCode=" . urlencode($zipCode) . "&phone=" . urlencode($phone) . "';
            } else {
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "&clientFirst=" . urlencode($clientFirst) . "&clientLast=" . urlencode($clientLast) . "';
            }
          </script>";

    $conn->close();
    exit();
}

if (isset($_GET['confirmInfo']) && $_GET['confirmInfo'] == 1) {
    $clientID = trim($_GET['clientID']);
    $streetNumber = trim($_GET['streetNumber']);
    $streetName = trim($_GET['streetName']);
    $city = trim($_GET['city']);
    $state = trim($_GET['state']);
    $zipCode = trim($_GET['zipCode']);
    $phone = trim($_GET['phone']);

    $insertSQL = "INSERT INTO client_personal_info
                  (client_id, street_number, street_name, city, state, zip_code, phone)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("sssssss", $clientID, $streetNumber, $streetName, $city, $state, $zipCode, $phone);

    if ($insertStmt->execute()) {
        echo "<script>
                alert('Client information record created.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "&clientFirst=" . urlencode($clientFirst) . "&clientLast=" . urlencode($clientLast) . "';
              </script>";
    } else {
        echo "<script>
                alert('There was an error creating the client information record.');
                window.location.href='client_info.php?clientID=" . urlencode($clientID) . "';
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
    <title>Luxe Living - Client Personal Information</title>
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
            margin-bottom: 10px;
        }

        .client-line {
            text-align: center;
            margin-bottom: 25px;
            font-size: 16px;
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
            <h1>Client Personal Information</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>
            <div class="client-line">
                Client ID: <?php echo htmlspecialchars($clientID); ?>
                <?php if ($clientFirst !== "" || $clientLast !== "") { ?>
                    - <?php echo htmlspecialchars($clientFirst . " " . $clientLast); ?>
                <?php } ?>
            </div>

            <form action="client_info.php" method="post">
                <input type="hidden" name="clientID" value="<?php echo htmlspecialchars($clientID); ?>">
                <input type="text" name="streetNumber" placeholder="Street Number">
                <input type="text" name="streetName" placeholder="Street Name">
                <input type="text" name="city" placeholder="City">
                <input type="text" name="state" placeholder="State (2 letters)">
                <input type="text" name="zipCode" placeholder="Zip Code (5 digits)">
                <input type="text" name="phone" placeholder="Phone (123-456-7890)">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="create_client.php">Return to Create Client</a>
        </div>
    </div>
</body>
</html>
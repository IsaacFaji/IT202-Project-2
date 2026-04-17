<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['designer_id'])) {
    header("Location: index.html");
    exit();
}

$designerID = $_SESSION['designer_id'];
$designerName = $_SESSION['designer_name'];

$prefillClientID = "";
if (isset($_GET['clientID'])) {
    $prefillClientID = trim($_GET['clientID']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $clientID = trim($_POST['clientID']);
    $additionalSpec = trim($_POST['additionalSpec']);
    $cost = trim($_POST['cost']);

    if ($clientID === "" || $additionalSpec === "" || $cost === "") {
        echo "<script>
                alert('All fields are required.');
                window.location.href='additional_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d{3}$/', $clientID)) {
        echo "<script>
                alert('Client ID must be exactly 3 digits.');
                window.location.href='additional_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^[A-Za-z0-9 ]+$/', $additionalSpec)) {
        echo "<script>
                alert('Additional design specs must contain letters and numbers only.');
                window.location.href='additional_specs.php';
              </script>";
        exit();
    }

    if (!preg_match('/^\d+(\.\d{1,2})?$/', $cost)) {
        echo "<script>
                alert('Cost must be a valid number.');
                window.location.href='additional_specs.php';
              </script>";
        exit();
    }

    $specCheckSQL = "SELECT spec_id FROM design_specs WHERE client_id = ?";
    $specCheckStmt = $conn->prepare($specCheckSQL);
    $specCheckStmt->bind_param("s", $clientID);
    $specCheckStmt->execute();
    $specCheckStmt->store_result();

    if ($specCheckStmt->num_rows === 0) {
        echo "<script>
                alert('Design specs do not exist for this client. Please check the information and try again.');
                window.location.href='additional_specs.php';
              </script>";
        $specCheckStmt->close();
        $conn->close();
        exit();
    }
    $specCheckStmt->close();

    echo "<script>
            if (confirm('Do you want to request these additional design specs?')) {
                window.location.href='additional_specs.php?confirmAdditional=1&clientID=" . urlencode($clientID) . "&additionalSpec=" . urlencode($additionalSpec) . "&cost=" . urlencode($cost) . "';
            } else {
                window.location.href='additional_specs.php';
            }
          </script>";

    $conn->close();
    exit();
}

if (isset($_GET['confirmAdditional']) && $_GET['confirmAdditional'] == 1) {
    $clientID = trim($_GET['clientID']);
    $additionalSpec = trim($_GET['additionalSpec']);
    $cost = trim($_GET['cost']);

    $insertSQL = "INSERT INTO additional_design_specs (client_id, additional_spec, cost)
                  VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSQL);
    $insertStmt->bind_param("ssd", $clientID, $additionalSpec, $cost);

    if ($insertStmt->execute()) {
        echo "<script>
                alert('Additional design specs were added.');
                window.location.href='additional_specs.php';
              </script>";
    } else {
        echo "<script>
                alert('There was an error adding the additional design specs.');
                window.location.href='additional_specs.php';
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
    <title>Luxe Living - Additional Design Specs</title>
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
            <h1>Request Additional Design Specs</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <form action="additional_specs.php" method="post">
                <input type="text" name="clientID" placeholder="Client ID (3 digits)" value="<?php echo htmlspecialchars($prefillClientID); ?>">
                <input type="text" name="additionalSpec" placeholder="Additional Design Specs Requested">
                <input type="text" name="cost" placeholder="Cost of Additional Design Specs">
                <button type="submit">Submit</button>
            </form>

            <a class="return-link" href="search_account.php">Return to Designer Account</a>
        </div>
    </div>
</body>
</html>
<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['designer_id'])) {
    header("Location: index.html");
    exit();
}

$designerID = $_SESSION['designer_id'];
$designerName = $_SESSION['designer_name'];

$sql = "SELECT 
            d.first_name AS designer_first,
            d.last_name AS designer_last,
            d.designer_id,
            d.phone AS designer_phone,
            d.email AS designer_email,
            c.first_name AS client_first,
            c.last_name AS client_last,
            c.client_id,
            cp.street_number,
            cp.street_name,
            cp.city,
            cp.state,
            cp.zip_code,
            cp.phone AS client_phone,
            m.meeting_date,
            m.meeting_time,
            ds.design_type,
            ds.cost
        FROM designers d
        JOIN designer_clients dc ON d.designer_id = dc.designer_id
        JOIN clients c ON dc.client_id = c.client_id
        LEFT JOIN client_personal_info cp ON c.client_id = cp.client_id
        LEFT JOIN meetings m 
            ON c.client_id = m.client_id 
            AND d.designer_id = m.designer_id
        LEFT JOIN design_specs ds ON c.client_id = ds.client_id
        WHERE d.designer_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $designerID);
$stmt->execute();

$stmt->bind_result(
    $designer_first,
    $designer_last,
    $designer_id,
    $designer_phone,
    $designer_email,
    $client_first,
    $client_last,
    $client_id,
    $street_number,
    $street_name,
    $city,
    $state,
    $zip_code,
    $client_phone,
    $meeting_date,
    $meeting_time,
    $design_type,
    $cost
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Luxe Living - Search Designer Account</title>
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
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.25);
            overflow-x: auto;
        }

        h1 {
            text-align: center;
        }

        .welcome {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        .logout-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            font-weight: bold;
            color: black;
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
            <h1>Luxe Living Designer Account</h1>
            <div class="welcome">Welcome <?php echo htmlspecialchars($designerName); ?></div>

            <table>
                <tr>
                    <th>Designer First Name</th>
                    <th>Designer Last Name</th>
                    <th>Designer ID</th>
                    <th>Designer Phone</th>
                    <th>Designer Email</th>
                    <th>Client First Name</th>
                    <th>Client Last Name</th>
                    <th>Client ID</th>
                    <th>Client Street Address</th>
                    <th>Client City</th>
                    <th>Client State</th>
                    <th>Client Zip Code</th>
                    <th>Client Phone</th>
                    <th>Meeting Date</th>
                    <th>Meeting Time</th>
                    <th>Design Specs</th>
                    <th>Cost of Design Project</th>
                </tr>

                <?php
                $hasRows = false;

                while ($stmt->fetch()) {
                    $hasRows = true;
                    $streetAddress = trim($street_number . " " . $street_name);

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($designer_first) . "</td>";
                    echo "<td>" . htmlspecialchars($designer_last) . "</td>";
                    echo "<td>" . htmlspecialchars($designer_id) . "</td>";
                    echo "<td>" . htmlspecialchars($designer_phone) . "</td>";
                    echo "<td>" . htmlspecialchars($designer_email) . "</td>";
                    echo "<td>" . htmlspecialchars($client_first) . "</td>";
                    echo "<td>" . htmlspecialchars($client_last) . "</td>";
                    echo "<td>" . htmlspecialchars($client_id) . "</td>";
                    echo "<td>" . htmlspecialchars($streetAddress) . "</td>";
                    echo "<td>" . htmlspecialchars($city) . "</td>";
                    echo "<td>" . htmlspecialchars($state) . "</td>";
                    echo "<td>" . htmlspecialchars($zip_code) . "</td>";
                    echo "<td>" . htmlspecialchars($client_phone) . "</td>";
                    echo "<td>" . htmlspecialchars($meeting_date) . "</td>";
                    echo "<td>" . htmlspecialchars($meeting_time) . "</td>";
                    echo "<td>" . htmlspecialchars($design_type) . "</td>";
                    echo "<td>$" . htmlspecialchars($cost) . "</td>";
                    echo "</tr>";
                }

                if (!$hasRows) {
                    echo "<tr><td colspan='17'>No records found for this designer.</td></tr>";
                }

                $stmt->close();
                $conn->close();
                ?>
            </table>

            <a class="logout-link" href="index.html">Return to Login</a>
        </div>
    </div>
</body>
</html>
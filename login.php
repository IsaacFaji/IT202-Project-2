<?php
session_start();
include("db_connect.php");

$firstName   = $_POST['firstName'];
$lastName    = $_POST['lastName'];
$password    = $_POST['password'];
$designerID  = $_POST['designerID'];
$phone       = $_POST['phone'];
$email       = $_POST['email'];
$confirm     = isset($_POST['emailConfirm']);
$transaction = $_POST['transaction'];

$sql = "SELECT * FROM designers
        WHERE first_name='$firstName'
        AND last_name='$lastName'
        AND password='$password'
        AND designer_id='$designerID'
        AND phone='$phone'";

if ($confirm) {
    $sql .= " AND email='$email'";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $_SESSION['designer_id'] = $designerID;
    $_SESSION['designer_name'] = $firstName . " " . $lastName;

    switch ($transaction) {

        case "Search A Designer's Accounts":
            header("Location: search_account.php");
            break;

        case "Book A Client's Design Project":
            header("Location: book_meeting.php");
            break;

        case "Cancel A Client's Design Project":
            header("Location: cancel_meeting.php");
            break;

        case "Design Services Specs":
            header("Location: request_specs.php");
            break;

        case "Request Additional Design Specs":
            header("Location: additional_specs.php");
            break;

        case "Create A New Client Account":
            header("Location: create_client.php");
            break;

        default:
            header("Location: index.html");
    }

} else {

    echo "<script>
            alert('Designer account not found.');
            window.location.href='index.html';
          </script>";
}

$conn->close();
?>
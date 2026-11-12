
<?php
// Adjust paths for pages inside the helpdesk folder
$base = (strpos($_SERVER['SCRIPT_NAME'], '/helpdesk/') !== false)
    ? '../'
    : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Small Business IT</title>

    <link rel="stylesheet"
          href="<?php echo $base; ?>css/style.css">
</head>
<body>

<header>
    <h1>Small Business IT</h1>
    <p>Network Solutions & Cybersecurity</p>
</header>

<nav>
    <a href="<?php echo $base; ?>index.php">Home</a>
    <a href="<?php echo $base; ?>about.php">About</a>
    <a href="<?php echo $base; ?>services.php">Services</a>
    <a href="<?php echo $base; ?>contact.php">Contact</a>

    <a href="<?php echo $base; ?>helpdesk/support.php">
        IT Support
    </a>

    <a href="<?php echo $base; ?>helpdesk/submit_ticket.php">
        Submit Ticket
    </a>

    <a href="<?php echo $base; ?>helpdesk/my_tickets.php">
        My Tickets
    </a>
</nav>

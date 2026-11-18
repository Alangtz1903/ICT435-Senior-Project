
<?php
// Start a session for employee and administrator accounts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL for the ICT 435 Senior Project
$base = "/senior-project/";

// Check whether the user is logged in
$loggedIn = isset($_SESSION['user_id']);
$isAdmin = $loggedIn &&
    ($_SESSION['role'] ?? '') === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Small Business IT</title>

    <!-- Website CSS: updated red theme -->
    <link rel="stylesheet"
          href="<?php echo $base; ?>css/style.css?v=5">
</head>

<body>

<!-- Website Header -->
<header>
    <h1>Small Business IT</h1>
    <p>Network Solutions & Cybersecurity</p>
</header>

<!-- Navigation Bar -->
<nav>
    <a href="<?php echo $base; ?>index.php">
        Home
    </a>

    <a href="<?php echo $base; ?>about.php">
        About
    </a>

    <a href="<?php echo $base; ?>services.php">
        Services
    </a>

    <a href="<?php echo $base; ?>contact.php">
        Contact
    </a>

    <a href="<?php echo $base; ?>helpdesk/support.php">
        IT Support
    </a>

    <?php if ($loggedIn): ?>

        <a href="<?php echo $base; ?>helpdesk/submit_ticket.php">
            Submit Ticket
        </a>

        <a href="<?php echo $base; ?>helpdesk/my_tickets.php">
            My Tickets
        </a>

        <?php if ($isAdmin): ?>
            <a href="<?php echo $base; ?>helpdesk/admin.php">
                Admin Dashboard
            </a>
        <?php endif; ?>

        <a href="<?php echo $base; ?>helpdesk/logout.php">
            Logout
        </a>

    <?php else: ?>

        <a href="<?php echo $base; ?>helpdesk/login.php">
            Login
        </a>

        <a href="<?php echo $base; ?>helpdesk/register.php">
            Register
        </a>

    <?php endif; ?>
</nav>

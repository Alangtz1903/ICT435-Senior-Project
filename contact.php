
<?php
$name = "";
$email = "";
$message = "";
$result = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name == "" || $email == "" || $message == "") {
        $result = "Please complete all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result = "Please enter a valid email address.";
    } else {
        $result = "Thank you! Your form was validated successfully.";
        $name = "";
        $email = "";
        $message = "";
    }
}

function safe($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<?php include 'includes/header.php'; ?>

<main>
    <h2>Contact Us</h2>

    <p>
        Have a question about our IT services?
        Fill out the form below.
    </p>

    <?php if ($result != ""): ?>
        <p class="message">
            <?php echo safe($result); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="contact.php">

        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name"
               value="<?php echo safe($name); ?>"
               required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               value="<?php echo safe($email); ?>"
               required>

        <label for="message">Message:</label>
        <textarea id="message" name="message"
                  rows="5" required><?php
            echo safe($message);
        ?></textarea>

        <button type="submit">Submit</button>
    </form>
</main>

<?php include 'includes/footer.php'; ?>

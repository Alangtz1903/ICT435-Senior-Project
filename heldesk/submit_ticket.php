
<?php
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $category = $_POST["category"] ?? "";
    $priority = $_POST["priority"] ?? "";
    $description = trim($_POST["description"] ?? "");

    if (
        $name === "" ||
        $email === "" ||
        $category === "" ||
        $priority === "" ||
        $description === ""
    ) {
        $error = "Please complete all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } else {
        $error = "Form validated. Database saving is not connected yet.";
    }
}

include '../includes/header.php';
?>

<main>
    <h2>Submit an IT Support Ticket</h2>

    <p>Use this form to report an IT issue.</p>

    <?php if ($error !== ""): ?>
        <p class="message">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="submit_ticket.php">

        <label>Full Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Issue Category:</label>
        <select name="category" required>
            <option value="">Select Category</option>
            <option value="Hardware">Hardware</option>
            <option value="Software">Software</option>
            <option value="Network">Network</option>
            <option value="Account">Account</option>
        </select>

        <label>Priority:</label>
        <select name="priority" required>
            <option value="">Select Priority</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
        </select>

        <label>Describe the Issue:</label>
        <textarea name="description"
                  rows="5" required></textarea>

        <button type="submit">Submit Ticket</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>

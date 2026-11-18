
<?php
session_start();
require_once '../includes/db_connect.php';

$error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $validToken = isset($_POST['csrf_token'])
        && hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        );

    if (!$validToken) {
        $error = "Invalid request. Refresh and try again.";
    } elseif (
        $name === '' ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 255
    ) {
        $error = "Please enter a valid name and email.";
    } elseif (strlen($password) < 12) {
        $error = "Password must be at least 12 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users
             (name, email, password_hash, role)
             VALUES (?, ?, ?, 'employee')"
        );

        $stmt->bind_param("sss", $name, $email, $hash);

        try {
            $stmt->execute();
            $stmt->close();

            header("Location: login.php?registered=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "An account with that email already exists.";
            } else {
                error_log($e->getMessage());
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<main>
    <h2>Create Employee Account</h2>

    <p>Register to submit and track IT support tickets.</p>

    <?php if ($error): ?>
        <p class="message">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars(
                   $_SESSION['csrf_token'],
                   ENT_QUOTES, 'UTF-8'
               ); ?>">

        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name"
               maxlength="100" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               maxlength="255" required>

        <label for="password">Password:</label>
        <input type="password" id="password"
               name="password" minlength="12"
               autocomplete="new-password" required>

        <button type="submit">Create Account</button>
    </form>

    <p>
        Already have an account?
        <a href="login.php">Log In</a>
    </p>
</main>

<?php include '../includes/footer.php'; ?>

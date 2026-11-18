
<?php
session_start();
require_once '../includes/db_connect.php';

$error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $password === ''
    ) {
        $error = "Invalid email or password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, password_hash, role
             FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (
            $user &&
            password_verify($password, $user['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: my_tickets.php");
            }
            exit;
        }

        $error = "Invalid email or password.";
    }
}

include '../includes/header.php';
?>

<main>
    <h2>IT Help Desk Login</h2>

    <?php if (isset($_GET['registered'])): ?>
        <p class="message">
            Account created! Please log in.
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="message">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars(
                   $_SESSION['csrf_token'],
                   ENT_QUOTES, 'UTF-8'
               ); ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               autocomplete="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password"
               name="password"
               autocomplete="current-password" required>

        <button type="submit">Log In</button>
    </form>

    <p>
        Don't have an account?
        <a href="register.php">Create an Account</a>
    </p>
</main>

<?php include '../includes/footer.php'; ?>

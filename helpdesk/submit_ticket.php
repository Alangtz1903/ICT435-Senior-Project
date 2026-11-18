
<?php
session_start();

// Require employee login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db_connect.php';

$error = "";

$categories = ['Hardware', 'Software', 'Network', 'Account'];
$priorities = ['Low', 'Medium', 'High'];

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = (int) $_SESSION['user_id'];
    $category = $_POST['category'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // Validate request
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        $error = "Invalid request. Please try again.";

    } elseif (
        !in_array($category, $categories, true) ||
        !in_array($priority, $priorities, true) ||
        $description === '' ||
        strlen($description) > 5000
    ) {
        $error = "Please complete all fields correctly.";

    } else {

        // Insert ticket into database
        $stmt = $conn->prepare(
            "INSERT INTO tickets
             (user_id, category, priority, description, status)
             VALUES (?, ?, ?, ?, 'Open')"
        );

        $stmt->bind_param(
            "isss",
            $user_id,
            $category,
            $priority,
            $description
        );

        try {
            $stmt->execute();

            $ticket_id = $conn->insert_id;
            $stmt->close();

            // Rotate token after successful submission
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Redirect to prevent duplicate submissions
            header(
                "Location: submit_ticket.php?success=" . $ticket_id
            );
            exit;

        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = "Unable to save ticket. Please try again.";
        }
    }
}

// Check for successful submission
$successId = filter_input(
    INPUT_GET,
    'success',
    FILTER_VALIDATE_INT
);

// Confirm the ticket belongs to this user
$showSuccess = false;

if ($successId) {
    $stmt = $conn->prepare(
        "SELECT id FROM tickets
         WHERE id = ? AND user_id = ?"
    );

    $currentUser = (int) $_SESSION['user_id'];

    $stmt->bind_param("ii", $successId, $currentUser);
    $stmt->execute();

    $showSuccess = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

include '../includes/header.php';
?>

<main>

    <h2>Submit an IT Support Ticket</h2>

    <p>
        Use this form to report computer, network,
        software, or account-related issues.
    </p>

    <?php if ($error !== ""): ?>
        <p class="message">
            <?php echo htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="submit_ticket.php">

        <input type="hidden"
               name="csrf_token"
               value="<?php echo htmlspecialchars(
                   $_SESSION['csrf_token'],
                   ENT_QUOTES,
                   'UTF-8'
               ); ?>">

        <label for="category">Issue Category:</label>

        <select id="category" name="category" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $item): ?>
                <option value="<?php echo $item; ?>">
                    <?php echo $item; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="priority">Priority:</label>

        <select id="priority" name="priority" required>
            <option value="">Select Priority</option>
            <?php foreach ($priorities as $item): ?>
                <option value="<?php echo $item; ?>">
                    <?php echo $item; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="description">
            Describe the Issue:
        </label>

        <textarea
            id="description"
            name="description"
            rows="5"
            maxlength="5000"
            required
        ></textarea>

        <button type="submit">
            Submit Ticket
        </button>

    </form>

    <p>
        <a href="my_tickets.php">
            View My Tickets
        </a>
    </p>

</main>

<?php if ($showSuccess): ?>

<!-- Ticket submission notification -->
<div id="ticketPopup" class="ticket-popup-overlay">
    <div class="ticket-popup" role="alertdialog"
         aria-modal="true"
         aria-labelledby="popupTitle">

        <div class="popup-check">✓</div>

        <h2 id="popupTitle">Ticket Submitted!</h2>

        <p>
            Your IT support ticket was submitted
            successfully.
        </p>

        <p>
            <strong>
                Ticket #<?php echo (int)$successId; ?>
            </strong>
        </p>

        <p>
            Your ticket has been assigned an
            Open status.
        </p>

        <a href="my_tickets.php" class="btn">
            View My Tickets
        </a>

        <button type="button" onclick="closeTicketPopup()">
            Close
        </button>

    </div>
</div>

<script>
function closeTicketPopup() {
    document.getElementById('ticketPopup').remove();

    // Remove success parameter from browser URL
    history.replaceState(
        null,
        '',
        'submit_ticket.php'
    );
}
</script>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>

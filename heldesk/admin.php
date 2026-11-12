
<?php
session_start();

// Only administrators can access this page
if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    http_response_code(403);
    exit("Access denied. Administrator login required.");
}

require_once '../includes/db_connect.php';

$message = "";

// Update ticket status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        exit("Invalid request.");
    }

    $ticket_id = filter_input(
        INPUT_POST,
        'ticket_id',
        FILTER_VALIDATE_INT
    );

    $status = $_POST['status'] ?? '';

    $allowed = ['Open', 'In Progress', 'Resolved'];

    if ($ticket_id && in_array($status, $allowed, true)) {

        $stmt = $conn->prepare(
            "UPDATE tickets SET status = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $status, $ticket_id);
        $stmt->execute();
        $stmt->close();

        $message = "Ticket status updated successfully.";
    } else {
        $message = "Invalid ticket information.";
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$result = $conn->query(
    "SELECT id, name, email, category, priority,
            description, status, created_at
     FROM tickets
     ORDER BY created_at DESC"
);

include '../includes/header.php';
?>

<main>
    <h2>IT Help Desk Admin Dashboard</h2>

    <p>
        View and manage employee support requests.
    </p>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?php echo htmlspecialchars(
                $message, ENT_QUOTES, 'UTF-8'
            ); ?>
        </p>
    <?php endif; ?>

    <div class="table-container">
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>

            <?php while ($ticket = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        #<?php echo (int) $ticket['id']; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(
                            $ticket['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                        <p>
                            <?php echo htmlspecialchars(
                                $ticket['email'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </p>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(
                            $ticket['category'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(
                            $ticket['priority'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </td>
                    <td>
                        <?php echo nl2br(htmlspecialchars(
                            $ticket['description'],
                            ENT_QUOTES,
                            'UTF-8'
                        )); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars(
                            $ticket['status'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </td>
                    <td>
                        <form method="POST" action="admin.php">

                            <input type="hidden"
                                   name="csrf_token"
                                   value="<?php echo htmlspecialchars(
                                       $_SESSION['csrf_token'],
                                       ENT_QUOTES,
                                       'UTF-8'
                                   ); ?>">

                            <input type="hidden"
                                   name="ticket_id"
                                   value="<?php echo (int) $ticket['id']; ?>">

                            <select name="status">
                                <option value="Open"
                                    <?php if ($ticket['status'] === 'Open') echo 'selected'; ?>>
                                    Open
                                </option>
                                <option value="In Progress"
                                    <?php if ($ticket['status'] === 'In Progress') echo 'selected'; ?>>
                                    In Progress
                                </option>
                                <option value="Resolved"
                                    <?php if ($ticket['status'] === 'Resolved') echo 'selected'; ?>>
                                    Resolved
                                </option>
                            </select>

                            <button type="submit">
                                Update
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

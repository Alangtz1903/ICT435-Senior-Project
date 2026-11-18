
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

// Connect to the MySQL database
require_once '../includes/db_connect.php';

$message = "";

// Escape HTML output to prevent XSS
function safe($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Create a CSRF token for secure form submissions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle ticket status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        exit("Invalid request.");
    }

    // Get submitted ticket ID
    $ticket_id = filter_input(
        INPUT_POST,
        'ticket_id',
        FILTER_VALIDATE_INT
    );

    // Get new ticket status
    $status = $_POST['status'] ?? '';

    // Allowed ticket statuses
    $allowed = [
        'Open',
        'In Progress',
        'Resolved'
    ];

    // Validate input
    if (
        $ticket_id &&
        in_array($status, $allowed, true)
    ) {

        // Secure prepared statement
        $stmt = $conn->prepare(
            "UPDATE tickets
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $ticket_id
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "Ticket status updated successfully.";
        } else {
            $message = "No ticket changes were made.";
        }

        $stmt->close();

    } else {
        $message = "Invalid ticket information.";
    }
}

// Retrieve all tickets and their employee information
// Employee names and emails are stored in users,
// not in the tickets table.
$result = $conn->query(
    "SELECT
        tickets.id,
        users.name,
        users.email,
        tickets.category,
        tickets.priority,
        tickets.description,
        tickets.status,
        tickets.created_at
     FROM tickets
     INNER JOIN users
         ON tickets.user_id = users.id
     ORDER BY tickets.created_at DESC, tickets.id DESC"
);

// Count tickets by status
$counts = [
    'Open' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];

$countResult = $conn->query(
    "SELECT status, COUNT(*) AS total
     FROM tickets
     GROUP BY status"
);

while ($row = $countResult->fetch_assoc()) {
    if (array_key_exists($row['status'], $counts)) {
        $counts[$row['status']] = (int)$row['total'];
    }
}

include '../includes/header.php';
?>

<main>

    <h2>IT Help Desk Admin Dashboard</h2>

    <p>
        Welcome to the IT administration dashboard.
        Here you can review employee support requests,
        monitor ticket activity, and update ticket statuses.
    </p>

    <!-- Ticket Summary -->
    <h3>Ticket Overview</h3>

    <div class="service">
        <p>
            <strong>Open Tickets:</strong>
            <?php echo $counts['Open']; ?>
        </p>

        <p>
            <strong>In Progress:</strong>
            <?php echo $counts['In Progress']; ?>
        </p>

        <p>
            <strong>Resolved Tickets:</strong>
            <?php echo $counts['Resolved']; ?>
        </p>
    </div>

    <!-- Confirmation Messages -->
    <?php if ($message !== ""): ?>
        <p class="message">
            <?php echo safe($message); ?>
        </p>
    <?php endif; ?>

    <!-- Ticket Management -->
    <h3>Manage Support Tickets</h3>

    <p>
        Review the submitted IT support requests
        and update their status as needed.
    </p>

    <div class="table-container">

        <table class="ticket-table">

            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Employee</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Update Status</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($ticket = $result->fetch_assoc()): ?>

                    <?php
                    // Choose CSS class for status
                    $statusClass = match ($ticket['status']) {
                        'Open' => 'status-open',
                        'In Progress' => 'status-progress',
                        'Resolved' => 'status-resolved',
                        default => ''
                    };
                    ?>

                    <tr>

                        <!-- Ticket ID -->
                        <td>
                            #<?php echo (int)$ticket['id']; ?>
                        </td>

                        <!-- Employee Information -->
                        <td>
                            <strong>
                                <?php echo safe($ticket['name']); ?>
                            </strong>

                            <p>
                                <?php echo safe($ticket['email']); ?>
                            </p>
                        </td>

                        <!-- Category -->
                        <td>
                            <?php echo safe($ticket['category']); ?>
                        </td>

                        <!-- Priority -->
                        <td>
                            <?php echo safe($ticket['priority']); ?>
                        </td>

                        <!-- Description -->
                        <td>
                            <?php echo nl2br(
                                safe($ticket['description'])
                            ); ?>
                        </td>

                        <!-- Current Status -->
                        <td>
                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo safe($ticket['status']); ?>
                            </span>
                        </td>

                        <!-- Submission Date -->
                        <td>
                            <?php echo safe($ticket['created_at']); ?>
                        </td>

                        <!-- Status Update Form -->
                        <td>

                            <form method="POST" action="admin.php">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo safe(
                                        $_SESSION['csrf_token']
                                    ); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="ticket_id"
                                    value="<?php echo (int)$ticket['id']; ?>"
                                >

                                <label>
                                    Change Status:
                                </label>

                                <select name="status">

                                    <option value="Open"
                                        <?php
                                        if ($ticket['status'] === 'Open')
                                            echo 'selected';
                                        ?>>
                                        Open
                                    </option>

                                    <option value="In Progress"
                                        <?php
                                        if ($ticket['status'] === 'In Progress')
                                            echo 'selected';
                                        ?>>
                                        In Progress
                                    </option>

                                    <option value="Resolved"
                                        <?php
                                        if ($ticket['status'] === 'Resolved')
                                            echo 'selected';
                                        ?>>
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

            <?php else: ?>

                <tr>
                    <td colspan="8">
                        No support tickets have been submitted yet.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p>
        <a href="support.php" class="btn">
            Return to IT Support
        </a>
    </p>

</main>

<?php
$result->free();
$countResult->free();
include '../includes/footer.php';
?>

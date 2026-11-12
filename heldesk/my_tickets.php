
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Please log in to view your tickets.");
}

require_once '../includes/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, category, priority, description,
            status, created_at
     FROM tickets
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();

include '../includes/header.php';
?>

<main>
    <h2>My Support Tickets</h2>

    <p>
        View the status of your submitted IT requests.
    </p>

    <a href="submit_ticket.php" class="btn">
        Submit New Ticket
    </a>

    <div class="table-container">
        <table class="ticket-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($tickets->num_rows > 0): ?>
                <?php while ($ticket = $tickets->fetch_assoc()): ?>
                    <?php
                    $statusClass = match ($ticket['status']) {
                        'Open' => 'status-open',
                        'In Progress' => 'status-progress',
                        'Resolved' => 'status-resolved',
                        default => ''
                    };
                    ?>
                    <tr>
                        <td>
                            #<?php echo (int) $ticket['id']; ?>
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
                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars(
                                    $ticket['status'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(
                                $ticket['created_at'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        You have no support tickets.
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</main>

<?php
$stmt->close();
include '../includes/footer.php';
?>

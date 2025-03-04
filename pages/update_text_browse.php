<?php
include '../includes/config.php'; // Include DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['field'], $_POST['value'])) {
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    // Only allow updates to these specific fields
    if (!in_array($field, ['hero_title', 'hero_subtitle'])) {
        exit();
    }

    // Check if a row with id = 1 exists
    $check = $conn->query("SELECT id FROM browse WHERE id = 1");

    if ($check->num_rows > 0) {
        // Update if exists
        $stmt = $conn->prepare("UPDATE browse SET $field = ? WHERE id = 1");
    } else {
        // Insert if no row exists
        $stmt = $conn->prepare("INSERT INTO browse (id, $field) VALUES (1, ?)");
    }

    $stmt->bind_param("s", $value);
    $stmt->execute();
}

// Auto-refresh the page
echo "<script>window.location.href='browse.php';</script>";
exit();
?>

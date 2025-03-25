<?php
session_start(); // Start the session
include '../includes/config.php'; // Include your DB connection

// Get the search query from the request
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepare and execute the query to fetch matching skills with category names
    $sql = "
        SELECT sl.id, sl.skill_name, c.name AS category_name 
        FROM skill_list sl
        JOIN categories c ON sl.category_id = c.id
        WHERE sl.skill_name LIKE ? OR c.name LIKE ?
        ORDER BY sl.skill_name
    ";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm); // Bind the search term twice for both conditions
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the results as an associative array
    $skills = $result->fetch_all(MYSQLI_ASSOC);

    // Return the results as JSON
    echo json_encode($skills);

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([]); // Return an empty array if no query is provided
}
?>
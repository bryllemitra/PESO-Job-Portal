<?php
session_start(); // Start the session
include '../includes/config.php'; // Include your DB connection

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Get the search query from the request
    if (isset($_GET['query'])) {
        $query = $_GET['query'];

        // Fetch the user's already added skills
        $userSkillsSql = "
            SELECT skill_id FROM skills WHERE user_id = ?
        ";
        $stmt = $conn->prepare($userSkillsSql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $userSkillsResult = $stmt->get_result();

        $userSkills = [];
        while ($row = $userSkillsResult->fetch_assoc()) {
            $userSkills[] = $row['skill_id'];
        }

        // Prepare the base query
        $sql = "
            SELECT sl.id, sl.skill_name, c.name AS category_name 
            FROM skill_list sl
            JOIN categories c ON sl.category_id = c.id
            WHERE (sl.skill_name LIKE ? OR c.name LIKE ?)
        ";

        // Add NOT IN clause only if there are user skills
        if (!empty($userSkills)) {
            $sql .= " AND sl.id NOT IN (" . implode(",", array_fill(0, count($userSkills), "?")) . ")";
        }

        $sql .= " ORDER BY sl.skill_name";

        $stmt = $conn->prepare($sql);

        // Prepare the bindings
        $searchTerm = "%$query%";
        $bindings = [$searchTerm, $searchTerm];
        
        if (!empty($userSkills)) {
            foreach ($userSkills as $skillId) {
                $bindings[] = $skillId;
            }
        }

        // Create the parameter types string
        $paramTypes = str_repeat("s", 2); // For the two search terms
        
        if (!empty($userSkills)) {
            $paramTypes .= str_repeat("i", count($userSkills)); // For the skill IDs
        }

        $stmt->bind_param($paramTypes, ...$bindings);
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
}
?>
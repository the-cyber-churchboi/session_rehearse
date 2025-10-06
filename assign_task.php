<?php
// Include your config.php file which contains the database connection setup
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $taskDescription = $_POST["task"];
    $assigneeInfo = $_POST["assignee"];
    $profession = $assigneeInfo[0];
    $adminFullName = $assigneeInfo[1];
    $queryId = $_POST["query"];

    try {
        // Assume your PDO connection is established in the config.php file as $pdo

        // Insert the task information into the 'task' table
        $sql = "INSERT INTO task (consultant_id, task_title, task_description, query_identifier, user_id) 
                VALUES (
                    (SELECT unique_identifier FROM consultants WHERE full_name = :adminFullName AND profession = :profession),
                    :taskTitle,
                    :taskDescription,
                    :queryIdentifier,
                    (SELECT user_id FROM queries WHERE query_id = :queryId)
                )";

        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);

        // Bind the parameters
        $stmt->bindParam(':adminFullName', $adminFullName, PDO::PARAM_STR);
        $stmt->bindParam(':profession', $profession, PDO::PARAM_STR);
        $stmt->bindParam(':taskTitle', $taskDescription, PDO::PARAM_STR);
        $stmt->bindParam(':taskDescription', $taskDescription, PDO::PARAM_STR);
        $stmt->bindParam(':queryIdentifier', $queryId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Redirect back to the developer dashboard after task assignment
        header("Location: developer_dashboard.php");
        exit();

    } catch (PDOException $e) {
        // Handle any database connection or query errors
        echo "Error: " . $e->getMessage();
    }
} else {
    // Redirect to the developer dashboard if accessed directly without form submission
    header("Location: developer_dashboard.php");
    exit();
}
?>

<?php
// Include your PDO connection file[cite: 3]
require_once 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // We named the textarea 'feedback' in the HTML form
    $sitin_id = $_POST['sitin_id'] ?? null;
    $feedback_content = $_POST['feedback'] ?? null;

    if ($sitin_id && $feedback_content) {
        try {
            // Your table column is likely 'message' based on your error
            $sql = "INSERT INTO notifications (student_id, message) VALUES (:student_id, :message)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':student_id' => $sitin_id,
                ':message' => $feedback_content
            ]);

            // Redirect back to history with a success message
            header("Location: history.php?status=success");
            exit();
            
        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("Error: Missing feedback content or student ID.");
    }
}
?>
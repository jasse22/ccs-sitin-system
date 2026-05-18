<?php
require_once 'db.php'; 

$sitin_id = isset($_POST['sitin_id']) ? $_POST['sitin_id'] : null;
$feedback_text = isset($_POST['feedback']) ? $_POST['feedback'] : null;

if (!$sitin_id || !$feedback_text) {
    die("Error: Missing required form data.");
}

try {
    $lookup_stmt = $pdo->prepare("SELECT student_id FROM sit_in_history WHERE id = :sitin_id");
    $lookup_stmt->execute(['sitin_id' => $sitin_id]);
    $session = $lookup_stmt->fetch();

    if ($session) {
        $actual_student_id = $session['student_id']; 

        // Inserts feedback as a descriptive system message
        $insert_stmt = $pdo->prepare("INSERT INTO notifications (student_id, message, is_read, created_at) 
                                      VALUES (:student_id, :message, 0, NOW())");
        
        $insert_stmt->execute([
            'student_id' => $actual_student_id,
            'message'    => "Feedback: " . $feedback_text
        ]);

        header("Location: history.php?success=1");
        exit();

    } else {
        die("Error: No active lab record matches history ID: " . htmlspecialchars($sitin_id));
    }

} catch (PDOException $e) {
    die("Database Processing Error: " . $e->getMessage());
}
?>
<?php
include 'config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
        // delete from database
        $stmt = $conn->prepare("DELETE FROM student_materails WHERE material_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: teacher-materials.php?msg=deleted");
    }
}
?>
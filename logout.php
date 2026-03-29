<?php
session_start();
// Destroy all session data
session_unset();
session_destroy();
<<<<<<< HEAD
header("Location: ../index.php");
=======
header("Location: ./index.php");
>>>>>>> 874cf89 (Updated project)
?>
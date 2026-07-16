<?php
    include('../config/config.php'); // or the file where $mysqli is created

    if (!empty($_POST["Course_Code"]))
    {
        $id = $_POST['Course_Code'];
        $stmt = $mysqli->prepare("SELECT c_id, c_name FROM courses WHERE c_code = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {echo htmlentities($row['c_id']) . "|" . htmlentities($row['c_name']);}
        $stmt->close();
    }
?>
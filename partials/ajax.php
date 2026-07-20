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

    if(isset($_POST['course_id']))
    {
        $c_id = $_POST['course_id'];
        
        // Fetch units based on the selected course
        $stmt = $mysqli->prepare("SELECT u_id, u_name FROM units WHERE c_id = ?");
        $stmt->bind_param('i', $c_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo '<option value="">-- Choose a Unit --</option>';
        while($row = $result->fetch_assoc()){
            echo '<option value="'.$row['u_id'].'">'.$row['u_name'].'</option>';
        }
    }
?>

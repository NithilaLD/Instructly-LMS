<?php
  include('../config/config.php');
  include('../config/checklogin.php');
  userRoles(['admin', 'instructor', 'student']);

  if (!isset($_GET['m_id']) || !is_numeric($_GET['m_id'])) {
      header("Location: manage_unit_materials.php");
      exit();
  }

  $m_id = intval($_GET['m_id']);

  $stmt = $mysqli->prepare("SELECT m_id, m_number, m_name, m_title FROM materials WHERE m_id = ?");
  $stmt->bind_param("i", $m_id);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 0) {
      die("Material not found.");
  }

  $material = $res->fetch_object();
  $filePath = "../public/sys_data/uploads/materials/" . $material->m_name;

  if (!file_exists($filePath)) {
      die("PDF file not found.");
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo htmlspecialchars($material->m_title); ?></title>
      <link rel="icon" href="../public/sys_data/logo/logo.png" type="image/png">
      <style>
          html, body {
              margin: 0;
              height: 100%;
              overflow: hidden;
              font-family: Arial, sans-serif;
          }
          .topbar {
              height: 50px;
              background: #f8f9fa;
              border-bottom: 1px solid #ddd;
              display: flex;
              align-items: center;
              justify-content: space-between;
              padding: 0 15px;
          }
          .viewer {
              width: 100%;
              height: calc(100vh);
              border: none;
          }
          .btn {
              text-decoration: none;
              padding: 8px 12px;
              border: 1px solid #333;
              border-radius: 4px;
              color: #333;
              margin-left: 8px;
          }
      </style>
  </head>
  <body>
      <iframe class="viewer" src="<?php echo htmlspecialchars($filePath); ?>"></iframe>
  </body>
</html>
<?php
    session_start();

    $pconfig = require '../config/config.php';
    $merchant_id = $pconfig['payhere']['merchant_id'];
    $merchant_secret = $pconfig['payhere']['merchant_secret'];
    $return_url = $pconfig['payhere']['return_url'];
    $cancel_url = $pconfig['payhere']['cancel_url'];
    $notify_url = $pconfig['payhere']['notify_url'];

    if ($mysqli->connect_error) {die("Connection failed: " . $mysqli->connect_error);}
    if (!isset($_POST['pay_for_reading_material'])) {die("Invalid request");}

    $error = 0;
    $err = "";

    /* Logged-in student ID */
    $studentId = $_SESSION['user_id'];
    if (empty($studentId)) {die("Session expired. Please log in again.");}

    /* Material ID from form only */
    if (isset($_POST['m_id']) && is_numeric($_POST['m_id'])) {$m_id = (int) $_POST['m_id'];}
    else
    {
        $error = 1;
        $err = "Material cannot be empty";
    }

    if (!$error)
    {
        /* Load material + unit + course + instructor from DB */
        $sql = "
            SELECT
                m.m_id,
                m.m_number,
                m.m_name,
                m.m_price,
                u.u_id,
                u.u_code,
                u.u_name,
                c.c_id,
                c.c_code,
                c.c_name,
                inst.user_id AS i_id,
                inst.name AS i_name
            FROM materials m
            INNER JOIN units u ON m.u_id = u.u_id
            INNER JOIN courses c ON u.c_id = c.c_id
            INNER JOIN users inst ON c.i_id = inst.user_id
            INNER JOIN enrollments e ON e.c_id = c.c_id AND e.s_id = ?
            WHERE m.m_id = ?
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $studentId, $m_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $material = $res->fetch_object();

        if (!$material) {die("Material not found or you are not enrolled for this course.");}

        /* Load student details from DB */
        $studentSql = "SELECT user_id, user_code, name, email, phone FROM users WHERE user_id = ? AND role = 'student'";
        $studentStmt = $mysqli->prepare($studentSql);
        $studentStmt->bind_param("i", $studentId);
        $studentStmt->execute();
        $studentRes = $studentStmt->get_result();
        $student = $studentRes->fetch_object();

        if (!$student) {die("Student record not found.");}

        $formatted_amount = number_format((float)$material->m_price, 2, '.', '');
        $order_id = uniqid('ORD-');
        $currency = "LKR";

        /* Store trusted order data in session */
        $_SESSION['orders'][$order_id] = [
            'name'       => $student->name,
            'email'      => $student->email,
            'phone'      => $student->phone,
            'amount'     => $formatted_amount,
            'currency'   => $currency,

            'm_id'       => $material->m_id,
            'm_number'   => $material->m_number,
            'm_name'     => $material->m_name,

            's_id'       => $student->user_id,
            's_regno'    => $student->user_code,

            'u_id'       => $material->u_id,
            'u_code'     => $material->u_code,
            'u_name'     => $material->u_name,

            'c_id'       => $material->c_id,
            'c_code'     => $material->c_code,
            'c_name'     => $material->c_name,

            'i_id'       => $material->i_id,
            'i_name'     => $material->i_name,

            'p_method'   => 'Credit/Debit Card',
            'p_amt'      => $formatted_amount
        ];

        $hash = strtoupper(md5($merchant_id . $order_id . $formatted_amount . $currency .strtoupper(md5($merchant_secret))));
?>
<!DOCTYPE html>
    <html>
        <head><title>Redirecting...</title></head>
        <body>
            <form method="post" action="https://sandbox.payhere.lk/pay/checkout" id="payhere-form">
                <input type="hidden" name="merchant_id" value="<?php echo htmlspecialchars($merchant_id); ?>">
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">
                <input type="hidden" name="cancel_url" value="<?php echo htmlspecialchars($cancel_url); ?>">
                <input type="hidden" name="notify_url" value="<?php echo htmlspecialchars($notify_url); ?>">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                <input type="hidden" name="items" value="Payment Gateway">
                <input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars($formatted_amount); ?>">
                <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($student->name); ?>">
                <input type="hidden" name="last_name" value="Customer">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($student->email); ?>">
                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($student->phone); ?>">
                <input type="hidden" name="address" value="Colombo">
                <input type="hidden" name="city" value="Colombo">
                <input type="hidden" name="country" value="Sri Lanka">
                <input type="hidden" name="hash" value="<?php echo htmlspecialchars($hash); ?>">
            </form>
            <script>document.getElementById("payhere-form").submit();   </script>
        </body>
    </html>
<?php
    }
?>
<?php
    session_start();
    $config = require __DIR__ . '/config.php';
    $merchant_id = $config['merchant_id'];
    $merchant_secret = $config['merchant_secret'];
    $dbHost = $config['db']['host'];
    $dbUser = $config['db']['user'];
    $dbPass = $config['db']['pass'];
    $dbName = $config['db']['name'];
    $ls_id=0;
    $s_id=0;
    $s_regno="";
    $s_name="";
    $s_email="";
    $s_phone="";
    $c_code="";
    $sm_number="";
    $c_id=0;
    $cc_id=0;
    $c_name="";
    $c_category="";
    $i_id=0;
    $i_name="";
    $p_method="";
    $p_amt=0.00;
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    if (isset($_POST['pay_for_reading_material'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['ls_id']) && !empty($_POST['ls_id'])) {
            $ls_id = mysqli_real_escape_string($mysqli, trim($_POST['ls_id']));
        } else {
            $error = 1;
            $err = "Study Material Cannot Be Empty";
        }

        if (isset($_POST['s_id']) && !empty($_POST['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_POST['s_id']));
        } else {
            $error = 1;
            $err = "Student ID Cannot Be Empty";
        }

        if (isset($_POST['s_regno']) && !empty($_POST['s_regno'])) {
            $s_regno = mysqli_real_escape_string($mysqli, trim($_POST['s_regno']));
        } else {
            $error = 1;
            $err = "Student Regno Cannot Be Empty";
        }

        if (isset($_POST['s_name']) && !empty($_POST['s_name'])) {
            $s_name = mysqli_real_escape_string($mysqli, trim($_POST['s_name']));
        } else {
            $error = 1;
            $err = "Student Name Cannot Be Empty";
        }

        if (isset($_POST['s_email']) && !empty($_POST['s_email'])) {
            $s_email = mysqli_real_escape_string($mysqli, trim($_POST['s_email']));
        } else {
            $error = 1;
            $err = "Student Email Cannot Be Empty";
        }

        if (isset($_POST['s_phoneno']) && !empty($_POST['s_phoneno'])) {
            $s_phone = mysqli_real_escape_string($mysqli, trim($_POST['s_phoneno']));
        } else {
            $error = 1;
            $err = "Student Phone Number Cannot Be Empty";
        }

        if (isset($_POST['p_amt']) && !empty($_POST['p_amt'])) {
            $p_amt  = mysqli_real_escape_string($mysqli, trim($_POST['p_amt']));
        } else {
            $error = 1;
            $err = "Payment Amount Cannot Be Empty";
        }

        if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
            $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
        } else {
            $error = 1;
            $err = "Unit Code Cannot Be Empty";
        }

        if (isset($_POST['sm_number']) && !empty($_POST['sm_number'])) {
            $sm_number = mysqli_real_escape_string($mysqli, trim($_POST['sm_number']));
        } else {
            $error = 1;
            $err = "Study Material Number Cannot Be Empty";
        }

        if (isset($_POST['c_id']) && !empty($_POST['c_id'])) {
            $c_id  = mysqli_real_escape_string($mysqli, trim($_POST['c_id']));
        } else {
            $error = 1;
            $err = "Unit ID Number Cannot Be Empty";
        }

        if (isset($_POST['cc_id']) && !empty($_POST['cc_id'])) {
            $cc_id  = mysqli_real_escape_string($mysqli, trim($_POST['cc_id']));
        } else {
            $error = 1;
            $err = "Course ID Cannot Be Empty";
        }

        if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
            $c_name  = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
        } else {
            $error = 1;
            $err = "Unit Name Cannot Be Empty";
        }

        if (isset($_POST['c_category']) && !empty($_POST['c_category'])) {
            $c_category  = mysqli_real_escape_string($mysqli, trim($_POST['c_category']));
        } else {
            $error = 1;
            $err = "Course Name Cannot Be Empty";
        }

        if (isset($_POST['i_id']) && !empty($_POST['i_id'])) {
            $i_id  = mysqli_real_escape_string($mysqli, trim($_POST['i_id']));
        } else {
            $error = 1;
            $err = "Instructor ID Cannot Be Empty";
        }

        if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
            $i_name  = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
        } else {
            $error = 1;
            $err = "Instructor Name Cannot Be Empty";
        }

        if (isset($_POST['p_amt']) && !empty($_POST['p_amt'])) {
            $p_amt  = mysqli_real_escape_string($mysqli, trim($_POST['p_amt']));
        } else {
            $error = 1;
            $err = "Payment Amount Cannot Be Empty";
        }

        if (!$error) {
            // 1. Format the amount to exactly 2 decimal places first
            $formatted_amount = number_format($p_amt, 2, '.', '');

            $order_id = uniqid();

            $currency = "LKR";

            // Store order data in session
            $_SESSION['orders'][$order_id] = [
                'name'     => $s_name,
                'email'    => $s_email,
                'phone'    => $s_phone,
                'amount'   => $formatted_amount,
                'currency' => $currency,
                'ls_id'    => $ls_id,
                's_id'     => $s_id,
                's_regno'  => $s_regno,
                'c_code'   => $c_code,
                'sm_number'=> $sm_number,
                'c_id'     => $c_id,
                'cc_id'    => $cc_id,
                'c_name'   => $c_name,
                'c_category'=> $c_category,
                'i_id'     => $i_id,
                'i_name'   => $i_name,
                'p_method' => 'Credit/Debit Card',
                'p_amt'    => $formatted_amount
            ];

            $hash = strtoupper(md5(
                $merchant_id . $order_id . $formatted_amount . $currency .
                strtoupper(md5($merchant_secret))
            ));
?>
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Redirecting...</title>
                </head>
                <body>
                    <form method="post" action="https://sandbox.payhere.lk/pay/checkout" id="payhere-form">
                        <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>">
                        <input type="hidden" name="return_url" value="https://upload-unclad-slouching.ngrok-free.dev/instructly/payment/success.php">
                        <input type="hidden" name="cancel_url" value="https://upload-unclad-slouching.ngrok-free.dev/instructly/payment/cancel.php">
                        <input type="hidden" name="notify_url" value="https://upload-unclad-slouching.ngrok-free.dev/instructly/payment/notify.php">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="items" value="Payment Gateway">
                        <input type="hidden" name="currency" value="LKR">
                        <input type="hidden" name="amount" value="<?php echo $formatted_amount; ?>">
                        <input type="hidden" name="first_name" value="<?php echo $s_name; ?>">
                        <input type="hidden" name="last_name" value="Customer">
                        <input type="hidden" name="email" value="<?php echo $s_email; ?>">
                        <input type="hidden" name="phone" value="<?php echo $s_phone; ?>">
                        <input type="hidden" name="address" value="Colombo">
                        <input type="hidden" name="city" value="Colombo">
                        <input type="hidden" name="country" value="Sri Lanka">
                        <input type="hidden" name="hash" value="<?php echo $hash; ?>">
                    </form>
                    <script>
                        document.getElementById("payhere-form").submit();
                    </script>
                </body>
            </html>
<?php
        }
    }
?>
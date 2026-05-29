<?php
    session_start();
    // Import PHPMailer classes into the global namespace
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $config = require __DIR__ . '/config.php';
    $merchant_secret = $config['merchant_secret'];
    $dbHost = $config['db']['host'];
    $dbUser = $config['db']['user'];
    $dbPass = $config['db']['pass'];
    $dbName = $config['db']['name'];
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $smtpHost = $config['smtp']['host'];
    $smtpUsername = $config['smtp']['username'];
    $smtpPassword = $config['smtp']['password'];
    $smtpPort = $config['smtp']['port'];
    $smtpFromEmail = $config['smtp']['from_email'];
    $smtpFromName = $config['smtp']['from_name'];
    $downloadPdf = isset($_GET['download_pdf']) && $_GET['download_pdf'] === '1';

    // Optionally accept an order_id, email and amount query parameters
    $order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : null;
    $customer_name = isset($_GET['name']) ? trim($_GET['name']) : null;
    $customer_email = isset($_GET['email']) ? trim($_GET['email']) : null;
    $customer_phone = isset($_GET['phone']) ? trim($_GET['phone']) : null;
    $amount = isset($_GET['amount']) ? trim($_GET['amount']) : null;
    $currency = isset($_GET['currency']) ? trim($_GET['currency']) : null;
    $ls_id = isset($_GET['ls_id']) ? trim($_GET['ls_id']) : null;
    $s_id = isset($_GET['s_id']) ? trim($_GET['s_id']) : null;
    $s_regno = isset($_GET['s_regno']) ? trim($_GET['s_regno']) : null;
    $c_code = isset($_GET['c_code']) ? trim($_GET['c_code']) : null;
    $sm_number = isset($_GET['sm_number']) ? trim($_GET['sm_number']) : null;
    $c_id = isset($_GET['c_id']) ? trim($_GET['c_id']) : null;
    $cc_id = isset($_GET['cc_id']) ? trim($_GET['cc_id']) : null;
    $c_name = isset($_GET['c_name']) ? trim($_GET['c_name']) : null;
    $c_category = isset($_GET['c_category']) ? trim($_GET['c_category']) : null;
    $i_id = isset($_GET['i_id']) ? trim($_GET['i_id']) : null;
    $i_name = isset($_GET['i_name']) ? trim($_GET['i_name']) : null;    
    $p_method = isset($_GET['p_method']) ? trim($_GET['p_method']) : null;
    $p_amt = isset($_GET['p_amt']) ? trim($_GET['p_amt']) : null;

    if ($order_id && isset($_SESSION['orders'][$order_id])) {
        $sessionOrder = $_SESSION['orders'][$order_id];

        if (empty($customer_name) && !empty($sessionOrder['name'])) {
            $customer_name = $sessionOrder['name'];
        }
        if (empty($customer_email) && !empty($sessionOrder['email'])) {
            $customer_email = $sessionOrder['email'];
        }
        if (empty($customer_phone) && !empty($sessionOrder['phone'])) {
            $customer_phone = $sessionOrder['phone'];
        }
        if (empty($amount) && !empty($sessionOrder['amount'])) {
            $amount = $sessionOrder['amount'];
        }
        if (empty($currency) && !empty($sessionOrder['currency'])) {
            $currency = $sessionOrder['currency'];
        }
        if (empty($ls_id) && !empty($sessionOrder['ls_id'])) {
            $ls_id = $sessionOrder['ls_id'];
        }
        if (empty($s_id) && !empty($sessionOrder['s_id'])) {
            $s_id = $sessionOrder['s_id'];
        }
        if (empty($s_regno) && !empty($sessionOrder['s_regno'])) {
            $s_regno = $sessionOrder['s_regno'];
        }
        if (empty($c_code) && !empty($sessionOrder['c_code'])) {
            $c_code = $sessionOrder['c_code'];
        }
        if (empty($sm_number) && !empty($sessionOrder['sm_number'])) {
            $sm_number = $sessionOrder['sm_number'];
        }
        if (empty($c_id) && !empty($sessionOrder['c_id'])) {
            $c_id = $sessionOrder['c_id'];
        }
        if (empty($cc_id) && !empty($sessionOrder['cc_id'])) {
            $cc_id = $sessionOrder['cc_id'];
        }
        if (empty($c_name) && !empty($sessionOrder['c_name'])) {
            $c_name = $sessionOrder['c_name'];
        }
        if (empty($c_category) && !empty($sessionOrder['c_category'])) {
            $c_category = $sessionOrder['c_category'];
        }
        if (empty($i_id) && !empty($sessionOrder['i_id'])) {
            $i_id = $sessionOrder['i_id'];
        }
        if (empty($i_name) && !empty($sessionOrder['i_name'])) {
            $i_name = $sessionOrder['i_name'];
        }
        if (empty($p_method) && !empty($sessionOrder['p_method'])) {
            $p_method = $sessionOrder['p_method'];
        }
        if (empty($p_amt) && !empty($sessionOrder['p_amt'])) {
            $p_amt = $sessionOrder['p_amt'];
        }
    }

    $logFile = __DIR__ . '/payment-log.txt';
    $paymentData = [];

    if (is_readable($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines) {
            $searchOrderId = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $row = json_decode($lines[$i], true);

                if (!is_array($row)) {
                    continue;
                }

                if ($searchOrderId === '' || (($row['order_id'] ?? '') === $searchOrderId)) {
                    $paymentData = $row;
                    break;
                }
            }
        }
    }

    $merchant_id      = $paymentData['merchant_id'] ?? '';
    $order_id         = $paymentData['order_id'] ?? '';
    $payment_id       = $paymentData['payment_id'] ?? '';
    $payhere_amount   = $paymentData['payhere_amount'] ?? '';
    $payhere_currency = $paymentData['payhere_currency'] ?? '';
    $status_code      = $paymentData['status_code'] ?? '';
    $md5sig           = $paymentData['md5sig'] ?? '';

    // Generate local hash
    $local_md5sig = strtoupper(
        md5(
            $merchant_id .
            $order_id .
            $payhere_amount .
            $payhere_currency .
            $status_code .
            strtoupper(md5($merchant_secret))
        )
    );

    // Validate payment
    if (($local_md5sig === $md5sig) && ($status_code == 2)) {
        $check = $mysqli->prepare("SELECT psm_id FROM lms_paid_study_materials WHERE order_id = ? LIMIT 1");    
        $check->bind_param("s", $order_id);
        $check->execute();
        $result = $check->get_result();

        if (!$result || !$result->fetch_assoc()) {

            // ensure fallback values
            $paid_at = date('Y-m-d H:i:s');
            $p_date_paid = $paid_at;
            if (empty($p_amt)) $p_amt = $payhere_amount;
            $verification_status = 'pending';

            $stmt = $mysqli->prepare("
                INSERT INTO lms_paid_study_materials (
                    order_id,
                    currency,
                    ls_id,
                    c_code,
                    sm_number,
                    c_id,
                    cc_id,
                    c_name,
                    c_category,
                    i_id,
                    i_name,
                    p_method,
                    p_code,
                    p_amt,
                    p_date_paid,
                    s_id,
                    s_name,
                    s_regno,
                    p_verification_status
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                'sssssssssssssssssss',
                $order_id,
                $payhere_currency,
                $ls_id,
                $c_code,
                $sm_number,
                $c_id,
                $cc_id,
                $c_name,
                $c_category,
                $i_id,
                $i_name,
                $p_method,
                $payment_id,
                $p_amt,
                $p_date_paid,
                $s_id,
                $customer_name,
                $s_regno,
                $verification_status
            );

            $executed = $stmt->execute();
            $inserted = false;
            if ($executed) {
                // mysqli_stmt::affected_rows is available after execute
                $inserted = ($stmt->affected_rows > 0);
            }
            $stmt->close();

            // If DB insert succeeded, remove the payment-log.txt file written by notify.php
            if ($inserted) {
                $payloadFile = 'payment-log.txt';
                if (is_file($payloadFile) && is_writable($payloadFile)) {
                    @unlink($payloadFile);
                }
            }
        }
        $check->close();
    }
    

    // Initialize commonly used variables and attempt to load order data from DB
    $order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : (isset($_SESSION['order_id']) ? $_SESSION['order_id'] : null);
    $orderData = [];
    $logFile = __DIR__ . DIRECTORY_SEPARATOR . 'payment.log';
    $errorMessage = '';

    if ($order_id) {
        // safe DB retrieval using prepared statements
        $mysqli_conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if (!$mysqli_conn->connect_errno) {
            $stmt = $mysqli_conn->prepare("SELECT * FROM lms_paid_study_materials WHERE order_id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $order_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && ($row = $result->fetch_assoc())) {
                    // Normalize DB columns to the receipt fields used by buildReceiptHtml()
                    $orderData = [
                        // payment id in this table is stored as p_code (fallback to payment_id)
                        'payment_id' => $row['p_code'],
                        // paid date/time
                        'paid_at'    => $row['p_date_paid'],
                        // student / payer name
                        'name'       => $row['s_name'],
                        // email/phone may not exist in this table — keep if present
                        'email'      => $_SESSION['orders'][$order_id]['email'] ?? '',
                        'phone'      => $_SESSION['orders'][$order_id]['phone'] ?? '',
                        // amount stored as p_amt in this table
                        'amount'     => $row['p_amt'],
                        'currency'   => $row['currency'] ?? '',
                    ];
                }
                if ($result) $result->free();
                $stmt->close();
            } else {
                file_put_contents($logFile, sprintf("[%s] DB prepare error (select): %s\n", date('c'), $mysqli_conn->error), FILE_APPEND);
            }
            $mysqli_conn->close();
        } else {
            file_put_contents($logFile, sprintf("[%s] DB connect error (select): %s\n", date('c'), $mysqli_conn->connect_error), FILE_APPEND);
        }
    }

    if ($downloadPdf && $order_id && !empty($orderData)) {
        require '../vendor/autoload.php';

        if (class_exists('\\Mpdf\\Mpdf')) {
            $pdfFileName = 'Receipt - ' . (!empty($orderData['payment_id']) ? $orderData['payment_id'] : $order_id) . '.pdf';
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML(buildReceiptHtml($orderData, $amount ?? ''));
            $mpdf->Output($pdfFileName, \Mpdf\Output\Destination::DOWNLOAD);
            exit;
        }
    }

    function buildReceiptHtml(array $orderData, string $amount): string
    {
        $html = '<html><head><meta charset="utf-8"/><style>' .
            'body{font-family: Arial, Helvetica, sans-serif; color:#333;}' .
            '.header{display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:22px;font-weight:700;}' .
            '.title{font-size:20px;font-weight:700}' .
            '.hr{border-top:1px solid #ccc;margin:12px 0}' .
            '.grid{width:100%;border-collapse:collapse;margin-top:8px}' .
            '.grid th{background:#f4f4f4;text-align:left;padding:8px;border:1px solid #ddd}' .
            '.grid td{padding:8px;border:1px solid #ddd}' .
            '.small{font-size:12px;color:#666}' .
            '</style></head><body>';

        $html .= '<div class="header"><img src="../public/sys_data/logo/lms_logo.png" style="height:60px;"><br>Instructly LMS</div><div class="title">Payment Confirmation</div></div>';
        $html .= '<div class="hr"></div>';

        $html .= '<table class="grid">';
        $html .= '<tr><th>Transaction</th><th>Details</th></tr>';
        $html .= '<tr><td>Payment ID</td><td>' . htmlspecialchars($orderData['payment_id'] ?? '') . '</td></tr>';
        $html .= '<tr><td>Date, Time</td><td>' . htmlspecialchars($orderData['paid_at'] ?? date('d/m/Y H:i:s')) . '</td></tr>';
        $html .= '<tr><td>Name</td><td>' . htmlspecialchars($orderData['name'] ?? '') . '</td></tr>';
        $html .= '<tr><td>Email</td><td>' . htmlspecialchars($orderData['email'] ?? '') . '</td></tr>';
        $html .= '<tr><td>Phone</td><td>' . htmlspecialchars($orderData['phone'] ?? '') . '</td></tr>';
        $html .= '<tr><td>Amount</td><td>' . htmlspecialchars($orderData['amount'] ?? $amount) . ' ' . htmlspecialchars($orderData['currency'] ?? '') . '</td></tr>';
        $html .= '</table>';

        $html .= '<p class="small">This is a system generated receipt.</p>';
        $html .= '</body></html>';

        return $html;
    }

    function generateReceiptPdfBinary(array $orderData, string $order_id, string $amount): string
    {
        require '../vendor/autoload.php';

        if (!class_exists('\\Mpdf\\Mpdf')) {
            return '';
        }

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(buildReceiptHtml($orderData, $amount));
        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    function streamReceiptPdf(array $orderData, string $order_id, ?string $amount): bool
    {
        require '../vendor/autoload.php';

        if (!class_exists('\\Mpdf\\Mpdf')) {
            return false;
        }

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(buildReceiptHtml($orderData, $amount));
        $mpdf->Output('Receipt - ' . (!empty($orderData['payment_id']) ? $orderData['payment_id'] : $payment_id) . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        return true;
    }


    if ($order_id && $customer_email) {
        // Use DB-backed lock and record to prevent duplicate sends (prefer DB over filesystem)
        $dbAvailable = false;
        $mysqli = null;
        try {
            $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if (!$mysqli->connect_errno) {
                $dbAvailable = true;
            } else {
                file_put_contents($logFile, sprintf("[%s] DB connect error: %s\n", date('c'), $mysqli->connect_error), FILE_APPEND);
            }
        } catch (Exception $e) {
            file_put_contents($logFile, sprintf("[%s] DB exception: %s\n", date('c'), $e->getMessage()), FILE_APPEND);
        }

        $shouldSend = true;
        $lockName = '';
        $gotLock = false;

        if ($dbAvailable) {
            // acquire named lock for this order
            $lockName = 'email_flag_lock_' . preg_replace('/[^A-Za-z0-9_]/', '_', $order_id);
            $res = $mysqli->query("SELECT GET_LOCK('" . $mysqli->real_escape_string($lockName) . "', 10) AS got");
            $gotLock = false;
            if ($res) {
                $row = $res->fetch_assoc();
                $gotLock = isset($row['got']) && intval($row['got']) === 1;
                $res->free();
            }

            if ($gotLock) {
                // check existing flag
                $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM lms_paid_study_materials WHERE order_id = ? AND email_flag IS NOT NULL");
                if ($stmt) {
                    $stmt->bind_param('s', $order_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = 0;
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $count = isset($row['c']) ? intval($row['c']) : 0;
                        $result->free();
                    }
                    $stmt->close();

                    if ($count > 0) {
                        // already sent
                        $shouldSend = false;
                    }
                }
            } else {
                // couldn't get lock; assume another process is handling it
                $shouldSend = false;
            }
        }

        if ($shouldSend) {
            // attempt send
            require '../vendor/autoload.php';
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUsername;
                $mail->Password   = $smtpPassword;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $smtpPort;

                $mail->setFrom($smtpFromEmail, $smtpFromName);
                $mail->addAddress($customer_email);

                $mail->isHTML(false);
                $mail->Subject = "Payment Confirmation - Order #$order_id";

                $message = "Dear Sir/Madam,\n\n";
                $message .= "Your Payment was Successful!\n";
                $message .= "Payment ID: " . ($orderData['payment_id'] ?? '') . "\n";
                if ($amount) $message .= "Amount Paid: $amount\n";
                $message .= "\nThank You for Your Payment.";

                $mail->Body = $message;

                // Generate PDF receipt from order data and attach
                try {
                    // ensure order data is loaded from DB if not already
                    if (empty($orderData) && $order_id && $dbAvailable) {
                        $stmt = $mysqli->prepare("SELECT * FROM lms_paid_study_materials WHERE order_id = ? LIMIT 1");
                        if ($stmt) {
                            $stmt->bind_param('s', $order_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result) {
                                $orderData = $result->fetch_assoc();
                                $result->free();
                            }
                            $stmt->close();
                        }
                    }

                    if (!empty($orderData) && class_exists('\Mpdf\Mpdf')) {
                        $pdfContent = generateReceiptPdfBinary((array) $orderData, $order_id, $amount);
                        if ($pdfContent !== '') {
                            $mail->addStringAttachment($pdfContent, 'Receipt - ' . ($orderData['payment_id'] ?? $payment_id) . '.pdf', 'base64', 'application/pdf');
                        }
                    }
                } catch (Exception $pdfEx) {
                    file_put_contents($logFile, sprintf("[%s] PDF error: %s\n", date('c'), $pdfEx->getMessage()), FILE_APPEND);
                }

                $mail->send();

                // record into DB if available
                if ($dbAvailable) {
                    $stmt = $mysqli->prepare("UPDATE lms_paid_study_materials SET email_flag = 1 WHERE order_id = ?");
                    if ($stmt) {
                        $stmt->bind_param('s', $order_id);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        file_put_contents($logFile, sprintf("[%s] DB prepare error: %s\n", date('c'), $mysqli->error), FILE_APPEND);
                    }
                }

            } catch (Exception $e) {
                $err = sprintf("[%s] OrderID:%s Email:%s Exception:%s MailerInfo:%s\n", date('c'), $order_id, $customer_email, $e->getMessage(), isset($mail) ? $mail->ErrorInfo : 'N/A');
                file_put_contents($logFile, $err, FILE_APPEND);
            }
        }

        // release DB lock if held
        if ($dbAvailable && $gotLock) {
            $mysqli->query("SELECT RELEASE_LOCK('" . $mysqli->real_escape_string($lockName) . "')");
            $mysqli->close();
        } elseif ($dbAvailable) {
            $mysqli->close();
        }
    }

    // Read last error entry (if any) for display
    if (file_exists($logFile) && is_readable($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false && count($lines) > 0) {
            // Search for the most recent matching entry if order_id provided
            if ($order_id) {
                for ($i = count($lines) - 1; $i >= 0; $i--) {
                    if (strpos($lines[$i], "OrderID:" . $order_id) !== false) {
                        $errorMessage = $lines[$i];
                        break;
                    }
                }
            }

            // If no matching entry found, fall back to the last log line
            if (!$errorMessage) {
                $errorMessage = end($lines);
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Success</title>
        <link rel="stylesheet" href="../public/css/pstyle.css">
        <link rel="icon" type="image/png" href="../public/sys_data/logo/lms_logo.png">
    </head>
    <body class="status-page">
        <main class="status-card">
            <div class="status-icon success">✓</div>
            <h1>Payment Successful</h1>
            <p>Your transaction was completed successfully.<br>The receipt has been sent to your email.</p>
            <?php 
                if ($errorMessage): ?>
                    <section class="status-error">
                        <h2>Email Delivery Issue</h2>
                        <p>The system recorded an issue while sending the confirmation email:</p>
                        <pre><?php echo htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></pre>
                        <p>We logged this error; our support team can help if needed.</p>
                    </section>
                    <?php 
                endif; 
            ?>
            <div class="status-actions">
                <a class="status-button" href="../views/std_billings.php">Back to Home</a>&ensp;
                <?php 
                    if ($order_id): ?>
                        <a class="status-button" href="success.php?order_id=<?php echo urlencode($order_id); ?>&download_pdf=1" target="_blank" rel="noopener">Download Receipt</a>
                        <?php 
                    endif;
                ?>
            </div>
        </main>
    </body>
</html>
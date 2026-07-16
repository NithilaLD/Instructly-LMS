<?php
include('../config/config.php');
require_once('../config/audit.php');
include('../config/checklogin.php');
userRoles(['admin', 'instructor', 'student']);
$role    = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['user_id'] ?? 0);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function fetchSystem(mysqli $mysqli): ?object
{
    $sql = "SELECT * FROM system LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $res = $stmt->get_result();
    $sys = $res->fetch_object();
    $stmt->close();
    return $sys ?: null;
}

function getCertificateById(mysqli $mysqli, int $certId): ?object
{
    $sql = "
        SELECT
            cert.cert_id,
            cert.en_id,
            cert.date_generated,
            e.c_id,
            e.s_id,
            c.c_code,
            c.c_name,
            c.i_id,
            s.user_code AS student_code,
            s.name AS student_name,
            i.user_code AS instructor_code,
            i.name AS instructor_name
        FROM certificates cert
        INNER JOIN enrollments e ON cert.en_id = e.en_id
        INNER JOIN courses c ON e.c_id = c.c_id
        INNER JOIN users s ON e.s_id = s.user_id
        LEFT JOIN users i ON c.i_id = i.user_id
        WHERE cert.cert_id = ?
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $certId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_object();
    $stmt->close();

    return $row ?: null;
}

function userCanViewCertificate(object $cert, string $role, int $user_id): bool
{
    if ($role === 'admin') {
        return true;
    }

    if ($role === 'student') {
        return ((int)($cert->s_id ?? 0) === $user_id);
    }

    if ($role === 'instructor') {
        return ((int)($cert->i_id ?? 0) === $user_id);
    }

    return false;
}

function fetchEligibleEnrollments(mysqli $mysqli, string $role, int $user_id): array
{
    if ($role == 'admin') {

        $sql = "
                SELECT DISTINCT
            e.en_id,
            e.en_date,

            c.c_id,
            c.c_code,
            c.c_name,

            s.user_code AS student_code,
            s.name AS student_name,

            u.u_id,
            u.u_code,
            u.u_name

        FROM enrollments e

        INNER JOIN courses c
            ON e.c_id = c.c_id

        INNER JOIN users s
            ON e.s_id = s.user_id

        INNER JOIN marks r
            ON r.s_id = e.s_id

        INNER JOIN units u
            ON u.u_id = r.u_id
            AND u.c_id = e.c_id   -- IMPORTANT FIX

        LEFT JOIN certificates cert
            ON cert.en_id = e.en_id
            AND cert.u_id = u.u_id

        WHERE
            (
                r.u_cat1_marks IS NOT NULL
                OR r.u_cat2_marks IS NOT NULL
                OR r.u_eos_marks IS NOT NULL
            )
            AND cert.cert_id IS NULL

        ORDER BY e.en_id DESC";

        $stmt = $mysqli->prepare($sql);

    } else if ($role === 'instructor') {

        $sql = "
        SELECT DISTINCT
            e.en_id,
            e.en_date,

            c.c_id,
            c.c_code,
            c.c_name,

            s.user_code AS student_code,
            s.name AS student_name,

            u.u_id,
            u.u_code,
            u.u_name

        FROM enrollments e

        INNER JOIN courses c
            ON e.c_id = c.c_id

        INNER JOIN users s
            ON e.s_id = s.user_id

        INNER JOIN marks r
            ON r.s_id = e.s_id

        INNER JOIN units u
            ON u.u_id = r.u_id
            AND u.c_id = e.c_id

        LEFT JOIN certificates cert
            ON cert.en_id = e.en_id
            AND cert.u_id = u.u_id

        WHERE
            cert.cert_id IS NULL AND
            u.c_id = e.c_id
            AND c.i_id = ?
            AND (
                    r.u_cat1_marks IS NOT NULL
                 OR r.u_cat2_marks IS NOT NULL
                 OR r.u_eos_marks  IS NOT NULL
            )
        ORDER BY e.en_id DESC
        ";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    else if($role === 'student') {
        $sql = "
        SELECT DISTINCT
            e.en_id,
            e.en_date,

            c.c_id,
            c.c_code,
            c.c_name,

            s.user_code AS student_code,
            s.name AS student_name,

            u.u_id,
            u.u_code,
            u.u_name

        FROM enrollments e

        INNER JOIN courses c
            ON e.c_id = c.c_id

        INNER JOIN users s
            ON e.s_id = s.user_id

        INNER JOIN marks r
            ON r.s_id = e.s_id

        INNER JOIN units u
            ON u.u_id = r.u_id
            AND u.c_id = e.c_id

        LEFT JOIN certificates cert
            ON cert.en_id = e.en_id
            AND cert.u_id = u.u_id

        WHERE
            cert.cert_id IS NULL AND
            e.s_id = ?
            AND (
                    r.u_cat1_marks IS NOT NULL
                 OR r.u_cat2_marks IS NOT NULL
                 OR r.u_eos_marks  IS NOT NULL
            )
        ORDER BY e.en_id DESC
        ";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $user_id);
    } else {
        return [];
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

function fetchCertificates(mysqli $mysqli, string $role, int $user_id): array
{
    if ($role === 'admin') {
        $sql = "
            SELECT
                cert.cert_id,
                cert.en_id,
                cert.date_generated,
                e.c_id,
                e.s_id,
                c.c_code,
                c.c_name,
                c.i_id,
                s.user_code AS student_code,
                s.name AS student_name,
                i.user_code AS instructor_code,
                i.name AS instructor_name
            FROM certificates cert
            INNER JOIN enrollments e ON cert.en_id = e.en_id
            INNER JOIN courses c ON e.c_id = c.c_id
            INNER JOIN users s ON e.s_id = s.user_id
            LEFT JOIN users i ON c.i_id = i.user_id
            ORDER BY cert.cert_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
    } elseif ($role === 'instructor') {
        $sql = "
            SELECT
                cert.cert_id,
                cert.en_id,
                cert.date_generated,
                e.c_id,
                e.s_id,
                c.c_code,
                c.c_name,
                c.i_id,
                s.user_code AS student_code,
                s.name AS student_name,
                i.user_code AS instructor_code,
                i.name AS instructor_name
            FROM certificates cert
            INNER JOIN enrollments e ON cert.en_id = e.en_id
            INNER JOIN courses c ON e.c_id = c.c_id
            INNER JOIN users s ON e.s_id = s.user_id
            LEFT JOIN users i ON c.i_id = i.user_id
            WHERE c.i_id = ?
            ORDER BY cert.cert_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $user_id);
    } else {
        $sql = "
            SELECT
                cert.cert_id,
                cert.en_id,
                cert.date_generated,
                e.c_id,
                e.s_id,
                c.c_code,
                c.c_name,
                c.i_id,
                s.user_code AS student_code,
                s.name AS student_name,
                i.user_code AS instructor_code,
                i.name AS instructor_name
            FROM certificates cert
            INNER JOIN enrollments e ON cert.en_id = e.en_id
            INNER JOIN courses c ON e.c_id = c.c_id
            INNER JOIN users s ON e.s_id = s.user_id
            LEFT JOIN users i ON c.i_id = i.user_id
            WHERE e.s_id = ?
            ORDER BY cert.cert_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $user_id);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}
/* Insert / Generate Certificate */
if (isset($_POST['add_certificate']) &&($role === 'admin' || $role === 'instructor')) {
    $value = $_POST['en_id'];
    list($en_id, $u_id) = explode('|', $value);

    $en_id = (int)$en_id;
    $u_id  = (int)$u_id;
    if ($en_id <= 0) {
        $err = "Please select a valid enrollment.";
    } else {
        if ($role === 'admin') {
            $enStmt = $mysqli->prepare("
                SELECT
                    e.en_id,
                    e.c_id,
                    c.i_id
                FROM enrollments e
                INNER JOIN courses c ON e.c_id = c.c_id
                WHERE e.en_id = ?
                LIMIT 1
            ");
            $enStmt->bind_param('i', $en_id);
        } else {
            $enStmt = $mysqli->prepare("
                SELECT
                    e.en_id,
                    e.c_id,
                    c.i_id
                FROM enrollments e
                INNER JOIN courses c ON e.c_id = c.c_id
                WHERE e.en_id = ? AND c.i_id = ?
                LIMIT 1
            ");
            $enStmt->bind_param('ii', $en_id, $user_id);
        }

        $enStmt->execute();
        $enRes = $enStmt->get_result();
        $enRow = $enRes->fetch_object();
        $enStmt->close();

        if ($enRow) {
            $markCheck = $mysqli->prepare("
                SELECT r.r_id
                FROM enrollments e
                INNER JOIN units u
                    ON u.c_id = e.c_id
                INNER JOIN marks r
                    ON r.u_id = u.u_id
                AND r.s_id = e.s_id
                WHERE e.en_id = ?
                AND (
                        r.u_cat1_marks IS NOT NULL
                    OR r.u_cat2_marks IS NOT NULL
                    OR r.u_eos_marks IS NOT NULL
                )
                LIMIT 1
            ");

            $markCheck->bind_param("i", $en_id);
            $markCheck->execute();
            $markRes = $markCheck->get_result();

            if (!$markRes->fetch_object()) {
                $err = "Certificate can only be generated after marks are entered for this course.";
                $markCheck->close();
            } else {
                $check = $mysqli->prepare("
                    SELECT cert_id
                    FROM certificates
                    WHERE en_id = ?
                    AND u_id = ?
                    LIMIT 1
                ");
                $check->bind_param("ii", $en_id, $u_id);
                $check->execute();
                $checkRes = $check->get_result();
                $exists = $checkRes->fetch_object();
                $check->close();

                if ($exists) {
                    $err = "A certificate already exists for this enrollment.";
                } else {
                    $ins = $mysqli->prepare("INSERT INTO certificates (en_id, u_id) VALUES (?, ?)");
                    $ins->bind_param('ii', $en_id, $u_id);

                    if ($ins->execute()) {
                        logAuditAction($mysqli, 'add_certificate', 'Certificate generated', 'certificates', 'certificate', (string) $en_id);
                        $_SESSION['flash_success'] = "Certificate generated successfully.";
                        header("Location: certificates.php");
                        exit;
                    } else {
                        $_SESSION['flash_error'] = "Failed to generate certificate.";
                    }

                    $ins->close();
                }
            }
        }
    }
}

/* Reject Certificate - admin only */
if ($role === 'admin' && isset($_GET['delete'])) {
    $cert_id = (int)$_GET['delete'];

    $del = $mysqli->prepare("UPDATE certificates SET status = 'deleted' WHERE cert_id = ?");
    $del->bind_param('i', $cert_id);

    if ($del->execute()) {
        logAuditAction($mysqli, 'delete_certificate', 'Certificate deleted', 'certificates', 'certificate', (string) $cert_id);
        $_SESSION['flash_success'] = "Certificate deleted successfully.";
        header("Location: certificates.php");
        exit;
    } else {
        $_SESSION['flash_error'] = "Failed to delete certificate.";
        header("Location: certificates.php");
        exit;
    }
}

$sys = fetchSystem($mysqli);

if ($sys) {
    require_once('../partials/head.php');
?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php
        require_once('../partials/navbar.php');
        require_once('../partials/sidebar.php');
    ?>

    <div class="content-wrapper">
        <section class="content <?php if ($role == 'student'): ?> pt-3 <?php endif; ?>">
            <div class="container-fluid">

                <?php
                $viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

                if ($viewId > 0) {
                    $cert = getCertificateById($mysqli, $viewId);

                    if (!$cert) {
                        echo '<div class="alert alert-info">Certificate not found.</div>';
                    } elseif (!userCanViewCertificate($cert, $role, $user_id)) {
                        echo '<div class="alert alert-danger">You do not have permission to view this certificate.</div>';
                    } else {
                ?>
                    <div class="card card-warning" id="Print">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-body">
                                            <ul class="list-group list-group-unbordered mb-3">
                                                <li class="list-group-item">
                                                    <b>Certificate ID</b>
                                                    <span class="float-right"><?php echo h($cert->cert_id); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Enrollment ID</b>
                                                    <span class="float-right"><?php echo h($cert->en_id); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Generated On</b>
                                                    <span class="float-right"><?php echo h($cert->date_generated); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Student Code</b>
                                                    <span class="float-right"><?php echo h($cert->student_code); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Student Name</b>
                                                    <span class="float-right"><?php echo h($cert->student_name); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Course Code</b>
                                                    <span class="float-right"><?php echo h($cert->c_code); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Course Name</b>
                                                    <span class="float-right"><?php echo h($cert->c_name); ?></span>
                                                </li>
                                                <li class="list-group-item">
                                                    <b>Instructor</b>
                                                    <span class="float-right"><?php echo h($cert->instructor_name ?? ''); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-body text-center">
                                            <h4>Certificate Preview</h4>
                                            <img src="../public/sys_data/logo/logo.png" alt="Certificate Template" class="img-fluid mb-3" style="max-height: 200px;">
                                            <p class="mb-1">This certificate belongs to</p>
                                            <h3><?php echo h($cert->student_name); ?></h3>
                                            <p class="mb-1">for the course</p>
                                            <h4><?php echo h($cert->c_name); ?></h4>
                                            <p class="mb-0">
                                                Certificate generated on <?php echo h($cert->date_generated); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-outline-warning" onclick="printContent('Print');">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                <?php
                    }
                } else {
                    $enrollments = fetchEligibleEnrollments($mysqli, $role, $user_id);
                    $certificates = fetchCertificates($mysqli, $role, $user_id);
                ?>
                    <?php if ($role === 'admin' || $role === 'instructor'): ?>
                            <div class="text-right text-dark" style="padding-top: 10px !important;">
                                <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#addCertificateModal">
                                    <i class="fas fa-plus"></i> Generate Certificate
                                </button>
                            </div>
                            <hr>
                    <?php endif; ?>
                    <div class="card">
                        <div class="container">
                            <div class="modal fade" id="addCertificateModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content" style="border: none; border-radius: 14px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,.18);">
                                        
                                        <div class="modal-header" style="border-bottom: 1px solid #eee;">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <form method="post">
                                            <div class="modal-body p-4">
                                                <div class="form-group mb-0">
                                                    <select name="en_id" id="en_id" class="form-control select2bs4" required style="width: 100%;">
                                                        <option value="">Select Student and Course</option>
                                                        <?php foreach ($enrollments as $en): ?>
                                                            <option value="<?php echo (int)$en->en_id . '|' . (int)$en->u_id; ?>">
                                                                <?php
                                                                    echo h($en->en_id) . ' - ' .
                                                                        h($en->student_name) . ' (' . h($en->student_code) . ') - ' .
                                                                        h($en->u_code) . ' / ' . h($en->u_name);
                                                                ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer text-right" style="background: #fafafa; border-top: 1px solid #eee;">
                                                <button type="submit" name="add_certificate" class="btn btn-warning text-white">
                                                    <i class="fas fa-check"></i> Generate
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($certificates)): ?>
                                <div class="alert alert-info" style="background: #84B7F9 !important;border: 1px solid #84B7F9 !important;">No Certificates Found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Certificate ID</th>
                                                <th>Student Code</th>
                                                <th>Student Name</th>
                                                <th>Course Code</th>
                                                <th>Course Name</th>
                                                <th>Generated On</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach ($certificates as $cert): ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo h($cert->cert_id); ?></td>
                                                    <td><?php echo h($cert->student_code); ?></td>
                                                    <td><?php echo h($cert->student_name); ?></td>
                                                    <td><?php echo h($cert->c_code); ?></td>
                                                    <td><?php echo h($cert->c_name); ?></td>
                                                    <td><?php echo h($cert->date_generated); ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="certificates.php?view=<?php echo (int)$cert->cert_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i> View
                                                        </a>
                                                    <?php if ($role === 'admin'): ?>
                                                    <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo (int)$cert->cert_id; ?>">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </a>

                                                    <div class="modal fade" id="delete-<?php echo (int)$cert->cert_id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">CONFIRM</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center text-danger">
                                                                    <h4>Delete certificate <?php echo h($cert->cert_id); ?>?</h4>
                                                                    <br>
                                                                    <button type="button" class="btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                    <a href="certificates.php?delete=<?php echo (int)$cert->cert_id; ?>" class="btn btn-outline-warning">Delete</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </section>
    </div>
</div>

<?php require_once('../partials/scripts.php'); ?>
</body>
</html>
<?php } ?>
<?php
include('../config/config.php');
include('../config/checklogin.php');

userRoles(['admin', 'instructor', 'student']);

$role    = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['user_id'] ?? 0);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$answerId   = isset($_GET['view']) ? (int) $_GET['view'] : 0;
if ($answerId <= 0 && isset($_GET['an_id'])) {
    $answerId = (int) $_GET['an_id'];
}

$questionId = isset($_GET['q_id']) ? (int) $_GET['q_id'] : 0;
$unitId     = isset($_GET['u_id']) ? (int) $_GET['u_id'] : 0;

$pageTitle = 'Answers View';
if ($answerId > 0) {
    $pageTitle = 'Answer Details';
} elseif ($questionId > 0) {
    $pageTitle = 'Question Answers';
}

function fetchSystem(mysqli $mysqli): ?object
{
    $ret = "SELECT * FROM system LIMIT 1";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    $sys = $res->fetch_object();
    $stmt->close();
    return $sys ?: null;
}

function fetchSingleAnswer(mysqli $mysqli, int $answerId): ?object
{
    $sql = "
        SELECT
            a.an_id,
            a.q_id,
            a.an_code,
            a.ans_details,
            q.q_code,
            q.q_details,
            q.u_id,
            q.i_id,
            u.u_code,
            u.u_name,
            c.c_id,
            c.c_code,
            c.c_name,
            c.c_dpic AS c_dpic,
            usr.name AS instructor_name
        FROM answers a
        LEFT JOIN questions q ON a.q_id = q.q_id
        LEFT JOIN units u ON q.u_id = u.u_id
        LEFT JOIN courses c ON u.c_id = c.c_id
        LEFT JOIN users usr ON q.i_id = usr.user_id
        WHERE a.an_id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $answerId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_object();
    $stmt->close();
    return $row ?: null;
}

function fetchAnswersByQuestion(mysqli $mysqli, int $questionId): array
{
    $sql = "
        SELECT
            a.an_id,
            a.q_id,
            a.an_code,
            a.ans_details,
            q.q_code,
            q.q_details,
            q.u_id,
            q.i_id,
            u.u_code,
            u.u_name,
            c.c_id,
            c.c_code,
            c.c_name,
            usr.name AS instructor_name
        FROM answers a
        LEFT JOIN questions q ON a.q_id = q.q_id
        LEFT JOIN units u ON q.u_id = u.u_id
        LEFT JOIN courses c ON u.c_id = c.c_id
        LEFT JOIN users usr ON q.i_id = usr.user_id
        WHERE a.q_id = ?
        ORDER BY a.an_id DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $q_id_str = (string) $questionId;
    $stmt->bind_param('s', $q_id_str);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

function userHasCourseAccess(mysqli $mysqli, int $courseId, string $role, int $user_id): bool
{
    if ($role === 'admin') {
        return true;
    }

    if ($role === 'instructor') {
        $sql = "
            SELECT 1
            FROM courses c
            INNER JOIN units u ON c.c_id = u.c_id
            WHERE c.c_id = ? AND u.a_id = ?
            LIMIT 1
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $courseId, $user_id);
    } else {
        $sql = "
            SELECT 1
            FROM enrollments e
            WHERE e.c_id = ? AND e.s_id = ?
            LIMIT 1
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $courseId, $user_id);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $ok = (bool) $res->fetch_row();
    $stmt->close();

    return $ok;
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
        <section class="content pt-3">
            <div class="container-fluid">

                <?php
                if ($answerId > 0) {
                    $answer = fetchSingleAnswer($mysqli, $answerId);

                    if (!$answer) {
                        echo '<div class="alert alert-info" style="background: #84B7F9 !important;border: 1px solid #84B7F9 !important;">Answers Not Found.</div>';
                    } else {
                        $courseIdFromAnswer = (int) ($answer->c_id ?? 0);

                        if ($courseIdFromAnswer > 0 && !userHasCourseAccess($mysqli, $courseIdFromAnswer, $role, $user_id)) {
                            echo '<div class="alert alert-danger">You do not have permission to view this answer.</div>';
                        } else {
                ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-warning" id="Print">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-warning card-outline">
                                                <div class="card-body box-profile">
                                                    <div class="text-center">
                                                        <?php
                                                        $logo = 'Default.png';
                                                        if (!empty($answer->c_dpic)) {
                                                            $logo = $answer->c_dpic;
                                                        }
                                                        ?>
                                                        <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/courses/<?php echo h($logo); ?>" alt="Course Logo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card card-warning card-outline">
                                                <div class="card-body box-profile">
                                                    <ul class="list-group list-group-unbordered mb-3">
                                                        <li class="list-group-item">
                                                            <b>Course Code</b>
                                                            <a class="float-right"><?php echo h($answer->c_code ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Course Name</b>
                                                            <a class="float-right"><?php echo h($answer->c_name ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Unit Code</b>
                                                            <a class="float-right"><?php echo h($answer->u_code ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Unit Name</b>
                                                            <a class="float-right"><?php echo h($answer->u_name ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Instructor</b>
                                                            <a class="float-right"><?php echo h($answer->instructor_name ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Question Code</b>
                                                            <a class="float-right"><?php echo h($answer->q_code ?? ''); ?></a>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <b>Answer Code</b>
                                                            <a class="float-right"><?php echo h($answer->an_code ?? ''); ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <h5><b>Question:</b></h5>
                                        <?php echo nl2br(h($answer->q_details ?? '')); ?>
                                    </div>

                                    <hr>

                                    <div class="mt-3">
                                        <h5><b>Answer:</b></h5>
                                        <?php echo nl2br(h($answer->ans_details ?? '')); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button id="print" onclick="printContent('Print');" type="button" class="btn btn-outline-warning">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
                        }
                    }
                } elseif ($questionId > 0) {
                    $answers = fetchAnswersByQuestion($mysqli, $questionId);

                    /* Fetch question details */
                    $qStmt = $mysqli->prepare("
                        SELECT q.q_id, q.q_code, q.q_details, u.u_name, c.c_name, usr.name AS instructor_name
                        FROM questions q
                        LEFT JOIN units u ON q.u_id = u.u_id
                        LEFT JOIN courses c ON u.c_id = c.c_id
                        LEFT JOIN users usr ON q.i_id = usr.user_id
                        WHERE q.q_id = ?
                        LIMIT 1
                    ");
                    $qStmt->bind_param('i', $questionId);
                    $qStmt->execute();
                    $qRes = $qStmt->get_result();
                    $question = $qRes->fetch_object();
                    $qStmt->close();

                    if (!$question) {
                        echo '<div class="alert alert-info">Question not found.</div>';
                    } else {
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0"><?php echo h($question->q_code ?? ''); ?> - Answers</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Question:</strong><br>
                                <?php echo nl2br(h($question->q_details ?? '')); ?>
                            </div>
                            <hr>
                            <?php if (empty($answers)): ?>
                                <div class="alert alert-info">No answers found for this question.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Answer Code</th>
                                                <th>Question Code</th>
                                                <th>Unit Name</th>
                                                <th>Instructor</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach ($answers as $a): ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo h($a->an_code ?? ''); ?></td>
                                                    <td><?php echo h($a->q_code ?? ''); ?></td>
                                                    <td><?php echo h($a->u_name ?? ''); ?></td>
                                                    <td><?php echo h($a->instructor_name ?? ''); ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="view_answers.php?view=<?php echo (int) $a->an_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    }
                } elseif ($unitId > 0) {
    if ($role === 'instructor') {
        $sql = "
            SELECT
                a.an_id,
                a.q_id,
                a.an_code,
                a.ans_details,
                q.q_code,
                q.u_id,
                q.i_id,
                u.u_code,
                u.u_name,
                c.c_code,
                c.c_name,
                usr.name AS instructor_name
            FROM answers a
            LEFT JOIN questions q ON a.q_id = q.q_id
            INNER JOIN units u ON q.u_id = u.u_id
            LEFT JOIN courses c ON u.c_id = c.c_id
            LEFT JOIN users usr ON q.i_id = usr.user_id
            WHERE q.u_id = ? AND u.a_id = ?
            ORDER BY a.an_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $unitId, $user_id);
    } elseif ($role === 'student') {
        $sql = "
            SELECT
                a.an_id,
                a.q_id,
                a.an_code,
                a.ans_details,
                q.q_code,
                q.u_id,
                q.i_id,
                u.u_code,
                u.u_name,
                c.c_code,
                c.c_name,
                usr.name AS instructor_name
            FROM answers a
            LEFT JOIN questions q ON a.q_id = q.q_id
            INNER JOIN units u ON q.u_id = u.u_id
            LEFT JOIN courses c ON u.c_id = c.c_id
            LEFT JOIN users usr ON q.i_id = usr.user_id
            WHERE q.u_id = ?
            ORDER BY a.an_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $unitId);
    } else {
        $sql = "
            SELECT
                a.an_id,
                a.q_id,
                a.an_code,
                a.ans_details,
                q.q_code,
                q.u_id,
                q.i_id,
                u.u_code,
                u.u_name,
                c.c_code,
                c.c_name,
                usr.name AS instructor_name
            FROM answers a
            LEFT JOIN questions q ON a.q_id = q.q_id
            INNER JOIN units u ON q.u_id = u.u_id
            LEFT JOIN courses c ON u.c_id = c.c_id
            LEFT JOIN users usr ON q.i_id = usr.user_id
            WHERE q.u_id = ?
            ORDER BY a.an_id DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $unitId);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $answers = [];
    while ($row = $res->fetch_object()) {
        $answers[] = $row;
    }
    $stmt->close();
    ?>
    <div class="card">
        <div class="card-body">
            <?php if (empty($answers)): ?>
                <div class="alert alert-info">No answers found for this unit.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Answer Code</th>
                                <th>Question Code</th>
                                <th>Unit Code</th>
                                <th>Unit Name</th>
                                <th>Instructor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($answers as $a): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo h($a->an_code ?? ''); ?></td>
                                    <td><?php echo h($a->q_code ?? ''); ?></td>
                                    <td><?php echo h($a->u_code ?? ''); ?></td>
                                    <td><?php echo h($a->u_name ?? ''); ?></td>
                                    <td><?php echo h($a->instructor_name ?? ''); ?></td>
                                    <td>
                                        <a class="badge badge-outline-warning" href="view_answers.php?view=<?php echo (int) $a->an_id; ?>">
                                            <i class="fas fa-external-link-alt"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
} else {
    echo '<div class="alert alert-info">No answer selected.</div>';
}
                ?>
            </div>
        </section>
    </div>
</div>

<?php require_once('../partials/scripts.php'); ?>
</body>
</html>
<?php } ?>

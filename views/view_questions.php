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

    $questionId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
    if ($questionId <= 0 && isset($_GET['q_id'])) {
        $questionId = (int) $_GET['q_id'];
    }

    $courseId = isset($_GET['course']) ? (int) $_GET['course'] : 0;
    $unitId   = isset($_GET['u_id']) ? (int) $_GET['u_id'] : 0;

    $pageTitle = 'Question View';
    if ($questionId > 0) {
        $pageTitle = 'Question Details';
    } elseif ($courseId > 0) {
        $pageTitle = 'Questions Bank';
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

    function fetchSingleQuestion(mysqli $mysqli, int $questionId): ?object
    {
        $sql = "
            SELECT
                q.q_id,
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
            FROM questions q
            LEFT JOIN units u ON q.u_id = u.u_id
            LEFT JOIN courses c ON u.c_id = c.c_id
            LEFT JOIN users usr ON q.i_id = usr.user_id
            WHERE q.q_id = ? AND q.status <> 'rejected'
            LIMIT 1
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $questionId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_object();
        $stmt->close();
        return $row ?: null;
    }

    function fetchQuestionsByCourse(mysqli $mysqli, int $courseId, string $role, int $user_id): array
    {
        if ($role === 'instructor') {
            $sql = "
                SELECT
                    q.q_id,
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
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE u.c_id = ? AND u.a_id = ? AND q.status <> 'rejected' AND u.status <> 'rejected' AND c.status <> 'rejected'
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ii', $courseId, $user_id);
        } elseif ($role === 'student') {
            $sql = "
                SELECT
                    q.q_id,
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
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE u.c_id = ? AND q.status <> 'rejected' AND u.status <> 'rejected' AND c.status <> 'rejected' AND EXISTS (
                    SELECT 1
                    FROM enrollments e
                    WHERE e.c_id = c.c_id AND e.s_id = ? AND e.status <> 'inactive'
                )
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ii', $courseId, $user_id);
        } else {
            $sql = "
                SELECT
                    q.q_id,
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
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE u.c_id = ? AND q.status <> 'rejected' AND u.status <> 'rejected' AND c.status <> 'rejected'
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $courseId);
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
                    if ($questionId > 0) {
                        $question = fetchSingleQuestion($mysqli, $questionId);

                        if (!$question) {
                            echo '<div class="alert alert-info">Question not found.</div>';
                        } else {
                            $courseIdFromQuestion = (int) ($question->c_id ?? 0);

                            if ($courseIdFromQuestion > 0 && !userHasCourseAccess($mysqli, $courseIdFromQuestion, $role, $user_id)) {
                                echo '<div class="alert alert-danger">You do not have permission to view this question.</div>';
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
                                                            if (!empty($question->c_dpic)) {
                                                                $logo = $question->c_dpic;
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
                                                                <a class="float-right"><?php echo h($question->c_code ?? ''); ?></a>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b>Course Name</b>
                                                                <a class="float-right"><?php echo h($question->c_name ?? ''); ?></a>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b>Unit Code</b>
                                                                <a class="float-right"><?php echo h($question->u_code ?? ''); ?></a>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b>Unit Name</b>
                                                                <a class="float-right"><?php echo h($question->u_name ?? ''); ?></a>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b>Instructor</b>
                                                                <a class="float-right"><?php echo h($question->instructor_name ?? ''); ?></a>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b>Question Code</b>
                                                                <a class="float-right"><?php echo h($question->q_code ?? ''); ?></a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <?php echo nl2br(h($question->q_details ?? '')); ?>
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
                    } elseif ($courseId > 0) {
                        if (!userHasCourseAccess($mysqli, $courseId, $role, $user_id)) {
                            echo '<div class="alert alert-danger">You do not have permission to view these questions.</div>';
                        } else {
                            $questions = fetchQuestionsByCourse($mysqli, $courseId, $role, $user_id);

                            $courseStmt = $mysqli->prepare("
                                SELECT c.c_id, c.c_code, c.c_name, u.name AS instructor_name
                                FROM courses c
                                LEFT JOIN users u ON c.i_id = u.user_id
                                WHERE c.c_id = ?
                                LIMIT 1
                            ");
                            $courseStmt->bind_param('i', $courseId);
                            $courseStmt->execute();
                            $courseRes = $courseStmt->get_result();
                            $course = $courseRes->fetch_object();
                            $courseStmt->close();

                            if (!$course) {
                                echo '<div class="alert alert-info">Course not found.</div>';
                            } else {
                    ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0"><?php echo h($course->c_name ?? ''); ?> - Questions</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($questions)): ?>
                                    <div class="alert alert-info">No questions found for this course.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Question Code</th>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <th>Instructor</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i = 1; foreach ($questions as $q): ?>
                                                    <tr>
                                                        <td><?php echo $i++; ?></td>
                                                        <td><?php echo h($q->q_code ?? ''); ?></td>
                                                        <td><?php echo h($q->u_code ?? ''); ?></td>
                                                        <td><?php echo h($q->u_name ?? ''); ?></td>
                                                        <td><?php echo h($q->instructor_name ?? ''); ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" href="view_questions.php?view=<?php echo (int) $q->q_id; ?>">
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
                        }
                    } elseif ($unitId > 0) {
        if ($role === 'instructor') {
            $sql = "
                SELECT
                    q.q_id,
                    q.q_code,
                    q.q_details,
                    q.u_id,
                    q.i_id,
                    u.u_code,
                    u.u_name,
                    c.c_code,
                    c.c_name,
                    usr.name AS instructor_name
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE q.u_id = ? AND u.a_id = ?
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ii', $unitId, $user_id);
        } elseif ($role === 'student') {
            $sql = "
                SELECT
                    q.q_id,
                    q.q_code,
                    q.q_details,
                    q.u_id,
                    q.i_id,
                    u.u_code,
                    u.u_name,
                    c.c_code,
                    c.c_name,
                    usr.name AS instructor_name
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE q.u_id = ?
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $unitId);
        } else {
            $sql = "
                SELECT
                    q.q_id,
                    q.q_code,
                    q.q_details,
                    q.u_id,
                    q.i_id,
                    u.u_code,
                    u.u_name,
                    c.c_code,
                    c.c_name,
                    usr.name AS instructor_name
                FROM questions q
                INNER JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE q.u_id = ?
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $unitId);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $questions = [];
        while ($row = $res->fetch_object()) {
            $questions[] = $row;
        }
        $stmt->close();
        ?>
        <div class="card">
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <div class="alert alert-info">No questions found for this unit.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question Code</th>
                                    <th>Unit Code</th>
                                    <th>Unit Name</th>
                                    <th>Instructor</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($questions as $q): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo h($q->q_code ?? ''); ?></td>
                                        <td><?php echo h($q->u_code ?? ''); ?></td>
                                        <td><?php echo h($q->u_name ?? ''); ?></td>
                                        <td><?php echo h($q->instructor_name ?? ''); ?></td>
                                        <td>
                                            <a class="badge badge-outline-warning" href="view_questions.php?view=<?php echo (int) $q->q_id; ?>">
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
        echo '<div class="alert alert-info">No question selected.</div>';
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
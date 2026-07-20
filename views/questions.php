<?php
    require_once('../config/config.php');
    require_once('../config/checklogin.php');

    userRoles(['admin', 'instructor', 'student']);

    $role    = $_SESSION['role'];
    $user_id = (int) $_SESSION['user_id'];

    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    function buildQuestionCode(): string
    {
        return 'Q-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
    }

    function fetchUnitsForRole(mysqli $mysqli, string $role, int $user_id): array
    {
        if ($role === 'admin')
        {
            $sql = "
                SELECT
                    u.u_id,
                    u.u_code,
                    u.u_name,
                    u.c_id,
                    c.c_code,
                    c.c_name,
                    u.a_id AS instructor_id,
                    usr.name AS instructor_name
                FROM units u
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON u.a_id = usr.user_id
                WHERE u.status <> 'deleted' AND c.status <> 'deleted'
                ORDER BY u.u_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
        } elseif ($role === 'instructor') {
            $sql = "
                SELECT
                    u.u_id,
                    u.u_code,
                    u.u_name,
                    u.c_id,
                    c.c_code,
                    c.c_name,
                    u.a_id AS instructor_id,
                    usr.name AS instructor_name
                FROM units u
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON u.a_id = usr.user_id
                WHERE u.a_id = ? AND u.status <> 'deleted' AND c.status <> 'deleted'
                ORDER BY u.u_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $user_id);
        } else {
            $sql = "
                SELECT DISTINCT
                    u.u_id,
                    u.u_code,
                    u.u_name,
                    u.c_id,
                    c.c_code,
                    c.c_name,
                    c.i_id AS instructor_id,
                    usr.name AS instructor_name
                FROM enrollments e
                INNER JOIN units u ON e.c_id = u.c_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON c.i_id = usr.user_id
                WHERE e.s_id = ? AND e.status <> 'inactive' AND u.status <> 'deleted' AND c.status <> 'deleted'
                ORDER BY u.u_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    function fetchQuestionsForRole(mysqli $mysqli, string $role, int $user_id): array
    {
        if ($role === 'admin') {
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
                LEFT JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
        } elseif ($role === 'instructor') {
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
                LEFT JOIN units u ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE q.i_id = ?
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $user_id);
        } else {
            $sql = "
                SELECT DISTINCT
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
                FROM enrollments e
                INNER JOIN units u ON e.c_id = u.c_id
                INNER JOIN questions q ON q.u_id = u.u_id
                LEFT JOIN courses c ON u.c_id = c.c_id
                LEFT JOIN users usr ON q.i_id = usr.user_id
                WHERE e.s_id = ?
                ORDER BY q.q_id DESC
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('i', $user_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    /* Add Question */
    if (isset($_POST['add_question_bank']) && ($role === 'admin' || $role === 'instructor')) {
        $u_id      = isset($_POST['u_id']) ? (int) $_POST['u_id'] : 0;
        $q_details = trim($_POST['q_details'] ?? '');
        $q_code    = trim($_POST['q_code'] ?? '');

        if ($u_id <= 0) {
            $_SESSION['flash_error'] = "Unit is required.";
        } elseif ($q_details === '') {
            $_SESSION['flash_error'] = "Question description cannot be empty.";
        } else {
            if ($q_code === '') {
                $q_code = buildQuestionCode();
            }

            $stmt = $mysqli->prepare("
                SELECT
                    u.u_id,
                    u.a_id,
                    c.i_id
                FROM units u
                LEFT JOIN courses c ON u.c_id = c.c_id
                WHERE u.u_id = ?
                LIMIT 1
            ");
            $stmt->bind_param('i', $u_id);
            $stmt->execute();
            $unitRes = $stmt->get_result();
            $unitRow  = $unitRes->fetch_object();
            $stmt->close();

            if (!$unitRow) {
                $_SESSION['flash_error'] = "Selected unit was not found.";
            } else {
                $instructorId = (int) ($unitRow->a_id ?: $unitRow->i_id ?: $user_id);

                $stmt = $mysqli->prepare("SELECT q_id FROM questions WHERE q_code = ? AND status <> 'deleted' LIMIT 1");
                $stmt->bind_param('s', $q_code);
                $stmt->execute();
                $existsRes = $stmt->get_result();
                $exists = $existsRes->fetch_object();
                $stmt->close();

                if ($exists) {
                    $_SESSION['flash_error'] = "A question with this code already exists.";
                } else {
                    $stmt = $mysqli->prepare("
                        INSERT INTO questions (q_code, u_id, i_id, q_details,status)
                        VALUES (?, ?, ?, ?, 'added')
                    ");
                    $u_id_str = (string) $u_id;
                    $i_id_str = (string) $instructorId;
                    $stmt->bind_param('ssss', $q_code, $u_id_str, $i_id_str, $q_details);

                    if ($stmt->execute()) {
                        $_SESSION['flash_success'] = "Question added successfully.";
                    } else {
                        $_SESSION['flash_error'] = "Failed to add question.";
                    }

                    $stmt->close();
                }
            }
        }
    }

    /* Load system settings */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php');

        $units     = fetchUnitsForRole($mysqli, $role, $user_id);
        $questions = fetchQuestionsForRole($mysqli, $role, $user_id);
    ?>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <?php require_once('../partials/navbar.php'); ?>
        <?php require_once('../partials/sidebar.php'); ?>

        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
                    <?php if ($role !== 'student'): ?>
                        <div class="text-right">
                            <a href="manage_questions.php" class="btn btn-outline-warning">
                                Manage Questions
                            </a>
                        </div>
                    <hr>
                    <?php endif; ?>
                    <div class="card">
                        <div class="card-body">
                            
                            <?php if ($role === 'student'): ?>
                                <div class="table-responsive">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Unit Code</th>
                                            <th>Unit Name</th>
                                            <th>Instructor</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($units as $unit): ?>
                                            <tr>
                                                <td><?php echo h($unit->c_code ?? ''); ?></td>
                                                <td><?php echo h($unit->c_name ?? ''); ?></td>
                                                <td><?php echo h($unit->u_code ?? ''); ?></td>
                                                <td><?php echo h($unit->u_name ?? ''); ?></td>
                                                <td><?php echo h($unit->instructor_name ?? ''); ?></td>
                                                <td>
                                                    <a class="badge badge-outline-warning"
                                                    href="view_questions.php?u_id=<?php echo urlencode((string) $unit->u_id); ?>">
                                                        <i class="fas fa-external-link-alt"></i>
                                                        View Questions
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php else: ?>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="table-responsive">
                                            <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Course Name</th>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <?php if ($role === 'admin'): ?>
                                                        <th>Instructor</th>
                                                    <?php endif; ?>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($units as $unit): ?>
                                                    <tr>
                                                        <td><?php echo h($unit->c_code ?? ''); ?></td>
                                                        <td><?php echo h($unit->c_name ?? ''); ?></td>
                                                        <td><?php echo h($unit->u_code ?? ''); ?></td>
                                                        <td><?php echo h($unit->u_name ?? ''); ?></td>
                                                        <?php if ($role === 'admin'): ?>
                                                            <td><?php echo h($unit->instructor_name ?? ''); ?></td>
                                                        <?php endif; ?>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#add-<?php echo (int) $unit->u_id; ?>">
                                                                <i class="fas fa-plus"></i>
                                                                Create Questions
                                                            </a>
                                                            <a class="badge badge-outline-warning" href="view_questions.php?u_id=<?php echo urlencode((string) $unit->u_id); ?>">
                                                                <i class="fas fa-external-link-alt"></i>
                                                                View Questions
                                                            </a>

                                                            <div class="modal fade" id="add-<?php echo (int) $unit->u_id; ?>">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">
                                                                                Create Questions for <?php echo h($unit->u_name ?? ''); ?>
                                                                            </h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form method="post">
                                                                                <input type="hidden" name="u_id" value="<?php echo (int) $unit->u_id; ?>">
                                                                                <input type="hidden" name="q_code" value="<?php echo h(buildQuestionCode()); ?>">

                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Course</label>
                                                                                        <input type="text" class="form-control" readonly value="<?php echo h($unit->c_name ?? ''); ?>">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Unit</label>
                                                                                        <input type="text" class="form-control" readonly value="<?php echo h($unit->u_name ?? ''); ?>">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Unit Code</label>
                                                                                        <input type="text" class="form-control" readonly value="<?php echo h($unit->u_code ?? ''); ?>">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label>Instructor</label>
                                                                                        <input type="text" class="form-control" readonly value="<?php echo h($unit->instructor_name ?? ''); ?>">
                                                                                    </div>
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="q_details_<?php echo (int) $unit->u_id; ?>">Question</label>
                                                                                        <textarea name="q_details" id="q_details_<?php echo (int) $unit->u_id; ?>" class="form-control" rows="5" required></textarea>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="text-right">
                                                                                    <button type="submit" name="add_question_bank" class="btn btn-outline-warning">
                                                                                        Add Question
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <?php require_once('../partials/scripts.php'); ?>
    </body>
    </html>
    <?php
    }
    ?>

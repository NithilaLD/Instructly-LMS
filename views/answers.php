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

    function buildAnswerCode(): string
    {
        return strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 5)) . '-' . mt_rand(10000, 99999);
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

    function fetchAnswerForQuestion(mysqli $mysqli, int $q_id): ?object
    {
        $stmt = $mysqli->prepare("SELECT an_id, q_id, an_code, ans_details FROM answers WHERE q_id = ? LIMIT 1");
        $q_id_str = (string) $q_id;
        $stmt->bind_param('s', $q_id_str);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_object();
        $stmt->close();
        return $row ?: null;
    }

    /* Add Answer */
    if (isset($_POST['add_answer']) && ($role === 'admin' || $role === 'instructor')) {
        $q_id        = isset($_POST['q_id']) ? (int) $_POST['q_id'] : 0;
        $ans_details = trim($_POST['ans_details'] ?? '');
        $an_code     = trim($_POST['an_code'] ?? '');

        if ($q_id <= 0) {
            $err = "Question is required.";
        } elseif ($ans_details === '') {
            $err = "Answer details cannot be empty.";
        } else {
            if ($an_code === '') {
                $an_code = buildAnswerCode();
            }

            /* Check if question exists */
            $stmt = $mysqli->prepare("SELECT q_id FROM questions WHERE q_id = ? LIMIT 1");
            $stmt->bind_param('i', $q_id);
            $stmt->execute();
            $qRes = $stmt->get_result();
            $qRow = $qRes->fetch_object();
            $stmt->close();

            if (!$qRow) {
                $err = "Selected question was not found.";
            } else {
                /* Check if answer already exists for this question */
                $stmt = $mysqli->prepare("SELECT an_id FROM answers WHERE q_id = ? LIMIT 1");
                $q_id_str = (string) $q_id;
                $stmt->bind_param('s', $q_id_str);
                $stmt->execute();
                $existsRes = $stmt->get_result();
                $exists = $existsRes->fetch_object();
                $stmt->close();

                if ($exists) {
                    $err = "An answer for this question already exists.";
                } else {
                    $stmt = $mysqli->prepare("
                        INSERT INTO answers (q_id, an_code, ans_details)
                        VALUES (?, ?, ?)
                    ");
                    $q_id_str = (string) $q_id;
                    $stmt->bind_param('sss', $q_id_str, $an_code, $ans_details);

                    if ($stmt->execute()) {
                        $_SESSION['flash_success'] = "Answer added successfully.";
                    } else {
                        $_SESSION['flash_error'] = "Failed to add answer.";
                    }

                    $stmt->close();
                }
            }
        }
    }

    /* Update Answer */
    if (isset($_POST['update_answer']) && ($role === 'admin' || $role === 'instructor')) {
        $error = 0;

        $ans_details = trim($_POST['ans_details'] ?? '');
        $an_code     = trim($_POST['an_code'] ?? '');
        $an_id       = (int)($_POST['an_id'] ?? 0);

        if ($ans_details === '') {
            $error = 1;
            $err = "Answer Details Cannot Be Empty";
        }

        if ($an_code === '') {
            $error = 1;
            $err = "Answer Code Cannot Be Empty";
        }

        if ($an_id <= 0) {
            $error = 1;
            $err = "Answer ID Cannot Be Empty";
        }

        if (!$error) {
            $query = "UPDATE answers SET ans_details = ?, an_code = ? WHERE an_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ssi', $ans_details, $an_code, $an_id);

            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "Answer Updated Successfully";
                header("Location: answers.php");
                exit;
            } else {
                $_SESSION['flash_error'] = "Failed to update answer.";
                header("Location: answers.php");
                exit;
            }
        }
    }

    /* Delete Answer - Admin only */
    if ($role === 'admin' && isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];

        $stmt = $mysqli->prepare("DELETE FROM answers WHERE an_id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Answer Deleted Successfully";
            header("Location: answers.php");
            exit;
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: answers.php");
            exit;
        }
    }

    /* Load system settings */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php');
        $questions = fetchQuestionsForRole($mysqli, $role, $user_id);
?>
        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <?php require_once('../partials/navbar.php'); ?>
                <?php require_once('../partials/sidebar.php'); ?>

                <div class="content-wrapper">
                    <section class="content pt-3">
                        <div class="container-fluid">
                            <div class="card">
                                <div class="card-body">
                                    <?php if ($role === 'student'): ?>
                                        <div class="table-responsive">
                                            <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>Question Code</th>
                                                    <th>Course Name</th>
                                                    <th>Unit Name</th>
                                                    <th>Instructor</th>
                                                    <th>Answer Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($questions as $question):
                                                    $answer = fetchAnswerForQuestion($mysqli, (int) $question->q_id);
                                                ?>
                                                    <tr>
                                                        <td><?php echo h($question->q_code ?? ''); ?></td>
                                                        <td><?php echo h($question->c_name ?? ''); ?></td>
                                                        <td><?php echo h($question->u_name ?? ''); ?></td>
                                                        <td><?php echo h($question->instructor_name ?? ''); ?></td>
                                                        <td>
                                                            <?php if ($answer): ?>
                                                                <span class="badge badge-success">Answeres Provided</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Answeres Not Provided Yet</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($answer): ?>
                                                                <a class="badge badge-outline-warning"
                                                                href="view_answers.php?view=<?php echo (int) $answer->an_id; ?>">
                                                                    <i class="fas fa-external-link-alt"></i>
                                                                    View Answer
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">No Answer Yet</span>
                                                            <?php endif; ?>
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
                                                            <th>Question Code</th>
                                                            <th>Course Name</th>
                                                            <th>Unit Name</th>
                                                            <th>Instructor</th>
                                                            <th>Answer Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($questions as $question):
                                                            $answer = fetchAnswerForQuestion($mysqli, (int) $question->q_id);
                                                        ?>
                                                            <tr>
                                                                <td><?php echo h($question->q_code ?? ''); ?></td>
                                                                <td><?php echo h($question->c_name ?? ''); ?></td>
                                                                <td><?php echo h($question->u_name ?? ''); ?></td>
                                                                <td><?php echo h($question->instructor_name ?? ''); ?></td>
                                                                <td>
                                                                    <?php if ($answer): ?>
                                                                        <span class="badge badge-success">Answeres Provided</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-secondary">Answeres Not Provided Yet</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (!$answer): ?>
                                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#add-<?php echo (int) $question->q_id; ?>">
                                                                            <i class="fas fa-plus"></i>
                                                                            Create Answers
                                                                        </a>
                                                                        <div class="modal fade" id="add-<?php echo (int) $question->q_id; ?>">
                                                                            <div class="modal-dialog modal-lg">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4 class="modal-title">
                                                                                            Create Answer for <?php echo h($question->q_code ?? ''); ?>
                                                                                        </h4>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <form method="post">
                                                                                            <input type="hidden" name="q_id" value="<?php echo (int) $question->q_id; ?>">
                                                                                            <input type="hidden" name="an_code" value="<?php echo h(buildAnswerCode()); ?>">

                                                                                            <div class="row">
                                                                                                <div class="form-group col-md-6">
                                                                                                    <label>Course</label>
                                                                                                    <input type="text" class="form-control" readonly value="<?php echo h($question->c_name ?? ''); ?>">
                                                                                                </div>
                                                                                                <div class="form-group col-md-6">
                                                                                                    <label>Unit</label>
                                                                                                    <input type="text" class="form-control" readonly value="<?php echo h($question->u_name ?? ''); ?>">
                                                                                                </div>
                                                                                                <div class="form-group col-md-6">
                                                                                                    <label>Question Code</label>
                                                                                                    <input type="text" class="form-control" readonly value="<?php echo h($question->q_code ?? ''); ?>">
                                                                                                </div>
                                                                                                <div class="form-group col-md-6">
                                                                                                    <label>Instructor</label>
                                                                                                    <input type="text" class="form-control" readonly value="<?php echo h($question->instructor_name ?? ''); ?>">
                                                                                                </div>
                                                                                                <div class="form-group col-md-12">
                                                                                                    <label>Question Details</label>
                                                                                                    <textarea class="form-control" rows="3" readonly><?php echo h($question->q_details ?? ''); ?></textarea>
                                                                                                </div>
                                                                                                <div class="form-group col-md-12">
                                                                                                    <label for="ans_details_<?php echo (int) $question->q_id; ?>">Answer</label>
                                                                                                    <textarea name="ans_details" id="ans_details_<?php echo (int) $question->q_id; ?>" class="form-control" rows="5" required></textarea>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="text-right">
                                                                                                <button type="submit" name="add_answer" class="btn btn-outline-warning">
                                                                                                    Add Answer
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
                                                                    <?php endif; ?>
                                                                    <?php if ($answer): ?>
                                                                        <a class="badge badge-outline-warning" href="view_answers.php?view=<?php echo (int) $answer->an_id; ?>">
                                                                            <i class="fas fa-external-link-alt"></i>
                                                                            View Answers
                                                                        </a>
                                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#edit-<?php echo (int)$answer->an_id; ?>">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                            Update Answer
                                                                        </a>

                                                                        <div class="modal fade" id="edit-<?php echo (int)$answer->an_id; ?>">
                                                                            <div class="modal-dialog modal-lg">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4 class="modal-title">Edit Answer <?php echo h($answer->an_code); ?></h4>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <form method="post" enctype="multipart/form-data">
                                                                                            <div class="row">
                                                                                                <div class="form-group col-md-12">
                                                                                                    <label for="an_code">Answer Code</label>
                                                                                                    <input type="hidden" name="an_id" value="<?php echo (int)$answer->an_id; ?>" class="form-control">
                                                                                                    <input type="text" name="an_code" value="<?php echo h($answer->an_code); ?>" readonly required class="form-control" id="an_code">
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="row">
                                                                                                <div class="form-group col-md-12">
                                                                                                    <label>Question Code</label>
                                                                                                    <input type="text" class="form-control" readonly value="<?php echo h($answer->q_code ?? ''); ?>">
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="row">
                                                                                                <div class="form-group col-md-12">
                                                                                                    <label for="editor-<?php echo (int)$answer->an_id; ?>">Answer Details</label>
                                                                                                    <textarea name="ans_details" class="form-control" id="editor-<?php echo (int)$answer->an_id; ?>" rows="6"><?php echo h($answer->ans_details); ?></textarea>
                                                                                                </div>
                                                                                            </div>

                                                                                            <hr>
                                                                                            <div class="text-right">
                                                                                                <button type="submit" name="update_answer" class="btn btn-outline-warning">Update Answer</button>
                                                                                            </div>
                                                                                        </form>
                                                                                    </div>
                                                                                    <div class="modal-footer justify-content-between">
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <?php if ($role === 'admin') { ?>
                                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo (int)$answer->an_id; ?>">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                                Delete Answer
                                                                            </a>

                                                                            <div class="modal fade" id="delete-<?php echo (int)$answer->an_id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title">CONFIRM</h5>
                                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                                <span aria-hidden="true">&times;</span>
                                                                                            </button>
                                                                                        </div>
                                                                                        <div class="modal-body text-center text-danger">
                                                                                            <h4>Delete <?php echo h($answer->an_code); ?> Answer?</h4>
                                                                                            <br>
                                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                                            <a href="answers.php?delete=<?php echo (int)$answer->an_id; ?>" class="text-center btn btn-outline-warning">Delete</a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php 
                                                                            } 
                                                                        endif;
                                                                    ?>
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

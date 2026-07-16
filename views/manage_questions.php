<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    $role    = $_SESSION['role'];
    $user_id = (int) $_SESSION['user_id'];

    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    require_once('../config/codeGen.php');

    /* Update Question */
    if (isset($_POST['update_question_bank'])) {
        $error = 0;

        $q_details = trim($_POST['q_details'] ?? '');
        $q_code    = trim($_POST['q_code'] ?? '');
        $q_id      = (int)($_POST['q_id'] ?? 0);

        if ($q_details === '') {
            $error = 1;
            $err = "Question Description Cannot Be Empty";
        }

        if ($q_code === '') {
            $error = 1;
            $err = "Question Code Cannot Be Empty";
        }

        if ($q_id <= 0) {
            $error = 1;
            $err = "Question ID Cannot Be Empty";
        }

        if (!$error) {
            $query = "UPDATE questions SET q_details = ?, q_code = ? WHERE q_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ssi', $q_details, $q_code, $q_id);

            if ($stmt->execute()) {
                logAuditAction($mysqli, 'update_question', 'Question updated', 'questions', 'question', (string) $q_id);
                $_SESSION['flash_success'] = "Question updated successfully.";
                header("Location: manage_questions.php");
                exit;
            } else {
                $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                header("Location: manage_questions.php");
                exit;
            }
        }
    }

    /* Delete Question - Admin only */
    if ($role === 'admin' && isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];

        $stmt = $mysqli->prepare("UPDATE questions SET status = 'deleted' WHERE q_id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'delete_question', 'Question deleted', 'questions', 'question', (string) $id);
            $_SESSION['flash_success'] = "Question deleted successfully.";
            header("Location: manage_questions.php");
            exit;
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: manage_questions.php");
            exit;
        }

        $stmt->close();
    }

    /* Load system settings */
    $ret = "SELECT * FROM system LIMIT 1";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php');
    ?>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <?php
            require_once('../partials/navbar.php');
            require_once('../partials/sidebar.php');
        ?>

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body">
                                <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Question Code</th>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Unit Code</th>
                                            <th>Unit Name</th>
                                            <th>Manage Question</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
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
                                                    c.c_name
                                                FROM questions q
                                                LEFT JOIN units u ON q.u_id = u.u_id
                                                LEFT JOIN courses c ON u.c_id = c.c_id
                                                WHERE q.status <> 'deleted'
                                                ORDER BY q.q_id DESC
                                            ";
                                            $stmt = $mysqli->prepare($sql);
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
                                                    c.c_name
                                                FROM questions q
                                                INNER JOIN units u ON q.u_id = u.u_id
                                                LEFT JOIN courses c ON u.c_id = c.c_id
                                                WHERE q.i_id = ? AND q.status <> 'deleted'
                                                ORDER BY q.q_id DESC
                                            ";
                                            $stmt = $mysqli->prepare($sql);
                                            $stmt->bind_param('i', $user_id);
                                        }

                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        while ($question = $res->fetch_object()) {
                                        ?>
                                            <tr>
                                                <td><?php echo h($question->q_code ?? ''); ?></td>
                                                <td><?php echo h($question->c_code ?? ''); ?></td>
                                                <td><?php echo h($question->c_name ?? ''); ?></td>
                                                <td><?php echo h($question->u_code ?? ''); ?></td>
                                                <td><?php echo h($question->u_name ?? ''); ?></td>
                                                <td>
                                                    <a class="badge badge-outline-warning" data-toggle="modal" href="#edit-<?php echo (int)$question->q_id; ?>">
                                                        <i class="fas fa-pencil-alt"></i>
                                                        Update
                                                    </a>

                                                    <div class="modal fade" id="edit-<?php echo (int)$question->q_id; ?>">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">Edit Questions <?php echo h($question->q_code); ?></h4>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <form method="post" enctype="multipart/form-data">
                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <label for="q_code">Question Code</label>
                                                                                <input type="hidden" name="q_id" value="<?php echo (int)$question->q_id; ?>" class="form-control">
                                                                                <input type="text" name="q_code" value="<?php echo h($question->q_code); ?>" readonly required class="form-control" id="q_code">
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <label for="editor-<?php echo (int)$question->q_id; ?>">Questions</label>
                                                                                <textarea name="q_details" class="form-control" id="editor-<?php echo (int)$question->q_id; ?>" rows="6"><?php echo h($question->q_details); ?></textarea>
                                                                            </div>
                                                                        </div>

                                                                        <hr>
                                                                        <div class="text-right">
                                                                            <button type="submit" name="update_question_bank" class="btn btn-outline-warning">Update Questions</button>
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
                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo (int)$question->q_id; ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Delete
                                                        </a>

                                                        <div class="modal fade" id="delete-<?php echo (int)$question->q_id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">CONFIRM</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center text-danger">
                                                                        <h4>Delete <?php echo h($question->q_code); ?> Question?</h4>
                                                                        <br>
                                                                        <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                        <a href="manage_questions.php?delete=<?php echo (int)$question->q_id; ?>" class="text-center btn btn-outline-warning">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        $stmt->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php require_once('../partials/scripts.php'); ?>
    </body>
    </html>
    <?php } ?>
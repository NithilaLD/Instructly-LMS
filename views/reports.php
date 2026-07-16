<?php
    require_once('../config/config.php');
    require_once('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    $report = $_GET['report'] ?? '';
    $allowedReports = [];

    if ($role === 'admin')
    {
        $allowedReports = [
            'students',
            'instructors',
            'courses',
            'units',
            'allocations',
            'enrollments',
            'billings',
        ];
    }
    elseif ($role === 'instructor')
    {
        $allowedReports = [
            'allocations',
            'enrollments',
            'units',
            'billings',
        ];
    }

    if (!in_array($report, $allowedReports, true)) {$report = '';}
    function h(string $value): string {return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');}

    function renderDropdown(array $options, string $selected): void
    {
        echo '<form method="get" class="mb-3">';
        echo '<input type="hidden" name="report" value="'.h($selected).'">';
        echo '<select name="report" class="form-control" onchange="this.form.submit()">';
        foreach ($options as $key => $label)
        {
            $sel = $selected === $key ? ' selected' : '';
            echo '<option value="'.h($key).'"'.$sel.'>'.h($label).'</option>';
        }
        echo '</select>';
        echo '</form>';
    }

    function renderTableStart(string $id, array $headers): void
    {
        echo '<table id="'.h($id).'" class="table table-bordered table-striped">';
        echo '<thead><tr>';
        foreach ($headers as $header) {echo '<th>'.h($header).'</th>';}
        echo '</tr></thead><tbody>';
    }

    function renderTableEnd(): void {echo '</tbody></table>';}

    function emptyState(string $message): void {echo '<div class="alert alert-info">'.h($message).'</div>';}

    $roleReportLabels = [];
    if ($role === 'admin')
    {
        $roleReportLabels = [
            'students' => 'Students',
            'instructors' => 'Instructors',
            'courses' => 'Courses',
            'units' => 'Units',
            'allocations' => 'Allocations',
            'enrollments' => 'Enrollments',
            'billings' => 'Billings',
        ];
    }
    elseif ($role === 'instructor')
    {
        $roleReportLabels = [
            'allocations' => 'Courses',
            'enrollments' => 'Enrollments',
            'units' => 'Units',
            'billings' => 'Billings',
        ];
    }

    $pageTitle = $reportLabels[$report] ?? 'Reports';
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php');
?>
        <body class="hold-transition sidebar-mini">
            <div class="wrapper">
                <?php include('../partials/navbar.php'); ?>
                <?php include('../partials/sidebar.php'); ?>
                <div class="content-wrapper pt-2" style="margin-bottom: 0 !important;">
                    <section class="content">
                        <div class="container-fluid">
                            <div class="card report-card">
                                <div class="card-header" style="padding: 0.75rem 1.25rem !important;">
                                    <div class="report-toolbar">
                                        <div style="min-width:260px; width: 100%; max-width: 360px;">
                                            <select id="reportFilter" class="form-control select2bs4" onchange="window.location.href='?report='+this.value">
                                                <option value="">Please Select a Report</option>
                                                <?php foreach ($roleReportLabels as $key => $label): ?>
                                                    <option value="<?php echo h($key); ?>" <?php echo $report === $key ? 'selected' : ''; ?>><?php echo h($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-body" style="padding-top: 0 !important;">
                                        <?php   if ($role === 'admin'):
                                                    if ($report === 'students'): 
                                                        renderTableStart('reports', ['No.', 'Student Code', 'Name', 'Email', 'Phone']);
                                                        $stmt = $mysqli->prepare("SELECT user_code, name, email, phone FROM users WHERE role = 'student' AND status = 'active' ORDER BY user_code ASC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['user_code']); ?></td>
                                                                <td><?php echo h($row['name']); ?></td>
                                                                <td><?php echo h($row['email']); ?></td>
                                                                <td><?php echo h($row['phone']); ?></td>
                                                            </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'instructors'): 
                                                        renderTableStart('reports', ['No.', 'Instructor Code', 'Name', 'Email', 'Phone']);
                                                        $stmt = $mysqli->prepare("SELECT user_code, name, email, phone FROM users WHERE role = 'instructor' AND status = 'active' ORDER BY user_code ASC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['user_code']); ?></td>
                                                                <td><?php echo h($row['name']); ?></td>
                                                                <td><?php echo h($row['email']); ?></td>
                                                                <td><?php echo h($row['phone']); ?></td>
                                                            </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'courses'):
                                                        renderTableStart('reports', ['No.', 'Course Code', 'Course Name', 'Instructor']);
                                                        $stmt = $mysqli->prepare("SELECT c.c_code, c.c_name, u.name AS i_name FROM courses c LEFT JOIN users u ON c.i_id = u.user_id WHERE c.status <> 'rejected' AND u.status = 'active' ORDER BY c.c_id DESC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['c_code']); ?></td>
                                                                <td><?php echo h($row['c_name']); ?></td>
                                                                <td><?php if ($row['i_name']): echo h($row['i_name']); else: echo 'N/A'; endif; ?></td>
                                                            </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'units'):
                                                        renderTableStart('reports', ['No.', 'Unit Code', 'Unit Name', 'Course']);
                                                        $stmt = $mysqli->prepare("SELECT u.u_code, u.u_name, c.c_name FROM units u LEFT JOIN courses c ON u.c_id = c.c_id WHERE u.status <> 'rejected' AND c.status <> 'rejected' ORDER BY u.u_id DESC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo h($row['u_code']); ?></td>
                                                            <td><?php echo h($row['u_name']); ?></td>
                                                            <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                        </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'allocations'):
                                                        renderTableStart('reports', ['No.', 'Course', 'Instructor']);
                                                        $stmt = $mysqli->prepare("
                                                            SELECT
                                                                c.c_code,
                                                                c.c_name,
                                                                i.name AS i_name
                                                            FROM courses c
                                                            LEFT JOIN users i ON c.i_id = i.user_id
                                                            WHERE c.status <> 'rejected' AND i.status = 'active'
                                                            ORDER BY c.c_id DESC
                                                        ");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                                <td><?php if (!empty($row['i_name'])){ ?><?php echo h($row['i_name']); ?><?php } else{ ?>N/A<?php } ?></td>
                                                            </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd(); 
                                                    elseif ($report === 'enrollments'): 
                                                        renderTableStart('reports', ['No.', 'Student', 'Course', 'Enrolled On']);
                                                        $stmt = $mysqli->prepare("SELECT e.en_date, s.name AS s_name, c.c_name FROM enrollments e LEFT JOIN users s ON e.s_id = s.user_id LEFT JOIN courses c ON e.c_id = c.c_id WHERE e.status <> 'inactive' AND s.status = 'active' AND c.status <> 'rejected' ORDER BY e.en_id DESC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo h($row['s_name'] ?? ''); ?></td>
                                                            <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                            <td><?php echo h($row['en_date'] ?? ''); ?></td>
                                                        </tr>
                                        <?php 
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'billings'):
                                                        renderTableStart('reports', ['No.', 'Order ID', 'Student', 'Amount', 'Date']);
                                                        $stmt = $mysqli->prepare("SELECT p.order_id, p.p_amt, p.p_date_paid, s.name AS s_name FROM payments p LEFT JOIN users s ON p.s_id = s.user_id ORDER BY p.psm_id DESC");
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['order_id'] ?? ''); ?></td>
                                                                <td><?php echo h($row['s_name'] ?? ''); ?></td>
                                                                <td><?php echo h($row['p_amt'] ?? ''); ?></td>
                                                                <td><?php echo h($row['p_date_paid'] ?? ''); ?></td>
                                                            </tr>
                                        <?php
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    endif;
                                                elseif ($role === 'instructor'):
                                                    if ($report === 'allocations'):
                                                        renderTableStart('reports', ['No.', 'Course', 'Instructor']);
                                                        $stmt = $mysqli->prepare("SELECT c.c_name, i.name AS i_name FROM courses c LEFT JOIN users i ON c.i_id = i.user_id WHERE c.i_id = ? AND c.status <> 'rejected' AND i.status = 'active' ORDER BY c.c_id DESC");
                                                        $stmt->bind_param('i', $user_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                                <td><?php echo h($row['i_name'] ?? ''); ?></td>
                                                            </tr>
                                        <?php 
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'enrollments'):
                                                    renderTableStart('reports', ['No.', 'Student', 'Course', 'Enrolled On']);
                                                        $stmt = $mysqli->prepare("SELECT e.en_date, s.name AS s_name, c.c_name FROM enrollments e LEFT JOIN users s ON e.s_id = s.user_id LEFT JOIN courses c ON e.c_id = c.c_id WHERE c.i_id = ? AND e.status <> 'inactive' AND s.status = 'active' AND c.status <> 'rejected' ORDER BY e.en_id DESC");
                                                        $stmt->bind_param('i', $user_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['s_name'] ?? ''); ?></td>
                                                                <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                                <td><?php echo h($row['en_date'] ?? ''); ?></td>
                                                            </tr>
                                        <?php
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'units'):
                                                        renderTableStart('reports', ['No.', 'Unit Code', 'Unit Name', 'Course']);
                                                        $stmt = $mysqli->prepare("SELECT u.u_code, u.u_name, c.c_name FROM units u LEFT JOIN courses c ON u.c_id = c.c_id WHERE c.i_id = ? AND u.status <> 'rejected' AND c.status <> 'rejected' ORDER BY u.u_id DESC");
                                                        $stmt->bind_param('i', $user_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                                        <tr>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo h($row['u_code']); ?></td>
                                                            <td><?php echo h($row['u_name']); ?></td>
                                                            <td><?php echo h($row['c_name'] ?? ''); ?></td>
                                                        </tr>
                                        <?php       
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    elseif ($report === 'billings'): 
                                                        renderTableStart('reports', ['No.', 'Order ID', 'Student', 'Amount', 'Date']);
                                                        $stmt = $mysqli->prepare("SELECT p.order_id, p.p_amt, p.p_date_paid, s.name AS s_name FROM payments p LEFT JOIN users s ON p.s_id = s.user_id LEFT JOIN materials m ON p.m_id = m.m_id LEFT JOIN units u ON m.u_id = u.u_id LEFT JOIN courses c ON u.c_id = c.c_id WHERE c.i_id = ? ORDER BY p.psm_id DESC");
                                                        $stmt->bind_param('i', $user_id);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $i = 1;
                                                        while ($row = $result->fetch_assoc()):
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo h($row['order_id'] ?? ''); ?></td>
                                                                <td><?php echo h($row['s_name'] ?? ''); ?></td>
                                                                <td><?php echo h($row['p_amt'] ?? ''); ?></td>
                                                                <td><?php echo h($row['p_date_paid'] ?? ''); ?></td>
                                                            </tr>
                                        <?php
                                                        endwhile; $stmt->close();
                                                        renderTableEnd();
                                                    endif;
                                                endif; 
                                        ?>
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
<?php 
    }
?>
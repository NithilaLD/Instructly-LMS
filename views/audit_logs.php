<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRole('admin');
    $ret = "SELECT * FROM system";
    $sysStmt = $mysqli->prepare($ret);
    $sysStmt->execute();
    $sysRes = $sysStmt->get_result();
    while ($sys = $sysRes->fetch_object())
    {
        require_once('../partials/head.php');
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
                                    <div class="table-responsive">
                                        <table id="audit-logs" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Role</th>
                                                    <th>Action</th>
                                                    <th>Details</th>
                                                    <th>Logged At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $query = " SELECT * FROM logs ORDER BY created_at DESC ";
                                            $result = $mysqli->query($query);
                                            while ($row = $result->fetch_assoc())
                                            {
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['user_name']; ?></td>
                                                    <td><?php echo $row['role']; ?></td>
                                                    <td><?php echo $row['action']; ?></td>
                                                    <td><?php echo $row['details']; ?></td>
                                                    <td><?php echo date('d M Y g:i', strtotime($row['created_at'])); ?></td>
                                                </tr>
                                            <?php } ?>
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
                        <script>
                            $(function() {
                                $('#audit-logs').DataTable({
                                    order: [],
                                    initComplete: function () {
                                        var tableId = $(this).attr('id');
                                        var $searchInput = $('#' + tableId + '_filter input');

                                        $searchInput.attr({
                                            id: tableId + '_search',
                                            autocomplete: 'off',
                                            'aria-label': 'Search table ' + tableId
                                        });
                                    }
                                });
                            });
                        </script>
        </body>
        </html>
<?php 
    } 
?>
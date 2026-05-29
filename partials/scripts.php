<!-- jQuery -->
<script src="../public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!--  App Js -->
<script src="../public/js/adminlte.min.js"></script>
<!-- Swal Scripts -->
<script src="../public/js/swal.js"></script>
<style>
  .swal-button--confirm {
    background-color: #007bff !important;
    border-color: #007bff !important;
    border-radius: .25rem;
  }

  .swal-button--confirm:hover {
    background-color: #0069d9 !important;
    border-color: #0069d9 !important;
  }
</style>
<!-- Init Swal To Inject Errors -->
<?php if (isset($success)) { ?>
  <!--This code for injecting an alert-->
  <script>
    setTimeout(function() {
        swal("Success", "<?php echo $success; ?>", "success");
      },
      100);
  </script>

<?php } ?>

<?php if (isset($err)) { ?>
  <!--This code for injecting an alert-->
  <script>
    setTimeout(function() {
        swal("Failed", "<?php echo $err; ?>", "error");
      },
      100);
  </script>

<?php } ?>


<!-- overlayScrollbars -->
<script src="../public/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- Dashboard -->
<script src="../public/js/pages/dashboard2.js"></script>
<!-- DataTables -->
<script src="../public/plugins/datatables/jquery.dataTables.js"></script>
<script src="../public/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<!-- Advanced Reporting Data Tables  -->
<!-- NOTE TO Use Copy CSV Excel PDF Print Options You Must Include These Files  -->
<script src="../public/plugins/datatable/button-ext/dataTables.buttons.min.js"></script>
<script src="../public/plugins/datatable/button-ext/jszip.min.js"></script>
<script src="../public/plugins/datatable/button-ext/buttons.html5.min.js"></script>
<script src="../public/plugins/datatable/button-ext/buttons.print.min.js"></script>
<!-- html2pdf for direct PDF generation (used for forced filename downloads) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script>
  // Helper: produce a friendly report title for a specific table
  function getReportTitle($table) {
    var title = '';

    if ($table && $table.length) {
      title = $table.closest('.card').find('.card-header h5, .card-header h4, .card-header h3').first().text().trim();
      if (!title) {
        title = $table.closest('.card').find('h1, h2, h3, h4, h5').first().text().trim();
      }
    }

    if (!title) {
      title = $('h1').first().text().trim() || $('h3').first().text().trim();
    }

    var p = window.location.pathname;
    var baseName = p.substring(p.lastIndexOf('/') + 1).replace('.php', '');

    var nameMap = {
      'ins_reports_allocations': 'Unit Allocations',
      'ins_reports_student_enrollments': 'Unit Enrollments',
      'ins_reports_students': 'Students',
      'ins_reports_billings': 'Billings',
      'reports_allocations': 'Unit Allocations',
      'reports_student_enrollments': 'Unit Enrollments',
      'reports_students': 'Students',
      'reports_billings': 'Billings',
      'reports_instructors': 'Instructors',
      'reports_courses': 'Courses',
      'reports_units': 'Units',
    };

    return title || nameMap[baseName] || baseName;
  }

  // Helper: produce report filename like "Report_Name_YYYY-MM-DD"
  function getReportFilename($table) {
    var friendly = getReportTitle($table);
    var base = friendly.replace(/\s+/g, '_');

    // Remove trailing 'reports' or 'report' and any trailing digits like 'reports2'
    base = base.replace(/_?reports?\d*$/i, '');

    // Also remove the literal word 'reports' if it appears as a separate token
    base = base.replace(/(^|_)reports(_|$)/ig, '$1$2');

    // Trim any leftover underscores at ends
    base = base.replace(/^_+|_+$/g, '');

    var d = new Date();
    var yyyy = d.getFullYear();
    var mm = String(d.getMonth()+1).padStart(2,'0');
    var dd = String(d.getDate()).padStart(2,'0');
    return base + '_' + yyyy + '-' + mm + '-' + dd;
  }

  function hasReportRows(dt) {
    return dt && dt.rows && dt.rows().count() > 0;
  }

  function showNoReportDataMessage() {
    swal('Failed', 'No records found to export.', 'error');
  }

  function initReportTable(selector) {
    if (!$(selector).length) {
      return;
    }

    var $table = $(selector);
    // Export every visible column except those with no-export class
    var exportColsSelector = ':visible:not(.no-export)';

    $table.DataTable({
      dom: '<"row"<"col-md-12"B> ><"row"<"col-md-12"<"row"<"col-md-6"><"col-md-6"f> > ><"col-md-12"rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>> >',
        buttons: {
        buttons: [{
            extend: 'copy',
            className: 'btn',
            title: function() {
              return getReportTitle($table);
            },
            messageTop: '',
            exportOptions: { columns: exportColsSelector },
            action: function(e, dt, node, config) {
              if (!hasReportRows(dt)) {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, node, config);
            }
          },
          {
            extend: 'csv',
            className: 'btn',
            filename: function() {
              return getReportFilename($table);
            },
            title: null,
            messageTop: '',
            exportOptions: { columns: exportColsSelector },
            action: function(e, dt, node, config) {
              if (!hasReportRows(dt)) {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, node, config);
            }
          },
          {
            extend: 'excel',
            className: 'btn',
            filename: function() {
              return getReportFilename($table);
            },
            title: null,
            messageTop: '',
            exportOptions: { columns: exportColsSelector },
            action: function(e, dt, node, config) {
              if (!hasReportRows(dt)) {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
            }
          },
          {
            text: 'PDF',
            className: 'btn',
            action: function(e, dt, node, config) {
              if (!hasReportRows(dt)) {
                showNoReportDataMessage();
                return;
              }
              var filename = getReportFilename($table) + '.pdf';
              // Build a temporary container without the page title heading
              var $wrap = $('<div/>').css({ padding: '10px', 'font-family': 'Arial, sans-serif' });

              // Clone the table node
              var tableNode = dt.table().node();
              var $tableClone = $(tableNode).clone();

              // Append cloned table and trigger html2pdf
              $wrap.append($tableClone);

              var opt = {
                margin:       10,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
              };

              // Generate and save PDF directly
              html2pdf().set(opt).from($wrap.get(0)).save();
            }
          },
        ]
      },
      "oLanguage": {
        "oPaginate": {
          "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
          "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
        },
        "sInfo": "Showing Page _PAGE_ of _PAGES_",
        "sSearch": "",
        "sSearchPlaceholder": "Search...",
        "sLengthMenu": "Results :  _MENU_",
      },
      "stripeClasses": [],
      "lengthMenu": [7, 10, 20, 50],
      "pageLength": 7,
      "initComplete": function () {
        var tableId = $(this).attr('id');
        var $searchInput = $('#' + tableId + '_filter input');

        $searchInput.attr({
          id: tableId + '_search',
          autocomplete: 'off',
          'aria-label': 'Search table ' + tableId
        });
      }
    });
  }

  initReportTable('#reports');
  initReportTable('#reports2');
  initReportTable('#reports3');
</script>
<!-- Init Data Tables -->
<script>
  $(function() {
    $("#dash-1").DataTable({
      "initComplete": function () {
        var tableId = $(this).attr('id');
        var $searchInput = $('#' + tableId + '_filter input');

        $searchInput.attr({
          id: tableId + '_search',
          autocomplete: 'off',
          'aria-label': 'Search table ' + tableId
        });
      }
    });
    $("#dash-2").DataTable({
      "initComplete": function () {
        var tableId = $(this).attr('id');
        var $searchInput = $('#' + tableId + '_filter input');

        $searchInput.attr({
          id: tableId + '_search',
          autocomplete: 'off',
          'aria-label': 'Search table ' + tableId
        });
      }
    });
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
    });
  });
</script>

<!-- File Uploads  -->
<script src="../public/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script>
  $(document).ready(function() {
    bsCustomFileInput.init();
  });
</script>
<!-- Select 2  -->
<script src="../public/plugins/select2/js/select2.full.min.js"></script>
<script>
  $('.select2bs4').select2({
    theme: 'bootstrap4'
  })
</script>
<!-- Ajaxes -->
<script>
  /* Course Details */
  function GetCourseDetails(val) {
    $.ajax({

      type: "POST",
      url: "../partials/ajax.php",
      data: 'Course_Code=' + val,
      success: function(data) {
        //alert(data);
        $('#Course_Id').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Course_Id=' + val,
      success: function(data) {
        //alert(data);
        $('#Course_Name').val(data);
      }
    });
  }

  /* Unit Details */
  function GetUnitDetails(val) {
    $.ajax({

      type: "POST",
      url: "../partials/ajax.php",
      data: 'Unit_Code=' + val,
      success: function(data) {
        //alert(data);
        $('#Unit_Name').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Unit_Name=' + val,
      success: function(data) {
        //alert(data);
        $('#Unit_Id').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Unit_Id=' + val,
      success: function(data) {
        //alert(data);
        $('#Course_Id').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Course_Id=' + val,
      success: function(data) {
        //alert(data);
        $('#Course_Name').val(data);
      }
    });
  }

  /* Instructor Details */
  function GetInstructorDetails(val) {
    $.ajax({

      type: "POST",
      url: "../partials/ajax.php",
      data: 'Ins_Number=' + val,
      success: function(data) {
        //alert(data);
        $('#Ins_Name').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Ins_Name=' + val,
      success: function(data) {
        //alert(data);
        $('#Ins_Id').val(data);
      }
    });
  }

  /* Allocation Details */
  function GetAllocatedUnitDetails(val) {
    $.ajax({

      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Unit_Code=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Unit_Name').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Unit_Name=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Ins_Name').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Ins_Name=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Course_ID').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Course_ID=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Unit_ID').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Unit_ID=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Ins_ID').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Allocated_Ins_ID=' + val,
      success: function(data) {
        //alert(data);
        $('#Allocated_Course_Name').val(data);
      }
    });


  }

  /* Student  Details */
  function GetStudentDetails(val) {
    $.ajax({

      type: "POST",
      url: "../partials/ajax.php",
      data: 'Std_Admn=' + val,
      success: function(data) {
        //alert(data);
        $('#Std_Name').val(data);
      }
    });

    $.ajax({
      type: "POST",
      url: "../partials/ajax.php",
      data: 'Std_Name=' + val,
      success: function(data) {
        //alert(data);
        $('#Std_Id').val(data);
      }
    });
  }
</script>
<!-- Print Contents Inside A Div -->
<script>
  function printContent(el) {
    var restorepage = $('body').html();
    var printcontent = $('#' + el).clone();
    $('body').empty().html(printcontent);
    window.print();
    $('body').html(restorepage);
  }
</script>

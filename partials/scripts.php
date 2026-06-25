<script src="../public/plugins/jquery/jquery.min.js"></script>
<script src="../public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/adminlte.min.js"></script>
<script src="../public/js/swal.js"></script>
<script src="../public/plugins/datatables/jquery.dataTables.js"></script>
<script src="../public/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script src="../public/plugins/datatable/button-ext/dataTables.buttons.min.js"></script>
<script src="../public/plugins/datatable/button-ext/jszip.min.js"></script>
<script src="../public/plugins/datatable/button-ext/buttons.html5.min.js"></script>
<script src="../public/plugins/datatable/button-ext/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script src="../public/plugins/select2/js/select2.full.min.js"></script>
<?php 
  if (!empty($_SESSION['flash_success']))
  {
    echo '<script>setTimeout(function(){swal("Success", ' . json_encode($_SESSION['flash_success']) . ', "success");},100);</script>';
    unset($_SESSION['flash_success']);
  }
  if (!empty($_SESSION['flash_error']))
  {
      echo '<script>setTimeout(function(){swal("Failed", ' . json_encode($_SESSION['flash_error']) . ', "error");},100);</script>';
      unset($_SESSION['flash_error']);
  }
?>
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
  $('.select2bs4').select2({  theme: 'bootstrap4'  })

  function GetCourseDetails(val)
  {
    $.post('../partials/ajax.php', { Course_Code: val }, function(data)
    {
        const parts = data.split('|');
        $('#Course_Id').val(parts[0]);
        $('#Course_Name').val(parts[1]);
      });
  }

  function printContent(el)
  {
    var restorepage = $('body').html();
    var printcontent = $('#' + el).clone();
    $('body').empty().html(printcontent);
    window.print();
    $('body').html(restorepage);
  }

  function getReportTitle() {return $('#reportFilter option:selected').text();}
  
  function getReportFilename()
  {
    var base = getReportTitle();
    base = base.replace(/\s+/g, '_');
    base = base.replace(/^_+|_+$/g, '');
    if (!base) { base = 'Report'; }
    var d = new Date();
    var yyyy = d.getFullYear();
    var mm = String(d.getMonth() + 1).padStart(2, '0');
    var dd = String(d.getDate()).padStart(2, '0');

    return base + '_' + yyyy + '-' + mm + '-' + dd;
  }

  function hasReportRows(dt) {return dt && dt.rows && dt.rows().count() > 0;}

  function showNoReportDataMessage() {swal('Failed', 'No records found to export.', 'error');}

  function initReportTable(selector)
  {
    if (!$(selector).length) {return;}
    var $table = $(selector);
    var exportColsSelector = ':visible:not(.no-export)';
    $table.DataTable({
      dom: '<"row"<"col-md-12 text-right"B> ><"row"<"col-md-12"<"row"<"col-md-6"><"col-md-6"> > ><"col-md-12"rt> <"col-md-12"<"row"<"col-md-5"i><"col-md-7"p>>> >',
        buttons: {
        buttons: [{
            extend: 'copy',
            className: 'btn',
            title: function() {return getReportTitle();},
            messageTop: '',
            exportOptions: {
              columns: exportColsSelector,
              format: {
                header: function (data, columnIdx) {
                  return columnIdx === 0 ? 'No.' : data;
                }
              }
            },
            action: function(e, dt, node, config)
            {
              if (!hasReportRows(dt))
              {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, node, config);
              setTimeout(function () {
                const $info = $('div.dt-button-info');

                $info.html(`<h2 style="margin:0;font-size:20px;font-weight:700;">Copied to Clipboard Successfully.</h2>`);
                $info.css({
                    top: '60px',
                    left: '40%',
                    right: 'auto',
                    bottom: 'auto',
                    transform: 'translateX(-40%)',
                    width: 'auto',
                    padding: '10px 20px',
                    borderRadius: '10px',
                    textAlign: 'center'
                });
              }, 10);
              
            }
          },
          {
            extend: 'csv',
            className: 'btn',
            filename: function() {return getReportFilename();},
            title: function() {return getReportTitle();},
            messageTop: '',
            exportOptions: {
              columns: exportColsSelector,
              format: {
                header: function (data, columnIdx) {
                  return columnIdx === 0 ? 'No.' : data;
                }
              }
            },
            action: function(e, dt, node, config) {
              if (!hasReportRows(dt))
              {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, node, config);
            }
          },
          {
            extend: 'excel',
            className: 'btn',
            filename: function() {return getReportFilename();},
            title: function() {return getReportTitle();},
            messageTop: '',
            exportOptions: {
              columns: exportColsSelector,
              format: {
                header: function (data, columnIdx) {
                  return columnIdx === 0 ? 'No.' : data;
                }
              }
            },
            action: function(e, dt, node, config)
          {
              if (!hasReportRows(dt))
              {
                showNoReportDataMessage();
                return;
              }
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
            }
          },
          {
            text: 'PDF',
            className: 'btn',
            action: function(e, dt, node, config)
            {
              if (!hasReportRows(dt))
              {
                showNoReportDataMessage();
                return;
              }
              var filename = getReportFilename() + '.pdf';
              var $wrap = $('<div/>').css({ padding: '10px', 'font-family': 'Arial, sans-serif' });
              var tableNode = dt.table().node();
              var $tableClone = $(tableNode).clone();
              var $tableClone = $(tableNode).clone();
              $tableClone.find('th') .removeClass('sorting sorting_asc sorting_desc sorting_disabled')
                .css({
                    'background': '#fff',
                    'box-shadow': 'none',
                    'border' : '1px solid #000',
                });
              $tableClone.find('th').find('span, .sorting, .sorting_asc, .sorting_desc').remove();
              $tableClone.find('table, thead, tbody, tr, th, td')
                .css({
                    'background': '#fff',
                    'box-shadow': 'none',
                    'border' : '1px solid #000',
                });
              $tableClone.find('th, td')
                .css({
                  'border-bottom': '1px solid #000',
                  'padding': '10px'
              });
              $tableClone.find('thead th:first').text('No.');
              $wrap.append($tableClone);
              var opt = {
                margin:       10,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
              };
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

</script>
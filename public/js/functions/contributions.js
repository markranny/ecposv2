$(function () {
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var table = $('#contribution').DataTable({
      processing: true,
      language: {
          processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
      },
      scrollX: true,
      scrollY: "400px",
      scrollCollapse: true,
      dom: 'lBfrtp',
      buttons: [
          'csv',
          {
              extend: 'excel',
              customize: function (xlsx) {
                  // Calculate sum of every column
                  var sumColumns = [2, 3, 4, 5, 6, 7, 8, 12];
                  $('#contribution tfoot tr').find('th').each(function () {
                      var columnIndex = $(this).index();
                      var sum = $(this).text().replace(/\$/g, ''); // Remove $ sign
                      sumColumns[columnIndex] = parseFloat(sum) || 0;
                  });

                  // Add sum row to the Excel file
                  var sheet = xlsx.xl.worksheets['sheet1.xml'];
                  var lastRow = $('row', sheet).length + 1;
                  var xmlSumRow = '<row>';
                  sumColumns.forEach(function (sum) {
                      xmlSumRow += '<c t="n"><v>' + sum + '</v></c>';
                  });
                  xmlSumRow += '</row>';
                  $('row:last', sheet).after(xmlSumRow);

                  // Calculate sum of totals
                  var sumTotal = 0;
                  $('#contribution tfoot tr').find('th').each(function () {
                      var value = parseFloat($(this).text().replace(/\$/g, ''));
                      if (!isNaN(value)) {
                          sumTotal += value;
                      }
                  });

                  // Add sum of totals to the Excel file
                  var lastRow = $('row', sheet).length + 3;
                  var xmlSumTotalRow = '<row><c t="inlineStr" s="51"><is><t>Total</t></is></c><c t="inlineStr" s="51"></c><c t="n" s="51"><v>' + sumTotal + '</v></c></row>';
                  $('row:last', sheet).after(xmlSumTotalRow);
              }
          },
          'pdf',
          'print'
      ],
      ajax: "contribution-data",
      columns: [
        {data: 'employee_no', name: 'employee_no'},
        {data: 'employee_name', name: 'employee_name'},
        {data: 'sss_loan', name: 'sss_loan'},
        {data: 'pag_ibig_loan', name: 'pag_ibig_loan'},
        {data: 'mutual_loan', name: 'mutual_loan'},
        {data: 'sss_prem', name: 'sss_prem'},
        {data: 'pag_ibig_prem', name: 'pag_ibig_prem'},
        {data: 'philhealth', name: 'philhealth'},
        {data: 'unions', name: 'unions'},
        {data: 'month', name: 'month'},
        {data: 'year', name: 'year'},
        {data: 'period', name: 'period'},
          { data: null, render: function (data, type, rowData) {
              return ['sss_loan', 'pag_ibig_loan', 'mutual_loan', 'sss_prem', 'pag_ibig_prem','philhealth','unions'].reduce(function (sum, prop) {
                  return sum + Number(rowData[prop]); 
              }, 0).toFixed(2); 
          }},
      ],
      footerCallback: function (row, data, start, end, display) {
          var api = this.api(), data;

          // Define columns to sum
          var sumColumns = [2, 3, 4, 5, 6, 7, 8]; // Column indices of columns to sum

          // Calculate sum for each column
          sumColumns.forEach(function (colIndex) {
              var sum = api.column(colIndex, { search: 'applied' }).data().reduce(function (a, b) {
                  return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
              }, 0);

              // Update footer with sum
              $(api.column(colIndex).footer()).html('P' + sum.toFixed(2)); // Display sum with two decimal places
          });

          // Calculate and render sum for the last column
          var sumLastColumn = api.column(12, { search: 'applied' }).data().reduce(function (a, b) {
              return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
          }, 0);
          $(api.column(12).footer()).html('P' + sumLastColumn.toFixed(2)); // Display sum with two decimal places
      }
  });
});

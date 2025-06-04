$(function () {
    /*------------------------------------------
   --------------------------------------------
   Pass Header Token
   --------------------------------------------
   --------------------------------------------*/ 
   $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
  });

  var table = $('#importatt').DataTable({

      processing: true,
language: {
    processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
},

scrollX: true,
scrollY: "400px",
scrollcollapse: true,

"lengthMenu": [[10, 20, 100, -1], [10, 20, 100, "All"]],

 dom: '<"top"fl<"clear">>rt<"bottom"ip<"clear">>',

 dom: 'lBfrtp',

 buttons: [
    {
        extend: 'excel',
        exportOptions: {
            columns: ':not(:first-child)' // Exclude the first column
        }
    },
    {
        extend: 'csv',
        exportOptions: {
            columns: ':not(:first-child)' // Exclude the first column
        }
    },
    {
        extend: 'pdf',
        exportOptions: {
            columns: ':not(:first-child)' // Exclude the first column
        }
    },
    // Add more export options as needed
],

  

  ajax: "payroll-list-data",

  columns: [
    {data: 'action', name: 'action', orderable: false, searchable: false},
    {data: 'employeeattendanceid', name: 'employeeattendanceid'},
    {data: 'employee_no', name: 'employee_no'},
    {data: 'employee_name', name: 'employee_name'},
    {data: 'department', name: 'department'},
    {data: 'job_status', name: 'job_status'},
    { data: 'NETSALARY', name: 'NETSALARY', render: $.fn.dataTable.render.number(',', '.', 2) },
    { data: 'BONDDEPO', name: 'BONDDEPO', render: $.fn.dataTable.render.number(',', '.', 2) },
    { data: 'MUTUALSHARE', name: 'MUTUALSHARE', render: $.fn.dataTable.render.number(',', '.', 2) },
    { data: 'month', name: 'month' },
    { data: 'year', name: 'year' },
    { data: 'period', name: 'period' }
],

footerCallback: function(row, data, start, end, display) {
    var api = this.api();

    // Define columns to sum
    var sumColumns = [6, 7, 8]; // Column indices of columns to sum

    // Calculate sum for each column
    sumColumns.forEach(function(colIndex) {
        var sum = api.column(colIndex, { search: 'applied' }).data().reduce(function(a, b) {
            return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
        }, 0);

        // Format the sum with comma separators for thousands
        var formattedSum = sum.toLocaleString('en-US');

        // Update footer with formatted sum
        $(api.column(colIndex).footer()).html('P' + formattedSum + '.00'); // Display sum with two decimal places
    });
}


  });
});
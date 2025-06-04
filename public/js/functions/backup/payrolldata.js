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

"lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],

 dom: '<"top"fl<"clear">>rt<"bottom"ip<"clear">>',

 dom: 'lBfrtp',

buttons: [ 'csv', 'excel', 'pdf', 'print' ],

  

  ajax: "payroll-list-data",

  columns: [

      {data: 'action', name: 'action', orderable: false, searchable: false},

      {data: 'employeeattendanceid', name: 'employeeattendanceid'},

      {data: 'employee_no', name: 'employee_no'},

      {data: 'employee_name', name: 'employee_name'},

      {data: 'department', name: 'department'},

      {data: 'job_status', name: 'job_status'},

      {data: 'rank_file', name: 'rank_file'},

      {data: 'day_needs', name: 'day_needs'},

      {data: 'days', name: 'days'},

      {data: 'GrossAmount', name: 'GrossAmount'},

      {data: 'NetAmount', name: 'NetAmount'},

      {data: 'TotalDeducAmount', name: 'TotalDeducAmount'},

      {data: 'TotalContriAmount', name: 'TotalContriAmount'},

      {data: 'TotalOtherDeduc', name: 'TotalOtherDeduc'},
      
      {data: 'month', name: 'month'},
      
      {data: 'year', name: 'year'},
      
      {data: 'period', name: 'period'}
  ],

  footerCallback: function (row, data, start, end, display) {
    var api = this.api();

    // Define columns to sum
    var sumColumns = [9, 10, 11, 12, 13]; // Column indices of columns to sum
    var sumColumns2 = [7,8]; // Column indices of columns to sum

    // Calculate sum for each column
    sumColumns.forEach(function (colIndex) {
        var sum = api.column(colIndex, { search: 'applied' }).data().reduce(function (a, b) {
            return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
        }, 0);

        // Update footer with sum
        $(api.column(colIndex).footer()).html('P' + sum.toFixed(2)); // Display sum with two decimal places
    });

    // Calculate sum for each column
    sumColumns2.forEach(function (colIndex) {
        var sum2 = api.column(colIndex, { search: 'applied' }).data().reduce(function (a, b) {
            return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
        }, 0);

        // Update footer with sum
        $(api.column(colIndex).footer()).html('' + sum2); // Display sum with two decimal places
    });
}

  });
});
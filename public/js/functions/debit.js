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

  var table = $('#debit').DataTable({

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

  

  ajax: "debit-data",

  columns: [

      {data: 'costcenter', name: 'costcenter'},

      {data: 'wages', name: 'wages'},

      {data: 'overtimepremium', name: 'overtimepremium'}

  ],

  footerCallback: function (row, data, start, end, display) {
    var api = this.api();

    // Define columns to sum
    var sumColumns = [1,2]; // Column indices of columns to sum

    // Calculate sum for each column
    sumColumns.forEach(function (colIndex) {
        var sum = api.column(colIndex, { search: 'applied' }).data().reduce(function (a, b) {
            return parseFloat(a) + parseFloat(b); // Use parseFloat to handle decimal values
        }, 0);

        // Update footer with sum
        $(api.column(colIndex).footer()).html('P' + sum.toFixed(2)); // Display sum with two decimal places
    });

}

  });
});
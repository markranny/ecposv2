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

  var table = $('#sched').DataTable({

  processing: true,
  language: {
    processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
    },

  serverSide: true,

  "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],

   dom: 'lBfrtp',

  buttons: [ 'csv', 'excel', 'pdf', 'print' ],

  ajax: "employee-schedule",

  columns: [

          {data: 'sched_no', name: 'sched_no'},

          {data: 'created_at', name: 'created_at'},

          {data: 'action', name: 'action', orderable: false, searchable: false}

  ]

  });
});
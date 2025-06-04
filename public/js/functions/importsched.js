function loadData() {
    if ($.fn.dataTable.isDataTable('#importsched')) {
        $('#importsched').DataTable().destroy();
    }

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

  var table = $('#importsched').DataTable({

  processing: true,
  language: {
    processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
    },

  scrollX: true,
  scrollY: "400px",
  scrollcollapse: true,

  serverSide: true,
  

  /* dom: 'Bfrtp',

  buttons: [ 'csv', 'excel', 'pdf', 'print' ], */

  ajax: "import-employee-schedule",

  columns: [

          {data: 'employee_no', name: 'employee_no'},

          {data: 'employee_name', name: 'employee_name'},

          {data: 'department', name: 'department'},

          {data: 'line', name: 'line'},

          {data: 'date_sched', name: 'date_sched'},

          {data: 'time', name: 'time'},

          {
            data: 'employee_no',
            name: 'employee_no',
            orderable: false,
            searchable: false,
            render: function (data, type, full, meta) {
                return `
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deleteModal123" onclick="setDeleteButton(${data}, '${full.date_sched}')" >DELETE</button>    
                `;

            }
        } 
  ]
  

  });
}

function setDeleteButton(employee_no, date_sched){
  $("#delete-footer").html(`
  <button class="btn btn-danger btn-sm mr-1" data-dismiss="modal" onclick="deleteSched(${employee_no}, '${date_sched}')">Delete</button>   
  <button class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</button>
  `);
}

function deleteSched(employee_no, date_sched){
  $.ajax({
          url: "/delete/addsched/"+employee_no+'/'+date_sched, 
          type: "GET",
          dataType: "json",
          success: function(response) {
              if (response.message == 'success') {
                  $("#message").html(`<div class="alert alert-success alert-dismissible" id="disappearingAlert">
                  Deletion success
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>`);
                  loadData(); // Call loadData function here
              } else {
                  $("#message").html(`<div class="alert alert-danger alert-dismissible" id="disappearingAlert">
                  Deletion failed
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>`);
              }
          },
          error: function() {
              $("#message").html(`<div class="alert alert-danger alert-dismissible" id="disappearingAlert">
                  There is an unexpected error. Please try again later.
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>`);
          }
      });
}

$(document).ready(function() {
    loadData(); // Call loadData when document is ready
});

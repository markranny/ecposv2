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

  serverSide: true,

  "lengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],

   dom: 'lBfrtp',

  buttons: [ 'csv', 'excel', 'pdf', 'print' ],

  ajax: "{{ route('schedinfosdata',['id'=>$sched_no]) }}",

  columns: [

          {data: 'employee_no', name: 'employee_no'},

          {data: 'employee_name', name: 'employee_name'},

          {data: 'department', name: 'department'},

          {data: 'line', name: 'line'},

          {data: 'date_sched', name: 'date_sched'},

          {data: 'timess', name: 'timess'},

          {data: 'change_sched', name: 'change_sched'},

          {
              data: 'id',
              name: 'id',
              orderable: false,
              searchable: false,
              render: function (data, type, full, meta) {
                  return `
                  <button class="btn btn-sm btn-primary" onclick="setUpdateForm(${data}, '${full.dept_code}', '${full.department}')" data-toggle="modal" data-target="#updateModal" >EDIT</button>
                  <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deleteModal" onclick="setDeleteButton(${data})">DELETE</button>    
                  `;
              }
          } 

  ]

  });
});


function setDeleteButton(id){
  $("#delete-footer").html(`
  <button class="btn btn-danger btn-sm mr-1" data-dismiss="modal" onclick="deleteDepartment(${id})">Delete</button>   
  <button class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</button>
  `);
}

function setUpdateForm(id, dept_code, department){
  $("#updateData").html(`
  <div class="col-12">
      <div class="form-group">
          <input type="hidden" name="id" class="form-control" value="${id}"><br>
      </div>
  </div>
  <div class="col-12">
      <div class="form-group">
          <input type="text" name="dept_code" class="form-control" required placeholder="ENTER DEP CODE" style='text-transform:uppercase' value="${dept_code}"><br>
      </div>
  </div>

  <div class="col-12">
      <div class="form-group">
          <input type="text" name="department" class="form-control" required placeholder="ENTER DEPARTMENT" style='text-transform:uppercase' value="${department}"><br>
      </div>
  </div>
  <div class="col-12">
      <div class="form-group text-right">
              <button class="btn btn-danger btn-sm mr-1" type="submit">Update</button>   
              <button class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
      </div>
  </div>
  `);
}
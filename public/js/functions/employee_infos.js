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

  var table = $('#att').DataTable({

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

  buttons: [ 'csv', 'excel', 'pdf', 'print' ],

  ajax: "employee-data",

  columns: [

          {data: 'employee_no', name: 'employee_no'},

          {data: 'lastname', name: 'lastname'},

          {data: 'firstname', name: 'firstname'},

          {data: 'middlename', name: 'middlename'},

          {data: 'suffix', name: 'suffix'},

          {data: 'gender', name: 'gender'},

          {data: 'educational_attainment', name: 'educational_attainment'},

          {data: 'degree', name: 'degree'},

          {data: 'civil_status', name: 'civil_status'},

          {data: 'birthdate', name: 'birthdate'},

          {data: 'contact_no', name: 'contact_no'},

          {data: 'email', name: 'email'},

          {data: 'present_address', name: 'present_address'},

          {data: 'permanent_address', name: 'permanent_address'},

          {data: 'emergency_contact_name', name: 'emergency_contact_name'},

          {data: 'emergency_contact', name: 'emergency_contact'},

          {data: 'emergency_relationship', name: 'emergency_relationship'},

          {data: 'employee_status', name: 'employee_status'},

          {data: 'job_status', name: 'job_status'},

          {data: 'rank_file', name: 'rank_file'},

          {data: 'department', name: 'department'},

          {data: 'line', name: 'line'},

          {data: 'job_title', name: 'job_title'},

          {data: 'hired_date', name: 'hired_date'},

          {data: 'endcontract', name: 'endcontract'},

          {data: 'pay_type', name: 'pay_type'},

          {data: 'pay_rate', name: 'pay_rate'},

          {data: 'allowance', name: 'allowance'},

          {data: 'sss_no', name: 'sss_no'},

          {data: 'philhealth_no', name: 'philhealth_no'},

          {data: 'hdmf_no', name: 'hdmf_no'},

          {data: 'tax_no', name: 'tax_no'},

          {data: 'taxable', name: 'taxable'},
          
          {data: 'costcenter', name: 'costcenter'},

  ]

  });
});
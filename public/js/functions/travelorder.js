function loadData() {
    if ($.fn.dataTable.isDataTable('#to')) {
        $('#to').DataTable().destroy();
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

    var table = $('#to').DataTable({

    processing: true,
    language: {
        processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
        },

    serverSide: true,

    dom: 'lBfrtp',

    buttons: [ 'csv', 'excel', 'pdf', 'print' ],

    ajax: "travel-order-list",

    columns: [
            {data: 'id', name: 'id'},

            {data: 'employee_no', name: 'employee_no'},

            {data: 'firstname', name: 'firstname'},

            {data: 'date_sched', name: 'date_sched'},

            {data: 'location', name: 'location'},

            {data: 'remarks', name: 'remarks'},

            {data: 'month', name: 'month'},

            {data: 'year', name: 'year'},

            {data: 'period', name: 'period'},

            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                /* render: function (data, type, full, meta) {
                    return `
                    <button class="btn btn-sm btn-primary" onclick="setUpdateForm(${data}, '${full.employee_no}', '${full.firstname}', '${full.date_sched}', '${full.location}', '${full.remarks}')" data-toggle="modal" data-target="#updateModal" >EDIT</button>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deleteModal" onclick="setDeleteButton(${data}, '${full.employee_no}', '${full.date_sched}')">DELETE</button>    
                    `;
                } */

                render: function (data, type, full, meta) {
                    return `
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deleteModal" onclick="setDeleteButton(${data}, '${full.employee_no}', '${full.date_sched}')">DELETE</button>    
                    `;
                }
            } 

    ]

    });
}


function setDeleteButton(id, employee_no, date_sched){
    $("#delete-footer").html(`
    <button class="btn btn-danger btn-sm mr-1" data-dismiss="modal" onclick="deleteTravelOrder(${id}, '${employee_no}', '${date_sched}')">Delete</button>   
    <button class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</button>
    `);
}

function setUpdateForm(id, employee_no,  fullname,  date_sched, location, remarks){
    $("#updateData").html(`
    <div class="col-12">
        <div class="form-group">
            <input type="hidden" name="id" class="form-control" value="${id}"><br>
        </div>
    </div>
    <div class="col-12">
        <div class="form-group">
            <input type="text" name="employee_name" class="form-control" placeholder="Employee Name" disabled value="${fullname}"><br>
            <input type="text" name="employee_no" class="form-control" placeholder="Employee Number" disabled value="${employee_no}"><br>
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            <input type="text" name="datesched" class="form-control datepicker" id="datetimepicker5" placeholder="Select Date" ><br>
        </div>
    </div>

    <div class="col-6">
        <div class="form-group">
            <textarea name="location" class="form-control textarea-autosize" placeholder="location">${location}</textarea>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <textarea class="form-control textarea-autosize" name="remarks" id="textareaExample" rows="4" placeholder="remarks">${remarks}</textarea>
        </div>
    </div>
    <div class="col-12">
        <div class="form-group text-right">
                <button class="btn btn-danger btn-sm mr-1" type="submit">Update</button>   
                <button class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
        </div>
    </div>

    `);

    $('#datetimepicker5').datetimepicker({
        format: 'MM/DD/YYYY',
        defaultDate: date_sched
    });
}


$(document).ready(function() {
        $("#updateTravelOrder").submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
                url: "/update/travel_order/", 
                type: "POST",
                data: formData,
                processData: false,
                contentType: false, 
                success: function(response) {
                    if (response.message == 'success') {
                        $("#messageUpdate").html(`<div class="alert alert-success alert-dismissible" id="disappearingAlert">
                        Update success
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`);
                        $(loadData);
                    } else {
                        $("#messageUpdate").html(`<div class="alert alert-danger alert-dismissible" id="disappearingAlert">
                        ${response.message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`);
                    }
                },
                error: function() {
                    $("#messageUpdate").html(`<div class="alert alert-danger alert-dismissible" id="disappearingAlert">
                        There is an unexpected error. Please try again later.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`);
                }
            });
        });
});

function deleteTravelOrder(id, employee_no, datesched){
    $.ajax({
            url: "/delete/travel_order/"+id+'/'+employee_no+'/'+datesched, 
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
                    $(loadData);
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

$(loadData);
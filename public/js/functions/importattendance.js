$(function () {
    $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
    });

    var table = $('#importattendance').DataTable({
        processing: true,
        language: {
            processing: '<img width="80" height="80" src="/images/coffee-cup.webp" alt="spinner-frame-5"/>',
        },
        scrollX: true,
        scrollY: "400px",
        scrollCollapse: true,
        "lengthMenu": [[15, 50, 100, -1], [15, 50, 100, "All"]],
        dom: 'lBfrtp',
        ajax: "import-data",
        columns: [
            { data: 'employee_no', name: 'employee_no',visible: false},
            { data: 'fullname', name: 'fullname' },
            { data: 'date', name: 'date' },
            { data: 'day', name: 'day' },
            { 
                data: 'in1', 
                name: 'in1',
                render: function (data, type, row) {
                    return formatTimeTo12Hour(data);
                }
            },
            { 
                data: 'out1', 
                name: 'out1',
                render: function (data, type, row) {
                    return formatTimeTo12Hour(data);
                }
            },
            { 
                data: 'in2', 
                name: 'in2',
                render: function (data, type, row) {
                    return formatTimeTo12Hour(data);
                }
            },
            { 
                data: 'out2', 
                name: 'out2',
                render: function (data, type, row) {
                    return formatTimeTo12Hour(data);
                }
            },
            { data: 'nextday', name: 'nextday' },
            { data: 'hours_work', name: 'hours_work' },
            
        ]
    });

    function formatTimeTo12Hour(time) {
        if (time) {
            var date = new Date('1970-01-01T' + time + 'Z'); // Assuming the time is in "HH:mm:ss" format
            var hours = date.getUTCHours();
            var minutes = date.getUTCMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // The hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var strTime = hours + ':' + minutes + ' ' + ampm;
            return strTime;
        }
        return time;
    }

    table.on('draw', function () {
        var tbody = $('#importattendance tbody');
        var rows = Array.from(tbody.find('tr'));

        // Function to group rows by the Fullname column
        function groupByFullname(rows) {
            var groupedRows = {};
            rows.forEach(function (row) {
                var fullnameCell = $(row).find('td:eq(0)').text().trim(); // Assuming Fullname is in the second column
                if (!groupedRows[fullnameCell]) {
                    groupedRows[fullnameCell] = [];
                }
                groupedRows[fullnameCell].push(row);
            });
            return groupedRows;
        }

        // Clear the tbody
        tbody.empty();

        // Get grouped rows
        var groupedRows = groupByFullname(rows);

        // Append grouped rows back to tbody with separator row
        for (var fullname in groupedRows) {
            var fullnameRows = groupedRows[fullname];

            // Add a separator row for each group
            var separatorRow = $('<tr class="group group-start"></tr>');
            var separatorCell = $('<td></td>', {
                colspan: 10,
                style: 'background-color: green; color: white; font-weight: bold;',
                text: fullname
            });
            separatorRow.append(separatorCell);
            tbody.append(separatorRow);

            fullnameRows.forEach(function (row) {
                tbody.append(row);
            });
        }
    });
});

$('#datetimepicker1').datetimepicker({
    format: 'HH:mm'
});
$('#datetimepicker2').datetimepicker({
    format: 'HH:mm'
});

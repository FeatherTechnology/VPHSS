$(document).ready(function () {
    $('#view_honour_list').click(function (event) {
        event.preventDefault();

        let exam = $('#exam').val().trim();
        let standard = $('#standard').val().trim();


        if (exam === '' || standard === '') {
            alert("Please select all the fields");
            return;
        }

        $.post(
            'reports/roll_of_honour_report/view_honour_list.php',
            { standard: standard, exam: exam },
            function (data) {
                $('.subject_card').show();
                $('#mark_info_table_div').html(data.html);

                // Wait for DOM update
                setTimeout(() => {
                    $('#student_total_list').DataTable({
                        ordering: false,
                        paging: false,
                        info: false,
                        searching: false,
                        dom: 'Bfrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf',
                            {
                                extend: 'print',
                                text: 'Print',
                                title: '', // prevent DataTables from injecting its own title
                                customize: function (win) {
                                    // Replace the body with the full HTML (headers + table)
                                    let reportHtml = document.getElementById('honour_report').innerHTML;
                                    win.document.body.innerHTML = reportHtml;
                                
                                    // Optional: styling tweaks
                                    win.document.body.style.fontSize = '14px';
                                    win.document.body.style.textAlign = 'center';
                                }
                            }
                        ]
                    });

                }, 100);
            },
            'json'
        );
    });
});

$(function () {
    getStandardList('standard');
    getExamDropdown();
});
function getStandardList(targetSelectId) {
    $.ajax({
        type: 'POST',
        url: 'ajaxFiles/getStandardList.php',
        dataType: 'json',
        success: function (response) {
            var $select = $('#' + targetSelectId);
            $select.empty();
            $select.append("<option value=''>Select Standard</option>");
            for (var i = 0; i < response.length; i++) {
                $select.append(
                    "<option value='" + response[i]['std_id'] + "'>" + response[i]['std'] + "</option>"
                );
            }
        }
    });
}

function getExamDropdown() {
    $.post('examCreationFiles/get_exam_type.php', function (response) {
        let appendExamNameOption = '';
        appendExamNameOption += '<option value="">Select Exam</option>';
        $.each(response, function (index, val) {
            let selected = '';
            appendExamNameOption += '<option value="' + val.id + '" ' + selected + '>' + val.exam_type + '</option>';
        });
        $('#exam').empty().append(appendExamNameOption);
    }, 'json');
}
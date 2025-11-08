$(document).ready(function () {
     $('#standard').change(function () {
        let standardID = $(this).val();
        let academicYear = $('#academic_year').val();
        let medium = $('#medium').val();
        $.ajax({
            type: 'POST',
            data: { "standardID": standardID, "academicYear": academicYear, "medium": medium },
            url: 'examCreationFiles/sectionList.php',
            dataType: 'json',
            success: function (response) {
                $('#section').empty();
                $('#section').append("<option value=''>Select Option</option>");
                for (var i = 0; i < response.length; i++) {
                    $('#section').append("<option value='" + response[i] + "'>" + response[i] + "</option>");
                }
            }
        })
    });


     $('#view_student').click(function (event) {
        event.preventDefault();

        let exam = $('#exam').val().trim();
        let standard = $('#standard').val().trim();
        let section = $('#section').val().trim();

        if (exam === '' || standard === '' || section === '') {
            alert("Please Select all the fields");
            return;
        }

        $.post('examCreationFiles/sms_delivery_report_list.php',
            { standard: standard, exam: exam, section: section },
            function (data) {
                $('.report_card').show();
                $('#mark_info_table_div').html(data.html);


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
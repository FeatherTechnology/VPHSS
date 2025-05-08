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
                $('#section').append("<option value=''>Select option</option>");
                for (var i = 0; i < response.length; i++) {
                    $('#section').append("<option value='" + response[i] + "'>" + response[i] + "</option>");
                }
            }
        })
    });
    //////////////////////////////////////////view Student List////////////////////////////////////////////////////
    $('#view_subject').click(function (event) {
        event.preventDefault();
    
        let section = $('#section').val().trim();
        let standard = $('#standard').val().trim();
    
        if (section === '' || standard === '') {
            alert("Please Select all the fields");
            return;
        }
    
        $.post('examCreationFiles/paper_allocate.php', { standard: standard, section: section }, function (response) {
            if (!response.subjects || response.subjects.length === 0) {
                let html = `
                    <div class="alert alert-info text-center">
                      Paper Allocation for this Standard Not Done. Please Set Syllabus for this Standard and then Continue this Process.
                    </div>
                    <div class="text-left">
                      <button class="btn btn-primary btn-sm mt-2" onclick="setSyllabus()">To Allocate Syllabus Click Here</button>
                    </div><br>
                `;
                $('.subject_allocate').show().html(html);
                $('.subject_card').hide();
                return;
            }
    
            $('.subject_allocate').hide();
            $('.subject_card').show();
    
            let html = '';
            let sno = 1;
            let staffList = response.staff_list;
    
            $.each(response.subjects, function (index, value) {
                html += '<tr>';
                html += '<td>' + sno + '</td>';
                html += '<td>' + value.paper_name + '</td>';
    
                // Always show the dropdown, pre-select if allocated
                html += '<td>';
                html += '<select name="staff[' + value.paper_name + ']" class="form-control staff-select">';
                html += '<option value="">Select Staff</option>';
                $.each(staffList, function (i, staff) {
                    let selected = (staff.id == value.staff_id) ? 'selected' : '';
                    html += '<option value="' + staff.id + '" ' + selected + '>' + staff.staff_name + '</option>';
                });
                html += '</select>';
                html += '</td>';
    
                html += '</tr>';
                sno++;
            });
    
            $('#subject_info tbody').html(html);
        }, 'json');
    });
    

    /////////////////////////////////////////////////////view student List End/////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////Staff Allocation Mapping Start///////////////////////////////////////////////
    $('#submit_staff_create').click(function (event) {
        event.preventDefault();

        let section = $('#section').val().trim();
        let standard = $('#standard').val().trim();
        let subjectData = [];

        $('#subject_info tbody tr').each(function () {
            let paperName = $(this).find('td:eq(1)').text().trim(); // from plain text
            let staffId = $(this).find('td:eq(2) select').val()?.trim() || '';

            if (paperName !== '' && staffId !== '') {
                subjectData.push({
                    paper_name: paperName,
                    staff_id: staffId,
                });
            }
        });

        $.ajax({
            url: 'examCreationFiles/submit_staff_allocation.php',
            type: 'POST',
            data: {
                standard: standard,
                section: section,
                subjectData: subjectData, // no need for ( )
                dataType: 'json'
            },
            success: function (response) {
                const res = JSON.parse(response);
                alert(res.message);
            }
            ,
            error: function () {
                alert('Error while submitting exam data.');
            }
        });
    });
    /////////////////////////////////////////////////////Staff Allocation Mapping End/////////////////////////////////////////////////////
    //Document End
});
$(function () {
    getStandardList('standard');
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

function setSyllabus() {
    event.preventDefault();
    window.location.href = 'syllabus_allocation';
}

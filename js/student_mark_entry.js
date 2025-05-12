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
  //////////////////////////////////////////view Student List////////////////////////////////////////////////////
  $('#view_student').click(function (event) {
    event.preventDefault();

    let exam = $('#exam').val().trim();
    let standard = $('#standard').val().trim();
    let section = $('#section').val().trim();

    if (exam === '' || standard === '' || section === '') {
      alert("Please Select all the fields");
      return;
    }

    $.post('examCreationFiles/view_student_list.php',
      { standard: standard, exam: exam, section: section },
      function (data) {

        if (data.status === 'no_subject') {
          $('.subject_allocate').show();
          $('.subject_allocate').html(`
                <div class="alert alert-info text-center">
                  Paper Allocation for this Standard Not Done. Please Set Syllabus for this Standard and then Continue this Process.
                </div>
                <div class="text-left">
                  <button class="btn btn-primary btn-sm mt-2" onclick="setSyllabus()">To Allocate Syllabus Click Here</button>
                </div><br>
              `).show();
          $('.subject_card').hide();
        }
        else if (data.status === 'no_exam') {
          $('.subject_allocate').show();
          $('.subject_allocate').html(`
                <div class="alert alert-info text-center">
                  Exam Mark Allocation Not Created for this Standard. Please Set Exam Mark for this Standard and then Continue this Process.
                </div>
                <div class="text-left">
                  <button class="btn btn-primary btn-sm mt-2" onclick="setExam()">To Allocate Exam Mark Click Here</button>
                </div><br>
              `).show();
          $('.subject_card').hide();
        }
        else if (data.status === 'no_staff') {
          $('.subject_allocate').show();
          $('.subject_allocate').html(`
                <div class="alert alert-info text-center">
                  Staff Allocation for this Standard Not Done. Please Set Staff for this Standard and then Continue this Process.
                </div>
                <div class="text-left">
                  <button class="btn btn-primary btn-sm mt-2" onclick="setStaff()">To Allocate Staff Click Here</button>
                </div><br>
              `).show();
          $('.subject_card').hide();
        }
        else {
          $('.subject_allocate').hide();
          $('.subject_card').show();
          $('#mark_info_table_div').html(data.html);
        }

      },
      'json'
    );
  });
  $(document).on('input', '.mark-input', function () {
    let mark = parseFloat($(this).val());
    let outOf = parseFloat($(this).data('outof'));
    let pass = parseFloat($(this).data('pass'));

    // Validate if input is numeric and not NaN
    if (isNaN(mark)) {
        $(this).removeClass('text-danger');
        return;
    }

    if (mark > outOf) {
        alert('Entered mark should not exceed ' + outOf);
        $(this).val('');
        return;
    }

    if (mark < pass) {
        $(this).addClass('text-danger');
    } else {
        $(this).removeClass('text-danger');
    }
});

  $(document).on('click', '#saveMark', function (event) {
    event.preventDefault();
    $(this).attr('disabled', true);
     $(this).attr('disabled', true);
    let exam = $('#exam').val().trim();
    let standard = $('#standard').val().trim();
    let section = $('#section').val().trim();
    let marksData = [];

    let hasError = false;

    $('.mark-input').each(function () {
      let markVal = $(this).val().trim();
  
      // Skip if mark is empty or not a number
      if (markVal === '') return;
     if (!$.isNumeric(markVal) && markVal.toUpperCase() !== 'A') {
        hasError = true;
        return;
    }
      let studentId = $(this).data('student');
      let subject = $(this).data('subject');
      let staff_id = $(this).data('staff');
       let mark = markVal.toUpperCase() === 'A' ? 'A' : parseFloat(markVal);
  
      marksData.push({
          student_id: studentId,
          staff_id: staff_id,
          subject: subject,
          mark: mark
      });
  });
  

    if (hasError) return; // stop if any validation error

    if (marksData.length === 0) {
      alert('No marks entered to save.');
      $('#saveMark').attr('disabled', false);
      return;
    }

    // AJAX to send valid data
    $.ajax({
      url: 'examCreationFiles/submit_student_mark_entry.php',
      type: 'POST',
      data: {
        standard: standard,
        section: section,
        exam: exam,
        marks: marksData,
        dataType: 'json'
      },
      success: function (res) {
              $('#saveMark').attr('disabled', false);
        const response = typeof res === 'string' ? JSON.parse(res) : res;
        if (response.status === 'success') {
          alert('Marks saved successfully!');
        } else {
          alert(response.message || 'Failed to save marks!');
        }
      },
      error: function (err) {
        console.error(err);
        alert('AJAX error occurred');
      }
    });
  });

  /////////////////////////////////////////////////////view student List End/////////////////////////////////////////////////////////////////////
  //Document End
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
function setSyllabus() {
  event.preventDefault();
  window.location.href = 'syllabus_allocation';
}
function setExam() {
  event.preventDefault();
  window.location.href = 'exam_creation';
}
function setStaff() {
  event.preventDefault();
  window.location.href = 'staff_subject_allocation';
}
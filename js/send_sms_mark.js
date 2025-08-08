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
  //////////////////////////////////////////////////////View Student List Start/////////////////////////////////////////////////
  $('#view_student').click(function (event) {
    event.preventDefault();

    let exam = $('#exam').val().trim();
    let standard = $('#standard').val().trim();
    let section = $('#section').val().trim();

    if (exam === '' || standard === '' || section === '') {
      alert("Please Select all the fields");
      return;
    }

    $.post('examCreationFiles/view_mark_list.php',
      { standard: standard, exam: exam, section: section },
      function (data) {
        $('.subject_card').show();
        $('#mark_info_table_div').html(data.html);


      },
      'json'
    );
  });
  $(document).on('change', '#selectAll', function () {
    $('.student-check').prop('checked', this.checked);
  });


  $(document).on('click', '#sendSelectedSMS', function (event) {
    event.preventDefault();

    $(this).attr('disabled', true);

    let exam = $('#exam').val().trim();
    let standard = $('#standard').val();
    let section = $('#section').val();
    let selectedStudents = [];

    $('.student-check:checked').each(function () {
      var checkbox = $(this);
      var row = checkbox.closest('tr');
      var student_id = checkbox.data('student-id');
      let studentName = row.find('td:eq(2)').text().trim();
      let smsNo = row.data('sms');

      let marks = {};
      row.find('td[data-paper]').each(function () {
        let subject = $(this).data('paper');
        let mark = $(this).data('mark');
        marks[subject] = mark;
      });

      let total = 0;
      const totalTd = row.find('td[data-total]');
      total = totalTd.data('total');
      selectedStudents.push({
        student_id: student_id,
        student_name: studentName,
        smsNo: smsNo,
        marks: marks,
        total: total
      });
    });

    if (selectedStudents.length === 0) {
      alert('Please select at least one student to send SMS.');
      $('#sendSelectedSMS').attr('disabled', false);
      return;
    }
    $.ajax({
      url: 'examCreationFiles/sendSMSMark.php',
      type: 'POST',
      data: {
        standard: standard,
        section: section,
        exam: exam,
        selectedStudents: selectedStudents
      },
      dataType: 'json',
      success: function (response) {
        $('#sendSelectedSMS').attr('disabled', false);
        if (response.status == 'success') {
          alert('Success' + response.message);
        } else {
          alert('Message failed!');
        }
      },
      error: function (xhr, status, error) {
        $('#sendSelectedSMS').attr('disabled', false);
        console.error('Error:', error);
        alert('An error occurred while sending the message.');
      }
    });
  });

  /////////////////////////////////////////////////////View Student List End ///////////////////////////////////////////////
  ///Document End
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
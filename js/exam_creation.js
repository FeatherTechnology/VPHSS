$(document).ready(function () {

  $('input[name=exam_create]').click(function () {
    let examType = $(this).val();
    if (examType == 'mark_create') {
      $('#exam_creation_form').show(); $('#exam_report_form').hide(); $('.subject_allocate').hide(); $('.subject_card').hide();
      $('#standard').val($('#standard option:first').val());
      $('#exam').val($('#exam option:first').val());

    } else if (examType == 'mark_report') {
      $('#exam_creation_form').hide(); $('#exam_report_form').show(); $('.sub_report').hide()
      getStandardList('standard_id');

    }
  })

  /////////////////////////////////////////////////////////// Exam Modal START ///////////////////////////////////////////////////////////////////////
  $('#submit_exam').click(function (event) {
    event.preventDefault();
    let exam_type = $('#exam_type').val(); let id = $('#exam_id').val();

    if (exam_type == '') {
      alert("Please fill Exam Type.");
      $('#submit_exam').attr('disabled', false);
      return;
    }
    $.post('examCreationFiles/submit_exam.php', { exam_type, id }, function (response) {
      if (response == '0') {
        alert('Exam Type Already Exists!');
      } else if (response == '1') {
        alert('Exam Type Updated Successfully!');
      } else if (response == '2') {
        alert('Exam Type Added Successfully!');
      }
      getExamTable();
    }, 'json');
    clearExam();
  });

  $(document).on('click', '.examActionBtn', function () {
    var id = $(this).attr('value'); // Get value attribute
    $.post('examCreationFiles/editExamType.php', { id }, function (response) {
      $('#exam_id').val(id);
      $('#exam_type').val(response[0].exam_type);
    }, 'json');
  });

  $(document).on('click', '.examDeleteBtn', function () {
    var id = $(this).attr('value');
    var isok = confirm("Do you want Delete the Exam Type?");
    if (isok == false) {
      return false;
    } else {
      deleteExam(id);
    }
  });

  /////////////////////////////////////////////////////////// Exam Modal END ///////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////// Exam Creation START ///////////////////////////////////////////////////////////////////////
  $('#view_exam').click(function (event) {
    event.preventDefault();

    let exam = $('#exam').val().trim();
    let standard = $('#standard').val().trim();

    if (exam === '' || standard === '') {
      alert("Please Select all the fields");
      return;
    }

    $.post('examCreationFiles/submit_exam_create.php', { standard: standard, exam: exam }, function (data) {
      if (!Array.isArray(data) || data.length === 0) {
        let html = `
          <div class="alert alert-info text-center">
              Paper Allocation for this Standard Not Done. Please Set Syllabus for this Standard and then Continue this Process.
          </div>
          <div class="text-left">
              <button class="btn btn-primary btn-sm mt-2" onclick="setSyllabus()">To Allocate Syllabus Click Here</button>
          </div><br>
      `;
        $('.subject_allocate').show();
        $('.subject_allocate').html(html);
        $('.subject_card').hide();
      } else {
        $('.subject_allocate').hide();
        $('.subject_card').show();

        let html = '';
        let sno = 1;

        $.each(data, function (index, value) {
          html += '<tr>';
          html += '<td>' + sno + '</td>';
          html += '<td>' + value.paper_name + '</td>';
          html += `<td><input type="number" name="max_mark[]" class="out-of-mark" value="${value.max_mark}" data-max="${value.max_mark || 0}">`;
          html += `<input type="hidden" name="hidden_max_mark[]" value="${value.mrk}">`; // Hidden field for comparison
          html += `</td>`;
          html += `<td><input type="number" name="pass[]" class="pass-mark" value="${value.pass_mark}"></td>`;
          html += '</tr>';
          sno++;
        });

        $('#subject_info tbody').html(html);
      }
    }, 'json');

  });
  //  Out of Mark should not exceed max_mark
  $('#subject_info').on('input', '.out-of-mark', function () {
    let $this = $(this);
    let $row = $this.closest('tr');

    // Get the hidden max_mark value
    let maxAllowed = parseFloat($row.find('input[name="hidden_max_mark[]"]').val());
    let enteredVal = parseFloat($this.val());

    // Skip validation if maxAllowed is not a valid number
    if (isNaN(maxAllowed)) return;

    if (!isNaN(enteredVal) && enteredVal > maxAllowed) {
      alert(`Entered value cannot exceed ${maxAllowed}`);
      $this.val('');
    }
  });


  //  Pass mark should not be greater than Out of Mark
  $('#subject_info').on('input', '.pass-mark', function () {
    let $this = $(this);
    let $row = $this.closest('tr');
    let outOfMark = parseFloat($row.find('.out-of-mark').val());
    let passVal = parseFloat($this.val());

    if (!isNaN(outOfMark) && !isNaN(passVal) && passVal > outOfMark) {
      alert("Pass % cannot be greater than Out of Mark");
      $this.val('');
    }
  });


  $('#submit_exam_create').click(function (event) {
    event.preventDefault();

    let exam = $('#exam').val().trim();
    let standard = $('#standard').val().trim();
    let subjectData = [];

    $('#subject_info tbody tr').each(function () {
      let paperName = $(this).find('td:eq(1)').text().trim(); // from plain text
      let outOfMarks = $(this).find('td:eq(2) input').val()?.trim() || '';
      let passPercent = $(this).find('td:eq(3) input').val()?.trim() || '';

      if (paperName !== '' && outOfMarks !== '' && passPercent !== '') {
        subjectData.push({
          paper_name: paperName,
          out_of_marks: outOfMarks,
          pass_percent: passPercent
        });
      }
    });

    $.ajax({
      url: 'examCreationFiles/exam_create_submit.php',
      type: 'POST',
      data: {
        standard: standard,
        exam: exam,
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

  /////////////////////////////////////////////////////////// Exam Creation END ///////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////Report///////////////////////////////////////////////////////////////////////
    $('#view_report').on('click', function () {
      let standard = $('#standard_id').val();
      if (!standard || $.trim(standard) === '') {
        alert("Please Select all the fields");
        return;
      }
      $.ajax({
        url: 'examCreationFiles/get_exam_report.php',
        type: 'POST',
        data: { standard: standard },
        dataType: 'html',
        success: function (response) {
          $('.sub_report').show();
          $('#report_info_table_div').html(response);
        },
        error: function (xhr, status, error) {
          console.error('AJAX Error:', status, error);
        }
      });
    });
});
/////////////////////////////////////////////////////////////////Report End/////////////////////////////////////////////////////////////////
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
function clearExam() {
  $('#exam_type').val('');
  $('#exam_id').val('');
}

function getExamTable() {
  $.ajax({
    url: 'examCreationFiles/get_exam_type.php', // Ensure this is the correct path to your PHP script
    type: 'POST',
    dataType: 'json',
    success: function (response) {
      if (response.length > 0) {
        var tbody = $('#exam_creation_table tbody');
        tbody.empty(); // Clear existing rows
        i = 1;
        // Iterate over the response array and append each row to the table
        $.each(response, function (index, item) {

          var serial = i++; // Serial number from PHP
          var exam_type = item.exam_type;
          var action = item.action;

          var row = '<tr>' +
            '<td>' + serial + '</td>' +
            '<td>' + exam_type + '</td>' +
            '<td>' + action + '</td>' +
            '</tr>';

          tbody.append(row);
        });

      } else {
        console.log("No data found.");
      }
    },
    error: function (xhr, status, error) {
      console.error('AJAX Error: ' + status + ' ' + error);
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

    clearExam();
  }, 'json');
}
function deleteExam(id) {
  $.post('examCreationFiles/deleteExamType.php', { id }, function (response) {
    if (response == '1') {
      alert('Exam Deleted Successfully.');
      getExamTable();
    } else if (response == '0') {
      alert('Used in Exam Creation');
    } else {
      alert('Exam Delete Failed.');
    }
  }, 'json');
}
function setSyllabus() {
  event.preventDefault();
  window.location.href = 'syllabus_allocation';
}

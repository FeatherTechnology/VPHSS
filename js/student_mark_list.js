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

  $.post('reports/student_mark_report/view_student_mark.php',
    { standard: standard, exam: exam, section: section },
    function (data) {
      $('.subject_card').show();
      $('#mark_info_table_div').html(data.html);

      // Wait for DOM update
      setTimeout(() => {
  const table = $('#student_mark_list').DataTable({
    ordering: false,
    paging: false,
    info: false,
    searching: false,
    dom: 'Bfrtip',
    buttons: [
      'copy', 'csv', 'excel',
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        exportOptions: {
          columns: ':visible'
        },
        customize: function (doc) {
          // Combine all tables into the PDF body
          const allHtml = $('#student_mark_export').html();
          doc.content = [{ text: 'Student Mark Report', style: 'header' }];
          doc.content.push({ text: allHtml, style: 'body', margin: [0, 10, 0, 0] });
        }
      },
      {
        extend: 'print',
        text: 'Print',
        title: '',
        customize: function (win) {
          const css = `
            table {
              border-collapse: collapse !important;
              width: 100%;
            }
            table, th, td {
              border: 1px solid #000 !important;
            }
            table th, table td {
              padding: 5px;
              text-align: center;
            }
          `;
          const style = win.document.createElement('style');
          style.innerHTML = css;
          win.document.head.appendChild(style);

          const fullTable = $('#student_mark_export').clone();
          $(win.document.body).html(fullTable);
        }
      }
    ]
  });
}, 100);

    },
    'json'
  );
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
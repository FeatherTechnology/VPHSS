
$(document).ready(function () {

    $('#bank_creation').on('submit', function (e) {

        if (!validations()) {
        event.preventDefault();
        return false;
    }
    });

});


// Document ready END


function validations() {
    let validation = true;

    let bank_name = $('#bank_name').val().trim();
    let short_name = $('#short_name').val().trim();
    let acc_no = $('#acc_no').val().trim();
    let ifsc = $('#ifsc').val().trim();
    let branch = $('#branch').val().trim();

    if (bank_name === '') {
        $('#banknameCheck').show();
        validation = false;
    } else {
        $('#banknameCheck').hide();
    }

    if (short_name === '') {
        $('#shortnameCheck').show();
        validation = false;
    } else {
        $('#shortnameCheck').hide();
    }
    if (branch == '') {
        $('#branchCheck').show();
        validation = false;
    } else {
        $('#branchCheck').hide();
    }
    if (acc_no === '') {
        $('#accnoCheck').show();
        validation = false;
    } else {
        $('#accnoCheck').hide();
    }

    if (ifsc === '') {
        $('#ifscCheck').show();
        validation = false;
    } else {
        $('#ifscCheck').hide();
    }

    return validation;
}

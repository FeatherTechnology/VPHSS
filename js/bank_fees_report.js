// Document is ready
$(document).ready(function () {

    $('#from_date').change(function(){
        let fromDate = $(this).val();
        $('#to_date').attr('min', fromDate);
    });

    $('#fees_collection_table_view_btn').click(function(){
        let feesFromDate = $('#from_date').val();
        let feesToDate = $('#to_date').val();
        let bankid = $('#bank_name').val();

        if(feesFromDate !='' && feesToDate !='' && bankid !=''){
            $.ajax({
                type: 'POST',
                data: {"feesFromDate": feesFromDate, "feesToDate": feesToDate, "bankid": bankid},
                url: 'reports/fees_details_report/bankFeesCollectionList.php',
                success: function(response){
                    $('#listCard').show();
                    $('#showStudentDailyFeesCollectionList').empty();
                    $('#showStudentDailyFeesCollectionList').html(response);
                }
            })
        }else{
            $('#showStudentDailyFeesCollectionList').empty();
            $('#listCard').hide();
            alert("Kindly select All Fields!");
        }
    });

}); //Document END//

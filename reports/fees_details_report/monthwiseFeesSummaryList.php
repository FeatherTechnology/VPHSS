<?php
include "../../ajaxconfig.php";
@session_start();
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];
}

if (isset($_POST['feesFromDate'])) {
    $feesFromDate = new DateTime($_POST['feesFromDate']);
    $startdate = clone $feesFromDate;
}
if (isset($_POST['feesToDate'])) {
    $feesToDate = new DateTime($_POST['feesToDate']);
    $to_date = $feesToDate->format('Y-m-d');
}
?>

<table class="table table-bordered" id="show_monthwise_fees_summary">
    <thead>
        <tr>
            <th colspan='12'>Fees Summary Report From <?php echo $feesFromDate->format('M-Y'); ?> To <?php echo $feesToDate->format('M-Y'); ?> </th>
        </tr>
        <tr>
            <th rowspan="2">S.No</th>
            <th rowspan="2">Date</th>
            <th colspan="3">School Fee</th>
            <th rowspan="2">Book Fee</th>
            <th colspan="3">Transport Fee</th>
            <th rowspan="2">Last year Fee</th>
            <th rowspan="2">Total Amount</th>
        </tr>
        <tr>
            <th>Term I</th>
            <th>Term II</th>
            <th>Term III</th>
            <th>Term I</th>
            <th>Term II</th>
            <th>Term III</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        $schoolfee_total1 = 0;
        $schoolfee_total2 = 0;
        $schoolfee_total3 = 0;
        $bookfee_total = 0;
        $transportfee_total1 = 0;
        $transportfee_total2 = 0;
        $transportfee_total3 = 0;
        $lastyear_total = 0;
        $total = 0;
        while ($startdate <= $feesToDate) {
            $from_date = $startdate->format('Y-m-d');

            //School fee
            $getCollectedFeesQry = $connect->query("SELECT COALESCE( SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%I%' 
                     AND gcf.grp_particulars NOT LIKE '%II%' 
                     AND gcf.grp_particulars NOT LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ),0) AS grp_fee_t1,
        
           COALESCE( SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%II%' 
                     AND gcf.grp_particulars NOT LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ),0) AS grp_fee_t2,
        
           COALESCE( SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ),0) AS grp_fee_t3 FROM `admission_fees` af JOIN admission_fees_details afd ON af.id = afd.admission_fees_ref_id LEFT JOIN group_course_fee gcf 
            ON afd.fees_id = gcf.grp_course_id WHERE (MONTH(af.receipt_date) = MONTH('$from_date') AND YEAR(af.receipt_date) = YEAR('$from_date') )  AND afd.fees_table_name ='grptable' AND af.school_id = '$school_id' ");
            $collectedFeesInfo = $getCollectedFeesQry->fetchObject();

            //Book feee
            $getBookFeesQry = $connect->query("SELECT COALESCE(SUM(afd.fee_received),0) AS bookFees FROM `admission_fees` af JOIN admission_fees_details afd ON af.id = afd.admission_fees_ref_id WHERE (MONTH(af.receipt_date) = MONTH('$from_date') AND YEAR(af.receipt_date) = YEAR('$from_date') ) AND afd.fees_table_name ='amenitytable' AND af.school_id = '$school_id' ");
            $bookFeesInfo = $getBookFeesQry->fetchObject();

            //Transport fee
            $getTransportFeesQry = $connect->query("SELECT COALESCE(SUM(CASE WHEN acp.particulars LIKE '%I%' AND acp.particulars NOT LIKE '%II%' AND acp.particulars NOT LIKE '%III%' THEN tafd.fee_received ELSE 0 END ), 0) AS transport_fee_t1,
        COALESCE(SUM(CASE WHEN acp.particulars LIKE '%II%' AND acp.particulars NOT LIKE '%III%' THEN tafd.fee_received ELSE 0 END),0) AS transport_fee_t2,
       COALESCE( SUM(CASE WHEN acp.particulars LIKE '%III%' THEN tafd.fee_received ELSE 0 END),0) AS transport_fee_t3  FROM `transport_admission_fees` taf JOIN transport_admission_fees_details tafd ON taf.id = tafd.admission_fees_ref_id JOIN area_creation_particulars acp ON tafd.area_creation_particulars_id = acp.particulars_id WHERE (MONTH(taf.receipt_date) = MONTH('$from_date') AND YEAR(taf.receipt_date) = YEAR('$from_date') ) AND taf.school_id = '$school_id' ");
            $transportFeesInfo = $getTransportFeesQry->fetchObject();

            //Last Year Fee
            $getLastyearFeesQry = $connect->query("SELECT COALESCE(SUM(lyfd.fee_received),0) AS lastyearFees FROM `last_year_fees` lyf JOIN last_year_fees_details lyfd ON lyf.id = lyfd.admission_fees_ref_id WHERE (MONTH(lyf.receipt_date) = MONTH('$from_date') AND YEAR(lyf.receipt_date) = YEAR('$from_date') ) AND lyf.school_id = '$school_id' ");
            $lastyearFeesInfo = $getLastyearFeesQry->fetchObject();
        ?>

            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $startdate->format('M-Y'); ?></td>
                <td><?php echo $collectedFeesInfo->grp_fee_t1; ?></td>
                <td><?php echo $collectedFeesInfo->grp_fee_t2; ?></td>
                <td><?php echo $collectedFeesInfo->grp_fee_t3; ?></td>
                <td><?php echo $bookFeesInfo->bookFees; ?></td>
                <td><?php echo $transportFeesInfo->transport_fee_t1; ?></td>
                <td><?php echo $transportFeesInfo->transport_fee_t2; ?></td>
                <td><?php echo $transportFeesInfo->transport_fee_t3; ?></td>
                <td><?php echo $lastyearFeesInfo->lastyearFees; ?></td>
                <td><?php echo $totalAmnt = $collectedFeesInfo->grp_fee_t1 +  $collectedFeesInfo->grp_fee_t2 +  $collectedFeesInfo->grp_fee_t3 + $bookFeesInfo->bookFees + $transportFeesInfo->transport_fee_t1 + $transportFeesInfo->transport_fee_t2 + $transportFeesInfo->transport_fee_t3 +  $lastyearFeesInfo->lastyearFees; ?></td>
            </tr>

        <?php
            $schoolfee_total1 += $collectedFeesInfo->grp_fee_t1;
            $schoolfee_total2 += $collectedFeesInfo->grp_fee_t2;
            $schoolfee_total3 += $collectedFeesInfo->grp_fee_t3;
            $bookfee_total += $bookFeesInfo->bookFees;
            $transportfee_total1 += $transportFeesInfo->transport_fee_t1;
            $transportfee_total2 += $transportFeesInfo->transport_fee_t2;
            $transportfee_total3 += $transportFeesInfo->transport_fee_t3;
            $lastyear_total += $lastyearFeesInfo->lastyearFees;
            $total += $totalAmnt;
            $startdate->modify('+1 month');
        } ?>
        <tr style="font-weight: bold;">
            <td><?php echo $i; ?></td>
            <td>Grand Total</td>
            <td><?php echo $schoolfee_total1; ?></td>
            <td><?php echo $schoolfee_total2; ?></td>
            <td><?php echo $schoolfee_total3; ?></td>
            <td><?php echo $bookfee_total; ?></td>
            <td><?php echo $transportfee_total1; ?></td>
            <td><?php echo $transportfee_total2; ?></td>
            <td><?php echo $transportfee_total3; ?></td>
            <td><?php echo $lastyear_total; ?></td>
            <td><?php echo $total; ?></td>
        </tr>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#show_monthwise_fees_summary').DataTable({
            order: [
                [0, "asc"]
            ],
            // columnDefs: [
            //     { type: 'natural', targets: 0 }
            // ],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            paging: false, // Disable paging
        });
    });
</script>
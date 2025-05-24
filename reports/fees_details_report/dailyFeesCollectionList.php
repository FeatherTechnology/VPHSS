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
$getbrc = $mysqli->query("SELECT sc.school_name, sc.district, sc.pincode FROM school_creation sc WHERE sc.status = 0 AND school_id = '$school_id'");
while ($schoolInfo = $getbrc->fetch_assoc()) {
    $school_name     = $schoolInfo["school_name"];
    $district  = $schoolInfo["district"];
    $pincode  = $schoolInfo["pincode"];
}
?>

<table class="table table-bordered" id="show_student_fees_summary_list">
    <thead>
        <tr>
            <th colspan='20' class="report-title">Fees Summary Report From <?php echo $feesFromDate->format('d-m-Y'); ?> To <?php echo $feesToDate->format('d-m-Y'); ?> </th>
        </tr>
        <tr>
            <th rowspan="2">S.No</th>
            <th rowspan="2">Date</th>
            <th rowspan="2">Receipt No</th>
            <th rowspan="2">Admission No</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2">Standard - Section</th>
            <th rowspan="2">Last Year Fee </th>
            <th rowspan="2">Books</th>
            <th colspan="3">Group Fees</th>
            <th colspan="3">Transport Fees</th>
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
        $admissionfee_total = 0;
        $extra_total = 0;
        $transportfee3_total = 0;
        $schoolfee3_total = 0;
        $schoolfee2_total = 0;
        $schoolfee1_total = 0;
        $bookfee_total = 0;
        $transportfee1_total = 0;
        $transportfee2_total = 0;
        $lastyear_total = 0;
        $uniformfee_total = 0;
        $cash_total = 0;
        $bank_total = 0;
        $total = 0;

        $a = 1;
        while ($startdate <= $feesToDate) {
            $from_date = $startdate->format('Y-m-d');

            $getFeeCollectionQry = $connect->query("SELECT 
            receipt_no,
            admission_number,
            student_name,
            standard,
            section,
            SUM(grp_fee_t1) AS grp_fee_t1,
            SUM(grp_fee_t2) AS grp_fee_t2,
            SUM(grp_fee_t3) AS grp_fee_t3,
            SUM(book_fees) AS book_fees,
            SUM(transport_fee_t1) AS transport_fee_t1,
            SUM(transport_fee_t2) AS transport_fee_t2,
            SUM(transport_fee_t3) AS transport_fee_t3,
            SUM(lastyearFees) AS lastyearFees
        FROM (
            SELECT
            *
        FROM
            (
            SELECT
            af.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section,
            
            -- Group Fee Terms
            SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%I%' 
                     AND gcf.grp_particulars NOT LIKE '%II%' 
                     AND gcf.grp_particulars NOT LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ) AS grp_fee_t1,
        
            SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%II%' 
                     AND gcf.grp_particulars NOT LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ) AS grp_fee_t2,
        
            SUM(
                CASE 
                    WHEN afd.fees_table_name = 'grptable' 
                     AND gcf.grp_particulars LIKE '%III%' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ) AS grp_fee_t3,
        
            -- Extra fee
            SUM(
                CASE 
                    WHEN afd.fees_table_name = 'amenitytable' 
                    THEN afd.fee_received 
                    ELSE 0 
                END
            ) AS book_fees,
            -- Transport fee placeholders
            0 AS transport_fee_t1,
            0 AS transport_fee_t2,
            0 AS transport_fee_t3,
        
            -- Last year fee placeholder
            0 AS lastyearFees
        FROM admission_fees af
        JOIN admission_fees_details afd 
            ON af.id = afd.admission_fees_ref_id
        JOIN student_creation sc 
            ON af.admission_id = sc.student_id
        JOIN student_history sh 
            ON sh.student_id = sc.student_id 
            AND af.academic_year = sh.academic_year
        JOIN standard_creation std 
            ON sh.standard = std.standard_id
        JOIN group_course_fee gcf 
            ON afd.fees_id = gcf.grp_course_id
        WHERE af.receipt_date = '$from_date' AND afd.fees_table_name != 'extratable'
            AND afd.fee_received > 0 
            AND sc.school_id = '$school_id' 
            AND sc.status = 0
        
        GROUP BY
            af.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section
        UNION ALL
        SELECT
            taf.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section,
            0 AS grp_fee_t1,
            0 AS grp_fee_t2,
            0 AS grp_fee_t3,
            0 AS book_fees,
        SUM(CASE WHEN acp.particulars LIKE '%I%' AND acp.particulars NOT LIKE '%II%' AND acp.particulars NOT LIKE '%III%' THEN tafd.fee_received ELSE 0 END) AS transport_fee_t1,
        SUM(CASE WHEN acp.particulars LIKE '%II%' AND acp.particulars NOT LIKE '%III%' THEN tafd.fee_received ELSE 0 END) AS transport_fee_t2,
        SUM(CASE WHEN acp.particulars LIKE '%III%' THEN tafd.fee_received ELSE 0 END) AS transport_fee_t3,
            0 AS lastyearFees
        FROM
            transport_admission_fees taf
        JOIN transport_admission_fees_details tafd 
            ON taf.id = tafd.admission_fees_ref_id
        JOIN student_creation sc 
            ON taf.admission_id = sc.student_id
        JOIN student_history sh 
            ON sh.student_id = sc.student_id AND taf.academic_year = sh.academic_year
        JOIN standard_creation std 
            ON sh.standard = std.standard_id
        JOIN area_creation ac 
            ON sh.transportarearefid = ac.area_id
        JOIN area_creation_particulars acp 
            ON tafd.area_creation_particulars_id = acp.particulars_id
        WHERE
            taf.receipt_date = '$from_date' 
            AND tafd.fee_received > 0 
            AND sc.school_id = '$school_id' 
            AND sc.status = 0
        GROUP BY
            taf.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section
        
        UNION ALL
        SELECT
            lyf.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section,
            0 AS grp_fee_t1,
            0 AS grp_fee_t2,
            0 AS grp_fee_t3,
            0 AS book_fees,
            0 AS transport_fee_t1,
            0 AS transport_fee_t2,
            0 AS transport_fee_t3,
            SUM(lyfd.fee_received) AS lastyearFees
        FROM
            last_year_fees lyf
        JOIN last_year_fees_details lyfd ON
            lyf.id = lyfd.admission_fees_ref_id
        JOIN last_year_fees_denomination lyfd_deno ON
            lyf.id = lyfd_deno.admission_fees_ref_id
        JOIN student_creation sc ON
            lyf.admission_id = sc.student_id
        JOIN student_history sh ON
            sh.student_id = sc.student_id AND lyf.academic_year = sh.academic_year
        JOIN standard_creation STD ON
            sh.standard = std.standard_id
        WHERE
            lyf.receipt_date = '$from_date' AND lyfd.fee_received > 0 AND sc.school_id = '$school_id' AND sc.status = 0
        GROUP BY
                lyfd.id,
            lyf.receipt_no,
            sc.admission_number,
            sc.student_name,
            std.standard,
            sh.section
        ) AS combined_result
        ORDER BY
            CAST(
                SUBSTRING(
                    receipt_no,
                    LOCATE('-', receipt_no) + 1
                ) AS UNSIGNED
            )
        ) AS combined_result
        GROUP BY receipt_no, admission_number, student_name, standard, section
        ORDER BY CAST( SUBSTRING( receipt_no, LOCATE('-', receipt_no) + 1 ) AS UNSIGNED ) ");

            while ($feeCollection = $getFeeCollectionQry->fetchObject()) {
        ?>

                <tr>
                    <td ><?php echo $a++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($from_date)); ?></td>
                    <td><?php echo $feeCollection->receipt_no; ?></td>
                    <td><?php echo $feeCollection->admission_number; ?></td>
                    <td><?php echo $feeCollection->student_name; ?></td>
                    <td><?php echo $feeCollection->standard . ' - ' . $feeCollection->section; ?></td>
                    <td><?php echo $feeCollection->lastyearFees; ?></td>
                    <td><?php echo $feeCollection->book_fees; ?></td>
                    <td><?php echo $feeCollection->grp_fee_t1; ?></td>
                    <td><?php echo $feeCollection->grp_fee_t2; ?></td>
                    <td><?php echo $feeCollection->grp_fee_t3; ?></td>
                    <td><?php echo $feeCollection->transport_fee_t1; ?></td>
                    <td><?php echo $feeCollection->transport_fee_t2; ?></td>
                    <td><?php echo $feeCollection->transport_fee_t3; ?></td>
                 
                    <td><?php echo $totalAmnt = $feeCollection->grp_fee_t1 + $feeCollection->grp_fee_t2  + $feeCollection->grp_fee_t3 + $feeCollection->book_fees + $feeCollection->transport_fee_t1 + $feeCollection->transport_fee_t2 + $feeCollection->transport_fee_t3 + $feeCollection->lastyearFees; ?></td>
                </tr>

        <?php
             
                $bookfee_total += $feeCollection->book_fees;
                $schoolfee1_total += $feeCollection->grp_fee_t1;
                $schoolfee2_total += $feeCollection->grp_fee_t2;
                $schoolfee3_total += $feeCollection->grp_fee_t3;
                $transportfee1_total += $feeCollection->transport_fee_t1;
                $transportfee2_total += $feeCollection->transport_fee_t2;
                $transportfee3_total += $feeCollection->transport_fee_t3;
                $lastyear_total += $feeCollection->lastyearFees;
               
                $total += $totalAmnt;
            }

            $startdate->modify('+1 day');
        } //End of While loop for getting dates from start to end date. 
        ?>
        <tr style="font-weight: bold;">
            <td><?php echo $a; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Grand Total</td>
            <td><?php echo $lastyear_total; ?></td>
            <td><?php echo $bookfee_total; ?></td>
            <td><?php echo $schoolfee1_total; ?></td>
            <td><?php echo $schoolfee2_total; ?></td>
            <td><?php echo $schoolfee3_total; ?></td>
            <td><?php echo $transportfee1_total; ?></td>
            <td><?php echo $transportfee2_total; ?></td>
            <td><?php echo $transportfee3_total; ?></td>
            <td><?php echo $total; ?></td>
        </tr>
    </tbody>
</table>

<!-- <script>
    $(document).ready(function () {
        var schoolName = "<?php echo $school_name . ' - ' . $district . ' - ' . $pincode; ?>";
        var feeHeading = "<?php echo 'Fees Summary Report From ' . $feesFromDate->format('d-m-Y') . ' To ' . $feesToDate->format('d-m-Y'); ?>";

        $('#show_student_fees_summary_list').DataTable({
            order: [[0, "asc"]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf',
                {
                    extend: 'print',
                    text: 'Print',
                    customize: function (win) {
                        var thead = `
                            <thead>
                                <tr>
                                    <th rowspan="2">S.No</th>
                                    <th rowspan="2">Date</th>
                                    <th rowspan="2">Receipt No</th>
                                    <th rowspan="2">Admission No</th>
                                    <th rowspan="2">Student Name</th>
                                    <th rowspan="2">Standard - Section</th>
                                    <th rowspan="2">Last Year Fee</th>
                                    <th rowspan="2">Books</th>
                                    <th colspan="3">Group Fees</th>
                                    <th colspan="3">Transport Fees</th>
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
                        `;

                        $(win.document.body).prepend(
                            '<h2 style="text-align:center;">' + schoolName + '</h2>' +
                            '<h4 style="text-align:center;">' + feeHeading + '</h4><br>'
                        );
                    
                    },
                    autoPrint: true
                }
            ],
            paging: false,
            ordering: false
        });
    });
</script> -->
<script>
    $(document).ready(function() {
            var schoolName = "<?php echo $school_name . ' - ' . $district . ' - ' . $pincode; ?>";
        var feeHeading = "<?php echo 'Fees Summary Report From ' . $feesFromDate->format('d-m-Y') . ' To ' . $feesToDate->format('d-m-Y'); ?>";
        $('#show_student_fees_summary_list').DataTable({
            order: [
                [0, "asc"]
            ],
            // columnDefs: [
            //     { type: 'natural', targets: 0 }
            // ],
            dom: 'Bfrtip',
           buttons: [
                'copy', 'csv', 'excel', 'pdf',
                {
                    extend: 'print',
                    text: 'Print',
                    title: '',
                    customize: function(win) {
                         var thead = `
                            <thead>
                                <tr>
                                    <th rowspan="2">S.No</th>
                                    <th rowspan="2">Date</th>
                                    <th rowspan="2">Receipt No</th>
                                    <th rowspan="2">Admission No</th>
                                    <th rowspan="2">Student Name</th>
                                    <th rowspan="2">Standard - Section</th>
                                    <th rowspan="2">Last Year Fee</th>
                                    <th rowspan="2">Books</th>
                                    <th colspan="3">Group Fees</th>
                                    <th colspan="3">Transport Fees</th>
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
                        `;
   $(win.document.body).find('table').html(thead + $(win.document.body).find('table tbody').html());

                        $(win.document.body).css('width', '100%');
                        $(win.document.body).prepend(
                            '<h2 style="text-align:center;">' + schoolName + '</h2>' +
                            '<h4 style="text-align:center;">' + feeHeading + '</h4><br>'
                        );
                    }
                }
            ],
            paging: false, // Disable paging
        });
    });
</script>
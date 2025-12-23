 <?php
    $getBankDetails = $userObj->getBankDetails($mysqli);
    ?>
 <!-- Page header start -->
 <div class="page-header">
     <ol class="breadcrumb">
         <li class="breadcrumb-item">SM - Bank Fees Collection Report </li>
     </ol>
 </div>
 <!-- Page header end -->


 <!-- Main container start -->
 <div class="main-container">
     <!--form start-->
     <form id="student_fees_collection_form" name="student_fees_collection_form" method="post" enctype="multipart/form-data">
         <!-- card start -->
         <div class="card">
             <div class="card-body">
                 <div class="row">
                     <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12"></div>
                     <div class="col-sm-4 col-md-4 col-lg-4 col-xl-3 col-12">
                         <div class="form-group">
                             <label>From Date</label>
                             <input type="date" class="form-control" id="from_date" name="from_date" tabindex="1">
                         </div>
                     </div>

                     <div class="col-sm-4 col-md-4 col-lg-4 col-xl-3 col-12">
                         <div class="form-group">
                             <label>To Date</label>
                             <input type="date" class="form-control" id="to_date" name="to_date" tabindex="2">
                         </div>
                     </div>
                     <div class="col-sm-4 col-md-4 col-lg-4 col-xl-3 col-12">
                         <div class="form-group">
                             <label>Bank Name</label>
                             <select type="text" class="form-control" id="bank_name" name="bank_name" tabindex="1">
                                 <option value="">Select Bank Name</option>
                                 <?php
                                    if (sizeof($getBankDetails) > 0) {
                                        for ($i = 0; $i < sizeof($getBankDetails); $i++) {
                                            $bank_id = $getBankDetails[$i]['id'];
                                            $bank_name = $getBankDetails[$i]['short_name'];

                                            // // Check if this is the selected bank ID
                                            // $selected = ($bank_id == $selected_bank_id) ? 'selected' : '';
                                    ?>
                                         <option value="<?php echo $bank_id; ?>">
                                             <?php echo $bank_name; ?>
                                         </option>
                                 <?php
                                        }
                                    }
                                    ?>
                             </select>
                         </div>
                     </div>
                     <br><br><br><br><br><br>

                     <div class="col-xl-5 col-lg-5 col-md-5 col-sm-5 col-12"></div>
                     <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                         <div class="form-group">
                             <input type="button" name="fees_collection_table_view_btn" id="fees_collection_table_view_btn" class="btn btn-primary" value="View" tabindex="3">
                         </div>
                     </div>

                 </div>
             </div>
         </div>
         <!-- card END -->

         <div class="card" id="listCard" style="display: none;">
             <div class="card-body">
                 <div id="showStudentDailyFeesCollectionList"></div>
             </div>
         </div>
     </form>
     <!--form END-->
 </div>
 <!-- Main container END -->
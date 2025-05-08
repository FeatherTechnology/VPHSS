<?php
@session_start();
if (isset($_SESSION["userid"])) {
    $userid = $_SESSION["userid"];
    $school_id = $_SESSION["school_id"];
    $year_id = $_SESSION["academic_year"];
    $academic_year = $_SESSION["academic_year"];
}

$StudentList = $userObj->getStudentList($mysqli, $school_id, $year_id);
?>

<!-- Page header start -->
<div class="page-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">SM - Staff Subject Allocation </li>
    </ol>
</div>
<!-- Page header end -->

<!-- Main container start -->
<div class="main-container">
    <!--form start-->
    <form id="exam_creation_form" name="exam_creation_form" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" class="form-control" value="<?php if (isset($fees_id)) echo $fees_id; ?>" id="id" name="id">
        <input type="hidden" class="form-control" name="admission_form_id" id="admission_form_id" value="<?php if (isset($_GET['studid'])) echo $_GET['studid']; ?>">
        <!-- Row start -->
        <div class="row gutters">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row ">
                            <!--Fields -->
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Standard List</label>
                                            <select class="form-control" id="standard" name="standard">
                                                <option value=" ">Select  Standard...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>Section</label>
                                            <select class="form-control" id="section" name="section">
                                                <option value=" ">Select Section</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                                        <button type="button" name="view_subject" id="view_subject" class="btn btn-primary" value="View" style="margin-top: 18px;" tabindex="4">View</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card subject_allocate" style="display: none;">

                </div>
                <div class="card subject_card" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">Subject Details</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <table id="subject_info" class="table table-bordered text-center custom-table" style="width: 100%;">
                                        <thead style="background-color: #5DADE2; color: white;">
                                            <tr>
                                                <th width="50">S.NO</th>
                                                <th>Paper Name</th>
                                                <th>Staff</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <!-- <div class="text-end mt-3">  -->
                                    <div class="text-right mt-3">
                                        <button type="button" class="btn btn-primary" id="submit_staff_create">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
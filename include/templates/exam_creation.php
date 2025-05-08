
<!-- Page header start -->
<div class="page-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">SM - Exam Creation </li>
    </ol>
</div>
<!-- Page header end -->

<!-- Main container start -->
<div class="main-container">
    <!--form start-->
    <div class="radio-container">
        <div class="selector">
            <div class="selector-item">
                <input type="radio" id="out_of_mark" name="exam_create" class="selector-item_radio" value="mark_create" checked>
                <label for="out_of_mark" class="selector-item_label">Out of Mark Creation</label>
            </div>
            <div class="selector-item">
                <input type="radio" id="mrk_report" name="exam_create" class="selector-item_radio" value="mark_report">
                <label for="mrk_report" class="selector-item_label">Report</label>
            </div>
        </div>
    </div><br>
    <form id="exam_creation_form" name="exam_creation_form" action="" method="post" enctype="multipart/form-data">
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
                                                <option value=" ">Select a Standard...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-3 col-md-5 col-sm-5 col-12">
                                        <div class="form-group">
                                            <label>Exam</label>
                                            <select class="form-control" id="exam" name="exam">
                                                <option value=" ">Select Exam</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 col-md-1 col-lg-1 text-right" style="margin-top: 18px;">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary modalBtnCss" data-toggle="modal" style="width: 75px;" data-target="#add_exam_modal" tabindex="14" onclick="getExamTable()"><span class="icon-add"></span></button>
                                        </div>
                                    </div><br>
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <button type="button" name="view_exam" id="view_exam" class="btn btn-primary" value="View" tabindex="4">View</button>
                                        </div>
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
                                                <th>Out of Mark</th>
                                                <th>Pass %</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <!-- <div class="text-end mt-3">  -->
                                    <div class="text-right mt-3"> 
                                        <button type="button" class="btn btn-primary" id="submit_exam_create">
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
    <!---- Report Start-->
    <form id="exam_report_form" name="exam_report_form" action="" method="post" enctype="multipart/form-data" style="display: none;">
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
                                            <select class="form-control " id="standard_id" name="standard_id">
                                                <option value=" ">Select a Standard...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button type="button" name="view_report" id="view_report" class="btn btn-primary" value="View" tabindex="4">View</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card sub_report" style="display: none;">
                    <div class="card-header">
                        <div class="card-title"> Subject Details
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" id="report_info_table_div" style="overflow: auto;">
                                
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
<!--Exam Info Modal-->
<div class="modal fade" id="add_exam_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Exam Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="getExamDropdown()" tabindex="1">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="academic_form">
                        <div class="row justify-content-center">
                            <input type="hidden" name="exam_id" id='exam_id'>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12  ">
                                <div class="form-group">
                                    <label for="exam_type">Exam Type</label><span class="text-danger">*</span>
                                    <input type="text" class="form-control" name="exam_type" id="exam_type" tabindex="1">
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4">
                                <div class="form-group">
                                    <label for="" style="visibility:hidden"></label><br>
                                    <button name="submit_exam" id="submit_exam" class="btn btn-primary" tabindex="1"><span class="icon-check"></span>&nbsp;Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-12 overflow-x-cls" style="width: 100%;">
                        <table id="exam_creation_table" class="custom-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="10%">S.No.</th>
                                    <th width="30%">Exam Type</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="getExamDropdown()" tabindex="1">Close</button>
            </div>
        </div>
    </div>
</div>
<!--Exam Modal End-->
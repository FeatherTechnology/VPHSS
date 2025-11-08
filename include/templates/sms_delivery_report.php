<!-- Page header start -->
<div class="page-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">SM - SMS Delivery Report </li>
    </ol>
</div>
<!-- Page header end -->

<!-- Main container start -->
<div class="main-container">
    <!--form start-->
    <form id="delivery_form" name="delivery_form" action="" method="post" enctype="multipart/form-data">
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
                                                <option value=" ">Select Standard...</option>
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
                                        <div class="form-group">
                                            <label>Exam</label>
                                            <select class="form-control" id="exam" name="exam">
                                                <option value=" ">Select Exam</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                                        <button type="button" name="view_student" id="view_student" class="btn btn-primary" value="View" style="margin-top: 18px;" tabindex="4">View</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card report_card" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">SMS Delivery List</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" id="mark_info_table_div" style="overflow: auto;">  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
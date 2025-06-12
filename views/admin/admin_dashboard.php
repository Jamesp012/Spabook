<div class="dashboard-view">
    <div class="row row-cols-1 row-cols-sm-3">
        <div class="col mb-4">
            <div class="card rounded-1 custom_card_hover border-0 shadow active" onclick="loadDashboardData('Bookings')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate">
                            <span class="fs-6">Total Bookings</span>
                            <div class="fs-2 fw-bold" id="filebackup_pending_count">1</div>
                            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Accepted Booking: 100</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-4">
            <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('History')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate">
                            <span class="fs-6">Appointment History</span>
                            <div class="fs-2 fw-bold" id="filebackup_ongoing_count">1</div>
                            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Appointment History</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-4">
            <div class="card rounded-1 custom_card_hover border-0 shadow" onclick="loadDashboardData('Recovery')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate">
                            <span class="fs-6">Update Recovery</span>
                            <div class="fs-2 fw-bold" id="filebackup_received_count">1</div>
                            <span class="fs-6"><i class="pe-2 bi bi-arrow-up"></i>Tota Updated Recovery: 10</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card rounded-1 shadow mb-4">
        <div class="card-header rounded-top-1">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 mt-2">
                    <span class="fs-25 fw-bold text-dark align-middle" id="active_dashboard"></span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- ==================== Cancelled Request Table ==================== -->
            <div class="table-responsive" id="booking_table_container">
                <table id="total_booking_table" class="table table-striped" width="100%">
                    <thead class="table-secondary">
                        <tr>
                            <th>CLIENT</th>
                            <th>SERVICES</th>
                            <th>DATE & TIME</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>
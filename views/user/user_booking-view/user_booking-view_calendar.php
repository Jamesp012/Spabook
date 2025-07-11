<div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
    <button class="btn btn-sm btn-light" id="prev"><i class="bi bi-chevron-left"></i></button>
    <h5 class="mb-0" id="calendar-month">June 2025</h5>
    <button class="btn btn-sm btn-light" id="next"><i class="bi bi-chevron-right"></i></button>
</div>
<div class="card-body p-0">
    <table class="calendar-table table table-bordered text-center mb-0">
        <thead>
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody id="calendar-body">
            <!-- Calendar will render here -->
        </tbody>
    </table>
</div>
<style>
    .calendar-table td {
        width: 14.28%;
        text-align: center;
        vertical-align: middle;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <!-- Calendar (left side) -->
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-light" id="prev"><i class="bi bi-chevron-left"></i></button>
                    <h5 class="mb-0" id="calendar-month">June 2025</h5>
                    <button class="btn btn-sm btn-light" id="next"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered text-center mb-0">
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
            </div>

            <!-- Recent Services (still on left side under calendar) -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                    <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                    <li class="list-group-item">Shiatsu - June 2, 2025</li>
                </ul>
            </div>
        </div>

        <!-- Appointment Form (right side) -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Book an Appointment</h5>
                </div>
                <div class="card-body">
                    <form id="appointment-form">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3">
                            <label for="service" class="form-label">Service</label>
                            <select class="form-select" id="service" required>
                                <option selected disabled value="">Choose a service</option>
                                <option>Swedish Massage</option>
                                <option>Shiatsu</option>
                                <option>Facial Treatment</option>
                                <option>Body Scrub</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Calendar Script -->
<script>
function initializeCalendar() {
    const calendarBody = document.getElementById('calendar-body');
    const calendarMonth = document.getElementById('calendar-month');
    const appointmentDateInput = document.getElementById('date');
    let currentDate = new Date();

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"];
        calendarMonth.textContent = `${monthNames[month]} ${year}`;

        let html = '<tr>';
        for (let i = 0; i < firstDay; i++) {
            html += '<td></td>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const fullDate = new Date(year, month, day).toISOString().split("T")[0];
            html += `<td class="calendar-cell" data-date="${fullDate}">${day}</td>`;
            if ((firstDay + day) % 7 === 0) html += '</tr><tr>';
        }

        html += '</tr>';
        calendarBody.innerHTML = html;

        document.querySelectorAll('.calendar-cell').forEach(cell => {
            cell.style.cursor = 'pointer';
            cell.onclick = () => {
                appointmentDateInput.value = cell.dataset.date;
                document.querySelectorAll('.calendar-cell').forEach(c => c.classList.remove('bg-info', 'text-white'));
                cell.classList.add('bg-info', 'text-white');
            };
        });
    }

    document.getElementById('prev').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    document.getElementById('next').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    renderCalendar(currentDate);
}
</script>

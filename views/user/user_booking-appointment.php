<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-8 mb-1">
            <div class="card " id="cardView" style="height: calc(100vh - 300px)">
                
            </div>

            <!-- Recent Services (still on left side under calendar) -->
            <div class="card mt-2">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Recent Services</h5>
                </div>
                <div class="recent-services-scroll" >
                    <ul class="list-group list-group-flush" style="overflow-y: auto; max-height: 120px;">
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                        <li class="list-group-item">Swedish Massage - June 9, 2025</li>
                        <li class="list-group-item">Facial Treatment - June 5, 2025</li>
                        <li class="list-group-item">Shiatsu - June 2, 2025</li>
                    <!-- Add more to test scroll -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Appointment Form (right side) -->
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Book an Appointment</h5>
                </div>

                <!-- Add style to limit height and enable scroll -->
                <div class="card-body p-3" style="max-height: 70vh; overflow-y: auto;">
                    <form id="appointment-form">
                        <div class="mb-3">
                            <label for="service" class="form-label">Select Services</label>
                            <input type="text" class="form-control" id="service" data-content="user_booking-view_services.php" placeholder="Click to choose services" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" placeholder="Enter first name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" placeholder="Enter last name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" placeholder="Enter contact number" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email address" required>
                        </div>
                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" placeholder="Enter your age" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" data-content="user_booking-view_calendar.php" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Complete Address</label>
                            <textarea class="form-control" id="address" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Book Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Views Script
$(document).ready(function () {
    // Load calendar view on page load
    loadCardView('user_booking-view_calendar.php');

    // When the service input is focused or clicked
    $('#service').on('focus click', function () {
        loadCardView('user_booking-view_services.php');
    });

    // When the date input is focused or clicked
    $('#date').on('focus click', function () {
        loadCardView('user_booking-view_calendar.php');
    });

    // Back to calendar view button
    $(document).on('click', '#back-to-calendar', function () {
        loadCardView('user_booking-view_calendar.php');
    });

    // AJAX loader function for calendar/services view
    function loadCardView(viewFile) {
        $('#cardView').fadeOut(200, function () {
            $.ajax({
                url: `/SpaBook/views/user/user_booking-view/${viewFile}`,
                method: 'GET',
                success: function (data) {
                    $('#cardView').html(data).fadeIn(200);
                    if (viewFile === 'user_booking-view_calendar.php') {
                        initializeCalendar();
                    }
                },
                error: function () {
                    $('#cardView').html('<div class="alert alert-danger">Failed to load view.</div>').fadeIn(200);
                }
            });
        });
    }
});

// Calendar Script
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

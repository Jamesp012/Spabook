<!-- Add Notes Modal -->
<div class="modal fade" id="addNotesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note me-2"></i>Add Patient Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addNotesForm">
                    <input type="hidden" id="add_booking_detail_id" name="booking_detail_id">
                    <input type="hidden" id="add_patient_id" name="patient_id">
                    
                    <!-- Patient Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Patient Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <span id="add_patient_name"></span></p>
                                    <p class="mb-0"><strong>Service:</strong> <span id="add_service_name"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Session Information</h6>
                                    <p class="mb-1"><strong>Date:</strong> <span id="add_session_date"></span></p>
                                    <p class="mb-0"><strong>Time:</strong> <span id="add_session_time"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Main Notes -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_notes" class="form-label">Session Notes</label>
                                <textarea class="form-control" id="add_notes" name="notes" rows="6" 
                                          placeholder="Describe the session, patient's condition, observations, etc."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="add_treatment_progress" class="form-label">Treatment Progress</label>
                                <textarea class="form-control" id="add_treatment_progress" name="treatment_progress" rows="3"
                                          placeholder="Patient's progress since last session..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Assessment & Recommendations -->
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="add_pain_level" class="form-label">Pain Level (1-10)</label>
                                    <select class="form-select" id="add_pain_level" name="pain_level">
                                        <option value="">Select level</option>
                                        <option value="1">1 - No pain</option>
                                        <option value="2">2 - Minimal</option>
                                        <option value="3">3 - Mild</option>
                                        <option value="4">4 - Mild-Moderate</option>
                                        <option value="5">5 - Moderate</option>
                                        <option value="6">6 - Moderate-Severe</option>
                                        <option value="7">7 - Severe</option>
                                        <option value="8">8 - Very Severe</option>
                                        <option value="9">9 - Extreme</option>
                                        <option value="10">10 - Unbearable</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="add_mobility_level" class="form-label">Mobility Level (1-10)</label>
                                    <select class="form-select" id="add_mobility_level" name="mobility_level">
                                        <option value="">Select level</option>
                                        <option value="1">1 - Bedridden</option>
                                        <option value="2">2 - Wheelchair bound</option>
                                        <option value="3">3 - Limited walking</option>
                                        <option value="4">4 - Walking with aid</option>
                                        <option value="5">5 - Slow walking</option>
                                        <option value="6">6 - Moderate walking</option>
                                        <option value="7">7 - Good walking</option>
                                        <option value="8">8 - Brisk walking</option>
                                        <option value="9">9 - Running capable</option>
                                        <option value="10">10 - Full mobility</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="add_recommendations" class="form-label">Recommendations</label>
                                <textarea class="form-control" id="add_recommendations" name="recommendations" rows="5"
                                          placeholder="Recommended exercises, follow-up treatments, lifestyle changes, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePatientNotes()">
                    <i class="fas fa-save me-1"></i>Save Notes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Notes Modal -->
<div class="modal fade" id="viewNotesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Patient Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewNotesContent">
                <!-- Notes content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editNotesBtn" onclick="editNotes()">
                    <i class="fas fa-edit me-1"></i>Edit Notes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Notes Modal -->
<div class="modal fade" id="editNotesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Patient Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editNotesForm">
                    <input type="hidden" id="edit_notes_id" name="notes_id">
                    
                    <!-- Patient Information (Read-only) -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Patient Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <span id="edit_patient_name"></span></p>
                                    <p class="mb-0"><strong>Service:</strong> <span id="edit_service_name"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Session Information</h6>
                                    <p class="mb-1"><strong>Date:</strong> <span id="edit_session_date"></span></p>
                                    <p class="mb-0"><strong>Time:</strong> <span id="edit_session_time"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Main Notes -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_notes" class="form-label">Session Notes</label>
                                <textarea class="form-control" id="edit_notes" name="notes" rows="6"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_treatment_progress" class="form-label">Treatment Progress</label>
                                <textarea class="form-control" id="edit_treatment_progress" name="treatment_progress" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <!-- Assessment & Recommendations -->
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_pain_level" class="form-label">Pain Level (1-10)</label>
                                    <select class="form-select" id="edit_pain_level" name="pain_level">
                                        <option value="">Select level</option>
                                        <option value="1">1 - No pain</option>
                                        <option value="2">2 - Minimal</option>
                                        <option value="3">3 - Mild</option>
                                        <option value="4">4 - Mild-Moderate</option>
                                        <option value="5">5 - Moderate</option>
                                        <option value="6">6 - Moderate-Severe</option>
                                        <option value="7">7 - Severe</option>
                                        <option value="8">8 - Very Severe</option>
                                        <option value="9">9 - Extreme</option>
                                        <option value="10">10 - Unbearable</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_mobility_level" class="form-label">Mobility Level (1-10)</label>
                                    <select class="form-select" id="edit_mobility_level" name="mobility_level">
                                        <option value="">Select level</option>
                                        <option value="1">1 - Bedridden</option>
                                        <option value="2">2 - Wheelchair bound</option>
                                        <option value="3">3 - Limited walking</option>
                                        <option value="4">4 - Walking with aid</option>
                                        <option value="5">5 - Slow walking</option>
                                        <option value="6">6 - Moderate walking</option>
                                        <option value="7">7 - Good walking</option>
                                        <option value="8">8 - Brisk walking</option>
                                        <option value="9">9 - Running capable</option>
                                        <option value="10">10 - Full mobility</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_recommendations" class="form-label">Recommendations</label>
                                <textarea class="form-control" id="edit_recommendations" name="recommendations" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="updatePatientNotes()">
                    <i class="fas fa-save me-1"></i>Update Notes
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-xl .modal-dialog {
    max-width: 1200px;
}

.card {
    border: 1px solid #dee2e6;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

#viewNotesContent .info-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

#viewNotesContent .notes-section {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.assessment-badges .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

.pain-level-1, .pain-level-2, .pain-level-3 { background-color: #28a745; }
.pain-level-4, .pain-level-5, .pain-level-6 { background-color: #ffc107; color: #212529; }
.pain-level-7, .pain-level-8, .pain-level-9, .pain-level-10 { background-color: #dc3545; }

.mobility-level-1, .mobility-level-2, .mobility-level-3 { background-color: #dc3545; }
.mobility-level-4, .mobility-level-5, .mobility-level-6 { background-color: #ffc107; color: #212529; }
.mobility-level-7, .mobility-level-8, .mobility-level-9, .mobility-level-10 { background-color: #28a745; }
</style>

<script>
// Global variable to store current appointment data
let currentAppointment = {};

function addNotes(bookingDetailId, patientName, serviceName, appointmentDate, appointmentTime, patientId) {
    currentAppointment = {
        bookingDetailId: bookingDetailId,
        patientName: patientName,
        serviceName: serviceName,
        appointmentDate: appointmentDate || '',
        appointmentTime: appointmentTime || '',
        patientId: patientId
    };
    
    // Populate the form
    $('#add_booking_detail_id').val(bookingDetailId);
    $('#add_patient_id').val(patientId);
    $('#add_patient_name').text(patientName);
    $('#add_service_name').text(serviceName);
    $('#add_session_date').text(appointmentDate || 'N/A');
    $('#add_session_time').text(appointmentTime || 'N/A');
    
    // Clear the form
    $('#addNotesForm')[0].reset();
    $('#add_booking_detail_id').val(bookingDetailId); // Reset hidden fields
    $('#add_patient_id').val(patientId);
    
    // Show the modal
    $('#addNotesModal').modal('show');
}

function savePatientNotes() {
    const formData = $('#addNotesForm').serialize();
    
    $.ajax({
        url: '../controller/therapist_schedule_contr.php',
        type: 'POST',
        dataType: 'json',
        data: formData + '&action=add_patient_notes',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Patient notes saved successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                $('#addNotesModal').modal('hide');
                refreshSchedule(); // Reload the schedule
                
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to save notes',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving notes:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to save notes. Please try again.',
                icon: 'error'
            });
        }
    });
}

function viewNotes(bookingDetailId) {
    $('#viewNotesContent').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading notes...</p></div>');
    $('#viewNotesModal').modal('show');
    
    $.ajax({
        url: '../controller/therapist_schedule_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_patient_notes',
            booking_detail_id: bookingDetailId
        },
        success: function(response) {
            if (response.status === 'success') {
                displayViewNotes(response.notes);
            } else {
                $('#viewNotesContent').html('<div class="alert alert-danger">Error loading notes: ' + (response.message || 'Unknown error') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#viewNotesContent').html('<div class="alert alert-danger">Error loading notes. Please try again.</div>');
        }
    });
}

function displayViewNotes(notes) {
    let painBadgeClass = '';
    let mobilityBadgeClass = '';
    
    if (notes.pain_level) {
        painBadgeClass = `pain-level-${notes.pain_level}`;
    }
    
    if (notes.mobility_level) {
        mobilityBadgeClass = `mobility-level-${notes.mobility_level}`;
    }
    
    const html = `
        <div class="info-section">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-user me-2"></i>Patient Information</h6>
                    <p><strong>Name:</strong> ${notes.patient_name}</p>
                    <p><strong>Service:</strong> ${notes.service_name}</p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-calendar me-2"></i>Session Information</h6>
                    <p><strong>Date:</strong> ${notes.booking_date}</p>
                    <p><strong>Time:</strong> ${notes.booking_time}</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>Created:</strong> ${notes.created_at}</p>
                </div>
                <div class="col-md-6">
                    ${notes.updated_at ? `<p><strong>Last Updated:</strong> ${notes.updated_at}</p>` : ''}
                </div>
            </div>
        </div>
        
        ${notes.pain_level || notes.mobility_level ? `
        <div class="notes-section">
            <h6><i class="fas fa-chart-line me-2"></i>Assessment</h6>
            <div class="assessment-badges">
                ${notes.pain_level ? `<span class="badge ${painBadgeClass}">Pain Level: ${notes.pain_level}/10</span>` : ''}
                ${notes.mobility_level ? `<span class="badge ${mobilityBadgeClass}">Mobility Level: ${notes.mobility_level}/10</span>` : ''}
            </div>
        </div>
        ` : ''}
        
        ${notes.notes ? `
        <div class="notes-section">
            <h6><i class="fas fa-sticky-note me-2"></i>Session Notes</h6>
            <p>${notes.notes.replace(/\n/g, '<br>')}</p>
        </div>
        ` : ''}
        
        ${notes.treatment_progress ? `
        <div class="notes-section">
            <h6><i class="fas fa-chart-bar me-2"></i>Treatment Progress</h6>
            <p>${notes.treatment_progress.replace(/\n/g, '<br>')}</p>
        </div>
        ` : ''}
        
        ${notes.recommendations ? `
        <div class="notes-section">
            <h6><i class="fas fa-lightbulb me-2"></i>Recommendations</h6>
            <p>${notes.recommendations.replace(/\n/g, '<br>')}</p>
        </div>
        ` : ''}
    `;
    
    $('#viewNotesContent').html(html);
    
    // Store notes data for editing
    $('#editNotesBtn').data('notes', notes);
}

function editNotes() {
    const notes = $('#editNotesBtn').data('notes');
    
    if (!notes) return;
    
    // Hide view modal and show edit modal
    $('#viewNotesModal').modal('hide');
    
    // Populate edit form
    $('#edit_notes_id').val(notes.id);
    $('#edit_patient_name').text(notes.patient_name);
    $('#edit_service_name').text(notes.service_name);
    $('#edit_session_date').text(notes.booking_date);
    $('#edit_session_time').text(notes.booking_time);
    
    $('#edit_notes').val(notes.notes || '');
    $('#edit_treatment_progress').val(notes.treatment_progress || '');
    $('#edit_pain_level').val(notes.pain_level || '');
    $('#edit_mobility_level').val(notes.mobility_level || '');
    $('#edit_recommendations').val(notes.recommendations || '');
    
    // Show edit modal
    $('#editNotesModal').modal('show');
}

function updatePatientNotes() {
    const formData = $('#editNotesForm').serialize();
    
    $.ajax({
        url: '../controller/therapist_schedule_contr.php',
        type: 'POST',
        dataType: 'json',
        data: formData + '&action=update_patient_notes',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Patient notes updated successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                $('#editNotesModal').modal('hide');
                refreshSchedule(); // Reload the schedule
                
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to update notes',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating notes:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to update notes. Please try again.',
                icon: 'error'
            });
        }
    });
}
</script>
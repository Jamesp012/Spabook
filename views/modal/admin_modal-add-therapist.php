<div class="modal-header">
    <h5 class="modal-title">Add New Therapist</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addTherapistForm">
        <!-- Therapist Photo -->
        <div class="mb-3 text-center">
            <label class="form-label">Profile Photo (Optional)</label>
            <div class="position-relative d-inline-block">
                <img id="therapistPhotoPreview" src="../vendor/images/default_profile.png" 
                     class="rounded-circle border" width="100" height="100" style="object-fit: cover;">
                <input type="file" id="therapistPhoto" class="form-control d-none" accept="image/*">
                <button type="button" class="btn btn-sm btn-outline-primary position-absolute bottom-0 end-0 rounded-circle" 
                        onclick="$('#therapistPhoto').click()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="row g-3">
            <div class="col-md-6">
                <label for="therapistFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="therapistFirstName" name="first_name" required>
            </div>
            <div class="col-md-6">
                <label for="therapistLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="therapistLastName" name="last_name" required>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label for="therapistEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="therapistEmail" name="email" required>
                <div class="form-text">This will be used for login</div>
            </div>
            <div class="col-md-6">
                <label for="therapistPassword" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="therapistPassword" name="password" required>
                <div class="form-text">Minimum 6 characters</div>
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label for="therapistContact" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="therapistContact" name="contact_number" 
                       placeholder="e.g., +63 912 345 6789">
            </div>
            <div class="col-md-6">
                <label for="therapistAddress" class="form-label">Address</label>
                <input type="text" class="form-control" id="therapistAddress" name="address">
            </div>
        </div>

        <!-- Therapist-Specific Information -->
        <hr class="my-4">
        <h6 class="text-primary mb-3">
            <i class="fas fa-spa me-2"></i>Professional Information
        </h6>

        <div class="mb-3">
            <label for="therapistSpecialties" class="form-label">Specialties</label>
            <textarea class="form-control" id="therapistSpecialties" name="specialties" rows="2" 
                      placeholder="e.g., Swedish massage, Deep tissue therapy, Reflexology"></textarea>
            <div class="form-text">List the therapist's areas of expertise</div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="therapistExperience" class="form-label">Years of Experience</label>
                <input type="number" class="form-control" id="therapistExperience" name="experience" 
                       min="0" max="50" placeholder="5">
            </div>
            <div class="col-md-6">
                <label for="therapistCertification" class="form-label">Certification</label>
                <input type="text" class="form-control" id="therapistCertification" name="certification" 
                       placeholder="e.g., Licensed Massage Therapist">
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label for="therapistBio" class="form-label">Professional Bio</label>
            <textarea class="form-control" id="therapistBio" name="bio" rows="3" 
                      placeholder="Brief description of the therapist's background and approach..."></textarea>
        </div>

        <!-- Status -->
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="therapistActive" name="is_active" checked>
                <label class="form-check-label" for="therapistActive">
                    Active Status
                </label>
                <div class="form-text">Inactive therapists won't be available for bookings</div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveTherapist()">
        <i class="fas fa-save me-1"></i>Add Therapist
    </button>
</div>

<script>
// Photo preview functionality
$('#therapistPhoto').on('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#therapistPhotoPreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }
});

function saveTherapist() {
    const form = $('#addTherapistForm')[0];
    const formData = new FormData(form);
    
    // Basic validation
    if (!formData.get('first_name') || !formData.get('last_name') || !formData.get('email') || !formData.get('password')) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Please fill in all required fields',
            icon: 'warning'
        });
        return;
    }

    // Add role
    formData.append('role', 'Therapist');
    formData.append('action', 'add_therapist_user');

    // Handle photo
    const photoFile = $('#therapistPhoto')[0].files[0];
    if (photoFile) {
        formData.append('profile_photo', photoFile);
    }

    // Show loading
    const button = $(event.target);
    const originalText = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Adding...');

    $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            button.prop('disabled', false).html(originalText);
            
            if (response.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Therapist added successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#globalModal').modal('hide');
                    // Refresh the unified users table
                    if (typeof loadUnifiedUsers === 'function') {
                        loadUnifiedUsers();
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to add therapist',
                    icon: 'error'
                });
            }
        },
        error: function(xhr) {
            button.prop('disabled', false).html(originalText);
            
            let errorMessage = 'Failed to add therapist';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMessage = response.message;
                }
            } catch (e) {
                // Use default error message
            }

            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error'
            });
        }
    });
}
</script>

<style>
.form-label {
    font-weight: 600;
}

.text-danger {
    font-weight: bold;
}

#therapistPhotoPreview {
    cursor: pointer;
    transition: opacity 0.3s;
}

#therapistPhotoPreview:hover {
    opacity: 0.8;
}

.position-relative .btn {
    width: 32px;
    height: 32px;
    padding: 0;
}
</style>
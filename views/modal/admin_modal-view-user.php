<div class="modal-header">
    <h5 class="modal-title">User Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body" id="userDetailsContent">
    <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Loading user details...</div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="editCurrentUser()">Edit User</button>
</div>

<script>
let currentUserId = null;
let currentUserRole = null;

$(document).ready(function() {
    const data = window.modalData;
    if (data && data.user_id) {
        currentUserId = data.user_id;
        currentUserRole = data.role;
        loadUserDetails(data.user_id);
    }
});

function loadUserDetails(userId) {
    $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'get_user_profile',
            id: userId
        },
        success: function(result) {
            displayUserDetails(result);
        },
        error: function() {
            $('#userDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load user details. Please try again.
                </div>
            `);
        }
    });
}

function displayUserDetails(user) {
    const userData = typeof user === 'string' ? JSON.parse(user) : user;
    
    const avatar = userData.profile_picture 
        ? `<img src="data:image/jpeg;base64,${userData.profile_picture}" class="rounded-circle mb-3" width="80" height="80">`
        : `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mb-3 mx-auto" style="width:80px;height:80px;"><i class="fas fa-user text-white fa-2x"></i></div>`;

    const roleColor = {
        'Admin': 'danger',
        'Therapist': 'success',
        'User': 'primary'
    }[userData.role] || 'secondary';

    let html = `
        <div class="text-center mb-4">
            ${avatar}
            <h4 class="mb-1">${userData.full_name || 'N/A'}</h4>
            <span class="badge bg-${roleColor} mb-3">${userData.role || 'User'}</span>
        </div>
        
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label text-muted">Email Address</label>
                <div class="fw-semibold">${userData.email || 'N/A'}</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label text-muted">Contact Number</label>
                <div class="fw-semibold">${userData.contact_number || 'N/A'}</div>
            </div>
            <div class="col-12">
                <label class="form-label text-muted">Address</label>
                <div class="fw-semibold">${userData.address || userData.user_address || 'N/A'}</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label text-muted">Member Since</label>
                <div class="fw-semibold">${userData.created_at ? new Date(userData.created_at).toLocaleDateString() : 'N/A'}</div>
            </div>
            <div class="col-sm-6">
                <label class="form-label text-muted">Email Verified</label>
                <div class="fw-semibold">
                    ${userData.is_email_verified ? 
                        '<span class="badge bg-success">Verified</span>' : 
                        '<span class="badge bg-warning">Not Verified</span>'}
                </div>
            </div>
        </div>
    `;

    // Add therapist-specific information if applicable
    if (userData.role === 'Therapist') {
        html += `
            <hr class="my-4">
            <h6 class="text-primary mb-3">
                <i class="fas fa-spa me-2"></i>Therapist Information
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-muted">Services</label>
                    <div class="fw-semibold">Massage Therapy, Spa Treatments</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label text-muted">Status</label>
                    <div class="fw-semibold">
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label text-muted">Experience</label>
                    <div class="fw-semibold">5+ years</div>
                </div>
            </div>
        `;
    }

    $('#userDetailsContent').html(html);
}

function editCurrentUser() {
    $('#globalModal').modal('hide');
    
    // Open appropriate edit modal based on role
    if (currentUserRole === 'Therapist') {
        showGlobalModal('../modal/admin_modal-edit-therapist.php', {
            user_id: currentUserId,
            action: 'edit'
        });
    } else {
        showGlobalModal('../modal/admin_modal-edit-user.php', {
            id: currentUserId,
            action: 'edit'
        });
    }
}
</script>

<style>
.modal-body .row > div {
    margin-bottom: 1rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.fw-semibold {
    font-weight: 600;
}
</style>
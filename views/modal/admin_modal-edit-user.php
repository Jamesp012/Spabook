<div class="modal-header">
  <h5 class="modal-title">Edit User</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <form id="editUserForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">

    <div class="mb-3">
      <label for="editUserName" class="form-label">Name</label>
      <input type="text" class="form-control" id="editUserName" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>" readonly>
    </div>

    <div class="mb-3">
      <label for="editUserEmail" class="form-label">Email</label>
      <input type="email" class="form-control" id="editUserEmail" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" readonly>
    </div>

    <div class="mb-3">
      <label for="editUserRole" class="form-label">Role</label>
      <select class="form-select" id="editUserRole" name="role">
        <option value="User" <?= ($_GET['role'] ?? '') === 'User' ? 'selected' : '' ?>>User</option>
        <option value="Admin" <?= ($_GET['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
        <option value="Therapist" <?= ($_GET['role'] ?? '') === 'Therapist' ? 'selected' : '' ?>>Therapist</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary float-end">Save Changes</button>
  </form>
</div>

<script>
$('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_user_role');
    
    $.ajax({
        url: '../controller/user_contr.php',
        type: 'POST',
        data: Object.fromEntries(formData),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: 'User role updated successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#globalModal').modal('hide');
                    location.reload(); // Refresh the user table
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to update user role',
                    icon: 'error'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update user role',
                icon: 'error'
            });
        }
    });
});
</script>
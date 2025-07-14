<div class="modal-header">
  <h5 class="modal-title">Edit User</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
  <form id="editUserForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
    
    <div class="mb-3">
      <label for="editUserName" class="form-label">Name</label>
      <input type="text" class="form-control" id="editUserName" name="name" required>
    </div>

    <div class="mb-3">
      <label for="editUserEmail" class="form-label">Email</label>
      <input type="email" class="form-control" id="editUserEmail" name="email" required>
    </div>

    <div class="mb-3">
      <label for="editUserRole" class="form-label">Role</label>
      <select class="form-select" id="editUserRole" name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>

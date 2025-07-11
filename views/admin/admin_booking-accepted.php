<div class="container-sm bg-white rounded-3 p-3 shadow-sm">
  <!-- Desktop Version -->
  <div class="row g-3 align-items-center d-none d-md-flex">
    <!-- Image -->
    <div class="col-auto d-flex align-items-center justify-content-center" style="height:100%; min-height:80px;">
      <i class="bi bi-person-circle user-avatar" style="font-size:4.5rem; min-height:72px; min-width:72px; display:flex; align-items:center; justify-content:center;"></i>
    </div>
    <!-- Info Text -->
    <div class="col">
      <div class="fw-semibold user-name">Juan Tamad</div>
      <div class="d-flex flex-wrap gap-2 small mt-1">
        <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-briefcase me-1"></i>Special Treatment for Stroke Patient</span>
        <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-calendar-event me-1"></i>May 30, 2025</span>
      </div>
    </div>
    <!-- Action Buttons -->
    <div class="col-auto d-flex flex-row gap-2">
      <button class="btn btn-primary btn-view px-4" data-id="123">View</button>
      <button class="btn btn-secondary px-4">Decline</button>
    </div>
  </div>
  <!-- Mobile/Tablet Compact Version -->
  <div class="d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 d-flex d-md-none">
    <!-- Image -->
    <div class="flex-shrink-0 d-flex align-items-center justify-content-center" style="min-width:56px;">
      <i class="bi bi-person-circle user-avatar-compact"></i>
    </div>
    <!-- Info Text -->
    <div class="flex-grow-1 text-center text-sm-start">
      <div class="fw-semibold user-name-compact">Juan Tamad</div>
      <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-2 small mt-1">
        <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-briefcase me-1"></i>Special Treatment for Stroke Patient</span>
        <span class="badge bg-light text-dark border border-1 px-2 py-1"><i class="bi bi-calendar-event me-1"></i>May 30, 2025</span>
      </div>
    </div>
    <!-- Action Buttons -->
    <div class="d-flex flex-row flex-sm-column gap-1 ms-sm-2 mt-2 mt-sm-0">
      <button class="btn btn-primary btn-view btn-sm px-3" data-id="123">View</button>
      <button class="btn btn-secondary btn-sm px-3">Decline</button>
    </div>
  </div>
</div>
<style>
  .user-avatar-compact {
    font-size: 2.2rem;
    border-radius: 50%;
    background: #f3f3f3;
    padding: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    color: #888;
  }
  .user-name-compact {
    font-size: 1.08rem;
    letter-spacing: 0.2px;
    margin-bottom: 2px;
  }
  .user-avatar {
    font-size: 4.5rem;
    min-height: 72px;
    min-width: 72px;
    border-radius: 50%;
    background: #f3f3f3;
    padding: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    color: #888;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .user-name {
    font-size: 1.18rem;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
  }
  @media (max-width: 767.98px) {
    .user-avatar {
      font-size: 48px !important;
      margin-bottom: 8px;
    }
    .user-name {
      font-size: 1.1rem !important;
    }
    .container-sm {
      padding: 1rem 0.5rem !important;
    }
    .row.g-3 {
      gap: 0.5rem !important;
    }
    .btn {
      font-size: 0.95rem;
      padding-left: 1.5rem !important;
      padding-right: 1.5rem !important;
    }
  }
  @media (min-width: 768px) {
    .col-auto.d-flex.flex-row.gap-2 {
      flex-direction: row !important;
      align-items: center !important;
      justify-content: flex-end !important;
    }
  }
  @media (max-width: 575.98px) {
    .user-avatar-compact {
      font-size: 1.6rem;
      padding: 4px;
    }
    .user-name-compact {
      font-size: 1rem;
    }
    .user-avatar {
      font-size: 1.6rem !important;
      padding: 4px;
    }
    .user-name {
      font-size: 1rem !important;
    }
    .container-sm {
      padding: 0.5rem 0.2rem !important;
    }
    .btn {
      font-size: 0.9rem;
      padding-left: 1rem !important;
      padding-right: 1rem !important;
    }
    .badge {
      font-size: 0.93rem;
      padding: 0.35em 0.7em;
      display: block;
      margin: 0.1rem auto;
      width: fit-content;
    }
    .row.g-3 {
      gap: 0.3rem !important;
    }
    .col-auto.d-flex.flex-column.gap-2 {
      flex-direction: row !important;
      gap: 0.5rem !important;
      margin-top: 0.5rem !important;
      justify-content: center !important;
    }
    .col {
      text-align: center !important;
    }
  }
</style>
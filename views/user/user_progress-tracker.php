<div class="container-fluid bg-secondary" style="max-height: calc(100vh - 130px); overflow-y: auto;">
    <div class="row p-2 align-items-center">
        <div class="col-12 col-md-6">
            <h1 class="mb-0 fw-bold">Your Recovery Journey</h1>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <p class="mb-0 fw-semibold">Treatment Plan: <span class="fw-normal">Back Pain Series</span></p>
        </div>
    </div>
    <!-- Stylish Progress Bar with Steps -->
    <div class="row justify-content-center my-4">
        <div class="col-12">
            <div class="stylish-progressbar mb-4 position-relative">
                <!-- Progress line only, no nodes -->
                <div class="stylish-progressbar-line"></div>
            </div>
            <!-- Progress Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="progress-card h-100">
                        <div class="progress-card-title">Pain Level</div>
                        <div class="progress-card-value">3/10</div>
                        <div class="progress-card-desc">Reduced from 8/10</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="progress-card h-100">
                        <div class="progress-card-title">Mobility</div>
                        <div class="progress-card-value">75%</div>
                        <div class="progress-card-desc">Improved from 40%</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="progress-card h-100">
                        <div class="progress-card-title">Overall Progress</div>
                        <div class="progress-card-value">70%</div>
                        <div class="progress-card-desc">On track for full recovery</div>
                    </div>
                </div>
            </div>
            <!-- Therapist Notes -->
            <div class="fw-bold mb-2">Therapist Notes</div>
            <div class="therapist-note mb-2">
                <div class="note-date">May 15, 2023</div>
                <div class="note-text">Initial assessment shows tension in upper back and shoulders. Recommended 4-session deep tissue treatment plan with home exercises.</div>
            </div>
            <div class="therapist-note mb-2">
                <div class="note-date">May 22, 2023</div>
                <div class="note-text">First treatment completed. Client reported 30% reduction in pain. Continuing with recommended stretches between sessions.</div>
            </div>
            <div class="therapist-note mb-2">
                <div class="note-date">June 5, 2023</div>
                <div class="note-text">Mid-point assessment shows significant improvement. Range of motion increased by approximately 40%. Continuing with treatment plan.</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Stylish Progress Bar */
    .stylish-progressbar {
        position: relative;
        margin-bottom: 2rem;
        padding: 0 10px;
        min-height: 40px;
    }

    .stylish-progressbar-line {
        position: absolute;
        top: 20px;
        left: 6%;
        right: 6%;
        height: 8px;
        background: linear-gradient(90deg, #b48a6a 60%, #e0e0e0 100%);
        z-index: 1;
        border-radius: 4px;
    }

    /* Progress Cards */
    .progress-card {
        background: #b48a6a;
        color: #fff;
        border-radius: 12px;
        padding: 1.2rem 1rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 120px;
    }

    .progress-card-title {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .progress-card-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }

    .progress-card-desc {
        font-size: 0.95rem;
        opacity: 0.85;
    }

    /* Therapist Notes */
    .therapist-note {
        background: #f5f5f5;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
    }

    .note-date {
        font-size: 0.95rem;
        color: #888;
        font-weight: 500;
        margin-bottom: 0.2rem;
    }

    .note-text {
        font-size: 1rem;
        color: #333;
    }
</style>
<!-- Add Bootstrap Icons CDN if not already included -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
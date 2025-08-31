<div class="container-fluid bg-secondary" style="max-height: calc(100vh - 130px); overflow-y: auto;">
    <!-- Loading State -->
    <div id="progress-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading your progress...</p>
    </div>

    <!-- Progress Content -->
    <div id="progress-content" style="display: none;">
        <div class="row p-2 align-items-center">
            <div class="col-12 col-md-6">
                <h1 class="mb-0 fw-bold">Your Treatment Journey</h1>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <p class="mb-0 fw-semibold" id="treatment-summary">
                    <span class="fw-normal" id="completed-sessions">0 sessions completed</span>
                </p>
            </div>
        </div>

        <!-- Your Journey Section (Only shown for stroke treatments) -->
        <div id="journey-section" style="display: none;">
            <div class="row justify-content-center my-4">
                <div class="col-12">
                    <h4 class="text-center mb-4 fw-bold">Your Recovery Progress</h4>
                    <div class="stylish-progressbar mb-4 position-relative">
                        <!-- Progress line only, no nodes -->
                        <div class="stylish-progressbar-line"></div>
                    </div>
                    <!-- Progress Cards -->
                    <div class="row g-3 mb-4" id="progress-cards">
                        <div class="col-12 col-md-4">
                            <div class="progress-card h-100">
                                <div class="progress-card-title">Pain Level</div>
                                <div class="progress-card-value" id="current-pain">-/10</div>
                                <div class="progress-card-desc" id="pain-change">Latest assessment</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="progress-card h-100">
                                <div class="progress-card-title">Mobility</div>
                                <div class="progress-card-value" id="current-mobility">-%</div>
                                <div class="progress-card-desc" id="mobility-change">Range of motion</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="progress-card h-100">
                                <div class="progress-card-title">Overall Progress</div>
                                <div class="progress-card-value" id="current-progress">-%</div>
                                <div class="progress-card-desc" id="progress-change">Recovery status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Therapist Notes Section (Always shown) -->
        <div class="therapist-notes-section">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-journal-text me-2"></i>
                    Therapist Notes
                </h4>
                <span class="badge bg-info" id="notes-count">0 notes</span>
            </div>
            
            <!-- Notes Container -->
            <div id="therapist-notes-container">
                <!-- Dynamic notes will be loaded here -->
            </div>
            
            <!-- Empty State -->
            <div id="no-notes-state" class="text-center py-5" style="display: none;">
                <i class="bi bi-journal-plus display-1 text-muted"></i>
                <h5 class="text-muted mt-3">No Notes Yet</h5>
                <p class="text-muted">Your therapist will add notes after each session.</p>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="progress-error" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
        <h5 class="text-warning mt-3">Unable to Load Progress</h5>
        <p class="text-muted mb-3">There was an error loading your treatment progress.</p>
        <button type="button" class="btn btn-primary" onclick="loadUserProgress()">
            <i class="bi bi-arrow-clockwise me-2"></i>
            Try Again
        </button>
    </div>
</div>

<script>
$(document).ready(function() {
    loadUserProgress();
});

function loadUserProgress() {
    const userId = sessionStorage.getItem('user_id');
    console.log('🔄 Loading user progress for user ID:', userId);
    
    if (!userId || userId === '0' || userId === 'null') {
        console.error('❌ No valid user ID found in session storage');
        $('#progress-loading').hide();
        $('#progress-error').show();
        return;
    }
    
    // Show loading state
    $('#progress-loading').show();
    $('#progress-content').hide();
    $('#progress-error').hide();
    
    $.ajax({
        url: '../controller/booking_contr.php',
        type: 'POST',
        data: { 
            action: 'get_user_progress',
            user_id: userId
        },
        dataType: 'json',
        success: function(response) {
            console.log('✅ User progress loaded:', response);
            
            $('#progress-loading').hide();
            
            if (response.status === 'success' && response.data) {
                displayUserProgress(response.data);
                $('#progress-content').show();
            } else if (response.status === 'no_data') {
                displayEmptyState();
                $('#progress-content').show();
            } else {
                console.error('❌ Error loading progress:', response.message);
                $('#progress-error').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error loading progress:', error);
            $('#progress-loading').hide();
            $('#progress-error').show();
        }
    });
}

function displayUserProgress(data) {
    const { sessions, hasStrokeTreatment, latestProgress, therapistNotes } = data;
    
    // Update summary
    $('#completed-sessions').text(`${sessions} session${sessions !== 1 ? 's' : ''} completed`);
    
    // Show/hide journey section based on stroke treatment
    if (hasStrokeTreatment && latestProgress) {
        $('#journey-section').show();
        displayProgressCards(latestProgress);
    } else {
        $('#journey-section').hide();
    }
    
    // Always display therapist notes
    displayTherapistNotes(therapistNotes);
}

function displayProgressCards(progress) {
    // Update progress cards with latest data
    $('#current-pain').text(progress.pain_level ? `${progress.pain_level}/10` : '-/10');
    $('#current-mobility').text(progress.mobility_level ? `${progress.mobility_level}%` : '-%');
    $('#current-progress').text(progress.overall_progress ? `${progress.overall_progress}%` : '-%');
    
    // Add descriptions based on progress
    if (progress.pain_level) {
        const painDesc = progress.pain_level <= 3 ? 'Low pain level' : 
                        progress.pain_level <= 6 ? 'Moderate pain' : 'High pain level';
        $('#pain-change').text(painDesc);
    }
    
    if (progress.mobility_level) {
        const mobilityDesc = progress.mobility_level >= 80 ? 'Excellent mobility' : 
                            progress.mobility_level >= 60 ? 'Good mobility' : 
                            progress.mobility_level >= 40 ? 'Improving mobility' : 'Limited mobility';
        $('#mobility-change').text(mobilityDesc);
    }
    
    if (progress.overall_progress) {
        const progressDesc = progress.overall_progress >= 80 ? 'Excellent progress' : 
                            progress.overall_progress >= 60 ? 'Good recovery' : 
                            progress.overall_progress >= 40 ? 'Steady improvement' : 'Early recovery';
        $('#progress-change').text(progressDesc);
    }
}

function displayTherapistNotes(notes) {
    const container = $('#therapist-notes-container');
    
    if (!notes || notes.length === 0) {
        $('#no-notes-state').show();
        $('#notes-count').text('0 notes');
        return;
    }
    
    $('#no-notes-state').hide();
    $('#notes-count').text(`${notes.length} note${notes.length !== 1 ? 's' : ''}`);
    
    let html = '';
    notes.forEach(note => {
        const date = new Date(note.completed_at || note.updated_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long', 
            day: 'numeric'
        });
        
        const isStroke = note.service_name && 
                        (note.service_name.toLowerCase().includes('stroke') || 
                         note.service_name.toLowerCase().includes('special treatment for stroke'));
        
        html += `
            <div class="therapist-note mb-3 ${isStroke ? 'stroke-note' : ''}">
                <div class="note-header d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="note-service fw-bold">${note.service_name}</div>
                        <div class="note-date small text-muted">${date}</div>
                    </div>
                    ${isStroke ? '<span class="badge bg-info">Stroke Treatment</span>' : ''}
                </div>
                <div class="note-text">${note.therapist_notes}</div>
                
                ${isStroke && (note.pain_level || note.mobility_level || note.overall_progress) ? `
                    <div class="progress-summary mt-2 p-2 bg-light rounded">
                        <small class="text-muted fw-bold">Session Progress:</small>
                        <div class="d-flex gap-3 mt-1">
                            ${note.pain_level ? `<span class="badge bg-warning">Pain: ${note.pain_level}/10</span>` : ''}
                            ${note.mobility_level ? `<span class="badge bg-info">Mobility: ${note.mobility_level}%</span>` : ''}
                            ${note.overall_progress ? `<span class="badge bg-success">Progress: ${note.overall_progress}%</span>` : ''}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    container.html(html);
}

function displayEmptyState() {
    $('#completed-sessions').text('No sessions completed yet');
    $('#journey-section').hide();
    $('#no-notes-state').show();
    $('#notes-count').text('0 notes');
}

console.log('📊 User Progress Tracker initialized');
</script>

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

    /* Therapist Notes */
    .therapist-note {
        background: #ffffff;
        border-left: 4px solid #b48a6a;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .therapist-note.stroke-note {
        border-left-color: #0d6efd;
        background: #f8f9ff;
    }
    
    .note-service {
        color: #333;
        font-size: 1.1rem;
    }
    
    .note-date {
        color: #666;
        font-size: 0.9rem;
    }
    
    .note-text {
        color: #444;
        line-height: 1.5;
        margin: 8px 0;
    }
    
    .progress-summary {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
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
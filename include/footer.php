<!-- Global Modal Template -->
<div class="modal fade" id="globalModal" tabindex="-1" aria-labelledby="globalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- You can change to modal-md or modal-sm -->
    <div class="modal-content" id="globalModalContent">
      <!-- Content will be injected here by JS -->
    </div>
  </div>
</div>

<script type="text/javascript" src="../vendor/js/jquery.min.js"></script>
<script type="text/javascript" src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="../vendor/SweetAlert/sweetalert2.all.min.js"></script>
<script src="../vendor/js/modal.js"></script>
<script type="module">
  import {
    createClient
  } from 'https://esm.sh/@supabase/supabase-js';

  // Initialize Supabase client
  window.supabase = createClient('https://rijeyetpxumyxzggihre.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJpamV5ZXRweHVteXh6Z2dpaHJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkzOTExNDEsImV4cCI6MjA2NDk2NzE0MX0.91YGOX7RfmqeC7rJK3qVMA1GKydvmEaeW61VNwasjVk');
  const {
    data: {
      session
    }
  } = await supabase.auth.getSession();

  if (session) {
    // User is signed in, redirect to user home page
    window.user_id = session.user.id;
    sessionStorage.setItem('user_id', session.user.id);
    // console.log('User is already logged in:', user_id);

  } else {
    await supabase.auth.signOut();
    window.location.href = '../index.php';
  }

  window.dispatchEvent(new Event('user_id_ready'));
</script>
<script>
<<<<<<< HEAD
  window.addEventListener('user_id_ready', async function() {
    const user_id = sessionStorage.getItem('user_id');
    $('#profileSkeleton').removeClass('d-none');
    $('#profileItem').addClass('d-none');
    if (!user_id) return;


    $.ajax({
      url: '../controller/user_contr.php',
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'get_user_profile',
        id: user_id
      },
      success: function(data) {
        // Update profile image and name in the navbar


        document.getElementById('navProfileImage').src = data.profile_picture ?
          `data:image/png;base64,${data.profile_picture}` :
          '../vendor/images/default_profile.png';
        document.getElementById('navProfileName').textContent = data.full_name || 'Guest';

        $('#profileSkeleton').addClass('d-none');
        $('#profileItem').removeClass('d-none');
      }
    })

=======
    window.addEventListener('user_id_ready', function() {
        const user_id = sessionStorage.getItem('user_id');
        // Now user_id is guaranteed to be set
    });
</script>
<script type="module">
  $(document).ready(function() {
    window.addEventListener('user_id_ready', async function () {
        const user_id = sessionStorage.getItem('user_id');

        if (!user_id) return;

        try {
        // For Deployment
        // const response = await fetch('./controller/user_contr.php', { 

        // For Local Testing
        const response = await fetch('/Spabook/controller/user_contr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get_user_profile&id=${user_id}`
        });

        const user = await response.json();

        // Optional fallback image
        // const defaultProfile = '../path/to/default-profile.jpg';

        // Update image and name
        document.getElementById('navProfileImage').src =
            user.profile_picture ? `data:image/jpeg;base64,${user.profile_picture}` : defaultProfile;

        document.getElementById('navProfileName').textContent =
            user.full_name || 'Guest';

        } catch (err) {
        console.error('Failed to load profile info:', err);
        }
    });
>>>>>>> origin/James
  });
</script>
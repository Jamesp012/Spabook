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
<!-- <script type="text/javascript" src="../vendor/DataTables/datatables.min.js"></script> -->
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
        console.log('User profile data:', data);
        if (data.role === 'User') {
          // Update profile image and name in the navbar
          $('#navProfileImage').attr('src', data.profile_picture);

          $('#navProfileName').text(data.full_name);

          $('#profileSkeleton').addClass('d-none');
          $('#profileItem').removeClass('d-none');
        } else {
          $('#navProfileImage').src = '../vendor/images/default_profile.png';
          $('#navProfileName').text('Guest');
        }

      }
    })

  });
</script>
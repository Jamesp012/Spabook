<script type="text/javascript" src="../vendor/js/jquery.min.js"></script>
<script type="text/javascript" src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="../vendor/SweetAlert/sweetalert2.all.min.js"></script>
<script type="module">
    import {
        createClient
    } from 'https://esm.sh/@supabase/supabase-js';

    // Initialize Supabase client
    window.supabase = createClient('https://rijeyetpxumyxzggihre.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJpamV5ZXRweHVteXh6Z2dpaHJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkzOTExNDEsImV4cCI6MjA2NDk2NzE0MX0.91YGOX7RfmqeC7rJK3qVMA1GKydvmEaeW61VNwasjVk');
    let user_id;
    const {
        data: {
            session
        }
    } = await supabase.auth.getSession();

    if (session) {
        // User is signed in, redirect to user home page
        user_id = session.user.id;
        console.log('User is already logged in:', session.user);

    } else {
        await supabase.auth.signOut();
        window.location.href = '../index.php';
    }
</script>
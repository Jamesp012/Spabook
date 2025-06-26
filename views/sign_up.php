<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="../vendor/images/SpaBook.ico" sizes="16x16" type="image/png" />
    <link rel="stylesheet" href="../vendor/Bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../vendor/Fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="../vendor/SweetAlert/sweetalert2.min.css" />
    <title>SpaBook - Sign Up</title>
</head>

<body>
    <div class="container-fluid p-0 m-0 vh-100">
        <div class="row g-0 h-100">
            <!-- Left: Image with overlay (reuse from login) -->
            <div class="col-12 col-lg-6 d-none d-lg-flex align-items-stretch position-relative" style="min-height: 100vh; overflow: hidden;">
                <img src="../vendor/images/background.png" class="w-100 h-100 position-absolute top-0 start-0"
                    style="object-fit: cover; min-height: 100vh; z-index: 1;" alt="Sample image" />
                <div style="
                    position: relative;
                    z-index: 2;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.4);
                    display: flex;
                    align-items: flex-start;
                    justify-content: flex-start;">
                    <div class="d-flex flex-column align-items-start justify-content-center h-100"
                        style="padding: 48px 48px 48px 64px;">
                        <img src="../vendor/images/spabookwithtitle.png" alt="SpaBook Logo"
                            class="position-absolute top-0 start-0 m-4" style="max-width: 250px; z-index: 3;" />
                        <div class="col-9">
                            <h3 class="text-white mb-4" style="font-size: 2.5rem; line-height: 1.1;">
                                All-in-one spa scheduling software to manage and grow your spa business
                            </h3>
                        </div>
                        <p class="text-white fs-5" style="max-width: 420px;">
                            Streamline your booking process for error-free, time-saving operations. Improve spa service details, offer
                            real-time booking, and simplify appointment management.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Right: Sign Up form -->
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center" style="background: #fff; min-height: 100vh;">
                <div class="w-100" style="max-width: 420px; margin: 32px;">
                    <form id="signup-form">
                        <p class="lead fw-bold mb-3">Sign up</p>
                        <div class="form-outline mb-3">
                            <label class="form-label text-secondary" for="signup_email">Email address</label>
                            <input type="email" id="signup_email" class="form-control shadow-sm" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-outline mb-3">
                            <label class="form-label text-secondary" for="signup_password">Password</label>
                            <div class="input-group">
                                <input type="password" id="signup_password" class="form-control shadow-sm" required />
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                    <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Confirm Password Field with Show/Hide Button Inside Input -->
                        <div class="form-outline mb-4">
                            <label class="form-label text-secondary" for="signup_password_confirm">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" id="signup_password_confirm" class="form-control shadow-sm" required />
                                <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm" tabindex="-1">
                                    <i class="fa-solid fa-eye-slash" id="togglePasswordConfirmIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" class="btn text-white btn-lg rounded-pill border-secondary w-100 shadow"
                                    id="signup-btn" style="background-color: #A1623F; height: 48px;">
                                    Create account
                                </button>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="termsCheck" required>
                            <label class="form-check-label small" for="termsCheck">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                            </label>
                            <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                        </div>
                        <p class="small text-dark mt-3 mb-0 text-start">
                            Already have an account?
                            <a href="../index.php" class="link-dark fw-bold">Sign in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <p>
                        By creating an account with SpaBook, you agree to the following terms and conditions:
                    </p>
                    <ul>
                        <li>Your information will be used to manage your bookings and provide spa services.</li>
                        <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                        <li>All bookings are subject to availability and spa policies.</li>
                        <li>We reserve the right to update these terms at any time. Continued use of SpaBook constitutes acceptance of any changes.</li>
                        <li>For more information, please contact our support team.</li>
                    </ul>
                    <p>
                        By checking the box, you acknowledge that you have read, understood, and agree to these terms.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/js/jquery.min.js"></script>
    <script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/SweetAlert/sweetalert2.min.js"></script>

    <?php include_once '../helper/input_validation.php'; ?>
    <script type="module">
        import {
            createClient
        } from 'https://esm.sh/@supabase/supabase-js';
        const supabase = createClient('https://rijeyetpxumyxzggihre.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJpamV5ZXRweHVteXh6Z2dpaHJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkzOTExNDEsImV4cCI6MjA2NDk2NzE0MX0.91YGOX7RfmqeC7rJK3qVMA1GKydvmEaeW61VNwasjVk');


        function isStrongPassword(password) {
            // At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password);
        }

        // Real-time email validation
        $('#signup_email').on('input', function() {
            const email = $(this).val();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').text('Please enter a valid email address.');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $(this).siblings('.invalid-feedback').text('');
            }
        });

        // Real-time password strength validation
        $('#signup_password').on('input', function() {
            const password = $(this).val();
            if (!isStrongPassword(password)) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').text(
                    'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'
                );
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $(this).siblings('.invalid-feedback').text('');
            }
            // Also trigger confirm password check
            // $('#signup_password_confirm').trigger('input');
        });

        // Real-time password match validation
        $('#signup_password_confirm').on('input', function() {
            const password = $('#signup_password').val();
            const passwordConfirm = $(this).val();
            if (password !== passwordConfirm) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').text('Passwords do not match.');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $(this).siblings('.invalid-feedback').text('');
            }
        });

        $('#termsCheck').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).removeClass('is-invalid');
                $(this).closest('.form-check').find('.invalid-feedback').hide();
            }
        });

        $('#togglePassword').on('click', function() {
            const pwField = $('#signup_password');
            const icon = $('#togglePasswordIcon');
            if (pwField.attr('type') === 'password') {
                pwField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                pwField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
        $('#togglePasswordConfirm').on('click', function() {
            const pwField = $('#signup_password_confirm');
            const icon = $('#togglePasswordConfirmIcon');
            if (pwField.attr('type') === 'password') {
                pwField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                pwField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        $('#signup-btn').on('click', async function() {
            const email = $('#signup_email').val();
            const password = $('#signup_password').val();
            const passwordConfirm = $('#signup_password_confirm').val();
            const termsChecked = $('#termsCheck').is(':checked');
            let valid = true;

            // Email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                $('#signup_email').addClass('is-invalid').removeClass('is-valid');
                $('#signup_email').siblings('.invalid-feedback').text('Please enter a valid email address.');
                valid = false;
            }

            // Password strength validation
            if (!isStrongPassword(password)) {
                $('#signup_password').addClass('is-invalid').removeClass('is-valid');
                $('#signup_password').siblings('.invalid-feedback').text(
                    'Please enter a password'
                );
                valid = false;
            }

            // Password match validation
            if (password !== passwordConfirm) {
                $('#signup_password_confirm').addClass('is-invalid').removeClass('is-valid');
                $('#signup_password_confirm').siblings('.invalid-feedback').text('Passwords do not match.');
                valid = false;
            }

            // Terms checkbox validation
            if (!termsChecked) {
                $('#termsCheck').addClass('is-invalid');
                $('#termsCheck').siblings('.invalid-feedback').show();
                valid = false;
            } else {
                $('#termsCheck').removeClass('is-invalid');
                $('#termsCheck').siblings('.invalid-feedback').hide();
            }

            if (valid) {
                // 1. Try logging in first to check if email already exists
                // const {
                //     error: loginError
                // } = await supabase.auth.signInWithPassword({
                //     email,
                //     password
                // });

                // if (loginError && loginError.message === 'Invalid login credentials') {
                //     Swal.fire({
                //         title: 'Already Registered?',
                //         text: 'This email may be registered with Google or already exists. Would you like to sign in with Google?',
                //         icon: 'info',
                //         showCancelButton: true,
                //         confirmButtonText: 'Go to Login',
                //     }).then(result => {
                //         if (result.isConfirmed) {
                //             // Redirect to login page
                //             window.location.href = '../index.php';
                //         }
                //     });
                //     return;
                // }
                // 2. If login fails (invalid credentials), attempt signup
                // if (loginError.message.toLowerCase() != 'invalid login credentials') {
                    const {
                        data,
                        error: signupError
                    } = await supabase.auth.signUp({
                        email,
                        password,
                    });

                    if (signupError) {
                        const msg = signupError.message.toLowerCase();
                        if (
                            msg.includes('already registered') ||
                            msg.includes('duplicate') ||
                            msg.includes('already exists')
                        ) {
                            Swal.fire({
                                title: 'Email Already Registered',
                                text: 'This email may be registered with Google. Would you like to sign in with Google?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Sign in with Google'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    supabase.auth.signInWithOAuth({
                                        provider: 'google'
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Error', signupError.message, 'error');
                        }
                    } else if (!data.user) {
                        Swal.fire('Error', 'Could not create account. Please try again.', 'error');
                    } else {
                        Swal.fire('Success', 'Check your email for a confirmation link!', 'success')
                            .then(() => {
                                window.location.href = '../index.php';
                            });
                    }

                // } else {
                //     // Other login error (network, etc.)
                //     Swal.fire('Error', loginError.message, 'error');
                // }
            }

        });
    </script>
</body>

</html>
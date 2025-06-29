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
                        <div class="row mb-3">
                            <div class="col-5">
                                <div class="form-outline">
                                    <label class="form-label text-secondary" for="signup_ln">Last Name</label>
                                    <input type="text" id="signup_ln" maxlength="15" pattern="[A-Za-z\s]+" style="text-transform: uppercase;" class="form-control shadow-sm" required oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'');" />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-outline">
                                    <label class="form-label text-secondary" for="signup_fn">First Name</label>
                                    <input type="text" id="signup_fn" maxlength="30" pattern="[A-Za-z\s]+" style="text-transform: uppercase;" class="form-control shadow-sm" required oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'');" />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-outline">
                                    <label class="form-label text-secondary" for="signup_mi">M.I.</label>
                                    <input type="text" id="signup_mi" maxlength="1" pattern="[A-Za-z\s]+" style="text-transform: uppercase;" class="form-control shadow-sm" required oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'');" />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-outline mb-3">
                            <label class="form-label text-secondary" for="signup_contact">Contact Number</label>
                            <div class="input-group">
                                <select class="form-select shadow-sm" id="signup_region" style="max-width: 110px;">
                                    <option value="+63" selected>PH +63</option>
                                    <option value="+1">US +1</option>
                                    <option value="+44">UK +44</option>
                                    <option value="+61">AU +61</option>
                                    <option value="+65">SG +65</option>
                                    <!-- Add more regions as needed -->
                                </select>
                                <input type="tel" id="signup_contact" class="form-control shadow-sm" maxlength="10" pattern="[0-9]{7,15}" placeholder="9123456789" required oninput="this.value=this.value.replace(/[^0-9]/g,'');" />
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-outline mb-3">
                            <label class="form-label text-secondary" for="signup_address">Current address</label>
                            <input type="text" id="signup_address" class="form-control shadow-sm" required />
                            <div class="invalid-feedback"></div>
                        </div>
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
        const regionMaxLength = {
            "+63": 10, // PH: 10 digits (e.g., 9123456789)
            "+1": 10, // US/CA: 10 digits (e.g., 4151234567)
            "+44": 10, // UK: 10 digits (varies, but 10 is common for local)
            "+61": 9, // AU: 9 digits (e.g., 412345678)
            "+65": 8 // SG: 8 digits (e.g., 91234567)
            // Add more as needed
        };

        const regionPlaceholders = {
            "+63": "9123456789", // PH
            "+1": "4151234567", // US/CA
            "+44": "7123456789", // UK
            "+61": "412345678", // AU
            "+65": "91234567" // SG
            // Add more as needed
        };


        $('#signup_region').on('change', function() {
            const region = $(this).val();
            const max = regionMaxLength[region] || 15;
            const placeholder = regionPlaceholders[region] || "Enter number";
            $('#signup_contact').attr('maxlength', max);
            $('#signup_contact').attr('placeholder', placeholder);
            $('#signup_contact').val(''); // Optionally clear input on region change
        });

        // Set initial maxlength and placeholder on page load
        $(document).ready(function() {
            const region = $('#signup_region').val();
            const max = regionMaxLength[region] || 15;
            const placeholder = regionPlaceholders[region] || "Enter number";
            $('#signup_contact').attr('maxlength', max);
            $('#signup_contact').attr('placeholder', placeholder);
        });

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
            const user_fullname = $('#signup_fn').val().trim() + ' ' + $('#signup_mi').val().trim() + ' ' + $('#signup_ln').val().trim();
            const user_address = $('#signup_address').val().trim();
            const contact = $('#signup_region').val() + $('#signup_contact').val();
            const email = $('#signup_email').val();
            const password = $('#signup_password').val();
            const passwordConfirm = $('#signup_password_confirm').val();
            const termsChecked = $('#termsCheck').is(':checked');
            let valid = true;


            if (inputValidation('signup_fn', 'signup_ln', 'signup_mi', 'signup_contact', 'signup_address')) {
                valid = true;
            }
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
                $.ajax({
                    url: '../controller/user_contr.php',
                    type: 'POST',
                    data: {
                        action: 'check_email_exists',
                        email: email,
                    },
                    success: async result => {
                        if (result == 'exists') {
                            Swal.fire({
                                title: 'Email Already Exists',
                                text: 'This email is already registered.',
                                icon: 'warning',
                                showCancelButton: true,
                            });
                        } else {
                            // Proceed with sign up
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
                                // Get the Supabase user UUID
                                const supabase_uuid = data.user.id;

                                // Save user to your own database with the uuid
                                $.ajax({
                                    url: '../controller/user_contr.php',
                                    type: 'POST',
                                    data: {
                                        action: 'sign_up_user',
                                        email: email,
                                        user_fullname: user_fullname,
                                        user_address: user_address,
                                        contact: contact,
                                        password: password,
                                        supabase_uuid: supabase_uuid // Pass the uuid here
                                    },
                                    success: function(response) {
                                        console.log('Database test response:', response);
                                        if (response === 'success') {
                                            Swal.fire('Success', 'Check your email for a confirmation link!', 'success')
                                                .then(() => {
                                                    window.location.href = '../index.php';
                                                });
                                        } else {
                                            Swal.fire('Error', response, 'error');
                                        }
                                    },
                                });
                            }
                        }
                    },
                });

                // $.ajax({
                //     url: '../cotroller/user_contr.php',
                //     type: 'POST',
                //     data: {
                //         action: 'sign_up_user',
                //         email: email,
                //         user_fullname: user_fullname,
                //         user_address: user_address,
                //         contact: contact,
                //     },
                //     success: function(response) {
                //         console.log('Database test response:', response);
                //     },
                // });
                // const {
                //     data,
                //     error: signupError
                // } = await supabase.auth.signUp({
                //     email,
                //     password,
                // });

                // if (signupError) {
                //     const msg = signupError.message.toLowerCase();
                //     if (
                //         msg.includes('already registered') ||
                //         msg.includes('duplicate') ||
                //         msg.includes('already exists')
                //     ) {
                //         Swal.fire({
                //             title: 'Email Already Registered',
                //             text: 'This email may be registered with Google. Would you like to sign in with Google?',
                //             icon: 'warning',
                //             showCancelButton: true,
                //             confirmButtonText: 'Sign in with Google'
                //         }).then(result => {
                //             if (result.isConfirmed) {
                //                 supabase.auth.signInWithOAuth({
                //                     provider: 'google'
                //                 });
                //             }
                //         });
                //     } else {
                //         Swal.fire('Error', signupError.message, 'error');
                //     }
                // } else if (!data.user) {
                //     Swal.fire('Error', 'Could not create account. Please try again.', 'error');
                // } else {
                //     Swal.fire('Success', 'Check your email for a confirmation link!', 'success')
                //         .then(() => {
                //             window.location.href = '../index.php';
                //         });
                // }

                // } else {
                //     // Other login error (network, etc.)
                //     Swal.fire('Error', loginError.message, 'error');
                // }
            }

        });
    </script>
</body>

</html>
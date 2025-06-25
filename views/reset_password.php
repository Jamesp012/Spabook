<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password - SpaBook</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="icon" href="../vendor/images/SpaBook.ico" sizes="16x16" type="image/png">
    <link rel="stylesheet" href="../vendor/Bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../vendor/Fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="../vendor/SweetAlert/sweetalert2.min.css" />
</head>

<body>
    <div class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 0;">
        <img src="../vendor/images/background.png"
            class="w-100 h-100 position-absolute top-0 start-0"
            style="object-fit: cover; min-height: 100vh; z-index: 1;" alt="Sample image" />
        <div style="
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.3); z-index: 2;">
        </div>
    </div>
    <div class="container d-flex flex-column align-items-center justify-content-center vh-100" style="position: relative; z-index: 3;">
        <!-- Logo above the card -->
        <div class="text-center mb-3">
            <img src="../vendor/images/spabookwithtitle.png" alt="SpaBook Logo" style="max-width: 180px; height: auto;">
        </div>
        <div class="w-100" style="max-width: 400px;">
            <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
                <h3 class="mb-3 text-center">Reset Your Password</h3>
                <form id="reset-form">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" id="new_password" class="form-control shadow-sm" required />
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i>
                            </button>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" id="confirm_password" class="form-control shadow-sm" required />
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword" tabindex="-1">
                                <i class="fa-solid fa-eye-slash" id="toggleConfirmPasswordIcon"></i>
                            </button>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <?php include_once '../helper/input_validation.php'; ?>
    <script src="../vendor/js/jquery.min.js"></script>
    <script src="../vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/SweetAlert/sweetalert2.min.js"></script>
    <script type="module">
        import {
            createClient
        } from 'https://esm.sh/@supabase/supabase-js';
        const supabase = createClient('https://rijeyetpxumyxzggihre.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJpamV5ZXRweHVteXh6Z2dpaHJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkzOTExNDEsImV4cCI6MjA2NDk2NzE0MX0.91YGOX7RfmqeC7rJK3qVMA1GKydvmEaeW61VNwasjVk');

        function isStrongPassword(password) {
            // At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password);
        }

        $('#new_password').on('input', function() {
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
        });

        $('#confirm_password').on('input', function() {
            const password = $('#new_password').val();
            const passwordConfirm = $(this).val();
            if (password !== passwordConfirm) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').text('Passwords do not match.');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                $(this).siblings('.invalid-feedback').text('');
            }
        });

        $('#togglePassword').on('click', function() {
            const pwField = $('#new_password');
            const icon = $('#togglePasswordIcon');
            if (pwField.attr('type') === 'password') {
                pwField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                pwField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
        $('#toggleConfirmPasswordIcon').on('click', function() {
            const pwField = $('#confirm_password');
            const icon = $('#togglePasswordConfirmIcon');
            if (pwField.attr('type') === 'password') {
                pwField.attr('type', 'text');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                pwField.attr('type', 'password');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        // On form submit
        $('#reset-form').on('submit', async function(e) {
            e.preventDefault();
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();

            let valid = true;
            if (!isStrongPassword(newPassword)) {
                $('#new_password').addClass('is-invalid').removeClass('is-valid');
                $('#new_password').siblings('.invalid-feedback').text(
                    'Please enter a password'
                );
                valid = false;
            }

            if (newPassword !== confirmPassword) {
                $('#confirm_password').addClass('is-invalid').removeClass('is-valid');
                $('#confirm_password').siblings('.invalid-feedback').text('Passwords do not match.');
                valid = false;
            }

            if (valid) {
                // Update password using Supabase

                const {
                    data,
                    error
                } = await supabase.auth.updateUser({
                    password: newPassword
                });

                if (error) {
                    Swal.fire('Error', error.message, 'error');
                } else {
                    Swal.fire('Success', 'Your password has been reset. Please log in.', 'success').then(() => {
                        window.location.href = '../index.php';
                    });
                }
            }
        });
    </script>
</body>

</html>
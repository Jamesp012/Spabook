<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="vendor/images/SpaBook.ico" sizes="16x16" type="image/png">

    <link rel="stylesheet" type="text/css" href="vendor/Bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="vendor/Fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="vendor/SweetAlert/sweetalert2.min.css" />
    <link rel="stylesheet" type="text/css" href="vendor/css/custom.index.css" />
    <link rel="stylesheet" type="text/css" href="vendor/css/util.css" />

    <script type="text/javascript" src="vendor/js/jquery.min.js"></script>
    <script type="text/javascript" src="vendor/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="vendor/SweetAlert/sweetalert2.min.js"></script>
    <script type="text/stylesheet" src="styles/style.css"></script>

    <title>SpaBook</title>
</head>

<body>
    <div class="container-fluid p-0 m-0 vh-100">
        <div class="row g-0 h-100">
            <!-- Left: Image with overlay -->
            <div class="col-12 col-lg-6 d-flex align-items-stretch position-relative" style="min-height: 100vh; overflow: hidden;">
                <img src="./vendor/images/background.png"
                    class="w-100 h-100 position-absolute top-0 start-0"
                    style="object-fit: cover; min-height: 100vh; z-index: 1;" alt="Sample image">
                <div style="
                    position: relative;
                    z-index: 2;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.4);
                    display: flex;
                    align-items: flex-start;
                    justify-content: flex-start;">
                    <div class="d-flex flex-column align-items-start justify-content-center h-100" style="padding: 48px 48px 48px 64px;">
                        <img src="./vendor/images/spabookwithtitle.png" alt="SpaBook Logo" class="position-absolute top-0 start-0 m-4"
                            style="max-width: 250px; z-index: 3;">
                        <div class="col-9">
                            <h3 class="text-white mb-4" style="font-size:2.5rem; line-height:1.1;">
                                All-in-one spa scheduling software to manage and grow your spa business
                            </h3>
                        </div>
                        <p class="text-white fs-5" style="max-width: 420px;">
                            Streamline your booking process for error-free, time-saving operations. Improve spa service details, offer real-time booking, and simplify appointment management.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Right: Login form -->
            <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center" style="background: #fff; min-height: 100vh;">
                <div class="w-100" style="max-width: 420px; margin: 32px;">

                    <form>
                        <p class="lead fw-bold mb-3">Sign in</p>
                        <button type="button" class="btn btn-white w-100 border border-secondary rounded-pill d-flex align-items-center justify-content-center mb-3 shadow-sm" style="height: 48px;">
                            <i class="fa-brands fa-google me-2"></i>
                            Continue with Google
                        </button>
                        <div class="divider d-flex align-items-center my-3">
                            <hr class="flex-grow-1">
                            <span class="mx-3 fw-bold text-secondary">OR</span>
                            <hr class="flex-grow-1">
                        </div>
                        <div class="form-outline mb-3">
                            <label class="form-label text-secondary" for="user_email_address">User name or email address</label>
                            <input type="email" id="user_email_address" class="form-control shadow-sm" />
                        </div>
                        <div class="form-outline mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-secondary mb-0" for="user_password">Your password</label>
                                <button class="btn text-secondary p-0" type="button" id="togglePassword" tabindex="-1" style="font-size: 1rem;">
                                    <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i> Show
                                </button>
                            </div>
                            <div class="input-group">
                                <input type="password" id="user_password" class="form-control shadow-sm" />
                            </div>
                        </div>
                        <div class="d-flex justify-content-end align-items-center mb-4">
                            <a href="#!" class="text-body small">Forgot your password?</a>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 ps-0">
                                <button type="button" class="btn text-white btn-lg rounded-pill border-secondary w-100 shadow" onclick="LogIn();"
                                    style="background-color: #A1623F; height: 48px;">
                                    Sign in
                                </button>
                            </div>
                        </div>
                        <p class="small text-dark mt-3 mb-0 text-start">Don't have an account? <a href="#!" class="link-dark fw-bold">Sign up</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include_once './helper/input_validation.php' ?>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#user_password');
                const toggleIcon = $('#togglePasswordIcon');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $('#togglePassword').contents().last()[0].textContent = ' Hide';
                } else {
                    passwordField.attr('type', 'password');
                    toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $('#togglePassword').contents().last()[0].textContent = ' Show';
                }
            });
        });

        function LogIn() {
            if (inputValidation('user_email_address')) {
                // Simulate a successful login
                if ($('#user_email_address').val() == 'Admin') {
                    location.href = "./views/admin_home_page";
                } else {
                    location.href = "./views/user_home_page";

                }
            }

        }
    </script>


</body>

</html>
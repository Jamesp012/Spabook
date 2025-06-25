<!-- callback.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../vendor/images/SpaBook.ico" sizes="16x16" type="image/png">

    <link rel="stylesheet" type="text/css" href="../vendor/Bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../vendor/Fontawesome/css/all.min.css" />
    <!-- <link rel="stylesheet" type="text/css" href="../vendor/css/custom.landing_page.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="../vendor/css/custom.app_page.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="../vendor/css/util.css" /> -->
    <link rel="stylesheet" type="text/css" href="../vendor/SweetAlert/sweetalert2.min.css" />
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css" />
    <title>Log In to Spa Book</title>

</head>

<body>
    <div class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 1;">
        <div class="d-flex">
            <!-- Sidebar Skeleton -->
            <nav class="d-flex flex-column justify-content-between bg-light" style="height: 100vh; width: 250px;">
                <div>
                    <div class="px-4 pt-3 pb-4 d-flex justify-content-between align-items-center">
                        <div class="bg-secondary rounded" style="width:120px; height:32px;"></div>
                        <div class="bg-secondary rounded-circle" style="width:32px; height:32px;"></div>
                    </div>
                    <ul class="list-unstyled px-2">
                        <li class="mb-3">
                            <div class="bg-secondary rounded" style="width:80%; height:24px;"></div>
                        </li>
                        <li class="mb-3">
                            <div class="bg-secondary rounded" style="width:70%; height:24px;"></div>
                        </li>
                        <li class="mb-3">
                            <div class="bg-secondary rounded" style="width:60%; height:24px;"></div>
                        </li>
                        <li class="mb-3">
                            <div class="bg-secondary rounded" style="width:75%; height:24px;"></div>
                        </li>
                        <li class="mb-3">
                            <div class="bg-secondary rounded" style="width:65%; height:24px;"></div>
                        </li>
                    </ul>
                </div>
                <div class="px-4 pt-3 pb-4">
                    <div class="bg-secondary rounded" style="width:100%; height:32px;"></div>
                </div>
            </nav>
            <!-- Content Skeleton -->
            <div class="flex-grow-1" style="margin-left:250px; width:100%;">
                <nav class="px-3 py-3 border-bottom bg-light d-flex align-items-center">
                    <div class="bg-secondary rounded" style="width:32px; height:32px; margin-right:16px;"></div>
                    <div class="bg-secondary rounded" style="width:180px; height:24px;"></div>
                </nav>
                <div class="p-4">
                    <div class="bg-secondary rounded mb-3" style="width:100%; height:32px;"></div>
                    <div class="bg-secondary rounded mb-3" style="width:100%; height:200px;"></div>
                    <div class="bg-secondary rounded" style="width:100%; height:100px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Centered Card with Loading Spinner -->
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="z-index: 10; background: rgba(255,255,255,0.0);">
        <div class="card border-0 shadow-none bg-transparent p-0" style="background: transparent; box-shadow: none;">
            <div class="position-relative d-flex justify-content-center align-items-center" style="min-width: 220px; min-height: 220px;">
                <div class="custom-spinner-wrapper d-flex justify-content-center align-items-center" style="min-width:220px; min-height:220px;">
                    <img src="../vendor/images/SpaBook.png" alt="Loading..." class="custom-spinner-glow" style="width: 120px; height: 120px;">
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-secondary {
            background-color: #e0e0e0 !important;
            position: relative;
            overflow: hidden;
        }

        .bg-secondary::after {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: -150px;
            height: 100%;
            width: 150px;
            background: linear-gradient(90deg, transparent, #f5f5f5 50%, transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            100% {
                left: 100%;
            }
        }

        .custom-spinner-glow {
            animation: spin 1.2s linear infinite, glow 1.2s ease-in-out infinite alternate;
            filter: drop-shadow(0 0 16px #a1623f);
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes glow {
            0% {
                filter: drop-shadow(0 0 8px #a1623f);
            }

            100% {
                filter: drop-shadow(0 0 32px #a1623f);
            }
        }
    </style>
</body>
<script type="module">
    import {
        createClient
    } from 'https://esm.sh/@supabase/supabase-js';

    const supabase = createClient('https://rijeyetpxumyxzggihre.supabase.co', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJpamV5ZXRweHVteXh6Z2dpaHJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkzOTExNDEsImV4cCI6MjA2NDk2NzE0MX0.91YGOX7RfmqeC7rJK3qVMA1GKydvmEaeW61VNwasjVk');

    const {
        data: {
            session
        },
        error
    } = await supabase.auth.getSession();

    if (session) {
        const jwt = session.access_token;

        // Send to your backend via PHP
        fetch('../config/auth.php', {
                method: 'POST',
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer ${jwt}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'google_login'
                })
            })
            .then(res => res.json())
            .then(data => {
                // console.log(data.user);
                if (data.user) {
                    if (data.user.email === 'spa.book19@gmail.com') {
                        window.location.href = '../views/admin_home_page';
                    } else {
                        window.location.href = '../views/user_home_page';
                    }
                } else {
                    document.body.innerHTML = 'User verification failed.';
                }
            });
    } else {
        window.location.href = '../index.php';

    }
</script>
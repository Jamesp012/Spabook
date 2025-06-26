<?php

require_once '../config/credentials.php';

$provider = "google";
$authUrl = "$projectUrl/auth/v1/authorize?provider=$provider&redirect_to=" . urlencode($redirectUrl);


header("Location: $authUrl");
exit;

<?php
require_once __DIR__ . '/../../config/auth.php';

logout_user();
redirect_to('/src/views/public/principal.php');

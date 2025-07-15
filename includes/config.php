<?php
require_once 'includes/config.php';

define('UPLOAD_DIR', 'uploads/lost_items/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png']);

$ITEM_CATEGORIES = [
    'electronics' => 'Electronics',
    'documents' => 'Documents',
    'accessories' => 'Accessories',
    'others' => 'Others'
];

$ITEM_STATUS = [
    'unclaimed' => 'Unclaimed',
    'claimed' => 'Claimed'
];
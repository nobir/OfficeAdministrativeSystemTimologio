<?php

$admin_menus = [
    [
        'title' => 'Verify Users',
        'path' => route('admin.viewUnverifiedUsers'),
    ],
    [
        'title' => 'View Users',
        'path' => route('admin.viewUsers'),
    ],
    [
        'title' => 'Add User',
        'path' => route('admin.createUser'),
    ],
    [
        'title' => 'Add Permission',
        'path' => route('admin.createPermission'),
    ],
    [
        'title' => 'View Permissions',
        'path' => route('admin.viewPermissions'),
    ],
    [
        'title' => 'Send Email Verification Link',
        'path' => route('admin.sendEmailVerifyLink'),
    ],
];

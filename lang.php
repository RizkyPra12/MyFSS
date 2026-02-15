<?php
/**
 * Language Support
 */

function getCurrentLanguage() {
    return $_COOKIE['language'] ?? 'en';
}

function t($key) {
    $translations = [
        'username' => 'Username',
        'password' => 'Password',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'welcome' => 'Welcome',
        'phone' => 'Phone Number',
        'confirm_password' => 'Confirm Password',
        'country_name' => 'Country Name',
        'email' => 'Email',
        'flag_url' => 'Flag URL',
        'government' => 'Government Form',
        'ideology' => 'Ideology',
        'age_range' => 'Age Range',
        'dont_have_account' => "Don't have an account?",
        'already_have_account' => 'Already have an account?',
        'username_too_short' => 'Username must be at least 3 characters',
        'passwords_dont_match' => 'Passwords do not match',
        'password_too_short' => 'Password must be at least 6 characters',
        'username_taken' => 'Username already taken',
        'registration_successful' => 'Registration successful! Please login.',
        'update_failed' => 'Update failed',
        'invalid_credentials' => 'Invalid username or password'
    ];
    
    return $translations[$key] ?? $key;
}

// Government forms
$govForms = [
    'Democracy',
    'Republic',
    'Monarchy',
    'Dictatorship',
    'Oligarchy',
    'Theocracy',
    'Anarchy'
];

// Ideologies
$ideologies = [
    'Liberalism',
    'Conservatism',
    'Socialism',
    'Libertarianism',
    'Centrism',
    'Environmentalism',
    'Nationalism',
    'Anarchism'
];

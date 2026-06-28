<?php

return [
    // Pages
    'login_title' => 'Login',
    'login_subtitle' => 'Sign in to your account',
    'register_title' => 'Register',
    'register_subtitle' => 'Create your free account',
    'profile_title' => 'Profile',
    'profile_heading' => 'My Profile',
    'profile_description' => 'Manage your personal information',

    // Login form
    'email_label' => 'Email',
    'password_label' => 'Password',
    'remember_me' => 'Remember me',
    'forgot_password_link' => 'Forgot your password?',
    'login_button' => 'Sign in',
    'no_account' => "Don't have an account?",
    'create_account_link' => 'Create account',

    // Register form
    'name_label' => 'Name',
    'password_confirm_label' => 'Confirm password',
    'register_button' => 'Create account',
    'have_account' => 'Already have an account?',
    'login_link' => 'Sign in',

    // Profile form
    'edit_profile' => 'Edit Profile',
    'update_profile_desc' => 'Update your personal information and profile photo',
    'profile_photo' => 'Profile photo',
    'profile_photo_hint' => 'JPG, PNG. Max 2MB.',
    'change_password' => 'Change password (optional)',
    'current_password' => 'Current password',
    'new_password' => 'New password',
    'new_password_confirm' => 'Confirm new password',
    'save_button' => 'Save changes',
    'saving_button' => 'Saving...',

    // User dropdown
    'profile_link' => 'Profile',
    'logout_link' => 'Log out',

    // Messages
    'login_failed' => 'The provided credentials are incorrect.',
    'wrong_password' => 'The current password is incorrect',
    'profile_updated' => 'Profile updated successfully',
    'free_plan_missing' => 'Free plan does not exist. Run database seeders.',

    // Email verification
    'disposable_email' => 'Temporary or disposable email addresses are not allowed.',
    'indisposable' => 'Disposable email addresses are not allowed.',
    'verification_sent' => 'We have sent a verification link to your email address.',
    'verification_success' => 'Email verified successfully.',
    'verification_failed' => 'The verification link is invalid or has expired.',
    'verification_already_verified' => 'Your email has already been verified.',

    // Password reset
    'password_reset_title' => 'Reset Password',
    'password_reset_subtitle' => 'We will send you a link to reset your password',
    'password_reset_button' => 'Send reset link',
    'password_reset_sent' => 'If the email exists, you will receive a password reset link.',
    'password_reset_subject' => 'Reset Password',
    'password_reset_greeting' => 'Hello :name,',
    'password_reset_intro' => 'You are receiving this email because you requested a password reset.',
    'password_reset_expiry' => 'This link expires in :count minutes.',
    'password_reset_no_action' => 'If you did not request this change, you can ignore this message.',
    'password_reset_new_password' => 'New password',
    'password_reset_new_password_confirm' => 'Confirm new password',
    'password_reset_submit' => 'Reset Password',
    'password_reset_success' => 'Your password has been reset successfully.',

    // MFA (two-factor authentication)
    'mfa_required' => 'Additional verification required. Check your email.',
    'mfa_email_subject' => 'Your verification code',
    'mfa_email_greeting' => 'Hello :name,',
    'mfa_email_intro' => 'Your verification code is:',
    'mfa_email_expiry' => 'This code expires in 10 minutes.',
    'mfa_email_salutation' => 'Thank you for using CoVa.',
    'mfa_challenge_title' => 'Two-step verification',
    'mfa_challenge_subtitle' => 'We have sent a verification code to your email address.',
    'mfa_setup_desc' => 'Add an extra layer of security to your account. You will receive a code by email when logging in.',
    'mfa_setup_title' => 'Protect your account',
    'mfa_setup_description' => 'Enable two-step verification to add an extra layer of security. Each time you log in, you will receive a unique code in your email.',
    'mfa_setup_enable_button' => 'Enable now',
    'mfa_setup_later_button' => 'Maybe later',
    'mfa_setup_activated' => 'Two-step verification enabled. Check your email to verify the code.',
    'mfa_code_label' => 'Verification code',
    'mfa_code_placeholder' => 'Enter the 6-digit code',
    'mfa_verify_button' => 'Verify',
    'mfa_resend_button' => 'Resend code',
    'mfa_invalid_code' => 'The code entered is invalid or has expired.',
    'mfa_expired_code' => 'The code has expired. Request a new one.',
    'mfa_code_sent' => 'A new code has been sent to your email.',
    'throttle' => 'Too many attempts. Please try again in :seconds seconds.',
];

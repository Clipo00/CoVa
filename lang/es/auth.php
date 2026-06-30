<?php

return [
    // Pages
    'login_title' => 'Login',
    'login_subtitle' => 'Inicia sesión en tu cuenta',
    'register_title' => 'Registro',
    'register_subtitle' => 'Crea tu cuenta gratuita',
    'profile_title' => 'Perfil',
    'profile_heading' => 'Mi Perfil',
    'profile_description' => 'Gestiona tu información personal',

    // Login form
    'email_label' => 'Correo electrónico',
    'password_label' => 'Contraseña',
    'show_password' => 'Mostrar contraseña',
    'hide_password' => 'Ocultar contraseña',
    'remember_me' => 'Recordarme',
    'forgot_password_link' => '¿Olvidaste tu contraseña?',
    'login_button' => 'Iniciar sesión',
    'login_processing' => 'Iniciando sesión...',
    'no_account' => '¿No tienes cuenta?',
    'create_account_link' => 'Crear cuenta',

    // Register form
    'name_label' => 'Nombre',
    'password_confirm_label' => 'Confirmar contraseña',
    'register_button' => 'Crear cuenta',
    'register_processing' => 'Creando cuenta...',
    'have_account' => '¿Ya tienes cuenta?',
    'login_link' => 'Iniciar sesión',

    // Profile form
    'edit_profile' => 'Editar Perfil',
    'update_profile_desc' => 'Actualiza tu información personal y foto de perfil',
    'profile_photo' => 'Foto de perfil',
    'profile_photo_hint' => 'JPG, PNG. Máximo 2MB.',
    'change_password' => 'Cambiar contraseña (opcional)',
    'current_password' => 'Contraseña actual',
    'new_password' => 'Nueva contraseña',
    'new_password_confirm' => 'Confirmar nueva contraseña',
    'save_button' => 'Guardar cambios',
    'saving_button' => 'Guardando...',

    // User dropdown
    'profile_link' => 'Perfil',
    'logout_link' => 'Cerrar sesión',

    // Messages
    'login_failed' => 'Las credenciales proporcionadas no son correctas.',
    'wrong_password' => 'La contraseña actual es incorrecta',
    'profile_updated' => 'Perfil actualizado correctamente',
    'free_plan_missing' => 'El plan gratuito no existe. Ejecuta los seeders de la base de datos.',

    // Email verification
    'disposable_email' => 'No se permiten direcciones de correo temporales o desechables.',
    'indisposable' => 'No se permiten direcciones de correo electrónico desechables.',
    'verification_sent' => 'Hemos enviado un enlace de verificación a tu correo electrónico.',
    'verification_success' => 'Correo electrónico verificado correctamente.',
    'verification_failed' => 'El enlace de verificación no es válido o ha caducado.',
    'verification_already_verified' => 'Tu correo electrónico ya ha sido verificado.',

    // Password reset
    'password_reset_title' => 'Restablecer contraseña',
    'password_reset_subtitle' => 'Te enviaremos un enlace para restablecer tu contraseña',
    'password_reset_button' => 'Enviar enlace de restablecimiento',
    'password_reset_sent' => 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.',
    'password_reset_subject' => 'Restablecimiento de contraseña',
    'password_reset_greeting' => 'Hola :name,',
    'password_reset_intro' => 'Recibes este correo porque solicitaste restablecer tu contraseña.',
    'password_reset_expiry' => 'Este enlace caduca en :count minutos.',
    'password_reset_no_action' => 'Si no solicitaste este cambio, puedes ignorar este mensaje.',
    'password_reset_new_password' => 'Nueva contraseña',
    'password_reset_new_password_confirm' => 'Confirmar nueva contraseña',
    'password_reset_submit' => 'Restablecer contraseña',
    'password_reset_success' => 'Tu contraseña ha sido restablecida correctamente.',

    // MFA (two-factor authentication)
    'mfa_required' => 'Se requiere verificación adicional. Revisa tu correo electrónico.',
    'mfa_email_subject' => 'Tu código de verificación',
    'mfa_email_greeting' => 'Hola :name,',
    'mfa_email_intro' => 'Tu código de verificación es:',
    'mfa_email_expiry' => 'Este código caduca en 10 minutos.',
    'mfa_email_salutation' => 'Gracias por usar CoVa.',
    'mfa_challenge_title' => 'Verificación en dos pasos',
    'mfa_challenge_subtitle' => 'Hemos enviado un código de verificación a tu correo electrónico.',
    'mfa_setup_desc' => 'Añade una capa extra de seguridad a tu cuenta de CoVa. Recibirás un código por correo al iniciar sesión.',
    'mfa_setup_title' => 'Protege tu cuenta de CoVa',
    'mfa_setup_description' => 'Te recomendamos activar la verificación en dos pasos para mantener tu cuenta de CoVa segura. Solo te llevará un momento: recibirás un código único en tu correo cada vez que inicies sesión desde un dispositivo nuevo.',
    'mfa_setup_enable_button' => 'Activar verificación',
    'mfa_setup_later_button' => 'Ahora no',
    'mfa_setup_activated' => 'Verificación activada. Revisa tu correo para confirmar el código.',
    'mfa_code_label' => 'Código de verificación',
    'mfa_code_placeholder' => 'Ingresa el código de 6 dígitos',
    'mfa_verify_button' => 'Verificar',
    'mfa_resend_button' => 'Reenviar código',
    'mfa_invalid_code' => 'El código introducido no es válido o ha caducado.',
    'mfa_expired_code' => 'El código ha caducado. Solicita uno nuevo.',
    'mfa_code_sent' => 'Se ha enviado un nuevo código a tu correo electrónico.',
    'throttle' => 'Demasiados intentos. Por favor, inténtalo de nuevo en :seconds segundos.',
];

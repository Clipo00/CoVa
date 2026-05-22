<?php

return [
    // Index
    'page_title' => 'Organizaciones',
    'heading' => 'Mis Organizaciones',
    'new_button' => 'Nueva Organización',

    // Create
    'create_title' => 'Crear Organización',
    'create_heading' => 'Crear tu primera organización',
    'create_description' => 'Una organización agrupa tus blueprints y permite colaborar con tu equipo.',

    // Create/Edit form
    'name_label' => 'Nombre de la organización',
    'slug_label' => 'Slug (URL)',
    'slug_hint' => 'Se genera automáticamente desde el nombre, pero podés editarlo.',
    'create_button' => 'Crear Organización',
    'edit_button' => 'Guardar Cambios',
    'cancel_link' => '← Cancelar y volver',

    // Edit
    'edit_title' => 'Editar :name',
    'edit_heading' => 'Editar Organización',
    'edit_breadcrumb' => 'Editar',
    'name_required' => 'Nombre *',
    'name_placeholder' => 'Mi Empresa',
    'slug_required' => 'Slug *',
    'slug_edit_hint' => 'Identificador único para URLs.',

    // List
    'list_empty' => 'No tenés organizaciones todavía.',
    'list_empty_link' => 'Crea tu primera organización',
    'slug_prefix' => 'slug: ',

    // Show
    'back_to_dashboard' => 'Volver al Dashboard',
    'edit_link' => 'Editar',
    'delete_confirm' => "¿Estás seguro de que querés eliminar esta organización?\n\nEsta acción es reversible desde el dashboard.\n\nPara eliminar permanentemente, andá al dashboard después de eliminar.",
    'delete_button' => 'Eliminar',
    'blueprints_count' => 'Blueprints',
    'view_blueprints' => 'Ver blueprints →',
    'members_count' => 'Miembros',
    'manage_members' => 'Gestionar miembros →',
    'plan_label' => 'Plan',
    'max_blueprints_text' => ':max blueprints máx.',
    'limit_warning' => 'Has alcanzado el límite de <strong>:max blueprints</strong> de tu plan <strong>:plan</strong>.',
    'limit_hint' => 'Eliminá un blueprint existente para poder crear uno nuevo.',
    'recent_blueprints' => 'Blueprints recientes',
    'new_blueprint_button' => '+ Nuevo Blueprint',
    'limit_reached' => 'Límite alcanzado',
    'no_blueprints' => 'No hay blueprints todavía.',
    'create_first_blueprint' => 'Crea el primer blueprint',

    // Members
    'members_title' => 'Miembros - :name',
    'members_heading' => 'Miembros',
    'members_breadcrumb' => 'Miembros',
    'member_count' => ':count miembros',
    'direct_member_heading' => 'Crear miembro directo',
    'member_name_label' => 'Nombre',
    'member_name_placeholder' => 'Juan Pérez',
    'member_email_label' => 'Email',
    'member_email_placeholder' => 'juan@ejemplo.com',
    'member_role_label' => 'Rol',
    'role_developer' => 'Developer',
    'role_maintainer' => 'Maintainer',
    'role_owner' => 'Owner',
    'create_member_button' => 'Crear',
    'invite_heading' => 'Invitar por email',
    'invite_email_placeholder' => 'email@ejemplo.com',
    'invite_role_label' => 'Rol',
    'invite_button' => 'Invitar',
    'pending_invitations' => 'Invitaciones pendientes',
    'role_prefix' => 'Rol:',
    'status_pending' => 'Pendiente',
    'invitation_info' => 'Rol: :role · Expira :time',

    // Messages
    'updated' => 'Organización actualizada correctamente.',
    'deleted' => 'Organización eliminada. Podés recuperarla desde el dashboard.',
    'restored' => 'Organización restaurada correctamente.',
    'force_deleted' => 'Organización eliminada permanentemente.',
    'user_added' => 'Usuario :name agregado correctamente.',
    'role_updated' => 'Rol de :name actualizado correctamente.',
    'invite_sent' => 'Invitación enviada correctamente.',

    // Errors
    'not_found' => 'Organización no encontrada.',
    'no_access' => 'No tenés acceso a esta organización.',
    'no_permission' => 'No tenés permisos para realizar esta acción.',
    'no_edit_permission' => 'No tenés permisos para editar esta organización.',
    'no_view_permission' => 'No tenés permisos para ver esta organización.',
    'no_manage_permission' => 'No tenés permisos para gestionar miembros.',
    'no_invite_permission' => 'No tenés permisos para invitar miembros.',
    'no_delete_permission' => 'No tenés permisos para eliminar esta organización.',
    'no_restore_permission' => 'No tenés permisos para restaurar esta organización.',
    'no_force_delete_permission' => 'No tenés permisos para eliminar permanentemente esta organización.',
    'cannot_change_owner_role' => 'No podés cambiar el rol del propietario de la organización.',
    'invalid_role' => 'El rol debe ser developer o maintainer.',
    'not_a_member' => 'El usuario no es miembro de esta organización.',
    'restore_limit' => 'Límite de :max organizaciones alcanzado. Eliminá una organización activa para poder recuperar esta.',
    'max_reached' => 'Límite de :max organizaciones alcanzado en plan :plan.',

    // Invitation
    'invitation_not_found' => 'Invitación no encontrada.',
    'invitation_expired' => 'La invitación ha expirado o ya fue utilizada.',
    'invitation_user_required' => 'Se requiere un usuario para aceptar esta invitación.',
    'invitation_no_user' => 'No existe un usuario con este email.',
];

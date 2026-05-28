<?php

return [
    // Index
    'page_title' => 'Organizations',
    'heading' => 'My Organizations',
    'new_button' => 'New Organization',

    // Create
    'create_title' => 'Create Organization',
    'create_heading' => 'Create your first organization',
    'create_description' => 'An organization groups your blueprints and allows collaboration with your team.',
    'create_first_link' => 'Create your first organization',

    // Create/Edit form
    'name_label' => 'Organization name',
    'slug_label' => 'Slug (URL)',
    'slug_hint' => 'Automatically generated from the name, but you can edit it.',
    'create_button' => 'Create Organization',
    'edit_button' => 'Save Changes',
    'cancel_link' => '← Cancel and go back',

    // Edit
    'edit_title' => 'Edit :name',
    'edit_heading' => 'Edit Organization',
    'edit_breadcrumb' => 'Edit',
    'name_required' => 'Name *',
    'name_placeholder' => 'My Company',
    'slug_required' => 'Slug *',
    'slug_edit_hint' => 'Unique identifier for URLs.',

    // List
    'list_empty' => "You don't have any organizations yet.",
    'list_empty_link' => 'Create your first organization',
    'slug_prefix' => 'slug: ',

    // Show
    'back_to_dashboard' => 'Back to Dashboard',
    'edit_link' => 'Edit',
    'delete_confirm' => "Are you sure you want to delete this organization?\n\nThis action is reversible from the dashboard.\n\nTo permanently delete, go to the dashboard after deleting.",
    'delete_button' => 'Delete',
    'blueprints_count' => 'Blueprints',
    'view_blueprints' => 'View blueprints →',
    'members_count' => 'Members',
    'manage_members' => 'Manage members →',
    'plan_label' => 'Plan',
    'max_blueprints_text' => ':max blueprints max.',
    'limit_warning' => "You've reached the <strong>:max blueprints</strong> limit of your <strong>:plan</strong> plan.",
    'limit_hint' => 'Delete an existing blueprint to create a new one.',
    'recent_blueprints' => 'Recent Blueprints',
    'new_blueprint_button' => '+ New Blueprint',
    'limit_reached' => 'Limit reached',
    'no_blueprints' => 'No blueprints yet.',
    'create_first_blueprint' => 'Create the first blueprint',

    // Members
    'members_title' => 'Members - :name',
    'members_heading' => 'Members',
    'members_breadcrumb' => 'Members',
    'member_count' => ':count members',
    'direct_member_heading' => 'Create direct member',
    'member_name_label' => 'Name',
    'member_name_placeholder' => 'John Doe',
    'member_email_label' => 'Email',
    'member_email_placeholder' => 'john@example.com',
    'member_role_label' => 'Role',
    'role_developer' => 'Developer',
    'role_maintainer' => 'Maintainer',
    'role_owner' => 'Owner',
    'create_member_button' => 'Create',
    'invite_heading' => 'Invite by email',
    'invite_email_placeholder' => 'email@example.com',
    'invite_role_label' => 'Role',
    'invite_button' => 'Invite',
    'pending_invitations' => 'Pending invitations',
    'role_prefix' => 'Role:',
    'status_pending' => 'Pending',
    'invitation_info' => 'Role: :role · Expires :time',

    // Messages
    'updated' => 'Organization updated successfully.',
    'deleted' => 'Organization deleted. You can restore it from the dashboard.',
    'restored' => 'Organization restored successfully.',
    'force_deleted' => 'Organization permanently deleted.',
    'user_added' => 'User :name added successfully.',
    'role_updated' => ':name\'s role updated successfully.',
    'invite_sent' => 'Invitation sent successfully.',

    // Errors
    'not_found' => 'Organization not found.',
    'no_access' => 'You do not have access to this organization.',
    'no_permission' => 'You do not have permission to perform this action.',
    'no_edit_permission' => 'You do not have permission to edit this organization.',
    'no_view_permission' => 'You do not have permission to view this organization.',
    'no_manage_permission' => 'You do not have permission to manage members.',
    'no_invite_permission' => 'You do not have permission to invite members.',
    'no_delete_permission' => 'You do not have permission to delete this organization.',
    'no_restore_permission' => 'You do not have permission to restore this organization.',
    'no_force_delete_permission' => 'You do not have permission to permanently delete this organization.',
    'cannot_change_owner_role' => 'You cannot change the role of the organization owner.',
    'invalid_role' => 'The role must be developer or maintainer.',
    'not_a_member' => 'The user is not a member of this organization.',
    'restore_limit' => 'Limit of :max organizations reached. Delete an active organization to restore this one.',
    'max_reached' => 'Limit of :max organizations reached on plan :plan.',

    // Invitation
    'invitation_not_found' => 'Invitation not found.',
    'invitation_expired' => 'The invitation has expired or has already been used.',
    'invitation_user_required' => 'A user is required to accept this invitation.',
    'invitation_no_user' => 'A user with this email does not exist.',
];

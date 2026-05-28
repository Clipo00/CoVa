<?php

return [
    // Index
    'page_title' => 'Blueprints',
    'heading' => 'Blueprints',
    'new_button' => 'New Blueprint',
    'all_orgs_limit' => 'All your organizations have reached the blueprint limit. Delete an existing one or upgrade your plan.',
    'no_orgs' => "You don't have any organizations yet.",

    // Create
    'create_title' => 'New Blueprint',
    'create_heading' => 'Create Blueprint',
    'create_description' => 'Define the variables, files, and configurations of your environment.',

    // Create/Edit form
    'general_info' => 'General Information',
    'org_label' => 'Organization *',
    'org_locked_hint' => 'Organization pre-selected from the previous page.',
    'title_label' => 'Title *',
    'title_placeholder' => 'My Laravel Project',
    'slug_label' => 'Slug *',
    'slug_hint' => 'Unique identifier for URLs. Auto-generated from the title.',
    'category_label' => 'Category',
    'category_none' => 'No category',
    'description_label' => 'Description',
    'description_placeholder' => 'Describe the purpose of this blueprint...',
    'tabs_section' => 'Tabs',
    'create_blueprint_button' => 'Create Blueprint',
    'edit_blueprint_button' => 'Save Changes',
    'cancel_link' => '← Cancel and go back',

    // Edit
    'edit_title' => 'Edit :title',
    'edit_heading' => 'Edit Blueprint',
    'edit_breadcrumb' => 'Edit',

    // List
    'search_placeholder' => 'Search blueprints...',
    'list_empty' => 'No blueprints.',
    'list_empty_link' => 'Create the first one',
    'list_empty_filtered' => 'No blueprints match the current filters. Try adjusting or clearing them.',
    'delete_tooltip' => 'Delete blueprint',
    'filter_button' => 'Filters',
    'filter_organizations' => 'Organization',
    'filter_categories' => 'Category',
    'filter_marketplace' => 'Marketplace (coming soon)',
    'filter_clear_all' => 'Clear all',
    'filter_count' => ':count active',
    'filter_preserve' => 'Save filters',
    'filter_preserve_hint' => 'Remember filters for next time',
    'filter_tag_org' => 'Org: :name',
    'filter_tag_cat' => 'Cat: :name',

    // Show
    'back_to_dashboard' => 'Back to Dashboard',
    'copy_uuid' => 'Copy UUID',
    'uuid_copied' => 'UUID copied to clipboard',
    'transfer_to' => 'Transfer to...',
    'transfer_button' => 'Transfer',
    'edit_button' => 'Edit',
    'delete_confirm' => 'Are you sure you want to delete this blueprint?',
    'delete_button' => 'Delete',

    // Variables
    'env_variables' => 'Environment Variables',
    'variable_count' => ':count variable(s)',
    'variables_empty' => 'No variables configured.',
    'variables_empty_hint' => 'Variables are defined when creating or editing the blueprint.',
    'var_key' => 'Key',
    'var_group' => 'Group',
    'var_type' => 'Type',
    'var_value' => 'Value',
    'var_interactive' => 'Interactive',
    'var_secret' => 'Secret',
    'var_actions' => 'Actions',
    'var_key_placeholder' => 'DB_HOST',
    'var_group_placeholder' => '.env',
    'var_type_fixed' => 'Fixed',
    'var_type_empty' => 'Empty',
    'var_value_placeholder' => 'Default value',
    'var_delete_tooltip' => 'Delete variable',
    'var_add_button' => 'Add Variable',
    'var_none' => 'No variables configured.',
    'var_add_first' => 'Add first variable',
    'secret_value' => '••••••••',
    'yes' => 'Yes',
    'no' => 'No',

    // Agent Context
    'agent_context' => 'Agent Context',
    'agent_md_badge' => 'agent.md',
    'copy_button' => 'Copy',
    'agent_md_copied' => 'agent.md copied to clipboard',

    // VSCode Extensions
    'vscode_extensions' => 'VSCode Extensions',
    'copy_install_command' => 'Copy install command',
    'command_copied' => 'Command copied to clipboard',

    // MCP Servers
    'mcp_servers' => 'MCP Servers',

    // Tab Manager
    'move_up' => 'Move up',
    'move_down' => 'Move down',
    'tab_delete' => 'Delete',
    'extensions_label' => 'Extensions (one per line)',
    'extensions_placeholder' => "esbenp.prettier-vscode\ndbaeumer.vscode-eslint",
    'extensions_count' => ':count extension(s) configured',
    'mcp_servers_label' => 'MCP Servers',
    'server_label' => 'Server #:index',
    'server_delete' => 'Delete',
    'server_name_label' => 'Name',
    'server_name_placeholder' => 'filesystem',
    'server_command_label' => 'Command',
    'server_command_placeholder' => 'npx',
    'server_args_label' => 'Arguments (space separated)',
    'server_args_placeholder' => '-y @modelcontextprotocol/server-filesystem',
    'server_add_button' => 'Add server',
    'code_presets' => 'Code Presets',
    'preset_psr12' => 'PSR-12',
    'preset_solid' => 'SOLID',
    'preset_clean_arch' => 'Clean Architecture',
    'skills_label' => 'Skills',
    'skill_stripe' => 'Stripe',
    'skill_tailwind' => 'Tailwind CSS',
    'custom_rules' => 'Custom Rules (Markdown)',
    'custom_rules_placeholder' => 'E.g.: Always use declare(strict_types=1). Prefer DTOs over arrays.',
    'tabs_empty' => 'No tabs configured',
    'tabs_empty_hint' => 'Add a tab to get started',
    'add_tab' => 'Add',

    // Favorites
    'favorites_title' => 'Favorites',
    'favorites_heading' => 'Favorite Blueprints',
    'favorites_count' => ':count favorites',
    'favorites_empty' => 'No favorites yet',
    'favorites_empty_desc' => 'Mark blueprints as favorites to access them quickly.',
    'explore_blueprints' => 'Explore Blueprints',
    'uuid_copied_short' => 'UUID copied',
    'uuid_label' => 'UUID',

    // Deleted
    'deleted_title' => 'Deleted Blueprints',
    'deleted_heading' => 'Deleted Blueprints',
    'deleted_count' => ':count deleted',
    'deleted_empty' => 'No deleted blueprints',
    'deleted_empty_desc' => 'Deleted blueprints will appear here.',
    'deleted_info' => ':organization · Deleted :time',
    'restore_limit' => 'Limit of :max blueprints reached. Delete an active blueprint to restore this one.',
    'restore_button' => 'Restore',
    'restore_disabled' => 'Not available',
    'restore_permission_info' => 'Only the owner can restore',

    // Tab types
    'tab_type_vscode' => 'VSCode Extensions',
    'tab_type_mcp' => 'MCP Servers',
    'tab_type_ai' => 'AI Context',
    'tab_type_unknown' => 'Unknown',

    // Messages
    'deleted_success' => 'Blueprint deleted successfully.',
    'restored_success' => 'Blueprint restored successfully.',
    'transferred_success' => 'Blueprint transferred successfully.',

    // Errors
    'org_unauthorized' => 'Organization not authorized.',
    'org_limit' => 'This organization has reached the blueprint limit. Delete an existing blueprint to create a new one.',
    'no_capacity' => 'You have no organization with available capacity to create blueprints. Delete an existing blueprint or upgrade your plan.',
    'no_delete_permission' => 'You do not have permission to delete this blueprint.',
    'no_restore_permission' => 'You do not have permission to restore this blueprint.',
    'restore_limit_msg' => ':message Delete an active blueprint to restore this one.',
    'no_edit_permission' => 'You do not have permission to edit this blueprint.',
    'no_create_permission' => 'You do not have permission to create blueprints in this organization.',
    'unique_variable_keys' => 'Variable keys must be unique.',
    'invalid_org_data' => 'Invalid organization data.',

    // Transfer errors
    'transfer_not_owner' => 'Only the owner can transfer blueprints.',
    'transfer_not_owner_target' => 'You can only transfer to organizations where you are the owner.',
    'transfer_same_org' => 'You cannot transfer a blueprint to the same organization.',
    'transfer_select_org' => 'Select a target organization',
    'transfer_slug_exists' => "A blueprint with the slug ':slug' already exists in the target organization. Rename the blueprint before transferring.",

    // Exception messages
    'max_blueprints_reached' => 'Limit of :max blueprints per organization reached on plan :plan.',
    'max_variables_reached' => 'Limit of :max variables per blueprint reached on plan :plan.',
    'unknown_tab_type' => 'Unknown tab type: \':type\'.',
];

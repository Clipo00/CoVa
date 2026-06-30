<?php

declare(strict_types=1);

return [
    // Nav
    'login' => 'Log in',
    'go_to_dashboard' => 'Go to dashboard',
    'register' => 'Register',

    // Hero
    'hero_title' => 'Stop copying your configs from one place to another',
    'site_title' => 'CoVa, your own space',
    'hero_subtitle' => 'CoVa is your configuration vault. Define, publish, and run <strong>vault fetch</strong>. Your environment is ready in seconds, no matter the stack.',
    'cta_primary' => 'Get Started Free',
    'cta_secondary' => 'See how it works',

    // Pain Point
    'pain_title' => 'Does this sound familiar?',
    'pain_env_title' => 'Scattered configs',
    'pain_env_desc' => 'Sharing configs through Slack, losing track of history, never knowing which version is the right one…',
    'pain_config_title' => 'Configuring from scratch',
    'pain_config_desc' => 'Every new project means hours setting up the same agent.md, the same Cursor rules, the same base files.',
    'pain_standards_title' => 'No standardization',
    'pain_standards_desc' => 'Every developer on your team has their own way of configuring things. Nothing is reproducible.',

    // How it Works
    'how_title' => 'From idea to environment in 3 steps',
    'step1_title' => 'Create your Blueprint',
    'step1_desc' => 'Define variables, config files, and environment rules visually in the dashboard. No terminal needed.',
    'step2_title' => 'Publish or keep it private',
    'step2_desc' => 'Keep it private for your organization or share it on the Marketplace for the community to use.',
    'step3_title' => 'Run vault fetch',
    'step3_desc' => 'A single command and your environment is ready. Variables loaded, files generated, everything in place.',
    'step3_note' => 'Includes AI context rules, post-install scripts, VS Code extensions, and MCP servers.',

    // Demo
    'demo_title' => 'See how it works',
    'demo_subtitle' => 'Create your first organization, define your blueprints, and run them with a single command.',
    'demo_dashboard' => 'Dashboard',
    'demo_org' => 'Create Organization',
    'demo_blueprint' => 'Create Blueprint',
    'demo_ai_context' => 'AI Context',
    'demo_prev' => 'Previous',
    'demo_next' => 'Next',

    // Demo slides - Dashboard
    'demo_dash_title' => 'Dashboard',
    'demo_dash_orgs' => 'Organizations',
    'demo_dash_blueprints' => 'Blueprints',
    'demo_dash_vars' => 'Variables',
    'demo_dash_org_name' => 'My Company',
    'demo_dash_org_blueprints' => ':count blueprints',
    'demo_dash_personal' => 'Personal Project',
    'demo_plan_pro' => 'Pro',
    'demo_plan_free' => 'Free',

    // Demo slides - Create Org
    'demo_org_title' => 'Create Organization',
    'demo_org_name_label' => 'Organization name',
    'demo_org_name_placeholder' => 'My Company',
    'demo_org_slug_label' => 'Slug (URL)',
    'demo_org_slug_placeholder' => 'my-company',
    'demo_org_slug_help' => 'Auto-generated from the name',
    'demo_org_plan_label' => 'Plan',
    'demo_org_plan_pro_desc' => '25 blueprints · 50 members',
    'demo_org_plan_pro_price' => '$19/month',
    'demo_org_plan_free_desc' => '3 blueprints · 5 members',
    'demo_org_plan_free_price' => 'Free',
    'demo_org_submit' => 'Create Organization',

    // Demo slides - Create Blueprint
    'demo_bp_title' => 'Create Blueprint',
    'demo_bp_title_label' => 'Title',
    'demo_bp_title_placeholder' => 'Laravel + Inertia + Tailwind',
    'demo_bp_desc_label' => 'Description',
    'demo_bp_desc_placeholder' => 'Complete stack for modern projects...',
    'demo_bp_cat_label' => 'Category',
    'demo_bp_cat_placeholder' => 'Fullstack',
    'demo_bp_submit' => 'Create Blueprint',
    'demo_bp_vars_title' => 'Environment Variables',
    'demo_bp_file_env' => '.env',
    'demo_bp_file_testing' => '.env.testing',
    'demo_bp_file_config' => 'config/app.php',
    'demo_bp_var_host' => 'DB_HOST',
    'demo_bp_var_db' => 'DB_DATABASE',
    'demo_bp_var_app_name' => 'APP_NAME',
    'demo_bp_var_app_key' => 'APP_KEY',
    'demo_bp_var_value_localhost' => 'localhost',
    'demo_bp_var_value_myapp' => 'myapp',
    'demo_bp_var_value_test' => 'myapp_test',
    'demo_bp_var_value_app' => 'MyApp',
    'demo_bp_var_value_hidden' => '••••••••',
    'demo_bp_vars_count' => '5 variables in 3 files',

    // Demo slides - AI Context
    'demo_ai_title' => 'AI Context Configuration',
    'demo_ai_desc' => 'Select coding presets and skills to generate a custom agent.md file for your AI tools.',
    'demo_ai_presets' => 'Code Presets',
    'demo_ai_skills' => 'Skills',
    'demo_ai_preset_psr12' => 'PSR-12',
    'demo_ai_preset_solid' => 'SOLID',
    'demo_ai_preset_clean' => 'Clean Architecture',
    'demo_ai_preset_laravel' => 'Laravel Conv.',
    'demo_ai_preset_ts' => 'TypeScript Strict',
    'demo_ai_skill_stripe' => 'Stripe',
    'demo_ai_skill_tailwind' => 'Tailwind',
    'demo_ai_skill_react' => 'React Expert',
    'demo_ai_skill_vue' => 'Vue Expert',
    'demo_ai_output' => '→ Generates agent.md with 2 presets + 2 skills',
    'demo_ai_count' => '5 presets · 4 skills available',

    // Marketplace
    'marketplace_title' => 'Start with a ready-to-use template',
    'marketplace_empty' => 'No public blueprints in the Marketplace yet. Be the first to publish.',
    'marketplace_cta' => 'Explore the Marketplace →',

    // CTA Final
    'cta_final_title' => 'Start saving time today',
    'cta_final_subtitle' => 'Sign up free and create your first blueprint in under 5 minutes.',
    'cta_final_button' => 'Create free account',
    'cta_final_note' => 'No credit card required. Free plan available.',

    // Terminal
    'terminal_aria_label' => 'Terminal demo running vault fetch',
    'terminal_title' => 'Terminal',
    'terminal_caption' => 'one command, everything ready',
    'terminal_cmd_fetch' => '$ vault fetch cova-marketplace/laravel-inertia',
    'terminal_downloading' => '> Downloading blueprint...',
    'terminal_variables' => '> Variables loaded: 12',
    'terminal_files' => '> Files generated: .env, agent.md, .cursorrules',
    'terminal_presets' => '> AI context: PSR-12, Clean Architecture, React',
    'terminal_scripts' => '> Post-install scripts: composer install, npm run build',
    'terminal_ready' => '✅ Environment ready in 2.4s',

    // Hero trust
    'hero_trust' => 'Your data is always encrypted. Privacy by design.',

    // Marketplace extra
    'marketplace_more' => 'and many more templates created by the community',
    'coming_soon' => 'Coming Soon',
    'coming_soon_badge' => '🔜 Coming Soon',
    'marketplace_coming_soon' => 'The marketplace will be available soon. Stay tuned!',

    // Pricing
    'nav_pricing' => 'Pricing',
    'pricing_title' => 'Simple, transparent pricing',
    'pricing_subtitle' => 'Start free and scale as your team grows. No surprises, no credit card required.',
    'pricing_note' => 'All prices in euros (EUR). Enterprise plan requires prior contact.',
    'pricing_coming_soon_note' => '* Marketplace features coming soon',

    'plan_free_desc' => 'To get started with CoVa',
    'plan_pro_desc' => 'For growing teams',
    'plan_enterprise_desc' => 'For large organizations',
    'plan_price_free' => 'free',
    'plan_price_month' => '/month',
    'plan_price_custom' => 'Contact us',

    'plan_orgs' => ':count organizations',
    'plan_orgs_unlimited' => 'Unlimited organizations',
    'plan_blueprints' => ':count blueprints/org',
    'plan_blueprints_unlimited' => 'Unlimited blueprints',
    'plan_members' => ':count members/org',
    'plan_members_unlimited' => 'Unlimited members',
    'plan_variables' => ':count variables/blueprint',
    'plan_variables_unlimited' => 'Unlimited variables',

    'plan_marketplace_browse' => 'Browse Marketplace',
    'plan_coming_soon' => 'Coming soon',
    'plan_api_access' => 'API Access',
    'plan_marketplace_publish' => 'Publish to Marketplace',
    'plan_priority_support' => 'Priority Support',
    'plan_dedicated_support' => 'Dedicated Support',
    'plan_sso' => 'SSO / SAML',

    'plan_name_free' => 'Free',
    'plan_name_pro' => 'Pro',
    'plan_name_enterprise' => 'Enterprise',

    'plan_cta_free' => 'Start for free',
    'plan_cta_pro' => 'Try Pro 14 days',
    'plan_cta_enterprise' => 'Contact sales',
    'plan_popular' => 'Most popular',

    // Footer
    'footer_tagline' => 'Configurations that travel with you.',
    'footer_product' => 'Product',
    'footer_account' => 'Account',
    'footer_links_login' => 'Log in',
    'footer_links_register' => 'Register',
    'footer_links_marketplace' => 'Marketplace',
    'footer_copyright' => '© 2026 CoVa. All rights reserved.',
];

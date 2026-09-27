<?php

/**
 * The panel's settings registry: which screens exist and which keys each owns.
 *
 * A screen only ever saves the keys it declares here, so a crafted form (web or
 * API) cannot rewrite settings that belong to another screen. Each field has a
 * type used by the generic form view and a `validation` rule list applied on
 * every save.
 *
 * Types:
 * - text, textarea, number  rendered as plain inputs
 * - boolean                 a checkbox; stored as "1" / "0"
 * - select                  a dropdown with `options`
 * - secret                  stored encrypted at rest, never rendered back;
 *                           the form shows "saved - leave blank to keep"
 *
 * Adding a screen: add a key here with `label` and `fields`; the show/update
 * routes, the API GET|PUT /settings/{screen} and the generic form pick it up.
 */

return [
    'screens' => [
        'general' => [
            'label' => 'General',
            'fields' => [
                'site_name' => [
                    'type' => 'text',
                    'label' => 'Website name',
                    'validation' => ['required', 'string', 'max:60'],
                ],
                'site_email' => [
                    'type' => 'text',
                    'label' => 'Contact email',
                    'validation' => ['required', 'string', 'email', 'max:255'],
                ],
                'site_phone' => [
                    'type' => 'text',
                    'label' => 'Contact phone',
                    'validation' => ['nullable', 'string', 'max:40'],
                ],
                'site_address' => [
                    'type' => 'textarea',
                    'label' => 'Business address',
                    'validation' => ['nullable', 'string', 'max:500'],
                ],
                'site_slogan' => [
                    'type' => 'text',
                    'label' => 'Slogan',
                    'validation' => ['nullable', 'string', 'max:120'],
                ],
                'footer_text' => [
                    'type' => 'textarea',
                    'label' => 'Footer text',
                    'validation' => ['nullable', 'string', 'max:300'],
                ],
                'maintenance_mode' => [
                    'type' => 'boolean',
                    'label' => 'Maintenance mode',
                    'hint' => 'Show a maintenance notice instead of the storefront.',
                    'validation' => ['boolean'],
                ],
            ],
        ],

        'features' => [
            'label' => 'Features Activation',
            'fields' => [
                'seller_system' => ['type' => 'boolean', 'label' => 'Seller system', 'validation' => ['boolean']],
                'wallet_system' => ['type' => 'boolean', 'label' => 'Customer wallet', 'validation' => ['boolean']],
                'coupon_system' => ['type' => 'boolean', 'label' => 'Coupons', 'validation' => ['boolean']],
                'offline_payment' => ['type' => 'boolean', 'label' => 'Offline payments', 'validation' => ['boolean']],
                'customer_products' => ['type' => 'boolean', 'label' => 'Customer classified products', 'validation' => ['boolean']],
                'affiliate_system' => ['type' => 'boolean', 'label' => 'Affiliate system', 'validation' => ['boolean']],
                'otp_system' => ['type' => 'boolean', 'label' => 'OTP / SMS', 'validation' => ['boolean']],
                'pos_system' => ['type' => 'boolean', 'label' => 'Point of sale', 'validation' => ['boolean']],
            ],
        ],

        'orders' => [
            'label' => 'Order Configuration',
            'fields' => [
                'guest_checkout' => ['type' => 'boolean', 'label' => 'Guest checkout', 'validation' => ['boolean']],
                'order_code_format' => [
                    'type' => 'select',
                    'label' => 'Order code format',
                    'options' => ['random' => 'Random letters and digits', 'sequential' => 'Sequential number'],
                    'validation' => ['nullable', 'in:random,sequential'],
                ],
                'order_email_confirmation' => ['type' => 'boolean', 'label' => 'Email the customer on order placement', 'validation' => ['boolean']],
                'order_sms_confirmation' => ['type' => 'boolean', 'label' => 'SMS the customer on order placement', 'validation' => ['boolean']],
                'min_order_amount' => [
                    'type' => 'number',
                    'label' => 'Minimum order amount',
                    'validation' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                ],
            ],
        ],

        'smtp' => [
            'label' => 'SMTP Settings',
            'fields' => [
                'mail_mailer' => [
                    'type' => 'select',
                    'label' => 'Mailer',
                    'options' => ['log' => 'Log (no real emails)', 'smtp' => 'SMTP server', 'sendmail' => 'Sendmail'],
                    'validation' => ['nullable', 'in:log,smtp,sendmail'],
                ],
                'mail_host' => ['type' => 'text', 'label' => 'Host', 'validation' => ['nullable', 'string', 'max:255']],
                'mail_port' => ['type' => 'number', 'label' => 'Port', 'validation' => ['nullable', 'integer', 'min:1', 'max:65535']],
                'mail_username' => ['type' => 'text', 'label' => 'Username', 'validation' => ['nullable', 'string', 'max:255']],
                'mail_password' => ['type' => 'secret', 'label' => 'Password', 'validation' => ['nullable', 'string', 'max:255']],
                'mail_encryption' => [
                    'type' => 'select',
                    'label' => 'Encryption',
                    'options' => ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'],
                    'validation' => ['nullable', 'in:tls,ssl,none'],
                ],
            ],
        ],

        'payment-methods' => [
            'label' => 'Payment Methods',
            'fields' => [
                'cash_on_delivery' => ['type' => 'boolean', 'label' => 'Cash on delivery', 'validation' => ['boolean']],
                'cod_instructions' => ['type' => 'textarea', 'label' => 'Cash on delivery instructions', 'validation' => ['nullable', 'string', 'max:500']],
                'bank_transfer' => ['type' => 'boolean', 'label' => 'Bank transfer', 'validation' => ['boolean']],
                'bank_instructions' => ['type' => 'textarea', 'label' => 'Bank transfer instructions', 'validation' => ['nullable', 'string', 'max:500']],
            ],
        ],

        'social-login' => [
            'label' => 'Social Media Logins',
            'fields' => [
                'google_login' => ['type' => 'boolean', 'label' => 'Sign in with Google', 'validation' => ['boolean']],
                'facebook_login' => ['type' => 'boolean', 'label' => 'Sign in with Facebook', 'validation' => ['boolean']],
            ],
        ],

        'google' => [
            'label' => 'Google',
            'fields' => [
                'google_analytics_id' => ['type' => 'text', 'label' => 'Analytics measurement ID', 'hint' => 'Like G-XXXXXXX.', 'validation' => ['nullable', 'string', 'max:40']],
                'google_recaptcha' => ['type' => 'boolean', 'label' => 'Enable reCAPTCHA', 'validation' => ['boolean']],
                'google_recaptcha_site_key' => ['type' => 'text', 'label' => 'reCAPTCHA site key', 'validation' => ['nullable', 'string', 'max:120']],
                'google_recaptcha_secret' => ['type' => 'secret', 'label' => 'reCAPTCHA secret', 'validation' => ['nullable', 'string', 'max:255']],
                'google_map_key' => ['type' => 'secret', 'label' => 'Google Map API key', 'validation' => ['nullable', 'string', 'max:255']],
                'google_firebase_key' => ['type' => 'secret', 'label' => 'Firebase server key', 'validation' => ['nullable', 'string', 'max:255']],
            ],
        ],

        'facebook' => [
            'label' => 'Facebook',
            'fields' => [
                'facebook_app_id' => ['type' => 'text', 'label' => 'App ID', 'validation' => ['nullable', 'string', 'max:80']],
                'facebook_app_secret' => ['type' => 'secret', 'label' => 'App secret', 'validation' => ['nullable', 'string', 'max:255']],
                'facebook_comments' => ['type' => 'boolean', 'label' => 'Facebook comments on product pages', 'validation' => ['boolean']],
                'facebook_pixel_id' => ['type' => 'text', 'label' => 'Pixel ID', 'validation' => ['nullable', 'string', 'max:80']],
            ],
        ],

        'whatsapp' => [
            'label' => 'WhatsApp Chat',
            'fields' => [
                'whatsapp_chat' => ['type' => 'boolean', 'label' => 'Show the WhatsApp chat widget', 'validation' => ['boolean']],
                'whatsapp_number' => ['type' => 'text', 'label' => 'WhatsApp number', 'hint' => 'Country code and number, no plus sign.', 'validation' => ['nullable', 'string', 'max:20']],
                'whatsapp_greeting' => ['type' => 'textarea', 'label' => 'Greeting message', 'validation' => ['nullable', 'string', 'max:300']],
            ],
        ],

        'shipping' => [
            'label' => 'Shipping Configuration',
            'fields' => [
                'shipping_method' => [
                    'type' => 'select',
                    'label' => 'Shipping method',
                    'options' => [
                        'product_wise' => 'Product-wise (each product carries its shipping cost)',
                        'flat' => 'Flat (one cost for every order)',
                        'seller_wise' => 'Seller-wise (each seller sets shipping for their lines)',
                        'area_wise' => 'Area-wise (per-city costs from the shipping cities)',
                        'carrier' => 'Carrier (billing ranges priced per zone)',
                    ],
                    'validation' => ['nullable', 'in:product_wise,flat,seller_wise,area_wise,carrier'],
                ],
                'flat_shipping_cost' => [
                    'type' => 'number',
                    'label' => 'Flat shipping cost',
                    'hint' => 'Used when the shipping method is flat.',
                    'validation' => ['nullable', 'numeric', 'min:0', 'max:999999'],
                ],
            ],
        ],

        'delivery-boy' => [
            'label' => 'Delivery Boy Configuration',
            'fields' => [
                'delivery_boy_pickup_time' => [
                    'type' => 'number',
                    'label' => 'Pickup time limit (minutes)',
                    'hint' => 'How long a delivery boy has to pick up an assigned order.',
                    'validation' => ['nullable', 'integer', 'min:1', 'max:10080'],
                ],
                'delivery_boy_delivery_time' => [
                    'type' => 'number',
                    'label' => 'Delivery time limit (minutes)',
                    'hint' => 'How long a delivery boy has to complete a picked-up order.',
                    'validation' => ['nullable', 'integer', 'min:1', 'max:10080'],
                ],
                'delivery_boy_cancel_order_time' => [
                    'type' => 'number',
                    'label' => 'Cancel request window (minutes)',
                    'hint' => 'How long after assignment a delivery boy may request to cancel.',
                    'validation' => ['nullable', 'integer', 'min:1', 'max:10080'],
                ],
            ],
        ],

        'homepage-select' => [
            'label' => 'Select Homepage',
            'fields' => [
                'homepage_layout' => [
                    'type' => 'select',
                    'label' => 'Homepage layout',
                    'options' => [
                        'classic' => 'Classic (sliders, featured categories, best sellers)',
                        'minimal' => 'Minimal (hero banner and curated picks)',
                        'boutique' => 'Boutique (brand-first, story blocks)',
                        'marketplace' => 'Marketplace (top sellers and deals up front)',
                        'campaign' => 'Campaign (flash deals and seasonal banners)',
                    ],
                    'validation' => ['nullable', 'in:classic,minimal,boutique,marketplace,campaign'],
                ],
            ],
        ],

        'homepage' => [
            'label' => 'Homepage Settings',
            'fields' => [
                'home_slider' => ['type' => 'boolean', 'label' => 'Show the hero slider', 'validation' => ['boolean']],
                'home_banner_1' => ['type' => 'text', 'label' => 'Banner 1 image (upload URL)', 'validation' => ['nullable', 'string', 'max:255']],
                'home_banner_1_link' => ['type' => 'text', 'label' => 'Banner 1 link', 'validation' => ['nullable', 'string', 'max:255']],
                'home_banner_2' => ['type' => 'text', 'label' => 'Banner 2 image (upload URL)', 'validation' => ['nullable', 'string', 'max:255']],
                'home_banner_2_link' => ['type' => 'text', 'label' => 'Banner 2 link', 'validation' => ['nullable', 'string', 'max:255']],
                'home_featured_section' => [
                    'type' => 'select',
                    'label' => 'Featured section under the banners',
                    'options' => [
                        'best_sellers' => 'Best sellers',
                        'new_arrivals' => 'New arrivals',
                        'on_sale' => 'On sale',
                        'none' => 'None',
                    ],
                    'validation' => ['nullable', 'in:best_sellers,new_arrivals,on_sale,none'],
                ],
            ],
        ],

        'header' => [
            'label' => 'Header',
            'fields' => [
                'header_top_bar' => ['type' => 'boolean', 'label' => 'Top bar (contact and links)', 'validation' => ['boolean']],
                'header_announcement' => ['type' => 'textarea', 'label' => 'Announcement line', 'validation' => ['nullable', 'string', 'max:200']],
                'header_sticky' => ['type' => 'boolean', 'label' => 'Sticky header on scroll', 'validation' => ['boolean']],
            ],
        ],

        'footer' => [
            'label' => 'Footer',
            'fields' => [
                'footer_about' => ['type' => 'textarea', 'label' => 'About text', 'validation' => ['nullable', 'string', 'max:500']],
                'footer_links_note' => ['type' => 'text', 'label' => 'Links column heading', 'validation' => ['nullable', 'string', 'max:60']],
                'footer_payment_logos' => ['type' => 'boolean', 'label' => 'Show payment logos', 'validation' => ['boolean']],
                'footer_copyright' => ['type' => 'text', 'label' => 'Copyright line', 'validation' => ['nullable', 'string', 'max:200']],
            ],
        ],

        'appearance' => [
            'label' => 'Appearance',
            'fields' => [
                'theme_color' => ['type' => 'text', 'label' => 'Theme colour (hex)', 'hint' => 'Like #F4772E.', 'validation' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/']],
                'accent_color' => ['type' => 'text', 'label' => 'Accent colour (hex)', 'validation' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/']],
                'site_logo' => ['type' => 'text', 'label' => 'Logo image (upload URL)', 'validation' => ['nullable', 'string', 'max:255']],
                'site_favicon' => ['type' => 'text', 'label' => 'Favicon (upload URL)', 'validation' => ['nullable', 'string', 'max:255']],
                'site_meta_title' => ['type' => 'text', 'label' => 'Meta title', 'validation' => ['nullable', 'string', 'max:120']],
                'site_meta_description' => ['type' => 'textarea', 'label' => 'Meta description', 'validation' => ['nullable', 'string', 'max:300']],
            ],
        ],

        'authentication' => [
            'label' => 'Authentication Layout & Settings',
            'fields' => [
                'auth_layout' => [
                    'type' => 'select',
                    'label' => 'Layout',
                    'options' => [
                        'split' => 'Split (image beside the form)',
                        'centered' => 'Centred card',
                        'banner' => 'Full-width banner',
                    ],
                    'validation' => ['nullable', 'in:split,centered,banner'],
                ],
                'auth_image' => ['type' => 'text', 'label' => 'Side image (upload URL)', 'validation' => ['nullable', 'string', 'max:255']],
                'auth_allow_registration' => ['type' => 'boolean', 'label' => 'Allow customer registration', 'validation' => ['boolean']],
                'auth_email_verification' => ['type' => 'boolean', 'label' => 'Verify email after registration', 'validation' => ['boolean']],
            ],
        ],

        'file-system' => [
            'label' => 'File System & Cache',
            'fields' => [
                'filesystem_driver' => [
                    'type' => 'select',
                    'label' => 'File system driver',
                    'options' => ['local' => 'Local disk', 's3' => 'Amazon S3'],
                    'validation' => ['nullable', 'in:local,s3'],
                ],
                's3_key' => ['type' => 'secret', 'label' => 'S3 access key', 'validation' => ['nullable', 'string', 'max:255']],
                's3_secret' => ['type' => 'secret', 'label' => 'S3 secret key', 'validation' => ['nullable', 'string', 'max:255']],
                's3_bucket' => ['type' => 'text', 'label' => 'S3 bucket', 'validation' => ['nullable', 'string', 'max:120']],
                's3_region' => ['type' => 'text', 'label' => 'S3 region', 'validation' => ['nullable', 'string', 'max:60']],
            ],
        ],
    ],
];

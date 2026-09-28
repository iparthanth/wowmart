<?php

/**
 * The panel's permission registry.
 *
 * A permission is a key here ("orders", "products"), shown as a labelled
 * checkbox on the role form. Each key lists the route-name patterns it opens,
 * matched with Str::is, so granting "orders" covers every admin.orders.* page
 * and its /api/v1 counterpart with one checkbox. A new admin screen joins its
 * key's patterns here and every role holding the key can open it.
 *
 * `always` lists routes every signed-in panel user may open: the dashboard is
 * the landing page and logout must always work.
 */

return [
    'always' => [
        'admin.dashboard',
        'admin.logout',
        'api.v1.auth.me',
        'api.v1.auth.logout',
    ],

    'permissions' => [
        'products' => [
            'label' => 'Products & catalogue',
            'patterns' => [
                'admin.products.*', 'admin.categories.*', 'admin.brands.*', 'admin.attributes.*',
                'admin.colors.*', 'admin.warranties.*', 'admin.notes.*', 'admin.reviews.*',
                'api.v1.products.*', 'api.v1.categories.*', 'api.v1.brands.*', 'api.v1.attributes.*',
                'api.v1.attribute-values.*', 'api.v1.colors.*', 'api.v1.warranties.*', 'api.v1.notes.*', 'api.v1.reviews.*',
            ],
        ],
        'orders' => [
            'label' => 'Orders',
            'patterns' => ['admin.orders.*', 'api.v1.orders.*'],
        ],
        'refunds' => [
            'label' => 'Refunds',
            'patterns' => ['admin.refunds.*', 'api.v1.refund-requests.*', 'api.v1.refund-settings.*'],
        ],
        'customers' => [
            'label' => 'Customers',
            'patterns' => ['admin.customers.*', 'api.v1.customers.*'],
        ],
        'sellers' => [
            'label' => 'Sellers',
            'patterns' => ['admin.sellers.*', 'api.v1.sellers.*'],
        ],
        'reports' => [
            'label' => 'Reports',
            'patterns' => ['admin.reports.*', 'api.v1.reports.*'],
        ],
        'media' => [
            'label' => 'Media library',
            'patterns' => ['admin.uploads.*', 'api.v1.uploads.*'],
        ],
        'marketing' => [
            'label' => 'Marketing (flash deals, coupons)',
            'patterns' => ['admin.flash-deals.*', 'admin.coupons.*', 'api.v1.flash-deals.*', 'api.v1.coupons.*'],
        ],
        'staff' => [
            'label' => 'Staff accounts',
            'patterns' => ['admin.staff.*', 'api.v1.staff.*'],
        ],
        'roles' => [
            'label' => 'Roles & permissions',
            'patterns' => ['admin.roles.*', 'api.v1.roles.*', 'api.v1.permissions.*'],
        ],
        'languages' => [
            'label' => 'Languages',
            'patterns' => ['admin.languages.*', 'api.v1.languages.*'],
        ],
        'support' => [
            'label' => 'Support desk (tickets, queries, conversations, contacts)',
            'patterns' => ['admin.support.*', 'api.v1.support.*'],
        ],
        'affiliate' => [
            'label' => 'Affiliate system (configs, users, referrals, withdrawals, logs)',
            'patterns' => ['admin.affiliate.*', 'api.v1.affiliate.*'],
        ],
        'otp' => [
            'label' => 'OTP system (login toggles, OTP parameters, SMS templates)',
            'patterns' => ['admin.otp.*', 'api.v1.otp.*'],
        ],
        'payment-gateways' => [
            'label' => 'Payment gateways (methods, manual methods, offline verifications)',
            'patterns' => ['admin.payment-methods.*', 'admin.manual-methods.*', 'admin.offline.*', 'api.v1.payment-methods.*', 'api.v1.manual-payment-methods.*', 'api.v1.offline.*'],
        ],
        'system' => [
            'label' => 'System (update, server status, sitemaps)',
            'patterns' => ['admin.system.*'],
        ],
        'notifications' => [
            'label' => 'Notifications (settings, types, custom sends, bulk SMS)',
            'patterns' => ['admin.notifications.*', 'admin.bulk-sms.*', 'api.v1.notifications.*', 'api.v1.bulk-sms.*'],
        ],
        'settings' => [
            'label' => 'Panel configuration (settings, currency, tax, pickup)',
            'patterns' => [
                'admin.settings.*', 'admin.currencies.*', 'admin.taxes.*', 'admin.pickup-points.*',
                'api.v1.settings.*', 'api.v1.currencies.*', 'api.v1.taxes.*', 'api.v1.pickup-points.*',
            ],
        ],
        'delivery-boys' => [
            'label' => 'Delivery boys and their money trail',
            'patterns' => [
                'admin.delivery-boys.*',
                'api.v1.delivery-boys.*',
            ],
        ],
        'shipping' => [
            'label' => 'Shipping (countries, states, cities, zones, carriers)',
            'patterns' => [
                'admin.countries.*', 'admin.states.*', 'admin.cities.*', 'admin.zones.*', 'admin.carriers.*',
                'api.v1.countries.*', 'api.v1.states.*', 'api.v1.cities.*', 'api.v1.zones.*', 'api.v1.carriers.*',
            ],
        ],
        'pages' => [
            'label' => 'Static pages',
            'patterns' => [
                'admin.pages.*',
                'api.v1.pages.*',
            ],
        ],
        'blog' => [
            'label' => 'Blog (posts and categories)',
            'patterns' => [
                'admin.blog.*',
                'api.v1.blogs.*',
                'api.v1.blog-categories.*',
            ],
        ],
    ],
];

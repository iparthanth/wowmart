<?php

/**
 * Generates docs/postman_collection.json from the live /api/v1 route list.
 *
 * The native Postman v2.1 format always imports cleanly (no OpenAPI-importer
 * quirks): one folder per module, example request bodies extracted from the
 * admin FormRequests' validation rules, sample ids in the URLs, and a Sign in
 * request that captures the bearer token into the collection automatically.
 * Run: php scripts/generate-postman.php
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$routeJson = shell_exec('php artisan route:list --json --path=api/v1');
$routes = json_decode($routeJson, true);

if (! is_array($routes)) {
    fwrite(STDERR, "Could not read the route list.\n");
    exit(1);
}

const SAMPLE_ORDER_CODE = '20260926-21150011';

$titles = [
    'auth' => 'Authentication',
    'products' => 'Products',
    'categories' => 'Categories',
    'brands' => 'Brands',
    'attributes' => 'Attributes',
    'attribute-values' => 'Attribute Values',
    'colors' => 'Colors',
    'currencies' => 'Currencies',
    'customers' => 'Customers',
    'staff' => 'Staff',
    'roles' => 'Roles & Permissions',
    'permissions' => 'Roles & Permissions',
    'sellers' => 'Sellers',
    'delivery-boys' => 'Delivery Boys',
    'delivery-boys-payment-histories' => 'Delivery Boy Payments',
    'delivery-boys-collection-histories' => 'Delivery Boy Collections',
    'orders' => 'Orders',
    'refund-requests' => 'Refund Requests',
    'refund-settings' => 'Refund Settings',
    'flash-deals' => 'Flash Deals',
    'blogs' => 'Blog',
    'blog-categories' => 'Blog Categories',
    'pages' => 'Pages',
    'notes' => 'Notes',
    'uploads' => 'Media Uploads',
    'carriers' => 'Carriers',
    'carrier-ranges' => 'Carrier Ranges',
    'carrier-range-price' => 'Carrier Range Prices',
    'pickup-points' => 'Pickup Points',
    'zones' => 'Zones',
    'states' => 'States',
    'cities' => 'Cities',
    'countries' => 'Countries',
    'coupons' => 'Coupons',
    'settings' => 'Settings',
    'taxes' => 'Taxes',
    'warranties' => 'Warranties',
    'dynamic-popups' => 'Dynamic Pop-ups',
    'custom-alerts' => 'Custom Alerts',
    'email-templates' => 'Email Templates',
    'subscribers' => 'Subscribers',
    'reports' => 'Reports',
];

/** Resource slug -> the admin FormRequest whose validation rules feed the example body. */
$requestClasses = [
    'attributes' => 'AttributeRequest',
    'attribute-values' => 'AttributeValueRequest',
    'blog-categories' => 'BlogCategoryRequest',
    'blogs' => 'BlogRequest',
    'brands' => 'BrandRequest',
    'carrier-ranges' => 'CarrierRangeRequest',
    'carriers' => 'CarrierRequest',
    'categories' => 'CategoryRequest',
    'cities' => 'CityRequest',
    'colors' => 'ColorRequest',
    'countries' => 'CountryRequest',
    'coupons' => 'CouponRequest',
    'currencies' => 'CurrencyRequest',
    'custom-alerts' => 'CustomAlertRequest',
    'customers' => 'CustomerRequest',
    'delivery-boys' => 'DeliveryBoyRequest',
    'delivery-boys-payment-histories' => 'DeliveryBoyHistoryRequest',
    'delivery-boys-collection-histories' => 'DeliveryBoyHistoryRequest',
    'dynamic-popups' => 'DynamicPopupRequest',
    'email-templates' => 'EmailTemplateRequest',
    'flash-deals' => 'FlashDealRequest',
    'newsletters' => 'NewsletterRequest',
    'notes' => 'NoteRequest',
    'pages' => 'PageRequest',
    'pickup-points' => 'PickupPointRequest',
    'products' => 'ProductRequest',
    'refund-settings' => 'RefundSettingsRequest',
    'roles' => 'RoleRequest',
    'staff' => 'StaffRequest',
    'states' => 'StateRequest',
    'taxes' => 'TaxRequest',
    'uploads' => 'UploadRequest',
    'warranties' => 'WarrantyRequest',
    'zones' => 'ZoneRequest',
];

/**
 * @return array<string, mixed> field => example value, guessed from the rules
 */
function exampleBodyFromRules(string $requestClass): array
{
    $file = app_path("Http/Requests/Admin/{$requestClass}.php");
    if (! is_file($file)) {
        return [];
    }

    $body = [];
    $field = null;
    $buffer = '';
    $depth = 0;

    // Enum-shaped fields whose in: rule is built at runtime (implode), so it
    // cannot be read statically.
    $special = [
        'page' => 'home',
        'discount_type' => 'amount',
        'stock_visibility_state' => 'in_stock',
        'shipping_type' => 'flat',
        'video_provider' => 'youtube',
        'frequently_bought_selection_type' => 'category',
        'receiver' => 'customer',
        'segment' => 'subscribers',
        'approval' => 'pending',
        'method' => 'GET',
    ];

    $flush = function (string $field, string $rules) use (&$body, $special): void {
        if ($field === 'id' || $rules === '') {
            return;
        }
        if (str_contains($rules, 'array')) {
            $body[$field] = [1];

            return;
        }
        if (str_contains($rules, 'integer') || str_contains($rules, 'exists')) {
            $body[$field] = 1;

            return;
        }
        if (preg_match("/'in:([^'\"]+)'/", $rules, $m)) {
            $body[$field] = explode(',', $m[1])[0];

            return;
        }
        if (isset($special[$field])) {
            $body[$field] = $special[$field];

            return;
        }
        if (str_contains($rules, 'boolean')) {
            $body[$field] = true;

            return;
        }
        if (str_contains($rules, 'date')) {
            $body[$field] = '2026-09-27';

            return;
        }
        if (str_contains($rules, 'email')) {
            $body[$field] = 'sample@wowmart.test';

            return;
        }
        if (str_contains($field, 'password')) {
            $body[$field] = 'password';

            return;
        }
        if (in_array($field, ['banner', 'logo', 'image', 'photo', 'file', 'document', 'icon'], true)) {
            $body[$field] = 1; // an id from the media library

            return;
        }
        if ($field === 'code') {
            $body[$field] = SAMPLE_ORDER_CODE;

            return;
        }
        if ($field === 'slug') {
            $body[$field] = 'sample-slug';

            return;
        }
        if (preg_match('/(numeric|decimal)/', $rules)) {
            $body[$field] = 10.5;

            return;
        }

        $body[$field] = 'Sample';
    };

    foreach (file($file) as $line) {
        if ($field === null) {
            if (preg_match("/^\s+'([a-z_0-9]+)'\s*=>\s*\[(.*)$/", $line, $m)) {
                $field = $m[1];
                $buffer = $m[2];
                $depth = substr_count($buffer, '[') - substr_count($buffer, ']') + 1;
                if ($depth <= 0) {
                    $flush($field, $buffer);
                    $field = null;
                    $buffer = '';
                }
            }

            continue;
        }

        $buffer .= ' '.$line;
        $depth += substr_count($line, '[') - substr_count($line, ']');
        if ($depth <= 0 && str_contains($buffer, ']')) {
            $flush($field, $buffer);
            $field = null;
            $buffer = '';
        }
    }
    if ($field !== null) {
        $flush($field, $buffer);
    }

    return $body;
}

function sampleForParam(string $name): string
{
    return match ($name) {
        'order', 'code' => SAMPLE_ORDER_CODE,
        'screen' => 'shipping',
        'identifier' => 'order_placed',
        'slug' => 'sample-slug',
        default => '1',
    };
}

function summaryFor(string $action, string $noun): string
{
    return match ($action) {
        'index' => "List {$noun}",
        'store' => "Create {$noun}",
        'show' => "Show {$noun}",
        'update' => "Update {$noun}",
        'destroy' => "Delete {$noun}",
        'flags' => "Toggle {$noun} flags",
        'duplicate' => "Duplicate {$noun}",
        'delivery-status' => 'Update delivery status',
        'payment-status' => 'Update payment status',
        'status' => 'Update refund status',
        'verification' => 'Update seller verification',
        'send' => 'Send the newsletter',
        'test-mail' => 'Send a test e-mail',
        'me' => 'Show the signed-in admin',
        'login' => 'Sign in (captures the token automatically)',
        'logout' => 'Revoke the current token',
        'payments' => 'List payment histories',
        'collections' => 'List collection histories',
        default => Str::title(str_replace(['-', '.'], ' ', $action)),
    };
}

// ---------------------------------------------------------------- build items

$folders = [];
$bodyCache = [];

foreach ($routes as $route) {
    $verbs = array_values(array_filter(
        explode('|', strtoupper($route['method'])),
        fn (string $verb): bool => ! in_array($verb, ['HEAD', 'OPTIONS'], true),
    ));
    if ($verbs === []) {
        continue;
    }
    $method = $verbs[0];

    $uri = preg_replace('#^api/v1/#', '', $route['uri']);
    $segments = explode('/', trim($route['uri'], '/'));
    $tag = $segments[2] ?? 'misc';
    $tag === 'auth' ? 'Authentication' : $tag;

    $nameParts = explode('.', $route['name']);
    $action = end($nameParts);
    $noun = implode(' ', array_slice($nameParts, 2, -1));

    // Swap path placeholders for samples BEFORE prepending the {{baseUrl}}
    // variable, so the variable itself is never touched.
    $uri = preg_replace_callback('/\{([a-zA-Z_]+)(?::[a-zA-Z]+)?\}/', fn (array $m): string => sampleForParam($m[1]), $uri);
    $raw = '{{baseUrl}}/'.$uri;

    $description = 'Replace the sample id in the URL with a real one (run the List request first).';
    if ($tag === 'settings') {
        $description = 'PUT saves only the keys this screen owns (see config/settings.php); the example body fits the shipping screen.';
    }

    $request = [
        'method' => $method,
        'header' => in_array($method, ['POST', 'PUT', 'PATCH'], true)
            ? [['key' => 'Content-Type', 'value' => 'application/json']]
            : [],
        'url' => ['raw' => $raw, 'host' => ['{{baseUrl}}']],
        'description' => $description,
    ];

    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $example = match (true) {
            $route['name'] === 'api.v1.auth.login' => [
                'email' => 'admin@wowmart.test',
                'password' => 'password',
            ],
            $tag === 'settings' => [
                'shipping_method' => 'flat',
                'flat_shipping_cost' => 60,
            ],
            default => $bodyCache[$tag] ??= exampleBodyFromRules($requestClasses[$tag] ?? ''),
        };

        if ($example !== []) {
            $request['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
        }
    }

    $item = [
        'name' => summaryFor($action, $noun !== '' ? $noun : $tag),
        'request' => $request,
        'response' => [],
    ];

    if ($route['name'] === 'api.v1.auth.login') {
        $item['event'] = [[
            'listen' => 'test',
            'script' => ['type' => 'text/javascript', 'exec' => [
                'pm.test("Sign in succeeded", () => pm.response.to.have.status(200));',
                'const body = pm.response.json();',
                'pm.collectionVariables.set("token", body.token);',
                'console.log("Token saved to the collection automatically.");',
            ]],
        ]];
    }

    $folders[$tag][] = $item;
}

// ------------------------------------------------------------------ assemble

$folderItems = [];
foreach (array_keys($folders) as $tag) {
    $folderItems[] = [
        'name' => $titles[$tag] ?? Str::title(str_replace('-', ' ', $tag)),
        'description' => "Requests under /api/v1/{$tag}",
        'item' => $folders[$tag],
    ];
}

usort($folderItems, fn (array $a, array $b): int => $a['name'] === 'Authentication' ? -1 : ($b['name'] === 'Authentication' ? 1 : strcmp($a['name'], $b['name'])));

$collection = [
    'info' => [
        'name' => 'Picklymart Admin API',
        '_postman_id' => Str::uuid()->toString(),
        'description' => "Versioned REST API for the Picklymart admin panel, generated from the live route list.\n\n"
            ."1. Run **Authentication > Sign in** — the bearer token is saved to the collection automatically.\n"
           ."2. Everything else is then authorized; just Send.\n"
           .'3. Sample ids are baked into the URLs — run a List request first and paste a real id.',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => $folderItems,
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']],
    ],
    'variable' => [
        ['key' => 'baseUrl', 'value' => 'http://127.0.0.1:8124/api/v1'],
        ['key' => 'token', 'value' => ''],
    ],
];

$dir = __DIR__.'/../docs';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

file_put_contents($dir.'/postman_collection.json', json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$requests = 0;
array_walk_recursive($folderItems, function ($v) use (&$requests): void {
    if (is_string($v) && in_array($v, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $requests++;
    }
});

echo 'docs/postman_collection.json written: '.count($folderItems).' folders, '.$requests." requests.\n";

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Decides what a signed-in panel user may open, for the web panel and the API
 * from one place. Admins pass everything. Staff pass the always-allowed routes
 * plus any route whose name matches a pattern of a permission their role holds.
 * Everyone else (customers, sellers, delivery) never passes the panel gate.
 *
 * The same key list drives the role form's checkboxes and the sidebar filter,
 * so a permission cannot exist in one place and be missing from the others.
 */
class PermissionService
{
    /** @return array<string, array{label: string, patterns: list<string>}> */
    public function all(): array
    {
        return config('permissions.permissions');
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /** @return list<string> */
    public function patternsFor(string $key): array
    {
        return $this->all()[$key]['patterns'] ?? [];
    }

    /**
     * Whether the user may open the route with this name. A missing role
     * (deleted after sign-in) grants nothing, so a staff member locked out of
     * everything still sees the dashboard and can sign out.
     */
    public function allows(User $user, string $routeName): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isStaff()) {
            return false;
        }

        if (in_array($routeName, config('permissions.always'), true)) {
            return true;
        }

        if ($routeName === '') {
            return false;
        }

        $keys = $user->role?->permissionKeys() ?? [];

        foreach ($keys as $key) {
            foreach ($this->patternsFor($key) as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * The sidebar the user should see: links they cannot open are removed, and
     * a group survives only when something inside it survives. "Not built yet"
     * placeholders are inert, so they only stay inside a group that kept at
     * least one openable link.
     *
     * @param list<array<string, mixed>> $menu
     * @return list<array<string, mixed>>
     */
    public function filterMenu(array $menu, User $user): array
    {
        if ($user->isAdmin()) {
            return $menu;
        }

        $keep = function (array $node) use (&$keep, $user): bool {
            $route = $node['route'] ?? null;

            if ($route !== null) {
                return Route::has($route) && $this->allows($user, $route);
            }

            foreach ($node['children'] ?? [] as $child) {
                if ($keep($child)) {
                    return true;
                }
            }

            return false;
        };

        return array_values(array_filter($menu, $keep));
    }
}

<?php

namespace Database\Seeders;

use App\Services\RoleService;
use App\Services\StaffService;
use Illuminate\Database\Seeder;

/**
 * Two demo roles and one fictional staff member for each, so the Staff and
 * Staff permissions screens show how a role limits what someone can open.
 * Sign-in: sadiastaff and imranstaff, as listed in the README.
 */
class StaffSeeder extends Seeder
{
    public function run(RoleService $roles, StaffService $staff): void
    {
        $orders = $roles->create(['name' => 'Order manager', 'permissions' => ['orders', 'reports', 'support']]);
        $catalogue = $roles->create(['name' => 'Catalogue editor', 'permissions' => ['products', 'media']]);

        $staff->create(['name' => 'Sadia Rahman', 'email' => 'sadia@staff.wowmart.test', 'password' => 'sadiastaff', 'is_admin' => false, 'role_id' => $orders->id]);
        $staff->create(['name' => 'Imran Hossain', 'email' => 'imran@staff.wowmart.test', 'password' => 'imranstaff', 'is_admin' => false, 'role_id' => $catalogue->id]);
    }
}

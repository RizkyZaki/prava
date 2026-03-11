<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WhatsappPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard page
            'view_whatsapp::dashboard',

            // WhatsappConversation resource
            'view_any_whatsapp::conversation',
            'view_whatsapp::conversation',
            'delete_whatsapp::conversation',

            // Custom permissions
            'reply_whatsapp',
            'manage_whatsapp',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign all to super_admin
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}

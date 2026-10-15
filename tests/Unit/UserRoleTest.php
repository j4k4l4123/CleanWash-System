<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_role_methods_and_labels(): void
    {
        $pekerja = new User(['role' => 'pekerja']);
        $admin = new User(['role' => 'admin']);
        $kasir = new User(['role' => 'kasir']);

        $this->assertTrue($pekerja->isPekerja());
        $this->assertFalse($pekerja->isAdmin());
        $this->assertFalse($pekerja->isKasir());

        $this->assertEquals('Pekerja Laundry', $pekerja->role_label);
        $this->assertEquals('Admin / Owner', $admin->role_label);
        $this->assertEquals('Kasir / Petugas', $kasir->role_label);

        $this->assertStringContainsString('amber', $pekerja->role_badge_class);
    }
}

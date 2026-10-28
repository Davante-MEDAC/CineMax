<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_exactly_one_admin_exists_after_migrating(): void
    {
        $this->assertSame(1, DB::table('users')->where('role', 'admin')->count());
    }

    public function test_admin_cannot_be_inserted(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Evil',
            'email' => 'evil@example.com',
            'password' => 'x',
            'role' => 'admin',
        ]);
    }

    public function test_customer_cannot_be_promoted_to_admin(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('users')->where('id', $user->id)->update(['role' => 'admin']);
    }

    public function test_admin_cannot_be_deleted(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->where('role', 'admin')->delete();
    }
}

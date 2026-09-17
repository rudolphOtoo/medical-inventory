<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_directory(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($admin);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee($staff->name)
            ->assertSee($staff->email);
    }

    public function test_non_admin_cannot_access_users_directory(): void
    {
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($staff);
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $staff = User::factory()->departmentStaff()->create();

        $this->actingAs($staff);
        $this->post(route('users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@medtrack.test',
            'role' => UserRole::DepartmentUser->value,
            'password' => 'SecureTemp!2026',
        ])->assertForbidden();
    }

    public function test_admin_can_create_user_with_department_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $dept = Department::factory()->create();

        $this->actingAs($admin);
        $response = $this->post(route('users.store'), [
            'name' => 'Dr. Maya Chen',
            'email' => 'maya.chen@medtrack.test',
            'role' => UserRole::DepartmentUser->value,
            'department_id' => $dept->id,
            'password' => 'SecureTemp!2026',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'maya.chen@medtrack.test',
            'role' => UserRole::DepartmentUser->value,
            'department_id' => $dept->id,
        ]);

        $user = User::where('email', 'maya.chen@medtrack.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecureTemp!2026', $user->password));
    }

    public function test_admin_can_create_admin_account_without_department(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $response = $this->post(route('users.store'), [
            'name' => 'Dr. Helen Wu',
            'email' => 'helen.wu@medtrack.test',
            'role' => UserRole::Admin->value,
            'department_id' => null,
            'password' => 'SecureTemp!2026',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'helen.wu@medtrack.test',
            'role' => UserRole::Admin->value,
            'department_id' => null,
        ]);
    }

    public function test_staff_account_requires_department_assignment(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $response = $this->post(route('users.store'), [
            'name' => 'Dr. No Ward',
            'email' => 'n.ward@medtrack.test',
            'role' => UserRole::DepartmentUser->value,
            'department_id' => null,
            'password' => 'SecureTemp!2026',
        ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertDatabaseMissing('users', ['email' => 'n.ward@medtrack.test']);
    }

    public function test_admin_can_update_user_and_department_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $deptOld = Department::factory()->create();
        $deptNew = Department::factory()->create();
        $user = User::factory()->departmentStaff($deptOld->id)->create();

        $this->actingAs($admin);
        $response = $this->put(route('users.update', $user), [
            'name' => 'Renamed Staff Member',
            'email' => $user->email,
            'role' => UserRole::DepartmentUser->value,
            'department_id' => $deptNew->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Renamed Staff Member',
            'department_id' => $deptNew->id,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->departmentStaff()->create();

        $this->actingAs($admin);

        $this->post(route('users.status', $user))->assertRedirect();
        $this->assertFalse($user->refresh()->is_active);

        $this->post(route('users.status', $user))->assertRedirect();
        $this->assertTrue($user->refresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);
        $this->post(route('users.status', $admin))->assertRedirect();

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admin_can_filter_users_by_department_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $deptA = Department::factory()->create();
        $deptB = Department::factory()->create();

        $alice = User::factory()->departmentStaff($deptA->id)->create(['name' => 'Alice Rivers']);
        $bob = User::factory()->departmentStaff($deptB->id)->create(['name' => 'Bob Carter']);
        $casey = User::factory()->departmentStaff($deptA->id)->create(['name' => 'Casey Jones', 'is_active' => false]);

        $this->actingAs($admin);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee($alice->name)
            ->assertSee($bob->name);

        $this->get(route('users.index', ['department_id' => $deptA->id]))
            ->assertSee('Alice Rivers')
            ->assertSee('Casey Jones')
            ->assertDontSee('Bob Carter');

        $this->get(route('users.index', ['status' => 'inactive']))
            ->assertSee('Casey Jones')
            ->assertDontSee('Alice Rivers')
            ->assertDontSee('Bob Carter');
    }

    public function test_admin_can_search_users_by_name_or_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Goldie Halloway', 'email' => 'goldie@medtrack.test']);
        User::factory()->create(['name' => 'Rusty Naylor', 'email' => 'rusty@medtrack.test']);

        $this->actingAs($admin);

        $this->get(route('users.index', ['search' => 'goldie']))
            ->assertSee('Goldie Halloway')
            ->assertDontSee('Rusty Naylor');

        $this->get(route('users.index', ['search' => 'rusty@medtrack']))
            ->assertSee('Rusty Naylor')
            ->assertDontSee('Goldie Halloway');
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['password' => Hash::make('OldSecure@123')]);

        $this->actingAs($admin);
        $response = $this->post(route('users.password.reset', $user), [
            'password' => 'NewSecure@456',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewSecure@456', $user->refresh()->password));
    }

    public function test_admin_can_generate_temporary_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['password' => Hash::make('OldSecure@123')]);

        $this->actingAs($admin);
        $response = $this->post(route('users.password.generate', $user));

        $response->assertRedirect();
        $this->assertFalse(Hash::check('OldSecure@123', $user->refresh()->password));
        $this->assertTrue(Hash::isHashed($user->refresh()->password));
    }

    public function test_user_can_change_own_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current@123'),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::settings.security')
            ->set('current_password', 'Current@123')
            ->set('password', 'NewSecure@456')
            ->set('password_confirmation', 'NewSecure@456')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecure@456', $user->refresh()->password));
    }

    public function test_password_update_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current@123'),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::settings.security')
            ->set('current_password', 'WrongPassword')
            ->set('password', 'NewSecure@456')
            ->set('password_confirmation', 'NewSecure@456')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('Current@123', $user->refresh()->password));
    }
}

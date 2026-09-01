<?php

namespace Tests\Feature;

use App\Models\ClinicalNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_sticky_note(): void
    {
        $user = User::factory()->departmentStaff()->create();
        $this->actingAs($user);

        $response = $this->post(route('notes.store'), [
            'title' => 'Shift Handoff ICU',
            'body' => 'All ventilator backups checked and operational.',
            'color' => 'mint',
            'tags' => 'urgent, shift-handoff',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clinical_notes', [
            'title' => 'Shift Handoff ICU',
            'color' => 'mint',
            'is_pinned' => true,
            'author_id' => $user->id,
        ]);

        $note = ClinicalNote::where('title', 'Shift Handoff ICU')->first();
        $this->assertEquals(['urgent', 'shift-handoff'], $note->tags);
    }

    public function test_author_can_delete_their_own_note(): void
    {
        $user = User::factory()->departmentStaff()->create();
        $this->actingAs($user);

        $note = ClinicalNote::create([
            'title' => 'Temporary Reminder',
            'body' => 'Test memo',
            'color' => 'canary',
            'author_id' => $user->id,
        ]);

        $response = $this->delete(route('notes.destroy', $note));

        $response->assertRedirect();
        $this->assertDatabaseMissing('clinical_notes', ['id' => $note->id]);
    }

    public function test_admin_can_delete_any_note(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->departmentStaff()->create();

        $note = ClinicalNote::create([
            'title' => 'Staff Memo',
            'body' => 'Need maintenance check',
            'color' => 'coral',
            'author_id' => $staff->id,
        ]);

        $this->actingAs($admin);
        $response = $this->delete(route('notes.destroy', $note));

        $response->assertRedirect();
        $this->assertDatabaseMissing('clinical_notes', ['id' => $note->id]);
    }

    public function test_non_admin_cannot_delete_another_users_note(): void
    {
        $userA = User::factory()->departmentStaff()->create();
        $userB = User::factory()->departmentStaff()->create();

        $note = ClinicalNote::create([
            'title' => 'User A Memo',
            'body' => 'Confidential info',
            'color' => 'azure',
            'author_id' => $userA->id,
        ]);

        $this->actingAs($userB);
        $response = $this->delete(route('notes.destroy', $note));

        $response->assertForbidden();
        $this->assertDatabaseHas('clinical_notes', ['id' => $note->id]);
    }
}

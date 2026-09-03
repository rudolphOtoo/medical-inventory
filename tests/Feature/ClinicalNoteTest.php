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

    public function test_author_can_update_their_own_note(): void
    {
        $user = User::factory()->departmentStaff()->create();
        $this->actingAs($user);

        $note = ClinicalNote::create([
            'title' => 'Initial Title',
            'body' => 'Initial message body',
            'color' => 'canary',
            'author_id' => $user->id,
            'tags' => ['old-tag'],
        ]);

        $response = $this->put(route('notes.update', $note), [
            'title' => 'Updated Title',
            'body' => 'Updated message body',
            'color' => 'azure',
            'tags' => 'new-tag, handoff',
            'is_pinned' => '1',
        ]);

        $response->assertRedirect();
        $note->refresh();

        $this->assertEquals('Updated Title', $note->title);
        $this->assertEquals('Updated message body', $note->body);
        $this->assertEquals('azure', $note->color);
        $this->assertEquals(['new-tag', 'handoff'], $note->tags);
        $this->assertTrue($note->is_pinned);
    }

    public function test_user_cannot_update_another_users_note(): void
    {
        $userA = User::factory()->departmentStaff()->create();
        $userB = User::factory()->departmentStaff()->create();

        $note = ClinicalNote::create([
            'title' => 'User A Memo',
            'body' => 'Private directives',
            'color' => 'coral',
            'author_id' => $userA->id,
        ]);

        $this->actingAs($userB);
        $response = $this->put(route('notes.update', $note), [
            'title' => 'Hacked Title',
            'body' => 'Hacked body',
            'color' => 'mint',
        ]);

        $response->assertForbidden();
        $note->refresh();
        $this->assertEquals('User A Memo', $note->title);
    }

    public function test_admin_can_update_any_note(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->departmentStaff()->create();

        $note = ClinicalNote::create([
            'title' => 'Staff Draft',
            'body' => 'Staff notes',
            'color' => 'canary',
            'author_id' => $staff->id,
        ]);

        $this->actingAs($admin);
        $response = $this->put(route('notes.update', $note), [
            'title' => 'Admin Verified Memo',
            'body' => 'Approved directives',
            'color' => 'mint',
        ]);

        $response->assertRedirect();
        $note->refresh();
        $this->assertEquals('Admin Verified Memo', $note->title);
    }

    public function test_user_can_toggle_pin_status(): void
    {
        $user = User::factory()->departmentStaff()->create();
        $this->actingAs($user);

        $note = ClinicalNote::create([
            'title' => 'Unpinned Memo',
            'body' => 'Body',
            'color' => 'canary',
            'author_id' => $user->id,
            'is_pinned' => false,
        ]);

        // Toggle to pinned
        $response = $this->patch(route('notes.pin', $note));
        $response->assertRedirect();
        $this->assertTrue($note->fresh()->is_pinned);

        // Toggle back to unpinned
        $response2 = $this->patch(route('notes.pin', $note));
        $response2->assertRedirect();
        $this->assertFalse($note->fresh()->is_pinned);
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

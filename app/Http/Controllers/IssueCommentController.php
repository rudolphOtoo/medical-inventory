<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IssueComment;
use App\Models\IssueReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IssueCommentController extends Controller
{
    /**
     * Store a new comment on an issue ticket.
     */
    public function store(Request $request, IssueReport $issue): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $issue->department_id !== $user->department_id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'is_internal_only' => ['nullable', 'boolean'],
        ]);

        $comment = IssueComment::create([
            'issue_report_id' => $issue->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
            'is_internal_only' => $request->boolean('is_internal_only'),
        ]);

        ActivityLog::record(
            $user,
            'issue.comment_added',
            "Added comment on issue #{$issue->id} ('{$issue->title}')",
            $issue
        );

        return back()->with('success', 'Comment appended to ticket.');
    }

    /**
     * Delete a comment from an issue ticket.
     */
    public function destroy(Request $request, IssueComment $comment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $comment->user_id !== $user->id) {
            abort(403, 'You may only delete your own comments.');
        }

        $issue = $comment->issue;

        $comment->delete();

        ActivityLog::record(
            $user,
            'issue.comment_removed',
            "Removed comment from issue #{$issue->id} ('{$issue->title}')",
            $issue
        );

        return back()->with('success', 'Comment removed.');
    }
}

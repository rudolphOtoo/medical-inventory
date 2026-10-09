<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueCommentRequest;
use App\Models\ActivityLog;
use App\Models\IssueComment;
use App\Models\IssueReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueCommentController extends Controller
{
    /**
     * Store a new comment on an issue ticket.
     */
    public function store(StoreIssueCommentRequest $request, IssueReport $issue): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $issue->department_id !== $user->department_id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $issue, $user, $validated): void {
            IssueComment::create([
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
        });

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

        DB::transaction(function () use ($user, $comment, $issue): void {
            $comment->delete();

            ActivityLog::record(
                $user,
                'issue.comment_removed',
                "Removed comment from issue #{$issue->id} ('{$issue->title}')",
                $issue
            );
        });

        return back()->with('success', 'Comment removed.');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['issue_report_id', 'user_id', 'body', 'is_internal_only'])]
class IssueComment extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_internal_only' => 'boolean',
            'issue_report_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    /**
     * Issue ticket this comment belongs to.
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    /**
     * Author of the comment.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for comments on a specific issue.
     */
    public function scopeForIssue($query, int $issueReportId)
    {
        return $query->where('issue_report_id', $issueReportId);
    }
}

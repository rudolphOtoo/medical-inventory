<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IssueCommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $issue_report_id
 * @property int $user_id
 * @property string $body
 * @property bool $is_internal_only
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read IssueReport $issue
 * @property-read User $author
 */
#[Fillable(['issue_report_id', 'user_id', 'body', 'is_internal_only'])]
class IssueComment extends Model
{
    /** @use HasFactory<IssueCommentFactory> */
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
     *
     * @return BelongsTo<IssueReport, $this>
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    /**
     * Author of the comment.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for comments on a specific issue.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForIssue(Builder $query, int $issueReportId): Builder
    {
        return $query->where('issue_report_id', $issueReportId);
    }
}

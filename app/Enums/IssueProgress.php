<?php

namespace App\Enums;

enum IssueProgress: string
{
    case Reported = 'reported';
    case Acknowledged = 'acknowledged';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case AwaitingParts = 'awaiting_parts';
    case ReadyForTesting = 'ready_for_testing';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Reported => 'Reported',
            self::Acknowledged => 'Acknowledged',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::AwaitingParts => 'Awaiting Parts',
            self::ReadyForTesting => 'Ready for Testing',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Reported => 'amber',
            self::Acknowledged => 'sky',
            self::Assigned => 'indigo',
            self::InProgress => 'blue',
            self::AwaitingParts => 'orange',
            self::ReadyForTesting => 'purple',
            self::Resolved => 'emerald',
            self::Closed => 'zinc',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Resolved, self::Closed], true);
    }
}

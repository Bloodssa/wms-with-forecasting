<?php

namespace App\Enum;

enum InquiryStatusType: string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case IN_PROGRESS = 'in-progress';
    case CLOSED = 'closed';
    case RESOLVED = 'resolved';
    case REPLACED = 'replaced';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::RESOLVED,
            self::REPLACED,
            self::CLOSED,
        ]);
    }

    public static function activeOptions(): array
    {
        return collect([
            self::OPEN,
            self::PENDING,
            self::IN_PROGRESS,
        ])->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }

    public function priority(): int
    {
        return match ($this) {
            self::OPEN        => 1,
            self::PENDING     => 2,
            self::IN_PROGRESS => 3,

            // same value so the inquiry is done
            self::RESOLVED,
            self::REPLACED,
            self::CLOSED      => 4,
        };
    }

    public function canTransitionTo(InquiryStatusType $target): bool
    {
        if ($this->isFinal()) {
            return false;
        }

        return $target->priority() >= $this->priority();
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}

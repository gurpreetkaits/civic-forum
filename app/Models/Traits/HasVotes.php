<?php

namespace App\Models\Traits;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVotes
{
    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function vote(int $userId, int $value): void
    {
        $existing = $this->votes()->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->value === $value) {
                $existing->delete();
            } else {
                $existing->update(['value' => $value]);
            }
        } else {
            $this->votes()->create([
                'user_id' => $userId,
                'value' => $value,
            ]);
        }

        $this->recalculateVoteCount();
    }

    public function recalculateVoteCount(): void
    {
        $this->update([
            'vote_count' => $this->votes()->sum('value'),
        ]);
    }

    public function getUserVote(int $userId): ?int
    {
        return $this->votes()->where('user_id', $userId)->value('value');
    }
}

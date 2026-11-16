<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['loggable_type', 'loggable_id', 'user_id', 'actor_name', 'action', 'from_status', 'to_status', 'comment'];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one audit entry for any model (polymorphic).
     */
    public static function record(Model $model, string $action, ?string $from = null, ?string $to = null, ?string $comment = null): self
    {
        $user = auth()->user();
        $actor = 'System';
        if ($user) {
            $actor = (!app()->runningInConsole() ? session('fitpulse_user') : null) ?: $user->name;
        }

        return static::create([
            'loggable_type' => $model->getMorphClass(),
            'loggable_id' => $model->getKey(),
            'user_id' => optional($user)->id,
            'actor_name' => $actor,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'comment' => $comment,
        ]);
    }
}

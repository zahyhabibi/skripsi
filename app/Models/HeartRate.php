<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HeartRate extends Model
{

    use HasFactory, HasUuids;

        protected $fillable = [
            'user_id',
            'heart_rate',
            'status',
            'recorded_at',
        ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

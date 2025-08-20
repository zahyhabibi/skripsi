<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultPrediction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'age',
        'gender',
        'heart_rate',
        'hasil',
        'probabilitas',
    ];
        protected $casts = [
        'probabilitas' => 'float',
    ];

    /**
     * Get the user that owns the prediction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
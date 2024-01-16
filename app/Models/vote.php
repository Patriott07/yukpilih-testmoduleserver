<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\choice;

class vote extends Model
{
    use HasFactory;

    public $fillable = [
        'choice_id', 'user_id', 'poll_id', 'division_id'
    ];

    /**
     * Get the user that owns the vote
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function choice()
    {
        return $this->belongsTo(choice::class, 'choice_id', 'id');
    }

    
}

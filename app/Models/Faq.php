<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $views_number
 * @property string $question
 * @property string $answer
 * @property boolean $active
 * @property int $pos
 *
 * @mixin Builder
 */
class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question', 'answer'
    ];
}

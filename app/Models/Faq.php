<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    public static function getQuestions($str = ''): array
    {
        $faqs = Faq::query()->where('active', true);
        if ($str){
            $faqs->where(function($query) use ($str){
               $query->where('question', 'LIKE', "%{$str}%");
               $query->orWhere('answer', 'LIKE', "%{$str}%");
            });
        }
        return $faqs->select('id', 'question')->get()->toArray();
    }

    public static function getAnswerById($id): array
    {
        $faq = Faq::where('id', $id)->first();
        if ($faq) {
            $data = [
                'answer' => Arr::get($faq, 'answer'),
                'views_number' => Arr::get($faq, 'views_number')
            ];

            $faq->addViews();

            return $data;
        }
        return [];
    }

    public function addViews()
    {
        $this->views_number++;
        $this->save();
    }
}

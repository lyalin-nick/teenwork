<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class TaskImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'task_id', 'name', 'alt', 'path', 'pos', 'ext'
    ];

    public function getNewFullPath($new_path)
    {
        return $this->path . $new_path . $this->name . '.' . $this->ext;
    }

    public function getLink()
    {//'/storage/'
        return asset($this->getFullPath());
    }

    public function getFullPath()
    {
        return $this->path . $this->name . '.' . $this->ext;
    }

    public function profile()
    {
        $this->belongsTo(Task::class, 'id', 'task_id');
    }
}

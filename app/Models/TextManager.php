<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextManager extends Model {
    protected $table = 'text_manager';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'chapter',
        'text',
    ];

    public $timestamps = false;
}

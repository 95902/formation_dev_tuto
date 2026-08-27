<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'source_type', 'path', 'content'];

    public function lines(): array
    {
        return preg_split("/\r\n|\n|\r/", $this->content);
    }
}

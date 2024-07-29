<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;


      protected $table = "books";

      protected $fillable = ["name", "image", "link", "user_id", "category_id"];

       function getUser (){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

      function getCategory (){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}

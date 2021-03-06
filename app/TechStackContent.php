<?php

namespace App;

use App\Traits\Filtering;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechStackContent extends Model
{
    use SoftDeletes, Filtering;
    /**
      * AboutMe table.
      *
      * @var string
      */
    protected $table = 'tech_stack_content';

    /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
    protected $fillable = [
      'user_id', 'body'
    ];


    /**
       * Run functions on boot.
       *
       * @return void
       */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->user_id = auth('api')->user()->id;
        });

        static::updating(function ($model) {
            $model->user_id = auth('api')->user()->id;
        });
    }

    /**
     * The TechStackContent belongs to a user.
     *
     * @return object
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

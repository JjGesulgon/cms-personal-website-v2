<?php

namespace App;

use App\Traits\Filtering;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Imaging;

class SamplePageImage extends Model
{
    use SoftDeletes, Filtering, Imaging;

    /**
     * Sample Page Images table.
     *
     * @var string
     */
    protected $table = 'sample_page_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'user_id', 'project_id', 'image'
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
              static::storeImage($model, null, true);
          });
  
          static::updating(function ($model) {
              $model->user_id = auth('api')->user()->id;
              static::updateImage($model, null, true);
          });

          static::deleting(function ($model) {
            static::deleteImage($model);
        });
      }

    /**
       * A SamplePageImage belongs to a user.
       *
       * @return object
       */
      public function user()
      {
          return $this->belongsTo(User::class);
      }

    /**
      * A SamplePageImage belongs to a Project.
      *
      * @return object
      */
     public function project()
     {
         return $this->belongsTo(Project::class);
     }
}

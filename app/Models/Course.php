<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use DB;
/**
 * Class Course
 *
 * @package App
 * @property string $title
 * @property string $slug
 * @property text $description
 * @property decimal $price
 * @property string $course_image
 * @property string $start_date
 * @property tinyInteger $published
 */
class Course extends Model
{
    use SoftDeletes;

    protected $fillable = ['client_id', 'title', 'slug', 'description', 'price', 'course_image', 'header_image','course_video', 'start_date', 'published', 'free','featured', 'trending', 'popular', 'meta_title', 'meta_description', 'meta_keywords','course_header_image'];

    protected $appends = ['image'];


    protected static function boot()
    {
        parent::boot();
        if (auth()->check()) {
            if (auth()->user()->hasRole('Supervisor')) {
                static::addGlobalScope('filter', function (Builder $builder) {
                    $builder->whereHas('Supervisors', function ($q) {
                        $q->where('course_user.user_id', '=', auth()->user()->id);
                    });
                });
            }
        }

        static::deleting(function ($course) { // before delete() method call this
            if ($course->isForceDeleting()) {
                if (File::exists(public_path('/storage/uploads/' . $course->course_image))) {
                    File::delete(public_path('/storage/uploads/' . $course->course_image));
                    File::delete(public_path('/storage/uploads/thumb/' . $course->course_image));
                }
                if (File::exists(public_path('/storage/uploads/' . $course->course_header_image))) {
                    File::delete(public_path('/storage/uploads/' . $course->course_header_image));
                }
            }
        });


    }


    public function getImageAttribute()
    {
        if ($this->course_image != null) {
            return url('storage/uploads/'.$this->course_image);
        }
        return NULL;
    }

    public function getPriceAttribute()
    {
        if (($this->attributes['price'] == null)) {
            return round(0.00);
        }
        return $this->attributes['price'];
    }


    /**
     * Set attribute to money format
     * @param $input
     */
    public function setPriceAttribute($input)
    {
        $this->attributes['price'] = $input ? $input : null;
    }

    /**
     * Set attribute to date format
     * @param $input
     */
    public function setStartDateAttribute($input)
    {
        if ($input != null && $input != '') {
            $this->attributes['start_date'] = Carbon::createFromFormat(config('app.date_format'), $input)->format('Y-m-d');
        } else {
            $this->attributes['start_date'] = null;
        }
    }

    /**
     * Get attribute from date format
     * @param $input
     *
     * @return string
     */
    public function getStartDateAttribute($input)
    {
        $zeroDate = str_replace(['Y', 'm', 'd'], ['0000', '00', '00'], config('app.date_format'));

        if ($input != $zeroDate && $input != null) {
            return Carbon::createFromFormat('Y-m-d', $input)->format(config('app.date_format'));
        } else {
            return '';
        }
    }

    public function Supervisors()
    {
        return $this->belongsToMany(User::class, 'course_user')->withPivot('user_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student')->withTimestamps()->withPivot(['rating']);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function publishedLessons()
    {
        return $this->hasMany(Lesson::class)->where('published', 1);
    }

    public function scopeOfSupervisor($query)
    {
        if (!Auth::user()->isAdmin()) {
            return $query->whereHas('Supervisors', function ($q) {
                $q->where('user_id', Auth::user()->id);
            });
        }
        return $query;
    }

    public function getRatingAttribute()
    {
        return $this->reviews->avg('rating');
    }

    public function orderItem()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function Client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Client::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    public function courseTimeline()
    {
        return $this->hasMany(CourseTimeline::class);
    }

    public function getIsAddedToCart(){
        if(auth()->check() && (auth()->user()->hasRole('student')) && (\Cart::session(auth()->user()->id)->get( $this->id))){
            return true;
        }
        return false;
    }


    public function reviews()
    {
        return $this->morphMany('App\Models\Review', 'reviewable');
    }

    public function progress($user_id='')
    {
        if(auth()->guard('vendor')->check()) {
            $user_ids=auth()->user()->students();
            $completed_lessons=DB::table('chapter_students')->where('user_id',$user_id)->where('course_id', $this->id)->get()->pluck('model_id')->toArray();
        } else {
            $completed_lessons = DB::table('chapter_students')->where('user_id',\Auth::user()->id)->where('course_id',$this->id)->distinct('model_id')->count();
        }

        // dd($completed_lessons > 0);

        if ($completed_lessons > 0 && $completed_lessons == FALSE) {
            $timelines=array();
            $timelines=DB::table('course_timeline')->where('course_id',$this->id)->distinct('model_id')->pluck('model_id')->toArray();
            $timelines=count($timelines);
            if($this->id==479 && intval(($completed_lessons / $timelines) * 100)==99){
                return 100;
            }
            if($completed_lessons > $timelines) {
                $completed_lessons=$timelines;
            }
            return intval(($completed_lessons / $timelines) * 100);
        } else {
            return 0;
        }
    }


    public function isUserCertified()
    {
        $status = false;
        $certified = auth()->user()->certificates()->where('course_id', '=', $this->id)->first();
        if ($certified != null) {
            $status = true;
        }
        return $status;
    }

    public function isUserEnrolled()
    {
        $status = false;
        $result=DB::table('course_student')->where('user_id',auth()->user()->id)->where('course_id',$this->id)->first();
        if ($result != null) {
            $status = true;
        }
        return $status;
    }

    public function item()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses');
    }

    public function chapterCount()
    {
        $timeline = $this->courseTimeline;
        $chapters = 0;
        foreach ($timeline as $item) {
            if (isset($item->model->published) && $item->model->published == 1) {
                $chapters++;
            }
        }
        return $chapters;
    }

    public function mediaVideo()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed'];
        return $this->morphOne(Media::class, 'model')
            ->whereIn('type', $types);

    }



}

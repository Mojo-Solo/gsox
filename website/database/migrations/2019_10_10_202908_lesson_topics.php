<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LessonTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(! Schema::hasTable('topics')) {
            Schema::create('topics', function (Blueprint $table){ 
                $table->increments('id');
                $table->integer('lesson_id')->unsigned()->nullable();
                $table->foreign('lesson_id', '64420_596eedbb6686e')->references('id')->on('lessons')->onDelete('cascade');
                $table->integer('course_id')->unsigned()->nullable();
                $table->foreign('course_id', '64419_596eedbb6686e')->references('id')->on('courses')->onDelete('cascade');
                $table->integer('parent_topic')->unsigned()->nullable();
                $table->foreign('parent_topic', '64421_596eedbb6686e')->references('id')->on('topics')->onDelete('cascade');
                $table->string('title')->nullable();
                $table->string('slug')->nullable();
                $table->string('topic_image')->nullable();
                $table->string('topic_video')->nullable();
                $table->string('topic_audio')->nullable();
                $table->text('full_text')->nullable();
                $table->integer('position')->nullable()->unsigned();
                $table->tinyInteger('is_quiz')->nullable()->default(1);
                $table->integer('quiz_id')->unsigned()->nullable();
                $table->tinyInteger('published')->nullable()->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['deleted_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
}

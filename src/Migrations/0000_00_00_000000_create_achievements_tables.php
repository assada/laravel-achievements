<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAchievementsTables
 */
class CreateAchievementsTables extends Migration
{
    public $achievement_details;
    public $achievement_progress;

    /**
     * CreateAchievementsTables constructor.
     */
    public function __construct()
    {
        $this->achievement_details = Config::get('achievements.table_names.details');
        $this->achievement_progress = Config::get('achievements.table_names.progress');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            $this->achievement_details,
            static function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('description');
                $table->unsignedInteger('points')->default(1);
                $table->boolean('secret')->default(false);
                $table->string('class_name');
                $table->timestamps();
            }
        );
        Schema::create(
            $this->achievement_progress,
            static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedInteger('achievement_id');
                $table->morphs('achiever');
                $table->unsignedInteger('points')->default(0);
                $table->timestamp('unlocked_at')->nullable()->default(null);
                $table->timestamps();

                $table->foreign('achievement_id')->references('id')->on('achievement_details');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_progress');
        Schema::dropIfExists('achievement_details');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobtitlePoliticalPartyEducationalPropertiesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jobtitle')->nullable();
            $table->string('political_party')->nullable();
            $table->string('educational')->nullable();
            $table->string('properties')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jobtitle');
            $table->dropColumn('political_party');
            $table->dropColumn('educational');
            $table->dropColumn('properties');
        });
    }
}

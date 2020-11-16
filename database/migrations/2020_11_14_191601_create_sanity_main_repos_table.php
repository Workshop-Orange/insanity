<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanityMainReposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sanity_main_repos', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('git');
            $table->string('branch');

            $table->foreignId('team_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sanity_main_repos');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanityDeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sanity_deployments', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('sanity_api_token');
            $table->string('sanity_project_id')->nullable();
            $table->string('sanity_dataset')->default('insanity');
            $table->string('deployment_status')->default('undeployed');
            $table->string('deployment_message')->nullable();

            $table->foreignId('sanity_main_repo_id')->constrained();
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
        Schema::dropIfExists('sanity_deployments');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('user_record', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('user_id');
      $table->integer('action');
      $table->timestamp('start_at');
      $table->timestamp('created_at');
      $table->smallInteger('update_count');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('user_record');
  }
}

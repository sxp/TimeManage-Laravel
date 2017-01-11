<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('user_action', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('user_id');
      $table->tinyInteger('tpe');
      $table->integer('parent')->nullable();
      $table->string('name');
      $table->timestamp('created_at');
    });

    $c = new \Carbon\Carbon();
    $conf = [
      '生活' => ['吃饭', '睡觉', '出行'],
      '工作' => ['工作', '开会'],
      '学习' => ['学习'],
      '娱乐' => ['玩游戏', '捣鼓新玩意'],
      '运动' => ['跑步', '走路', '羽毛球', '游泳'],
    ];

    foreach ($conf as $c1 => $c2) {
      $a = \App\UserAction::create(['user_id' => 0, 'tpe' => \App\UserAction::TPE_CATEGORY, 'name' => $c1, 'created_at' => $c]);
      foreach ($c2 as $n) {
        \App\UserAction::create(['user_id' => 0, 'tpe' => \App\UserAction::TPE_CATEGORY, 'name' => $n, 'created_at' => $c,
          'parent' => $a->id]);
      }
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('user_action');
  }
}

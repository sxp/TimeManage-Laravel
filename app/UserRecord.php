<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\UserRecord
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $user_id
 * @property integer $action
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $created_at
 * @property integer $update_count
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereStartAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRecord whereUpdateCount($value)
 */
class UserRecord extends Model
{
  protected $table = 'user_record';
  public $timestamps = false;
  protected $visible = ['id', 'start_at'];
  protected $dates = ['start_at', 'created_at'];
  protected $casts = ['action' => 'int', 'user_id' => 'int', 'update_count' => 'int'];

  /**
   * @param $uid
   * @param $id
   * @param int $protectSec
   * @return mixed
   */
  public static function changeCurrent($uid, $id, $protectSec = 60)
  {
    $id = intval($id);
    $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take(1)->get();
    $now = new Carbon();
    if (!$res->isEmpty()) {
      /** @var UserRecord $latest */
      $latest = $res->first();
      $sec = $latest->start_at->diffInSeconds($now);
      if ($sec < $protectSec) {
        $latest->action = $id;
        $latest->update_count++;
        $latest->save();
        return ['id' => $id, 'startAt' => $latest->start_at->timestamp];
      }
    }
    $row = new UserRecord();
    $row->user_id = $uid;
    $row->action = $id;
    $row->start_at = $now;
    $row->created_at = $now;
    $row->update_count = 0;
    $row->save();
    return ['id' => $id, 'startAt' => $now->timestamp];
  }

  public static function current($uid)
  {
    $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take(1)->get();
    if ($res->isEmpty()) {
      return null;
    } else {
      $res = $res->first();
      /** @var UserRecord $res */
      $ret = [];
      $ret['id'] = $res->action;
      $ret['startAt'] = $res->start_at->timestamp;
      return $ret;
    }
  }
}

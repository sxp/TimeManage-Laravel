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
   * @param $action
   * @param int $protectSec
   * @return mixed
   */
  public static function changeCurrent($uid, $action, $protectSec = 60)
  {
    $action = intval($action);
    $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take(2)->get();
    $now = new Carbon();
    if (!$res->isEmpty()) {
      /** @var UserRecord $latest */
      $latest = $res->first();
      $sec = $latest->start_at->diffInSeconds($now);
      if ($sec < $protectSec) {
        if ($res->count() > 1) {
          // 检查改变之前的已生效动作和现在要做的是否一样，如果是一样，则恢复用户之前做的事情，删除这条记录
          $before = $res->last();
          /** @var UserRecord $before */
          if ($before->action == $action) {
            UserRecord::destroy($latest->id);
            return ['actionId' => $action, 'startAt' => $before->start_at->timestamp, 'id' => $before->id];
          }
        }
        $latest->action = $action;
        $latest->update_count++;
        $latest->save();
        return ['actionId' => $action, 'startAt' => $latest->start_at->timestamp, 'id' => $latest->id];
      }
    }
    $row = new UserRecord();
    $row->user_id = $uid;
    $row->action = $action;
    $row->start_at = $now;
    $row->created_at = $now;
    $row->update_count = 0;
    $row->save();
    return ['actionId' => $action, 'startAt' => $now->timestamp, 'id' => $row->id];
  }

  public static function current($uid)
  {
    $uid = intval($uid);
    $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take(1)->get();
    if ($res->isEmpty()) {
      return null;
    } else {
      $res = $res->first();
      /** @var UserRecord $res */
      $ret = [];
      $ret['id'] = $res->id;
      $ret['actionId'] = $res->action;
      $ret['startAt'] = $res->start_at->timestamp;
      return $ret;
    }
  }

  public static function history($uid, $num, $max = 20)
  {
    $uid = intval($uid);
    $num = min(abs(intval($num)), $max);
    $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take($num)->get();
    $ret = [];
    $acts = [];
    foreach ($res as $r) {
      /** @var UserRecord $r */
      $tmp = [];
      $tmp['id'] = $r->id;
      $tmp['actionId'] = $r->action;
      $acts[] = $r->action;
      $tmp['startAt'] = $r->start_at->timestamp;
      $ret[] = $tmp;
    }
    $acts = array_unique($acts);
    $res = UserAction::whereIn('id', $acts)->get(['id', 'name']);
    $acts = [];
    foreach ($res as $r) {
      $acts[$r->id] = $r->name;
    }
    $lastTime = -1;
    foreach ($ret as &$r) {
      $r['name'] = $acts["{$r['actionId']}"];
      if ($lastTime != -1) {
        $r['due'] = $lastTime - $r['startAt'];
      }
      $lastTime = $r['startAt'];
    }
    return $ret;
  }
}

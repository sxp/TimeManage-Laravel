<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;

/**
 * App\UserAction
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $tpe
 * @property integer $parent
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereTpe($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereParent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserAction whereCreatedAt($value)
 * @mixin \Eloquent
 */
class UserAction extends Model
{
  const TPE_CATEGORY = 1;
  const TPE_OVERWRITE_DEFAULT = 2;
  const TPE_DEL_DEFAULT = 3;
  const TPE_DELETED = 4;

  protected $table = "user_action";
  public $timestamps = false;
  protected $fillable = ['user_id', 'tpe', 'parent', 'name', 'created_at'];
  protected $visible = ['id', 'name'];

  public static function validateAction($uid, $id)
  {
    $id = intval($id);
    $a = self::prepareTree($uid);
    $c = array_first($a, function ($v) use (&$id) {
      return array_first($v['children'], function ($a) use (&$id) {
        return $a['id'] === $id;
      }, false);
    });
    return $c != null;
  }

  /**
   * 获取某个用户的动作列表，打平分类信息
   * @param int $uid 用户ID
   * @return array
   */
  public static function getMyAction($uid)
  {
    $uid = intval($uid);
    $a = self::prepareTree($uid);
    return array_reduce($a, function ($ret, $i) {
      return array_merge($ret, $i['children']);
    }, []);
  }

  /**
   * 获取某个用户的完整分类列表，包含动作
   * @param int $uid 用户ID
   * @return array
   */
  public static function getMyCategory($uid)
  {
    return self::prepareTree($uid);
  }

  /**
   * 新建一个分类
   * @param int $uid 用户ID
   * @param string $name 分类名
   * @return array|string 失败返回字符串错误信息，成功返回新建的分类信息
   */
  public static function addCategory($uid, $name)
  {
    $name = trim($name);
    if (strlen($name) < 1) {
      return '分类名至少要一个字符';
    }
    $a = self::prepareTree($uid);
    $c = array_first($a, function ($v) use (&$name) {
      return $v['name'] === $name;
    });
    if ($c == null) {
      $c = new UserAction();
      $c->name = $name;
      $c->user_id = $uid;
      $c->tpe = static::TPE_CATEGORY;
      $c->created_at = new Carbon();
      $c->save();
      return ['id' => $c->id, 'name' => $c->name, 'children' => []];
    }
    return '相同分类名已经存在，不能重复创建';
  }

  /**
   * 重命名分类
   * @param int $uid 用户ID
   * @param int $id 目标分类ID
   * @param string $name 新名字
   * @return array|string 失败返回字符串错误信息，否则返回数据库操作是否成功
   */
  public static function saveCategory($uid, $id, $name)
  {
    $id = intval($id);
    $name = trim($name);
    if (strlen($name) < 1) {
      return '分类名至少要一个字符';
    }
    $a = self::prepareTree($uid);
    if (!isset($a[$id])) {
      return "分类不存在(id: $id)";
    } else {
      if ($a[$id]['name'] === $name) {
        // 没有对名字进行修改，也当做是成功的修改了名字
        return true;
      }
      $d = self::find($id);
      if ($d->parent != null) {
        return '不是一个分类(id: $id)';
      }
      $c = array_first($a, function ($v) use (&$name) {
        return $v['name'] === $name;
      });
      if ($c != null) {
        return '相同分类名已经存在，不能重名';
      }
      if ($d->tpe == static::TPE_CATEGORY) {
        if ($d->user_id == 0) {
          $old = UserAction::whereParent($id)->whereUserId($uid)->take(1)->get();
          if ($old->isEmpty()) {
            // 需要创建一个新的数据来删除默认分类
            $n = new UserAction();
            $n->name = $name;
            $n->user_id = $uid;
            $n->parent = $id;
            $n->tpe = static::TPE_OVERWRITE_DEFAULT;
            $n->created_at = new Carbon();
            $d = $n;
          } else {
            $old = $old->first();
            $old->name = $name;
            $d = $old;
          }
        } else {
          $d->name = $name;
        }
      } else {
        return '不是一个分类(id: $id)';
      }
      return $d->save();
    }
  }

  public static function deleteCategory($uid, $id)
  {
    $id = intval($id);
    $a = self::prepareTree($uid);
    if (!isset($a[$id])) {
      return "分类不存在(id: $id)";
    } else {
      $d = self::find($id);
      if ($d->parent != null) {
        return '不是一个分类(id: $id)';
      }
      if ($d->tpe == static::TPE_CATEGORY) {
        if ($d->user_id == 0) {
          $old = UserAction::whereParent($id)->whereUserId($uid)->take(1)->get();
          if ($old->isEmpty()) {
            // 需要创建一个新的数据来删除默认分类
            $n = new UserAction();
            $n->name = $d->name;
            $n->user_id = $uid;
            $n->parent = $id;
            $n->tpe = static::TPE_DEL_DEFAULT;
            $n->created_at = new Carbon();
            $d = $n;
          } else {
            $old = $old->first();
            $old->tpe = static::TPE_DEL_DEFAULT;
            $d = $old;
          }
        } else {
          $d->tpe = static::TPE_DELETED;
        }
      } else {
        return '不是一个分类(id: $id)';
      }
      return $d->save();
    }
  }

  public static function addAction($uid, $cid, $name)
  {
    $cid = intval($cid);
    $name = trim($name);
    if (strlen($name) < 1) {
      return '动作名至少要一个字符';
    }
    $a = self::prepareTree($uid);
    $c = array_first($a, function ($v) use (&$name) {
      return array_first($v['children'], function ($a) use (&$name) {
        return $a['name'] === $name;
      }, false);
    });
    if ($c == null) {
      if (isset($a[$cid])) {
        $c = new UserAction();
        $c->name = $name;
        $c->user_id = $uid;
        $c->parent = $cid;
        $c->tpe = static::TPE_CATEGORY;
        $c->created_at = new Carbon();
        $c->save();
        return ['id' => $c->id, 'name' => $c->name];
      } else {
        return "分类不存在(id: $cid)";
      }
    }
    return "相同动作名已经存在，不能重复创建(动作名: $name)";
  }

  public static function deleteAction($uid, $id)
  {
    $id = intval($id);
    $a = self::prepareTree($uid);
    $c = array_first($a, function ($v) use (&$id) {
      return array_first($v['children'], function ($a) use (&$id) {
        return $a['id'] === $id;
      }, false);
    });
    if ($c == null) {
      return "动作不存在(id: $id)";
    } else {
      $d = self::find($id);
      if ($d->tpe == static::TPE_CATEGORY) {
        if ($d->user_id == 0) {
          // 需要创建一个新的数据来覆盖默认动作
          $n = new UserAction();
          $n->name = $d->name;
          $n->user_id = $uid;
          $n->parent = $id;
          $n->tpe = static::TPE_DEL_DEFAULT;
          $n->created_at = new Carbon();
          $d = $n;
        } else {
          $d->tpe = static::TPE_DELETED;
        }
      } else if ($d->tpe == static::TPE_OVERWRITE_DEFAULT) {
        $d->tpe = static::TPE_DEL_DEFAULT;
      }
      return $d->save();
    }
  }

  public static function saveAction($uid, $id, $name, $changeOld)
  {
    $id = intval($id);
    $name = trim($name);
    $changeOld = boolval($changeOld);
    $a = self::prepareTree($uid);
    $c = array_first($a, function ($v) use (&$id) {
      return array_first($v['children'], function ($a) use (&$id) {
        return $a['id'] === $id;
      }, false);
    });
    if ($c == null) {
      return "动作不存在(id: $id)";
    } else {
      try {
        return \DB::transaction(function () use ($id, $name, $uid, $changeOld) {
          $needUpdateOld = false;
          $d = self::find($id);
          if ($d->tpe == static::TPE_CATEGORY) {
            if ($d->user_id == 0) {
              if (UserAction::whereParent($id)->whereUserId($uid)->exists()) {
                // 不应该存在覆盖此默认动作的数据
                throw new Exception('数据异常，请刷新重试');
              }
              // 需要创建一个新的数据来覆盖默认动作
              $n = new UserAction();
              $n->name = $name;
              $n->user_id = $uid;
              $n->parent = $id;
              $n->tpe = static::TPE_OVERWRITE_DEFAULT;
              $n->created_at = new Carbon();
              $d = $n;
              // 因为涉及到新建了动作，如果要修改旧的记录，需要更新旧记录到新的 id 上
              $needUpdateOld = true;
            } else {
              if ($changeOld) {
                $d->name = $name;
              } else {
                // 如果不修改之前的动作记录，则需要新建一个记录
                $d->tpe = static::TPE_DELETED;
                if (!$d->save()) throw new Exception('更新数据失败，请重试');

                $n = new UserAction();
                $n->name = $name;
                $n->user_id = $uid;
                $n->parent = $d->parent;
                $n->tpe = static::TPE_CATEGORY;
                $n->created_at = new Carbon();
                $d = $n;
                $needUpdateOld = true;
              }
            }
          } else if ($d->tpe == static::TPE_OVERWRITE_DEFAULT) {
            if ($changeOld) {
              $d->name = $name;
            } else {
              // 如果不修改之前的动作记录，则需要新建一个记录
              $d->tpe = static::TPE_DELETED;
              if (!$d->save()) throw new Exception('更新数据失败，请重试');

              $n = new UserAction();
              $n->name = $name;
              $n->user_id = $uid;
              $n->parent = $d->parent;
              $n->tpe = static::TPE_OVERWRITE_DEFAULT;
              $n->created_at = new Carbon();
              $d = $n;
              $needUpdateOld = true;
            }
          }
          if (!$d->save()) throw new Exception('更新数据失败，请重试');
          $newId = intval($d->id);
          if ($needUpdateOld) {
            $res = false;
            if ($changeOld) {
              // 更新之前的记录的动作引用
              $res = UserRecord::whereAction($id)->whereUserId($uid)->update(['action' => $newId]);
            } else {
              // 如果当前正在做的就是我们需要重命名的动作，则更新他
              $res = UserRecord::whereUserId($uid)->orderBy('start_at', 'desc')->take(1)->get();
              if ($res->isEmpty()) {
                $res = true;
              } else {
                $res = $res->first();
                /** @var UserRecord $res */
                if ($res->action == $id) {
                  $res = UserRecord::whereId($res->id)->update(['action' => $newId]);
                } else {
                  $res = true;
                }
              }
            }
            if (!$res) throw new Exception('更新数据失败，请重试');
          }
          return $newId;
        });
      } catch (Exception $exp) {
        return $exp->getMessage();
      }
    }
  }

  private static function prepareTree($uid)
  {
    $a = UserAction::whereIn('user_id', [$uid, 0])->get();
    $ret = [];
    $map = [];
    foreach ($a as $c) {
      $map[$c->id] = $c;
    }
    foreach ($map as $id => $item) {
      switch ($item->tpe) {
        case static::TPE_DEL_DEFAULT:
          unset($map[$item->parent]);
          unset($map[$id]);
          unset($ret[$item->parent]);
          break;
        case static::TPE_OVERWRITE_DEFAULT:
          $p = $map[$item->parent];
          if ($p->parent != null) {
            // 如果覆盖的是一个默认动作，则将被覆盖动作的父子关系继承下来
            $item->tpe = static::TPE_CATEGORY;
            $item->parent = $p->parent;
            unset($map[$p->id]);
          } else {
            // 如果覆盖的是一个默认分类，则简单修改分类的名字，因为分类的 id 会被其他父子关系引用，所以不能删除
            $p->name = $item->name;
            unset($map[$id]);
          }
          break;
        case static::TPE_DELETED:
          unset($map[$id]);
          break;
        case static::TPE_CATEGORY:
          if ($item->parent == null) {
            $tmp = ['id' => $id];
            $tmp['name'] = $item->name;
            $tmp['children'] = [];
            $ret[$id] = $tmp;
          }
          break;
      }
    }
    $aMap = [];
    foreach ($map as $id => $item) {
      if ($item->parent != null && isset($ret[$item->parent])) {
        $c = &$ret[$item->parent];
        $tmp = ['id' => $id];
        $tmp['name'] = $item->name;
        $c['children'][] = $tmp;
        $aMap[$id] = &$c['children'][sizeof($c['children'])-1];
      }
    }
    $res = UserRecord::whereUserId($uid)->whereIn('action', array_keys($aMap))->select(DB::raw('count(*) as ac, action'))->groupBy(['action'])->get();
    foreach ($res as $r) {
      $d = &$aMap[$r->action];
      $d['recordNum'] = $r->ac;
    }
    return $ret;
  }
}

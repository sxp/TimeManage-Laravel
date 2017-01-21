<?php

namespace App\Http\Controllers;

use App\UserAction;
use App\UserRecord;
use Illuminate\Http\Request;

class RecordManager extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function changeCurrent(Request $req)
  {
    $id = $req->input('id');
    if (UserAction::validateAction(\Auth::id(), $id)) {
      $ret = UserRecord::changeCurrent(\Auth::id(), $id);
      return response()->json(['res' => 0, 'data' => $ret]);
    } else {
      return response()->json(['res' => -1, 'msg' => "动作不存在(id: $id)"]);
    }
  }

  public function current()
  {
    $ret = UserRecord::current(\Auth::id());
    if ($ret == null) {
      return response()->json([]);
    } else {
      return response()->json([$ret]);
    }
  }

  public function history($num = 10, Request $req)
  {
    $num = intval($num);
    $ret = UserRecord::history(\Auth::id(), $num);
    return response()->json(['res' => 0, 'data' => $ret]);
  }
}

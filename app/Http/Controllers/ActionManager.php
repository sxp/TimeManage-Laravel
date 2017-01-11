<?php

namespace App\Http\Controllers;

use App\UserAction;
use Illuminate\Http\Request;

class ActionManager extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function myActionList()
  {
    $a = UserAction::getMyAction(\Auth::id());
    return response()->json($a);
  }

  public function myCategoryList(Request $req)
  {
    $a = UserAction::getMyCategory(\Auth::id());
    return response()->json(array_values($a));
  }

  public function deleteCategory(Request $req)
  {
    $ret = UserAction::deleteCategory(\Auth::id(), $req->input('id'));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0]);
    }
  }

  public function saveCategory(Request $req)
  {
    $ret = UserAction::saveCategory(\Auth::id(), $req->input('id'), $req->input('name'));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0]);
    }
  }

  public function addCategory(Request $req)
  {
    $ret = UserAction::addCategory(\Auth::id(), $req->input('name'));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0, 'data' => $ret]);
    }
  }

  public function addAction(Request $req)
  {
    $ret = UserAction::addAction(\Auth::id(), $req->input('cid'), $req->input('name'));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0, 'data' => $ret]);
    }
  }

  public function saveAction(Request $req)
  {
    $ret = UserAction::saveAction(\Auth::id(), $req->input('id'), $req->input('name'), $req->input('changeOld', true));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0, 'id' => $ret]);
    }
  }

  public function deleteAction(Request $req)
  {
    $ret = UserAction::deleteAction(\Auth::id(), $req->input('id'));
    if (is_string($ret)) {
      return response()->json(['res' => -1, 'msg' => $ret]);
    } else {
      return response()->json(['res' => 0]);
    }
  }
}

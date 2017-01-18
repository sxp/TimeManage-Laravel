<?php

namespace App\Http\Controllers;

use App\UserAction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //$this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('index-' . env('FRONT_TYPE', 'ng2'));
  }

  public function accountInfo()
  {
    if (\Auth::check()) {
      $data = [];
      $user = \Auth::user();
      $qqUser = \Session::get('qqUser');
      if ($qqUser != null) {
        $data['qqNick'] = $qqUser->nickname;
        $data['avatarUrl'] = $qqUser->figureurl_qq_1;
      }
      $data['name'] = $user->name;
      return response()->json(['res' => 'logon', 'account' => $data]);
    } else {
      return response()->json(['res' => 'guest']);
    }
  }
}

<?php

namespace App\Http\Controllers;

use App\QQHelper;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QQAuthController extends Controller
{
  use RegistersUsers {
    register as oRegister;
  }

  protected $redirectTo = '/';
  private $rules = [
    'name' => 'required|max:12|min:3',
  ];

  /**
   * QQAuthController constructor.
   */
  public function __construct()
  {
    $this->middleware('guest');
  }

  public function index()
  {
    return view('index-' . env('FRONT_TYPE', 'ng2'));
  }

  public function code(Request $req, QQHelper $qq)
  {
    $code = $req->input('code');
    if ($code == null) {
      return redirect('/');
    }
    $ret = $this->getContents($qq->tokenUrl($code));
    parse_str($ret, $out);
    if (isset($out['access_token'])) {
      $ret = $this->getContents($qq->me($out['access_token']));
      preg_match('/.*"openid":"([^"]+)".*/', $ret, $m);
      $openId = $m[1];
      $ret = $this->getContents($qq->getUserInfo($out['access_token'], $openId));
      $user = json_decode($ret);
//    var_dump($ret);
//    var_dump($user);
      logger(json_encode($user));
      session(['qqAccessToken' => $out['access_token'], 'qqOpenId' => $openId, 'qqUser' => $user]);
      $ret = \DB::selectOne("SELECT * FROM auth WHERE id_str = ?", [$openId]);
      if ($ret == null) {
        return redirect('/auth/qq/create');
      } else {
        Auth::loginUsingId($ret->id);
        return redirect('/');
      }
    } else {
      return redirect('/');
    }
  }

  public function qqUserInfo(Request $req, QQHelper $qq)
  {
    if (!\Session::has('qqUser')) {
      return response()->json(['res' => -1]);
    }
    $data = [];
    $user = \Session::get('qqUser');
    $data['qqNick'] = $user->nickname;
    $data['avatarUrl'] = $user->figureurl_qq_1;
    return response()->json(['data' => $data, 'res' => 0]);
  }

  public function showCreateForm(Request $req, QQHelper $qq)
  {
    if (!\Session::has('qqUser')) {
      return redirect('/');
    }
    return view('auth.qq.create', ['user' => \Session::get('qqUser')]);
  }

  protected function validator(array $data)
  {
    return Validator::make($data, $this->rules);
  }

  protected function create(array $data)
  {
    $user = null;
    \DB::transaction(function () use (&$user, $data) {
      $user = User::create([
        'name' => $data['name'],
      ]);
      \DB::insert('INSERT INTO auth (id, id_str, tpe, created_at) VALUES (?, ?, ?, ?)', [$user->getAuthIdentifier(),
        session('qqOpenId'), QQHelper::AUTH_TPE, $user->created_at]);
    });
    return $user;
  }

  private function getContents($url)
  {
    return file_get_contents($url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_name' => false]]));
  }

  public function newQqUserOptions(Request $req, QQHelper $qq)
  {
    return response('', 200, [
      'Access-Control-Allow-Origin' => 'http://localhost:4200',
      'Access-Control-Allow-Credentials' => 'true',
      'Access-Control-Allow-Methods' => 'POST',
      'Access-Control-Allow-Headers' => 'content-type'
    ]);
  }

  public function register(Request $request)
  {
    $this->validate($request, $this->rules);
    return $this->oRegister($request);
  }

  protected function registered(Request $request, $user)
  {
    return response()->json(['res' => 0]);
  }
}

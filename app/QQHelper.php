<?php

namespace App;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class QQHelper
{
  const AUTH_TPE = 1;

  private $appId = 0;
  private $appKey = "";
  private $appUrl = "";

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->appId = env("QQ_APP_ID");
    $this->appKey = env("QQ_APP_KEY");
    $this->appUrl = env("APP_URL");
  }

  public function authUrl()
  {
    if ($this->appId === null) {
      return null;
    } else {
      $loginUrl = urlencode($this->appUrl . "/auth/qq");
      return "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id={$this->appId}&redirect_uri=$loginUrl&scope=get_simple_userinfo";
    }
  }

  public function tokenUrl($code)
  {
    $loginUrl = urlencode($this->appUrl . "/auth/qq");
    return "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&" .
      "client_id={$this->appId}&client_secret={$this->appKey}&code=$code&redirect_uri=$loginUrl";
  }

  public function me($token)
  {
    return "https://graph.qq.com/oauth2.0/me?access_token=$token";
  }

  public function getUserInfo($token, $openId)
  {
    return "https://graph.qq.com/user/get_user_info?access_token=$token&oauth_consumer_key={$this->appId}&openid=$openId";
  }
}

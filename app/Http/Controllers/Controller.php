<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  protected function formatValidationErrors(Validator $validator)
  {
    $arr = $validator->errors()->all();
    $arr = array_values($arr);
    $ret = [];
    $ret['res'] = -1;
    $ret['msg'] = $arr;
    return $ret;
  }

  protected function buildFailedValidationResponse(Request $request, array $errors)
  {
    return new JsonResponse($errors);
  }
}

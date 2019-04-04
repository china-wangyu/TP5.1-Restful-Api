<?php
/**
 * Created by User: wene<china_wangyu@aliyun.com> Date: 2019/4/3 Time: 17:34
 */

namespace app\api\controller;

use think\Request;
use think\restful\{
    ApiAuthorization,ApiReponse
};
class Token
{

    public function create(Request $request)
    {
        $param = $request->param();
        if(empty($param['userName']) or empty($param['userLoginKey'])){
           return ApiReponse::json(404,'参数userName/userLoginKey不能为空~');
        }
        $token = $tokenTemplate = config('api.API_AUTHORIZATION_TOKEN');
        $token['iat'] = time();
        $token['nbf'] = $token['iat']  + 10;
        $token['exp'] = $token['iat'] + 600;
        $token['data'] = ['userName'=>$param['userName'],'userLoginKey'=>$param['userLoginKey']];
        $jwt = ApiAuthorization::encode($token,config('api.API_AUTHORIZATION_KEY'));
        return ApiReponse::json(200,'操作成功~',[
            'jwt'=>$jwt,
            'tt'=>  $token['iat'],
            'exp' => $token['exp'],
            'signature' => md5(join(',',$token['data']))
        ]);
    }

    /**
     * 刷新时长
     * @return array
     */
    public function reset(){
        $param = request()->param();
        if(empty($param['jwt']))return ApiReponse::json(404,'参数jwt不能为空~');
        $jwtArr = ApiAuthorization::reset($jwt,config('api.API_AUTHORIZATION_KEY'));
        return ApiReponse::json(200,'操作成功~',[
            'jwt'=> $jwtArr['jwt'],
            'tt'=>  $jwtArr['jwt']['iat'],
            'exp' => $jwtArr['jwt']['exp'],
            'signature' => md5(join(',',$jwtArr['token']['data']))
        ]);
    }
}
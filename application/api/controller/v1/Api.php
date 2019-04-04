<?php
/**
 * Created by User: wene<china_wangyu@aliyun.com> Date: 2019/4/3 Time: 16:36
 */

namespace app\api\controller\v1;

use think\restful\ApiAuthorization;

/**
 * Class Api API基类
 * @package app\api\controller\v1
 */
class Api extends \think\restful\Api
{
    protected function handle()
    {
        if (config('api.API_AUTHORIZATION')){
            // 开启JWT验证,执行业务代码
            if(!isset($this->param['jwt']) or !isset($this->param['signature'])) {
                // 没有jwt参数 或 signature 签名
                $this->error('400 缺少API授权信息~');
            }
            $jwtArr = ApiAuthorization::decode($this->param['jwt'],config('api.API_AUTHORIZATION_KEY'));
            $userJwtSignature = md5(join(',',$jwtArr['data']));
            if ($userJwtSignature !== $this->param['signature']) {
                $this->error('400 API授权信息错误~');
            }
        }
    }
}
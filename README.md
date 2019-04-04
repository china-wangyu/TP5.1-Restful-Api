# TP5.1 Restful  Api

#### 介绍
PHP7.2 + TP5.1  + Restful  Api  ，构建的API项目架构，支持API文档输出、API接口自检、开启API JWT模式、反射路由模式、API参数自检等功能

#### 软件架构
软件架构说明
```text
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─api        接口模块目录（可以更改，但不建议：很麻烦，模块里面不建议写模型和视图）
│  │  ├─common.php      模块函数文件(接口函数推荐写在这里)
│  │  ├─controller      控制器目录
│  │  │  ├─v1          接口版本模块
│  │  │  ├─v...          接口版本模块
│  ├─common             公共模块目录（可以更改）
│  │
│  ├─command.php        命令行定义文件
│  ├─common.php         公共函数文件
│  └─provider.php       应用容器绑定定义
│  └─tags.php           应用行为扩展定义文件
│
├─config                应用配置目录
│  ├─api               模块配置目录
│  │  ├─app.php       应用配置
│  │
│  ├─api.php            接口配置
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─log.php            日志配置
│  ├─session.php        Session配置
│  ├─template.php       模板引擎配置
│  └─trace.php          Trace配置
│
├─route                 路由定义目录
│  ├─route.php          路由定义
│  └─...                更多
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
```

#### 安装教程


1. 克隆本项目代码

```bash
git clone git@gitee.com:china_wangyu/TP5.1-Restful-Api.git
```

2. 进入项目工程

```bash
cd TP5.1-Restful-Api
```

3. 使用`composer`更新项目扩展,提升项目安全性、可用性

> 推荐使用`composer`中国镜像源，具体操作见【[文档](https://learnku.com/laravel/composer)】

```bash
composer update
```

#### 使用说明

##### 必须配置以下内容

1. 配置`api.php` 与`app.php`

`api.php` 目录在 `{项目}/config/api.php`

`app.php` 目录在 `{项目}/config/api/app.php`

2. 配置`route.php`

`route.php` 目录在 `{项目}/route/api.php`

> 如果不修改模块，请直接使用默认配置

##### 开启JWT模式 （可选）

1. 配置`api.php`

   ```php
   // 是否开启授权验证
   'API_AUTHORIZATION' => true,
    ```
    
2. 修改`Api.php`项目基类(不建议修改)

    ```php
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
    ```
 3. 修改`token.php`(不建议修改)
 
    ```php
    <?php
    /**
     * Created by User: wene<china_wangyu@aliyun.com> Date: 2019/4/3 Time: 17:34
     */
    
    namespace app\api\controller;
    
    use think\Request;
    use think\restful\ApiAuthorization;
    use think\restful\ApiReponse;
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
    ```
    
#### 参与贡献

1. Fork 本仓库
2. 新建 Feat_xxx 分支
3. 提交代码
4. 新建 Pull Request


#### 项目自评

本扩展或者说是一个TP5.1+PHP7.2的后端项目API架构，
主要是帮助刚刚入行或者快速建站的朋友们，进行项目快速迭代开发，
把接口授权、接口验证、参数校验、接口文档输出、接口自验包裹封装起来，
只为大家用的安心。

#### 帮助作者

项目开发或者扩展开发，都需要不断地编码尝试与线上环境验证。
所需的资源和时间都是有成本的，如果项目帮助到您了，
如果您有心帮助作者,请点击下方的捐赠按钮
    
#### 参与贡献

1. Fork 本仓库
2. 新建 ts_{用户名} 分支
3. 提交代码
4. 新建 Pull Request


#### 联系作者

 - 如有疑问，请联系邮箱 china_wangyu@aliyun.com

 - 请联系QQ 354007048 / 354937820
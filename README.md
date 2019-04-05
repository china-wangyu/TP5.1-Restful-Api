# TP5.1 Restful  Api
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)

#### 介绍
PHP7.2 + TP5.1  + Restful  Api  ，构建的API项目架构，支持API文档输出、API接口自检、开启API JWT模式、反射路由模式、API参数自检等功能

为了本项目拥有更加直白与客观的简易性、阅读性、实用性，所用的扩展和第三方代码，均未考虑高度抽象和深度封装，各位大大可以很简单的看懂源码和框架设计。

如果有需要或涉及到高并发的服务架构，可以在issues提出，或者留言也行，我将参考大家的意愿，出一个版本或demo。


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

##### API接口编码模板

- `auth`文件举例

    > 代码模板

    ```php
    <?php

    namespace app\api\controller\v1;

    /**
     * Class Auth Auth授权类
     * @package app\api\controller\v1
     */
    class Auth extends Api
    {

        /**
        * @doc 获取服务器授权1
        * @route /api/v1/auth get
        * @param string $appSecret 授权字符 require|alphaNum 1
        * @param string $appSec2t 授权字符1 require|alphaNum 1
        * @param string $appId 开发者ID
        * @success {"code":400,"msg":"appSecret不能为空","data":[]}
        * @error {"code":400,"msg":"appSecret不能为空","data":[]}
        */
        public function read()
        {
            return $this->success('成功~');
        }
    }
    ```

    > 接口类注释说明

    ```php
    /**
     * Class Auth Auth授权类
     * @package app\api\controller\v1
     */
    class Auth extends Api
    ```

    > 接口方法注释

    ```php
    /**
     * @doc 获取服务器授权1
     * @route /api/v1/auth get
     * @param string $appSecret 授权字符 require|alphaNum 1
     * @param string $appSec2t 授权字符1 require|alphaNum 1
     * @param string $appId 开发者ID
     * @success {"code":400,"msg":"appSecret不能为空","data":[]}
     * @error {"code":400,"msg":"appSecret不能为空","data":[]}
     */
    public function read()
    {
        return $this->success('成功~');
    }
    ```

    > 接口方法注释参数说明

    | @ 名称 | 参数1注解 | 参数2注解 | 参数3注解 |
    | :----: | :----: | :----: | :----: |
    | doc  | API接口文档 | |  |
    | route  | API路由规则 | 请求类型 |  |
    | param  | api参数 | 验证规则 | 默认值 |
    | success  | API请求成功返回json示例 |  |  |
    | error  | API请求失败返回json示例 |  |  |

##### 接受接口请求数据

- url 请求样例

```bash
http://127.0.0.1:8000/api/v1/auth?appSecret=12&appSec2t=12
```

- 代码样例

```php
public function read()
{
    # $this->param 就是接口请求数据，包含请求版本号，请求接口类名称
    return $this->success('成功~',$this->param);
}

```

- 返回样例

```json
{
"responseCode": 200,
"responseMsg": "成功~",
"responseData": {
    "appSecret": "12",
    "appSec2t": "12",
    "version": "v1",
    "controller": "auth"
    }
}
```

##### 返回`json`数据

> 注意本函数与 `TP` 内置 `think\Controller` 的 `success\errror`同名

- 状态为`200`的 样例

    ```php
    public function read()
    {
        return $this->success('成功~');
    }
    ```

    ```json
    {
    "responseCode": 200,
    "responseMsg": "成功~",
    "responseData": {
        "appSecret": "12",
        "appSec2t": "12",
        "version": "v1",
        "controller": "auth"
        }
    }
    ```

- 状态为`400`的 样例

    ```php
    public function read()
    {
        return $this->error('成功~');
    }
    ```

    ```json
    {
    "responseCode": 400,
    "responseMsg": "参数错误：appSecret不能为空",
    "responseData": []
    }
    ```

- `success/error`函数参数说明

    | 参数名称 | 注解 | 类型 | 默认值 |
    | :----: | :----: | :----: | :----: |
    | msg  | 接口调用提示 | |  |
    | data  | 返回数据 | 请求类型 |  |




##### 输出API文档

1. 打开`cmd/ssh`工具

2. 进入`项目目录`

3. 执行命令

    ```bash
    wy@DESKTOP-2G0M7DJ MINGW64 ~/Desktop/TP5.1-Restful-Api (master)
    $ php think API -M 1
    |- --------------------------------------------------------------创建API文档 START-------------------------------------------------------------- -|
    |- 创建的 API 文档路径：C:\Users\wy\Desktop\TP5.1-Restful-Api\/API接口文档2019-04-05 10.md -|
    |- --------------------------------------------------------------创建API文档 END-------------------------------------------------------------- -|
    ```
##### 自检API接口

1. 打开`cmd/ssh`工具

2. 进入`项目目录`

3. 开启接口服务

    - 使用PHP内置服务器
    ~~~bash
    php -S {IP地址}:{端口} -t {项目目录}/public/
    ~~~
    - TP5 启动服务
    ```bash
    > php think run -H {IP地址} -P {端口}
    
    ThinkPHP Development server is started On <http://127.0.0.1:8000/>
    You can exit with `CTRL-C`
    Document root is: E:\VirtualBox\vms\CICD\labs\tp5restfulapi_architecture\public
    ```
4. 然后配置项目 `api.php` 配置文件的参数 `（可选）`

~~~php
'API_HOST'=> 'http://127.0.0.1:8000',# 设置API网址
~~~
> 如果没有配置这个，请在执行的时候加上-H指定网址

5. 输入自检命令 **``php think API -C 1``**

    - 基本命令

    ```bash
    wy@DESKTOP-2G0M7DJ MINGW64 ~/Desktop/TP5.1-Restful-Api (master)
    $ php think API -C 1
    |- --------------------------------------------------------------检验API START-------------------------------------------------------------- -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read ,响应结果：{正常} 返回结果：{"responseCode":200,"responseMsg":"成功~","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read1 ,响应结果：{正常} 返回结果：{"responseCode":200,"responseMsg":"token","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read2 ,响应结果：{正常} 返回结果：{"responseCode":404,"responseMsg":"sss","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read3 ,响应结果：{正常} 返回结果：{"responseCode":404,"responseMsg":"sss","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\AuthEsac ,正在检验的函数为：read ,响应结果：{正常} 返回结果：{"responseCode":400,"responseMsg":"\u53c2\u6570\u9519\u8bef\uff1aappSecret\u4e0d\u80fd\u4
    e3a\u7a7a","responseData":[]} -|
    |- --------------------------------------------------------------检验API END-------------------------------------------------------------- -|
    ```

    - 指定网址 **`php think API -C 1 -H`**

    ```bash
    wy@DESKTOP-2G0M7DJ MINGW64 ~/Desktop/TP5.1-Restful-Api (master)
    $ php think API -C 1 -H http://127.0.0.1:8000/
    |- --------------------------------------------------------------检验API START-------------------------------------------------------------- -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read ,响应结果：{正常} 返回结果：{"responseCode":200,"responseMsg":"成功~","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read1 ,响应结果：{正常} 返回结果：{"responseCode":200,"responseMsg":"token","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read2 ,响应结果：{正常} 返回结果：{"responseCode":404,"responseMsg":"sss","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\Auth ,正在检验的函数为：read3 ,响应结果：{正常} 返回结果：{"responseCode":404,"responseMsg":"sss","responseData":[]} -|
    |- 正在检验 API 类：app\api\controller\v1\AuthEsac ,正在检验的函数为：read ,响应结果：{正常} 返回结果：{"responseCode":400,"responseMsg":"\u53c2\u6570\u9519\u8bef\uff1aappSecret\u4e0d\u80fd\u4
    e3a\u7a7a","responseData":[]} -|
    |- --------------------------------------------------------------检验API END-------------------------------------------------------------- -|
    ```

    - 如果您在`cmd/ssh 工具`感觉看着不是很方便，请在日志文件查看输出

    文件地址：

    ```bash
    {项目目录}/API Command Logs{年-月-日 时}.md
    ```



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
## 时间管理

一款用来记录自己做过、正在做的事情和计划将要做的事情的应用。这是后端部分，使用 Laravel 5.3 开发，还有一个[前端部分的代码库](../../../TimeManage-ng2)（使用 Angular 2 开发）。

此部分代码可以独立运行，`public`已经包含了编译好的前端代码。

## 本地运行

1. 关于 Laravel 的安装，请自行查看 https://laravel.com/docs/5.3/installation
1. 使用你熟悉的工具 clone 本仓库
1. 进入 clone 的仓库本地目录，运行`composer install`
1. 成功后，复制一份`.env.example`，改名为`.env`，*nix下操作为`cp .env.example .env`
1. 产生 Laravel APP KEY，`php artisan key:generate`，用来加密 SESSION 数据和 Cookies 等，具体功能参见 Laravel 文档
1. 准备数据库，根据你自己的情况在`.env`中配置数据库连接，然后运行`php artisan migrate`即可创建数据库结构以及初始化必要数据。
1. 运行`php artisan serve --host=0.0.0.0`，用浏览器访问`localhost:8000`查看本地测试服务器

**`.env`中的配置修改需要重启服务器，其他代码修改可以直接刷新测试。**

## 开发

1. 生成 IDE 帮助文件，具体用法参见：https://github.com/barryvdh/laravel-ide-helper
```
 php artisan ide-helper:generate
 php artisan ide-helper:meta
```
2. 使用 PhpStorm`File -> Open Directory...`，选中仓库目录打开

## 第三方登录配置

目前支持以下登录方式： QQ 登录

#### QQ 登录
1. 修改`.env`里面的如下配置项，使用你自己的 AppId 和 AppKey，并且配置 APP_URL 使用你在 QQ 申请权限时提供的域名或者子域名均可。
```
APP_URL=http://test.web.com:8000
QQ_APP_ID=
QQ_APP_KEY=
```
2. （可选操作）如果在本地测试，则需要修改`hosts`文件，将测试域名指向本地地址。
3. 如果服务器已经启动，`CTRL+C`关掉，然后重新打开。

## License

The project is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

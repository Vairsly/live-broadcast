<?php

namespace App\HttpController\Admin;

use App\Base\BaseController;
use App\Model\AdminAuth as AuthModel;
use App\Model\AdminRole as RoleModel;
use App\Model\AdminLoginLog as LoginLogModel;
use App\Utility\Message\Status;
use EasySwoole\EasySwoole\Config;
use EasySwoole\VerifyCode\Conf;
use easySwoole\Cache\Cache;

class Login extends BaseController
{
    public function index()
    {
        $isDebug = Config::getInstance()->getConf('DEBUG');

        $this->render('admin.login', [
            'debug' => $isDebug
        ]);
    }

    public function login()
    {
        $request = $this->request();
        $data    = $request->getRequestParam('uname', 'pwd', 'verify');
        $encry = Config::getInstance()->getConf('app.verify_encry');

        if (Config::getInstance()->getConf('DEBUG') && md5($encry . strtoupper($data['verify']) . $encry) != $this->request()->getCookieParams('v-idea')) {
            $this->writeJson(Status::CODE_VERIFY_ERR, '验证码有误');
            LoginLogModel::getInstance()->add($data['uname']);
            return;
        }

        unset($data['verify']);

        $bool = AuthModel::getInstance()->login($data['uname'], $data['pwd']);

        if ($bool) {
            $time  = time();
            $id    = $bool['id'];
            $token = md5($id . Config::getInstance()->getConf('app.token') . $time);

            $this->response()->setCookie('id', $id);
            $this->response()->setCookie('time', $time);
            $this->response()->setCookie('token', $token);
            $this->writeJson(Status::CODE_OK, '登录成功');
            LoginLogModel::getInstance()->add($data['uname'], 1);
            AuthModel::getInstance()->setLoginedTime($id);
            if(!Cache::has('role_' . $bool['role_id'])) {
                var_dump(Cache::get('role_' . $bool['role_id']));
                RoleModel::getInstance()->cacheRules($bool['role_id']);
            }
        } else {
            $this->writeJson(Status::CODE_ERR, '用户或密码错误');
            LoginLogModel::getInstance()->add($data['uname']);
        }
        return;
    }

    public function logout()
    {
        $this->response()->setCookie('token', '');
        $this->response()->redirect("/login");
        return;
    }

    public function verify()
    {
        $config = new Conf(['backColor' => [243, 243, 243]]);
        $code   = new \EasySwoole\VerifyCode\VerifyCode($config);
        $this->response()->withHeader('Content-Type', 'image/png');
        $drawcode = $code->DrawCode();
        $this->response()->write($drawcode->getImageByte());
        $verify = strtoupper($drawcode->getImageCode());

        $encry = Config::getInstance()->getConf('app.verify_encry');
        $this->response()->setCookie('v-idea', md5($encry . $verify . $encry));
    }
}

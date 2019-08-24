<?php


namespace app\api\middleware;


use app\api\facade\MemberTokenFacade;
use think\Db;
use think\Request;

class AuthMiddleware
{
    /**
     * @param $request Request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token');
        if(empty($token)){
            $token = $request->param('token');
        }
        $request->isLogin=false;
        if(!empty($token)){
            $request->token = $token;
            $token=MemberTokenFacade::findToken($request->token);
            
            $errorno=ERROR_TOKEN_INVAILD;
            if(!empty($token)) {
                if($token['update_time']+$token['expire_in']>time()){
                    $request->user = Db::name('Member')->find($token['member_id']);
                }else{
                    $errorno=ERROR_TOKEN_EXPIRE;
                }
            }
        
            if(!empty($request->user)) {
                $request->isLogin=true;
            }else{
                $request->auth_error=$errorno;
            }
        }
        return $next($request);
    }
}
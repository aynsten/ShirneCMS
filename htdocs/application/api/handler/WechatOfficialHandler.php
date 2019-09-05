<?php


namespace app\api\handler;


use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Raw;
use think\facade\Log;

class WechatOfficialHandler extends BaseHandler implements EventHandlerInterface
{
    /**
     * @param mixed $message
     */
    public function handle($message = null)
    {
        Log::record('接收到消息:'.var_export($message,true),'Wechat');
        switch ($message['MsgType']) {
            case 'event':
                switch ($message['event']){
                    case 'subscribe':
                        $userinfo=$this->app->user->get($message['FromUserName']);
                        if(!empty($message['EventKey'])){
                            $this->onSubscribe($message, $userinfo);
                            $scene_id=substr($message['EventKey'],8);
                            return $this->onScan($message,$scene_id);
                        }else {
                            return $this->onSubscribe($message, $userinfo);
                        }
                        break;
                    case 'SCAN':
                        return $this->onScan($message,$message['EventKey']);
                        break;
                    case 'LOCATION':
                        return $this->onLocation($message);
                        break;
                    case 'CLICK':
                        return $this->onClick($message);
                        break;
                    case 'VIEW':
                        return $this->onView($message);
                        break;
                    case 'TEMPLATESENDJOBFINISH':
                        $this->updateTplMsg($message);
                        break;
                    default:
                        return '收到事件消息';
                }
                break;
            case 'text':
                return $this->getTypeReply('keyword',$message['Content']);
                break;
            case 'image':
                return '收到图片消息';
                break;
            case 'voice':
                return '收到语音消息';
                break;
            case 'video':
                return '收到视频消息';
                break;
            case 'location':
                return '收到坐标消息';
                break;
            case 'link':
                return '收到链接消息';
                break;
            case 'file':
                return '收到文件消息';
            // ... 其它消息
            default:
                return '收到其它消息';
                break;
        }
    
        return new Raw('');
    }
}
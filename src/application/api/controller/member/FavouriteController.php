<?php

namespace app\api\controller\member;


use app\api\controller\AuthedController;
use app\common\model\MemberFavouriteModel;
use think\Db;

class FavouriteController extends AuthedController
{
    public function index($type){
        $model=new MemberFavouriteModel();
        $this->response($model->getFavourites($type));
    }

    public function add($type,$id){
        $model=new MemberFavouriteModel();
        if($model->addFavourite($this->user['id'],$type,$id)){
            $this->success('已添加收藏');
        }else{
            $this->error($model->getError());
        }
    }

    public function remove($type,$ids){
        $model=Db::name('memberFavourite')
        ->where('member_id',$this->user['id']);
        if(empty($type)){
            $model->whereIn('id',idArr($ids));
        }else{
            $model->where('fav_type',$type)
            ->whereIn('fav_id',idArr($ids));
        }
        $model->delete();
        $this->success('已移除收藏');
    }
}
<?php

namespace app\index\controller;


use think\Db;

class PageController extends BaseController{

    public function index($name='',$group='')
    {
        if(!empty($name)) {
            $page = Db::name('page')->where(array('id|name' => $name))->find();
            if (empty($page)) $this->error('页面不存在');
            $group=$page['group'];
        }elseif(empty($group)){
            $this->error('页面不存在');
        }

        $model=Db::name('page');
        $groupset=null;
        if(!empty($group)){
            $model->where('group',$group);
            $groupset=Db::name('PageGroup')->where('group',$group)->find();
        }
        $lists=$model->field('id,name,group,icon,title')->order('sort ASC,id ASC')->select();
        if(empty($lists))$this->error('页面不存在');
        if(empty($page)){
            $page=Db::name('page')->where(array('id|name' => $lists[0]['name']))->find();;
        }

        $this->seo($page['title']);
        $this->assign('page',$page);
        $this->assign('group',$groupset);
        $this->assign('lists',$lists);
        return $this->fetch();
    }

    public function __call($method,$args){
        $this->index($method);
    }
}

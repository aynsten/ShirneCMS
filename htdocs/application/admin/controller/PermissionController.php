<?php
/**
 * Created by IntelliJ IDEA.
 * User: shirne
 * Date: 2017/5/8
 * Time: 7:56
 */

namespace app\admin\controller;


use app\index\validate\PermissionValidate;
use think\Db;

class PermissionController extends BaseController
{
    /**
     * 权限列表
     */
    public function index()
    {
        $lists=getMenus();
        $this->assign('model', $lists);
        return $this->fetch();
    }

    public function clearcache(){
        cache('menus',null);
        $this->success("清除成功", url('permission/index'));
    }

    public function add($pid){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $validate = new PermissionValidate();
            $validate->setId();

            if (!$validate->check($data)) {
                $this->error($validate->getError());
            } else {
                if (Db::name('Permission')->insert($data)) {
                    $this->success("添加成功", url('permission/index'));
                } else {
                    $this->error("添加失败");
                }
            }
        }
        $model=array('pid'=>$pid);
        $this->assign('perm',$model);
        return $this->fetch('edit');
    }

    /**
     * 添加权限
     */
    public function edit($id=0)
    {
        $id = intval($id);
        if ($this->request->isPost()) {
            $data=$this->request->post();
            $validate=new PermissionValidate();
            $validate->setId($id);

            if (!$validate->check($data)) {
                $this->error($validate->getError());
            } else {
                $data['id']=$id;
                if (Db::name('Permission')->update()) {
                    $this->success("更新成功", url('permission/index'));
                } else {
                    $this->error("更新失败");
                }

            }
        }
            $model = Db::name('permission')->where(["id" => $id])->find();
        if(empty($model)){
            $this->error('要编辑的项不存在');
        }
        $this->assign('perm',$model);
        return $this->fetch();
    }
    /**
     * 删除权限
     * @param $id int|string
     */
    public function delete($id)
    {
        $id = intval($id);
        $model = Db::name('Permission');
        $result = $model->where(["id"=>$id])->delete();
        if($result){
            $this->success("删除成功", url('permission/index'));
        }else{
            $this->error("删除失败");
        }
    }
}
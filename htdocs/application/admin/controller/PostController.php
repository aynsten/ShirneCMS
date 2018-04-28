<?php
namespace app\admin\controller;
/**
 * 文章管理
 */
class PostController extends BaseController
{
    /**
     * 文章列表
     */
    public function index($key="")
    {
        $model = D('PostView');
        $where=array();
        if(!empty($key)){
            $where['post.title'] = array('like',"%$key%");
            $where['manager.username'] = array('like',"%$key%");
            $where['category.title'] = array('like',"%$key%");
            $where['_logic'] = 'or';
        }

        $this->pagelist($model,$where,'post.id DESC');

        $this->display();     
    }

    /**
     * 更新文章信息
     */
    public function edit($id=0)
    {
        $id = intval($id);

        if (IS_POST) {
            $model = D("Post");
            if (!$model->create()) {
                $this->error($model->getError());
            }else{
                $uploaded=$this->upload('post','upload_cover',true);
                if(!empty($uploaded))$model->cover=$uploaded['url'];
                if($id>0) {
                    if ($model->save()) {
                        user_log($this->mid, 'updatepost', 1, '修改文章 ' . $id, 'manager');
                        $this->success("编辑成功", U('post/index'));
                    } else {
                        $this->error("编辑失败");
                    }
                }else{
                    $model->time = time();
                    $model->user_id = $this->mid;
                    if ($model->add()) {
                        user_log($this->mid,'addpost',1,'添加文章 '.$model->getLastInsID() ,'manager');
                        $this->success("添加成功", U('post/index'));
                    } else {
                        $this->error("添加失败");
                    }
                }
            }
        }else{
            if($id>0) {
                $model = M('post')->find($id);
            }else{
                $model=array('type'=>1);
            }
            $this->assign("category",getSortedCategory(M('category')->select()));
            $this->assign('post',$model);
            $this->assign('id',$id);
            $this->display();
        }
    }
    /**
     * 删除文章
     */
    public function delete($id)
    {
    		$id = intval($id);
        $model = M('post');
        $result = $model->where("id= %d",$id)->delete();
        if($result){
            user_log($this->mid,'deletepost',1,'删除文章 '.$id ,'manager');
            $this->success("删除成功", U('post/index'));
        }else{
            $this->error("删除失败");
        }
    }
	public function push($id) {//post到前台
		$id = intval($id);
        $status = M('post') -> where("id= %d",$id) -> getField('status');
        if ($status === '0') {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        $result = M('post') -> where("id= %d",$id) -> save($data);
        if ($result && $data['status'] === 1) {
            $this -> success("发布成功", U('post/index'));
        } elseif ($result && $data['status'] === 0) {
            $this -> success("撤销成功", U('post/index'));
        } else {
            $this -> error("操作失败");
        }
	}
}

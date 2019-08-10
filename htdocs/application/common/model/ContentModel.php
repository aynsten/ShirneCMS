<?php

namespace app\common\model;

use app\common\facade\CategoryFacade;
use think\Db;
use think\Paginator;

/**
 * Class ContentModel
 * @package app\common\model
 */
class ContentModel extends BaseModel
{
    protected $model;
    protected $cateModel;
    protected $searchFields='title';
    protected $hiddenFields='content';
    private $transedSearchFields='';
    protected $defaultOrder='id DESC';

    /**
     * @var $cateFacade CategoryModel
     */
    protected $cateFacade;

    protected function tagBase($hidden=null)
    {
        $this->model=ucfirst($this->name);
        $this->cateModel=($this->model=='Article'?'':$this->model).'Category';
        if(is_null($hidden )){
            $hidden = $this->hiddenFields;
        }
        $fields = '*';
        if(!empty($hidden)){
            $fields = $this->getTableFields();
            if(!empty($fields)){
                $hiddens = explode(',',$hidden);
                $fields = array_diff($fields,$hiddens);
            }
        }
        return Db::view($this->model,$fields)
            ->view($this->cateModel,
                ["title"=>"category_title","name"=>"category_name","short"=>"category_short","icon"=>"category_icon","image"=>"category_image"],
                $this->model.".cate_id=".$this->cateModel.".id",
                "LEFT"
            )
            ->where($this->model.".status",1);
    }
    
    protected function analysisType($list, $islist=true){
        if($this->type){
            if($islist) {
                if($list instanceof Paginator){
                    $list->each(function($item){
                        return $this->analysisType($item, false);
                    });
                }else {
                    foreach ($list as $k => $item) {
                        $list[$k] = $this->analysisType($item, false);
                    }
                }
            }else{
                foreach ($this->type as $f => $type) {
                    $list[$f] = $this->readTransform($list[$f],$type);
                }
            }
        }
        return $list;
    }
    
    protected function filterOrder($order){
        if(strpos($order,'(')!==false){
            $order = str_replace(str_split('()+-/*@#%!`~'),'',$order);
        }
        if(strpos($order,'__RAND__')!==false){
            $order = str_replace('__RAND__','rand()',$order);
        }
        return $order;
    }
    
    protected function getSearchFields(){
        if($this->transedSearchFields)return $this->transedSearchFields;
        $fields = explode('|',$this->searchFields);
        foreach ($fields as $k=>$field){
            if(strpos($field,'.')===false){
                $fields[$k] = $this->model.'.'.$field;
            }
        }
        $this->transedSearchFields = implode('|',$fields);
        return $this->transedSearchFields;
    }
    
    /**
     * @param $attrs
     * @return array|Paginator
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tagList($attrs, $filter=false)
    {
        $model=$this->tagBase($attrs['hidden']);
        if(!empty($attrs['category'])){
            $cate_id=$attrs['category'];
            if(!is_int($cate_id)){
                $cate_id=$this->cateFacade->getCategoryId($cate_id);
            }
            if(isset($attrs['recursive']) && $attrs['recursive']){
                $model->whereIn($this->model.".cate_id", $this->cateFacade->getSubCateIds($cate_id));
            }else{
                $model->where($this->model.".cate_id",$cate_id);
            }
        }
        if(!empty($attrs['keyword'])){
            $model->whereLike($this->getSearchFields(),"%{$attrs['keyword']}%");
        }
        if(!empty($attrs['brand'])){
            if(strpos($attrs['brand'],',')>0){
                $model->whereIn($this->model . ".brand_id", idArr($attrs['brand']));
            }else {
                $model->where($this->model . ".brand_id", intval($attrs['brand']));
            }
        }
        if(!empty($attrs['type'])){
            if(strpos($attrs['type'],',')!==false){
                $types=array_filter(array_map('trim',explode(',',$attrs['type'])));
                if(!empty($types))$model->whereIn($this->model.".type",$types);
            }else{
                $model->where($this->model.".type",$attrs['type']);
            }
        }
        if(!empty($attrs['cover'])){
            $model->where($this->model.".cover","<>","");
        }
        if(!empty($attrs['image'])){
            $model->where($this->model.".image","<>","");
        }

        if(empty($attrs['order'])){
            $attrs['order']=$this->model.'.'.$this->defaultOrder;
        }else {
            if($filter){
                $attrs['order']=$this->filterOrder($attrs['order']);
            }
            if (strpos($attrs['order'], '(') !== false) {
                $attrs['order'] = Db::raw($attrs['order']);
            } elseif (strpos($attrs['order'], '.') === false) {
                $attrs['order'] = $this->model . '.' . $attrs['order'];
            }
        }
        $model->order($attrs['order']);
        
        if($attrs['page']){
            $page = max(1,intval($attrs['page']));
            $pagesize = isset($attrs['pagesize'])?intval($attrs['pagesize']):10;
            if($pagesize<1)$pagesize=1;
            $list = $model->paginate($pagesize,false,['page'=>$page]);
        }else {
            if (empty($attrs['limit'])) {
                $attrs['limit'] = 10;
            }
            $model->limit($attrs['limit']);
    
            $list = $model->select();
        }
        
        return $this->analysisType($list);
    }

    public function tagRelation($attrs, $filter=false)
    {
        $model=$this->tagBase($attrs['hidden']);
        if(!empty($attrs['category'])){
            $cate_id=$attrs['category'];
            if(!is_int($cate_id)){
                $cate_id=$this->cateFacade->getCategoryId($cate_id);
            }

            //默认递归分类
            if(isset($attrs['recursive']) && $attrs['recursive']===false){
                $model->where($this->model.".cate_id",$cate_id);
            }else{
                $model->where($this->model.".cate_id", "IN", $this->cateFacade->getSubCateIds($cate_id));
            }
        }
        if(!empty($attrs['id'])){
            $model->where($this->model.".id", "NEQ",  $attrs['id'] );
        }
        if(empty($attrs['order'])){
            $attrs['order']=$this->model.'.'.$this->defaultOrder;
        }else {
            if($filter){
                $attrs['order']=$this->filterOrder($attrs['order']);
            }
            if (strpos($attrs['order'], '(') !== false) {
                $attrs['order'] = Db::raw($attrs['order']);
            } elseif (strpos($attrs['order'], '.') === false) {
                $attrs['order'] = $this->model . '.' . $attrs['order'];
            }
        }
        $model->order($attrs['order']);

        if(empty($attrs['limit'])){
            $attrs['limit']=10;
        }
        $model->limit($attrs['limit']);
    
        $list = $model->select();
    
        return $this->analysisType($list);
    }

    public function tagPrev($attrs)
    {
        $model=$this->tagBase($attrs['hidden']);
        if(!empty($attrs['category'])){
            $cate_id=$attrs['category'];
            if(!is_int($cate_id)){
                $cate_id=$this->cateFacade->getCategoryId($cate_id);
            }

            //默认递归分类
            if(isset($attrs['recursive']) && $attrs['recursive']===false){
                $model->where($this->model.".cate_id",$cate_id);
            }else{
                $model->where($this->model.".cate_id", "IN", $this->cateFacade->getSubCateIds($cate_id));
            }
        }
        if(!empty($attrs['id'])){
            $model->where($this->model.".id", "LT",  $attrs['id'] );
        }

        $model->order($this->model.'.'.$this->getPk().' DESC');

        return $this->analysisType($model->find(),false);
    }

    public function tagNext($attrs)
    {
        $model=$this->tagBase($attrs['hidden']);
        if(!empty($attrs['category'])){
            $cate_id=$attrs['category'];
            if(!is_int($cate_id)){
                $cate_id=$this->cateFacade->getCategoryId($cate_id);
            }

            //默认递归分类
            if(isset($attrs['recursive']) && $attrs['recursive']===false){
                $model->where($this->model.".cate_id",$cate_id);
            }else{
                $model->where($this->model.".cate_id", "IN", $this->cateFacade->getSubCateIds($cate_id));
            }
        }
        if(!empty($attrs['id'])){
            $model->where($this->model.".id", "GT",  $attrs['id'] );
        }

        $model->order($this->model.'.'.$this->getPk().' ASC');

        return $this->analysisType($model->find(),false);
    }
}
<extend name="public:base" />

<block name="body">
<include file="public/bread" menu="product_index" title="商品列表" />
<div id="page-wrapper">

	<div class="row list-header">
		<div class="col-md-6">
			<div class="btn-toolbar list-toolbar" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group btn-group-sm mr-2" role="group" aria-label="check action group">
					<a href="javascript:" class="btn btn-outline-secondary checkall-btn" data-toggle="button" aria-pressed="false">全选</a>
					<a href="javascript:" class="btn btn-outline-secondary checkreverse-btn">反选</a>
				</div>
				<div class="btn-group btn-group-sm mr-2" role="group" aria-label="action button group">
					<a href="javascript:" class="btn btn-outline-secondary action-btn" data-action="publish">发布</a>
					<a href="javascript:" class="btn btn-outline-secondary action-btn" data-action="cancel">撤销</a>
					<a href="javascript:" class="btn btn-outline-secondary action-btn" data-action="delete">删除</a>
				</div>
				<a href="{:url('product/add')}" class="btn btn-outline-primary btn-sm"><i class="ion-md-add"></i> 添加商品</a>
			</div>
		</div>
		<div class="col-md-6">
			<form action="{:url('product/index')}" method="post">
				<div class="form-row">
					<div class="col input-group input-group-sm mr-2">
						<div class="input-group-prepend">
							<span class="input-group-text">分类</span>
						</div>
						<select name="cate_id" class="form-control">
							<option value="0">不限分类</option>
							<foreach name="category" item="v">
								<option value="{$v.id}" {$cate_id == $v['id']?'selected="selected"':""}>{$v.html} {$v.title}</option>
							</foreach>
						</select>
					</div>
					<div class="col input-group input-group-sm">
						<input type="text" class="form-control" name="key" value="{$keyword}" placeholder="搜索标题、作者或分类">
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="submit"><i class="ion-md-search"></i></button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<table class="table table-hover table-striped">
		<thead>
			<tr>
				<th width="50">编号</th>
				<th>图片</th>
				<th>产品名称</th>
				<th>SKU</th>
				<th>发布时间</th>
				<th>分类</th>
				<th>状态</th>
				<th width="160">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<php>$empty=list_empty(8);</php>
			<volist name="lists" id="v" empty="$empty">
				<tr>
					<td><input type="checkbox" name="id" value="{$v.id}" /></td>
					<td>
						<figure class="figure img-view" data-img="{$v.image}" >
							<img src="{$v.image|default='/static/images/nopic.png'}?w=100" class="figure-img img-fluid rounded" alt="image">
						</figure>
					</td>
					<td>
						<if condition="$v['type'] GT 1"><span class="badge badge-warning">{$types[$v['type']]}</span></if>
						<a href="{:url('index/product/view',['id'=>$v['id']])}" target="_blank">{$v.title}</a>
					</td>
					<td>
						<foreach name="v['skus']" item="sku">
							<div class="input-group input-group-sm mb-2">
								<span class="input-group-prepend">
									<span class="input-group-text">{$sku.goods_no}</span>
								</span>
								<span class="form-control">￥{$sku.price}</span>
								<span class="input-group-middle">
									<span class="input-group-text">库存</span>
								</span>
								<span class="form-control">{$sku.storage}</span>
							</div>
						</foreach>
					</td>
					<td>{$v.create_time|showdate}</td>
					<td>{$v.category_title}</td>
					<td data-url="{:url('push')}" data-id="{$v.id}">
						<if condition="$v['status'] EQ 1">
							<span class="chgstatus" data-status="0" title="点击下架">已上架</span>
							<else/>
							<span class="chgstatus off" data-status="1" title="点击上架">已下架</span>
						</if>
					</td>
					<td class="operations">
					<a class="btn btn-outline-primary" title="编辑" href="{:url('product/edit',array('id'=>$v['id']))}"><i class="ion-md-create"></i> </a>
						<a class="btn btn-outline-primary" title="图集" href="{:url('product/imagelist',array('aid'=>$v['id']))}"><i class="ion-md-images"></i> </a>
						<a class="btn btn-outline-primary" title="评论" href="{:url('product/comments',array('aid'=>$v['id']))}"><i class="ion-md-chatboxes"></i> </a>
					<a class="btn btn-outline-danger link-confirm" title="删除" data-confirm="您真的确定要删除吗？\n删除后将不能恢复!" href="{:url('product/delete',array('id'=>$v['id']))}" ><i class="ion-md-trash"></i> </a>
					</td>
				</tr>
			</volist>
		</tbody>
	</table>
	<div class="clearfix"></div>
	{$page|raw}

</div>
</block>
<block name="script">
	<script type="text/javascript">
		(function(w){
			w.actionPublish=function(ids){
				dialog.confirm('确定将选中产品发布到前台？',function() {
				    $.ajax({
						url:'{:url('article/push',['id'=>'__id__','status'=>1])}'.replace('__id__',ids.join(',')),
						type:'GET',
						dataType:'JSON',
						success:function(json){
						    if(json.code==1){
                                dialog.alert(json.msg,function() {
                                    location.reload();
                                });
                            }else{
                                dialog.warning(json.msg);
                            }
                        }
					});
                });
            };
            w.actionCancel=function(ids){
                dialog.confirm('确定取消选中产品的发布状态？',function() {
                    $.ajax({
                        url:'{:url('article/push',['id'=>'__id__','status'=>0])}'.replace('__id__',ids.join(',')),
                        type:'GET',
                        dataType:'JSON',
                        success:function(json){
                            if(json.code==1){
                                dialog.alert(json.msg,function() {
                                    location.reload();
                                });
                            }else{
                                dialog.warning(json.msg);
                            }
                        }
                    });
                });
            };
            w.actionDelete=function(ids){
                dialog.confirm('确定删除选中的产品？',function() {
                    $.ajax({
                        url:'{:url('article/delete',['id'=>'__id__'])}'.replace('__id__',ids.join(',')),
                        type:'GET',
                        dataType:'JSON',
                        success:function(json){
                            if(json.code==1){
                                dialog.alert(json.msg,function() {
                                    location.reload();
                                });
                            }else{
                                dialog.warning(json.msg);
                            }
                        }
                    });
                });
            };
        })(window)
	</script>
</block>
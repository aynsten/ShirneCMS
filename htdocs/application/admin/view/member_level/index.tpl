<extend name="public:base" />

<block name="body">

    <include file="public/bread" menu="member_level_index" title="会员组列表" />

    <div id="page-wrapper">

        <div class="row list-header">
            <div class="col-6">
                <a href="{:url('MemberLevel/add')}" class="btn btn-outline-primary btn-sm"><i class="ion-md-add"></i> 添加等级</a>
            </div>
            <div class="col-6">
            </div>
        </div>
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th width="50">编号</th>
                <th>名称</th>
                <th>排序</th>
                <th>购买价格</th>
                <th width="200">操作</th>
            </tr>
            </thead>
            <tbody>
            <foreach name="lists" item="v">
                <tr>
                    <td>{$v.level_id}</td>
                    <td>{$v.level_name}[{$v.short_name}]<if condition="$v['is_default']"><span class="badge badge-info">默认</span> </if></td>
                    <td>{$v.sort}</td>
                    <td>{$v.level_price}</td>
                    <td>
                        <a class="btn btn-outline-dark btn-sm" href="{:url('memberLevel/update',array('id'=>$v['level_id']))}"><i class="ion-md-create"></i> 编辑</a>
                        <a class="btn btn-outline-dark btn-sm" href="{:url('memberLevel/delete',array('id'=>$v['level_id']))}" onclick="javascript:return del('您真的确定要删除吗？\n\n删除后将不能恢复!');"><i class="ion-md-trash"></i> 删除</a>
                    </td>
                </tr>
            </foreach>
            </tbody>
        </table>
    </div>
</block>
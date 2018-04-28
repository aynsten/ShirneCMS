<extend name="Public:Base" />

<block name="body">
<include file="Public/bread" menu="member_index" section="会员" title="会员管理" />

<div id="page-wrapper">
    <div class="row">
        <div class="col-xs-6">
            <a href="{:U('member/add')}" class="btn btn-success">添加会员</a>
        </div>
        <div class="col-xs-6">
            <form action="{:U('member/index')}" method="post">
                <div class="form-group input-group">
                    <span class="input-group-addon">上级</span>
                    <input type="text" class="form-control" name="referer" placeholder="填写id或会员名" value="{$referer}">
                    <span class="input-group-addon">关键字</span>
                    <input type="text" class="form-control" value="{$keyword}" name="keyword" placeholder="输入用户名或者邮箱关键词搜索">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th width="50">编号</th>
                <th>用户名</th>
                <th>余额/总流水</th>
                <th>总充值/总提现</th>
                <th>手机/邮箱</th>
                <th>推荐人</th>
                <th>注册时间</th>
                <th>上次登陆</th>
                <th>代理</th>
                <th>状态</th>
                <th width="160">操作</th>
            </tr>
        </thead>
        <tbody>
        <foreach name="lists" item="v">
            <tr>
                <td>{$v.id}</td>
                <td>{$v.username}<br/>{$v.realname}</td>
                <td>{$v.money|showmoney}<br />{$v.bet_all|showmoney}</td>
                <td>{$v.charge_all|showmoney}<br />{$v.cash_all|showmoney}</td>
                <td>{$v.mobile}<br />{$v.email}</td>
                <td>
                    <empty name="v.refer_name">
                        -
                        <else/>
                        {$v.refer_name}[{$v.refer_agent}]<br />
                        {$v.refer_realname}
                    </empty>
                </td>
                <td>{$v.create_at|showdate}</td>
                <td>{$v.login_ip}<br />{$v.logintime|showdate}</td>
                <td>
                    <if condition="0">
                    <if condition="$v.type eq 1"> <span class="label label-success">普通会员</span>
                    <elseif condition="$v.type eq 2"/><span class="label label-danger">VIP</span>
                    </if>
                    </if>

                    <if condition="$v.isagent neq 0">
                        <div class="btn-group">
                                <a class="btn btn-default btn-sm" href="{:U('member/cancel_agent',array('id'=>$v['id']))}" style="color:green;" onclick="javascript:return del('取消代理不能更改已注册的用户!!!');"><i class="fa fa-close"></i> 取消代理[{$v.isagent}]</a>
                            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <!--<li><a href="{:U('member/set_agent',array('id'=>$v['id'],'level'=>1))}">设为一级代理</a></li>-->
                                <li><a href="{:U('member/index',array('referer'=>$v['id']))}">查看下线</a></li>
                            </ul>
                        </div>
                    <else/>
                    <a class="btn btn-default btn-sm {$v.refer_agent>2?'disabled':''}" href="{:U('member/set_agent',array('id'=>$v['id']))}" ><i class="fa fa-check"></i> 设置代理[<php> echo $v['refer_agent']+1;</php>]</a>
                    </if>

                </td> 
                <td><if condition="$v.status eq 1">正常<else/><span style="color:red">禁用</span></if></td>
                <td>

                    <a class="btn btn-default btn-sm" href="{:U('member/update',array('id'=>$v['id']))}"><i class="fa fa-edit"></i> 编辑</a>
                    <if condition="$v.status eq 1">
                        <a class="btn btn-default btn-sm" href="{:U('member/delete',array('id'=>$v['id']))}" style="color:red;" onclick="javascript:return del('禁用后用户将不能登陆!\n\n请确认!!!');"><i class="fa fa-close"></i> 禁用</a>
                    <else/>
                        <a class="btn btn-default btn-sm" href="{:U('member/delete',array('id'=>$v['id']))}" style="color:#50AD1E;"><i class="fa fa-check"></i> 启用</a>
                    </if>
                </td>
            </tr>
        </foreach>
        </tbody>
    </table>
    {$page}
</div>

</block>
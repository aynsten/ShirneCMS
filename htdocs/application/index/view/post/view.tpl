<extend name="Public:Base" />

<block name="body">
	<div class="main">
		<div class="container">
			<ol class="breadcrumb">
				<li class="icon"><a href="/">首页</a></li>
				<li><a href="{:U('Post/index')}">资讯中心</a></li>
			</ol>
		</div>

		<div class="container">
			<include file="side" />

			<div class="panel pull-right main_right news_list">
				<div class="panel-body postbody">
					<h1>{$post.title}</h1>
					<div class="info">
						分类:<a href="{:U('Post/index',array('name'=>$category['name']))}">{$category.title}</a>
						&nbsp;&nbsp;
						发表时间:{$post.time|showdate}
					</div>
					<div class="container-fluid">
						{$post.content|html_entity_decode}
					</div>
				</div>
			</div>
		</div>
	</div>
</block>
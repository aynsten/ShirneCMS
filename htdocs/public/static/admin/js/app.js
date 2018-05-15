function del(msg) { 
//    var msg = "您真的确定要删除吗？\n\n删除后将不能恢复!请确认！"; 
    return confirm(msg);
}


Number.prototype.format=function(fix){
    if(fix===undefined)fix=2;
    var num=this.toFixed(fix);
    var z=num.split('.');
    var format=[],f=z[0].split(''),l=f.length;
    for(var i=0;i<l;i++){
        if(i>0 && i % 3==0){
            format.unshift(',');
        }
        format.unshift(f[l-i-1]);
    }
    return format.join('')+(z.length==2?'.'+z[1]:'');
};
String.prototype.compile=function(data,list){

    if(list){
        var temps=[];
        for(var i in data){
            temps.push(this.compile(data[i]));
        }
        return temps.join("\n");
    }else{
        return this.replace(/\{@([\w\d\.]+)(?:\|([\w\d]+)(?:\s*=\s*([\w\d,\s#]+))?)?\}/g,function(all,m1,func,args){

            if(m1.indexOf('.')>0){
                var keys=m1.split('.'),val=data;
                for(var i=0;i<keys.length;i++){
                    if(val[keys[i]]){
                        val=val[keys[i]];
                    }else{
                        val = '';
                        break;
                    }
                }
                return callfunc(val,func,args);
            }else{
                return data[m1]?callfunc(data[m1],func,args,data):'';
            }
        });
    }
};

function callfunc(val,func,args,thisobj){
    if(!args){
        args=[val];
    }else{
        if(typeof args=='string')args=args.split(',');
        var argidx=args.indexOf('###');
        if(argidx>=0){
            args[argidx]=val;
        }else{
            args=[val].concat(args);
        }
    }
    //console.log(args);
    return window[func]?window[func].apply(thisobj,args):val;
}

function iif(v,m1,m2){
    if(v=='0')v=0;
    return v?m1:m2;
}

var dialogTpl='<div class="modal fade" id="{@id}" tabindex="-1" role="dialog" aria-labelledby="{@id}Label" aria-hidden="true">\
    <div class="modal-dialog">\
    <div class="modal-content">\
    <div class="modal-header">\
    <h4 class="modal-title" id="{@id}Label"></h4>\
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>\
    </div>\
    <div class="modal-body">\
    </div>\
    <div class="modal-footer">\
    <nav class="nav nav-fill"></nav>\
    </div>\
    </div>\
    </div>\
    </div>';
var dialogIdx=0;
function Dialog(opts){
    if(!opts)opts={};
    //处理按钮
    if(opts.btns!==undefined) {
        if (typeof(opts.btns) == 'string') {
            opts.btns = [opts.btns];
        }
        var dft=-1;
        for (var i = 0; i < opts.btns.length; i++) {
            if(typeof(opts.btns[i])=='string'){
                opts.btns[i]={'text':opts.btns[i]};
            }
            if(opts.btns[i].isdefault){
                dft=i;
            }
        }
        if(dft<0){
            dft=opts.btns.length-1;
            opts.btns[dft].isdefault=true;
        }

        if(!opts.btns[dft]['type']){
            opts.btns[dft]['type']='primary';
        }
        opts.defaultBtn=dft;
    }

    this.options=$.extend({
        'id':'dlgModal'+dialogIdx++,
        'size':'',
        'btns':[
            {'text':'取消','type':'secondary'},
            {'text':'确定','isdefault':true,'type':'primary'}
        ],
        'defaultBtn':1,
        'onsure':null,
        'onshow':null,
        'onshown':null,
        'onhide':null,
        'onhidden':null
    },opts);

    this.box=$(this.options.id);
}
Dialog.prototype.generBtn=function(opt,idx){
    if(opt['type'])opt['class']='btn-outline-'+opt['type'];
    return '<a href="javascript:" class="nav-item btn '+(opt['class']?opt['class']:'btn-outline-secondary')+'" data-index="'+idx+'">'+opt.text+'</a>';
};
Dialog.prototype.show=function(html,title){
    this.box=$('#'+this.options.id);
    if(!title)title='系统提示';
    if(this.box.length<1) {
        $(document.body).append(dialogTpl.compile({'id': this.options.id}));
        this.box=$('#'+this.options.id);
    }else{
        this.box.unbind();
    }

    //this.box.find('.modal-footer .btn-primary').unbind();
    var self=this;
    Dialog.instance=self;

    //生成按钮
    var btns=[];
    for(var i=0;i<this.options.btns.length;i++){
        btns.push(this.generBtn(this.options.btns[i],i));
    }
    this.box.find('.modal-footer .nav').html(btns.join('\n'));

    var dialog=this.box.find('.modal-dialog');
    dialog.removeClass('modal-sm').removeClass('modal-lg');
    if(this.options.size=='sm') {
        dialog.addClass('modal-sm');
    }else if(this.options.size=='lg') {
        dialog.addClass('modal-lg');
    }
    this.box.find('.modal-title').text(title);

    var body=this.box.find('.modal-body');
    body.html(html);
    this.box.on('hide.bs.modal',function(){
        if(self.options.onhide){
            self.options.onhide(body,self.box);
        }
        Dialog.instance=null;
    });
    this.box.on('hidden.bs.modal',function(){
        if(self.options.onhidden){
            self.options.onhidden(body,self.box);
        }
        self.box.remove();
    });
    this.box.on('show.bs.modal',function(){
        if(self.options.onshow){
            self.options.onshow(body,self.box);
        }
    });
    this.box.on('shown.bs.modal',function(){
        if(self.options.onshown){
            self.options.onshown(body,self.box);
        }
    });
    this.box.find('.modal-footer .btn').click(function(){
        var result=true,idx=$(this).data('index');
        if(self.options.btns[idx]['click']){
            result = self.options.btns[idx]['click'].apply(this,[body, self.box]);
        }
        if(idx==self.options.defaultBtn) {
            if (self.options.onsure) {
                result = self.options.onsure.apply(this,[body, self.box]);
            }
        }
        if(result!==false){
            self.box.modal('hide');
        }
    });
    this.box.modal('show');
    return this;
};
Dialog.prototype.hide=function(){
    this.box.modal('hide');
    return this;
};

var dialog={
    alert:function(message,callback,title){
        var called=false;
        return new Dialog({
            btns:'确定',
            onsure:function(){
                if(typeof callback=='function'){
                    called=true;
                    return callback(true);
                }
            },
            onhide:function(){
                if(!called && typeof callback=='function'){
                    callback(false);
                }
            }
        }).show(message,title);
    },
    confirm:function(message,confirm,cancel){
        var called=false;
        return new Dialog({
            'onsure':function(){
                if(typeof confirm=='function'){
                    called=true;
                    return confirm();
                }
            },
            'onhide':function () {
                if(called=false && typeof cancel=='function'){
                    return cancel();
                }
            }
        }).show(message);
    },
    prompt:function(message,callback,cancel){
        var called=false;
        return new Dialog({
            'onshown':function(body){
                body.find('[name=confirm_input]').focus();
            },
            'onsure':function(body){
                var val=body.find('[name=confirm_input]').val();
                if(typeof callback=='function'){
                    var result = callback(val);
                    if(result===true){
                        called=true;
                    }
                    return result;
                }
            },
            'onhide':function () {
                if(called=false && typeof cancel=='function'){
                    return cancel();
                }
            }
        }).show('<input type="text" name="confirm_input" class="form-control" />',message);
    },
    pickUser:function(url,callback,filter){
        var user=null;
        if(!filter)filter={};
        var dlg=new Dialog({
            'onshown':function(body){
                var btn=body.find('.searchbtn');
                var input=body.find('.searchtext');
                var listbox=body.find('.list-group');
                var isloading=false;
                btn.click(function(){
                    if(isloading)return;
                    isloading=true;
                    listbox.html('<span class="list-loading">加载中...</span>');
                    filter['key']=input.val();
                    $.ajax(
                        {
                            url:url,
                            type:'GET',
                            dataType:'JSON',
                            data:filter,
                            success:function(json){
                                isloading=false;
                                if(json.status){
                                    if(json.data && json.data.length) {
                                        listbox.html('<a href="javascript:" data-id="{@id}" class="list-group-item list-group-item-action">[{@id}]&nbsp;<i class="ion-md-person"></i> {@username}&nbsp;&nbsp;&nbsp;<small><i class="ion-md-phone-portrait"></i> {@mobile}</small></a>'.compile(json.data, true));
                                        listbox.find('a.list-group-item').click(function () {
                                            var id = $(this).data('id');
                                            for (var i = 0; i < json.data.length; i++) {
                                                if(json.data[i].id==id){
                                                    user=json.data[i];
                                                    listbox.find('a.list-group-item').removeClass('active');
                                                    $(this).addClass('active');
                                                    break;
                                                }
                                            }
                                        })
                                    }else{
                                        listbox.html('<span class="list-loading"><i class="ion-md-warning"></i> 没有检索到会员</span>');
                                    }
                                }else{
                                    listbox.html('<span class="text-danger"><i class="ion-md-warning"></i> 加载失败</span>');
                                }
                            }
                        }
                    );

                }).trigger('click');
            },
            'onsure':function(body){
                if(!user){
                    toastr.warning('没有选择会员!');
                    return false;
                }
                if(typeof callback=='function'){
                    var result = callback(user);
                    return result;
                }
            }
        }).show('<div class="input-group"><input type="text" class="form-control searchtext" name="keyword" placeholder="根据会员id或名称，电话来搜索"/><div class="input-group-append"><a class="btn btn-outline-secondary searchbtn"><i class="ion-md-search"></i></a></div></div><div class="list-group mt-2"></div>','请搜索并选择会员');
    }
};

jQuery(function($){

    //监控按键
    $(document).on('keydown', function(e){
        if(!Dialog.instance)return;
        var dlg=Dialog.instance;
        if (e.keyCode == 13) {
            dlg.box.find('.modal-footer .btn').eq(dlg.options.defaultBtn).trigger('click');
        }
        //默认已监听关闭
        /*if (e.keyCode == 27) {
         self.hide();
         }*/
    });
});

jQuery(function ($) {
    //高亮当前选中的导航
    var bread= $(".breadcrumb");
    var menu = bread.data('menu');
    if(menu) {
        var link = $('.side-nav a[data-key=' + menu + ']');

        var html=[];
        if (link.length > 0) {
            if(link.is('.menu_top')){
                html.push('<li class="breadcrumb-item"><a href="javascript:"><i class="'+link.find('i').attr('class')+'"></i>&nbsp;'+link.text()+'</a></li>');
            }else {
                var parent = link.parents('.collapse').eq(0);
                parent.addClass('show');
                link.addClass("active");
                var topmenu=parent.siblings('.card-header').find('a.menu_top');
                html.push('<li class="breadcrumb-item"><a href="javascript:"><i class="'+topmenu.find('i').attr('class')+'"></i>&nbsp;'+topmenu.text()+'</a></li>');
                html.push('<li class="breadcrumb-item"><a href="javascript:">'+ link.text()+'</a></li>');
            }
        }
        var title=bread.data('title');
        if(title){
            html.push('<li class="breadcrumb-item active" aria-current="page">'+ title+'</li>');
        }
        bread.html(html.join("\n"));
    }

    //全选、反选按钮
    $('.checkall-btn').click(function (e) {
        var target=$(this).data('target');
        if(!target)target='id';
        var ids=$('[name='+target+']');
        if($(this).is('.active')){
            ids.removeAttr('checked');
        }else{
            ids.attr('checked',true);
        }
    });
    $('.checkreverse-btn').click(function (e) {
        var target=$(this).data('target');
        if(!target)target='id';
        var ids=$('[name='+target+']');
        for(var i=0;i<ids.length;i++) {
            if (ids[i].checked) {
                ids.eq(i).removeAttr('checked');
            } else {
                ids.eq(i).attr('checked', true);
            }
        }
    });
    //操作按钮
    $('.action-btn').click(function(e){
        e.preventDefault();
        var action=$(this).data('action');
        if(!action){
            return toastr.error('未知操作');
        }
        action='action'+action.replace(/^[a-z]/,function(letter){
            return letter.toUpperCase();
        });
        if(!window[action] || typeof window[action] !== 'function'){
            return toastr.error('未知操作');
        }
        var needChecks=$(this).data('needChecks');
        if(needChecks===undefined)needChecks=true;
        if(needChecks){
            var target=$(this).data('target');
            if(!target)target='id';
            var ids=$('[name='+target+']:checked');
            if(ids.length<1){
                return toastr.warning('请选择需要操作的项目');
            }else{
                var idchecks=[];
                for(var i=0;i<ids.length;i++){
                    idchecks.push(ids.eq(i).val());
                }
                window[action](idchecks);
            }
        }else{
            window[action]();
        }
    });

    //异步显示资料链接
    $('a[rel=ajax]').click(function(e){
       e.preventDefault();
        var self=$(this);
        var title=$(this).data('title');
        if(!title)title=$(this).text();
        var dlg=new Dialog({
            btns:['确定'],
            onshow:function(body){
                $.ajax({
                    url:self.attr('href'),
                    success:function(text){
                        body.html(text);
                    }
                });
            }
        }).show('<p class="loading">加载中...</p>',title);

    });

    $('.nav-tabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show')
    });

    //上传框
    $('.custom-file .custom-file-input').on('change',function(){
        var label=$(this).parents('.custom-file').find('.custom-file-label');
        label.text($(this).val());
    });

    //表单Ajax提交
    $('.btn-primary[type=submit]').click(function(e){
        var form=$(this).parents('form');
        var btn=this;
        var options={
            url:$(form).attr('action'),
            type:'POST',
            dataType:'JSON',
            success:function (json) {
                if(json.code==1){
                    new Dialog({
                        onhidden:function(){
                            if(json.url){
                                location.href=json.url;
                            }else{
                                location.reload();
                            }
                        }
                    }).show(json.msg);
                }else{
                    toastr.warning(json.msg);
                    $(btn).removeAttr('disabled');
                }
            }
        };
        if(form.attr('enctype')=='multipart/form-data'){
            if(!FormData){
                return true;
            }
            options.data=new FormData(form[0]);
            options.cache=false;
            options.processData=false;
            options.contentType=false;
        }else{
            options.data=$(form).serialize();
        }

        e.preventDefault();
        $(this).attr('disabled',true);
        $.ajax(options);
    });

    $('.pickuser').click(function(e){
        var group=$(this).parents('.input-group');
        var idele=group.find('[name=member_id]');
        var infoele=group.find('[name=member_info]');
        dialog.pickUser($(this).data('url'),function(user){
            idele.val(user.id);
            infoele.val('['+user.id+'] '+user.username+(user.mobile?(' / '+user.mobile):''));
        },$(this).data('filter'));
    });

    //日期组件
    if($.fn.datetimepicker) {
        var tooltips= {
            today: '定位当前日期',
            clear: '清除已选日期',
            close: '关闭选择器',
            selectMonth: '选择月份',
            prevMonth: '上个月',
            nextMonth: '下个月',
            selectYear: '选择年份',
            prevYear: '上一年',
            nextYear: '下一年',
            selectDecade: '选择年份区间',
            prevDecade: '上一区间',
            nextDecade: '下一区间',
            prevCentury: '上个世纪',
            nextCentury: '下个世纪'
        };
        var icons={
            time: 'ion-clock',
            date: 'ion-calendar',
            up: 'ion-arrow-up-c',
            down: 'ion-arrow-down-c',
            previous: 'ion-arrow-left-c',
            next: 'ion-arrow-right-c',
            today: 'ion-pinpoint',
            clear: 'ion-trash-a',
            close: 'ion-close'
        };
        $('.datepicker').datetimepicker({
            icons:icons,
            tooltips:tooltips,
            format: 'YYYY-MM-DD',
            locale: 'zh-cn',
            showClear:true,
            showTodayButton:true,
            showClose:true,
            keepInvalid:true
        });

        $('.date-range').each(function () {
            var from = $(this).find('[name=fromdate],.fromdate'), to = $(this).find('[name=todate],.todate');
            var options = {
                icons:icons,
                tooltips:tooltips,
                format: 'YYYY-MM-DD',
                locale:'zh-cn',
                showClear:true,
                showTodayButton:true,
                showClose:true,
                keepInvalid:true
            };
            from.datetimepicker(options).on('dp.change', function () {
                if (from.val()) {
                    to.data('DateTimePicker').minDate(from.val());
                }
            });
            to.datetimepicker(options).on('dp.change', function () {
                if (to.val()) {
                    from.data('DateTimePicker').maxDate(to.val());
                }
            });
        });
    }
});



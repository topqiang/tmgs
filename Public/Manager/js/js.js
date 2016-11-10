$(document).ready(function(){
    // 全选
    $('.check-all').click(
        function(){
            $(this).parents('table').find("input[type='checkbox']").attr('checked', $(this).is(':checked'));
            if($(this).is(':checked') == true) {
                $(this).parents('table').find("div.checker span").addClass('checked');
            } else {
                $(this).parents('table').find("div.checker span").removeClass('checked');
            }
        }
    );
    //隔行换色
    $('.tbody tr:odd').addClass("odd-row");

    //表单
    $('input[type=checkbox],input[type=radio],input[type=file]').uniform();

    //重置表格里的复选框
    $('table').find("input[type='checkbox']").attr('checked', false);
    $('table').find("div.checker span").removeClass('checked');
    //按钮点击跳转
    $('.href').bind('click',function(){ window.location.href = $(this).attr('url'); });

    //下拉菜单点击事件
    $('ul.dropdown-menu li').bind('click',function(){
        $(this).parents('div.btn-group').find('button.checked').html($(this).attr('data-title'));
        $(this).parents('div.btn-group').next('input').val($(this).attr('data-value'));
    });

    //下拉菜单 选中
    $('div.btn-group').each(function(key,obj){
        if($(obj).find('ul li.selected a').html() == null) {
            $(obj).find('button.checked').html($(obj).find('button.checked').attr('data-default'));
        } else {
            $(obj).find('button.checked').html($(obj).find('ul li.selected a').html());
        }
    });

    //只能填写数字
    $('input.number-only').keyup(function(){
        this.value=this.value.replace(/[^\d]/g,'');
    });

    //定义setTimeout执行方法  双击文本框内容清空
    var TimeFn = null;
    $('input[type="text"]').click(function () {
        // 取消上次延时未执行的方法
        clearTimeout(TimeFn);
        //执行延时
        TimeFn = setTimeout(function(){
            //在此处写单击事件要执行的代码
        },300);
    });
    $('input[type="text"]').dblclick(function () {
        //取消上次延时未执行的方法
        clearTimeout(TimeFn);
        //双击事件的执行代码
        $(this).val('');
    });

    //点击高级查询
    $('.senior').click(function(){
        if($(this).next('div.senior-search').css('display') == 'none') {
            $(this).next('div.senior-search').show();
        } else {
            $(this).next('div.senior-search').hide();
        }
    });
    $(document).click(function (ev) {
        if ($(ev.target).parents('div.senior-search').html() == null && $(ev.target).attr("class") != 'btn btn-warning senior') {
            $("div.senior-search").hide();
        }
    });


    $('.single-edit').click(function () {
        // 取消上次延时未执行的方法
        clearTimeout(TimeFn);
        //执行延时
        TimeFn = setTimeout(function(){
            //在此处写单击事件要执行的代码
        },300);
    });
    //列表中单一字段修改
    $('.single-edit').dblclick(function () {
        //取消上次延时未执行的方法
        clearTimeout(TimeFn);
        //双击事件的执行代码
        var value = $(this).html();
        var html = '<input type="text" style="margin-bottom:0;width:100px" data-old-value="'+value+'" ' +
            'data-model="'+$(this).attr('data-model')+'" data-field="'+$(this).attr('data-field')+'" data-id="'+$(this).attr('data-id')+'" ' +
            'onblur="single_edit_input_blur(this)">';
        $(this).html(html);
        $(this).find('input').focus().val(value);
    });

    //开关操作
    $('.on-off').click(function(){
        var arr = $(this).next('input').val().split('||');
        ajaxIsShow(arr[0],arr[1],arr[2],arr[3],this);
    });
});
function single_edit_input_blur(obj) {
    var that = $(obj), model = that.attr('data-model'), field = that.attr('data-field'), id = that.attr('data-id'), new_value = that.val(), old_value = that.attr('data-old-value');
    var target = '/Manager/'+model+'/singleEdit.shtml', data = {model:model,id:id,field:field,value:new_value};
    if(new_value == old_value) {
        $(obj).parent().html(old_value); return;
    }
    $.post(target,data).success(function(data){
        if (data.status == 1) {
            $(obj).parent().html(new_value);
        } else {
            $(obj).parent().html(old_value);
            updateAlert(data.info,'alert-error');
            setTimeout(function() {
                $('.alert').hide();
            }, 1500);
        }
    });
}

/**
 * @param model 模型名称
 * @param field 要修改的字段
 * @param id 条件id
 * @param flag 标记 1显示 0隐藏
 * @param obj
 * 开关操作
 */
function  ajaxIsShow(model,field,id,flag,obj){
    $.ajax({
        url: '/Manager/'+model+'/onOff.shtml',type: 'post',data: {model:model,id:id,flag:flag,field:field},dataType: 'JSON',
        success: function (data) {
            if(data.status == 1){
                updateAlert(data.info,'alert-success');
                setTimeout(function() {
                    $('.alert').hide();
                }, 1500);
                $(obj).parent().find('.on-off').show();
                $(obj).hide();
            }else{
                updateAlert(data.info,'alert-error');
                setTimeout(function() {
                    $('.alert').hide();
                }, 1500);
            }
        }
    });
}
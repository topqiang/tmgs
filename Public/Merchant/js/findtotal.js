$(document).on('dblclick','.show-date-list',function(){
    $(this).remove();
    $('.total-money').text(gainDateList().length * $('select[name="type"]').find('option:selected').data('money')  * 1 + '元');
})
function gainDateList(){
    var arr = new Array;
    $.map($('.show-date-list'),function(n,k){
        arr[k] = n.textContent;
    });
    return arr;
}
// 选择时间 并 返回 时间列表
$(".form_datetime-btn").datetimepicker({
    language : 'zh-CN',
    minView : 2,
    linkField: "form_datetime",
    linkFormat: "yyyy-mm-dd"
}).on('changeDate',function(ev){
    var b = $('#form_datetime').val();
    if(Date.parse(b) <= Date.parse(getYesterday(-1)))return false;
    if($.inArray(b,gainDateList()) != -1)return false;
    judgeDay(b);

});
// 选择办理服务
$('select[name="type"]').change(function() {
    var type = $(this).val(); // 获得
    if(type == '3'){
        // 当为发现好商品的时候显示
        $('.good_goods').show();
    }else{
        // 当为非发现好商品的时候隐藏
        $('.good_goods').hide();
    }
});


// 提交
$('#submit').click(function(){
    var pay_type = $('select[name="pay_type"]').val(); // 支付类型
    var type = $('select[name="type"]').val(); // 投放类型
    var start_time = gainDateList(); // 选择所有时间
    var money = parseFloat($('.total-money').text()); // 总价格
    if(type == 3){
        var goods_id =  $('.goods_name_list').val(); // 商品id
    }
    if(pay_type == '4')
        if(confirm('你将使用余额支付,确认支付') == false)
            return false;
    $.post('/Merchant/FindTotal/addFindTotal',
        {
            pay_type :pay_type,
            type :type,
            start_time :start_time,
            money :money,
            goods_id : goods_id
        },
        function(d){
            if(d.status == 1){
                alert(d.info);
                window.location.href = d.url;
            }else{
                updateAlert(d.info,'alert-error');
                setTimeout(function(){$('.alert').hide();},1500);
                return false;
            }
        }
    )
});
// 当为 选择商品的时候 
$(document).on('keyup','input[name="goods_name"]',function(){
    var keyWord = $(this).val(); // 商品名称
    $.post('/Merchant/FindTotal/getGoodsName',{name:keyWord},function(data){
        $('.goods_name_list').html('');
        $.map(data, function(item, index) {
            $('.goods_name_list').append("<option value='"+item.id+"'>"+item.cn_goods_name+"</option>");
        });
    });
})
// 置0
// 当为 支付类类型
$('select[name="pay_type"]').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
})
// 当为 类型
$('select[name="type"]').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
})

/**
 * 获取昨天的时间
 * @param fate 正向 为推进 负向 为追溯
 */
function getYesterday(fate){
    var newDay = new Date(); // 生成一个新时间
    newDay.setDate(newDay.getDate() + fate);
    var year = newDay.getFullYear(); //获取年份
    var month = ''; // 获取月份
    if(month < 10){
        month = '0' + (newDay.getMonth() + 1);
    }else{
        month = newDay.getMonth() + 1;
    }
    var day = newDay.getDate(); //获取天
    return year +'-'+ month +'-'+ day;
}
/**
 * 判断当前时间是否可以办理
 */
function judgeDay(date)
{
    var option = {
        type : $('select[name="type"]').val(),
        today : date
    }
    if($('select[name="type"]').val() == '') return false; // 当办理服务未选择时
    $('.show-date').append('<div class="show-date-list" style="padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;cursor: pointer">'+option.today+'</div>');
    $('.total-money').text(gainDateList().length * $('select[name="type"]').find('option:selected').data('money')  * 1 + '元');
}
$(document).on('dblclick','.show-date-list',function(){
    $(this).remove();
    $('.total-money').text(gainDateList().length * $('select[name="location"]').find('option:selected').data('money') * 1 + '元');
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

// 提交
$('#submit').click(function(){
    var url = $('input[name="url"]').val(); // 地址
    var pic = $('input[name="pic"]').val(); // 图片
    var region = $('select[name="region"]').val(); // 地区
    var pay_type = $('select[name="pay_type"]').val(); // 支付类型
    var type = $('select[name="type"]').val(); // 投放类型
    var location = $('select[name="location"]').val(); // 投放位置
    var start_time = gainDateList(); // 选择所有时间
    var money = parseFloat($('.total-money').text()); // 总价格
    $.post('/Merchant/AdPosition/addAdPosition',
        {
            url : url,
            pic : pic,
            region :region,
            pay_type :pay_type,
            type :type,
            location :location,
            start_time :start_time,
            money :money
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

// 置0
$('select[name="pay_type"]').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
})
$('select[name="type"]').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
})
$('select[name="location"]').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
})
$('#city').change(function(){
    $('.show-date').html('');
    $('.total-money').html('0元')
});

//  市区选择
$('#province').change(function(){
    $('#city').html(' <option value="">请选择市级地区</option>');
    var parent_id = $(this).val(); // 上级ID
    $.post('/Merchant/AdPosition/getRegionAjax',{parent_id:parent_id},function(d){
        $.map(d,function(md){
            $('#city').append('<option value="'+md.id+'">'+md.region_name+'</option>')
        })
    });
});
// 选择 投放位置
$('select[name="type"]').change(function(){
    var type= '';
    var a = 1;
    $('select[name="location"]').html('');
    if($(this).val() == 1){type = 1;}else{type = 2;}
    $.post('/Merchant/AdPosition/getAdvertAjax',{type:type},function(d){
        $.map(d,function(dd){
            $('select[name="location"]').append('<option value="'+dd.location+'" data-id="'+dd.id+'" data-money="'+dd.money+'">位置'+a+'-价格'+ dd.money+'</option>');
            a += 1;
        })
    });
});

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
        region : $('#city').val(),
        type : $('select[name="type"]').val(),
        location : $('select[name="location"]').val(),
        today : date
    }
    if(option.region == ''){
        updateAlert('请选择投放的城市','alert-error');
        setTimeout(function(){$('.alert').hide();},1500);
        return false;
    }
    $.post('/Merchant/AdPosition/judgeAmount',option,function(d){
        if(d.status == 0){
            updateAlert(d.info,'alert-error');
            setTimeout(function(){$('.alert').hide();},1500);
            return false;
        }else{
            $('.show-date').append('<div class="show-date-list" style="padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;cursor: pointer">'+option.today+'</div>');
            $('.total-money').text(gainDateList().length * $('select[name="location"]').find('option:selected').data('money') * 1 + '元');
        }
    });
}
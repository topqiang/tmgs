function getRegion(url,parent_id,param)
{
    var param = param;
    $.ajax({
        url : url,
        type : 'POST',
        data : {parent_id:parent_id},
        success:function(d){
            if(d.status == 1){
                if(param == '#city'){
                    $(param).html('<option value="">请选择地区</option>');
                    $('#district').html('<option value="">请选择地区</option>');
                }
                $(param).html('<option value="">请选择地区</option>');
                for(var i=0;i< d.data.length;i++) {
                    var option = "<option value="+ d.data[i].id+">"+ d.data[i].region_name+"</option>";
                    $(param).append(option);
                }
            }else{
                alert('请重试');
            }

        }
    })
}
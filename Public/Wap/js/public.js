/**
 * @name dom  linkto 属性增强
 * @author topqiang
 * @version 1.0
 * **/
function top_linkto(){
	$("[linkto]").on('click',function(){
		var self = $(this);
		var url = $.trim(self.attr("linkto"));
		if( url == ""){
			return;
		}
		//匹配全路径跳转
		if( url.indexOf("http://") == 0 || url.indexOf("https://") == 0 ){
			window.location.href = url;
		}else if( url.indexOf("/") == 0 ){
			//匹配   类似于Thinkphp类型的URL {:U('Index/index')} => /项目名/index.php/Index/index  
			var hostname = getRootPath();
			url = hostname + url;
			window.location.href = url;
		}else if( url.indexOf("tel:") == 0){
			window.location.href = url;
		}else if( url.indexOf("javascript:") == 0){
			var start = url.indexOf(":");
			var str = url.substring(start+1);
			eval(str);
		}else if( url.indexOf("./") == 0 || url.indexOf("/") == -1){
			//匹配静态跳转，已经相对路径跳转。
			var curUrl = window.location.href;
			url = curUrl.substr(0,curUrl.lastIndexOf("/")+1) + url;
			window.location.href = url;
		}
	});
}
function getRootPath(){
    var curWwwPath=window.document.location.href;
    var pathName=window.document.location.pathname;
    var pos=curWwwPath.indexOf(pathName);
    var localhostPaht=curWwwPath.substring(0,pos);
    var projectName=pathName.substring(0,pathName.substr(1).indexOf('/')+1);
    return(localhostPaht);
}
/**
 * @name check响应
 * @author topqiang
 * @version 1.0
 * **/
function top_check(){
	$(".top_radio").off('click');
	$(".top_check").off('click');
	$(".top_radio").on('click',function(){
		var self = $(this);
		self.addClass("on");
		self.siblings().removeClass("on");

		if (typeof radioAfter === 'function') {
			radioAfter(self);
		}
	});
	$(".top_check").on('click',function(){
		var self = $(this);
		if(self.hasClass("on")){
			self.removeClass("on");
		}else{
			self.addClass("on");
		}

		if (typeof checkAfter === 'function') {
			checkAfter(self);
		}
	});
}
/**
 * @name  星级评价组件
 * @author topqiang
 * @version 1.0
 * **/
function top_rate(){
	$(".top_rate span").on('click',function(){
		var self = $(this);
		self.nextAll().removeClass("on");
		self.prevAll().addClass("on");
		if(!self.hasClass("on")){
			self.addClass("on");
		}
	});
}
/**
 * @name  购物车显示隐藏
 * @author topqiang
 * @version 1.0
 * **/
function top_ingley(){
	$(".gley .icongley").on('click',function(){
		if($(this).parents('.gley').hasClass('on')){
			$(".zhao").toggleClass('disn');
			$(".zhcon").toggleClass('disn');
		}
	});
}
/**
 * @name 隐式下拉框交互
 * @author topqiang
 * @version 1.0
 * **/
function top_select(){
	$("select.selpstime").on('change',function(){
		var self = $(this);
		var elename = self.attr("forele");
		if($.trim(elename) != ""){
			var ele = self.parent().find("[ele="+elename+"]");
			var value = self.val();
			var inValue = self.find('option[value='+value+']').text();
			// ele.text(self.val());
			ele.text(inValue);
		}

		if (typeof selectAfter === 'function') {
			selectAfter(self);
		}
	});
}

/**
 * @name 回顶部
 * @author topqiang
 * @version 1.0
 * **/
function top_gohead(){
	$(".top_gohead").on('click',function(){
		var height = $(document).scrollTop();
		$("html,body").animate({scrollTop:0},500);
	});
}

/**
 * @name  切换商品查看样式
 * @author topqiang
 * @version 1.0
 * **/
function top_jeep(){
	$(".iconv").on('click',function(){
		var self = $(this);
		if(self.hasClass('on')){
			$(".good").removeClass('good').addClass('goodh');
		}else{
			$(".goodh").removeClass('goodh').addClass('good');
		}
		self.toggleClass('on');

		if (typeof jeepAfter == 'function') {
			jeepAfter(self);
		}
	});
}

/**
 * @name 分类筛选
 * @author topqiang
 * @version 1.0
 * **/
function top_sifting(){
	$(".dosifting").on('click',function(){
		$(".sifting").slideToggle();
	});
}
/**
 * @name range
 * @author topqiang
 * @version 1.0
 * */
function top_range(){
	$(".range").each(function(){
		var self = $(this);
		var total = self.attr("total");
		var cur = self.attr("cur");
		var width = cur / total * 100;
		if (!total || !cur) {
			var completeness = self.attr("completeness");
			width = completeness * 100;
		}
		self.find("span").css({"width" : width+"%" });
	});
}

/**
*ajax 请求封装
*@author topqiang
*@name requestUrl
**/ 
function requestUrl(URL,DATA,CALLBACK,TYPE,DATATYPE){
	if (!URL) return;
	if (!TYPE) TYPE ="post";
	if (!DATATYPE) DATATYPE ="json";
	$.ajax({
		"url":URL,
		"data" : DATA,
		"dataType" : DATATYPE,
		"type" : TYPE,
		"success" : function(res){
			if (typeof CALLBACK == 'function') {
				CALLBACK(res);
			}
		}
	});
}

/**
*获取用户信息
*@author  topqiang
***/
function getUserInfo(URL){
	var jsonstr = sessionStorage.getItem("top_user");
	if (jsonstr) {
		window.top_user = JSON.parse(jsonstr);
		return top_user;
	}else{
		if(!URL){
			console.warn("请在获取用户信息时，填写登录页面URL");
			return "";
		}else{
			window.location.href = URL;
		}
	}
}
//将用户信息挂在到
var jsonstr = sessionStorage.getItem("top_user");
	if (jsonstr) {
		window.top_user = JSON.parse(jsonstr);
	}else{
		window.top_user = false;
	}


$(function(){
	//吊起linkto增强页面跳转
	top_linkto();
	//吊起check响应
	top_check();
	//开启星级评价
	top_rate();
	//开启购物车
	top_ingley();
	//吊起统一下拉框
	top_select();
	//吊起回顶部
	top_gohead();
	//切换预览商品版图
	top_jeep();
	//吊起分类刷选事件
	top_sifting();
	//显示进度条
	top_range();
});

$(document).ready(function(){
	// === Sidebar navigation === //
	
	$('.submenu > a').click(function(e) {
		e.preventDefault();
		var submenu = $(this).siblings('ul');
		var li = $(this).parents('li');
		var submenus = $('#sidebar li.submenu ul');
		var submenus_parents = $('#sidebar li.submenu');
		if(li.hasClass('open')) {
			if(($(window).width() > 768) || ($(window).width() < 479)) {
				submenu.slideUp();
			} else {
				submenu.fadeOut(250);
			}
			li.removeClass('open');
		} else {
			if(($(window).width() > 768) || ($(window).width() < 479)) {
				submenus.slideUp();			
				submenu.slideDown();
			} else {
				submenus.fadeOut(250);			
				submenu.fadeIn(250);
			}
			submenus_parents.removeClass('open');		
			li.addClass('open');	
		}
	});
	
	var ul = $('#sidebar > ul');
	
	$('#sidebar > a').click(function(e) {
		e.preventDefault();
		var sidebar = $('#sidebar');
		if(sidebar.hasClass('open')) {
			sidebar.removeClass('open');
			ul.slideUp(250);
		} else {
			sidebar.addClass('open');
			ul.slideDown(250);
		}
	});
	
	// === Tooltips === //
	$('.tip').tooltip();	
	$('.tip-left').tooltip({ placement: 'left' });	
	$('.tip-right').tooltip({ placement: 'right' });	
	$('.tip-top').tooltip({ placement: 'top' });	
	$('.tip-bottom').tooltip({ placement: 'bottom' });	
	
	// === Search input typeahead === //
	$('#search input[type=text]').typeahead({
		source: ['Dashboard','Form elements','Common Elements','Validation','Wizard','Buttons','Icons','Interface elements','Support','Calendar','Gallery','Reports','Charts','Graphs','Widgets'],
		items: 4
	});
	
	// === Style switcher === //
	$('#style-switcher i').click(function() {
		if($(this).hasClass('open')) {
			$(this).parent().animate({marginRight:'-=190'});
			$(this).removeClass('open');
		} else {
			$(this).parent().animate({marginRight:'+=190'});
			$(this).addClass('open');
		}
		$(this).toggleClass('icon-arrow-left');
		$(this).toggleClass('icon-arrow-right');
	});
	
	$('#style-switcher a').click(function() {
		var style = $(this).attr('href').replace('#','');
		$('.skin-color').attr('href','css/unicorn.'+style+'.css');
		$(this).siblings('a').css({'border-color':'transparent'});
		$(this).css({'border-color':'#aaaaaa'});
	});


    //不同浏览器 placeholder处理
    if($.browser.msie == true && $.browser.version.slice(0,3) < 10) {
        $('input[placeholder]').each(function(){
            var input = $(this);
            $(input).val(input.attr('placeholder'));
            $(input).focus(function(){
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            });
            $(input).blur(function(){
                if (input.val() == '' || input.val() == input.attr('placeholder')) {
                    input.val(input.attr('placeholder'));
                }
            });
        });
    }
});

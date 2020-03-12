// JavaScript Document
$(function(){	
	$(".select").click(function(event){
		$(".sub").slideToggle();
		$(".select").toggleClass('type_hover');
		event.stopPropagation() 
	});
	$(".sub dd").each(function(a,b){
		$(this).click(function(){
			$("#searchtype").val($(this).data('value'));
			$(".select").html($(this).html());
			$(".select").removeClass('type_hover');
			$(".sub").slideUp();
		});
	});
	$(document).click(function(){
		$(".sub").slideUp();
		$(".select").removeClass('type_hover');
	});
	if($(window).scrollTop() <= 100){
		$('#go_top').hide()
	}
	$(window).scroll(function(){
		if($(window).scrollTop() > 100){
			if($('#go_top').css('display') == 'none'){
				$('#go_top').fadeIn()
			}
		}else{
			$('#go_top').fadeOut()
		}
	});
	$('#go_top').click(function(){
		$("html, body").animate({scrollTop:0});
	});
	$('.find_error').click(function(){
		$.post(geterror,{'url':url},function(result){
			if(result.msg != null){
				alertmsg(result.msg);
			}else{
				window.open(geterror+'?url='+url,'geterror','width=400,height=300,top=0,left=0,scrollbars=0,resizable=no');
			}
		});
	});
});
function SetHome(obj,url){
	try{
       obj.style.behavior='url(#default#homepage)';
       obj.setHomePage(url);
  	}catch(e){
       if(window.netscape){
          try{
              netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
          }catch(e){
              alert("抱歉，此操作被浏览器拒绝！\n\n请在浏览器地址栏输入\"about:config\"并回车然后将[signed.applets.codebase_principal_support]设置为'true'");
          }
       }else{
        alert("抱歉，您所使用的浏览器无法完成此操作。\n\n您需要手动将【"+url+"】设置为首页。");
       }
	}
}
function AddFavorite(title, url) {
  try {
      window.external.addFavorite(url, title);
  }
	catch (e) {
		try {
		   window.sidebar.addPanel(title, url, "");
		}
		 catch (e) {
			 alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
		 }
    }
}
function getcookie(name) {
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
}
 
function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	expires.setTime(expires.getTime() + seconds);
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)
	+ (expires ? '; expires=' + expires.toGMTString() : '')
	+ (path ? '; path=' + path : '/')
	+ (domain ? '; domain=' + domain : '')
	+ (secure ? '; secure' : '');
}
function alertmsg(msg){
	var html = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner">'+msg+'</td><td class="pc_r">&nbsp;</td></tr></table>';
	$('body').append(html);
	var width  = ($(window).width() - $('.popupcredit').width()) / 2;
	var height = ($(window).height() - 42) / 2;
	$('.popupcredit').css('top',height);
	$('.popupcredit').css('left',width);
	$('.popupcredit').fadeIn(800);
	setTimeout(function(){
		$('.popupcredit').fadeOut(800,function(){$('.popupcredit').remove();});

	},2000)
}
function dealresult(result){
	alertmsg(result.msg);
	if(result.url != null && result.url != ''){
		setTimeout(function(){
			window.location = result.url;
		},1000);
	}
}
function delsure(id){
	if(confirm('确定要删除?')){
		$.ajax({
			url: '/member/delBook',
			data: {id:id},
			dataType: 'json', //服务器返回json格式数据
			type: 'POST', //HTTP请求类型
			timeout: 10000, //超时时间设置为10秒；
			success: function(res) {
				if (res.code == 201) {
					alertmsg('未登录，请先登录');
					setTimeout(function() {
						window.location.href = '/';
					}, 1500);
				} else if (res.code == 0) {
					alertmsg('移除成功');
					setTimeout(function() {
						window.location.reload();
					}, 1500);
					// $('.nav-top .member').html('<a href="/member/index" class="red">'+res.data.username+'</a>')
				} else {
					alertmsg(res.msg);
				}
			},
			error: function(xhr, type, errorThrown) {
				alertmsg('系统错误');
			}
		});
	}else{
		return false;
	}
}


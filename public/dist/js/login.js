$(function(){
	var login_left = ($(window).width() - $('#login-jump').width()) / 2;
	var login_top = ($(window).height() - $('#login-jump').height()) / 2;
	$('#login-jump').css('left', login_left);
	$('#login-jump').css('top', login_top);
	
	$('#login-jump .tit span').click(function(){
		$('#login-jump .tit span').removeClass('current');
		$(this).addClass('current');
		if($(this).attr('tabid') == 1){
			$('#lg-jump').slideDown();
			$('#rg-jump').slideUp();
		}else if($(this).attr('tabid') == 2){
			$('#lg-jump').slideUp();
			$('#rg-jump').slideDown();
		}
	});
	$('#reg_a').click(function(){
		$('#login-jump .tit span').removeClass('current');
		$('#login-jump .tit span').get(1).setAttribute('class','current');
		$('#lg-jump').slideUp();
		$('#rg-jump').slideDown();
	});
	$('#login-jump .close').click(function(){
		$('#login-jump').fadeOut();
		$('.bg').fadeOut();
	});
	
	$('#show_log').click(function(){
		$('.bg').fadeIn();
		$('#login-jump').fadeIn();
		$('#login-jump .tit span').removeClass('current');
		$('#login-jump .tit span').get(0).setAttribute('class','current');
		$('#lg-jump').slideDown();
		$('#rg-jump').slideUp();
	});
	$('#show_reg').click(function(){
		$('.bg').fadeIn();
		$('#login-jump').fadeIn();
		$('#login-jump .tit span').removeClass('current');
		$('#login-jump .tit span').get(1).setAttribute('class','current');
		$('#lg-jump').slideUp();
		$('#rg-jump').slideDown();
	});
	$('.captcha_img_btn').click(function(){
		$('.login_yzm').attr('src', verify+'?tm='+Math.random());
	});
	$('.captcha_btn').click(function(){
		$('.register_yzm').attr('src', verify+'?tm='+Math.random());
	});
	$('#login').click(function(){
		var fm = document.getElementsByName('login_form')[0];
		if(fm.username.value.length < 6 || fm.password.value.length > 20){
			alertmsg('用户名长度为6位到20位');
			fm.username.focus();
			return false;
		}
		if(fm.password.value.length < 6){
			alertmsg('密码最少6位');
			fm.password.focus();
			return false;
		}
		$.post(login_a, {'username':fm.username.value,'password':fm.password.value}, log_reg);
		return false;
	});
	
	$('#register').click(function(){
		var fm = document.getElementsByName('register_form')[0];

		if(fm.username.value.length < 6 || fm.password.value.length > 20){
			alertmsg('用户名长度为6位到20位');
			fm.username.focus();
			return false;
		}
		if(fm.password.value.length < 6 || fm.password.value.length > 16){
			alertmsg('密码最小6位，最长16位');
			fm.password.focus();
			return false;
		}
		if(fm.password.value !== fm.repassword.value){
			alertmsg('两次密码不一致');
			fm.repassword.focus();
			return false;
		}
		$.post(register_a, {'username':fm.username.value,'password':fm.password.value}, log_reg);
	});
	$('#password').keyup(function(){
		$('.passwordSuggestion').removeClass('red');
		$('.passwordSuggestion').removeClass('orange');
		$('.passwordSuggestion').removeClass('green');
			
		if(($(this).val().length >= 6) && ($(this).val().length <= 16)){
			var val = $(this).val();
			if(/[0-9]+/.test(val) && /[a-zA-Z]+/.test(val) && /[\W_]+/.test(val)){
				$('.passwordSuggestion').html('强');
	            $('.passwordSuggestion').addClass('green');
	            return;
			}
			if((/[0-9]+/.test(val) && /[a-zA-Z]+/.test(val)) || (/[0-9]+/.test(val) && /[\W_]+/.test(val)) || (/[a-zA-Z]+/.test(val) && /[\W_]+/.test(val))){
				$('.passwordSuggestion').html('中');
	            $('.passwordSuggestion').addClass('orange');
	            return;
			}
			if(/[0-9]+|[a-zA-Z]+|[\W_]+/.test(val)){
				$('.passwordSuggestion').html('弱');
	            $('.passwordSuggestion').addClass('red');
	            return;
			}
		}else{
			$('.passwordSuggestion').html('无效');
		}
	});
	$('#email').blur(function(){
		if(/^([\w\.-]+)@([a-zA-Z0-9-]+)(\.[a-zA-Z\.]+)$/.test($(this).val())){
			if($('#old_email').val() != $(this).val()){
				$('.emailMessage').html('<em class="load"></em> 正在检测该邮箱是否可用');
				$.post(is_valid,{'email':$(this).val()},validresult);
				$('#old_email').val($(this).val());
			}
		}else{
			$('.emailMessage').html('<em class="error"></em> 请输入正确的邮箱！');
			$('#old_email').val($(this).val());
			$('.emailMessage').addClass('red');
		}
	});
	$('#password').blur(function(){
		if(($(this).val().length >= 6) && ($(this).val().length <= 16)){
			$('.passwordMessage').html('<em class="em_right"></em> 密码可以使用');
			$('.passwordMessage').addClass('green');
		}else{
			$('.passwordMessage').html('<em class="error"></em> 密码应该大于6位小于16位！');
			$('.passwordMessage').addClass('red');
		}
	});
	$('#nickName').blur(function(){
		var textlength = $(this).val().replace(/[^\x00-\xff]/g, '**').length;
		if(textlength > 0 && textlength <= 12){
			if($('#old_nick').val() != $(this).val()){
				$('.nickNameMessage').html('<em class="load"></em> 正在检测该昵称是否可用');
				$.post(is_valid,{'nick':$(this).val()},validresult);
				$('#old_nick').val($(this).val());
			}
		}else{
			$('.nickNameMessage').html('<em class="error"></em> 昵称不超过6个汉字或12个字符！');
			$('.nickNameMessage').addClass('red');
			$('#old_nick').val($(this).val());
		}
	});
});
function validresult(result){
	$('.'+result.classname).html(result.html);
	$('.'+result.classname).addClass(result.addclass);
}
function log_reg(result){
	alertmsg(result.msg);
	if(result.code == 0){
		$('#login-jump').fadeOut();
		$('.bg').fadeOut();
		$('.yhao').html("欢迎 <span class=\"red\">"+result.data.username+"</span> 回来！ [<a href=\"/member/index\">用户中心</a>] [<a href=\"/member/logout\">退出</a>]");
	}
}
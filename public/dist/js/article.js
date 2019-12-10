// JavaScript Document
$(function(){
	let read_bjys = localStorage.getItem('read_bjys');
	let read_ztys = localStorage.getItem('read_ztys');
	let read_ztdx = localStorage.getItem('read_ztdx');
	$('body').css('background', read_bjys);
	$('.body').css('color', read_ztys);
	$('.body').css('font-size', read_ztdx);
	$('.body p').css('font-size', read_ztdx);
	if (read_bjys) $('#bjys').val(read_bjys);
	if (read_ztys)$('#ztys').val(read_ztys);
	if (read_ztdx)$('#ztdx').val(read_ztdx);
	$(document).keyup(function(a){
		if(a.keyCode == 37){
			window.location = $('#prev').attr('href');
		}else if(a.keyCode == 39){
			window.location = $('#next').attr('href');
		}
	});
});
function selectbj(obj){
	$('body').css('background', $(obj).val());
	localStorage.setItem('read_bjys', $(obj).val());
}
function selectzy(obj){
	$('.body').css('color', $(obj).val());
	localStorage.setItem('read_ztys', $(obj).val());
}
function selectzd(obj){
	$('.body').css('font-size', $(obj).val());
	$('.body p').css('font-size', $(obj).val());
	localStorage.setItem('read_ztdx', $(obj).val());
}

var speed = 5;
var timer;
function selectgd(obj){
	if($(obj).val() > 0 || $(obj).val() < 11){
		speed = $(obj).val();
	}
}
function stopScroll()
{
    clearInterval(timer);
}

function beginScroll()
{
	timer=setInterval("scrolling()",300/speed);
}

function scrolling()
{
	var currentpos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    window.scroll(0, ++currentpos);
	var nowpos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    if(currentpos != nowpos) clearInterval(timer);
}
document.onmousedown=stopScroll;
document.ondblclick=beginScroll;

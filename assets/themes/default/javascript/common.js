/***********
{
    state:'success', //失败为'failure'
    data:{
    	//一些待处理的数据对象
        username:'sss',
        age:28
    },
    referer:'', //跳转地址
    refresh:false, //是否刷新 
    msg:'操作成功' //返回的提示文案
}
***********/			

function getid(id){
	return document.getElementById(id);
}
/**********
 Cookie
@param name 
@return string
**********/ 
function GetXmlHttpObject(){
	var xmlHttp=null;
  	try {  
  		xmlHttp=new XMLHttpRequest();
  	}catch (e){ 
    	try{
      		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
  		}catch (e) {
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}
function HttpRequest(opaions){  
	var xmlHttp=GetXmlHttpObject()  
  	var url=opaions.url+"&sid="+Math.random();
  	xmlHttp.onreadystatechange=function(){ 
		if (xmlHttp.readyState==4){ 
			opaions.cal?opaions.cal.call(this,xmlHttp.responseText):'';  
			opaions.m?$('#'+opaions.m).html(xmlHttp.responseText):'';  
		}
 	};
  	xmlHttp.open("GET",url,true);
  	xmlHttp.send(null);
} 
function getajax(opaions){  
	var cal=opaions.cal; 
	var url=(opaions.url.indexOf('?')!=-1)?(opaions.url+'&format=json&ran='+Math.random()):(opaions.url+'?format=json&ran='+Math.random());
	$.post(url, opaions.data,
		function(data) {
			if (data) {  
				if (data.status=='success') { 
					opaions.m?$('#'+opaions.m).html(data.data):''; 
					cal?cal.call(this,data):''; 
					data.call?data.call.call(this,data):''; 
				}else if(data.status=='failure'){
					alert(data.msg);
				}else{
					alert('错误');
				}
			} else{
				alert('错误');
			} 		
	}); 	
} 
 
function mgtablet(){
	$('#mgtablet ul li').click(function(){
		var id=$(this).attr('lang');
		$('#mgtablet ul li').removeClass('on');						  
		$('.msgtable').hide();	
		$(this).addClass('on');  
		$('#'+id).show();
								  
	});	 		
}  
 
 
/****
删除对话框
*****/
function deletedata(n,v){ 
	dialogs = art.dialog({
		id:'deletedata',
		lock: true, 
		opacity: 0.87,	
		content: '删除后将无法恢复,确认删除吗?',
		icon: 'question',
		ok: function () {
				var d={'url':n,'data':{'referer':v},'cal':caldelete};
				getajax(d);
				return false;
			},
			cancel: true
		}
	); 
	
	//showMessageBox('消息：','删除后将无法恢复,确认删除吗?',0,350,'dodelete(\''+n+'\',\''+v+'\')',1,0,0,0,1,1);
	return false;
}
/****
删除操作
*****/
function dodelete(n,v){
	var d={'url':n,'data':{'referer':v},'cal':caldelete};
	getajax(d);
} 
function caldelete(data){  
	art.dialog.list['deletedata'].close();
	art.dialog.tips(data.msg); 
	location.assign(location.href);
}
/****
删除操作
*****/
function cachetemplates(url){  
	var d={'url':url,'data':'','cal':messager};
	getajax(d);
	return false;
}

function messager(str,fun){ 
	art.dialog.tips(str.msg);
	return 1;
	art.dialog({
		'id':'messager',
		title:'网页消息',
		content: str.msg
	}); 
} 

function changcode(obj) {
    document.getElementById(obj).src = document.getElementById(obj).lang + '?ran='+Math.random()
}

 
$(document).ready(function(){
	mgtablet(); 
});
 
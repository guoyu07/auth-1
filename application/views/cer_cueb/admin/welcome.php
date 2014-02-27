<? if($format!='html') { ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>聚众合力</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet"  type="text/css" media="all"href="http://127.0.0.1:8081/auth/assets/themes/default/styles/style.css" />
<link rel="stylesheet"  type="text/css" media="all"href="http://127.0.0.1:8081/auth/assets/themes/default/styles/dtree.css"  />
<link rel="stylesheet"  type="text/css" media="all"href="http://127.0.0.1:8081/auth/assets/themes/default/styles/buttons.css"  />
<script type="text/javascript">
var BASE_URL="http://127.0.0.1:8081/auth/";
var SITE_URL="http://127.0.0.1:8081/auth/index.php" ; 
var STYLES_URL="http://127.0.0.1:8081/auth/assets/cer_cueb/styles/" ; 
var JAVASCRIPT_URL="http://127.0.0.1:8081/auth/assets/cer_cueb/javascript/" ; 
var SYSTEMBUILDURL="http://127.0.0.1:8081/auth/index.php/admin";   
var BASE_STYLES="http://127.0.0.1:8081/auth/assets/themes/default/styles/"; 
var BASE_JAVASCRIPT="http://127.0.0.1:8081/auth/assets/themes/default/javascript/"; 
var BASE_IMAGES="http://127.0.0.1:8081/auth/assets/themes/default/images/"; 
</script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/jquery-1.4.2.js"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/jquery.pjax.js"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/artdialog-pake.js?skin=idialog"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/iframetools.source.js"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/plugins/datepicker/wdatepicker.js"></script>
</head>
<body>
<script type="text/javascript"> 

(function (config) {
    config['lock'] = false;
    config['fixed'] = true;
    config['okVal'] = '确认';
    config['cancelVal'] = '取消'; 
})(art.dialog.defaults);

document.onreadystatechange = subSomething;
function subSomething() {  
	if(document.readyState == "complete"){ 
		 
	} 
} 
function DrawImage(ImgD,FitWidth,FitHeight){  
    var image=new Image();  
    image.src=ImgD.src;  
    if(image.width>0 && image.height>0){  
        if(image.width/image.height>= FitWidth/FitHeight){  
            if(image.width>FitWidth){  
                ImgD.width=FitWidth;  
                ImgD.height=(image.height*FitWidth)/image.width;  
            }  
            else{  
                ImgD.width=image.width;  
                ImgD.height=image.height;  
            }  
        }  
        else{  
            if(image.height>FitHeight){  
                ImgD.height=FitHeight;  
                ImgD.width=(image.width*FitHeight)/image.height;  
            }  
            else{  
                ImgD.width=image.width;  
                ImgD.height=image.height;  
            }  
        }  
    }  
}
</script>
<div class="header">
  <h3> <a class="logo" href="http://127.0.0.1:8081/auth/index.php/admin.html" title="聚众合力" style="background-image:url('http://127.0.0.1:8081/auth/assets/themes/default/images/logo.png')">
    聚众合力    </a></h3>
  <div id="uinfo"> <span>欢迎您：<? echo isset($this->user_auth->ucdata['username'])?$this->user_auth->ucdata['username']:'';; ?></span>
    <p>消&nbsp;息：(<? echo isset($this->user_auth->ucdata['notice'])?$this->user_auth->ucdata['notice']:'';; ?>)</p>
    <h1><a href="http://127.0.0.1:8081/auth/index.php/admin/avatar.html"><img src="http://127.0.0.1:8081/auth/index.php/api/avatar/show/<? echo isset($this->user_auth->ucdata['uid'])?$this->user_auth->ucdata['uid']:'0'; ?>.html?uid=<? echo isset($this->user_auth->ucdata['uid'])?$this->user_auth->ucdata['uid']:'0'; ?>"  onerror="this.onerror=null;this.src='http://127.0.0.1:8081/auth/assets/themes/default/images/noavatar.gif'"  /></a></h1>
  </div>
  <div id="snnav"> </div>
</div>
<iframe  id="dopost" name="dopost" style="display:none" ></iframe>
<div class="wrap" id="wrap">
<div class="side" id="side" ></div>
<div class="mainbox" id="mainbox"><? } ?><h3>内容管理系统(CMS)</h3>
<div class="mainmsg">框架易扩展,稳定且具有超强大负载能力,轻松管理、发布、编辑网站内容，无需任何编程知识。</div>
<h3>网上商城系统</h3>
<div class="mainmsg">基于内存的分布式缓存系统、分布式文件系统、分布式数据存储系统可以支持站点拥有服务于百万甚至千万级庞大用户群的能力。</div>
<h3>社交网络(SNS)</h3>
<div class="mainmsg">一键升级、一键换模板皮肤、一键安装插件。</div>
<h3>oAuth协议</h3>
<div class="mainmsg">oAuth协议为用户资源的授权提供了一个安全的、开放而又简易的标准。</div>
<h3>会员管理系统</h3>
<div class="mainmsg">大型会员管理系统,具体会员管理,储值管理,积分管量,短信管理等,支持全国连锁.提供详尽的会员消费形为分析,根据客户的喜好提供针对性的解决方案。</div>
<h3>oa办公系统</h3>
<div class="mainmsg">易用,好用,适用”的oa办公系统,300万精英每天同时在线.高效,便捷致远oa办公系统,中国最大的协同管理软件专业厂商!市场占有率第一!</div>
<div class="mainmsg" style="display:none">
  <center>
    <img src="" id="statistics" lang="http://127.0.0.1:8081/auth/index.php/admin/welcome/statistics.html" align="middle" style="text-align:center" />
  </center>
</div>
<div class="mainmsg" style="display:none">
  <div class=line></div>
  手册设计： <A href="http://www.discuz.net/space.php?uid=80629" target="_blank">Ning 'Monkey' Hou</A>, <A href="http://www.discuz.net/space.php?uid=15104" target="_blank">Xiongfei 'Redstone' Zhao</A>, <A href="http://www.discuz.net/space.php?uid=122246" target="_blank">Min 'Heyond' Huang</A>, <br />
  界面设计： <A href="http://www.discuz.net/space.php?uid=362790" target="_blank">Defeng 'Dfox' Xu</A> <br />
  手册版本：
  2008-12-12, 请访问 <a href="http://www.discuz.net/thread-879237-1-1.html" target="_blank">http://www.discuz.net/thread-879237-1-1.html</a> 获取最新版本 <br />
  适用版本：
  UCenter 1.5.0 Release 20081031 及以上版本 <br />
  官方论坛： <a href="http://www.discuz.net/forum-155-1.html" target="_blank">http://www.discuz.net/forum-155-1.html</a> <br />
</div><? if($format!='html') { ?></div>
<div id="footer" class="footer">
  版权所有 聚众合力  </div>
</div>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/common.js"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/ajaxcategory.js"></script>
<script><? if(isset($_GET['msg']) && $_GET['msg']) { ?>//showMessageBox('网页提示',"<?=$_GET['msg']?>",0,350,'',0,0,1,0,1,1);
art.dialog.tips('<?=$_GET["msg"]?>');<? } ?> 
$(document).ready(function(){
	$("a").pjax("#mainbox");
});
</script>
</body>
</html><? } ?><script src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/welcome.js"></script>
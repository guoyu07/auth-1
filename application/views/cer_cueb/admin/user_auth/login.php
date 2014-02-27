<!DOCTYPE html PUBLIC "-//W3C//DTH XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTH/xhtml1-transitional.dTH">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>聚众合力</title>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<link href="http://127.0.0.1:8081/auth/assets/themes/default/styles/login.css" rel="stylesheet" type="text/css" /> 

</head>
<body>
<div id="append"></div><style>
.serLoginRight ul.serInput li span.sertext { letter-spacing:0.5em; }
</style>
<div class="container">
  <form action="http://127.0.0.1:8081/auth/index.php/admin/auth/login.html" method="post" target="_top"  onsubmit="return checkform(this);" id="loginform" name="loginform">
    <h2 style="display:none">
      聚众合力      后台管理</h2>
    <div class="serContBox">
      <div class="serLoginLeft"><img src="http://127.0.0.1:8081/auth/assets/themes/default/images/serimgl.jpg"/></div>
      <div class="serLoginRight">
        <h3>用户登录</h3>
        <ul class="serInput">
          <li> <span class="sertext">账  号:</span> <span class="serinbox">
            <input type="text" name="username" class="sertxt" id="username" value="">
            </span>
            <div class="clear"></div>
          </li>
          <li> <span class="sertext">密  码:</span> <span class="serinbox">
            <input type="password" name="password" class="sertxt" id="password" value="">
            </span>
            <div class="clear"></div>
          </li>
          <? if($show_captcha) { ?>          <li> <span class="sertext">验证码:</span> <span class="serinbox" style="position:relative">
            <input type="text" name="imgcode" class="sertxt" id="imgcode" value="" style="width:80px;"  onfocus="document.getElementById('showcode').style.display='';" />
            <div  id="showcode" CLASS="" style="position:absolute;top:0px;left:100px;display:none; cursor:pointer"> <img src="http://127.0.0.1:8081/auth/index.php/api/authcode/show.html" onclick="changcode(this);" alt="点击刷新" title="点击刷新" lang="http://127.0.0.1:8081/auth/index.php/api/authcode/show.html"/> </div>
            </span>
            <div class="clear"></div>
          </li>
          <? } ?>          <li> <span class="serbtl">
            <input type="hidden" name="dosubmit" value="1" />
            <input type="submit" name="submit" value="登&nbsp;录" class="serbtn" tabindex="3">
            </span> <span class="serbtr"> <a href="http://127.0.0.1:8081/auth/index.php/admin/auth/forgotpassword.html" style="line-height:35px;">忘记密码</a> </span>
            <div class="clear"></div>
          </li>
        </ul>
      </div>
    </div>
  </form>
</div><div class="footer">版权所有 © <a href="http://127.0.0.1:8081/auth/" target="_blank">聚众合力</a></div>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/jquery-1.4.2.js"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/artdialog-pake.js?skin=idialog"></script>
<script type="text/javascript" src="http://127.0.0.1:8081/auth/assets/themes/default/javascript/iframetools.source.js"></script> 
<script language="javascript">
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

  function checkform(theform) {
    if (theform.username.value == '') {
      art.dialog.tips('账号不能为空');
      return false;
    }
    if (theform.password.value == '') {
      art.dialog.tips('密码不能为空');
      return false;
    }
  }
  function changcode(obj) {
    obj.src = "http://127.0.0.1:8081/auth/index.php/api/authcode/show.html?" + Math.random()
  }
</script><? if(isset($_GET['msg']) && $_GET['msg']) { ?><script>
	art.dialog({ 	
		content:'<?=$_GET["msg"]?>',
		icon: 'error'
		}
	); 
</script><? } ?></body>
</html>
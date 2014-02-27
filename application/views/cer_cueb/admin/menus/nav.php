<ul>
  <li class="upl"><a href="http://127.0.0.1:8081/auth/" target="_blank">首页</a></li>
  <? if(isset($menus['1'])&&$menus['1']) { ?>  <li class="upl" id="help"><a name="nav01" href="http://127.0.0.1:8081/auth/index.php/admin/help.html">使用手册</a> <b></b>
    <ul style="display: none; ">
      <? if(is_array($menus['1'])) { foreach($menus['1'] as $k => $v) { ?>      <li><a href="<?=$v['url']?>"><?=$v['subject']?></a></li>
      <? } } ?>    </ul>
  </li>
  <? } ?>  <? if(isset($menus['2'])&&$menus['2']) { ?>  <li class="upl"  id="document" style="display: none; "><a name="nav01" href="http://127.0.0.1:8081/auth/index.php/admin/document.html">开发手册</a> <b></b>
    <ul style="display: none; ">
      <? if(is_array($menus['2'])) { foreach($menus['2'] as $k => $v) { ?>      <li><a href="<?=$v['url']?>"><?=$v['subject']?></a></li>
      <? } } ?>    </ul>
  </li>
  <? } ?>  <? if(isset($menus['3'])&&$menus['3']) { ?>  <li class="upl" id="appuser"><a name="nav07" href="#">用户管理</a>
    <ul style="display: none; ">
      <? if(is_array($menus['3'])) { foreach($menus['3'] as $k => $v) { ?>      <li><a href="<?=$v['url']?>" ><?=$v['subject']?></a></li>
      <? } } ?>    </ul>
  </li>
  <? } ?>  <? if(isset($menus['4'])&&$menus['4']) { ?>  <li class="upl" id="authaction"  style="display: none; "><a name="nav01" href="#">用户中心</a><b></b>
    <ul style="display: none; ">
      <? if(is_array($menus['4'])) { foreach($menus['4'] as $k => $v) { ?>      <li><a href="<?=$v['url']?>"><?=$v['subject']?></a></li>
      <? } } ?>    </ul>
  </li>
  <? } ?>  <? if(isset($menus['5'])&&$menus['5']) { ?>  <li class="upl" id="openoauth"><a name="nav07" href="#">开放平台</a>
    <ul style="display: none; ">
      <? if(is_array($menus['5'])) { foreach($menus['5'] as $k => $v) { ?>      <li><a href="<?=$v['url']?>"><?=$v['subject']?></a></li>
      <? } } ?>    </ul>
  </li>
  <? } ?>  <li class="upl" id="system"><a name="nav07" href="#">系统</a>
    <ul style="display: none; ">
      <? if(isset($menus['6'])&&$menus['6']) { ?>      <? if(is_array($menus['6'])) { foreach($menus['6'] as $k => $v) { ?>      <? if($v['subject']=='模板缓存') { ?>     <li><a rel="group" href="http://127.0.0.1:8081/auth/index.php/admin/templates/cache.html" onclick="cachetemplates('http://127.0.0.1:8081/auth/index.php/admin/templates/cache/ajax.html?format=json');return false;" >模板缓存</a></li>
      <? } else { ?>      <li><a href="<?=$v['url']?>"><?=$v['subject']?></a></li>
      <? } ?>      <? } } ?>      <? } ?>      <li><a rel="group" href="http://127.0.0.1:8081/auth/index.php/admin/auth/logout.html">退出系统</a></li>
    </ul>
  </li>
</ul>
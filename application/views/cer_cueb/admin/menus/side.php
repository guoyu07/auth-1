<? function shownavs($datas,$ts='display:block'){  ?><ul>
  <? if(is_array($datas)) { foreach($datas as $k => $v) { ?>  <? if(isset($v['children'])&&$v['children']) { ?>  <li class="upl"> <a class="sidelist" href="http://127.0.0.1:8081/auth/index.php/admin/content/getcontent/<?=$v['id']?>.html?parentid=1" ><?=$v['catname']?></a>
    <? shownavs($v['children'],'display:none') ?>    <? } else { ?>  <li> <a class="sidelist" href="http://127.0.0.1:8081/auth/index.php/admin/content/getcontent/<?=$v['id']?>.html?parentid=1" ><?=$v['catname']?></a>
    <? } ?>  </li>
  <? } } ?></ul><? }shownavs($datalist) ?>
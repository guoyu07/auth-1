<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}

$config ['extend'] ['id']['type'] ='int' ;
$config ['extend'] ['id']['constraint'] ='8' ;
$config ['extend'] ['id']['unsigned'] =TRUE ; 

$config ['extend'] ['links']['type']='varchar';
$config ['extend'] ['links']['constraint']='300';
//$config ['extend'] ['links']['default']='0';
$config ['extend'] ['links']['comment']='链接';
$config ['extend'] ['links']['null']=true;

$config ['extend'] ['islink']['type']='tinyint'; 
$config ['extend'] ['islink']['constraint']='1'; 
$config ['extend'] ['islink']['unsigned']=TRUE; 
//$config ['extend'] ['islink']['default']='0'; 
$config ['extend'] ['islink']['comment']='是否连接'; 
$config ['extend'] ['islink']['null']=true; 

$config ['extend'] ['groupids_view']['type']='tinyint';
$config ['extend'] ['groupids_view']['constraint']='11';
$config ['extend'] ['groupids_view']['unsigned']=TRUE; 
$config ['extend'] ['groupids_view']['default']='0';
$config ['extend'] ['groupids_view']['comment']='浏览组'; 
$config ['extend'] ['groupids_view']['null']=true; 

$config ['extend'] ['paginationtype']['type']='tinyint';
$config ['extend'] ['paginationtype']['constraint']='1';
$config ['extend'] ['paginationtype']['default']='0';
$config ['extend'] ['paginationtype']['null']=true;
 
$config ['extend'] ['maxcharperpage']['type']='mediumint';
$config ['extend'] ['maxcharperpage']['constraint']='6';
$config ['extend'] ['maxcharperpage']['default']='0';
$config ['extend'] ['maxcharperpage']['null']=true;


$config ['extend'] ['template']['type']='char';
$config ['extend'] ['template']['constraint']='38';
//$config ['extend'] ['template']['default']='0';
$config ['extend'] ['template']['comment']='模板'; 
$config ['extend'] ['template']['null']=true;

$config ['extend'] ['pages']['type']='tinyint';
$config ['extend'] ['pages']['constraint']='2';
$config ['extend'] ['pages']['default']='0';
$config ['extend'] ['pages']['null']=true;

$config ['extend'] ['allow_comment']['type']='tinyint';
$config ['extend'] ['allow_comment']['constraint']='1';
$config ['extend'] ['allow_comment']['default']='0';
$config ['extend'] ['allow_comment']['comment']='是非评论'; 
$config ['extend'] ['allow_comment']['null']=true; 

$config ['extend'] ['copyfrom']['type']='varchar';
$config ['extend'] ['copyfrom']['constraint']='300';
//$config ['extend'] ['copyfrom']['default']='0';
$config ['extend'] ['copyfrom']['comment']='来源'; 
$config ['extend'] ['copyfrom']['null']=true; 


$config ['extend'] ['copyfromurl']['type']='varchar';
$config ['extend'] ['copyfromurl']['constraint']='300';
//$config ['extend'] ['copyfromurl']['default']='0';
$config ['extend'] ['copyfromurl']['comment']='来源URL'; 
$config ['extend'] ['copyfromurl']['null']=true; 

$config ['extend'] ['keywords']['type']='varchar';
$config ['extend'] ['keywords']['constraint']='500';
//$config ['extend'] ['keywords']['default']='0';
$config ['extend'] ['keywords']['comment']='关键字'; 
$config ['extend'] ['keywords']['null']=true; 

$config ['extend'] ['keywords']['type']='varchar';
$config ['extend'] ['keywords']['constraint']='500';
$config ['extend'] ['keywords']['default']='0';
//$config ['extend'] ['keywords']['comment']='关键字'; 
$config ['extend'] ['keywords']['null']=true; 

$config ['extend'] ['description']['type']='varchar';
$config ['extend'] ['description']['constraint']='500';
//$config ['extend'] ['description']['default']='0';
$config ['extend'] ['description']['comment']='描述'; 
$config ['extend'] ['description']['null']=true; 

$config ['extend'] ['content']['type']='mediumtext'; 
$config ['extend'] ['content']['comment']='内容';  
$config ['extend'] ['content']['null']=true;

$config ['extendfield'] ['links'] ['field']='links';
$config ['extendfield'] ['links'] ['constraint']='200';
$config ['extendfield'] ['links'] ['name']='链接'; 
$config ['extendfield'] ['links'] ['minlength']='1';
$config ['extendfield'] ['links'] ['maxlength']='300';
$config ['extendfield'] ['links'] ['pattern']='';
$config ['extendfield'] ['links'] ['errortips']='请输入链接';
$config ['extendfield'] ['links'] ['formtype']='url'; 
$config ['extendfield'] ['links'] ['iscore']='1';
$config ['extendfield'] ['links'] ['issystem']='0';
$config ['extendfield'] ['links'] ['isunique']='0';
$config ['extendfield'] ['links'] ['isbase']='0'; 
$config ['extendfield'] ['links'] ['isadd']='1'; 
$config ['extendfield'] ['links'] ['isposition']='0';
$config ['extendfield'] ['links'] ['listorder']='11';
$config ['extendfield'] ['links'] ['disabled']='0'; 
$config ['extendfield'] ['links'] ['datatypes']='varchar'; 

$config ['extendfield'] ['islink'] ['field']='islink';
$config ['extendfield'] ['islink'] ['constraint']='1';
$config ['extendfield'] ['islink'] ['name']='是否连接'; 
$config ['extendfield'] ['islink'] ['minlength']='1';
$config ['extendfield'] ['islink'] ['maxlength']='2';
$config ['extendfield'] ['islink'] ['pattern']='';
$config ['extendfield'] ['islink'] ['errortips']='请选择是否连接';
$config ['extendfield'] ['islink'] ['formtype']='islink'; 
$config ['extendfield'] ['islink'] ['iscore']='1';
$config ['extendfield'] ['islink'] ['issystem']='0';
$config ['extendfield'] ['islink'] ['isunique']='0';
$config ['extendfield'] ['islink'] ['isbase']='0'; 
$config ['extendfield'] ['islink'] ['isadd']='1'; 
$config ['extendfield'] ['islink'] ['isposition']='0';
$config ['extendfield'] ['islink'] ['listorder']='10';
$config ['extendfield'] ['islink'] ['disabled']='0'; 
$config ['extendfield'] ['islink'] ['datatypes']='tinyint'; 


$config ['extendfield'] ['groupids_view'] ['field']='groupids_view';
$config ['extendfield'] ['groupids_view'] ['constraint']='1';
$config ['extendfield'] ['groupids_view'] ['name']='浏览组'; 
$config ['extendfield'] ['groupids_view'] ['minlength']='1';
$config ['extendfield'] ['groupids_view'] ['maxlength']='2';
$config ['extendfield'] ['groupids_view'] ['pattern']='';
$config ['extendfield'] ['groupids_view'] ['errortips']='请选择浏览组';
$config ['extendfield'] ['groupids_view'] ['formtype']='groupid'; 
$config ['extendfield'] ['groupids_view'] ['iscore']='1';
$config ['extendfield'] ['groupids_view'] ['issystem']='0';
$config ['extendfield'] ['groupids_view'] ['isunique']='0';
$config ['extendfield'] ['groupids_view'] ['isbase']='0'; 
$config ['extendfield'] ['groupids_view'] ['isadd']='1'; 
$config ['extendfield'] ['groupids_view'] ['isposition']='0';
$config ['extendfield'] ['groupids_view'] ['listorder']='14';
$config ['extendfield'] ['groupids_view'] ['disabled']='0'; 
$config ['extendfield'] ['groupids_view'] ['datatypes']='tinyint'; 


$config ['extendfield'] ['paginationtype'] ['field']='paginationtype';
$config ['extendfield'] ['paginationtype'] ['constraint']='1';
$config ['extendfield'] ['paginationtype'] ['name']='分页类型'; 
$config ['extendfield'] ['paginationtype'] ['minlength']='1';
$config ['extendfield'] ['paginationtype'] ['maxlength']='2';
$config ['extendfield'] ['paginationtype'] ['pattern']='';
$config ['extendfield'] ['paginationtype'] ['errortips']='请选择分页类型';
$config ['extendfield'] ['paginationtype'] ['formtype']='groupid'; 
$config ['extendfield'] ['paginationtype'] ['iscore']='1';
$config ['extendfield'] ['paginationtype'] ['issystem']='0';
$config ['extendfield'] ['paginationtype'] ['isunique']='0';
$config ['extendfield'] ['paginationtype'] ['isbase']='0'; 
$config ['extendfield'] ['paginationtype'] ['isadd']='1'; 
$config ['extendfield'] ['paginationtype'] ['isposition']='0';
$config ['extendfield'] ['paginationtype'] ['listorder']='1';
$config ['extendfield'] ['paginationtype'] ['disabled']='0'; 
$config ['extendfield'] ['paginationtype'] ['datatypes']='tinyint'; 

$config ['extendfield'] ['maxcharperpage'] ['field']='maxcharperpage';
$config ['extendfield'] ['maxcharperpage'] ['constraint']='1';
$config ['extendfield'] ['maxcharperpage'] ['name']='浏览组'; 
$config ['extendfield'] ['maxcharperpage'] ['minlength']='1';
$config ['extendfield'] ['maxcharperpage'] ['maxlength']='2';
$config ['extendfield'] ['maxcharperpage'] ['pattern']='';
$config ['extendfield'] ['maxcharperpage'] ['errortips']='请选择浏览组';
$config ['extendfield'] ['maxcharperpage'] ['formtype']='groupid'; 
$config ['extendfield'] ['maxcharperpage'] ['iscore']='1';
$config ['extendfield'] ['maxcharperpage'] ['issystem']='0';
$config ['extendfield'] ['maxcharperpage'] ['isunique']='0';
$config ['extendfield'] ['maxcharperpage'] ['isbase']='0'; 
$config ['extendfield'] ['maxcharperpage'] ['isadd']='1'; 
$config ['extendfield'] ['maxcharperpage'] ['isposition']='0';
$config ['extendfield'] ['maxcharperpage'] ['listorder']='1';
$config ['extendfield'] ['maxcharperpage'] ['disabled']='0'; 
$config ['extendfield'] ['maxcharperpage'] ['datatypes']='mediumint'; 


$config ['extendfield'] ['template'] ['field']='template';
$config ['extendfield'] ['template'] ['constraint']='1';
$config ['extendfield'] ['template'] ['name']='模板'; 
$config ['extendfield'] ['template'] ['minlength']='1';
$config ['extendfield'] ['template'] ['maxlength']='8';
$config ['extendfield'] ['template'] ['pattern']='';
$config ['extendfield'] ['template'] ['errortips']='请选择模板';
$config ['extendfield'] ['template'] ['formtype']='template'; 
$config ['extendfield'] ['template'] ['iscore']='1';
$config ['extendfield'] ['template'] ['issystem']='0';
$config ['extendfield'] ['template'] ['isunique']='0';
$config ['extendfield'] ['template'] ['isbase']='0'; 
$config ['extendfield'] ['template'] ['isadd']='1'; 
$config ['extendfield'] ['template'] ['isposition']='0';
$config ['extendfield'] ['template'] ['listorder']='15';
$config ['extendfield'] ['template'] ['disabled']='0'; 
$config ['extendfield'] ['template'] ['datatypes']='tinyint'; 


$config ['extendfield'] ['pages'] ['field']='pages';
$config ['extendfield'] ['pages'] ['constraint']='1';
$config ['extendfield'] ['pages'] ['name']='模板'; 
$config ['extendfield'] ['pages'] ['minlength']='1';
$config ['extendfield'] ['pages'] ['maxlength']='8';
$config ['extendfield'] ['pages'] ['pattern']='';
$config ['extendfield'] ['pages'] ['errortips']='请选择模板';
$config ['extendfield'] ['pages'] ['formtype']='pages'; 
$config ['extendfield'] ['pages'] ['iscore']='1';
$config ['extendfield'] ['pages'] ['issystem']='0';
$config ['extendfield'] ['pages'] ['isunique']='0';
$config ['extendfield'] ['pages'] ['isbase']='0'; 
$config ['extendfield'] ['pages'] ['isadd']='1'; 
$config ['extendfield'] ['pages'] ['isposition']='0';
$config ['extendfield'] ['pages'] ['listorder']='1';
$config ['extendfield'] ['pages'] ['disabled']='0'; 
$config ['extendfield'] ['pages'] ['datatypes']='tinyint'; 

$config ['extendfield'] ['copyfrom'] ['field']='copyfrom';
$config ['extendfield'] ['copyfrom'] ['constraint']='300';
$config ['extendfield'] ['copyfrom'] ['name']='来源'; 
$config ['extendfield'] ['copyfrom'] ['minlength']='1';
$config ['extendfield'] ['copyfrom'] ['maxlength']='300';
$config ['extendfield'] ['copyfrom'] ['pattern']='';
$config ['extendfield'] ['copyfrom'] ['errortips']='来源';
$config ['extendfield'] ['copyfrom'] ['formtype']='text'; 
$config ['extendfield'] ['copyfrom'] ['iscore']='1';
$config ['extendfield'] ['copyfrom'] ['issystem']='0';
$config ['extendfield'] ['copyfrom'] ['isunique']='0';
$config ['extendfield'] ['copyfrom'] ['isbase']='0'; 
$config ['extendfield'] ['copyfrom'] ['isadd']='1'; 
$config ['extendfield'] ['copyfrom'] ['isposition']='0';
$config ['extendfield'] ['copyfrom'] ['listorder']='12';
$config ['extendfield'] ['copyfrom'] ['disabled']='0'; 
$config ['extendfield'] ['copyfrom'] ['datatypes']='varchar'; 


$config ['extendfield'] ['copyfromurl'] ['field']='copyfromurl';
$config ['extendfield'] ['copyfromurl'] ['constraint']='300';
$config ['extendfield'] ['copyfromurl'] ['name']='来源URL'; 
$config ['extendfield'] ['copyfromurl'] ['minlength']='1';
$config ['extendfield'] ['copyfromurl'] ['maxlength']='300';
$config ['extendfield'] ['copyfromurl'] ['pattern']='';
$config ['extendfield'] ['copyfromurl'] ['errortips']='来源URL';
$config ['extendfield'] ['copyfromurl'] ['formtype']='url'; 
$config ['extendfield'] ['copyfromurl'] ['iscore']='1';
$config ['extendfield'] ['copyfromurl'] ['issystem']='0';
$config ['extendfield'] ['copyfromurl'] ['isunique']='0';
$config ['extendfield'] ['copyfromurl'] ['isbase']='0'; 
$config ['extendfield'] ['copyfromurl'] ['isadd']='1'; 
$config ['extendfield'] ['copyfromurl'] ['isposition']='0';
$config ['extendfield'] ['copyfromurl'] ['listorder']='13';
$config ['extendfield'] ['copyfromurl'] ['disabled']='0'; 
$config ['extendfield'] ['copyfromurl'] ['datatypes']='varchar'; 

$config ['extendfield'] ['keywords'] ['field']='keywords';
$config ['extendfield'] ['keywords'] ['constraint']='500';
$config ['extendfield'] ['keywords'] ['name']='关键字'; 
$config ['extendfield'] ['keywords'] ['minlength']='1';
$config ['extendfield'] ['keywords'] ['maxlength']='500';
$config ['extendfield'] ['keywords'] ['pattern']='';
$config ['extendfield'] ['keywords'] ['errortips']='关键字';
$config ['extendfield'] ['keywords'] ['formtype']='textarea'; 
$config ['extendfield'] ['keywords'] ['iscore']='1';
$config ['extendfield'] ['keywords'] ['issystem']='0';
$config ['extendfield'] ['keywords'] ['isunique']='0';
$config ['extendfield'] ['keywords'] ['isbase']='0'; 
$config ['extendfield'] ['keywords'] ['isadd']='1'; 
$config ['extendfield'] ['keywords'] ['isposition']='0';
$config ['extendfield'] ['keywords'] ['listorder']='16';
$config ['extendfield'] ['keywords'] ['disabled']='0'; 
$config ['extendfield'] ['keywords'] ['datatypes']='varchar'; 

$config ['extendfield'] ['description'] ['field']='description';
$config ['extendfield'] ['description'] ['constraint']='500';
$config ['extendfield'] ['description'] ['name']='描述'; 
$config ['extendfield'] ['description'] ['minlength']='1';
$config ['extendfield'] ['description'] ['maxlength']='500';
$config ['extendfield'] ['description'] ['pattern']='';
$config ['extendfield'] ['description'] ['errortips']='描述';
$config ['extendfield'] ['description'] ['formtype']='textarea'; 
$config ['extendfield'] ['description'] ['iscore']='1';
$config ['extendfield'] ['description'] ['issystem']='0';
$config ['extendfield'] ['description'] ['isunique']='0';
$config ['extendfield'] ['description'] ['isbase']='0'; 
$config ['extendfield'] ['description'] ['isadd']='1'; 
$config ['extendfield'] ['description'] ['isposition']='0';
$config ['extendfield'] ['description'] ['listorder']='17';
$config ['extendfield'] ['description'] ['disabled']='0'; 
$config ['extendfield'] ['description'] ['datatypes']='varchar'; 

$config ['extendfield'] ['content'] ['field']='content';
$config ['extendfield'] ['content'] ['constraint']='';
$config ['extendfield'] ['content'] ['name']='内容'; 
$config ['extendfield'] ['content'] ['minlength']='1';
$config ['extendfield'] ['content'] ['maxlength']='1000';
$config ['extendfield'] ['content'] ['pattern']='';
$config ['extendfield'] ['content'] ['errortips']='内容';
$config ['extendfield'] ['content'] ['formtype']='editor'; 
$config ['extendfield'] ['content'] ['iscore']='1';
$config ['extendfield'] ['content'] ['issystem']='0';
$config ['extendfield'] ['content'] ['isunique']='0';
$config ['extendfield'] ['content'] ['isbase']='1'; 
$config ['extendfield'] ['content'] ['isadd']='1'; 
$config ['extendfield'] ['content'] ['isposition']='0';
$config ['extendfield'] ['content'] ['listorder']='6';
$config ['extendfield'] ['content'] ['disabled']='0'; 
$config ['extendfield'] ['content'] ['datatypes']='text'; 




 
$config ['basic'] ['id']['type']='int';
$config ['basic'] ['id']['constraint']='8';
$config ['basic'] ['id']['unsigned']=TRUE;
$config ['basic'] ['id']['auto_increment']=TRUE;
$config ['basic'] ['id']['comment']='ID';  

$config ['basic'] ['columnid']['type']='char';
$config ['basic'] ['columnid']['constraint']='38';
$config ['basic'] ['columnid']['unsigned']=FALSE;
//$config ['basic'] ['columnid']['default']=0;
$config ['basic'] ['columnid']['comment']='栏目'; 
$config ['basic'] ['columnid']['null']=true; 

$config ['basic_s'] ['typeid']['type']='smallint';
$config ['basic_s'] ['typeid']['constraint']='5';
$config ['basic_s'] ['typeid']['unsigned']=TRUE;
$config ['basic_s'] ['typeid']['default']=0;
$config ['basic_s'] ['typeid']['comment']='类别'; 
$config ['basic_s'] ['typeid']['null']=true; 

$config ['basic'] ['title']['type']='varchar';
$config ['basic'] ['title']['constraint']='200'; 
//$config ['basic'] ['title']['default']=0;
$config ['basic'] ['title']['comment']='标题'; 
$config ['basic'] ['title']['null']=true; 

$config ['basic'] ['style']['type']='varchar';
$config ['basic'] ['style']['constraint']='80'; 
//$config ['basic'] ['style']['default']=0;
$config ['basic'] ['style']['comment']='风格'; 
$config ['basic'] ['style']['null']=true; 

$config ['basic'] ['thumb']['type']='varchar';
$config ['basic'] ['thumb']['constraint']='300'; 
//$config ['basic'] ['thumb']['default']=0;
$config ['basic'] ['thumb']['comment']='图片'; 
$config ['basic'] ['thumb']['null']=true; 

$config ['basic'] ['url']['type']='varchar';
$config ['basic'] ['url']['constraint']='200'; 
//$config ['basic'] ['url']['default']=0;
$config ['basic'] ['url']['comment']='URL'; 
$config ['basic'] ['url']['null']=true; 

 
$config ['basic'] ['listorder']['type']='tinyint';
$config ['basic'] ['listorder']['constraint']='5'; 
$config ['basic'] ['listorder']['unsigned']=TRUE; 
$config ['basic'] ['listorder']['default']=0;
$config ['basic'] ['listorder']['comment']='排序'; 
$config ['basic'] ['listorder']['null']=true;

$config ['basic'] ['status']['type']='tinyint';
$config ['basic'] ['status']['constraint']='2'; 
$config ['basic'] ['status']['unsigned']=TRUE; 
$config ['basic'] ['status']['default']=0;
$config ['basic'] ['status']['comment']='状态'; 
$config ['basic'] ['status']['null']=true;

$config ['basic'] ['summary']['type']='varchar';
$config ['basic'] ['summary']['constraint']='500';  
//$config ['basic'] ['summary']['default']=0;
$config ['basic'] ['summary']['comment']='简介'; 
$config ['basic'] ['summary']['null']=true;

$config ['basic'] ['summary']['type']='varchar';
$config ['basic'] ['summary']['constraint']='500';  
//$config ['basic'] ['summary']['default']=0;
$config ['basic'] ['summary']['comment']='简介'; 
$config ['basic'] ['summary']['null']=true;
 
$config ['basic'] ['datetime']['type']='int';
$config ['basic'] ['datetime']['constraint']='11'; 
$config ['basic'] ['datetime']['unsigned']=TRUE; 
$config ['basic'] ['datetime']['default']=0;
$config ['basic'] ['datetime']['comment']='添加时间'; 
$config ['basic'] ['datetime']['null']=true;

$config ['basic'] ['updatetime']['type']='int';
$config ['basic'] ['updatetime']['constraint']='11'; 
$config ['basic'] ['updatetime']['unsigned']=TRUE; 
$config ['basic'] ['updatetime']['default']=0;
$config ['basic'] ['updatetime']['comment']='更新时间'; 
$config ['basic'] ['updatetime']['null']=true;

$config ['basic'] ['username']['type']='varchar';
$config ['basic'] ['username']['constraint']='300';  
//$config ['basic'] ['username']['default']=0;
$config ['basic'] ['username']['comment']='用户名'; 
$config ['basic'] ['username']['null']=true;

$config ['basic'] ['uid']['type']='char';
$config ['basic'] ['uid']['constraint']='38'; 
$config ['basic'] ['uid']['unsigned']=FALSE; 
//$config ['basic'] ['uid']['default']=0;
$config ['basic'] ['uid']['comment']='用户ID'; 
$config ['basic'] ['uid']['null']=true;

$config ['basic'] ['upusername']['type']='varchar';
$config ['basic'] ['upusername']['constraint']='300';  
//$config ['basic'] ['upusername']['default']=0;
$config ['basic'] ['upusername']['comment']='更新用户名'; 
$config ['basic'] ['upusername']['null']=true;

$config ['basic'] ['upuid']['type']='char';
$config ['basic'] ['upuid']['constraint']='38'; 
$config ['basic'] ['upuid']['unsigned']=FALSE; 
//$config ['basic'] ['upuid']['default']=0;
$config ['basic'] ['upuid']['comment']='更新用户ID'; 
$config ['basic'] ['upuid']['null']=true;

$config ['basic'] ['pageview']['type']='int';
$config ['basic'] ['pageview']['constraint']='11'; 
$config ['basic'] ['pageview']['unsigned']=TRUE; 
$config ['basic'] ['pageview']['default']=0;
$config ['basic'] ['pageview']['comment']='浏览量'; 
$config ['basic'] ['pageview']['null']=true;

$config ['basicfield'] ['title'] ['field']='title';
$config ['basicfield'] ['title'] ['constraint']='200';
$config ['basicfield'] ['title'] ['name']='标题'; 
$config ['basicfield'] ['title'] ['minlength']='1';
$config ['basicfield'] ['title'] ['maxlength']='200';
$config ['basicfield'] ['title'] ['pattern']='required|xss';
$config ['basicfield'] ['title'] ['errortips']='请输入标题';
$config ['basicfield'] ['title'] ['formtype']='text'; 
$config ['basicfield'] ['title'] ['iscore']='1';
$config ['basicfield'] ['title'] ['issystem']='1';
$config ['basicfield'] ['title'] ['isunique']='0';
$config ['basicfield'] ['title'] ['isbase']='1'; 
$config ['basicfield'] ['title'] ['isadd']='1'; 
$config ['basicfield'] ['title'] ['isposition']='1';
$config ['basicfield'] ['title'] ['listorder']='1';
$config ['basicfield'] ['title'] ['disabled']='0'; 
$config ['basicfield'] ['title'] ['datatypes']='varchar'; 

 
$config ['basicfield'] ['style'] ['field']='style';
$config ['basicfield'] ['style'] ['constraint']='80';
$config ['basicfield'] ['style'] ['name']='风格'; 
$config ['basicfield'] ['style'] ['minlength']='2';
$config ['basicfield'] ['style'] ['maxlength']='80';
$config ['basicfield'] ['style'] ['pattern']=''; 
$config ['basicfield'] ['style'] ['errortips']='请输入风格';
$config ['basicfield'] ['style'] ['formtype']='style'; 
$config ['basicfield'] ['style'] ['iscore']='1';
$config ['basicfield'] ['style'] ['issystem']='1';
$config ['basicfield'] ['style'] ['isunique']='0';
$config ['basicfield'] ['style'] ['isbase']='1'; 
$config ['basicfield'] ['style'] ['isadd']='1'; 
$config ['basicfield'] ['style'] ['isposition']='0';
$config ['basicfield'] ['style'] ['listorder']='1'; 
$config ['basicfield'] ['style'] ['disabled']='0'; 
$config ['basicfield'] ['style'] ['datatypes']='varchar'; 


$config ['basicfield_s'] ['typeid'] ['field']='typeid';
$config ['basicfield_s'] ['typeid'] ['constraint']='3';
$config ['basicfield_s'] ['typeid'] ['name']='类别'; 
$config ['basicfield_s'] ['typeid'] ['minlength']='1';
$config ['basicfield_s'] ['typeid'] ['maxlength']='100';
$config ['basicfield_s'] ['typeid'] ['pattern']=''; 
$config ['basicfield_s'] ['typeid'] ['errortips']='请选择类别';
$config ['basicfield_s'] ['typeid'] ['formtype']='typeid'; 
$config ['basicfield_s'] ['typeid'] ['iscore']='1';
$config ['basicfield_s'] ['typeid'] ['issystem']='1';
$config ['basicfield_s'] ['typeid'] ['isunique']='0';
$config ['basicfield_s'] ['typeid'] ['isbase']='1'; 
$config ['basicfield_s'] ['typeid'] ['isadd']='1'; 
$config ['basicfield_s'] ['typeid'] ['isposition']='0';
$config ['basicfield_s'] ['typeid'] ['listorder']='1'; 
$config ['basicfield_s'] ['typeid'] ['disabled']='0'; 
$config ['basicfield_s'] ['typeid'] ['datatypes']='smallint'; 


$config ['basicfield'] ['thumb'] ['field']='thumb';
$config ['basicfield'] ['thumb'] ['constraint']='300';
$config ['basicfield'] ['thumb'] ['name']='缩略图'; 
$config ['basicfield'] ['thumb'] ['minlength']='4';
$config ['basicfield'] ['thumb'] ['maxlength']='300';
$config ['basicfield'] ['thumb'] ['pattern']=''; 
$config ['basicfield'] ['thumb'] ['errortips']='请上传图片';
$config ['basicfield'] ['thumb'] ['formtype']='image'; 
$config ['basicfield'] ['thumb'] ['iscore']='1';
$config ['basicfield'] ['thumb'] ['issystem']='1';
$config ['basicfield'] ['thumb'] ['isunique']='0';
$config ['basicfield'] ['thumb'] ['isbase']='1'; 
$config ['basicfield'] ['thumb'] ['isadd']='1'; 
$config ['basicfield'] ['thumb'] ['isposition']='0';
$config ['basicfield'] ['thumb'] ['listorder']='1'; 
$config ['basicfield'] ['thumb'] ['disabled']='0'; 
$config ['basicfield'] ['thumb'] ['datatypes']='varchar'; 

$config ['basicfield'] ['url'] ['field']='url';
$config ['basicfield'] ['url'] ['constraint']='200';
$config ['basicfield'] ['url'] ['name']='URL'; 
$config ['basicfield'] ['url'] ['minlength']='1';
$config ['basicfield'] ['url'] ['maxlength']='200';
$config ['basicfield'] ['url'] ['pattern']=''; 
$config ['basicfield'] ['url'] ['errortips']='请输入URL';
$config ['basicfield'] ['url'] ['formtype']='url'; 
$config ['basicfield'] ['url'] ['iscore']='0';
$config ['basicfield'] ['url'] ['issystem']='1';
$config ['basicfield'] ['url'] ['isunique']='1';
$config ['basicfield'] ['url'] ['isbase']='0'; 
$config ['basicfield'] ['url'] ['isadd']='1'; 
$config ['basicfield'] ['url'] ['isposition']='0';
$config ['basicfield'] ['url'] ['listorder']='9'; 
$config ['basicfield'] ['url'] ['disabled']='0'; 
$config ['basicfield'] ['url'] ['datatypes']='varchar'; 

$config ['basicfield'] ['listorder'] ['field']='listorder';
$config ['basicfield'] ['listorder'] ['constraint']='5';
$config ['basicfield'] ['listorder'] ['name']='排序'; 
$config ['basicfield'] ['listorder'] ['minlength']='1';
$config ['basicfield'] ['listorder'] ['maxlength']='100';
$config ['basicfield'] ['listorder'] ['pattern']='numeric'; 
$config ['basicfield'] ['listorder'] ['errortips']='请输入排序';
$config ['basicfield'] ['listorder'] ['formtype']='text'; 
$config ['basicfield'] ['listorder'] ['iscore']='1';
$config ['basicfield'] ['listorder'] ['issystem']='1';
$config ['basicfield'] ['listorder'] ['isunique']='0';
$config ['basicfield'] ['listorder'] ['isbase']='0'; 
$config ['basicfield'] ['listorder'] ['isadd']='1'; 
$config ['basicfield'] ['listorder'] ['isposition']='0';
$config ['basicfield'] ['listorder'] ['listorder']='7'; 
$config ['basicfield'] ['listorder'] ['disabled']='0'; 
$config ['basicfield'] ['listorder'] ['datatypes']='tinyint'; 

 


$config ['basicfield'] ['summary'] ['field']='summary';
$config ['basicfield'] ['summary'] ['constraint']='500';
$config ['basicfield'] ['summary'] ['name']='简介'; 
$config ['basicfield'] ['summary'] ['minlength']='1';
$config ['basicfield'] ['summary'] ['maxlength']='500';
$config ['basicfield'] ['summary'] ['pattern']=''; 
$config ['basicfield'] ['summary'] ['errortips']='请输入简介';
$config ['basicfield'] ['summary'] ['formtype']='textarea'; 
$config ['basicfield'] ['summary'] ['iscore']='1';
$config ['basicfield'] ['summary'] ['issystem']='1';
$config ['basicfield'] ['summary'] ['isunique']='0';
$config ['basicfield'] ['summary'] ['isbase']='1'; 
$config ['basicfield'] ['summary'] ['isadd']='1'; 
$config ['basicfield'] ['summary'] ['isposition']='0';
$config ['basicfield'] ['summary'] ['listorder']='5'; 
$config ['basicfield'] ['summary'] ['disabled']='0'; 
$config ['basicfield'] ['summary'] ['datatypes']='varchar'; 


 
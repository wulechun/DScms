<?php

namespace app\common\taglib;

use think\template\TagLib;
use think\facade\Db;

class Dstaglib extends TagLib {

    /**
     * 定义标签列表
     */
    protected $tags = [
        'adv' => ['attr' => 'limit,order,where,item', 'close' => 1],
        'channel' => ['attr' => 'refObj,intag,typeid,reid,row,col,type,currentstyle,cacheid', 'close' => 1],
        'arclist' => ['attr' => 'refObj,intag,col,row,typeid,notypeid,getall,titlelen,infolen,imgwidth,imgheight,listtype,orderby,keyword,innertext,aid,idlist,channelid,limit,flag,noflag,orderway,subday', 'close' => 1],
        'flink' => ['attr' => 'refObj,type,row,titlelen,linktype,typeid', 'close' => 1],
        'type' => ['attr' => 'refObj,intag,typeid', 'close' => 1],
        'channelartlist' => ['attr' => 'refObj,item,typeid,notypeid,row', 'close' => 1],
        'prenext' => ['attr' => 'refObj,get', 'close' => 0],
    ];

    public function tagAdv($tag, $content) {
        $order = !empty($tag['order']) ? $tag['order'] : ''; //排序
        $limit = !empty($tag['limit']) ? $tag['limit'] : '1';
        $where = !empty($tag['where']) ? $tag['where'] : ''; //查询条件
        $item = !empty($tag['item']) ? $tag['item'] : 'item'; // 返回的变量item	
        $key = !empty($tag['key']) ? $tag['key'] : 'key'; // 返回的变量key
        $ap_id = !empty($tag['ap_id']) ? $tag['ap_id'] : '0'; // 返回的变量key

        $str = '<?php ';
        $str .= '$ap_id =' . $ap_id . ';';
        $str .= '$ad_position = \think\facade\Db::name("advposition")->cache(3600)->column("ap_id,ap_name,ap_width,ap_height","ap_id");';
        $str .= '$result = \think\facade\Db::name("adv")->where(array(array("ap_id","=",$ap_id),array("adv_enabled", "=", 1)))->order("adv_order desc")->cache(36000)->limit(' . $limit . ')->select()->toArray();';
        $str .= '
if(!in_array($ap_id,array_keys($ad_position)) && $ap_id)
{
  \think\facade\Db::name("advposition")->insert(array(
         "ap_id"=>$ap_id,
         "ap_name"=>"home/".request()->controller()."/".request()->action()."页面自动增加广告位 $ap_id ",
  ));
  $ad_position[$ap_id]=array();
  \think\facade\Cache::clear();  
}


$c = ' . $limit . '- count($result); //  如果要求数量 和实际数量不一样 并且编辑模式
if($c > 0 && input("get.edit_ad"))
{
    for($i = 0; $i < $c; $i++) // 还没有添加广告的时候
    {
      $result[] = array(
          "adv_id" => 0,
          "adv_code" => "/public/images/not_adv.jpg",
          "adv_link" => ADMIN_SITE_URL."/Adv/adv_add/ap_id/$ap_id.html",
          "adv_title"   =>"暂无广告图片",
          "not_adv" => 1,
          "adv_target" => "_self",
          "ap_id"   =>$ap_id,
      );  
    }
}

foreach($result as $' . $key . '=>$' . $item . '):       

    $' . $item . '["position"] = $ad_position[$' . $item . '["ap_id"]]; 
    $' . $item . '["adv_link"] = HOME_SITE_URL."/Advclick/Advclick/adv_id/".$' . $item . '["adv_id"].".html";
    $' . $item . '["adv_target"] = "_blank"; 
    if(input("get.edit_ad") && !isset($' . $item . '["not_adv"]) )
    {
        
        $' . $item . '["style"] = "filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity: 0.5; opacity: 0.5"; // 广告半透明的样式
        $' . $item . '["adv_link"] = ADMIN_SITE_URL."/Adv/adv_edit/adv_id/".$' . $item . '[\'adv_id\'].".html";
        $' . $item . '["adv_title"] = $ad_position[$' . $item . '["ap_id"]]["ap_name"]."===".$' . $item . '["adv_title"];
        $' . $item . '["adv_target"] = "_self";
        $' . $item . '["adv_style"] = "filter:alpha(opacity=30); -moz-opacity:0.3; -khtml-opacity: 0.3; opacity: 0.3";
    }
    ?>';
        $str .= $content;
        $str .= '<?php endforeach; ?>';
        if (!empty($str)) {
            return $str;
        }
        return;
    }

    public function tagChannel($tag, $content) {
        $typeid = 0;
        $reid = 0;
        $row = 100;
        $col = 1;
        $type = 'son';
        $currentstyle = '';
        $cacheid = '';
        extract($tag);
        $innertext = $content;
        $line = empty($row) ? 100 : $row;

        $likeType = '';
        //读取固定的缓存块
        $cacheid = trim($cacheid);
        if ($cacheid != '') {
            $likeType = rcache($cacheid);
            if ($likeType !== false)
                return $likeType;
        }

        

        if ($type == '' || $type == 'sun')
            $type = 'son';
        if ($innertext == '')
            $innertext = ' | <a href="{$field.typelink}">{$field.typename}</a>';
        $needRel = false;
        //检查是否有子栏目，并返回rel提示（用于二级菜单）
        if (preg_match('#\$field\.rel#', $innertext))
            $needRel = true;
        $attarray = compact('needRel');
        $likeType .= '<?php ';
        $likeType .= '$column_model = model("column");
        $topid = 0;
        $typeid = '.$typeid.';?>';
        if(isset($intag)){
            $likeType .= '<?php $refObj = $'.$intag.';?>';
        }else{
            $likeType .= '<?php $refObj = '.$refObj.';?>';
        }
        $likeType .= '<?php //如果属性里没指定栏目id，从引用类里获取栏目信息
        if (empty($typeid)) {
            if (isset($refObj) && is_array($refObj)) {
                $row2 = $column_model->getOneColumn($refObj["column_id"]);
                $typeid = $row2["column_id"];
                $reid = $row2["parent_id"];
                $topid = $row2["parent_id"];
            } else {
                $typeid = 0;
            }
        }
        //如果指定了栏目id，从数据库获取栏目信息
        else {
            $row2 = $column_model->getOneColumn($typeid);
            $typeid = $row2["column_id"];
            $reid = $row2["parent_id"];
            $topid = $row2["parent_id"];
        }
        $condition = array();
        $condition[] = array("column_display", "=", 1);
        switch ("'.$type.'") {
            case "top":
                $condition[] = array("parent_id", "=", 0);
                break;
            case "son":
                $condition[] = array("parent_id", "=", $typeid);
                break;
            case "self":
                if ($reid == 0)
                    $condition[] = array(1, "=", 2);
                $condition[] = array("parent_id", "=", $reid);
                break;
            default:
                $condition[] = array(1, "=", 2);
        }';

        $likeType .= '$list = $column_model->getColumnList($condition, ' . $line . ', "column_order asc");
            
        //如果用子栏目模式，当没有子栏目时显示同级栏目
        if ("'.$type.'" == "son" && $reid != 0 && empty($list)) {
            $condition = array();
            $condition[] = array("column_display", "=", 1);
            $condition[] = array("parent_id", "=", $reid);
            $list = $column_model->getColumnList($condition, ' . $line . ', "column_order asc");
        }
        $autoindex = 0;
        for ($i = 0; $i < ' . $line . '; $i++):';
        $likeType .= '    if (' . $col . ' > 1): ?>';
        $likeType .= "<dl>\r\n";
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php    for ($j = 0; $j < ' . $col . '; $j++):
                if (' . $col . ' > 1): ?>';
        $likeType .= "<dd>\r\n";
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php        if (isset($list[$i * ' . $col . ' + $j])) :
                    $row = $column_model->dedeMerge($list[$i * ' . $col . ' + $j],' . var_export($attarray, true) . ');
                    $field =$row;
                    if (($row["column_id"] == $typeid || ($topid == $row["column_id"] && "' . $type . '" == "top") ) && "' . $currentstyle . '" != "") :?>';
                        $linkOkstr = $currentstyle;
                        $linkOkstr = str_replace("~rel~", '{$field.rel}', $linkOkstr);
                        $linkOkstr = str_replace("~id~", '{$field.id}', $linkOkstr);
                        $linkOkstr = str_replace("~typelink~", '{$field.typelink}', $linkOkstr);
                        $linkOkstr = str_replace("~typename~", '{$field.typename}', $linkOkstr);
        $likeType .= $linkOkstr;
        $likeType .= '<?php else: ?>';
        $likeType .= $innertext;
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php        if (' . $col . ' > 1):?>';
        $likeType .= "</dd>\r\n";
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php         $autoindex ++;?>';
        $likeType .= '<?php endfor; ?>';
        //Loop Col
        $likeType .= '<?php    if (' . $col . ' > 1) :
                $i += ' . $col . ' - 1;?>';
        $likeType .= "    </dl>\r\n";
        $likeType .= '<?php endif; ?>';
        $likeType .= '<?php endfor; ?>';
        //Loop for $i
        if ($cacheid != '') {
            wcache($cacheid, $likeType);
        }
        return $likeType;
    }

    public function tagArclist($tag, $content) {
        $typeid = 0;
        $notypeid = 0;
        $row = 10;
        $col = 1;
        $titlelen = 30;
        $infolen = 160;
        $imgwidth = 120;
        $imgheight = 90;
        $listtype = 'all';
        $orderby = 'default';
        $keyword = '';
        $innertext = '';
        $arcid = 0;
        $idlist = '';
        $channelid = 0;
        $limit = '';
        $att = '';
        $order = 'desc';
        $subday = 0;
        $noflag = '';
        $tagid = '';
        $pagesize = 0;
        $isweight = 'N';


        $autopartid = 0;
        $getall = 0;
        extract($tag);
        //增加对分页内容的处理
        if ($pagesize == '') {
            $multi = 0;
        }

        //排序
        if (isset($sort))
            $orderby = $sort;

        //对相应的标记使用不同的默认innertext
        if ($content != '')
            $innertext = $content;
        else
            $innertext = '·<a href="{$field.filename}">{$field.title}</a><br/>';

        //兼容titlelength
        if (isset($titlelength))
            $titlelen = $titlelength;

        //兼容infolength
        if (isset($infolength))
            $infolen = $infolength;

        if ($att != '') {
            $flag = $att;
        }

        $line = $row;
        $orderWay = $order;
        $orderby = strtolower($orderby);
        $keyword = trim($keyword);
        $innertext = trim($innertext);

        if (!isset($tablewidth) || $tablewidth == "")
            $tablewidth = 100;
        if (empty($col))
            $col = 1;
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth . "%";
        $colWidth = $colWidth . "%";


        if ($att == '0')
            $att = '';
        if ($att == '3')
            $att = 'f';
        if ($att == '1')
            $att = 'h';
        $attarray = compact('titlelen', 'infolen', 'imgwidth', 'imgheight');
        
        $artlist = '';
        if(isset($intag)){
            $artlist .= '<?php $refObj = $'.$intag.';?>';
        }else{
            $artlist .= '<?php $refObj = '.$refObj.';?>';
        }
        $artlist .= '<?php $typeid='.$typeid.';
        $keyword = "'.$keyword.'";
        $line = '.$line.';    
        if (empty($typeid) && isset($refObj) && is_array($refObj)) {
            $typeid = $refObj["column_id"];
        }

        $column_model = model("column");
        $table = "news";
        $column_list = array();
        if (!isset($column_list[$typeid])) {
            $column_list[$typeid] = $column_model->getOneColumn($typeid);
            switch ($column_list[$typeid]["column_module"]) {
                case COLUMN_NEWS://文章
                    $table = "news";
                    break;
                case COLUMN_PRODUCT://商品
                    $table = "product";
                    break;
                case COLUMN_CASES://图片
                    $table = "cases";
                    break;
            }
        }
        $orwheres = array();
        //按不同情况设定SQL条件 排序方式
        if ("'.$idlist.'" == "") {
            if ("'.$orderby.'" == "near") {
                $keyword = "";
            }

            //时间限制(用于调用最近热门文章、热门评论之类)，这里的时间只能计算到天，否则缓存功能将无效
            if ('.$subday.' > 0) {
                $ntime = gmmktime(0, 0, 0, gmdate("m"), gmdate("d"), gmdate("Y"));
                $limitday = $ntime - ($subday * 24 * 3600);
                $orwheres[] = array($table . "_addtime", ">", $limitday);
            }
            //关键字条件
            if ($keyword != "") {
                preg_match("/~([A-Za-z0-9]+)~/s", $keyword, $conditions);
                if (isset($refObj[$conditions[1]])) {
                    $keyword = addslashes($refObj[$conditions[1]]);
                }

                $keyword = str_replace(",", "|", $keyword);
                $keyword = trim($keyword, "|");

                $orwheres[] = array($table . "_title", "exp", Db::raw("REGEXP \'$keyword\'"));
            }
            if (!empty($typeid) && $typeid != "top") {
                //指定了多个栏目时，不再获取子类的id
                if (preg_match("#,#", $typeid)) {
                    //指定了getall属性或主页模板例外
                    if ($getall == 1 || empty($refObj["typeid"])) {
                        $typeids = explode(",", $typeid);
                        foreach ($typeids as $ttid) {
                            $typeidss[] = $column_model->getColumnSonIds($ttid);
                        }
                        $typeidStr = join(",", $typeidss);
                        $typeidss = explode(",", $typeidStr);
                        $typeidssok = array_unique($typeidss);
                        $typeid = join(",", $typeidssok);
                    }
                    $orwheres[] = array("column_id", "in", $typeid);
                }else{
                    $orwheres[] = array("column_id", "in", $column_model->getColumnSonIds($typeid));
                }
            }

            if (!empty('.$notypeid.')) {
                $orwheres[] = array("column_id", "not in", $column_model->getColumnSonIds('.$notypeid.'));
            }


            $orwheres[] = array($table . "_displaytype", "=", 1);
        }

        //文档排序的方式
        $ordersql = "";
        if ("'.$orderby.'" == "hot" || "'.$orderby.'" == "click")
            $ordersql = $table . "_hits '.$orderWay.'";
        else if ("'.$orderby.'" == "sortrank" || "'.$orderby.'" == "pubdate")
            $ordersql = $table . "_order '.$orderWay.'";
        else if ("'.$orderby.'" == "id")
            $ordersql = $table . "_id '.$orderWay.'";
        else if ("'.$orderby.'" == "near")
            $ordersql = "ABS(" . $table . "_id - " . '.$arcid.' . ")";
        else if ("'.$orderby.'" == "rand")
            $ordersql = "rand()";
        else
            $ordersql = $table . "_order '.$orderWay.'";

        //limit条件
        $limit = trim(preg_replace("#limit#is", "", "'.$limit.'"));
        if ($limit != "") {
            $limitarr = explode(",", $limit);
            $limitsql = $limitarr;
            $line = isset($limitarr[1]) ? $limitarr[1] : $line;
        } else
            $limitsql = [0, $line];

        
        
        $model = model($table);
        switch ($column_list[$typeid]["column_module"]) {
            case COLUMN_NEWS://文章
                $list = $model->getNewsList($orwheres, $limitsql, "*", "", $ordersql);
                break;
            case COLUMN_PRODUCT://商品
                $list = $model->getProductList($orwheres, $limitsql, "*", "", $ordersql);
                break;
            case COLUMN_CASES://图片
                $list = $model->getCasesList($orwheres, $limitsql, "*", "", $ordersql);
                break;
        }

        if ('.$pagesize.' > 0):?>';
            $artlist .= '    <div id="'.$tagid.'">\r\n';
        $artlist .= '<?php endif; ?>';    
        $artlist .= '<?php if ('.$col.' > 1): ?>';  
            $artlist .= '<table width="'.$tablewidth.'" border="0" cellspacing="0" cellpadding="0">\r\n';
        $artlist .= '<?php endif; ?>';
        
        
        $artlist .= '<?php $autoindex = 0;
        $ids = array();
        for ($i = 0; $i < '.$line.'; $i++) :
            if ('.$col.' > 1):?>';
                $artlist .= "<tr>\r\n";
        $artlist .= '<?php endif; ?>';
        $artlist .= '<?php    for ($j = 0; $j < '.$col.'; $j++) :
                if ('.$col.' > 1):?>';
                    $artlist .= "    <td width='$colWidth'>\r\n";
        $artlist .= '<?php endif; ?>';
        $artlist .= '<?php        if (isset($list[$i * '.$col.' + $j])) :
                    $row = $model->dedeMerge($list[$i * '.$col.' + $j], '.var_export($attarray,true).');
                    $ids[] = $row[$table."_id"];
                    $field =$row;
                    $autoindex++;
                    if ('.$pagesize.' <= 0 || ($autoindex <= '.$pagesize.')) :?>';
                        $artlist .= $innertext . "\r\n";
        $artlist .= '<?php endif; ?>';
        $artlist .= '<?php endif; ?>';

        $artlist .= '<?php        if ('.$col.' > 1):?>';
                    $artlist .= "    </td>\r\n";
        $artlist .= '<?php endif; ?>';            
        $artlist .= '<?php endfor; ?>';
        $artlist .= '<?php        if ('.$col.' > 1):
                $i += '.$col.' - 1;?>';
        $artlist .= '<?php endif; ?>';     
        $artlist .= '<?php        if ('.$col.' > 1):?>';
                $artlist .= "    </tr>\r\n";
        $artlist .= '<?php endif; ?>';     
        $artlist .= '<?php endfor; ?>';
        $artlist .= '<?php        if ('.$col.' > 1):?>';
            $artlist .= "    </table>\r\n";
        $artlist .= '<?php endif; ?>'; 
        //分页特殊处理
        $artlist .= '<?php if ('.$pagesize.' > 0) :?>';
            $artlist .= "    </div>\r\n";
        $artlist .= '<?php endif; ?>'; 

        return $artlist;
    }

    public function tagFlink($tag, $content) {
        $type = 'textall';
        $row = 24;
        $titlelen = 24;
        $linktype = 1;
        $typeid = 0;
        extract($tag);
        $totalrow = $row;
        $revalue = '';

        $wsql = array();
        $wsql[] = array('link_show_ok', '=', 1);

        if ($type == 'image') {
            $wsql[] = array('link_type', '=', 1);
        } else if ($type == 'text') {
            $wsql[] = array('link_type', '=', 0);
        }

        $link_model = model('link');
        $list = $link_model->getLinkList($wsql, '*', $totalrow);

        if (trim($content) == '')
            $innertext = '<li>{$field.link}</li>';
        else
            $innertext = $content;


        foreach ($list as $key => $dbrow) {
            if ($type == 'text' || $type == 'textall') {
                $link = "<a href='" . $dbrow['link_weburl'] . "' target='_blank'>" . mb_substr($dbrow['link_webname'], 0, $titlelen) . "</a> ";
            } else if ($type == 'image') {
                $link = "<a href='" . $dbrow['link_weburl'] . "' target='_blank'><img src='" . get_link_img($dbrow['link_weblogo']) . "' width='88' height='31' border='0'></a> ";
            } else {
                if ($dbrow['link_weblogo'] == '') {
                    $link = "<a href='" . $dbrow['link_weburl'] . "' target='_blank'>" . mb_substr($dbrow['link_webname'], 0, $titlelen) . "</a> ";
                } else {
                    $link = "<a href='" . $dbrow['link_weburl'] . "' target='_blank'><img src='" . get_link_img($dbrow['link_weblogo']) . "' width='88' height='31' border='0'></a> ";
                }
            }
            $new_row = [
                'webname' => $dbrow['link_webname'],
                'url' => $dbrow['link_weburl'],
                'sortrank' => $dbrow['link_order'],
                'ischeck' => $dbrow['link_show_ok'],
                'dtime' => $dbrow['link_addtime'],
                'link' => $link,
            ];
            $revalue .= '<?php ';
            $revalue .= '$field =' . var_export(array_merge($dbrow, $new_row), true) . ';';
            $revalue .= '?>';
            $revalue .= $innertext;
        }
        return $revalue;
    }

    public function tagType($tag, $content) {
        $typeid = 0;
        extract($tag);
        $innertext = $content;
        $revalue = '';
        $attarray = compact('needRel');
        if(isset($intag)){
            $revalue .= '<?php $refObj = $'.$intag.';?>';
        }else{
            $revalue .= '<?php $refObj = '.$refObj.';?>';
        }
        $revalue .= '<?php $typeid = '.$typeid.';
        if ($typeid == 0) {
            $typeid = (isset($refObj) && is_array($refObj)) ? $refObj["column_id"] : 0;
        }
        if ($typeid):
        $column_model = model("column");
        $row = $column_model->getOneColumn($typeid);
        if (is_array($row)):?>';
        if (trim($innertext) == "")
            $innertext = '<a href="{$field.typelink}">{$field.typename}</a>';
        
        
        $revalue .= '<?php ';
        $revalue .= '$field =$column_model->dedeMerge($row, '. var_export($attarray, true).');';
        $revalue .= '?>';
        $revalue .= $innertext;
        $revalue .= '<?php endif; ?>';
        $revalue .= '<?php endif; ?>';
        return $revalue;
    }

    public function tagChannelartlist($tag, $content) {
        $typeid = 0;
        $notypeid = 0;
        $row = 20;
        $cacheid = '';
        $currentstyle='';
        extract($tag);
        $innertext = trim($content);
        $artlist = '';
        //读取固定的缓存块
        $cacheid = trim($cacheid);
        if ($cacheid != '') {
            $artlist = rcache($cacheid);
            if ($artlist != '')
                return $artlist;
        }

        if (empty($typeid)) {
            $typeid = (isset($refObj) && is_array($refObj)) ? $refObj['column_id'] : 0;
        }

        if ($innertext == '')
            $innertext = '
<table width="99%" border="0" cellpadding="3" cellspacing="1" bgcolor="#BFCFA9">
  <tr>
    <td bgcolor="#E6F2CC">
    	{dstaglib:type intag="v" refObj="isset($item_info)?$item_info:0"}
    	<table border="0" cellpadding="0" cellspacing="0" width="98%">
    		<tr>
    		<td width="10%">·</td>
    	 <td width="60%">
    	<a href="{$field.typelink}">{$field.typename}</a>
        </td>
      <td width="30%" align="right">
      <a href="{$field.typelink}">更多...</a>
      </td>
    </tr>
   </table>
   {/dstaglib:type}
   </td>
  </tr>
  <tr>
    <td height="100" valign="top" bgcolor="#FFFFFF">
	{dstaglib:arclist intag="v" refObj="isset($item_info)?$item_info:0"}
	·<a href="{$field.arcurl}">{$field.title}</a><br>
  {/dstaglib:arclist}
	</td>
  </tr>
</table>
<div style="font-size:2px">&nbsp;</div>';
        $totalnum = $row;
        if (empty($totalnum))
            $totalnum = 20;

        //获得类别ID总数的信息
        $typeids = array();
        $tpsql = array();
        $tpsql[] = array('column_display', '=', 1);
        if ($typeid == 0 || $typeid == 'top') {
            $tpsql[] = array('parent_id', '=', 0);
        } else {
            if (!preg_match('#,#', $typeid)) {
                $tpsql[] = array('parent_id', '=', $typeid);
            } else {
                $tpsql[] = array('column_id', 'in', $typeid);
            }
        }

        if ($notypeid != 0) {
            $tpsql[] = array('column_id', 'not in', $notypeid);
        }



   

        $attarray = compact('needRel');
        $artlist .= '<?php ';
        $artlist .= '$column_model = model("column");';
        $artlist .= '$channelartlist = $column_model->getColumnList('. var_export($tpsql, true).', '.$totalnum.', "column_order asc");';
        $artlist .= 'for ($channelartitem = 0; $channelartitem < count($channelartlist); $channelartitem++) :
            
        $'.$item.' =$column_model->dedeMerge($channelartlist[$channelartitem], ' . var_export($attarray, true) . ');
        if(request()->url()==$'.$item.'["typeurl"]){
            $'.$item.'["currentstyle"] ="'.$currentstyle.'";
        }?>';
            $artlist .= $innertext;
        $artlist .= '<?php endfor; ?>';

        if ($cacheid != '') {
            wcache($cacheid, $artlist);
        }
        return $artlist;
    }

    public function tagPrenext($tag) {
        $get = 'next';
        extract($tag);
        $str = '';
        if (isset($refObj['news_id'])) {
            $condition = [];
            $condition[] = ['column_id', '=', $refObj['column_id']];
            $condition[] = ['news_displaytype', '=', 1];
            $news_model = model('news');
            switch ($get) {
                case 'pre':
                    $str .= '上';
                    $condition[] = ['news_id', '<', $refObj['news_id']];
                    break;
                case 'next':
                    $str .= '下';
                    $condition[] = ['news_id', '>', $refObj['news_id']];
                    break;
            }
            $str .= '一篇：';
            $item = $news_model->getOneNews($condition);
            if (!$item) {
                $str .= '没有了</a>';
                $str = '<a>' . $str;
            } else {
                $str .= $item['news_title'] . '</a>';
                $str = '<a href="' . (String) url('News/detail', ['news_id' => $item['news_id']]) . '">' . $str;
            }
        }
        if (isset($refObj['cases_id'])) {
            $condition = [];
            $condition[] = ['column_id', '=', $refObj['column_id']];
            $condition[] = ['cases_displaytype', '=', 1];
            $cases_model = model('cases');
            switch ($get) {
                case 'pre':
                    $str .= '上';
                    $condition[] = ['cases_id', '<', $refObj['cases_id']];
                    break;
                case 'next':
                    $str .= '下';
                    $condition[] = ['cases_id', '>', $refObj['cases_id']];
                    break;
            }
            $str .= '一篇：';
            $item = $cases_model->getOneCases($condition);
            if (!$item) {
                $str .= '没有了</a>';
                $str = '<a>' . $str;
            } else {
                $str .= $item['cases_title'] . '</a>';
                $str = '<a href="' . (String) url('Cases/detail', ['cases_id' => $item['cases_id']]) . '">' . $str;
            }
        }
        if (isset($refObj['product_id'])) {
            $condition = [];
            $condition[] = ['column_id', '=', $refObj['column_id']];
            $condition[] = ['product_displaytype', '=', 1];
            $product_model = model('product');
            switch ($get) {
                case 'pre':
                    $str .= '上';
                    $condition[] = ['product_id', '<', $refObj['product_id']];
                    break;
                case 'next':
                    $str .= '下';
                    $condition[] = ['product_id', '>', $refObj['product_id']];
                    break;
            }
            $str .= '一篇：';
            $item = $product_model->getOneProduct($condition);
            if (!$item) {
                $str .= '没有了</a>';
                $str = '<a>' . $str;
            } else {
                $str .= $item['product_title'] . '</a>';
                $str = '<a href="' . (String) url('Product/detail', ['product_id' => $item['product_id']]) . '">' . $str;
            }
        }


        return $str;
    }

}

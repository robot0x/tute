<?php
namespace Think\Template\TagLib;
use Think\Template\TagLib;
/**
 * CX标签库解析类
 */
class Yunzhi extends TagLib {

    // 标签定义
    protected $tags   =  array(
    	'user'       =>  array('attr'=>'id,name'),
        'page'      =>array('attr'=>'id,name,class,totalnumber','close'=>1)
    );

    public function _user($tag,$content) {
    	//获取变量
    	$id = $tag['id'];
    	$name = $tag['name'];

    	//字符串接拼。会直接传到前面的页面，为避免冲突，变量前加yunzhi_
        $parseStr = '<?php ';
        $parseStr .= '$yunzhi_UserM = new User\Model\UserModel();';
        $parseStr .= '$' . $name . '= $yunzhi_UserM->getUserById(';
        $parseStr .= "$id"; //双引号进行变量替换
        $parseStr .= ');';
        $parseStr .= ' ?>';
        $parseStr .= $content;
        
        return $parseStr;
    }
    /**
     * page标签解析
     * 格式： <html:page id="" class="" totalnumber=""/>
     * @access public
     * @param array $tag 标签属性
     * @return string|void
     */
    public function _page($tag){
        $id         =   !empty($tag['id'])?$tag['id']: '_page';
        $name       =   $tag['name'];
        $class      =   !empty($tag['class'])?$tag['class']:'';
        $totalnumber = !empty($tag['totalnumber'])?$tag['class']:'0';
        $page = new Page($tag['totalnumber']);
        $parseStr =  $page->show();
        return $parseStr;
    }
}
<?php
/**
 * xms
 * ============================================================================

 */ 
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\response\Json;
use think\Session;
use think\Cookie;

class Base extends Controller {
    public $session_id;
    public $cateTrre = array();
    /*
     * 初始化操作
     */
    public function _initialize() {
        Session::start();
        header("Cache-control: private");  // history.back返回后输入框值丢失问题 参考文章 #article_id_1465.html  http://blog.csdn.net/qinchaoguang123456/article/details/29852881
    	$this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        
        // 判断当前用户是否手机                
        if(isMobile())
            cookie('is_mobile','1',3600); 
        else 
            cookie('is_mobile','0',3600);
                
        $this->public_assign();
        $this->doCookieArea();
    }
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign()
    {
        
       $xms_config = array();
       $tp_config = M('config')->cache(true,xms_CACHE_TIME)->select();       
       foreach($tp_config as $k => $v)
       {
       	  if($v['name'] == 'hot_keywords'){
       	  	 $xms_config['hot_keywords'] = explode('|', $v['value']);
       	  }       	  
          $xms_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
       }                        
       
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = M('brand')->cache(true,xms_CACHE_TIME)->field('id,name,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();
       $this->assign('brand_list', $brand_list);
       $this->assign('xms_config', $xms_config);
    }

    /*
     * 
     */
    public function ajaxReturn($data)
    {
        exit(json_encode($data));
    }

    /**
     * 根据ip设置获取的地区来设置地区缓存
     */
    private function doCookieArea()
    {
//        $ip = '183.147.30.238';//测试ip
        $cookie_province_id = Cookie::get('province_id');
        $cookie_city_id = Cookie::get('city_id');
        $cookie_district_id = Cookie::get('district_id');
        if(empty($cookie_province_id) || empty($cookie_city_id) || empty($cookie_district_id)){
            $address = GetIpLookup();
            if(empty($address['province'])){
                $this->setCookieArea();
                return;
            }
            $province_id = Db::name('region')->where(['level' => 1, 'name' => ['like', '%' . $address['province'] . '%']])->limit('1')->value('id');
            if(empty($province_id)){
                $this->setCookieArea();
                return;
            }
            if (empty($address['city'])) {
                $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id])->limit('1')->order('id')->value('id');
            } else {
                $city_id = Db::name('region')->where(['level' => 2, 'parent_id' => $province_id, 'name' => ['like', '%' . $address['city'] . '%']])->limit('1')->value('id');
            }
            if (empty($address['district'])) {
                $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id])->limit('1')->order('id')->value('id');
            } else {
                $district_id = Db::name('region')->where(['level' => 3, 'parent_id' => $city_id, 'name' => ['like', '%' . $address['district'] . '%']])->limit('1')->value('id');
            }
            $this->setCookieArea($province_id, $city_id, $district_id);
        }
    }

    /**
     * 设置地区缓存
     * @param $province_id
     * @param $city_id
     * @param $district_id
     */
    private function setCookieArea($province_id = 1, $city_id = 2, $district_id = 3)
    {
        Cookie::set('province_id', $province_id);
        Cookie::set('city_id', $city_id);
        Cookie::set('district_id', $district_id);
    }
}
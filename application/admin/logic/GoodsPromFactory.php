<?php
/**
 * xms
 * ============================================================================

 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: Dyr
 * Date: 2017-05-11
 */
namespace app\admin\logic;
/**
 * 商品活动工厂类
 * Class CatsLogic
 * @package admin\Logic
 */
class GoodsPromFactory
{
    public function makeModule($promType, $promId)
    {
        switch ($promType) {
            case 1:
                return new FlashSaleLogic($promId);
            case 2:
                return new GroupBuyLogic($promId);
            case 3:
                return new PromGoodsLogic($promId);
        }
    }

    /**
     * 检测是否符合商品活动工厂类的使用
     * @param $promType |活动类型
     * @return bool
     */
    public function checkPromType($promType)
    {
        if (in_array($promType, array_values([1, 2, 3]))) {
            return true;
        } else {
            return false;
        }
    }

}

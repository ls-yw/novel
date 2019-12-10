<?php
namespace application\logic;

use application\library\HelperExtend;
use application\models\Config;

class ConfigLogic
{
    /**
     * 配置数组
     *
     * @author woodlsy
     * @param string $type
     * @return array
     */
    public function getPairs(string $type)
    {
        return HelperExtend::arrayToKeyValue((new Config())->getAll(['type' => $type]), 'config_key', 'config_value');
    }
}
<?php
/**
 * User: sunkangYun@aliyun.com
 * Date: 2017/6/24
 * Time: 下午9:31
 */
namespace image;

class Exception extends \RuntimeException
{
    public function getError()
    {
        echo "An error occured!";
        exit;
    }

}
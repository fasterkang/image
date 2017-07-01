<?php
// +----------------------------------------------------------------------
// | Author: fasterkang <sunkangYun@aliyun.com>
// +----------------------------------------------------------------------
namespace image;

class Exception extends \RuntimeException
{
    public function getError()
    {
        echo "An error occured!";
        exit;
    }

}
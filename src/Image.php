<?php

// +----------------------------------------------------------------------
// | Author: fasterkang <sunkangYun@aliyun.com>
// +----------------------------------------------------------------------

namespace image;
use image\Exception as ImageException;
use image\Config as ImageConfig;

class Image
{
    const DEFAULT_TTF  = 'STCAIYUN.TTF';

    //image src
    private static $imageSrc;
    //image width
    private static $width;
    //image width
    private static $height;
    //image minetype
    private static $mineType;
    //image extension
    private static $extension;
    //image resource
    private static $imageResource;
    //self
    private static $self = null;
    //default color
    private static $rgb = array(
        220,
        220,
        220
    );
    //default watermark fontsize
    private static $fontsize = 50;
    //default watermark angle
    private static $angle    = 0;
    //water coordinate x
    private static $waterX   = 0;
    //water coordinate x
    private static $waterY   = 0;

    //X axis offset multiple
    private static $offsetXTimes = 1;

    //Y axis offset multiple
    private static $offsetYTimes = 1;

    private function __construct(){}
    /**
     * 获取图片
     * @param $filepath
     * @return Image
     */
    public static function createImage($filepath)
    {
        try{
            if (!is_file($filepath))
            {
                throw new ImageException('resource not exists');
            }

            self::$imageSrc = $filepath;
            self::getImageResource();

        }catch (ImageException $e)
        {
           echo $e->getMessage();
           exit();
        }

        return is_null(self::$self) ? new self() : self::$self;
    }

    /**
     * set base params
     */
    public static function getImageResource()
    {
        $imageInfo = getimagesize(self::$imageSrc);
        if (false === $imageInfo)
        {
            throw new ImageException('Illegal image resource');
        }

        self::$width     = $imageInfo[0];
        self::$height    = $imageInfo[1];
        self::$mineType  = $imageInfo['mime'];
        self::$extension = image_type_to_extension($imageInfo[2],false);
        $functinon = "imagecreatefrom".self::$extension;
        self::$imageResource = $functinon(self::$imageSrc);
        if (!self::$imageResource)
        {
            throw new ImageException('create image resource fail');
        }

    }

    /**
     * get image width
     * @return mixed
     */
    public function getWidth()
    {
        return self::$width;
    }

    /**
     * get image height
     * @return mixed
     */
    public function getHeight()
    {
        return self::$height;
    }

    /**
     * get image minetype
     * @return mixed
     */
    public function getMineType()
    {
        return self::$mineType;
    }

    /**
     * get image extension
     * @return mixed
     */
    public function getExtension()
    {
        return self::$extension;
    }

    /**
     * @param array $times
     * @return $this
     */
    public function setOffsetTimes(Array $times)
    {
        if (!empty($times))
        {
            list($x, $y) = $times;
            self::$offsetXTimes = $x;
            self::$offsetYTimes = $y;
        }
        return $this;
    }

    /**
     * @param $w Crop area width
     * @param $h Crop area height
     * @param int $x Crop area x
     * @param int $y Crop area y
     * @param int $width Image save width
     * @param int $height Image save height
     * @return $this
     */
    public function resize($w, $h, $x = 0, $y = 0, $width = 0, $height = 0)
    {
        if ($width == 0)  $width  = $w;
        if ($height == 0) $height = $h;

        $im = @imagecreatetruecolor($width, $height);
        if ($im ===  false)
        {
            throw new ImageException('Cannot Initialize new GD image stream');
        }

        //crop
        imagecopyresampled($im, self::$imageResource, 0, 0, $x, $y, $width, $height,$w, $h);
        //destroy original image resource
        imagedestroy(self::$imageResource);
        //set new image resource
        self::$imageResource = $im;
        self::$width         = $width;
        self::$height        = $height;

        return $this;
    }

    /**
     * @param $degrees
     * @return $this
     */
    public function rotate($degrees)
    {
      $image = imagerotate(self::$imageResource, -$degrees, 0);
      self::$imageResource = $image;
      self::$width  = imagesx(self::$imageResource);
      self::$height = imagesy(self::$imageResource);
      return $this;
    }

    /**
     * @param null $savePath
     * @param string $suffix
     * @param null $type
     * @return $this
     */
    public function output($savePath = null, $suffix = 'thumb', $type = null)
    {
        $ext = pathinfo(self::$imageSrc, PATHINFO_EXTENSION);
        $extension = self::$extension;
        if (!is_null($type))
        {
            $type = $ext = strtolower($type);
            if ('jpg' === $type)
            {
                $type = 'jpeg';
            }

            $extension = $type;
        }

        $function = "image".$extension;

        if (is_null($savePath))
        {
            $savePath = rtrim(self::$imageSrc, '.'.self::$extension).'_'.$suffix.'.'.$ext;
        }

        $function(self::$imageResource, $savePath);

        imagedestroy(self::$imageResource);
    }

    /**
     * Cut pic
     * @param $thumb_width
     * @param $thumb_height
     * @return $this
     */
    public function thumb($thumb_width, $thumb_height = 0)
    {
        if (!is_numeric($thumb_width) || intval($thumb_width) < 0)
        {
            throw new ImageException('error params');
        }

        if ($thumb_width >= self::$width || $thumb_height >= self::$height)
        {
            return $this;
        }

        $scanX = $thumb_width / self::$width ;
        if ($thumb_height > 0) $scanY = $thumb_height / self::$height;
        else $scanY = 1;
        $scan   = min($scanX, $scanY);
        $width  =  $scan * self::$width;
        $height = $scan * self::$height;
        $this->resize(self::$width, self::$height, 0, 0, $width, $height);
        return $this;

    }


    /**
     * text watermark
     * @param $text
     * @param null $position
     * @param null $fontSize
     * @param null $fontpath
     * @param null $angle
     * @param array $rgb
     * @return $this
     */
    public function watermark($text,$position = null ,$fontSize = null ,$fontpath = null, $angle = null, Array $rgb = array())
    {
        if (empty($text))
        {
            throw new ImageException('watermater content miss');
        }

        $imageContent = file_get_contents(self::$imageSrc);
        $dist = imagecreatefromstring($imageContent);
        if (is_null($fontpath))
        {
            $fontpath = __DIR__.DIRECTORY_SEPARATOR.'ttf'.DIRECTORY_SEPARATOR.self::DEFAULT_TTF;
        }

        if (!empty($rgb))
        {
            if (isset($rgb[0])) self::$rgb[0] = $rgb[0];
            if (isset($rgb[1])) self::$rgb[1] = $rgb[1];
            if (isset($rgb[2])) self::$rgb[2] = $rgb[2];
        }

        $background = imagecolorallocate($dist, self::$rgb[0], self::$rgb[1], self::$rgb[2]);

        if (!is_null($fontSize))
        {
            self::$fontsize = $fontSize;
        }

        if (!is_null($angle))
        {
            self::$angle = $angle;
        }
        $box = imagettfbbox(self::$fontsize, self::$angle, $fontpath, $text);

        if (is_null($position))
        {
            $position = ImageConfig::$WATER_CENTER_CENTER;
        }

        self::setWaterXy($box, $position);
        //x,y是水印字体的左下角
        imagefttext($dist, self::$fontsize, self::$angle, self::$waterX, self::$waterY, $background, $fontpath, $text);
        self::$imageResource = $dist;
        return $this;
    }

    /**
     * set watermark x,y coordinate
     * @param $box
     * @param $position
     */
    protected static function setWaterXy($box, $position)
    {
        $minx = min($box[0], $box[2], $box[4], $box[6]);
        $miny = min($box[1], $box[3], $box[5], $box[7]);

        $water_width =  abs($box[4] - $box[0]);
        $water_height = abs($box[5] - $box[1]);
        switch ($position)
        {
            case 1:
                $x = $y = 0;
                break;
            case 2:
                $x = (self::$width - $water_width) / 2;
                $y = 0;
                break;
            case 3:
                $x = self::$width - $water_width;
                $y = 0;
                break;
            case 4:
                $x = 0;
                $y = (self::$height - $water_height) / 2;
                break;
            case 5:
                $x = (self::$width - $water_width) / 2;
                $y = (self::$height - $water_height) / 2;
                break;
            case 6:
                $x = self::$width - $water_width;
                $y = (self::$height - $water_height) / 2;
                break;
            case 7:
                $x = 0;
                $y = self::$height - $water_height;
                break;
            case 8:
                $x = (self::$width - $water_width) / 2;
                $y = self::$height - $water_height;
                break;
            case 9:
                $x = self::$width - $water_width;
                $y = self::$height - $water_height;
                break;
            case 10:
                $x = ((self::$width - $water_width) / 2) * self::$offsetXTimes;
                $y = ((self::$height - $water_height) / 2) * self::$offsetYTimes;
                break;
            default:
                break;
        }
        self::$waterX = $x + abs($minx);
        self::$waterY = $y + abs($miny);
    }

}


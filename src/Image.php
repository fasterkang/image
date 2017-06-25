<?php
/**
 * User: sunkangYun@aliyun.com
 * Date: 2017/6/24
 * Time: 下午9:31
 */
namespace image;
use image\Exception as ImageException;

class Image
{
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
      self::$width = imagesx(self::$imageResource);
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

        return $this;
    }
}


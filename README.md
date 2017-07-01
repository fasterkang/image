# Image Package

## 安装

> composer require faster/image

## 使用
use image\Image

+ 裁剪
+ ~~~
 Image::createImage('100.jpg')->resize(100,100)->output()
 
  
+ 旋转
+ ~~~
  Image::createImage('100.jpg')->rotate(30)->output()
  
+ 缩略图
+ ~~~
  Image::createImage('100.jpg')->thumb(100,100)->output()  
  
+ 文字水印
+ ~~~
  Image::createImage('100.jpg')->watermask('水印')->output()  
  







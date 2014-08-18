<?php defined('IN_FATE') or exit('access denied');

	 /**
     * @brief 图片处理类
     */
     
		class IImage {
				
					/** 
					 * @brief  创建图片资源
					 * @param  $file 需要创建的图片名称
					 * @return $imageRes 图片句柄
					 */
					public  function createResource($file){
						
									$ext  = pathinfo($file,PHPINFO_EXTENSION);
									$imageRes = '';
							    switch($ext)
							    {
							        case 'jpg' :
							        case 'jpeg':
							        {
							        	$imageRes = imagecreatefromjpeg($file);
							        }
							        break;
						
							        case 'gif' :
							        {
							        	$imageRes = imagecreatefromgif($file);
							        }
							        break;
						
							        case 'png' :
							        {
							        	$imageRes = imagecreatefrompng($file);
							        }
							        break;
						
							        case 'bmp' :
							        {
										$imageRes = imagecreatefromwbmp($file);
							        }
							        break;
							    }
							    return $imageRes;
					}
					
					/**
					 * @brief 创建图片
					 * @param $imageRes 图片句柄
					 * @param $thumbFilename 缩略图文件名称
					 */
					public function createImage($imageRes,$thumbFileName){
						
									//如果目录不可写直接返回，防止错误抛出
									if(!is_writeable(dirname($thumbFileName)))
									{
										return false;
									}
							
									$imageResult = false;
									//获取文件扩展名
									$fileExt  = IFile::getFileSuffix($thumbFileName);
							
								    switch($fileExt)
								    {
								        case 'jpg' :
								        case 'jpeg':
								        {
								        	$imageResult = imagejpeg($imageRes,$thumbFileName,100);
								        }
								        break;
							
								        case 'gif' :
								        {
								        	$imageResult = imagegif($imageRes,$thumbFileName);
								        }
								        break;
							
								        case 'png' :
								        {
								        	$imageResult = imagepng($imageRes,$thumbFileName);
								        }
								        break;
							
								        case 'bmp' :
								        {
													$imageResult = imagewbmp($imageRes,$thumbFileName);
								        }
								        break;
								    }
								  return $imageResult;
						
					}
					
					//生成缩略图
					public function thumb($file,$width,$height,$ext='_thumb',$cut=false){
						
									if(is_file($file))
									{
										//获取原图信息
										list($imgWidth,$imgHeight) = getImageSize($file);
										//剪切位置
										$cutx = $cuty = 0;
										//粘贴位置 
										$targetx = $targety = 0; 
							
										if($cut)
										{					
												if(($width/$height) >= ($imgWidth/$imgHeight))
												{
													$thumbWidth=$width;
													$thumbHeight=$thumbWidth*($imgHeight/$imgWidth);
												}
												else
												{
													$thumbHeight=$height;
													$thumbWidth=$thumbHeight*($imgWidth/$imgHeight);
												}					
										}else{
												if($imgWidth >= $imgHeight)
												{
													$thumbWidth  = $width;
													$thumbHeight = ($width / $imgWidth) * $imgHeight;
												}
												else
												{
													$thumbWidth  = ($height / $imgHeight) * $imgWidth;
													$thumbHeight = $height;
												}
										}
										$targetx = ($width-$thumbWidth)/2;
										$targety =  ($height-$thumbHeight)/2;
										//生成$fileName文件图片资源
									  $thumbRes = self::createResource($file);
								    $thumbBox = imageCreateTrueColor($width,$height);
								    //填充补白
										$padColor = imagecolorallocate($thumbBox,255,255,255);
							      imagefilledrectangle($thumbBox,0,0,$width,$height,$padColor);
							
										//拷贝图像
								    imagecopyresampled($thumbBox, $thumbRes, $targetx , $targety, $cutx, $cuty, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
								    //生成缩略图文件名
								    $fileExt       = pathinfo($file,PHPINFO_EXTENSION);
								    $thumbFileName = str_replace('.'.$fileExt,$ext.'.'.$fileExt,$file);
										//生成图片文件
								    $result = self::createImage($thumbBox,$thumbFileName);
						        if($result == true)
						        {
						        	return $thumbFileName;
						        }
									}
									
								  return null;
									
					}
			
		}


?>
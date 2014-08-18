<?php defined('IN_FATE') or exit('access denied');
/**
 * @brief 验证码生成类库
 * @param $width  生成图片的宽度
 * @param $height 生成图片的高度
 * @param $minWordLength 字符串最小长度
 * @param $maxWordLength 字符串最大长度
 * @param $session_var   存储字串的session名称
 * @param $backgroundColor RGB数组定义背景颜色
 * @param $colors          RGB数组定义四种字体颜色
 * @param $shadowColor     定义阴影颜色null或者array(0,0,0)
 * @param $fontSize        定义字体大小
 * @notice 为了安全考虑，使用的字体最好不要在站点目录
 */
class ICaptcha
{
  
    public $width  = 200;
    public $height = 70;
    public $minWordLength = 4;
    public $maxWordLength = 5;
    public $session_var = 'captcha';
    public $backgroundColor = array(255, 255, 255);
    public $colors = array(
								        array(0,100,181),  // blue
								        array(0,200,35),   // green
								        array(225,30,0),   // red
												array(0,111,111),  // red
    								 );
    public $shadowColor = null;

    public $fontSize    = 10;

    /**
     * @brief 字体配置
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public $fonts = array(
       	'Time'    => array('spacing' => 2, 'minSize' => 22, 'maxSize' => 24, 'font' => 'font.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    public $Yperiod    = 12;
    public $Yamplitude = 14;
    public $Xperiod    = 11;
    public $Xamplitude = 5;

    /** letter rotation clockwise */
    public $maxRotation = 8;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    public $scale = 3;

    /**
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public $blur = false;
    /** Debug? */
    public $debug = false;
    /** Image format: jpeg or png */
    public $imageFormat = 'jpeg';
    public $im;

    public function __construct($config = array()) {
    	
    }
    
    /**
     * @brief 生成图片
     */
    public function CreateImage($text='') {
    	
        $ini = microtime(true);
        $this->ImageAllocate();
        if(empty($text)){
          $text = $this->GetCaptchaText();
        }
        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $this->WriteText($text, $fontcfg);

        /** Transformations */
        $this->WaveImage();
        if ($this->blur && function_exists('imagefilter'))
				{
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->ReduceImage();

        if ($this->debug)
				{
            imagestring($this->im, 1, 1, $this->height-8,
                "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
                $this->GdFgColor
            );
        }
        
        //输出3种线条 干扰线
				for($i=0;$i<5;$i++){
					//imageline($this->im,rand(1,$this->width),rand(1,29),rand(1,$this->width),rand(1,29), $this->GdFgColor);
				}

        /** Output */
        $this->WriteImage();
        $this->Cleanup();
    }

    /**
     * @生成图片资源
     */
    protected function ImageAllocate()
		{
        if (!empty($this->im))
				{
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        // Background color
 				$this->GdBgColor = imagecolorallocate($this->im,$this->backgroundColor[0],$this->backgroundColor[1],$this->backgroundColor[2]);
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

        // Foreground color
        $color = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3)
		    {
          $this->GdShadowColor = imagecolorallocate($this->im,$this->shadowColor[0],$this->shadowColor[1],$this->shadowColor[2]);
        }

     }

    /**
     * @brief 获取字符串
     */
    public function GetCaptchaText()
	  {
        $text = $this->GetRandomCaptchaText();
        return $text;
    }

    /**
     * @brief 生成随机字符串
     */
    protected function GetRandomCaptchaText($length = null)
	  {
        if (empty($length))
				{
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstvwyz";
        $vocals = "aeiou";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++)
				{
            if ($vocal)
						{
                $text .= substr($vocals, mt_rand(0, 4), 1);
            }
						else
						{
                $text .= substr($words, mt_rand(0, 22), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }


    /**
     * @brief 向图片资源输入内容
     */
    protected function WriteText($text, $fontcfg = array())
		{
        if (empty($fontcfg))
				{
            $fontcfg  = $this->fonts[array_rand($this->fonts)];
        }

        $fontfile = dirname(__FILE__).'/'.$fontcfg['font'];


        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);
        // Text generation (char by char)
        $x      = 20*$this->scale;
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++)
				{
            $degree   = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($this->fontSize+1, $this->fontSize-1)*$this->scale*$fontSizefactor;
						//$fontsize = $maxSize*$this->scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);

            if ($this->shadowColor)
						{
               $coords = imagettftext($this->im,$fontsize,$degree,$x+$this->scale,$y+$this->scale,$this->GdShadowColor,$fontfile, $letter);
            }
            $coords = imagettftext($this->im, $fontsize, $degree,$x, $y,$this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }
    }

    /**
     * @brief 随机生成文字位置
     */
    protected function WaveImage()
		{

        $xp = $this->scale*$this->Xperiod*rand(1,3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++)
				{
           imagecopy($this->im,$this->im,$i-1,sin($k+$i/$xp)*($this->scale*$this->Xamplitude),$i,0,1,$this->height*$this->scale);
        }

        $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for ($i = 0; $i < ($this->height*$this->scale); $i++)
				{
           imagecopy($this->im, $this->im,sin($k+$i/$yp)*($this->scale*$this->Yamplitude),$i-1,0,$i,$this->width*$this->scale,1);
        }
    }

    /**
     * @brief 图像充采样减小图片大小
     */
    protected function ReduceImage()
		{
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }

    /**
     * @brief 向浏览器直接输出图片资源
     */
    protected function WriteImage()
	{
        if ($this->imageFormat == 'png' && function_exists('imagepng'))
		{
            header("Content-type: image/png");
            imagepng($this->im);
        }
		else
		{
            header("Content-type: image/jpeg");
            imagejpeg($this->im, null, 90);
        }
    }

    /**
     * @brief 销毁句柄
     */
    protected function Cleanup()
	{
        imagedestroy($this->im);
    }
}

?>
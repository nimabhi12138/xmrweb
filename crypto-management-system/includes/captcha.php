<?php
session_start();

class Captcha {
    private $width = 120;
    private $height = 40;
    private $length = 4;
    private $font_size = 20;
    
    public function generate() {
        // 生成随机验证码
        $code = $this->generateCode();
        $_SESSION['captcha_code'] = $code;
        
        // 创建图像
        $image = imagecreatetruecolor($this->width, $this->height);
        
        // 背景颜色
        $bg_color = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $bg_color);
        
        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            $line_color = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imageline($image, rand(0, $this->width), rand(0, $this->height), 
                     rand(0, $this->width), rand(0, $this->height), $line_color);
        }
        
        // 添加干扰点
        for ($i = 0; $i < 50; $i++) {
            $dot_color = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imagesetpixel($image, rand(0, $this->width), rand(0, $this->height), $dot_color);
        }
        
        // 绘制验证码文字
        $text_color = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
        $x = 10;
        for ($i = 0; $i < strlen($code); $i++) {
            imagettftext($image, $this->font_size, rand(-15, 15), $x, 30, $text_color, 
                        __DIR__ . '/arial.ttf', $code[$i]);
            $x += 25;
        }
        
        // 输出图像
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
    
    private function generateCode() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $code;
    }
    
    public static function verify($input) {
        if (!isset($_SESSION['captcha_code'])) {
            return false;
        }
        
        $result = strtoupper($input) === $_SESSION['captcha_code'];
        unset($_SESSION['captcha_code']);
        return $result;
    }
}

// 如果直接访问此文件，生成验证码
if (basename($_SERVER['PHP_SELF']) == 'captcha.php') {
    $captcha = new Captcha();
    $captcha->generate();
}
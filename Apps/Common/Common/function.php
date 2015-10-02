<?php
/**
 * 邮件发送函数
 * @param string to      要发送的邮箱地址
 * @param string subject 邮件标题
 * @param string content 邮件内容
 * @return array
 */
function SendMail($to, $subject, $content) {
	require_cache(VENDOR_PATH."PHPMailer/class.smtp.php");
    require_cache(VENDOR_PATH."PHPMailer/class.phpmailer.php");
    $mail = new PHPMailer();
    // 装配邮件服务器
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = $GLOBALS['CONFIG']['mailSmtp']['fieldValue'];
    $mail->SMTPAuth = $GLOBALS['CONFIG']['mailAuth']['fieldValue'];
    $mail->Username = $GLOBALS['CONFIG']['mailUserName']['fieldValue'];
    $mail->Password = $GLOBALS['CONFIG']['mailPassword']['fieldValue'];
    $mail->CharSet = 'utf-8';
    // 装配邮件头信息
    $mail->From = $GLOBALS['CONFIG']['mailUserName']['fieldValue'];
    $mail->AddAddress($to);
    $mail->FromName = $GLOBALS['CONFIG']['mailSendTitle']['fieldValue'];
    $mail->IsHTML(true);
    // 装配邮件正文信息
    $mail->Subject = $subject;
    $mail->Body = $content;
    // 发送邮件
    $rs =array();
    if (!$mail->Send()) {
    	$rs['status'] = 0;
    	$rs['msg'] = $mail->ErrorInfo;
        return $rs;
    } else {
    	$rs['status'] = 1;
        return $rs;
    }
}
/**
 * 发送短信
 * 此接口要根据不同的短信服务商去写，这里只是一个参考
 * @param string $phoneNumer  手机号码
 * @param string $content     短信内容
 */
function SendSMS($phoneNumer,$content){
	$url = '短信接口';
	$ch=curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 ); //设置连接等待时间
    curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
    $data=curl_exec($ch);
    curl_close($ch);
    return "$data";
}
/**
 * 字符串替换
 * @param string $str     要替换的字符串
 * @param string $repStr  即将被替换的字符串
 * @param int $start      要替换的起始位置,从0开始
 * @param string $splilt  遇到这个指定的字符串就停止替换
 */
function StrReplace($str,$repStr,$start,$splilt = ''){
	$newStr = substr($str,0,$start);
	$breakNum = -1;
	for ($i=$start;$i<strlen($str);$i++){
		$char = substr($str,$i,1);
		if($char==$splilt){
			$breakNum = $i;
			break;
		}
		$newStr.=$repStr;
	}
	if($splilt!='' && $breakNum>-1){
		for ($i=$breakNum;$i<strlen($str);$i++){
			$char = substr($str,$i,1);
			$newStr.=$char;
		}
	}
	return $newStr;
}
/**
 * 循环删除指定目录下的文件及文件夹
 * @param string $dirpath 文件夹路径
 */
function DelDir($dirpath){
	$dh=opendir($dirpath);
	while (($file=readdir($dh))!==false) {
		if($file!="." && $file!="..") {
		    $fullpath=$dirpath."/".$file;
		    if(!is_dir($fullpath)) {
		        unlink($fullpath);
		    } else {
		        WSTDelDir($fullpath);
		        rmdir($fullpath);
		    }
	    }
	}	 
	closedir($dh);
    $isEmpty = 1;
	$dh=opendir($dirpath);
	while (($file=readdir($dh))!== false) {
		if($file!="." && $file!="..") {
			$isEmpty = 0;
			break;
		}
	}
	return $isEmpty;
}
/**
 * 获取网站域名
 */
function Domain(){
	$server = $_SERVER['SERVER_NAME'];
	$http = is_ssl()?'https://':'http://';
	return $http.$server.__ROOT__;
}
/**
 * 设置当前页面对象
 * @param int 0-用户  1-商家
 */
function LoginTarget($target = 0){
	$WST_USER = session('WST_USER');
	$WST_USER['loginTarget'] = $target;
	session('WST_USER',$WST_USER);
}

function compareDistance($a,$b){
	if(!isset($a['distance'])||(!isset($b['distance']))){
		return "compareDistance()：传入参数没有distance参数";
	}
	// if($a['distance']==$b['distance'])
	// 	return 0;
	// return ($a['distance']<$b['distance'])?-1:1;
	//echo "<br/>a['distance']=".$a['distance'].":b['distance']".$b['distance']."<br/>";
	//$a=strcmp($a['distance'], $b['distance']);
	$tmp=((int)$a['distance']<(int)$b['distance'])?-1:1;
	//echo "strcmp=".$tmp."<br/>";
	return $tmp;//strcmp($a['distance'], $b['distance']);
}

function compareSale($a,$b){
    if(!isset($a['monthOrderCnt'])||(!isset($b['monthOrderCnt']))){
        return "compareSale()：传入参数没有monthOrderCnt参数";
    }
    $tmp=((int)$a['monthOrderCnt']>(int)$b['monthOrderCnt'])?-1:1;
    return $tmp;
}

function compareDeliveryCost($a,$b){
    if(!isset($a['deliveryMoney'])||(!isset($b['deliveryMoney']))){
        return "compareDeliveryCost()：传入参数没有deliveryMoney参数";
    }
    $tmp=((int)$a['deliveryStartMoney']<(int)$b['deliveryStartMoney'])?-1:1;
    return $tmp;
}

function  microtime_float ()
{
    list( $usec ,  $sec ) =  explode ( " " ,  microtime ());
    return ((float) $usec  + (float) $sec );
}

/*HTTP GET请求
 *注意：该函数只能处理Body是JSON数据格式的数据，若响应Body不是JSON数据
 *则会返回空
 *@prama url GET请求URL
 *@prama prama GET请求参数
 返回值：GET响应的Body，以数组形式返回
*/
function get($url,$param=array()){


    if(!is_array($param)){
        throw new Exception("参数必须为array");
    }
    $p='';
    foreach($param as $key => $value){
        $p=$p.$key.'='.$value.'&';
    }
    if(preg_match('/\?[\d\D]+/',$url)){//matched ?c
        $p='&'.$p;
    }else if(preg_match('/\?$/',$url)){//matched ?$
        $p=$p;
    }else{
        $p='?'.$p;
    }
    $p=preg_replace('/&$/','',$p);
    if(get_magic_quotes_gpc()){$p = stripslashes($p);}
    $url=$url.$p;
    $httph =curl_init($url);
    curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($httph,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
    
    curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($httph, CURLOPT_HEADER,false);
    $rst=curl_exec($httph);
    curl_close($httph);
    $arr=json_decode($rst,true);
    return $arr;
}
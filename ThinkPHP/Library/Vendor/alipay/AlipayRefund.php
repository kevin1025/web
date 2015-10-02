<?php

require_once("AlipayConfig.php");
require_once("lib/alipay_submit.class.php");

class AlipayRefund{
	private $alipay_config = array();
	private $notify_url = "http://商户网关地址/refund_fastpay_by_platform_pwd-PHP-UTF-8/notify_url.php";
	function __construct(){
		$alipayConfig = new AlipayConfig();
		$this->alipay_config = $alipayConfig->getConfig();
	}
	public function refund($refundInfo = array()){
		
		$parameter = array(
			"service" => "refund_fastpay_by_platform_pwd",
			"partner" => trim($this->alipay_config['partner']),
			"notify_url"	=> $this->notify_url,
			"seller_email"	=> $this->alipay_config['seller_email'],
			"refund_date"	=> $refundInfo['refund_date'],
			"batch_no"	=> $refundInfo['batch_no'],
			"batch_num"	=> $refundInfo['batch_num'],
			"detail_data"	=> $refundInfo['detail_data'],
			"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
		);

		//建立请求
		$alipaySubmit = new AlipaySubmit($this->alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		//$html_text = $alipaySubmit->buildRequestHttp($parameter);
		echo $html_text;
	}
}

?>
<?php
	class payamakaria extends WP_SMS {
		private $wsdl_link = "http://api.payamak-panel.com/post/send.asmx?WSDL";
		public $tariff = "http://www.payamakaria.ir/";
		public $unitrial = true;
		public $unit;
		public $flash = "disable";
		public $isflash = false;

		public function __construct() {
			parent::__construct();
			$this->validateNumber = "09xxxxxxxx";
			
			ini_set("soap.wsdl_cache_enabled", "0");
		}

		public function SendSMS() {
			// Check credit for the gateway
			if(!$this->GetCredit()) return;
			
			/**
			 * Modify sender number
			 *
			 * @since 3.4
			 * @param string $this->from sender number.
			 */
			$this->from = apply_filters('wp_sms_from', $this->from);
			
			/**
			 * Modify Receiver number
			 *
			 * @since 3.4
			 * @param array $this->to receiver number
			 */
			$this->to = apply_filters('wp_sms_to', $this->to);
			
			/**
			 * Modify text message
			 *
			 * @since 3.4
			 * @param string $this->msg text message.
			 */
			$this->msg = apply_filters('wp_sms_msg', $this->msg);
			
			try
			{
				$client = new SoapClient($this->wsdl_link);
				$parameters['username'] = $this->username;
				$parameters['password'] = $this->password;
				$parameters['from'] = $this->from;
				$parameters['to'] = $this->to;
				$parameters['text'] = $this->msg;
				$parameters['isflash'] = $this->isflash;
				$parameters['udh'] = "";
				$parameters['recId'] = array(0);
				$parameters['status'] = 0x0;
				
				$result = $client->SendSms($parameters)->SendSmsResult;
				
				$this->InsertToDB($this->from, $this->msg, $this->to);
				
				/**
				 * Run hook after send sms.
				 *
				 * @since 2.4
				 * @param string $result result output.
				 */
				do_action('wp_sms_send', $result);
				
				return $result;
			}
			catch(SoapFault $ex)
			{
				echo $ex->faultstring;
			}
		}

		public function GetCredit() {
			try
			{
				$client = new SoapClient($this->wsdl_link);
				return $client->GetCredit(array("username" => $this->username, "password" => $this->password))->GetCreditResult;
			}
			catch(SoapFault $ex)
			{
				echo $ex->faultstring;
			}
		}
	}
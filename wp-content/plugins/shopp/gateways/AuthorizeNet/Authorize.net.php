<?php
/**
 * Authorize.Net
 * @class AuthorizeNet
 *
 * @author Jonathan Davis
 * @version 1.2
 * @copyright Ingenesis Limited, 30 March, 2008
 * @package Shopp
 * @since 1.2
 * @subpackage AuthorizeNet
 *
 * $Id: Authorize.net.php 17 2012-02-07 15:42:35Z jdillick $
 **/

class AuthorizeNet extends GatewayFramework implements GatewayModule {

	var $cards = array("visa", "mc", "amex", "disc", "jcb", "dc");
	var $secure = true;
	var $captures = true;
	var $refunds = true;
	var $xml = true; // for ARB

	// AIM parameters
	var $liveurl = 'https://secure.authorize.net/gateway/transact.dll';
	var $testurl = 'https://test.authorize.net/gateway/transact.dll';
	var $xtypes = array('sale'=>'AUTH_CAPTURE', 'auth'=>'AUTH_ONLY', 'capture'=>'PRIOR_AUTH_CAPTURE', 'refund'=>'CREDIT', 'void'=>'VOID');

	var $Arb; // placeholder for ARB object

	function __construct() {
		parent::__construct();
		$this->setup('login','password','testmode');

		add_filter('shopp_authorizenet_url',array($this,'url'));

		// handing auth and auth-capture in sale method
		add_action('shopp_authorizenet_sale',array($this,'sale'));
		add_action('shopp_authorizenet_auth',array($this,'sale'));

		add_action('shopp_authorizenet_void',array($this,'void'));
		add_action('shopp_authorizenet_capture',array($this,'capture'));
		add_action('shopp_authorizenet_refund',array($this,'refund'));

		if ( isset($this->settings['arb']) && str_true($this->settings['arb']) )
			$this->Arb = new AuthorizeNetARB ( $this->settings );
	}

	function actions () {}

	// Handle both auth-only and auth-capture
	function sale ( $Event ) {
		$_ = array();
		$_ = $this->header($_);
		$_ = $this->payment($_, $Event->name);

		$response = $this->send($_);

		if (!$response || is_a($response,'ShoppError')) {
			return shopp_add_order_event($Event->order,'auth-fail',array(
				'amount' => $Event->amount,			// Amount to be captured
				'error' => $response->code,			// Error code (if provided)
				'message' => join(' ',$response->messages),	// Error message reported by the gateway
				'gateway' => $Event->gateway		// Gateway handler name (module name from @subpackage)
			));
		}

		$Paymethod = $this->Order->paymethod();
		$Billing = $this->Order->Billing;

		// authorized or held
		if ( in_array($response->code, array(1,4)) ) {
			shopp_add_order_event($Event->order,'authed',array(
				'txnid' => $this->txnid($response),		// Transaction ID
				'amount' => $response->amount,			// Gross amount authorized
				'gateway' => $Paymethod->processor,		// Gateway handler name (module name from @subpackage)
				'paymethod' => $Paymethod->label,		// Payment method (payment method label from payment settings)
				'paytype' => $Billing->cardtype,		// Type of payment (check, MasterCard, etc)
				'payid' => $Billing->card,				// Payment ID (last 4 of card or check number)
				'capture' => ('sale' == $Event->name )	// True if the payment was captured
			));

			/*
				TODO add log order event for held transactions
			*/
		}
	}

	function capture ( $Event ) {
		$_ = array();
		$_ = $this->header($_);

		$_['x_type'] = $this->xtypes[$Event->name];
		$_['x_trans_id'] = $Event->txnid;

		$response = $this->send($_);

		if ( is_a($response, 'ShoppError') ) {
			return shopp_add_order_event($Event->order, 'capture-fail', array(
				'amount' => $Event->amount,					// Amount to be captured
				'error' => $response->code,					// Error code (if provided)
				'message' => join(' ', $response->messages),// Error message reported by the gateway
				'gateway' => $Event->gateway				// Gateway handler name (module name from @subpackage)
			));
		}


		$txnid = $this->txnid($response->transactionid);

		// authorized or held
		if ( in_array($response->code, array(1,4)) ) {
			shopp_add_order_event($Event->order,'captured',array(
				'txnid' => $txnid,				// Transaction ID of the CAPTURE event
				'amount' => $Event->amount,		// Amount captured
				'fees' => 0.0,					// Transaction fees taken by the gateway net revenue = amount-fees
				'gateway' => $Event->gateway	// Gateway handler name (module name from @subpackage)
			));

			/*
				TODO add log order event for held transactions
			*/
		}
	}

	function void ( $Event ) {
		$Order = shopp_order ( $Event->txnid, 'trans' );

		$_ = array();
		$_ = $this->header($_);

		$_['x_type'] = $this->xtypes[$Event->name];
		$_['x_trans_id'] = $Event->txnid;
		$response = $this->send($_);


		$txnid = $this->txnid($response->transactionid);
		if ( is_a($response, 'ShoppError') ) {
			return shopp_add_order_event($Event->order, 'void-fail', array(
				'error' => $response->code,						// Error code (if provided)
				'message' => join( ' ', $response->messages ),	// Error message reported by the gateway
				'gateway' => $Event->gateway					// Gateway handler name (module name from @subpackage)
			));
		}

		shopp_add_order_event($Event->order, 'voided', array(
			'txnorigin' => $Event->txnid,		// Original transaction ID (txnid of original Purchase record)
			'txnid' => $txnid,					// Transaction ID for the VOID event
			'gateway' => $Event->gateway		// Gateway handler name (module name from @subpackage)
		));
	}

	function refund ( $Event ) {
		$Order = shopp_order ( $Event->order );

		$_ = array();
		$_ = $this->header($_);

		$_['x_type'] = $this->xtypes[$Event->name];
		$_['x_trans_id'] = $Event->txnid;
		$_['x_amount'] = $Event->amount;

		$_['x_card_num'] = $Order->card;
		$_['x_exp_date'] = _d('my', $Purchase->cardexpires);

		$response = $this->send($_);

		if ( is_a($response, 'ShoppError') ) {
			return shopp_add_order_event($Event->order,'refund-fail',array(
				'amount' => $Event->amount,					// Amount of the refund attempt
				'error' => $response->code,					// Error code (if provided)
				'message' => join(' ',$response->messages),	// Error message reported by the gateway
				'gateway' => $Event->gateway				// Gateway handler name (module name from @subpackage)
			));
		}

		$txnid = $this->txnid($response->transactionid);

		shopp_add_order_event($Event->order, 'refunded', array(
			'txnid' => $txnid,				// Transaction ID for the REFUND event
			'amount' => $response->amount,	// Amount refunded
			'gateway' => $Event->gateway	// Gateway handler name (module name from @subpackage)
		));

	}

	function header ( $_ ) {
		$_['x_test_request']		= str_true($this->settings['testmode'])?'TRUE':'FALSE'; // Set TRUE while testing
		$_['x_login'] 				= $this->settings['login'];
		$_['x_tran_key'] 			= $this->settings['password'];
		$_['x_Delim_Data'] 			= "TRUE";
		$_['x_Delim_Char'] 			= "|";
		$_['x_Encap_Char'] 			= "";
		$_['x_version'] 			= "3.1";
		$_['x_relay_response']		= "FALSE";
		$_['x_method']				= "CC";
		$_['x_email_customer']		= "FALSE";
		$_['x_merchant_email']		= $this->settings['merchant_email'];

		return $_;
	}

	function txnid ($response) {
		if ( ! isset($response->transactionid) || empty($response->transactionid) ) return parent::txnid();
		return $response->transactionid;
	}

	function error ($Response) {
		return new ShoppError($Response->reason,'authorize_net_error',SHOPP_TRXN_ERR,
			array('code'=>$Response->reasoncode));
	}

	function payment ($_, $type ) {
		$Order = ShoppOrder();
		$Customer = $Order->Customer;
		$Billing = $Order->Billing;
		$Shipping = $Order->Shipping;
		$Cart = $Order->Cart;
		$Totals = $Cart->Totals;

		// Options
		$_['x_type'] 				= $this->xtypes[$type];

		// Required Fields
		$_['x_amount']				= $Totals->total;
		$_['x_customer_ip']			= $_SERVER["REMOTE_ADDR"];
		$_['x_fp_sequence']			= time();
		$_['x_fp_timestamp']		= time();
		// $_['x_fp_hash']				= hash_hmac("md5","{$_['x_login']}^{$_['x_fp_sequence']}^{$_['x_fp_timestamp']}^{$_['x_amount']}",$_['x_tran_key']);

		// Customer Contact
		$_['x_first_name']			= $Customer->firstname;
		$_['x_last_name']			= $Customer->lastname;
		$_['x_email']				= $Customer->email;
		$_['x_phone']				= $Customer->phone;

		// Billing
		$_['x_card_num']			= $Billing->card;
		$_['x_exp_date']			= date("my",$Billing->cardexpires);
		$_['x_card_code']			= $Billing->cvv;
		$_['x_address']				= $Billing->address;
		$_['x_city']				= $Billing->city;
		$_['x_state']				= $Billing->state;
		$_['x_zip']					= $Billing->postcode;
		$_['x_country']				= $Billing->country;

		// Shipping
		$_['x_ship_to_first_name']  = $Customer->firstname;
		$_['x_ship_to_last_name']	= $Customer->lastname;
		$_['x_ship_to_address']		= $Shipping->address;
		$_['x_ship_to_city']		= $Shipping->city;
		$_['x_ship_to_state']		= $Shipping->state;
		$_['x_ship_to_zip']			= $Shipping->postcode;
		$_['x_ship_to_country']		= $Shipping->country;

		// Transaction
		$_['x_freight']				= $Totals->shipping;
		$_['x_tax']					= $Totals->tax;

		// Line Items
		$i = 1;
		foreach($Cart->contents as $Item) {
			$_['x_line_item'][] = $this->ascii_filter(
				($i++)."<|>".
				self::truncate_str($Item->name,31)."<|>".
				( ( sizeof($Item->options) > 1 ) ? " (".self::truncate_str($Item->option->label,253).")" : "" )."<|>".
				(int)$Item->quantity."<|>".
				number_format($Item->unitprice,$this->precision,'.','')."<|>".
				(($Item->tax)?"Y":"N"));
		}

		return $_;
	}

	function send ($data) {
		$url = apply_filters('shopp_authorizenet_url',$url);

		$request = $this->encode($data);

		$response = parent::send($request, $url);
		new ShoppError('RESPONSE: '.$response,false,SHOPP_DEBUG_ERR);
		$response = $this->response($response);

		if (!$response) return false;

		// not authorized or held
		if ( ! in_array($response->code, array(1, 4)) ) {
			return $this->error($response);
		}

		return $response;
	}

	function response ($buffer) {
		$_ = new stdClass();
		list($_->code,
			 $_->subcode,
			 $_->reasoncode,
			 $_->reason,
			 $_->authcode,
			 $_->avs,
			 $_->transactionid,
			 $_->invoicenum,
			 $_->description,
			 $_->amount,
			 $_->method,
			 $_->type,
			 $_->customerid,
			 $_->firstname,
			 $_->lastname,
			 $_->company,
			 $_->address,
			 $_->city,
			 $_->state,
			 $_->zip,
			 $_->country,
			 $_->phone,
			 $_->fax,
			 $_->email,
			 $_->ship_to_first_name,
			 $_->ship_to_last_name,
			 $_->ship_to_company,
			 $_->ship_to_address,
			 $_->ship_to_city,
			 $_->ship_to_state,
			 $_->ship_to_zip,
			 $_->ship_to_country,
			 $_->tax,
			 $_->duty,
			 $_->freight,
			 $_->taxexempt,
			 $_->ponum,
			 $_->md5hash,
			 $_->cvv2code,
			 $_->cvv2response) = explode("|",$buffer);

		return $_;
	}

	function url ($url) {
		if (str_true($this->settings['testmode'])) return $this->testurl;
		return $this->liveurl;
	}

	function settings () {
		$this->ui->cardmenu(0,array(
			'name' => 'cards',
			'selected' => $this->settings['cards']
		),$this->cards);

		$this->ui->text(1,array(
			'name' => 'login',
			'value' => $this->settings['login'],
			'size' => '16',
			'label' => __('Enter your AuthorizeNet API Login ID.','Shopp')
		));

		$this->ui->password(1,array(
			'name' => 'password',
			'value' => $this->settings['password'],
			'size' => '24',
			'label' => __('Enter your AuthorizeNet Transaction Key.','Shopp')
		));

		$this->ui->checkbox(1,array(
			'name' => 'testmode',
			'checked' => $this->settings['testmode'],
			'label' => __('Enable test mode','Shopp')
		));

	}

	static function truncate_str ( $string = '', $maxlen = 0 ) {
		if ( $maxlen < 1 || empty($string) ) return "";

		$i = 0;
		$truncated = substr( $string, 0, $maxlen - $i++ );
		while ( strlen( urlencode($truncated) ) > $maxlen ) {
			$truncated = substr($string, 0, $maxlen - $i++);
		}
		return $truncated;
	}

} // END class AuthorizeNet

/**
 * AuthorizeNetARB is used to process Authorize.net Automated Recurring Billing
 *
 * @author John Dillick
 * @since 1.2
 * @package shopp
 * @subpackage AuthorizeNetARB
 **/
class AuthorizeNetARB {
	var $urls = array(
		'live' => 'https://api.authorize.net/xml/v1/request.api',
		'test' => 'https://apitest.authorize.net/xml/v1/request.api',
		'schema' => 'https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd'
		);
	var $settings;

	function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * ARBCreateSubscriptionRequest
	 *
	 * @author John Dillick
	 * @since 1.2
	 *
	 * @return void Description...
	 **/
	function create ( $Event ) {
		$_ = array();
		$_ = $this->header($_, 'ARBCreateSubscriptionRequest');
		$_ = $this->footer($_, 'ARBCreateSubscriptionRequest');
	}

	/**
	 * ARBUpdateSubscriptionRequest
	 *
	 * @author John Dillick
	 * @since 1.2
	 *
	 * @return void Description...
	 **/
	function update ( $Event ) {
		$_ = array();
		$_ = $this->header($_, 'ARBCreateSubscriptionRequest');
		/*
			TODO add ARBUpdateSubscriptionRequest support
		*/
		$_ = $this->footer($_, 'ARBCreateSubscriptionRequest');
	}

	/**
	 * ARBGetSubscriptionStatusRequest
	 *
	 * @author John Dillick
	 * @since 1.2
	 *
	 * @return void Description...
	 **/
	function status ( $Event ) {
		$_ = array();
		$_ = $this->header($_, 'ARBGetSubscriptionStatusRequest');
		/*
			TODO add ARBGetSubscriptionStatusRequest support
		*/
		$_ = $this->footer($_, 'ARBGetSubscriptionStatusRequest');
	}

	/**
	 * ARBCancelSusbscriptionRequest
	 *
	 * @author John Dillick
	 * @since 1.2
	 *
	 * @return void Description...
	 **/
	function cancel ( $Event ) {
		$_ = array();
		$_ = $this->header($_, 'ARBCancelSusbscriptionRequest');
		/*
			TODO add ARBCancelSusbscriptionRequest support
		*/
		$_ = $this->footer($_, 'ARBCancelSusbscriptionRequest');
	}

	function header ( $_ = array(), $type ) {
		$_[] = '<?xml version="1.0" encoding="utf-8"?>';
		$_[] = "<$type xmlns=\"".$this->urls['schema']."\">";
		return $_;
	}

	function footer ( $_ = array(), $type ) {
		$_[] = '</'.$type.">";
		return $_;
	}

} // END class AuthorizeNetARB

?>
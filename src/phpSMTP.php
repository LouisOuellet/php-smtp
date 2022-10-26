<?php

//Declaring namespace
namespace LaswitchTech\SMTP;

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class phpSMTP{

	protected $Mailer;
	protected $Status = false;
  protected $TEXT = [
		"Sincerely" => "Sincerely",
		"TM and copyright" => "TM and copyright",
		"All Rights Reserved" => "All Rights Reserved",
		"Privacy Policy" => "Privacy Policy",
		"Support" => "Support",
		"This message was sent to you from an email address that does not accept incoming messages" => "This message was sent to you from an email address that does not accept incoming messages",
		"Any replies to this message will not be read. If you have questions, please visit" => "Any replies to this message will not be read. If you have questions, please visit",
	];
	protected $VAR = [
		"BRAND" => "Brand",
		"LOGO" => "https://github.com/LouisOuellet/php-smtp/raw/stable/dist/img/logo.png",
		"FROM" => null,
		"TO" => null,
		"CC" => null,
		"BCC" => null,
		"REPLY-TO" => null,
		"SUBJECT" => "Subject",
		"TITLE" => "Title",
		"MESSAGE" => "Message",
		"COPYRIGHT" => null,
		"TRADEMARK" => "https://domain.com/trademark",
		"POLICY" => "https://domain.com/policy",
		"SUPPORT" => "https://domain.com/support",
		"CONTACT" => "https://domain.com/contact",
	];
	protected $Template = null;
	protected $URL = null;

	public function __construct($smtp = null){
		// Setup URL
		if(isset($_SERVER['HTTP_HOST'])){
			$this->URL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://";
			$this->URL .= $_SERVER['HTTP_HOST'].'/';
		}

		// Setup VAR
		$this->setVar([
			"LOGO" => $this->URL."logo.png",
			"TRADEMARK" => $this->URL."trademark",
			"POLICY" => $this->URL."policy",
			"SUPPORT" => $this->URL."support",
			"CONTACT" => $this->URL."contact",
			"COPYRIGHT" => intval(date('Y')),
		]);

		// Setup Template
		$this->setTemplate(dirname(__FILE__).'/templates/default.html');

		// Setup Connection
		$this->connect($smtp);
	}

	public function setText($Array = []){
		foreach($Array as $key => $value){
			if(isset($this->TEXT[$key]) && is_string($value)){ $this->TEXT[$key] = $value; }
		}
	}

	public function setVar($Array = []){
		foreach($Array as $key => $value){
			if(array_key_exists($key, $this->VAR)){
				switch($key){
					case"FROM":
					case"CC":
					case"BCC":
					case"TO":
					case"REPLY-TO":
						if(filter_var($value, FILTER_VALIDATE_EMAIL) || $value == null){ $this->VAR[$key] = $value; }
						break;
					case"SUBJECT";
					case"TITLE";
					case"MESSAGE";
						if(is_string($value)){ $this->VAR[$key] = $value; }
						break;
					case"COPYRIGHT";
						if(is_int($value)){ $this->VAR[$key] = $value; }
						break;
					default:
						$this->VAR[$key] = $value;
						break;
				}
			}
			if(in_array($key,["FROM","CC","BCC","REPLY-TO","TO"])){
				if(filter_var($value, FILTER_VALIDATE_EMAIL) || $value == null){ $this->VAR[$key] = $value; }
			} else {
				if(isset($this->VAR[$key])){ $this->VAR[$key] = $value; }
			}
		}
	}

	public function setTemplate($file = null){
		if($file != null && is_file($file)){
			$this->Template = $file;
		} elseif($file != null){ $this->Template = $file; }
	}

	public function isConnected(){
		return is_bool($this->Status) && $this->Status ? true:false;
	}

	public function login($username,$password,$host,$port,$encryption = null){
		//Create a new SMTP instance
		$smtp = new SMTP;
		//Enable connection-level debug output
		// $smtp->do_debug = SMTP::DEBUG_CONNECTION;
		try {
			$options = [];
			// Set Encryption
			if(in_array($encryption,['SSL','ssl'])){
				$host = 'ssl://'.$host;
				$options = [
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					]
				];
			}
	    //Connect to an SMTP server
	    if (!$smtp->connect($host,$port,30,$options)) { throw new Exception('Connect failed'); }
	    //Say hello
	    if (!$smtp->hello(gethostname())) { throw new Exception('EHLO failed: ' . $smtp->getError()['error']); }
	    //Get the list of ESMTP services the server offers
	    $e = $smtp->getServerExtList();
	    //If server can do TLS encryption, use it
	    if (is_array($e) && array_key_exists('STARTTLS', $e)) {
        $tlsok = $smtp->startTLS();
        if (!$tlsok) {
          throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
        }
        //Repeat EHLO after STARTTLS
        if (!$smtp->hello(gethostname())) {
          throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
        }
        //Get new capabilities list, which will usually now include AUTH if it didn't before
        $e = $smtp->getServerExtList();
	    }
	    //If server supports authentication, do it (even if no encryption)
	    if (is_array($e) && array_key_exists('AUTH', $e)) {
        if ($smtp->authenticate($username, $password)) {
          return true;
        } else {
          throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
        }
	    }
		} catch (Exception $e) {
			error_log('SMTP error: '.$e->getMessage(), 0);
	    return false;
		}
	}

	public function connect($smtp = null){
		if($smtp != null && $this->login($smtp['username'],$smtp['password'],$smtp['host'],$smtp['port'],$smtp['encryption'])){
			$this->Status = true;
			$this->Mailer = new PHPMailer(true);
			$this->Mailer->isSMTP();
	    $this->Mailer->Host = $smtp['host'];
	    $this->Mailer->SMTPAuth = true;
	    $this->Mailer->Username = $smtp['username'];
	    $this->Mailer->Password = $smtp['password'];
			$this->Mailer->SMTPDebug = false;
			if(in_array($smtp['encryption'],['STARTTLS','starttls'])){ $this->Mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
			if(in_array($smtp['encryption'],['SSL','ssl'])){
				$this->Mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
				$this->Mailer->SMTPOptions = [
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					]
				];
			}
	    $this->Mailer->Port = $smtp['port'];
		}
	}

	protected function isHTML($string){
		return preg_match("/<[^<]+>/",$string,$m) != 0;
	}

	public function send($array = []){
		$this->setVar($array);
		if($this->isConnected() && $this->VAR['TO'] != null){
			$this->Mailer->ClearAllRecipients();
			$this->Mailer->addAddress($this->VAR['TO']);
			if($this->VAR['FROM'] != null){ $this->Mailer->setFrom($this->VAR['FROM']); }
			else { $this->Mailer->setFrom($this->Mailer->Username); }
			if($this->VAR['REPLY-TO'] != null){ $this->Mailer->addReplyTo($this->VAR['REPLY-TO']); }
			if($this->VAR['CC'] != null){ $this->Mailer->addCC($this->VAR['CC']); }
			if($this->VAR['BCC'] != null){ $this->Mailer->addBCC($this->VAR['BCC']); }
			$this->Mailer->clearAttachments();
			if((isset($array['FILES']))&&(is_array($array['FILES']))){
				foreach($array['FILES'] as $attachment){
					$this->Mailer->addAttachment($attachment);
				}
			}
			$this->Mailer->Subject = $this->VAR['SUBJECT'];
			if($this->Template == null){ $this->Mailer->Body = $this->VAR['MESSAGE']; }
			else { $this->Mailer->Body = file_get_contents($this->Template); }
			$this->Mailer->isHTML($this->isHTML($this->Mailer->Body));
			foreach($this->TEXT as $key => $value){
				$this->Mailer->Body = str_replace('[TEXT-'.$key.']',$value,$this->Mailer->Body);
			}
			foreach($this->VAR as $key => $value){
				$this->Mailer->Body = str_replace('[VAR-'.$key.']',$value,$this->Mailer->Body);
			}
			try { $this->Mailer->send(); return true; }
			catch (phpmailerException $e) { error_log($e, 0);return false; }
			catch (Exception $e) { error_log($e, 0);return false; }
		} else { return false; }
	}
}

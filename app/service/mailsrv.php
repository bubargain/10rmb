<?php
/**
 * @desc send email
 */

namespace app\service;

class MailSrv extends BaseSrv {
	
	public function sendMail($to,$title,$txt)
	{
			require_once( ROOT_PATH. '/app/common/mail/class.phpmailer.php');
			$mail= new \PHPMailer;
			$mail->isSMTP();
			$mail->Charset='UTF-8';         
			$mail->Encoding = "base64";//设置文本编码方式 
			                             // Set mailer to use SMTP
			$mail->Host = BUCK_MAIL_SMTP;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = BUCK_MAIL_HOST;                 // SMTP username
			$mail->Password = BUCK_MAIL_PASS;                           // SMTP password
			$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 465;                                    // TCP port to connect to
				
			$mail->From = BUCK_MAIL_HOST;
			$mail->FromName = '10buck';
				//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
			$mail->addAddress($to);               // Name is optional
				//$mail->addReplyTo('info@example.com', 'Information');
				//$mail->addCC('cc@example.com');
				//$mail->addBCC('bcc@example.com'); 
				
				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML				
			$mail->Subject =  "=?UTF-8?B?".base64_encode($title)."?=";	
			$mail->Body    = $txt;
			$mail->AltBody = 'none';
				
			if(!$mail->send()) {
				    return false;
				    
				} else {
				  return true;
			}
	}

}
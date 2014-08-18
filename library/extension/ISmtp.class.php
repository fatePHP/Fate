<?php  defined('IN_FATE') or die('access denied!');

			/**
			 *@brief SMTP�ʼ�������
			 *@param $host  SMTP��������ַ
			 *@param $port; �˿ں�
			 *@param $type; ���� ����������SMTP������
			 *@param $user; �û���
			 *@param $pass; ����
			 *@param $auth  �Ƿ�������֤
			 *@param $debug �Ƿ������Ϣ
			 **/
			 class ISmtp {
			 		
			 		 private $host;
					 private $port;
					 private $type;
					 private $user;
					 private $pass;
					 private $auth;
					 private $socket;
					 private $debug; 
					 private $timeout = 40;
					 private $local = 'localhost';
					 
					 /**
					  * @brief ���캯�� 
					  **/
					 public function __construct($host,$port=25,$user=false,$pass=false){
					 	
					 				$this->host = $host;
					 				$this->port = $port;
					 				$this->user = $user;
					 				$this->pass = $pass;
					 				$this->auth = ($user||$pass)? true:false;
					 			
					 }
					 /**
					  * @brief ���˵�С�����ڵ����� 
					  **/
					 private function stripAddress($address){
					 	
									$comment = "\\([^()]*\\)";
									while (preg_match('/'.$comment.'/i', $address))
									{
										$address = preg_replace('/'.$comment.'/i', "", $address);
									}
							
									return $address;
					 }
					 
					 /**
					  * @brief �����ʼ����͵�ַ
					  **/
					 private function getAddress($address){
					 		
							$address = preg_replace("/([ \t\r\n])+/i", "", $address);
							$address = preg_replace("/^.*<(.+)>.*$/i", "\\1", $address);
					
							return $address;
					 }
					 
					 /**
					  * @brief �����ʼ�
					  **/
					 public function sendMail($to,$from='',$subject='',$body='',$additional_header='',$contentType='html',$cc='',$darkcc=''){
					 	
					 		$from = $this->getAddress($this->stripAddress($from));
					 		$body = preg_replace("/(^|(\r\n))(\\.)/i", "\\1.\\3", $body);
					 		$header  = "MIME-Version:1.0\r\n";
					 		if($contentType='html'){
					 			$header.="Content-Type:text/html\r\n";
					 		}
					 		$header.="To: $to\r\n";
					 		!empty($cc)&& $header.="Cc: $cc\r\n"; 
							$header.="From: $from<{$from}>\r\n";
							$header.="Subject: $subject\r\n";
							$header.=$additional_header;
							$header.="Date: ".date('r')."\r\n";
							$header.="X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
							list($msec,$sec) = explode(" ",microtime());
							$header.="Message-ID: <".date("YmdHis",$sec).".".($msec*1000000).".".$from.">\r\n";
							$TO = explode(",",$this->stripAddress($to));
							!empty($cc) && $TO = array_merge($TO,explode(",",$this->stripAddress($cc)));
							!empty($darkcc) && $TO = array_merge($TO,explode(",",$this->stripAddress($darkcc)));
							$sent = TRUE;
							
							foreach ($TO as $mailTo){
							
							     if(empty($this->host)){  //��δָ��SMTP��������ַ ��PHP����mail����
									
										return mail($mailTo,'',$body,$header);
								   }
								   
								   $mailTo = $this->getAddress($mailTo);
								   
								   if(!$this->socketOpen($mailTo)){
								   			
								    	//�˴�������־��¼��ʧ��
							         $sent = false;
											 continue;
								   }
								   
								   if($this->smtpSend($this->local,$from,$mailTo,$header,$body)){
					
										//�˴���־��¼�����ʼ��ɹ�
								   }else{
								   	
										//�˴���־��¼�����ʼ�ʧ��
										$sent = false;
								   }
								   fclose($this->socket);
							}
							return $sent;
					 }
					 
					 /**
					  * @brief SMTP����������
					  * @param $helo ������SMTP��ַ
					  * @param $from ���͵�ַ
					  * @param $to   �ռ���ַ
					  * @param $header headerͷ��Ϣ
					  * @param $body   �ʼ�����
					  **/
					 public function smtpSend($helo, $from, $to, $header, $body = ""){
							
							if (!$this->smtp_putcmd("HELO", $helo))
							{
								return $this->smtp_error("sending HELO command");
							}

							if($this->auth)
							{
								if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user)))
								{
									return $this->smtp_error("sending HELO command");
								}

								if (!$this->smtp_putcmd("", base64_encode($this->pass)))
								{
									return $this->smtp_error("sending HELO command");
								}
							}
							if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">"))
							{
								return $this->smtp_error("sending MAIL FROM command");
							}

							if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">"))
							{
								return $this->smtp_error("sending RCPT TO command");
							}

							if (!$this->smtp_putcmd("DATA"))
							{
								return $this->smtp_error("sending DATA command");
							}

							if (!$this->smtp_message($header, $body))
							{
								return $this->smtp_error("sending message");
							}

							if (!$this->smtp_eom())
							{
								return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
							}

							if (!$this->smtp_putcmd("QUIT"))
							{
								return $this->smtp_error("sending QUIT command");
							}

							return TRUE;
					 }
					 
					 /**
					  *@brief ����socket
					  **/
					 public function socketOpen($address){
					 
							if(!empty($this->host)){
								
								return $this->socketOpenLocal();
							}else{
								return $this->socketOpenHost($address);
							}
					 }
					 
					 public function socketOpenLocal(){
					 
						  $this->socket = @fsockopen($this->host,$this->port,$errno,$errstr,$this->timeout);
							if(!($this->socket && $this->socketOk())){
							   //�˴���־��¼����
							   return false;
							}
							return true;
					 }
					 
					 public function socketOpenHost($address){
							
							$domain = preg_replace("/^.+@([^@]+)$/i", "\\1", $address);
							if (!@getmxrr($domain, $MXHOSTS))
							{
								//$this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
								return FALSE;
							}
							
							foreach ($MXHOSTS as $host)
							{
								//$this->log_write("Trying to ".$host.":".$this->smtp_port."\n");
								$this->socket = @fsockopen($host, $this->port, $errno, $errstr, $this->timeout);
								if (!($this->socket && $this->socketOk())) {
									//$this->log_write("Warning: Cannot connect to mx host ".$host."\n");
									//$this->log_write("Error: ".$errstr." (".$errno.")\n");
									continue;
								}
								//$this->log_write("Connected to mx host ".$host."\n");
								return TRUE;
							}
							
							//$this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
							return FALSE;
					 }
					 
					 public function socketOk(){
					 
					 		$response = str_replace("\r\n", "", fgets($this->socket, 512));
							//$this->smtp_debug($response."\n");

							if (!preg_match("/^[23]/i", $response))
							{
								fputs($this->socket, "QUIT\r\n");
								fgets($this->socket, 512);
								//$this->log_write("Error: Remote host returned \"".$response."\"\n");
								return FALSE;
							}
							return TRUE;
					 }
					 
					private function smtp_eom()
					{
						fwrite($this->socket, "\r\n.\r\n");
						$this->smtp_debug(". [EOM]\n");

						return $this->socketOk();
					}
					
					private function smtp_debug($message)
					{
						if ($this->debug)
						{
							echo $message."<br>";
						}
					}
					
					private function smtp_putcmd($cmd, $arg = "")
					{

						if ($arg != "")
						{
							if($cmd=="") $cmd = $arg;
							else $cmd = $cmd." ".$arg;
						}

						fwrite($this->socket, $cmd."\r\n");
						$this->smtp_debug("> ".$cmd."\n");

						return $this->socketOk();
					}

					private function smtp_error($string)
					{
						//$this->log_write("Error: Error occurred while ".$string.".\n");
						return FALSE;
					}
					
					/**
					 * @brief �ʼ�����Ϣ����
					 * @param string $header ͷ��Ϣ
					 * @param string $body ����
					 * @return bool ��Ϣ����״̬
					 */
					 
					private function smtp_message($header, $body)
					{
						
						fwrite($this->socket, $header."\r\n".$body);
						$this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
						return TRUE;
					}
				
			 
			 }


?>
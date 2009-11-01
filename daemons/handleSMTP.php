<?php
/********************************************************

xinetd file smtp:

service unlisted
              {
                     type                = UNLISTED
                     socket_type         = stream
                     protocol            = tcp
                     wait                = no
                     server              = /usr/bin/php
                     server_args        = /tmp/handleSmtp.php
                     port                = 25
                     user               = root
              }

***********************************************************/
set_time_limit('90');
file_put_contents("/tmp/connection","smtp connection\n",FILE_APPEND);
echo "220 clearhealth.local ESMTP\n";
$loop = 0;
$data4 = '';
$loadingData = false;
$email = '';
while (true) {
	$read[] = STDIN;
	stream_select($read, $write = null, $except = null, $tv = 0);
	if (count($read)) {
		$data4 = @fread(STDIN, 32768);
		$data4 = str_replace("\r\n", "\n", $data4);
		$data4 = str_replace("\n\r", "\n", $data4);
		$data4 = str_replace("\r", "\n", $data4);
		//$data4 = str_replace("\n", '', $data4);
	}
	$email .= @$data4;
//file_put_contents("/tmp/connection","stuff:" . @$data4 . "\n",FILE_APPEND);
	if (preg_match('/^HELO.*/',@$data4) || preg_match('/^EHLO.*/',@$data4)) {
		echo "250 clearhealth.local\n";
		$data4 = '';
	}
	elseif (preg_match('/^MAIL FROM.*/',@$data4)) {
		echo "250 Ok\n";
		$data4 = '';
	}
	elseif (preg_match('/^RCPT TO:.*/',@$data4)) {
		echo "250 Ok\n";
		$data4 = '';
	}
	elseif (preg_match('/^DATA.*/',@$data4)) {
		$loadingData = true;
		echo "354 End data with <CR><LF>.<CR><LF>\n";
		file_put_contents("/tmp/connection","354 end data" . "\n",FILE_APPEND);
		$data4 = '';
	}
	elseif ($loadingData == true && preg_match('/\.$/',@$data4)) {
		echo "250 ok 1251934559 qp 9841\n";
		file_put_contents("/tmp/connection","250 Ok: queued" . "\n",FILE_APPEND);
		$loadingData = false;
		$data4 = '';
	}
	elseif (preg_match('/^rset.*/',strtolower(@$data4)) || preg_match('/^noop.*/',strtolower(@$data4))) {
		echo "250 ok\n";
		file_put_contents("/tmp/connection","rset/noop\n",FILE_APPEND);
		$data4 = '';
	}
	elseif (preg_match('/^quit.*/',strtolower(@$data4))) {
		echo "221 clearhealth.local\n";
		$data4 = '';

		file_put_contents("/tmp/emails",$email . "\n\n\n\n",FILE_APPEND);
		exit;
	}
	elseif (false && !$loadingData && strlen($data4) > 0){
		echo "502 unimplemented (#5.5.1)\n";
		file_put_contents("/tmp/connection","unimplemented: " . substr(@$data4,0,10) . "\n\n\n",FILE_APPEND);
		$data4 = '';
	}
	else {
		//echo "250 ok\n";
		//$data4="";
	}	
	//unset($data4);
	usleep('1000');
	$loop++;
}

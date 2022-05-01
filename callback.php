<?php
$ServiceID	= 12345;	// WhatsApp Service Aktif Edilince Size Verilen WhatsApp Service ID
$YoncuApiID	= 12345;	// wwww.yoncu.com Kullanıcı Panelinden Alacağınız API ID ve Key Bilgileri
$YoncuApiKey= 'gk3g02g20g9j20g2gj209g3jg902gj2';

function YoncuWhatsAppAPI($Action,$Data){
	Global $ServiceID,$YoncuApiID,$YoncuApiKey;
	$Curl = curl_init("https://www.yoncu.com/API/WhatsApp/".$ServiceID."/".$Action);
	curl_setopt($Curl, CURLOPT_HEADER, false);
	curl_setopt($Curl, CURLOPT_ENCODING, false);
	curl_setopt($Curl, CURLOPT_COOKIESESSION, false);
	curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($Curl, CURLOPT_USERPWD,$YoncuApiID.":".$YoncuApiKey);
	curl_setopt($Curl, CURLOPT_HTTPHEADER, array(
		'Connection: keep-alive',
		'Accept: application/json',
		'User-Agent: '.$_SERVER['SERVER_NAME'],
		'Referer: http://www.yoncu.com/',
		'Cookie: YoncuKoruma='.$_SERVER['SERVER_ADDR'].';YoncuKorumaRisk=0',
	));
	curl_setopt($Curl, CURLOPT_POSTFIELDS,json_encode($Data));
	$Response	= curl_exec($Curl);
	curl_close($Curl);
	return json_decode($Response,true);
}
$Return	= [false,'Geçersiz Bilgi'];
$Post	= @file_get_contents('php://input');
if(!empty($Post)){
	if($Json=json_decode($Post,true)){
		if($Json['Action'] == "Received"){	// Received Message
			if(isset($Json['Senders'])){
				$Errors	= [];
				foreach($Json['Senders'] as $Say => $Sender){
					if(isset($Sender['Name'],$Sender['Phone'],$Sender['Files'],$Sender['Messages'])){
						if(is_array($Sender['Messages']) and count($Sender['Messages']) > 0){
							foreach($Sender['Messages'] as $Time => $Message){
								$Message	= trim(strip_tags($Message));
								// Telefon: $Sender['Phone']
								// Mesaj: $Message
								// Mesajı Buraya Fonksiyon Yazarak Sitenize/Veritabanına Ekleyebilir, Mail Gönderebilir veya https://www.yoncu.com/whatsapp adresindeki Send API ile otomatik cevaplayabilirsiniz.

								// Örnek Cevaplama:
								list($Status,$Info)	= YoncuWhatsAppAPI('Send',[
									'Phone'		=> $Sender['Phone'],
									'Message'	=> "Mesajınız Alınmıştır, teşekkürler.\n*".$_SERVER['HTTP_HOST']."*",
								]);
								if(empty($Status)){
									$Errors[]	= $Info;
								}
							}
						}
						if(is_array($Sender['Files']) and count($Sender['Files']) > 0){
							foreach($Sender['Files'] as $Zaman => $File){
								$FileName	= trim(strip_tags($File));
								// Telefon: $Sender['Phone']
								// DosyaAdi: $FileName
								// Dosya icerigini https://www.yoncu.com/whatsapp adresindeki FileDownload API ile çekebilirsiniz.
								/**
								// Örnek Dosya Çekme Kodu:
								list($Status,$FileSource)	= YoncuWhatsAppAPI('FileDownload',['Phone'=>$Sender['Phone'],'File'=>$FileName,]);
								if(empty($Status) or strlen($FileSource) < 100){
									$Errors[]	= $FileSource;
								}
								*/
							}
						}
					}
				}
				if(empty($Errors)){
					$Return	= [true,'All Messages Accepted'];
				}
			}
		}
	}
}
exit(json_encode($Return));

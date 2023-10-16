<?php
require("./common.php");
require("../librerie/librerie.php");

require '../librerie/mail/class.phpmailer.php';
require("../librerie/mail/lib.mail.php");
include('../librerie/SmsHostingSms.php');

global $db;
global $db_front;
 
$paction=get_param("_action");

switch($paction)
{
	case "login":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);  		

		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);
		$pprocedura=get_param("procedura");
		$pnapoli=get_param("_napoli");

		$fldhomepage_url=front_get_db_value("SELECT homepage_login FROM ".FRONT_ESONAME.".gen_utente WHERE login='$puser' AND password='$ppwd' AND idtabella_stato=1");
		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");

		/*
		if(
			($_SERVER["REMOTE_ADDR"]=="94.46.149.33" 
			|| $_SERVER["REMOTE_ADDR"]=="94.46.149.36" 
			|| $_SERVER["REMOTE_ADDR"]=="172.30.0.1" 
			|| $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it" 
			|| $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it" 
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP 
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM_NUOVO 
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM_VOIP 
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM 
			|| $_SERVER["HTTP_HOST"]==LOCAL_IP 
			|| $_SERVER["HTTP_HOST"]==LOCAL_IP_MAMP
			|| $_SERVER["HTTP_HOST"]=="pesaroeducativi.egovpu.it") 
			&& !empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		*/
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente 
			FROM ".FRONT_ESONAME.".gen_utente 
			WHERE login='$puser' 
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente 
			FROM ".FRONT_ESONAME.".gen_utente 
			WHERE login='$puser' 
			AND password='$ppwd' 
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

		if(empty($fldidgen_utente))	// Verifico gli operatori
		{	
			$fldidsso_anagrafica_utente=get_db_value("SELECT idsso_anagrafica_utente 
				FROM sso_anagrafica_operatore 
				INNER JOIN sso_tbl_accesso_operatore ON sso_tbl_accesso_operatore.idsso_tbl_accesso_operatore=sso_anagrafica_operatore.idsso_tbl_accesso_operatore 
				WHERE operatore_user='$puser' 
				AND operatore_pwd='$ppwd' 
				AND flag_login=1");
			if(!empty($fldidsso_anagrafica_utente))
				$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM ".FRONT_ESONAME.".eso_join_anagrafica WHERE idsso_anagrafica_utente='$fldidsso_anagrafica_utente'");
		}

		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente 
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser' 
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}

		if ($fldidgen_utente>0)
		{
			$oggi=date("Y-m-d");
			$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			//aggiornamento password ogni 3 mesi
			if(!empty_data($flddata_password))
			{
				$data_pass3 = date('Y-m-d', strtotime("+3 months", strtotime($flddata_password)));

				if($data_pass3<=$oggi)
				{
					if($accesso_temp)
						$params_accessotemp='&_accessotemp=true';
					elseif(empty($params_accessotemp))
  						$params_accessotemp='&_accessotemp=true&_upd3=true';
					else
  						$params_accessotemp.='&_upd3=true';
				}
			}

			$data=tosql(date("Y-m-d"),"Text");
			$ora=tosql(date("H:i:s"),"Text");
			$ip=$_SERVER['REMOTE_ADDR'];
			$ip = tosql($ip,'Text');
			$chiave=genera_chiave($data,$ora,$ip);

			setCookieUserFront($chiave);

			$obj = new OS_BR();
			$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
		
			$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
			$db_front->query($qrydeleteLog);
			
			$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
			$db_front->query($qry_insertLog);
			
        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

			$fldidgen_profilo=front_get_db_value("select idgen_profilo from ".FRONT_ESONAME.".gen_utente_profilo where idgen_utente='$fldidgen_utente'");

			setCookieProfiloFront($fldidgen_profilo);

			$fldflag_beneficiario=front_get_db_value("select flag_beneficiario from ".FRONT_ESONAME.".gen_utente where idgen_utente='$fldidgen_utente'");
			$fldidsso_tabella_tipologia_ente=front_get_db_value("select idsso_tabella_tipologia_ente from ".FRONT_ESONAME.".gen_utente where idgen_utente='$fldidgen_utente'");
			
			switch($pprocedura)
			{
				default:
				case IDPROCEDURA_SICARE:
					if($fldflag_beneficiario==1)
					{	
						// PER SICUREZZA (genitori che effettuano l'accesso a SiMeal con il vecchio link di login)
						switch($_SERVER['HTTP_HOST'])
						{
							case 'pollenza.simeal.it':
							case 'montesangiusto.simeal.it':
							case 'montemarciano.simeal.it':
							case 'fermignano.simeal.it':
							case 'lucignano.socialiccs.it':
							case 'mense.sicare.it':
							case 'montecassiano.simeal.it':
							case 'tolentino.simeal.it':
							case 'matelica.simeal.it':
							case 'sanseverinomarche.simeal.it':	
							case 'collideltronto.simeal.it': 
		                    case 'pomezia.simeal.it': 
		                    case 'sangennarovesuviano.simeal.it': 
		                    case 'striano.simeal.it': 
							case 'servizi.simeal.it':	
							case 'www.refezionenapoli.it':
							case 'mense.comune.macerata.it': 	// Macerata	
							case 'pomezia.simeal.it':
							case 'carrara.simeal.it':
							case 'oristano.simeal.it':
							case 'castelfrancodisotto.simeal.it':
								$sPage="./esimeal_home.php";
								break;

							case 'senigallia.socialiccs.it':
							case '37.206.216.84': 	// Macerata
							default:
								$sPage="./esicare_beneficiario_home.php";
								break;
						}
					}
					else
					{
						switch($fldidsso_tabella_tipologia_ente)
						{
							case 14:
								$sPage = "./esicare_amministratore_home.php";
								break;

							case 20:
								$sPage = "./esicare_home_cpi.php";
								break;	
							case 24:
								$sPage = "./esicare_datoriale.php";
								break;	

							default:
								$sPage="./esicare_home.php";
								break;										
						}
							
						switch($_SERVER['HTTP_HOST'])
						{
							case 'ambito20.sicare.it': // sso_tabella_tipologia_ente NON ALLINEATA
								$sPage="./esicare_home.php";
								break;
						}

					}

					if(!empty($fldhomepage_url)) 	// esicare_home_coop.php
						$sPage=$fldhomepage_url;
					break;


				case IDPROCEDURA_SIMEAL:
					if($pnapoli)
						$sPage = "./sicare/esimeal_home_napoli.php";
					else
						$sPage = "./esimeal_home.php";
					break;
			}
				

			if($fldidgen_profilo==3)
				$sPage="./esimeal_altri_home.php";
			
			if(get_param("j"))
				$sPage="./ejointly_home.php";


			/*
			}
			else
			{
				$sPage = "./esicare_home.php";
				$fldidgen_profilo=0;
			}
			*/			
			//$chiave=$fldidgen_utente;
			
			$sParams = "?_user=".$chiave;
			echo $sPage.$sParams.$params_accessotemp;			
		}
		break;
		
			
	case "recupera":
		$fldprocedura=get_param("_procedura");
		$pemail=mysql_real_escape_string(get_param("mail"));
		$psms=mysql_real_escape_string(get_param("sms"));
		
		if(!empty($pemail))
		{
			$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE email='$pemail'");
			if ($fldidgen_utente>0)
			{
				$fldcognome=front_get_db_value("SELECT cognome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
				$fldnome=front_get_db_value("SELECT nome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

				$fldlogin=front_get_db_value("SELECT login FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
				
				$password=generaPassword();

				$oggi=date("Y-m-d");
				$adesso=date("H:i:s");

				$fldinvio="<br>";
				$fldoggetto="Recupero Credenziali";
				$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
				$fldtesto.="inviamo di seguito, come da sua richiesta, le credenziali temporanee di accesso per il suo account:$fldinvio $fldinvio";
				$fldtesto.="Username: <b>$fldlogin</b> $fldinvio";
				$fldtesto.="Password: <b>$password</b>";

				$aEMAIL=array();
				$aEMAIL[0]=$pemail;
				$aEMAIL[1]=$fldoggetto;
				$aEMAIL[2]=$fldtesto;
				$aEMAIL[3]="";
				$fldresult=sendMAIL($aEMAIL);

				$password=md5($password);
				
				//annullo le precedenti richieste di recupero pendenti
				$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$fldidgen_utente'";
				$db_front->query($update);

				$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$fldidgen_utente','$oggi','$adesso','".IDPROCEDURA_SIMEAL."','$fldlogin','$password',0)";
				$db_front->query($insert);

				if($_SERVER["HTTP_HOST"]=="buonispesa.sicare.it")
				{
					$update="UPDATE ".FRONT_ESONAME.".gen_utente SET password='$password' WHERE idgen_utente='$fldidgen_utente'";
					$db_front->query($update);
				}

				if($_SERVER["HTTP_HOST"]=="cimiteri.comune.messina.it")
				{
					$update="UPDATE ".FRONT_ESONAME.".gen_utente SET password='$password' WHERE idgen_utente='$fldidgen_utente'";
					$db_front->query($update);
				}

				//Stesse operazioni sopra, ma fatte su socialiccs_mobile
				$response_mobile=siMeal_credenzialiMobile_temp(2,$fldidgen_utente,$fldlogin,$password);
				$response_mobile=siMeal_credenzialiMobile_temp(1,$fldidgen_utente,$fldlogin,$password);

				echo ("true|Le credenziali temporanee sono state inviate all'indirizzo mail fornito.");
			}
			else
			{
				echo ("false|L'indirizzo mail '$pemail' non risulta registrato: verificare la correttezza dei dati inseriti.");
			}
		}
		elseif(!empty($psms))
		{
			$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE cellulare='$psms'");
			if ($fldidgen_utente>0)
			{
				$idsso_anagrafica_utente=get_idsso_anagrafica_utente($fldidgen_utente);
				
				$fldidsso_ente=get_db_value("SELECT idsso_ente FROM sso_anagrafica_utente WHERE idutente='$idsso_anagrafica_utente'");
				$fldcognome=front_get_db_value("SELECT cognome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
				$fldnome=front_get_db_value("SELECT nome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

				$fldlogin=front_get_db_value("SELECT login FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
				
				$password=generaPassword();

				$oggi=date("Y-m-d");
				$adesso=date("H:i:s");

				$fldinvio="\n";
				$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
				$fldtesto.="inviamo di seguito, come da sua richiesta, le credenziali temporanee di accesso per il suo account:$fldinvio $fldinvio";
				$fldtesto.="Username: $fldlogin $fldinvio";
				$fldtesto.="Password: $password";
				
				$fldtesto=db_string($fldtesto);

				$fldtesto=stripslashes($fldtesto);
				
				$psms=trim($psms);
				$psms=str_replace("+39", "", $psms);
				$psms=str_replace(" ", "", $psms);
				$psms=str_replace("-", "", $psms);
				$psms=str_replace("/", "", $psms);

				$recipients='39'.$psms;
				if(smsSTATE($fldidsso_ente))
				{		
					//$fldsmsmittente=get_db_value("SELECT smsmittente FROM simeal_tbl_impostazioni WHERE idsimeal_tbl_impostazioni='1'");

					$fldsmsmittente=get_db_value("SELECT smsmittente FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente'");
					if(empty($fldsmsmittente))
						$fldsmsmittente="ICCS";

					$smsh_sms = new SmsHostingSms ( 'SMSHVD4VJAUAE30I7LCND', 'WQ9B8RI4PO1C3WJXVUXMHBP1TWYH9NHQ' );
					$response = $smsh_sms->smsSend ( $recipients, $fldsmsmittente, NULL, $fldtesto,'H', NULL, NULL, 'false', NULL, NULL );
					//print_r($response);
					if ($response && $response->errorCode == 0) 
					{
						$flag_success_sms=true;

						$fldtesto=db_string($fldtesto);
						$oggi=date("Y-m-d");
						$adesso=date("H:i:s");

						$sSQL_sms="INSERT INTO sso_anagrafica_sms (idsso_anagrafica_utente,descrizione,data_sms,ora_sms,idsso_ente) VALUES('$idsso_anagrafica_utente','$fldtesto','$oggi','$adesso','$fldidsso_ente')";
						$db->query($sSQL_sms);
					}
					else
						$flag_success_sms=false;
				}
				else
					$flag_success_sms=false;

				$password=md5($password);
				
				//annullo le precedenti richieste di recupero pendenti
				$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$fldidgen_utente'";
				$db_front->query($update);

				$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$fldidgen_utente','$oggi','$adesso','".IDPROCEDURA_SIMEAL."','$fldlogin','$password',0)";
				$db_front->query($insert);

				if($_SERVER["HTTP_HOST"]=="buonispesa.sicare.it")
				{
					$update="UPDATE ".FRONT_ESONAME.".gen_utente SET password='$password' WHERE idgen_utente='$fldidgen_utente'";
					$db_front->query($update);
				}

				//Stesse operazioni sopra, ma fatte su socialiccs_mobile
				$response_mobile=siMeal_credenzialiMobile_temp(2,$fldidgen_utente,$fldlogin,$password);
				$response_mobile=siMeal_credenzialiMobile_temp(1,$fldidgen_utente,$fldlogin,$password);

				if($flag_success_sms)
					echo ("true|Le credenziali temporanee sono state inviate via sms al numero di cellulare fornito.");
				else
					echo ("false|Errore durante l'invio delle credenziali via sms.");
			}
			else
			{
				echo ("false|Il numero di cellulare '$psms' non risulta registrato: verificare la correttezza dei dati inseriti.");
			}
		}

		break;

	case "recupera_napoli":
		$pemail=get_param("mail");
		$pcodice_fiscale=get_param("cf");
		
		$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE email='$pemail' AND codicefiscale='$pcodice_fiscale'");
		if ($fldidgen_utente>0)
		{
			$fldcognome=front_get_db_value("SELECT cognome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
			$fldnome=front_get_db_value("SELECT nome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

			$fldlogin=front_get_db_value("SELECT login FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
			$fldpassword=front_get_db_value("SELECT password FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
			
			$password=generaPassword();

			$fldinvio="<br>";
			$fldoggetto="Richiesta credenziali";
			$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
			$fldtesto.="inviamo di seguito, come da sua richiesta, le credenziali di accesso per il suo account:.$fldinvio $fldinvio";
			$fldtesto.="Username: <b>$fldlogin</b> $fldinvio";
			$fldtesto.="Password: <b>$password</b> $fldinvio $fldinvio";
			$fldtesto.="Privacy $fldinvio";
			$fldtesto.="Le informazioni contenute in questo messaggio sono riservate e confidenziali ed e' vietata la diffusione in qualunque modo eseguita. Qualora Lei non fosse la persona a cui il presente messaggio e' destinato, La invito ad eliminarlo e a non leggerlo, dandocene gentilmente comunicazione(GDPR 679/2016). Grazie per la collaborazione. ".$fldinvio.$fldinvio."
						This e-mail (including attachments) is intended only for the recipient(s) named above. It may contain confidential or privileged information and should not be read, copied or otherwise used by any other person (GDPR 679/2016 ). $fldinvio";
			$aEMAIL=array();
			$aEMAIL[0]=$pemail;
			$aEMAIL[1]=$fldoggetto;
			$aEMAIL[2]=$fldtesto;
			$aEMAIL[3]="";
			$fldresult=sendMAIL($aEMAIL);

			$password=md5($password);

			//annullo le precedenti richieste di recupero pendenti
			$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$fldidgen_utente'";
			$db_front->query($update);

			$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$fldidgen_utente','$oggi','$adesso','$fldprocedura','$fldlogin','$password',0)";
			$db_front->query($insert);

			if($fldresult=="Messaggio inviato correttamente.")
				echo ("true|Le credenziali sono state inviate all'indirizzo mail fornito.");
			else
				echo ("false|Impossibile inviare l'email.");
		}
		else
		{
			echo ("false|Non esiste un utente con l'indirizzo mail '$pemail' e il codice fiscale '$pcodice_fiscale': verificare la correttezza dei dati inseriti.");
		}
		break;  
	
	case "richiedi_indirizzo_napoli":
		$pcodice_fiscale=get_param("cf");
		
		$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE codicefiscale='$pcodice_fiscale'");
		if ($fldidgen_utente>0)
		{
			$fldemail=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

			if(!empty($fldemail))
				echo ("true|".$fldemail);
		}
		else
		{
			echo ("false|Non esiste un utente registrato con il codice fiscale '$pcodice_fiscale': verificare la correttezza dei dati inseriti.");
		}
		break;

	case "richiesta_modifica_mail_napoli":
		$pcodice_fiscale=get_param("cf");
		$pmail2=get_param("m2");
		$pmail3=get_param("m3");
		$flddata_richiesta=date("Y-m-d");
		$fldorario_richiesta=date("H:i:s");

		$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE codicefiscale='$pcodice_fiscale'");
		if ($fldidgen_utente>0)
		{
			if($pmail2==$pmail3)
			{
				$fldgen_utente_mail_modifica=front_get_db_value("SELECT idgen_utente_mail_modifica FROM gen_utente_mail_modifica WHERE idgen_utente='$fldidgen_utente' AND idoperatore IS NULL");
				if($fldgen_utente_mail_modifica)
				{
					$update="UPDATE gen_utente_mail_modifica SET idgen_utente='$fldidgen_utente', mail_nuova='$pmail2', data='$flddata_richiesta', ora='$fldorario_richiesta' WHERE idgen_utente_mail_modifica='$fldgen_utente_mail_modifica'";
					$db_front->query($update);
				}
				else
				{
					$insert="INSERT INTO gen_utente_mail_modifica(idgen_utente,mail_nuova,data,ora) VALUES('$fldidgen_utente','$pmail2','$flddata_richiesta','$fldorario_richiesta')";
					$db_front->query($insert);
				}
				
				echo ("true|È stata generata una richiesta di modifica dell'indirizzo email, quando verrà accetta riceverà le credenziali.");
			}
			else
				echo ("false|Gli indirizzi email inseriti non coincidono.");
		}
		else
		{
			echo ("false|Non esiste un utente registrato con il codice fiscale '$pcodice_fiscale': verificare la correttezza dei dati inseriti.");
		}
		break;
		
	case "comunicazionilette":
		$fldidgen_utente=get_param("_id");

		//Tutte le comunicazioni del fornitore sono considerate come lette
		$sSQL="UPDATE eso_accreditamento_comunicazione SET flag_letto='1' WHERE idgen_destinatario='$fldidgen_utente' AND idgen_mittente='0' AND flag_fornitore='1'";
		$db_front->query($sSQL);
		break;  		
		
		
	case "ordineservizio":
		$fldidsso_anagrafica_utente=get_param("_id");
		echo $notifica_beneficiari=get_notifica_pai_front($fldidsso_anagrafica_utente);
		break;  	

  
	case "loadprestazioni":
		$pidutente=get_param("_idutente");

		$db->query("select sso_tbl_prestazione.* from ".DBNAME_SS.".sso_tbl_prestazione 
		INNER JOIN ".DBNAME_SS.".sso_ente_join_servizio ON sso_ente_join_servizio.idsso_tbl_prestazione_ente=sso_tbl_prestazione.idsso_tbl_prestazione 
		WHERE sso_ente_join_servizio.idsso_anagrafica_utente='$pidutente'
		order by descrizione");
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldidsso_tbl_prestazione=$db->f("idsso_tbl_prestazione");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione = stripslashes($flddescrizione);
			
			//Rimuove tutti i caratteri speciali
			$flddescrizione = str_replace(' ', '-', $flddescrizione);
			$flddescrizione = preg_replace('/[^A-Za-z0-9\-]/', '', $flddescrizione);
			$flddescrizione = str_replace('-', ' ', $flddescrizione);

			$reply.='{"value":"'.$flddescrizione.'","data":"'.$fldidsso_tbl_prestazione.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;  	

  
	case "loadprestazionigruppi":
		$pidutente=get_param("_idutente");

		$sql="SELECT DISTINCT(sso_tbl_prestazione.idsso_tbl_prestazione), sso_tbl_prestazione.descrizione 
		FROM ".DBNAME_SS.".sso_tbl_prestazione 
		INNER JOIN ".DBNAME_SS.".sso_gruppo ON sso_gruppo.idsso_tbl_prestazione=sso_tbl_prestazione.idsso_tbl_prestazione ";

		if(!empty($pidutente))
			$sql.=" WHERE sso_gruppo.idsso_fornitore='$pidutente' ";
		
		$sql.="ORDER BY sso_tbl_prestazione.descrizione";

		$db->query($sql);
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldidsso_tbl_prestazione=$db->f("idsso_tbl_prestazione");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione = stripslashes($flddescrizione);
			
			//Rimuove tutti i caratteri speciali
			$flddescrizione = str_replace(' ', '-', $flddescrizione);
			$flddescrizione = preg_replace('/[^A-Za-z0-9\-]/', '', $flddescrizione);
			$flddescrizione = str_replace('-', ' ', $flddescrizione);

			$reply.='{"value":"'.$flddescrizione.'","data":"'.$fldidsso_tbl_prestazione.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;
		
		
	case "loadfornitori":

		$db->query("SELECT * FROM sso_anagrafica_utente inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente order by cognome");
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldcognome=$db->f("cognome");
			$fldidutente=$db->f("idutente");
						
			$fldcognome = stripslashes($fldcognome);
			/*
			//Rimuove tutti i caratteri speciali
			$fldcognome = str_replace(' ', '-', $fldcognome);
			$fldcognome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldcognome);
			$fldcognome = str_replace('-', ' ', $fldcognome);
			*/
			$reply.='{"value":"'.$fldcognome.'","data":"'.$fldidutente.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;		
		
		
	case "loadoperatori":
		$pidutente=get_param("_idutente");

		$db->query("SELECT * FROM sso_anagrafica_operatore WHERE idsso_anagrafica_utente='$pidutente' order by nominativo");
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldnominativo=$db->f("nominativo");
			$fldidsso_anagrafica_operatore=$db->f("idsso_anagrafica_operatore");
						
			$fldnominativo = stripslashes($fldnominativo);
			/*
			//Rimuove tutti i caratteri speciali
			$fldnominativo = str_replace(' ', '-', $fldnominativo);
			$fldnominativo = preg_replace('/[^A-Za-z0-9\-]/', '', $fldnominativo);
			$fldnominativo = str_replace('-', ' ', $fldnominativo);
			*/
			$reply.='{"value":"'.$fldnominativo.'","data":"'.$fldidsso_anagrafica_operatore.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;
		
		
	case "deleteregistrazione":
		$pidgen_utente=get_param("_utente");

		$elimina=false;

		$fldidutente=front_get_db_value("SELECT idsso_anagrafica_utente FROM ".FRONT_ESONAME.".eso_join_anagrafica WHERE idgen_utente='$pidgen_utente'");

		$ndomande=get_db_value("SELECT COUNT(*) FROM sso_domanda INNER JOIN sso_domanda_mensa ON sso_domanda.idsso_domanda=sso_domanda_mensa.idsso_domanda WHERE (sso_domanda.idutente='$fldidutente' OR sso_domanda.idpresentante='$fldidutente') AND (sso_domanda.flag_elimina IS NULL OR sso_domanda.flag_elimina=0)");
		
		$ndebiti=get_db_value("SELECT COUNT(*) FROM tco_preavviso INNER JOIN tco_preavviso_rata ON tco_preavviso.idtco_preavviso=tco_preavviso_rata.idtco_preavviso WHERE tco_preavviso.idtco_contribuente='$fldidutente' AND (tco_preavviso.flag_annullato IS NULL OR tco_preavviso.flag_annullato=0) AND (tco_preavviso.flag_nota_credito=0 OR flag_nota_credito IS NULL)");

		if(empty($ndomande) && empty($ndebiti))
			$elimina=true;
		
		if($elimina)
		{
			$sSQL="DELETE FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$pidgen_utente'";
			$db_front->query($sSQL);
			$sSQL="DELETE FROM ".FRONT_ESONAME.".gen_utente_profilo WHERE idgen_utente='$pidgen_utente'";
			$db_front->query($sSQL);
			$sSQL="DELETE FROM ".FRONT_ESONAME.".eso_join_anagrafica WHERE idgen_utente='$pidgen_utente'";
			$db_front->query($sSQL);

			siMeal_credenzialiMobile(3,$pidgen_utente);

			echo "1";
		}
		else
			echo "0";
		break;	
		
		
	case "changemail":
		$pidgen_utente=get_param("_utente");
		$pvalue=get_param("_value");
		
		$sSQL="update ".FRONT_ESONAME.".gen_utente set email='$pvalue' where idgen_utente='$pidgen_utente'";
		$db_front->query($sSQL);

		$fldidsso_anagrafica_utente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$pidgen_utente'");
		
		$sSQL="update ".DBNAME_SS.".sso_anagrafica_utente set email='$pvalue' where idutente='$fldidsso_anagrafica_utente'";
		$db->query($sSQL);
		break;	
  	case "changeuser":
		$pidgen_utente=get_param("_utente");
		$pvalue=get_param("_value");
		
		$sSQL="update ".FRONT_ESONAME.".eso_join_anagrafica set idsso_anagrafica_utente='$pvalue' where idgen_utente='$pidgen_utente'";
		$db_front->query($sSQL);

		break;	
  
	case "simeal_invia_credenziali":
		$pidgen_utente=get_param("_id");

		$fldemail=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$pidgen_utente'");
		//$fldidsso_anagrafica_utente=get_idsso_anagrafica_utente($pidgen_utente);

		if(!empty($fldemail))
		{
			$fldcognome=front_get_db_value("select cognome from gen_utente where idgen_utente='$pidgen_utente'");
			$fldnome=front_get_db_value("select nome from gen_utente where idgen_utente='$pidgen_utente'");
			//$fldpassword=front_get_db_value("select password from gen_utente where idgen_utente='$pidgen_utente'");
			$fldlogin=front_get_db_value("select login from gen_utente where idgen_utente='$pidgen_utente'");
			
			$fldpassword=generaPassword();

			$fldinvio="<br>";
			$fldoggetto="Richiesta credenziali";
			$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
			$fldtesto.="inviamo di seguito, come da sua richiesta, le credenziali temporanee di accesso per il suo account: $fldinvio $fldinvio";
			$fldtesto.="Username: <b>$fldlogin</b> $fldinvio";
			$fldtesto.="Password: <b>$fldpassword</b> $fldinvio $fldinvio";
			$fldtesto.="Privacy $fldinvio";
			$fldtesto.="Le informazioni contenute in questo messaggio sono riservate e confidenziali ed e' vietata la diffusione in qualunque modo eseguita. Qualora Lei non fosse la persona a cui il presente messaggio e' destinato, La invito ad eliminarlo e a non leggerlo, dandocene gentilmente comunicazione(GDPR 679/2016). Grazie per la collaborazione. ".$fldinvio.$fldinvio."
						This e-mail (including attachments) is intended only for the recipient(s) named above. It may contain confidential or privileged information and should not be read, copied or otherwise used by any other person (GDPR 679/2016 ). $fldinvio";

			if($_SERVER["HTTP_HOST"]=="37.206.216.84" 
				|| $_SERVER["HTTP_HOST"]=="172.30.0.87" 
				|| $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it")
			{
				$request_rest = curl_init();
				curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_mail_mc_simeal_back.php');

				$params=array();
				$params['nominativo']=$fldcognome." ".$fldnome;
				$params['user']=$fldlogin;
				$params['password']=$fldpassword;
				$params['email']=$fldemail;

				curl_setopt($request_rest, CURLOPT_SSL_VERIFYPEER, false);												//non verifico il certificato del peer
				curl_setopt($request_rest, CURLOPT_SSL_VERIFYHOST, true);												//non controllo se il peer ha il certificato
				curl_setopt($request_rest, CURLOPT_VERBOSE, true);														//mostra informazioni "verbose"
				curl_setopt($request_rest, CURLINFO_HEADER_OUT,true);													//tracciare la richiesta
				curl_setopt($request_rest, CURLOPT_CUSTOMREQUEST, "POST");												//decido di inviare i dati in POST
				curl_setopt($request_rest, CURLOPT_POST, true);															//per fare una regolare richiesta HTTP POST
				curl_setopt($request_rest, CURLOPT_POSTFIELDS,$params);
				curl_setopt($request_rest, CURLOPT_RETURNTRANSFER, true);												//per non stampare direttamente il risultato a video
				//, 'Content-Length: '.strlen($json_request)
				//curl_setopt($request_rest, CURLOPT_FOLLOWLOCATION, true);  
				curl_setopt($request_rest, CURLOPT_FRESH_CONNECT, true); 												//stabilisco una nuova connessione e non una in cache

				// output the response
				$fldresult=curl_exec($request_rest);		//invio la richiesta e ricevo la risposta
				//print_r($result);
				// close the session
				curl_close($request_rest);		//chiudo la sessione
			}
			else
			{
				$aEMAIL=array();
				$aEMAIL[0]=$fldemail;
				$aEMAIL[1]=$fldoggetto;
				$aEMAIL[2]=$fldtesto;
				$aEMAIL[3]="";
				$fldresult=sendMAIL($aEMAIL);				
			}
			
			echo $fldresult;

			if($fldresult=="Messaggio inviato correttamente.")
			{
				$update="UPDATE ".FRONT_ESONAME.".gen_utente SET flag_abilitato=1 WHERE idgen_utente='$pidgen_utente'";
				$db_front->query($update);

				$oggi=date("Y-m-d");
				$adesso=date("H:i:s");

				$fldpassword=md5($fldpassword);

				//annullo le precedenti richieste di recupero pendenti
				$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$pidgen_utente'";
				$db_front->query($update);

				$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$pidgen_utente','$oggi','$adesso','".IDPROCEDURA_SIMEAL."','$fldlogin','$fldpassword',0)";
				$db_front->query($insert);

				//Stesse operazioni sopra, ma fatte su socialiccs_mobile
				$response_mobile=siMeal_credenzialiMobile_temp(2,$pidgen_utente,"","");
				$response_mobile=siMeal_credenzialiMobile_temp(1,$pidgen_utente,$fldlogin,$fldpassword);
			}
			
			//echo ("La richieste delle credenziali e' avvenuta con successo: a breve verra' inviata un email sulla casella di posta elettronica e sulla casella di posta elettronica certificata da Voi indicate contenente i dati per accedere ai servizi.");
		}
		else
			echo ("Attenzione: l'utente selezionato non ha alcun indirizzo email salvato nella sua anagrafica.");
		break;

	case "simeal_invia_credenziali_altro":
		$pidgen_utente=get_param("_id");
		$fldemail=get_param("_email");

		//$fldemail=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$pidgen_utente'");
		//$fldidsso_anagrafica_utente=get_idsso_anagrafica_utente($pidgen_utente);

		if(!empty($fldemail))
		{
			$fldcognome=front_get_db_value("select cognome from gen_utente where idgen_utente='$pidgen_utente'");
			$fldnome=front_get_db_value("select nome from gen_utente where idgen_utente='$pidgen_utente'");
			//$fldpassword=front_get_db_value("select password from gen_utente where idgen_utente='$pidgen_utente'");
			$fldlogin=front_get_db_value("select login from gen_utente where idgen_utente='$pidgen_utente'");
			
			$fldpassword=generaPassword();

			$fldinvio="<br>";
			$fldoggetto="Richiesta credenziali";
			$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
			$fldtesto.="inviamo di seguito, come da sua richiesta, le credenziali di accesso per il suo account: $fldinvio $fldinvio";
			$fldtesto.="Username: <b>$fldlogin</b> $fldinvio";
			$fldtesto.="Password: <b>$fldpassword</b> $fldinvio $fldinvio";
			$fldtesto.="Privacy $fldinvio";
			$fldtesto.="Le informazioni contenute in questo messaggio sono riservate e confidenziali ed e' vietata la diffusione in qualunque modo eseguita. Qualora Lei non fosse la persona a cui il presente messaggio e' destinato, La invito ad eliminarlo e a non leggerlo, dandocene gentilmente comunicazione(GDPR 679/2016). Grazie per la collaborazione. ".$fldinvio.$fldinvio."
						This e-mail (including attachments) is intended only for the recipient(s) named above. It may contain confidential or privileged information and should not be read, copied or otherwise used by any other person (GDPR 679/2016 ). $fldinvio";

			$aEMAIL=array();
			$aEMAIL[0]=$fldemail;
			$aEMAIL[1]=$fldoggetto;
			$aEMAIL[2]=$fldtesto;
			$aEMAIL[3]="";
			$fldresult=sendMAIL($aEMAIL);				
			
			echo $fldresult;

			if($fldresult=="Messaggio inviato correttamente.")
			{
				$update="UPDATE ".FRONT_ESONAME.".gen_utente SET flag_abilitato=1 WHERE idgen_utente='$pidgen_utente'";
				$db_front->query($update);

				$oggi=date("Y-m-d");
				$adesso=date("H:i:s");

				$fldpassword=md5($fldpassword);

				//annullo le precedenti richieste di recupero pendenti
				$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$pidgen_utente'";
				$db_front->query($update);

				$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$pidgen_utente','$oggi','$adesso','".IDPROCEDURA_SIMEAL."','$fldlogin','$fldpassword',0)";
				$db_front->query($insert);

				//Stesse operazioni sopra, ma fatte su socialiccs_mobile
				$response_mobile=siMeal_credenzialiMobile_temp(2,$pidgen_utente,"","");
				$response_mobile=siMeal_credenzialiMobile_temp(1,$pidgen_utente,$fldlogin,$fldpassword);
			}

			//echo ("La richieste delle credenziali e' avvenuta con successo: a breve verra' inviata un email sulla casella di posta elettronica e sulla casella di posta elettronica certificata da Voi indicate contenente i dati per accedere ai servizi.");
		}
		else
			echo ("Attenzione: l'utente selezionato non ha alcun indirizzo email salvato nella sua anagrafica.");
		break;

	case "abilita_utente":
		$pidgen_utente=get_param("_id");
		$pflag_abilita=get_param("_abilitato");

		$update="UPDATE ".FRONT_ESONAME.".gen_utente SET flag_abilitato='$pflag_abilita', idtabella_stato='$pflag_abilita' WHERE idgen_utente='$pidgen_utente'";
		$db_front->query($update);
		
		echo $pflag_abilita;
	break;
		
	case "recupera_jointly":

		$pemail=get_param("_email");

		$fldemail_pec=front_get_db_value("SELECT email_pec FROM gen_utente WHERE email='$pemail'");

		$fldusername=front_get_db_value("SELECT login FROM gen_utente WHERE email='$pemail'");

		if(!empty($fldusername))
		{
			$password=generaPassword();
				   
			$sSQL="UPDATE gen_utente SET password='$password',idtabella_stato=1 WHERE email='$pemail' AND login='$fldusername'";
			$db_front->query($sSQL);

			 $fldinvio="<br>";
			 $fldoggetto="Rilascio credenziali";
			 $fldtesto="Gentile $fldnominativo_rl, $fldinvio";
			 $fldtesto.="inviamo di seguito, come da sua richiesta all'indirizzo e-mail da lei segnalato, le nuove credenziali per l'accesso.$fldinvio $fldinvio";
			 $fldtesto.="Riepilogo dei dati $fldinvio";
			 $fldtesto.="Username: <b>$fldusername</b> $fldinvio";
			 $fldtesto.="Password: <b>$password</b> $fldinvio $fldinvio";
			 $fldtesto.="Link per accedere ai servizi forniti dalla piattaforma: http://jointly.sicare.it/ $fldinvio$fldinvio";
			 $fldtesto.="<i>Jointly Support Team</i>$fldinvio$fldinvio";
			 $fldtesto.="Privacy $fldinvio";
			 $fldtesto.="Le informazioni contenute in questo messaggio sono riservate e confidenziali ed e' vietata la diffusione in qualunque modo eseguita. Qualora Lei non fosse la persona a cui il presente messaggio e' destinato, La invito ad eliminarlo e a non leggerlo, dandocene gentilmente comunicazione(GDPR 679/2016). Grazie per la collaborazione. ".$fldinvio.$fldinvio."
						This e-mail (including attachments) is intended only for the recipient(s) named above. It may contain confidential or privileged information and should not be read, copied or otherwise used by any other person (GDPR 679/2016 ). $fldinvio";			 
			 if ($fldemail_pec)
			 {
				 $aEMAIL=array();
				 $aEMAIL[0]=$fldemail_pec;
				 $aEMAIL[1]=$fldoggetto;
				 $aEMAIL[2]=$fldtesto;
				 $aEMAIL[3]="";
				 $fldresult=sendMAIL($aEMAIL);
			 }
			 
			 if ($fldemail)
			 {
				 $aEMAIL=array();
				 $aEMAIL[0]=$fldemail;
				 $aEMAIL[1]=$fldoggetto;
				 $aEMAIL[2]=$fldtesto;
				 $aEMAIL[3]="";
				 $fldresult=sendMAIL($aEMAIL);
			 }

			 if($fldresult)
				$alert_inviata=true;
		}

		if($alert_inviata)
			echo "1";
		else
			echo "0";
		break;


	case "assegnaintervento":
		$pidsso_domanda_intervento=get_param("_i");
		$pidsso_fornitore=get_param("_f");
		$sSQL="update sso_domanda_intervento set idsso_ente_servizio='$pidsso_fornitore' where idsso_domanda_intervento='$pidsso_domanda_intervento'";
		$db->query($sSQL);
		break;	

		
	case "loadbeneficiari_nominativo":
	
		$pidutente=get_param("_idutente");
		$fldidutente_operatore=front_get_db_value("SELECT idgen_utente FROM eso_join_anagrafica WHERE idsso_anagrafica_utente='$pidutente'");

		$db->query("select idutente, cognome, nome, codicefiscale, data_nascita
					from (
						select sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						from sso_anagrafica_utente 
						INNER JOIN sso_prestazione_fatta_dettaglio on sso_prestazione_fatta_dettaglio.idutente=sso_anagrafica_utente.idutente 
						where sso_prestazione_fatta_dettaglio.idsso_ente_assistenza='$pidutente'
						
						UNION ALL
						
						select sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						from sso_anagrafica_utente 
						INNER JOIN sso_domanda_intervento on sso_domanda_intervento.idutente=sso_anagrafica_utente.idutente 
						where sso_domanda_intervento.idsso_ente_servizio='$pidutente'

						UNION ALL

						select sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						from sso_anagrafica_utente
						INNER JOIN sso_anagrafica_altro ON sso_anagrafica_utente.idutente=sso_anagrafica_altro.idsso_anagrafica_utente
						where sso_anagrafica_altro.idgen_operatore_fornitore='$fldidutente_operatore'
					) t
					GROUP BY t.idutente
					");
					
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			$flddatanascita=invertidata($flddatanascita,"/","-",2);
			$fldcodicefiscale = $db->f("codicefiscale");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			if(!empty($flddatanascita))
				$flddatanascita='    -    '.$flddatanascita;
			
			if(!empty($fldcodicefiscale))
				$fldcodicefiscale='    -    '.$fldcodicefiscale;
				
			$fldcodicefiscale_data=removeslashes($fldcodicefiscale_data);
			$fldcodicefiscale=removeslashes($fldcodicefiscale);
			$fldcognome=removeslashes($fldcognome);
			$fldnome=removeslashes($fldnome);
			
			$popt=get_param("_opt");	//_opt imposta cosa visualizzare nell'imput (Nominativo/Data di nascita/Codice fiscale
			switch($popt){
				case 1:
					$value=$fldcognome.' '.$fldnome;
					break;
				case 2:
					$value=$fldcognome.' '.$fldnome.$flddatanascita;
					break;
				case 3:
					$value=$fldcognome.' '.$fldnome.$flddatanascita.$fldcodicefiscale;
					break;
				case 4:
					$value=$fldcognome.' '.$fldnome.$fldcodicefiscale;
					break;
				default:
					$value=$fldcognome.' '.$fldnome.$flddatanascita;
					break;
			}
						
			$reply.='{"value":"'.$value.'","data":"'.$fldidutente.'|'.$fldcodicefiscale_data.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;	


	case "loadbeneficiari_nominativo_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$pidutente=get_param("_idutente");
		$fldidutente_operatore=front_get_db_value("SELECT idgen_utente FROM eso_join_anagrafica WHERE idsso_anagrafica_utente='$pidutente'");

		$db->query("SELECT idutente, cognome, nome, codicefiscale, data_nascita
					FROM (
						SELECT sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						FROM ".DBNAME_SS.".sso_anagrafica_utente 
						INNER JOIN ".DBNAME_SS.".sso_prestazione_fatta_dettaglio on sso_prestazione_fatta_dettaglio.idutente=sso_anagrafica_utente.idutente 
						WHERE sso_prestazione_fatta_dettaglio.idsso_ente_assistenza='$pidutente'
						AND (LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')
						
						UNION ALL
						
						SELECT sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						FROM ".DBNAME_SS.".sso_anagrafica_utente 
						INNER JOIN ".DBNAME_SS.".sso_domanda_intervento on sso_domanda_intervento.idutente=sso_anagrafica_utente.idutente 
						WHERE sso_domanda_intervento.idsso_ente_servizio='$pidutente'
						AND (LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')

						UNION ALL

						SELECT sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						FROM ".DBNAME_SS.".sso_anagrafica_utente
						INNER JOIN ".DBNAME_SS.".sso_anagrafica_altro ON sso_anagrafica_utente.idutente=sso_anagrafica_altro.idsso_anagrafica_utente
						WHERE sso_anagrafica_altro.idgen_operatore_fornitore='$fldidutente_operatore'
						AND (LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')

						UNION ALL

						SELECT sso_anagrafica_utente.idutente, cognome, nome, codicefiscale, data_nascita 
						FROM ".DBNAME_SS.".sso_anagrafica_utente
						INNER JOIN ".DBNAME_SS.".sso_domanda ON sso_anagrafica_utente.idutente=sso_domanda.idutente
						INNER join ".DBNAME_SS.".sso_anagrafica_rei on sso_domanda.idutente=sso_anagrafica_rei.idsso_anagrafica_utente
						WHERE sso_domanda.idsso_tabella_stato_domanda='4' 
						AND (sso_anagrafica_rei.idsso_tbl_rei_valore=107 and rei_valore='$pidutente') 
						AND (LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%') 
					) t
					GROUP BY t.idutente
					");

		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			$flddatanascita=invertidata($flddatanascita,"/","-",2);
			$fldcodicefiscale = $db->f("codicefiscale");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			if(!empty($flddatanascita))
				$flddatanascita='    -    '.$flddatanascita;
			
			if(!empty($fldcodicefiscale))
				$fldcodicefiscale='    -    '.$fldcodicefiscale;
				
			$nominativo=$fldcognome.' '.$fldnome;

			$fldcodicefiscale_data=removeslashes($fldcodicefiscale_data);
			$fldcodicefiscale=removeslashes($fldcodicefiscale);
			$nominativo=removeslashes($nominativo);
			
			$nominativo=utf8_decode($nominativo);

			$popt=get_param("_opt");	//_opt imposta cosa visualizzare nell'imput (Nominativo/Data di nascita/Codice fiscale
			switch($popt){
				case 1:
					$value=$nominativo;
					break;
				case 2:
					$value=$nominativo.$flddatanascita;
					break;
				case 3:
					$value=$nominativo.$flddatanascita.$fldcodicefiscale;
					break;
				case 4:
					$value=$nominativo.$fldcodicefiscale;
					break;
				default:
					$value=$nominativo.$flddatanascita;
					break;
			}
						
			$record=array();
			$record['value']=$value;
			$record['data']=$fldidutente.'|'.$fldcodicefiscale_data;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}

		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;


	case "send_assistenza_napoli":
		$fldemail = get_param("indirizzo");
		$fldcf = get_param("cf");
		$fldnominativo = get_param("nominativo");
		$fldtelefono = get_param("telefono");
		$fldtesto = get_param("testo");
		$fldtesto = nl2br($fldtesto);

		$ptesto="<b>Nominativo</b>: ".$fldnominativo."<br><b>Codice Fiscale</b>: ".$fldcf."<br><b>Recapito telefonico</b>: ".$fldtelefono."<br><b>Indirizzo email</b>: ".$fldemail."<br><b>Testo del messaggio</b>: ".$fldtesto;

		$aEMAIL=array();
		$aEMAIL[0]="refezione.scolastica@comune.napoli.it";
		$aEMAIL[1]="Richiesta assistenza - ".$fldnominativo;
		$aEMAIL[2]=$ptesto;
		$aEMAIL[3]="";
		$fldresult=sendMAIL($aEMAIL);

		if($fldresult=="Messaggio inviato correttamente.")
		{
			echo ("true|La richiesta di assistenza e' stata inviata correttamente.");
		}
		else
		{
			echo ("false|La richiesta di assistenza non può essere inviata.");
		}

	break;

	case "send_assistenza_napoli_educativi":
		$fldemail = get_param("indirizzo");
		$fldcf = get_param("cf");
		$fldnominativo = get_param("nominativo");
		$fldtelefono = get_param("telefono");
		$fldtesto = get_param("testo");
		$fldtesto = nl2br($fldtesto);

		$ptesto="<b>Nominativo</b>: ".$fldnominativo."<br><b>Codice Fiscale</b>: ".$fldcf."<br><b>Recapito telefonico</b>: ".$fldtelefono."<br><b>Indirizzo email</b>: ".$fldemail."<br><b>Testo del messaggio</b>: ".$fldtesto;

		$aEMAIL=array();
		$aEMAIL[0]="servizieducativinapoli@iccs.it";
		$aEMAIL[1]="Richiesta assistenza - ".$fldnominativo;
		$aEMAIL[2]=$ptesto;
		$aEMAIL[3]="";
		$fldresult=sendMAIL($aEMAIL);

		if($fldresult=="Messaggio inviato correttamente.")
		{
			echo ("true|La richiesta di assistenza e' stata inviata correttamente.");
		}
		else
		{
			echo ("false|La richiesta di assistenza non può essere inviata.");
		}

	break;

	case "conferma_modifica_email":
		$pididgen_utente_mail_modifica=get_param("_idgen_utente_mail_modifica");
		$flddata_accettazione=date("Y-m-d");
		$fldorario_accettazione=date("H:i:s");
		$pidoperatore=get_param("_idoperatore");

		$fldidgen_utente=front_get_db_value("SELECT idgen_utente FROM gen_utente_mail_modifica WHERE idgen_utente_mail_modifica='$pididgen_utente_mail_modifica'");

		$fldidutente=front_get_db_value("SELECT idsso_anagrafica_utente FROM eso_join_anagrafica WHERE idgen_utente='$fldidgen_utente'");

		$fldemail_vecchia=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
		$fldcognome=front_get_db_value("SELECT cognome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
		$fldnome=front_get_db_value("SELECT nome FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
		$fldusername=front_get_db_value("SELECT login FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");
		$fldpassword=front_get_db_value("SELECT password FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

		$update="UPDATE gen_utente_mail_modifica SET mail_vecchia='$fldemail_vecchia', data_accettazione='$flddata_accettazione', ora_accettazione='$fldorario_accettazione', idoperatore='$pidoperatore' WHERE idgen_utente_mail_modifica='$pididgen_utente_mail_modifica'";
		$db_front->query($update);

		$fldemail_nuova=front_get_db_value("SELECT mail_nuova FROM gen_utente_mail_modifica WHERE idgen_utente_mail_modifica='$pididgen_utente_mail_modifica'");

		$update="UPDATE gen_utente SET email='$fldemail_nuova' WHERE idgen_utente='$fldidgen_utente'";
		$db_front->query($update);

		$update="UPDATE sso_anagrafica_utente SET email='$fldemail_nuova' WHERE idutente='$fldidutente'";
		$db->query($update);

		$fldemail_nuova=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$fldidgen_utente'");

		$fldinvio="<br>";
		$fldoggetto="Rilascio credenziali";
		$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
		$fldtesto.="la registrazione e' avvenuta con successo. Riportiamo di seguito le credenziali per l'accesso alla piattaforma. $fldinvio $fldinvio";
		$fldtesto.="Username: <b>$fldusername</b> $fldinvio";
		$fldtesto.="Password: <b>$fldpassword</b> $fldinvio $fldinvio";
		$fldtesto.="Privacy $fldinvio";
		$fldtesto.="Le informazioni contenute in questo messaggio sono riservate e confidenziali ed e' vietata la diffusione in qualunque modo eseguita. Qualora Lei non fosse la persona a cui il presente messaggio e' destinato, La invito ad eliminarlo e a non leggerlo, dandocene gentilmente comunicazione(GDPR 679/2016). Grazie per la collaborazione. ".$fldinvio.$fldinvio."
						This e-mail (including attachments) is intended only for the recipient(s) named above. It may contain confidential or privileged information and should not be read, copied or otherwise used by any other person (GDPR 679/2016 ). $fldinvio";
		$aEMAIL=array();
		$aEMAIL[0]=$fldemail_nuova;
		$aEMAIL[1]=$fldoggetto;
		$aEMAIL[2]=$fldtesto;
		$aEMAIL[3]="";
		$mail_result=sendMAIL($aEMAIL);

		if($mail_result=="Messaggio inviato correttamente.")
			echo "1";
		else
			echo "0";

		break;
	case "editprenota";
		$pidsso_prenotazione=get_param("_p");
		$pquantita=get_param("_value");
		$prenotazione=new Prenotazione($pidsso_prenotazione);
		$tariffa=$prenotazione->prenotazione_prezzo;
		$indirizzo_distanza=$prenotazione->indirizzo_distanza;
		if ($indirizzo_distanza>10)
		{
			$prenotazione_maggiorazione=round(($pquantita*$tariffa)*10/100,2);
		}		
		$sSQL="update sso_prenotazione set quantita='$pquantita',prenotazione_maggiorazione='$prenotazione_maggiorazione' where idsso_prenotazione='$pidsso_prenotazione'";
		$db->query($sSQL);
		
		break;
	case "insertprenota";
		list($pkey,$pfield)=explode("|",get_param("_k"));
		$pidsso_anagrafica_utente=get_param("_u");
		$pidsso_ordine=get_param("_o");
		$sWhere=" where idsso_anagrafica_utente='$pidsso_anagrafica_utente' and idsso_ente_join_servizio=0 ";
		if ($pidsso_ordine)
			$sWhere.="and idsso_ordine='".$pidsso_ordine."'";
		else
			$sWhere.="and idsso_ordine='0'";

		$pvalue=get_param("_value");
		$pfield=db_string($pfield);

		//Verifico se esiste nel carrello 
		$idsso_prenotazione=get_db_value("select idsso_prenotazione from sso_prenotazione ".$sWhere);
		if (!$idsso_prenotazione)
		{
			$sSQL="insert into sso_prenotazione (idsso_anagrafica_utente,idsso_ente_join_servizio,idsso_ordine,quantita,$pfield,data) values('$pidsso_anagrafica_utente','0','$pidsso_ordine','1','$pvalue','".date("Y-m-d")."')";
		}
		else
		{
			$sSQL="update sso_prenotazione set $pfield='$pvalue' where idsso_prenotazione='$idsso_prenotazione'";
		}	

		$db->query($sSQL);
		echo $pfield;
		break;		
	case "profilocpisave":
		$pidsso_domanda_intervento=get_param("_i");
		$pai=new Pai($pidsso_domanda_intervento);
		$idsso_anagrafica_utente=$pai->idutente;
		$puser=get_cookieuserFront();
		$fldidutente=verifica_eutente($puser);
		$fldprofilocpi=front_get_db_value("SELECT idsso_anagrafica_utente FROM eso_join_anagrafica WHERE idgen_utente='$fldidutente'");

		$pprofilodata=invertidata(get_param("_d"),"-","/",1);
		$pprofiloarea=db_string(get_param("_a"));
		$pprofiloqualifica=db_string(get_param("_q"));
		$pprofilonote=db_string(get_param("_n"));
		$pprofilointerventi=db_string(get_param("_icpi"));
		$pprofiloesperienze=db_string(get_param("_e"));
		$pprofilopiano=db_string(get_param("_p"));

		$profiloCPI=new profiloCPI(0);
		$idsso_domanda_intervento_profilocpi=$profiloCPI->esistePROFILO($pidsso_domanda_intervento);
		if (!$idsso_domanda_intervento_profilocpi)
		{	
			$sSQL="insert into ".DBNAME_SS.".sso_domanda_intervento_profilocpi (idsso_domanda_intervento,idutente,profilodata,profiloarea,profiloqualifica,profilonote,profilocpi,profilointerventi,profiloesperienze,profilopiano) values(".
				"'$pidsso_domanda_intervento',".
				"'$idsso_anagrafica_utente',".
				"'$pprofilodata',".
				"'$pprofiloarea',".
				"'$pprofiloqualifica',".
				"'$pprofilonote',".
				"'$fldprofilocpi',".
				"'$pprofilointerventi',".
				"'$pprofiloesperienze',".
				"'$pprofilopiano')";
		}
		else
		{
			$sSQL="update ".DBNAME_SS.".sso_domanda_intervento_profilocpi set profilodata='$pprofilodata',profiloarea='$pprofiloarea',profilonote='$pprofilonote',profiloqualifica='$pprofiloqualifica',profilointerventi='$pprofilointerventi',profiloesperienze='$pprofiloesperienze',profilopiano='$pprofilopiano' where idsso_domanda_intervento_profilocpi='$idsso_domanda_intervento_profilocpi'";
		}		
		$db->query($sSQL);

		break;	
	case "aziendadatoriale":
		$pidsso_domanda_intervento=get_param("_i");
		$pai=new Pai($pidsso_domanda_intervento);
		$idsso_anagrafica_utente=$pai->idutente;
		$puser=get_cookieuserFront();
		$fldidutente=verifica_eutente($puser);
		$fldaziendadatoriale=front_get_db_value("SELECT idsso_anagrafica_utente FROM eso_join_anagrafica WHERE idgen_utente='$fldidutente'");

		$paziendatelefono=get_param("_t");
		$paziendanome=db_string(get_param("_n"));
		$paziendasede=db_string(get_param("_s"));
		$paziendareferente=db_string(get_param("_r"));
		$panziendamail=get_param("_e");
		$assegnazionedata=date("Y-m-d");

		$profiloAZIENDA=new profiloAZIENDA(0);
		$idsso_domanda_intervento_azienda=$profiloAZIENDA->esisteAZIENDA($pidsso_domanda_intervento);
		if (!$idsso_domanda_intervento_azienda)
		{	
			$sSQL="insert into ".DBNAME_SS.".sso_domanda_intervento_azienda (idsso_domanda_intervento,idsso_associazione,aziendanome,aziendasede,aziendareferente,aziendatelefono,anziendamail,assegnazionedata) values(".
				"'$pidsso_domanda_intervento',".
				"'$fldaziendadatoriale',".
				"'$paziendanome',".
				"'$paziendasede',".
				"'$paziendareferente',".
				"'$paziendatelefono',".
				"'$panziendamail',".
				"'$assegnazionedata')";
		}
		else
		{
			$sSQL="update ".DBNAME_SS.".sso_domanda_intervento_azienda set aziendanome='$paziendanome',aziendasede='$paziendasede',aziendareferente='$paziendareferente',aziendatelefono='$paziendatelefono',aziendaemail='$panziendamail' where idsso_domanda_intervento_azienda='$idsso_domanda_intervento_azienda'";
		}		
		$db->query($sSQL);

		break;	
	case "notificasintesi":
		//Seleziono tutte le associazioni datoriali
		$aASSOCIAZIONI=db_fill_array("select sso_anagrafica_utente.email,cognome from sso_anagrafica_utente inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente where idsso_tabella_tipologia_ente=24");
		$fldinvio="<br>";
		$fldoggetto="Profilo sintetico CPI";
		$fldtesto="La presente per comunicarLe che il centro per l'\impiego ha completato un nuovo profilo sintetico.";
		foreach ($aASSOCIAZIONI as $email => $nominativo) 
		{

			$aEMAIL=array();
			$aEMAIL[0]=$email;
			$aEMAIL[1]=$fldoggetto;
			$aEMAIL[2]=$fldtesto;
			$aEMAIL[3]="";
			$fldresult=sendMAIL($aEMAIL);		
			
		}
		break;
	case "notificapatto":
		$pidsso_domanda=get_param("_domanda");
		$domanda=new Domanda($pidsso_domanda);
		$idassistente_preliminare=$domanda->idassistente_preliminare;
		$assistente=new Utente($idassistente_preliminare);
		$email_assistente=$assistente->email;
		$pidutente=get_param("_utente");
		$beneficiario=new Beneficiario($pidutente);
		$fldinvio="<br>";
		$fldoggetto="Notifica patto di servizio";
		$fldtesto.="La presente per comunicare che il centro per l'impiego ha allegato il patto di servizio del beneficiario ".$beneficiario->cognome." ".$beneficiario->nome;

		if ($email_assistente)
		{
			$aEMAIL=array();
			$aEMAIL[0]=$email_assistente;
			$aEMAIL[1]=$fldoggetto;
			$aEMAIL[2]=$fldtesto;
			$aEMAIL[3]="";
			echo $fldresult=sendMAIL($aEMAIL);			
		}
		else
		{
			echo "Email del case manager non presente";
		}
		break;

	case "servizio_estratto_conto":
		$fldidutente=get_param("_idutente");
		$panno=get_param("_anno");

		$aSERVIZI=array();
		$aSERVIZI_ANNO=array();
		$aSERVIZI_ANNO=db_fill_array("SELECT DISTINCT sso_domanda.idsso_tabella_tipologia_domanda
			FROM sso_domanda 
			INNER JOIN sso_domanda_mensa ON sso_domanda.idsso_domanda=sso_domanda_mensa.idsso_domanda
			WHERE (sso_domanda.idutente='$fldidutente' OR sso_domanda.idpresentante='$fldidutente') 
			AND sso_domanda.anno='$panno' AND (sso_domanda.flag_elimina=0 OR sso_domanda.flag_elimina IS NULL) 
			AND (idsso_progetto IS NULL OR sso_domanda.idsso_progetto=0)");
		if(!empty($aSERVIZI_ANNO))
		{
			foreach($aSERVIZI_ANNO as $idservizio=>$value)
			{
				if($idservizio>0)
				{
					$aSERVIZI[]=$idservizio;
				}
			}
		}

		$aPROGETTI=array();
		$aPROGETTI_ANNO=array();
		$aPROGETTI_ANNO=db_fill_array("SELECT DISTINCT sso_domanda.idsso_progetto
			FROM sso_domanda 
			INNER JOIN sso_domanda_mensa ON sso_domanda.idsso_domanda=sso_domanda_mensa.idsso_domanda
			INNER JOIN sso_progetto ON sso_domanda.idsso_progetto=sso_progetto.idsso_progetto
			WHERE (sso_domanda.idutente='$fldidutente' OR sso_domanda.idpresentante='$fldidutente') 
			AND sso_domanda.anno='$panno' AND (sso_domanda.flag_elimina=0 OR sso_domanda.flag_elimina IS NULL) AND sso_domanda.idsso_progetto IS NOT NULL AND sso_domanda.idsso_progetto>0");
		if(!empty($aPROGETTI_ANNO))
		{
			foreach($aPROGETTI_ANNO as $idprogetto=>$value)
			{
				if($idprogetto>0)
				{
					$aPROGETTI[]=$idprogetto;
				}
			}
		}

		if(!empty($aSERVIZI) || !empty($aPROGETTI))
		{
			$result.='<select class="form-control input-sm" style="" name="tipo_servizio" id="tipo_servizio" required>';

			$result.='<option value=""></option>';
			
		  	if(in_array(IDSSO_TABELLA_TIPOLOGIA_DOMANDA_MENSE,$aSERVIZI))
		  	{

				$result.='<option value=\''.IDSSO_TABELLA_TIPOLOGIA_DOMANDA_MENSE.'\'>Mensa scolastica</option>';
			}

			if(in_array(IDSSO_TABELLA_TIPOLOGIA_DOMANDA_TRASPORTI,$aSERVIZI))
		  	{
				if($_SERVER['HTTP_HOST']!="37.206.216.84" && $_SERVER['HTTP_HOST']!="mense.comune.macerata.it" && $_SERVER['HTTP_HOST']!="sociali.comune.macerata.it")
				{

					$result.='<option value=\''.IDSSO_TABELLA_TIPOLOGIA_DOMANDA_TRASPORTI.'\'>Trasporto scolastico</option>';
				}
			}

			if(in_array(IDSSO_TABELLA_TIPOLOGIA_DOMANDA_NIDI,$aSERVIZI))
		  	{
				$result.='<option value=\''.IDSSO_TABELLA_TIPOLOGIA_DOMANDA_NIDI.'\'>Nido</option>';
			}

			$sSQL="select idsso_progetto,descrizione from sso_progetto where idgen_procedura='8' ";
			$aPROGETTI_VIS=db_fill_array($sSQL);
			if (is_array($aPROGETTI_VIS))
			{
				reset($aPROGETTI_VIS);
				while(list($idsso_progetto,$fldprogetto)=each($aPROGETTI_VIS))
				{
					if(in_array($idsso_progetto,$aPROGETTI))
		  			{
						$result.="\n <option value='".$idsso_progetto."p' >$fldprogetto</option>";
					}					
				}
			}

			$result.='</select>';

			echo $result;
		}
		else
			echo "<h5>Nessun servizio disponibile per l'anno scelto</h5>";
		break;

	case "sibadalogin":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);  		

		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);

		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente 
			FROM ".FRONT_ESONAME.".gen_utente 
			WHERE login='$puser' 
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente 
			FROM ".FRONT_ESONAME.".gen_utente 
			WHERE login='$puser' 
			AND password='$ppwd' 
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente 
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser' 
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}
  		
		if ($fldidgen_utente>0)
		{
			$oggi=date("Y-m-d");
			$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			$data=tosql(date("Y-m-d"),"Text");
			$ora=tosql(date("H:i:s"),"Text");
			$ip=$_SERVER['REMOTE_ADDR'];
			$ip = tosql($ip,'Text');
			$chiave=genera_chiave($data,$ora,$ip);

			setCookieUserFront($chiave);

			$obj = new OS_BR();
			$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
		
			$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
			$db_front->query($qrydeleteLog);
			
			$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
			$db_front->query($qry_insertLog);
			
        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

			$fldidgen_profilo=1;

			setCookieProfiloFront($fldidgen_profilo);			

			$fldflag_beneficiario=front_get_db_value("SELECT flag_beneficiario FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");
			if(!empty($fldflag_beneficiario))
				$sPage="../sibada/esibada_home.php";
			else	
				$sPage="../sibada/esibada_home_ditta.php";
			
			echo $sPage.$sParams;			
		}
		break;



	case "cimiterilogin":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);  		


		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);

		$pprocedura=get_param("procedura");

		$fldhomepage_url=front_get_db_value("SELECT homepage_login FROM ".FRONT_ESONAME.".gen_utente WHERE login='$puser' AND password='$ppwd' AND idtabella_stato=1");

		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND password='$ppwd'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser'
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}
  		
		if ($fldidgen_utente>0)
		{
			$oggi=date("Y-m-d");
			$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			$data=tosql(date("Y-m-d"),"Text");
			$ora=tosql(date("H:i:s"),"Text");
			$ip=$_SERVER['REMOTE_ADDR'];
			$ip = tosql($ip,'Text');
			$chiave=genera_chiave($data,$ora,$ip);

			setCookieUserFront($chiave);

			$obj = new OS_BR();
			$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
		
			$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
			$db_front->query($qrydeleteLog);
			
			$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
			$db_front->query($qry_insertLog);
			
        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

			$fldidgen_profilo=1;

			setCookieProfiloFront($fldidgen_profilo);

			$sPage="../sicare/esicare_beneficiario_home.php";

			$sParams = "?_f=true&_cim=true";
			echo $sPage.$sParams;
		}
		break;

	case "login_bs_fornitori":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);

		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);
		$pprocedura=get_param("procedura");

		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");

		/*
		if(
			($_SERVER["REMOTE_ADDR"]=="94.46.149.33"
			|| $_SERVER["REMOTE_ADDR"]=="94.46.149.36"
			|| $_SERVER["REMOTE_ADDR"]=="172.30.0.1"
			|| $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it"
			|| $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it"
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM_NUOVO
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM_VOIP
			|| $_SERVER["REMOTE_ADDR"]==REMOTE_IP_TELECOM
			|| $_SERVER["HTTP_HOST"]==LOCAL_IP
			|| $_SERVER["HTTP_HOST"]==LOCAL_IP_MAMP
			|| $_SERVER["HTTP_HOST"]=="pesaroeducativi.egovpu.it")
			&& !empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		*/
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND password='$ppwd'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser'
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}
  		
		//if ($fldidgen_utente>0 && $idsso_ente_servizio>0)
		if ($fldidgen_utente>0)
		{
			$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

			//Verifico se è una ditta
			$idsso_ente_servizio=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_ente_servizio WHERE idutente='$fldidsso_anagrafica_utente'");

			$fldidsso_tabella_tipologia_ente=front_get_db_value("SELECT idsso_tabella_tipologia_ente FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			if(!empty($idsso_ente_servizio))
			{
				$oggi=date("Y-m-d");
				$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

				//aggiornamento password ogni 3 mesi
				if(!empty_data($flddata_password))
				{
					$data_pass3 = date('Y-m-d', strtotime("+3 months", strtotime($flddata_password)));

					if($data_pass3<=$oggi)
					{
						if($accesso_temp)
							$params_accessotemp='&_accessotemp=true';
						elseif(empty($params_accessotemp))
	  						$params_accessotemp='&_accessotemp=true&_upd3=true';
						else
	  						$params_accessotemp.='&_upd3=true';
					}
				}

				$data=tosql(date("Y-m-d"),"Text");
				$ora=tosql(date("H:i:s"),"Text");
				$ip=$_SERVER['REMOTE_ADDR'];
				$ip = tosql($ip,'Text');
				$chiave=genera_chiave($data,$ora,$ip);

				setCookieUserFront($chiave);

				$obj = new OS_BR();
				$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
			
				$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
				$db_front->query($qrydeleteLog);
				
				$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
				$db_front->query($qry_insertLog);
				
	        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

				$fldidgen_profilo=front_get_db_value("SELECT idgen_profilo FROM ".FRONT_ESONAME.".gen_utente_profilo WHERE idgen_utente='$fldidgen_utente'");

				setCookieProfiloFront($fldidgen_profilo);

				$fldflag_beneficiario=front_get_db_value("SELECT flag_beneficiario FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");
				$fldidsso_tabella_tipologia_ente=front_get_db_value("select idsso_tabella_tipologia_ente from ".FRONT_ESONAME.".gen_utente where idgen_utente='$fldidgen_utente'");
				
				switch($pprocedura)
				{
					default:
					case IDPROCEDURA_SICARE:
						$sPage="../buonispesa/buonispesa_fornitore.php";
						break;
				}

				$sParams = "?_user=".$chiave;
				echo $sPage.$sParams.$params_accessotemp;
			}
			else
			{
				echo "cittadino";
			}
		}
		break;

	
	case "login_bs_cittadini":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);

		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);
		$pprocedura=get_param("procedura");

		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND password='$ppwd'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		
		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser'
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}
  		
  		
		//if ($fldidgen_utente>0 && empty($idsso_ente_servizio))
		if ($fldidgen_utente>0)
		{
			$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

			//Verifico se è una ditta
			$idsso_ente_servizio=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_ente_servizio WHERE idutente='$fldidsso_anagrafica_utente'");

			$fldidsso_tabella_tipologia_ente=front_get_db_value("SELECT idsso_tabella_tipologia_ente FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			if(empty($idsso_ente_servizio))
			{
				$oggi=date("Y-m-d");
				$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

				//aggiornamento password ogni 3 mesi
				if(!empty_data($flddata_password))
				{
					$data_pass3 = date('Y-m-d', strtotime("+3 months", strtotime($flddata_password)));

					if($data_pass3<=$oggi)
					{
						if($accesso_temp)
							$params_accessotemp='&_accessotemp=true';
						elseif(empty($params_accessotemp))
	  						$params_accessotemp='&_accessotemp=true&_upd3=true';
						else
	  						$params_accessotemp.='&_upd3=true';
					}
				}

				$data=tosql(date("Y-m-d"),"Text");
				$ora=tosql(date("H:i:s"),"Text");
				$ip=$_SERVER['REMOTE_ADDR'];
				$ip = tosql($ip,'Text');
				$chiave=genera_chiave($data,$ora,$ip);

				setCookieUserFront($chiave);

				$obj = new OS_BR();
				$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
			
				$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
				$db_front->query($qrydeleteLog);
				
				$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
				$db_front->query($qry_insertLog);
				
	        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

				$fldidgen_profilo=front_get_db_value("SELECT idgen_profilo FROM ".FRONT_ESONAME.".gen_utente_profilo WHERE idgen_utente='$fldidgen_utente'");

				setCookieProfiloFront($fldidgen_profilo);

				$fldflag_beneficiario=front_get_db_value("SELECT flag_beneficiario FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");
				$fldidsso_tabella_tipologia_ente=front_get_db_value("select idsso_tabella_tipologia_ente from ".FRONT_ESONAME.".gen_utente where idgen_utente='$fldidgen_utente'");
				
				switch($pprocedura)
				{
					default:
					case IDPROCEDURA_SICARE:
						$sPage="../buonispesa/buonispesa_home.php";
						break;
				}

				$sParams = "";
				echo $sPage.$sParams;
			}
			else
			{
				echo "fornitore";
			}
		}
		break;
	case "login_bras_cittadini":
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);

		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);
		$pprocedura=get_param("procedura");

		$fldpwdiccs=get_db_value("SELECT pwdiccs FROM sso_dati_generali WHERE idsso_dati_generali='1'");
		
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		else
		{
			$sSQL="SELECT idgen_utente
			FROM ".FRONT_ESONAME.".gen_utente
			WHERE login='$puser'
			AND password='$ppwd'
			AND idtabella_stato=1 AND flag_abilitato=1";
		}
		
		$fldidgen_utente=front_get_db_value($sSQL);

		
		$accesso_temp=false;
  		$params_accessotemp='';
  		if(empty($fldidgen_utente))
  		{
  			$sSQL="SELECT idgen_utente
	      	FROM ".FRONT_ESONAME.".gen_password_temp
	      	WHERE login='$puser'
	      	AND password_temp='$ppwd' AND flag_accesso=0";
  			$fldidgen_utente=front_get_db_value($sSQL);

  			$accesso_temp=true;

  			$params_accessotemp='&_accessotemp=true';
  		}
  		

		//if ($fldidgen_utente>0 && empty($idsso_ente_servizio))
		if ($fldidgen_utente>0)
		{
			$fldidsso_anagrafica_utente = get_idsso_anagrafica_utente($fldidgen_utente);

			//Verifico se è una ditta
			$idsso_ente_servizio=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_ente_servizio WHERE idutente='$fldidsso_anagrafica_utente'");

			$fldidsso_tabella_tipologia_ente=front_get_db_value("SELECT idsso_tabella_tipologia_ente FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

			if(empty($idsso_ente_servizio))
			{
				$oggi=date("Y-m-d");
				$flddata_password=front_get_db_value("SELECT data_password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");

				//aggiornamento password ogni 3 mesi
				if(!empty_data($flddata_password))
				{
					$data_pass3 = date('Y-m-d', strtotime("+3 months", strtotime($flddata_password)));

					if($data_pass3<=$oggi)
					{
						if($accesso_temp)
							$params_accessotemp='&_accessotemp=true';
						elseif(empty($params_accessotemp))
	  						$params_accessotemp='&_accessotemp=true&_upd3=true';
						else
	  						$params_accessotemp.='&_upd3=true';
					}
				}

				$data=tosql(date("Y-m-d"),"Text");
				$ora=tosql(date("H:i:s"),"Text");
				$ip=$_SERVER['REMOTE_ADDR'];
				$ip = tosql($ip,'Text');
				$chiave=genera_chiave($data,$ora,$ip);

				setCookieUserFront($chiave);

				$obj = new OS_BR();
				$browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');
			
				$qrydeleteLog="DELETE FROM ".FRONT_ESONAME.".log_accessi WHERE idgen_utente='$fldidgen_utente' AND data<>".$data;
				$db_front->query($qrydeleteLog);
				
				$qry_insertLog="INSERT INTO ".FRONT_ESONAME.".log_accessi (ip,data,ora,idgen_utente,chiave,browser_information) values (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
				$db_front->query($qry_insertLog);
				
	        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

				$fldidgen_profilo=front_get_db_value("SELECT idgen_profilo FROM ".FRONT_ESONAME.".gen_utente_profilo WHERE idgen_utente='$fldidgen_utente'");

				setCookieProfiloFront($fldidgen_profilo);

				$fldflag_beneficiario=front_get_db_value("SELECT flag_beneficiario FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");
				$fldidsso_tabella_tipologia_ente=front_get_db_value("select idsso_tabella_tipologia_ente from ".FRONT_ESONAME.".gen_utente where idgen_utente='$fldidgen_utente'");
				
				switch($pprocedura)
				{
					default:
					case IDPROCEDURA_SICARE:
						$sPage="../bonus800ras/bonus800ras_home.php";
						break;
				}

				$sParams = "";
				echo $sPage.$sParams;
			}
			else
			{
				echo "fornitore";
			}
		}
		break;
}

?>
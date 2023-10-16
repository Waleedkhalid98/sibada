<?php

include("./common.php");
include("../librerie/librerie.php");

global $db;

$db2 = new DB_Sql();
$db2->Database = DATABASE_NAME;
$db2->User     = DATABASE_USER;
$db2->Password = DATABASE_PASSWORD;
$db2->Host     = DATABASE_HOST;

global $db_front;

$chiave=get_cookieuser();
$profilo=get_param("profilo");
$menu=get_param("menu");

//verifica_utente($chiave);

$pidutente=get_param("_utente");
$pidsso_accoglienza=get_param("_contatto");

$paction=get_param("_action");

switch($paction) 
{		
	case "sibada_login_back":  		
  		$puser=stripslashes($_REQUEST["_u"]);
      	$puser=mysql_real_escape_string($puser);  		
  		$ppwd=$_REQUEST["_p"];
		$ppwd=md5($ppwd);

      	$fldpwdiccs=get_db_value("SELECT pwdiccs FROM ".DBNAME_SS.".sso_dati_generali WHERE idsso_dati_generali='1'");
		if(!empty($fldpwdiccs) && $fldpwdiccs==$ppwd)
		{			
			$sSQL="SELECT idutente 
	      	FROM utenti 
	      	WHERE login='$puser'";
		}
		else
		{
	      	$sSQL="SELECT idutente 
	      	FROM utenti 
	      	WHERE login='$puser' 
	      	AND password='$ppwd' AND idtabella_stato=1";
		}	

  		$fldidgen_utente=get_db_value($sSQL);

  		if($fldidgen_utente>0)
  		{
	        $oggi=date("Y-m-d");
			
	        $data=tosql(date("Y-m-d"),"Text");
	        $ora=tosql(date("H:i:s"),"Text");
			$ip=$_SERVER['REMOTE_ADDR'];
			$ip = tosql($ip,'Text');
		    $chiave=genera_chiave($data,$ora,$ip);

		    setCookieUser($chiave);
		    
		    $obj = new OS_BR();
            $browser_information=$obj->showInfo('browser').$obj->showInfo('version').$obj->showInfo('os');

		    $qrydeleteLog="DELETE FROM ".DBNAME_A.".log_accessi where idutente='$fldidgen_utente' AND data<>".$data;
		    $db->query($qrydeleteLog);
		    
		    $qry_insertLog="INSERT INTO ".DBNAME_A.".log_accessi (ip,data,ora,idutente,chiave,browser_information) VALUES (".$ip.",".$data.",".$ora.",'$fldidgen_utente',".tosql($chiave,"Text").",'$browser_information')";
		    $db->query($qry_insertLog);	  
        
        	$db->query("INSERT INTO ".DBNAME_A.".log_utente (ip,data,ora,chiave,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','login')");

	        $sPage = "../sibada/sibada_home.php";  			    
			echo $sPage;
		}

		break;

	case "loadbeneficiari_nominativo_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$pidsso_progetto=get_param("_idprogetto");	

		$sSql="select * 
		from sso_anagrafica_utente";
		
		$sWhere='';
		//$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		
		$sWhere=aggiungi_condizione($sWhere, "(LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')");
		//$sWhere=aggiungi_condizione($sWhere, "(cognome LIKE '$pvalue%' OR nome LIKE '$pvalue%')");
		//$sWhere=aggiungi_condizione($sWhere, "(cognome LIKE '%$pvalue%' OR nome LIKE '%$pvalue%')");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				if ($_SERVER['HTTP_HOST']=="buonispesa.sicare.it" || $_SERVER['HTTP_HOST']=="bonus800ras.sicare.it" || $_SERVER['HTTP_HOST']=="www.vouchercns.it" || $_SERVER['HTTP_HOST']=="care.immediaspa.com")
					$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				else	
					$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente',$pidsso_progetto);
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome=$db->f("cognome");
			$fldnome=$db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';
			$fldcodicefiscale=$db->f("codicefiscale");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			if(!empty($flddatanascita))
				$flddatanascita=' - '.$flddatanascita;
			
			if(!empty($fldcodicefiscale))
				$fldcodicefiscale=' - '.$fldcodicefiscale;
				
			$nominativo=$fldcognome.' '.$fldnome;

			$fldcodicefiscale_data=removeslashes($fldcodicefiscale_data);
			$fldcodicefiscale=removeslashes($fldcodicefiscale);
			$nominativo=removeslashes($nominativo);

			/*
			$nominativo=str_replace(' ', '-', $fldcognome.' '.$fldnome);
			$nominativo=preg_replace("/^[ A-Za-z0-9']*$/", '', $nominativo);
			$nominativo=str_replace('-', ' ', $nominativo);
			*/

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



	case "loadalunni_nominativo_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$sSql="SELECT * 
		FROM sso_domanda_mensa";
		
		$sWhere='';
		//$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		
		$sWhere=aggiungi_condizione($sWhere, "(nominativo LIKE '%$pvalue%' OR codice_fiscale LIKE '%$pvalue%')");

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" ORDER BY nominativo";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_domanda=$db->f("idsso_domanda");
			$nominativo=$db->f("nominativo");

			$flddatanascita = $db->f("data_nascita");
			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';
			$fldcodicefiscale=$db->f("codice_fiscale");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			$domanda=new Domanda($fldidsso_domanda);

			/*
			if(!empty($flddatanascita))
				$flddatanascita=' - '.$flddatanascita;
			
			if(!empty($fldcodicefiscale))
				$fldcodicefiscale=' - '.$fldcodicefiscale;
			*/

			$fldcodicefiscale_data=removeslashes($fldcodicefiscale_data);
			$fldcodicefiscale=removeslashes($fldcodicefiscale);
			$nominativo=removeslashes($nominativo);

			/*
			$nominativo=str_replace(' ', '-', $fldcognome.' '.$fldnome);
			$nominativo=preg_replace("/^[ A-Za-z0-9']*$/", '', $nominativo);
			$nominativo=str_replace('-', ' ', $nominativo);
			*/

			$nominativo=utf8_decode($nominativo);

			$popt=get_param("_opt");	//_opt imposta cosa visualizzare nell'imput (Nominativo/Data di nascita/Codice fiscale
			switch($popt)
			{
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

				case 5:
					$value=$nominativo." - ID $fldidsso_domanda";
					break;

				case 6:
					$value=$nominativo." - ".$flddatanascita." - ".$fldcodicefiscale." - ID $fldidsso_domanda";
					break;

				default:
					$value=$nominativo.$flddatanascita;
					break;
			}
			
			$record=array();
			$record['value']=$value;
			$record['data']=$fldidsso_domanda.'|'.$fldcodicefiscale_data;
			$record['idsso_domanda']=$fldidsso_domanda;
			$record['nominativo']=strtoupper($nominativo);
			$record['datanascita']=$flddatanascita;
			$record['codicefiscale']=strtoupper($fldcodicefiscale);
			$record['idsso_domanda']=$fldidsso_domanda;
			$record['tipologia']=strtoupper(get_descrizione_tipologia_domanda($domanda->idsso_tabella_tipologia_domanda, $domanda->idsso_progetto));

			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadalunnisessione_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$sSql="SELECT * 
		FROM sso_anagrafica_tessera 
		INNER JOIN sso_domanda ON sso_anagrafica_tessera.idsso_domanda=sso_domanda.idsso_domanda
		INNER JOIN sso_domanda_mensa ON sso_anagrafica_tessera.idsso_domanda=sso_domanda_mensa.idsso_domanda ";
		
		$sWhere='';		
		$sWhere=aggiungi_condizione($sWhere, "sso_anagrafica_tessera.idsso_domanda IS NOT NULL");
		$sWhere=aggiungi_condizione($sWhere, "(sso_domanda_mensa.nominativo LIKE '%$pvalue%' OR codice_fiscale LIKE '%$pvalue%')");

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" ORDER BY sso_domanda_mensa.nominativo";
		
		$sql=$sSql.$sWhere.$sOrder;
		$db->query($sql);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_domanda=$db->f("idsso_domanda");

			$domanda=new Domanda($fldidsso_domanda);
			$domanda_mensa=new Domanda_mensa(get_idsso_domanda_mensa($fldidsso_domanda));

			$nominativo=removeslashes($domanda_mensa->nominativo);

			/*
			$nominativo=str_replace(' ', '-', $fldcognome.' '.$fldnome);
			$nominativo=preg_replace("/^[ A-Za-z0-9']*$/", '', $nominativo);
			$nominativo=str_replace('-', ' ', $nominativo);
			*/

			$nominativo=utf8_decode($nominativo);

			$record=array();
			$record['value']=$nominativo.' '.$domanda_mensa->codice_fiscale;
			$record['data']=$fldidsso_domanda;
			$record['nominativo']=strtoupper($nominativo);
			$record['datanascita']=$domanda_mensa->data_nascita_formattata;
			$record['codicefiscale']=strtoupper($domanda_mensa->codice_fiscale);
			$record['idsso_domanda']=$fldidsso_domanda;
			$record['tipologia']=strtoupper(get_descrizione_tipologia_domanda($domanda->idsso_tabella_tipologia_domanda, $domanda->idsso_progetto));

			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadbeneficiari_nominativo":
		$pvalue=get_param("value");	

		$sSql="SELECT * 
		FROM sso_anagrafica_utente";
		$sWhere='';
		
		$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		
		if(!empty($pvalue))
		{
			$pvalue=db_string($pvalue);
			$sWhere=aggiungi_condizione($sWhere, "(LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')");
		}

		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldidsso_ente=$db->f("idsso_ente");
			$fldcognome=$db->f("cognome");
			$fldnome=$db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';
			$fldcodicefiscale=$db->f("codicefiscale");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			if(!empty($flddatanascita))
				$flddatanascita='    -    '.$flddatanascita;
			
			if(!empty($fldcodicefiscale))
				$fldcodicefiscale='    -    '.$fldcodicefiscale;
				
			$nominativo=$fldcognome.' '.$fldnome;

			$fldcodicefiscale_data=removeslashes($fldcodicefiscale_data);
			$fldcodicefiscale=removeslashes($fldcodicefiscale);
			$nominativo=removeslashes($nominativo);
			
			/*
			$nominativo=str_replace(' ', '-', $fldcognome.' '.$fldnome);
			$nominativo=preg_replace("/^[ A-Za-z0-9']*$/", '', $nominativo);
			$nominativo=str_replace('-', ' ', $nominativo);
			*/

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
			

			$fldnum_procedimento=get_sina_sinba_casellario($fldidutente,3219,1);

			$record=array();
			$record['value']=$value;
			$record['data']=$fldidutente.'|'.$fldcodicefiscale_data.'|'.$fldidsso_ente.'|'.$fldnum_procedimento;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}

		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadamministratori_sostegno":
		$sSQL = "SELECT * 
		FROM sso_anagrafica_utente 
		INNER JOIN sso_ente_servizio ON sso_anagrafica_utente.idutente=sso_ente_servizio.idutente 
		WHERE sso_ente_servizio.idsso_tabella_tipologia_ente='14'";
		$db->query($sSQL);
		$res=$db->next_record();
		$response=array();

		while($res)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome=$db->f("cognome");

			$record=array();
			$record['value']=$fldcognome;
			$record['data']=$fldidutente.'|'.$fldcognome;
			array_push($response, $record);

			$res=$db->next_record();
		}
	
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadbeneficiaro_numprovvedimento":
			$fldidutente=get_param("_idbeneficiario");
			echo $fldnum_procedimento=get_sina_sinba_casellario($fldidutente,3219,1);
		break;
		
		/* JAVASCRIPT
			var loader = dhtmlxAjax.postSync("sicare_action.php","_user=<?php echo $chiave;?>&profilo=<?php echo $profilo;?>&menu=<?php echo $menu;?>&_action=loadbeneficiari_nominativo");		
			myParam=loader.xmlDoc.responseText;  
			var nominativi = JSON.parse(myParam)
			$(function() {
				$('#nominativo').autocomplete({
				  lookup: nominativi,
				  minChars:1,
				  width:350,
				  maxHeight:200,
				  autoSelectFirst:true,
				  //showNoSuggestionNotice:"Nessun utente trovato",
				  //forceFixPosition:true,
				  onSelect: function (suggestion) {
					var mySplitResult = suggestion.data.split("|");
					fldidutente=mySplitResult[0]

					document.getElementById("idutente").value=fldidutente
				  }
				});
			});
		*/
		


	case "loadbeneficiari_cf":

		$db->query("select * from sso_anagrafica_utente where idtipo=9 order by cognome, nome ");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';
			$fldcodicefiscale = $db->f("codicefiscale");

			if(!empty($flddatanascita))
				$flddatanascita='  -  '.$flddatanascita;
				
			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			$fldcognome = removeslashes($fldcognome);
			$fldnome = removeslashes($fldnome);

			$flddata=$fldidutente.'|'.$fldcognome.' '.$fldnome;
			$flddata=utf8_decode($flddata);

			$record=array();
			$record['value']=$fldcodicefiscale;
			$record['data']=$flddata;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;		



	case "loadbeneficiari_cf_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$sSql="select * from sso_anagrafica_utente";
		
		$sWhere='';
		$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		$sWhere=aggiungi_condizione($sWhere, "LTRIM(codicefiscale) LIKE '%$pvalue%'");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$fldcodicefiscale = $db->f("codicefiscale");

			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			$fldcognome = removeslashes($fldcognome);
			$fldnome = removeslashes($fldnome);

			$record=array();
			$record['value']=$fldcodicefiscale.' - '.$fldcognome.' '.$fldnome;
			$record['data']=$fldidutente.'|'.$fldcognome.' '.$fldnome;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		


	// sicare_bando_richiesta_domanda.php
	case "loadbeneficiari_cf_isee_value":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$pidsso_progetto=get_param("_idprogetto");	

		$sSql="select * from sso_anagrafica_utente";
		
		$sWhere='';
		$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		$sWhere=aggiungi_condizione($sWhere, "LTRIM(codicefiscale) LIKE '%$pvalue%'");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente',$pidsso_progetto);
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$fldcodicefiscale = $db->f("codicefiscale");
			$fldisee =get_db_value("SELECT valore_isee_famiglia from sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
				
			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			$fldcognome = removeslashes($fldcognome);
			$fldnome = removeslashes($fldnome);
			$fldisee = removeslashes($fldisee);
			if(empty($fldisee))
				$fldisee='0,00';
			else
				$fldisee=number_format($fldisee,2,',','');

			$record=array();
			$record['value']=$fldcodicefiscale.' - '.$fldcognome.' '.$fldnome;
			$record['data']=$fldidutente.'|'.$fldcognome.' '.$fldnome.'|'.$fldisee;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	// sicare_bando_richiesta_domanda.php
	case "loadbeneficiari_nominativo_isee_value":

		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);
		$segretariato_roma=get_param("_segretariato");

		$sSql="SELECT * 
		FROM sso_anagrafica_utente";
		
		$sWhere='';
		//$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		$sWhere=aggiungi_condizione($sWhere, "(LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$sql=$sSql.$sWhere.$sOrder;
		$db->query($sql);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$flddatanascita = $db->f("data_nascita");

			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';

			$fldcodicefiscale = $db->f("codicefiscale");
			$fldemail = $db->f("email");
			$fldtelefono = $db->f("telefono");
			$fldcellulare = $db->f("cellulare");
			$fldindirizzo = $db->f("indirizzo");
			$fldcivico = $db->f("civico");
			$fldsesso = $db->f("sesso");

			$fldpalazzina = $db->f("palazzina");
			$fldscala = $db->f("scala");
			$fldinterno = $db->f("interno");
			$fldpiano = $db->f("piano");
			$fldflag_ascensore = $db->f("flag_ascensore");
			$fldcitofonare = $db->f("citofonare");
			$fldasl_residenza = $db->f("asl_residenza");
			$fldasl_descrizione = $db->f("asl_descrizione");

			$fldidgen_comune_nascita = $db->f("idgen_comune_nascita");
			$fldidamb_comune = $db->f("idamb_comune_residenza");
			$fldcitta = $db->f("citta");
			$fldprov = $db->f("prov");
			$fldcap = $db->f("cap");
			$fldprov_nascita = $db->f("prov_nascita");

			$fldidamb_nazione=get_db_value("select idamb_nazione from sso_anagrafica where idutente='$fldidutente'");
			if($fldidamb_nazione=="122")
				$fldcomune_nascita=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
			else
				$fldcomune_nascita = $db->f("comune_nascita");

			$fldflag_domicilio_differente=get_db_value("select flag_domicilio_differente from sso_anagrafica where idutente='$fldidutente'");


			if(!empty($fldflag_domicilio_differente))
			{
				$fldidgen_comune_domicilio=get_db_value("select idgen_comune_domicilio from sso_anagrafica where idutente='$fldidutente'");

				if(!empty($fldidgen_comune_domicilio))
					$fldcitta_domicilio=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_domicilio'");
				else
					$fldcitta_domicilio="";

				$fldindirizzo_domicilio=get_db_value("select indirizzo_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldcivico_domicilio=get_db_value("select civico_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldprov_domicilio=get_db_value("select provincia_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldcap_domicilio=get_db_value("select cap_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldpalazzina_domicilio=get_db_value("select palazzina_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldscala_domicilio=get_db_value("select scala_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldinterno_domicilio=get_db_value("select interno_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldpiano_domicilio=get_db_value("select piano_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldflag_ascensore_domicilio=get_db_value("select flag_ascensore_domicilio from sso_anagrafica where idutente='$fldidutente'");
				$fldcitofonare_domicilio=get_db_value("select citofonare_domicilio from sso_anagrafica where idutente='$fldidutente'");
			}

			$sSQL="SELECT * FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'";
			$db2->query($sSQL);
			$next_record=$db2->next_record();

			$fldidsso_tabella_grado_istruzione=$db2->f("idsso_tabella_grado_istruzione");
			$fldidsso_tabella_tipo_occupazione=$db2->f("idsso_tabella_tipo_occupazione");
			$fldidsso_tbl_situazioneabitativa=$db2->f("idsso_tbl_situazioneabitativa");
			$fldflag_affitto=$db2->f("flag_affitto");
			$fldimporto_affitto=$db2->f("importo_abitazione");
			$fldflag_sfratto=$db2->f("flag_sfratto");
			$fldidsso_tbl_disabilita=$db2->f("idsso_tbl_disabilita");
			$fldflag_accompagno=$db2->f("flag_accompagno");
			$fldflag_certificazioni_invalidicivili=$db2->f("flag_certificazioni_invalidicivili");
			$fldpercentuale_invalidita_civile=$db2->f("percentuale_invalidita_civile");
			$fldnominativo_medico=$db2->f("nominativo_medico");
			$fldtelefono_medico=$db2->f("telefono_medico");
			$fldes_ticket=$db2->f("esenzione_ticket");
			
			$fldidgen_comune_documento=$db2->f("idgen_comune_documento");
			if(!empty($fldidgen_comune_documento))
				$fldcitta_documento=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_documento'");
			else
				$fldcitta_documento="";

			$fldflag_documentosoggiorno=$db2->f("flag_documentosoggiorno");	
			$flddata_rilascio=$db2->f("documento_data_rilascio");
			if(!empty_data($flddata_rilascio))
				$flddata_rilascio=invertidata($flddata_rilascio,"/","-",2);
			else
				$flddata_rilascio="";

			$flddata_scadenza=$db2->f("data_scadenza");	
			if(!empty_data($flddata_scadenza))
				$flddata_scadenza=invertidata($flddata_scadenza,"/","-",2);
			else
				$flddata_scadenza="";

			$fldidsso_tabella_condizione_soggiorno=$db2->f("idsso_tabella_condizione_soggiorno");
			$fldflag_nomade=$db2->f("flag_nomade");
			$fldnumero_documento=$db2->f("documento_numero");	
			$fldidsso_tbl_documento_ente=$db2->f("idsso_tbl_documento_ente");	
			$fldes_ticket=$db2->f("esenzione_ticket");

			$fldisee =get_db_value("SELECT valore_isee_famiglia from sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");

			if(!empty($flddatanascita))
				$flddatanascita_='  -  '.$flddatanascita;
			else
				$flddatanascita_='';
			
			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			if(empty($fldcodicefiscale))
				$fldcodicefiscale=' ';
			
			$fldcognome = removeslashes($fldcognome);
			$fldnome = removeslashes($fldnome);
			$fldisee = removeslashes($fldisee);
			if(empty($fldisee))
				$fldisee='0,00';
			else
				$fldisee=number_format($fldisee,2,',','');
			/*
			$fldcognome = str_replace(' ', '-', $fldcognome);
			$fldcognome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldcognome);
			$fldcognome = str_replace('-', ' ', $fldcognome);
			$fldnome = str_replace(' ', '-', $fldnome);
			$fldnome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldnome);
			$fldnome = str_replace('-', ' ', $fldnome);
			*/
			
			$record=array();
			$record['value']=$fldcognome.' '.$fldnome.$flddatanascita_;

			if($segretariato_roma==1)
				$record['data']=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldemail.'|'.$fldtelefono.'|'.$fldcellulare.'|'.$flddatanascita.'|'.$fldindirizzo."|".$fldcivico."|".$fldsesso."|".$fldidamb_nazione.'|'.$fldpalazzina.'|'.$fldscala.'|'.$fldinterno.'|'.$fldpiano.'|'.$fldflag_ascensore.'|'.$fldcitofonare.'|'.$fldasl_descrizione.'|'.$fldidgen_comune_nascita.'|'.$fldcitta.'|'.$fldprov.'|'.$fldcap."|".$fldcomune_nascita."|".$fldidamb_comune."|".$fldprov_nascita."|".$fldflag_domicilio_differente."|".$fldidgen_comune_domicilio."|".$fldcitta_domicilio."|".$fldindirizzo_domicilio."|".$fldcivico_domicilio."|".$fldprov_domicilio."|".$fldcap_domicilio."|".$fldpalazzina_domicilio."|".$fldscala_domicilio."|".$fldinterno_domicilio."|".$fldpiano_domicilio."|".$fldflag_ascensore_domicilio."|".$fldcitofonare_domicilio."|".$fldnominativo_medico."|".$fldtelefono_medico."|".$fldidsso_tabella_condizione_soggiorno."|".$fldnumero_documento."|".$fldidsso_tbl_documento_ente."|".$fldidgen_comune_documento."|".$fldcitta_documento."|".$flddata_rilascio."|".$flddata_scadenza."|".$fldflag_nomade."|".$fldflag_documentosoggiorno."|".$fldidsso_tabella_grado_istruzione."|".$fldidsso_tabella_tipo_occupazione."|".$fldidsso_tbl_situazioneabitativa."|".$fldflag_affitto."|".$fldflag_sfratto."|".$fldimporto_affitto."|".$fldidsso_tbl_disabilita."|".$fldflag_accompagno."|".$fldflag_certificazioni_invalidicivili."|".$fldpercentuale_invalidita_civile."|".$fldes_ticket;
			else
				$record['data']=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldisee.'|'.$fldemail.'|'.$fldtelefono.'|'.$fldcellulare.'|'.$flddatanascita.'|'.$fldindirizzo." ".$fldcivico."|".$fldsesso."|".$fldidamb_nazione."|".$fldidsso_tabella_grado_istruzione;
			
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;	



	case "load_data_beneficiario_segretariato_roma":

		$fldidutente=get_param("_id");
		
		if(!empty($fldidutente))
		{
			$sSql="select * from sso_anagrafica_utente where idutente='$fldidutente'";

			$db->query($sSql);
			$next_record=$db->next_record();
				
			$response=array();
			while($next_record)
			{
				$fldidutente=$db->f("idutente");
				$fldcognome = $db->f("cognome");
				$fldnome = $db->f("nome");
				$flddatanascita = $db->f("data_nascita");

				if(!empty_data($flddatanascita))
					$flddatanascita=invertidata($flddatanascita,"/","-",2);
				else
					$flddatanascita='';

				$fldcodicefiscale = $db->f("codicefiscale");
				$fldemail = $db->f("email");
				$fldtelefono = $db->f("telefono");
				$fldcellulare = $db->f("cellulare");
				$fldindirizzo = $db->f("indirizzo");
				$fldcivico = $db->f("civico");
				$fldsesso = $db->f("sesso");

				$fldpalazzina = $db->f("palazzina");
				$fldscala = $db->f("scala");
				$fldinterno = $db->f("interno");
				$fldpiano = $db->f("piano");
				$fldflag_ascensore = $db->f("flag_ascensore");
				$fldcitofonare = $db->f("citofonare");
				$fldasl_residenza = $db->f("asl_residenza");

				$fldidamb_comune = $db->f("idamb_comune_residenza");
				if(!empty($fldidamb_comune))
					$fldcitta=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune'");
				else
					$fldcitta = $db->f("citta");
				
				$fldprov = $db->f("prov");
				$fldcap = $db->f("cap");
				$fldprov_nascita = $db->f("prov_nascita");

				$fldidgen_comune_nascita = $db->f("idgen_comune_nascita");
				$fldidamb_nazione=get_db_value("select idamb_nazione from sso_anagrafica where idutente='$fldidutente'");
				if($fldidamb_nazione=="122")
					$fldcomune_nascita=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
				else
					$fldcomune_nascita = $db->f("comune_nascita");

				$fldidsso_tabella_stato_civile=get_db_value("select idsso_tabella_stato_civile from sso_anagrafica where idutente='$fldidutente'");

				$fldflag_domicilio_differente=get_db_value("select flag_domicilio_differente from sso_anagrafica where idutente='$fldidutente'");

				if(!empty($fldflag_domicilio_differente))
				{
					$fldidgen_comune_domicilio=get_db_value("select idgen_comune_domicilio from sso_anagrafica where idutente='$fldidutente'");

					if(!empty($fldidgen_comune_domicilio))
						$fldcitta_domicilio=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_domicilio'");
					else
						$fldcitta_domicilio="";

					$fldindirizzo_domicilio=get_db_value("select indirizzo_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldcivico_domicilio=get_db_value("select civico_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldprov_domicilio=get_db_value("select provincia_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldcap_domicilio=get_db_value("select cap_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldpalazzina_domicilio=get_db_value("select palazzina_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldscala_domicilio=get_db_value("select scala_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldinterno_domicilio=get_db_value("select interno_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldpiano_domicilio=get_db_value("select piano_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldflag_ascensore_domicilio=get_db_value("select flag_ascensore_domicilio from sso_anagrafica where idutente='$fldidutente'");
					$fldcitofonare_domicilio=get_db_value("select citofonare_domicilio from sso_anagrafica where idutente='$fldidutente'");
				}

				$sSQL="SELECT * FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'";
				$db2->query($sSQL);
				$next_record=$db2->next_record();

				$fldidsso_tabella_grado_istruzione=$db2->f("idsso_tabella_grado_istruzione");
				$fldidsso_tabella_tipo_occupazione=$db2->f("idsso_tabella_tipo_occupazione");
				$fldidsso_tbl_situazioneabitativa=$db2->f("idsso_tbl_situazioneabitativa");
				$fldflag_affitto=$db2->f("flag_affitto");
				$fldimporto_affitto=$db2->f("importo_abitazione");
				$fldflag_sfratto=$db2->f("flag_sfratto");
				$fldidsso_tbl_disabilita=$db2->f("idsso_tbl_disabilita");
				$fldflag_accompagno=$db2->f("flag_accompagno");
				$fldflag_certificazioni_invalidicivili=$db2->f("flag_certificazioni_invalidicivili");
				$fldpercentuale_invalidita_civile=$db2->f("percentuale_invalidita_civile");
				$fldnominativo_medico=$db2->f("nominativo_medico");
				$fldtelefono_medico=$db2->f("telefono_medico");
				$fldes_ticket=$db2->f("esenzione_ticket");
				
				$fldidgen_comune_documento=$db2->f("idgen_comune_documento");
				if(!empty($fldidgen_comune_documento))
					$fldcitta_documento=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_documento'");
				else
					$fldcitta_documento="";

				$fldflag_documentosoggiorno=$db2->f("flag_documentosoggiorno");	
				$flddata_rilascio=$db2->f("documento_data_rilascio");
				if(!empty_data($flddata_rilascio))
					$flddata_rilascio=invertidata($flddata_rilascio,"/","-",2);
				else
					$flddata_rilascio="";

				$flddata_scadenza=$db2->f("data_scadenza");	
				if(!empty_data($flddata_scadenza))
					$flddata_scadenza=invertidata($flddata_scadenza,"/","-",2);
				else
					$flddata_scadenza="";

				$fldidsso_tabella_condizione_soggiorno=$db2->f("idsso_tabella_condizione_soggiorno");
				$fldflag_nomade=$db2->f("flag_nomade");
				$fldnumero_documento=$db2->f("documento_numero");	
				$fldidsso_tbl_documento_ente=$db2->f("idsso_tbl_documento_ente");	
				$fldes_ticket=$db2->f("esenzione_ticket");

				$fldisee =get_db_value("SELECT valore_isee_famiglia from sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");

				if(!empty($flddatanascita))
					$flddatanascita_='  -  '.$flddatanascita;
				else
					$flddatanascita_='';
				
				$fldcodicefiscale = removeslashes($fldcodicefiscale);
				if(empty($fldcodicefiscale))
					$fldcodicefiscale=' ';
				
				$fldcognome = removeslashes($fldcognome);
				$fldnome = removeslashes($fldnome);
				
				$record=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldemail.'|'.$fldtelefono.'|'.$fldcellulare.'|'.$flddatanascita.'|'.$fldindirizzo."|".$fldcivico."|".$fldsesso."|".$fldidamb_nazione.'|'.$fldpalazzina.'|'.$fldscala.'|'.$fldinterno.'|'.$fldpiano.'|'.$fldflag_ascensore.'|'.$fldcitofonare.'|'.$fldasl_residenza.'|'.$fldidgen_comune_nascita.'|'.$fldcitta.'|'.$fldprov.'|'.$fldcap."|".$fldcomune_nascita."|".$fldidamb_comune."|".$fldprov_nascita."|".$fldflag_domicilio_differente."|".$fldidgen_comune_domicilio."|".$fldcitta_domicilio."|".$fldindirizzo_domicilio."|".$fldcivico_domicilio."|".$fldprov_domicilio."|".$fldcap_domicilio."|".$fldpalazzina_domicilio."|".$fldscala_domicilio."|".$fldinterno_domicilio."|".$fldpiano_domicilio."|".$fldflag_ascensore_domicilio."|".$fldcitofonare_domicilio."|".$fldnominativo_medico."|".$fldtelefono_medico."|".$fldidsso_tabella_condizione_soggiorno."|".$fldnumero_documento."|".$fldidsso_tbl_documento_ente."|".$fldidgen_comune_documento."|".$fldcitta_documento."|".$flddata_rilascio."|".$flddata_scadenza."|".$fldflag_nomade."|".$fldflag_documentosoggiorno."|".$fldidsso_tabella_grado_istruzione."|".$fldidsso_tabella_tipo_occupazione."|".$fldidsso_tbl_situazioneabitativa."|".$fldflag_affitto."|".$fldflag_sfratto."|".$fldimporto_affitto."|".$fldidsso_tbl_disabilita."|".$fldflag_accompagno."|".$fldflag_certificazioni_invalidicivili."|".$fldpercentuale_invalidita_civile."|".$fldes_ticket."|".$fldidsso_tabella_stato_civile;

				$next_record = $db->next_record();  
			}

			echo $record;
		}
		else
			echo "error";

		break;	
	break;



	case "loadbeneficiari_nominativo_svama":
		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$sSql="select * from sso_anagrafica_utente";
		
		$sWhere='';
		$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		$sWhere=aggiungi_condizione($sWhere, "(LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome = $db->f("cognome");
			$fldnome = $db->f("nome");
			$flddatanascita = $db->f("data_nascita");
			if(!empty_data($flddatanascita))
				$flddatanascita=invertidata($flddatanascita,"/","-",2);
			else
				$flddatanascita='';

			$fldcodicefiscale = $db->f("codicefiscale");
			$fldemail = $db->f("email");
			$fldtelefono = $db->f("telefono");
			$fldcellulare = $db->f("cellulare");
			$fldindirizzo = $db->f("indirizzo");
			$fldcivico = $db->f("civico");
			$fldtelefono = $db->f("telefono");
			$fldsesso = $db->f("sesso");
			$fldidamb_comune_residenza = $db->f("idamb_comune_residenza");
			if(!empty($fldidamb_comune_residenza))
			{
				$fldcomune=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
				$fldprov=get_db_value("SELECT provincia FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
			}
			else
			{
				$fldcomune=$db->f("citta");
				$fldprov=$db->f("prov");
			}

			$fldisee =get_db_value("SELECT valore_isee_famiglia from sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");

			if(!empty($flddatanascita))
				$flddatanascita_='  -  '.$flddatanascita;

			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			if(empty($fldcodicefiscale))
				$fldcodicefiscale=' ';
			
			$fldcognome = removeslashes($fldcognome);
			$fldnome = removeslashes($fldnome);
			$fldisee = removeslashes($fldisee);
			if(empty($fldisee))
				$fldisee='0,00';
			else
				$fldisee=number_format($fldisee,2,',','');

			$fldidsso_tabella_stato_civile=get_db_value("SELECT idsso_tabella_stato_civile FROM sso_anagrafica WHERE idutente='$fldidutente'");
			$fldidsso_tabella_grado_istruzione=get_db_value("SELECT idsso_tabella_grado_istruzione FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldidsso_tbl_situazioneabitativa=get_db_value("SELECT idsso_tbl_situazioneabitativa FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldimporto_affitto=get_db_value("SELECT importo_abitazione FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldimporto_affitto=number_format($fldimporto_affitto,2,",","");
			$fldtessera_sanitaria=get_db_value("SELECT tessera_sanitaria FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldes_ticket=get_db_value("SELECT esenzione_ticket FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldidsso_tbl_pensione=get_db_value("SELECT idsso_tbl_pensione FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldreddito_familiare=get_db_value("SELECT reddito_familiare FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			$fldreddito_familiare=number_format($fldreddito_familiare,2,",","");
			
			/*
			$fldcognome = str_replace(' ', '-', $fldcognome);
			$fldcognome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldcognome);
			$fldcognome = str_replace('-', ' ', $fldcognome);
			$fldnome = str_replace(' ', '-', $fldnome);
			$fldnome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldnome);
			$fldnome = str_replace('-', ' ', $fldnome);
			*/
			
			$record=array();
			$record['value']=$fldcognome.' '.$fldnome.$flddatanascita_;
			$record['data']=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldisee.'|'.$fldemail.'|'.$fldtelefono.'|'.$fldcellulare.'|'.$flddatanascita.'|'.$fldindirizzo.'|'.$fldcivico.'|'.$fldtelefono.'|'.$fldcomune.'|'.$fldidamb_comune_residenza.'|'.$fldprov.'|'.$fldidsso_tabella_stato_civile.'|'.$fldsesso.'|'.$fldidsso_tabella_grado_istruzione.'|'.$fldidsso_tbl_situazioneabitativa.'|'.$fldimporto_affitto.'|'.$fldtessera_sanitaria.'|'.$fldes_ticket.'|'.$fldidsso_tbl_pensione.'|'.$fldreddito_familiare;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;	



	case "loadbeneficiari_nominativo_isee":
		$db->query("select * from sso_anagrafica_utente order by cognome, nome");
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
			$fldisee =get_db_value("SELECT valore_isee_famiglia from sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");

			if(!empty($flddatanascita))
				$flddatanascita='  -  '.$flddatanascita;
				
			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			if(empty($fldcodicefiscale))
				$fldcodicefiscale=' ';
			
			$fldnominativo=$fldcognome.' '.$fldnome;

			$fldnominativo = removeslashes($fldnominativo);
			$fldisee = removeslashes($fldisee);
			if(empty($fldisee))
				$fldisee='0,00';
			else
				$fldisee=number_format($fldisee,2,',','');

			$fldnominativo=utf8_decode($fldnominativo);

			$record=array();
			$record['value']=$fldnominativo.$flddatanascita;
			$record['data']=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldisee;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadbeneficiari_nominativo_bando":
		
		$pidsso_progetto=get_param("_idprogetto");	

		$pvalue=get_param("value");	
		$pvalue=db_string($pvalue);

		$ponlyname=get_param("onlyname");

		$sSql="select * from sso_anagrafica_utente";
		
		$sWhere='';
		//$sWhere=aggiungi_condizione($sWhere, 'idtipo=9');
		$sWhere=aggiungi_condizione($sWhere, "(LTRIM(cognome) LIKE '$pvalue%' OR LTRIM(nome) LIKE '$pvalue%')");
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco dei beneficiari in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('sso_anagrafica_utente.idsso_ente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);

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
			$fldidgen_cittadinanza1 = $db->f("idgen_cittadinanza1");
			$fldnazionalita = get_db_value("SELECT nazione FROM ".DBNAME_A.".nazione WHERE idnazione='$fldidgen_cittadinanza1'");
			$fldsesso = $db->f("sesso");
			$fldindirizzo = $db->f("indirizzo");
			$fldcivico = $db->f("civico");
			$fldemail = $db->f("email");
			$fldidgen_comune_nascita = $db->f("idgen_comune_nascita");
			if(!empty($fldidgen_comune_nascita))
			{
				$fldcomune_nascita=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
				$fldprov_nascita=get_db_value("SELECT provincia FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
			}
			else
			{
				$fldcomune_nascita=$db->f("comune_nascita");
				$fldprov_nascita=$db->f("prov_nascita");
			}

			$fldidamb_comune_residenza = $db->f("idamb_comune_residenza");
			if(!empty($fldidamb_comune_residenza))
			{
				$fldcomune=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
				$fldprov=get_db_value("SELECT provincia FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
				$fldcap=get_db_value("SELECT cap FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
			}
			else
			{
				$fldcomune=$db->f("citta");
				$fldprov=$db->f("prov");
				$fldcap=$db->f("cap");
			}

			$fldcellulare = $db->f("cellulare");

			$fldidsso_anagrafica=get_db_value("SELECT idsso_anagrafica FROM ".DBNAME_SS.".sso_anagrafica WHERE idutente='$fldidutente'");
			$fldidamb_nazione=get_db_value("SELECT idamb_nazione FROM ".DBNAME_SS.".sso_anagrafica WHERE idsso_anagrafica='$fldidsso_anagrafica'");

			if(!empty($flddatanascita))
				$flddatanascita_='  -  '.$flddatanascita;
			else
				$flddatanascita_="";

			$fldcodicefiscale = removeslashes($fldcodicefiscale);
			if(empty($fldcodicefiscale))
				$fldcodicefiscale=' ';
			else
				$fldcodicefiscale_='  -  '.$fldcodicefiscale;
			
			$fldnominativo=$fldcognome.' '.$fldnome;
			$fldnominativo = removeslashes($fldnominativo);
			$fldnominativo=utf8_decode($fldnominativo);

			$fldidtipo_pagamento=get_db_value("SELECT idtipo_pagamento FROM sso_progetto_tipo_pagamento WHERE idsso_progetto='$pidsso_progetto' AND idutente='$fldidutente'");

			$record=array();
			if (empty($ponlyname))
				$record['value']=$fldnominativo.$fldcodicefiscale_.$flddatanascita_;
			else
				$record['value']=$fldnominativo;
			$record['data']=$fldidutente.'|'.$fldcodicefiscale.'|'.$fldidamb_nazione.'|'.$fldsesso.'|'.$flddatanascita.'|'.$fldcomune_nascita.'|'.$fldprov_nascita.'|'.$fldindirizzo.'|'.$fldcivico.'|'.$fldcomune.'|'.$fldidamb_comune_residenza.'|'.$fldprov.'|'.$fldcellulare.'|'.$fldidgen_cittadinanza1.'|'.$fldidgen_comune_nascita.'|'.$fldemail.'|'.$fldidtipo_pagamento.'|'.$fldcap;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		


	case "loadnazioni":
		$db->query("select * from ".DBNAME_A.".nazione order by idnazione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidnazione=$db->f("idnazione");
			$fldnazione=$db->f("nazione");
			
			$fldnazione = removeslashes($fldnazione);
			
			$record=array();
			$record['value']=$fldnazione;
			$record['data']=$fldidnazione;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;	



	case "loadcomuni":
		$db->query("select * from ".DBNAME_A.".comune order by comune");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidcomune=$db->f("idcomune");
			$flcomune=$db->f("comune");
			$flprovincia=$db->f("provincia");
						
			$flcomune = removeslashes($flcomune);

			$popt=get_param("_opt");	//_opt imposta cosa visualizzare nell'imput (Nominativo/Data di nascita/Codice fiscale
			switch($popt){
				case 1:
					$data=$fldidcomune.'|'.$flprovincia;
					break;
				case 2:
					$data=$fldidcomune;
					break;
				default:
					$data=$fldidcomune.'|'.$flprovincia;
					break;
			}
			
			$record=array();
			$record['value']=$flcomune.' ('.$flprovincia.')';
			$record['data']=$data;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;	



	case "loadaree":
		$db->query("select * from ".DBNAME_SS.".sso_tbl_area order by descrizione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_area=$db->f("idsso_tbl_area");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_tbl_area;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		

			
	case "loadservizi":
		$db->query("select * 
			from ".DBNAME_SS.".sso_tbl_servizio 
			order by descrizione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_tbl_servizio;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
	


	case "loadservizi_area":
		$pidsso_tbl_area=get_param("_area");
		$sSQL = "SELECT idsso_tbl_servizio,descrizione FROM sso_tbl_servizio WHERE idsso_tbl_area='$pidsso_tbl_area' ORDER BY descrizione";
		$db->query($sSQL);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_tbl_servizio;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadaree":
		$db->query("select * from ".DBNAME_SS.".sso_tbl_area order by descrizione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_area=$db->f("idsso_tbl_area");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);
			
			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_tbl_area;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		
		

	case "loadprestazioni":

		$ponlybypai=get_param("_onlybypai");
		if ($ponlybypai=="true")
		{
			$db->query("select sso_tbl_prestazione.idsso_tbl_prestazione,sso_tbl_prestazione.descrizione from ".DBNAME_SS.".sso_tbl_prestazione inner join sso_domanda_prestazione on sso_tbl_prestazione.idsso_tbl_prestazione=sso_domanda_prestazione.idsso_tbl_prestazione group by sso_tbl_prestazione.idsso_tbl_prestazione,sso_tbl_prestazione.descrizione ");
		}
		else	
		{
			$db->query("select * from ".DBNAME_SS.".sso_tbl_prestazione order by descrizione");
		}
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_prestazione=$db->f("idsso_tbl_prestazione");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione." (".$fldidsso_tbl_prestazione.")";
			$record['data']=$fldidsso_tbl_prestazione;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;

	case "loadprestazioni_dett":
		$pvalue=get_param("value");
		$db->query("SELECT * FROM ".DBNAME_SS.".sso_tbl_prestazione WHERE descrizione LIKE ".tosql("%".$pvalue."%", "Text")." ORDER BY descrizione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_prestazione=$db->f("idsso_tbl_prestazione");
			$flddescrizione=$db->f("descrizione");
			
			$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
			$fldservizio=get_descrizione_servizio($fldidsso_tbl_servizio);
			
			$fldidsso_tbl_area=get_db_value("SELECT idsso_tbl_area FROM sso_tbl_servizio WHERE idsso_tbl_servizio='$fldidsso_tbl_servizio'");
			$fldarea=get_descrizione_area($fldidsso_tbl_area);

			$flddescrizione=removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_tbl_prestazione;
			$record['area']=$fldarea;
			$record['servizio']=$fldservizio;

			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		
		/*
		$pidsso_tbl_prestazione=get_param("_idsso_tbl_prestazione");
		$flddescrizione_prestazione=get_db_value("SELECT descrizione FROM sso_tbl_prestazione WHERE idsso_tbl_prestazione='$pidsso_tbl_prestazione'");

		<input name="prestazione" id="prestazione" type="text" class="form-control input-sm" value="<?php echo $flddescrizione_prestazione; ?>" size="25" maxlength="68">
		<input type="hidden" name="idsso_tbl_prestazione" id="idsso_tbl_prestazione" value="<?php echo $pidsso_tbl_prestazione; ?>" >

		fldprestazione=document.getElementById("prestazione").value
		fldidsso_tbl_prestazione=document.getElementById("idsso_tbl_prestazione").value
		if(fldprestazione=="")
		fldidsso_tbl_prestazione="";
	
		var loader = dhtmlxAjax.postSync("sicare_action.php","_user=<?php echo $chiave;?>&profilo=<?php echo $profilo;?>&menu=<?php echo $menu;?>&_action=loadprestazioni");		
		myParam=loader.xmlDoc.responseText;  
		var nominativi = JSON.parse(myParam)
		$(function() {
			$('#prestazione').autocomplete({
			  lookup: nominativi,
			  minChars:1,
			  width:350,
			  maxHeight:200,
			  autoSelectFirst:true,
			  //showNoSuggestionNotice:"Nessun utente trovato",
			  //forceFixPosition:true,
			  onSelect: function (suggestion) {
				var mySplitResult = suggestion.data.split("|");
				idsso_tbl_prestazione=mySplitResult[0]

				document.getElementById("idsso_tbl_prestazione").value=idsso_tbl_prestazione
			  }
			});
		});
		*/

		
	/*
	case "loadfornitori":

		$db->query("SELECT * FROM sso_anagrafica_utente inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente order by cognome");
		$next_record=$db->next_record();
			
		$reply='[';
		while($next_record)
		{
			$fldcognome=$db->f("cognome");
			$fldidutente=$db->f("idutente");
						
			$fldcognome = removeslashes($fldcognome);
			
			//Rimuove tutti i caratteri speciali
			$fldcognome = str_replace(' ', '-', $fldcognome);
			$fldcognome = preg_replace('/[^A-Za-z0-9\-]/', '', $fldcognome);
			$fldcognome = str_replace('-', ' ', $fldcognome);
			
			$reply.='{"value":"'.$fldcognome.'","data":"'.$fldidutente.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;
	*/


	case "loadfornitori":
		$sSQL="SELECT * 
		FROM sso_anagrafica_utente 
		INNER JOIN sso_ente_servizio ON sso_anagrafica_utente.idutente=sso_ente_servizio.idutente 
		ORDER BY cognome";
		$db->query($sSQL);
		$next_record=$db->next_record($sSQL);
			
		$response=array();
		while($next_record)
		{
			$fldcognome=$db->f("cognome");
			$fldidutente=$db->f("idutente");
							
			$fldcognome=removeslashes($fldcognome);
			$fldcognome=utf8_decode($fldcognome);

			$fornitore=array();
			$fornitore['value']=$fldcognome;
			$fornitore['data']=$fldidutente;
			array_push($response, $fornitore);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;

	case "loadfornitori_dett":
		$pvalue=get_param("value");
		$sSQL="SELECT * 
		FROM sso_anagrafica_utente 
		INNER JOIN sso_ente_servizio ON sso_anagrafica_utente.idutente=sso_ente_servizio.idutente 
		WHERE cognome LIKE '%$pvalue%'
		ORDER BY cognome";
		$db->query($sSQL);
		$next_record=$db->next_record($sSQL);
			
		$response=array();
		while($next_record)
		{
			$fldcognome=$db->f("cognome");
			$fldidutente=$db->f("idutente");
							
			$fldcognome=removeslashes($fldcognome);
			$fldcognome=utf8_decode($fldcognome);

			$fornitore=array();
			$fornitore['value']=$fldcognome;
			$fornitore['data']=$fldidutente;
			array_push($response, $fornitore);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;


	case "loadmail_fornitore":
		$sSQL="SELECT * 
		FROM sso_anagrafica_utente 
		INNER JOIN sso_ente_servizio ON sso_anagrafica_utente.idutente=sso_ente_servizio.idutente 
		WHERE sso_anagrafica_utente.idutente='$pidutente'";
		$db->query($sSQL);
		$next_record=$db->next_record($sSQL);
					
		$fldmail=$db->f("email");
							
		$response=array();
		$response["mail"]=$fldmail;
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "loadfornitoriconsorzio":
		$pidsso_consorzio=get_param("_consorzio");
		$db->query("SELECT * FROM sso_anagrafica_utente inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente where idsso_consorzio='$pidsso_consorzio' order by cognome");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldcognome=$db->f("cognome");
			$fldidutente=$db->f("idutente");
							
			$fldcognome=removeslashes($fldcognome);
			$fldcognome=utf8_decode($fldcognome);

			$fornitore=array();
			$fornitore['value']=$fldcognome;
			$fornitore['data']=$fldidutente;
			array_push($response, $fornitore);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;

		

	// Lista di tutti i beneficiari registrati nel front
	case "loadbeneficiari_front":

		$array_utenti_registrati=array();

		$sSQL="SELECT eso_join_anagrafica.idsso_anagrafica_utente 
		FROM gen_utente 
		INNER JOIN eso_join_anagrafica ON eso_join_anagrafica.idgen_utente=gen_utente.idgen_utente
		WHERE gen_utente.flag_beneficiario='1'";
		$db_front->query($sSQL);
		$res_front=$db_front->next_record();
		while($res_front)
		{
			$fldidsso_anagrafica_utente=$db_front->f("idsso_anagrafica_utente");
			$array_utenti_registrati[]=$fldidsso_anagrafica_utente;
			$res_front=$db_front->next_record();
		}

		$response=array();

		$sSQL="SELECT sso_anagrafica_utente.* 
		FROM sso_anagrafica_utente 
		ORDER BY cognome, nome";	

		$db2->query($sSQL);		
		$next_record=$db2->next_record();
		while($next_record)
		{
			$fldidutente=$db2->f("idutente");
			
			if(!in_array($fldidutente, $array_utenti_registrati))
			{
				$next_record = $db2->next_record();  
				continue;
			}

			$fldcognome=$db2->f("cognome");
			$fldnome=$db2->f("nome");
			$fldidutente=$db2->f("idutente");
					
			$fldnominativo=$fldcognome.' '.$fldnome;	
			$fldnominativo=removeslashes($fldnominativo);
			$fldnominativo=utf8_decode($fldnominativo);

			$beneficiario=array();
			$beneficiario['value']=$fldnominativo;
			$beneficiario['data']=$fldidutente;
			array_push($response, $beneficiario);

			$next_record = $db2->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;
		


	case "loadoperatori_fornitori":
		$pidutente_fornitore=get_param("idutente_fornitore");
		$ptype=get_param("_tipo");
		$sSQL="SELECT * 
		FROM sso_anagrafica_operatore 
		WHERE idsso_anagrafica_utente='$pidutente_fornitore' 
		AND operatore_abilitato!='N' 
		order by nominativo";
		$db->query($sSQL);
		$next_record=$db->next_record();
		$result="";	
		$response=array();
		while($next_record)
		{
			$fldnominativo=$db->f("nominativo");
			$fldidsso_anagrafica_operatore=$db->f("idsso_anagrafica_operatore");
						
			$fldnominativo=removeslashes($fldnominativo);
			$fldnominativo=utf8_decode($fldnominativo);

			$record=array();
			$record['value']=$fldnominativo;
			$record['data']=$fldidsso_anagrafica_operatore;
			array_push($response, $record);
			$result.="<option value='".$fldidsso_anagrafica_operatore."'>".$fldnominativo."</option>";

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		if ($ptype=='option')
			echo $result;
		else	
			echo json_encode($response);
		break;
	
	case "notifiche_scrivania":
		$fldidgen_utente=verifica_utente($chiave);

		$fldidgen_tbl_qualifica=get_db_value("select idtipo from ".DBNAME_A.".utenti where idutente='$fldidgen_utente'");		
		$aBACHECAQUALIFICA=db_fill_array("select idsso_bacheca_operatore,idsso_tbl_bacheca from sso_bacheca_operatore where idgen_tbl_qualifica='$fldidgen_tbl_qualifica' order by idsso_tbl_bacheca");
		$aATTIVITA=array();
		$aBACHECA=array();

		if (is_array($aBACHECAQUALIFICA))
		{
			reset($aBACHECAQUALIFICA);
				
			while(list($fldidsso_bacheca_operatore,$fldidsso_tbl_bacheca)=each($aBACHECAQUALIFICA))
			{
				
				if ($fldidsso_tbl_bacheca==1)
				{
					$sPAGE="sicare_svama_contatto.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						if ($sWhere)
						  $sWhere.=" and ";
						else
						  $sWhere=" where ";	  
						$sWhere.=" (idgen_destinatario='$fldidgen_utente') and idsso_tbl_attivita_stato=1 and (data_chiusura is null or data_chiusura='0000-00-00')";
	
						$sSQL=$sSQL.$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_attivita_operatore");
							$fldidsso_pratica=$db->f("idsso_pratica");
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idsso_anagrafica_utente");
							$fldidsso_tbl_attivita=$db->f("idsso_tbl_attivita");
							$fldPAGE=$sPAGE."?idutente=$fldidutente&amp;_attivita=$fldidtabella&amp;&_scrivania=true";	//&_contatto=$fldidsso_pratica
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto=$db->f("oggetto_attivita");	
							
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldkey,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,1,$fldPAGE);						
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}
				
				if ($fldidsso_tbl_bacheca==2)
				{
					$sPAGE="ss_domanda_segretariato.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{					
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						if ($sWhere)
						  $sWhere.=" and ";
						else
						  $sWhere=" where ";
	
						$aENTI=responsabileENTE($fldidgen_utente);
						$cWhere="";
						foreach ($aENTI as $fldcentro_territoriale)
						{
							 if ($cWhere)
							   $cWhere.=" or ";	
							 $cWhere.=" idgen_ente='$fldcentro_territoriale'";
						}					  
	
						if ($cWhere)
							$sWhere.=" ($cWhere) ";
						else 
							$sWhere.=" idgen_ente=9999";					
						
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_attivita_operatore");
							$fldidsso_pratica=$db->f("idsso_pratica");
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idsso_anagrafica_utente");
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto=$db->f("oggetto_attivita");	
							$fldidsso_tbl_attivita=$db->f("idsso_tbl_attivita");
							//$fldPAGE=$sPAGE."?idutente=$fldidutente";
							if ($fldidsso_tbl_attivita==4)
								$fldPAGE="ss_domanda.php?idsso_domanda=$fldidsso_pratica&amp;_attivita=$fldidtabella";
							else
								$fldPAGE=$sPAGE."?idsso_accoglienza=$fldidsso_pratica&amp;_attivita=$fldidtabella";
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldkey,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,2,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==3)
				{
					$sPAGE="sicare_pai_tab.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");						      												
						if (!$sWhere && $sql_operatore)
							$sWhere=" where ".$sql_operatore;
						elseif ($sWhere && $sql_operatore)
							$sWhere=$sWhere." and ".$sql_operatore;

						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idutente");								
							$fldoggetto="Servizio da approvare ";
							$fldidsso_pratica=$db->f("idsso_domanda_intervento");
							$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
							$fldservizio=get_db_value("select descrizione from sso_tbl_servizio where idsso_tbl_servizio='$fldidsso_tbl_servizio'");
							if ($fldservizio)
								$fldoggetto=$fldoggetto." - ".$fldservizio;
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idutente");
							$fldutente=anagrafica_name_servizi($fldidutente);
								
							$fldPAGE=$sPAGE."?_id=$fldidsso_pratica";
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,3,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							
							$next_record=$db->next_record();
						}
					}	
				}				

				if ($fldidsso_tbl_bacheca==4)
				{

					$sPAGE="sicare_pai_tab.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");						      												
						if (!$sWhere && $sql_operatore)
							$sWhere=" where ".$sql_operatore;
						elseif ($sWhere && $sql_operatore)
							$sWhere=$sWhere." and ".$sql_operatore;

						//$sWhere.=" and sso_domanda_intervento.idassistente='$fldidgen_utente'";
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idutente");								
							$fldoggetto="Servizio da avviare";
							$fldidsso_pratica=$db->f("idsso_domanda_intervento");
							$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
							$fldservizio=get_db_value("select descrizione from sso_tbl_servizio where idsso_tbl_servizio='$fldidsso_tbl_servizio'");
							if ($fldservizio)
								$fldoggetto=$fldoggetto." - ".$fldservizio;
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idutente");
							$fldutente=anagrafica_name_servizi($fldidutente);
								
							$fldPAGE=$sPAGE."?_id=$fldidsso_pratica";
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,3,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							
							$next_record=$db->next_record();
						}
					}	
				}

				if ($fldidsso_tbl_bacheca==5)
				{
					$sPAGE="ss_fattura_ente.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						if ($sWhere)
						  $sWhere.=" and ";
						else
						  $sWhere=" where ";	  
						$sWhere.=" (idgen_operatore_assegnato='$fldidgen_utente') ";
	
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_fattura");
							$fldidsso_pratica=$db->f("idsso_fattura");
							$flddata=$db->f("data");
							$fldidutente=$db->f("idsso_struttura");
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto="Fattura assegnata";
							$fldPAGE=$sPAGE."?idsso_fattura=$fldidtabella";							
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidtabella,5,$fldPAGE);										
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}	

				if ($fldidsso_tbl_bacheca==7)
				{
					$sPAGE="ss_fattura_ente.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
					
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						
						$aAREE=areeRESPONSABILE($fldidgen_utente);
						
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_fattura");
							$fldidsso_domanda_intervento=get_db_value("select idsso_domanda_intervento from sso_fattura_utente where idsso_fattura='$fldidtabella' and idsso_domanda_intervento>0");
							$fldidsso_tbl_area=get_db_value("select idsso_tbl_area from sso_domanda_intervento where idsso_domanda_intervento='$fldidsso_domanda_intervento'");
							if (in_array($fldidsso_tbl_area,$aAREE))
							{
								$fldidsso_pratica=$db->f("idsso_fattura");
								$flddata=$db->f("data");
								$fldidutente=$db->f("idsso_struttura");
								$fldutente=anagrafica_name_servizi($fldidutente);
								$fldoggetto="Fattura da assegnare";		
								$fldPAGE=$sPAGE."?idsso_fattura=$fldidtabella";									
								$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidtabella,7,$fldPAGE);					
								$aBACHECA[]=$aATTIVITA;
							}	
							$next_record=$db->next_record();
						}
					}
				}	

				if ($fldidsso_tbl_bacheca==8)
				{
					$sPAGE="ss_fattura_ente.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_fattura");
							$fldidsso_pratica=$db->f("idsso_fattura");
							$flddata=$db->f("data");
							$fldidutente=$db->f("idsso_struttura");
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto="Fattura da approvare";		
							$fldPAGE=$sPAGE."?idsso_fattura=$fldidtabella";									
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidtabella,8,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}					
				
				if ($fldidsso_tbl_bacheca==9)
				{
					$sPAGE="ss_graduatoria_dettaglio.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
	
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							
							$fldidtabella=$db->f("idsso_graduatoria");
							$fldidsso_progetto=$db->f("idsso_progetto");
							$fldprogetto=$db->f("descrizione");
							$fldanno=$db->f("anno");
							$fldidsso_tbl_graduatoria_stato=$db->f("idsso_tbl_graduatoria_stato");
							$fldstato=get_db_value("select descrizione from sso_tbl_graduatoria_stato where idsso_tbl_graduatoria_stato='$fldidsso_tbl_graduatoria_stato'");
							$fldidsso_tabella_motivo_domanda=$db->f("idsso_tabella_motivo_domanda");			
							$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");			
							$fldservizio=get_db_value("select descrizione from sso_tbl_servizio where idsso_tbl_servizio='$fldidsso_tbl_servizio'");
							$fldPAGE=$sPAGE."?idsso_graduatoria=$fldidtabella";												
							$fldoggetto="Graduatoria da approvare servizio: $fldservizio";							
							
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidtabella,9,$fldPAGE);				
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==10)
				{
					global $db_front;
					$sPAGE="ss_credenziali_dettaglio.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sSQL=str_replace("\".FRONT_ESONAME.\"",FRONT_ESONAME,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db_front->query($sSQL);
						$next_record=$db_front->next_record();
						while($next_record)
						{
							$fldkey=$db_front->f("idgen_utente");
							$fldcognome = $db_front->f("cognome");
							$fldnome = $db_front->f("nome");
							$flddata_richiesta = invertidata($db_front->f("data_richiesta"),"/","-",2);
							$fldindirizzo = $db_front->f("indirizzo");
							$fldcitta = $db_front->f("citta");
							$fldpiva = $db_front->f("piva");
							$fldidtabella_stato=$db_front->f("idtabella_stato");
							$fldflag_beneficiario=$db_front->f("flag_beneficiario");
							$fldutente=$fldcognome." ".$fldnome;
							$fldoggetto="Credenziali da rilasciare";	
							$fldPAGE=$sPAGE."?idgen_utente=$fldkey";							
							$aATTIVITA=array($fldkey,$flddata_richiesta,$fldutente,$fldoggetto,$fldkey,10,$fldPAGE);						
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db_front->next_record();
						}
					}
				}		


				if ($fldidsso_tbl_bacheca==11)
				{
					$sPAGE="eso_accreditamento.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						global $db_front;
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sSQL=str_replace("\".FRONT_ESONAME.\"",FRONT_ESONAME,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db_front->query($sSQL);
						$next_record=$db_front->next_record();
						while($next_record)
						{
							$fldkey=$db_front->f("ideso_accreditamento");
							$fldidgen_utente = $db_front->f("idgen_utente");
							$fldcognome = $db_front->f("cognome");
							$flddata_invio = $db_front->f("data_invio");
							$fldorario_invio = substr($db_front->f("orario_invio"),0,5);
							$fldidsso_tbl_accreditamento_stato=$db_front->f("idsso_tbl_accreditamento_stato");
							$fldidsso_progetto = $db_front->f("idsso_progetto");
							$fldprogetto = get_db_value("select descrizione from sso_progetto where idsso_progetto='$fldidsso_progetto'");
							$fldoggetto="Valutazione istanza di accreditamento";	
							$fldPAGE=$sPAGE."?_key=$fldkey";						
							$aATTIVITA=array($fldkey,$flddata_invio,$fldcognome,$fldoggetto,$fldkey,11,$fldPAGE);						
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db_front->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==12)
				{
					$sPAGE="eso_convenzionamento.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						global $db_front;
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sSQL=str_replace("\".FRONT_ESONAME.\"",FRONT_ESONAME,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db_front->query($sSQL);
						$next_record=$db_front->next_record();
						while($next_record)
						{
							
							$fldkey=$db_front->f("ideso_convenzionamento");
							$fldidgen_utente = $db_front->f("idgen_utente");
							$fldcognome = $db_front->f("cognome");
							$flddata_invio = $db_front->f("data_invio");
							$fldorario_invio = substr($db_front->f("orario_invio"),0,5);
							$fldoggetto="Valutazione istanza di convenzionamento";	
							$fldPAGE=$sPAGE."?_key=$fldkey";						
							$aATTIVITA=array($fldkey,$flddata_invio,$fldcognome,$fldoggetto,$fldkey,12,$fldPAGE);						
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db_front->next_record();
						}
					}
				}				
				
				if ($fldidsso_tbl_bacheca==13)
				{
					$sPAGE="ss_fattura_ente.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sWhere.=" and idgen_operatore='$fldidgen_utente'";
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_fattura");
							$fldidsso_pratica=$db->f("idsso_fattura");
							$flddata=$db->f("data");
							$fldidutente=$db->f("idsso_struttura");
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto="Fattura da controllare";		
							$fldPAGE=$sPAGE."?idsso_fattura=$fldidtabella";									
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidtabella,8,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}		

				if ($fldidsso_tbl_bacheca==14)
				{
					$sPAGE="../atti/satt_determinazione_dettaglio.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_ATTI.\"",DBNAME_ATTI,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
						   $fldidtabella=$db->f("idsatt_atto"); 
						   $fldidsatt_tbl_tipologia=$db->f("idsatt_tbl_tipologia");
						   $fldtipologia=get_db_value("SELECT descrizione FROM ".DBNAME_ATTI.".satt_tbl_tipologia WHERE idsatt_tbl_tipologia='$fldidsatt_tbl_tipologia'");
						   $fldidgen_tbl_area=$db->f("idgen_tbl_area");
						   $fldarea=get_db_value("SELECT descrizione FROM ".DBNAME_A.".gen_tbl_area WHERE idgen_tbl_area='$fldidgen_tbl_area'");
						   $fldanno_atto=$db->f("anno_atto");
						   $fldidgen_responsabile=$db->f("idgen_responsabile");
						   $fldutente=anagrafica_name($fldidgen_responsabile);
						   $fldnumero_atto=$db->f("numero_atto");
						   $flddata_atto=$db->f("data_atto");
						   $flddescrizione=$db->f("descrizione");
						   $fldoggetto=$db->f("oggetto");
							
							
							$fldoggetto="Atto da pubblicare - $flddescrizione";		
							$fldPAGE=$sPAGE."?idsatt_atto=$fldidtabella";									
							$aATTIVITA=array($fldidtabella,$flddata_atto,$fldutente,$fldoggetto,$fldidtabella,14,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}		

				if ($fldidsso_tbl_bacheca==15)
				{
					$sPAGE="../atti/satt_determinazione_dettaglio.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_ATTI.\"",DBNAME_ATTI,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
						   $fldidtabella=$db->f("idsatt_atto"); 
						   $fldidsatt_tbl_tipologia=$db->f("idsatt_tbl_tipologia");
						   $fldtipologia=get_db_value("SELECT descrizione FROM ".DBNAME_ATTI.".satt_tbl_tipologia WHERE idsatt_tbl_tipologia='$fldidsatt_tbl_tipologia'");
						   $fldidgen_tbl_area=$db->f("idgen_tbl_area");
						   $fldarea=get_db_value("SELECT descrizione FROM ".DBNAME_A.".gen_tbl_area WHERE idgen_tbl_area='$fldidgen_tbl_area'");
						   $fldanno_atto=$db->f("anno_atto");
						   $fldidgen_responsabile=$db->f("idgen_responsabile");
						   $fldutente=anagrafica_name($fldidgen_responsabile);
						   $fldnumero_atto=$db->f("numero_atto");
						   $flddata_atto=$db->f("data_proposta_atto");
						   $flddescrizione=$db->f("descrizione");
						   $fldoggetto=$db->f("oggetto");
							
							
							$fldoggetto="Determinazione da firmare - $flddescrizione";		
							$fldPAGE=$sPAGE."?idsatt_atto=$fldidtabella";									
							$aATTIVITA=array($fldidtabella,$flddata_atto,$fldutente,$fldoggetto,$fldidtabella,15,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}					
				
				if ($fldidsso_tbl_bacheca==16)
				{
					$sPAGE="../atti/satt_atto_liquidato.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_ATTI.\"",DBNAME_ATTI,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
						   $fldidtabella=$db->f("idsatt_atto"); 
						   $fldidsatt_tbl_tipologia=$db->f("idsatt_tbl_tipologia");
						   $fldtipologia=get_db_value("SELECT descrizione FROM ".DBNAME_ATTI.".satt_tbl_tipologia WHERE idsatt_tbl_tipologia='$fldidsatt_tbl_tipologia'");
						   $fldidgen_tbl_area=$db->f("idgen_tbl_area");
						   $fldarea=get_db_value("SELECT descrizione FROM ".DBNAME_A.".gen_tbl_area WHERE idgen_tbl_area='$fldidgen_tbl_area'");
						   $fldanno_atto=$db->f("anno_atto");
						   $fldidgen_responsabile=$db->f("idgen_responsabile");
						   $fldutente=anagrafica_name($fldidgen_responsabile);
						   $fldnumero_atto=$db->f("numero_atto");
						   $flddata_atto=$db->f("data_proposta_atto");
						   $flddescrizione=$db->f("descrizione");
						   $fldoggetto=$db->f("oggetto");
							
							
							$fldoggetto="Determinazione di liquidazione da firmare - $flddescrizione";		
							$fldPAGE=$sPAGE."?idsatt_atto=$fldidtabella";									
							$aATTIVITA=array($fldidtabella,$flddata_atto,$fldutente,$fldoggetto,$fldidtabella,16,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}	

				if ($fldidsso_tbl_bacheca==17)	// Domande da inviare
				{
					$sPAGE="sicare_sia_domandatab.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{					
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						if ($sWhere)
						  $sWhere.=" and ";
						else
						  $sWhere=" where ";
	
						$aENTI=responsabileENTE($fldidgen_utente);
						$cWhere="";
						foreach ($aENTI as $fldcentro_territoriale)
						{
						 	if ($cWhere)
							   $cWhere.=" or ";	
						 	$cWhere.=" sso_anagrafica_utente.idsso_ente='$fldcentro_territoriale'";
						}					  
	
						if ($cWhere)
							$sWhere.=" ($cWhere) ";
						else 
							$sWhere.=" sso_anagrafica_utente.idsso_ente=9999";					
						
						$sSQL=$sSQL." ".$sWhere." order by data_protocollo,ora_protocollo ";
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldkey=$db->f("idsso_domanda");
							$fldidsso_pratica=$db->f("idsso_pratica");
							$flddata=$db->f("data_protocollo");
							$fldora=$db->f("ora_protocollo");
							$fldidutente=$db->f("idutente");
							$fldidsso_progetto=$db->f("idsso_progetto");
							$fldcodice_inps=get_db_value("select codice_inps from sso_progetto where idsso_progetto='$fldidsso_progetto'");
							$fldutente=anagrafica_name_servizi($fldidutente);
							switch($fldcodice_inps)
							{
								case "SIASISMA":
									$fldoggetto="DOMANDA CARTA SIA AREE SISMA";
									$sPAGE="sicare_siasisma_domandatab.php";
									break;

								case "SIA":
									$fldoggetto="DOMANDA CARTA SIA ";
									$sPAGE="sicare_sia_domandatab.php";
									break;

								case "REI":
									$fldoggetto="DOMANDA CARTA REI ";
									$sPAGE="sicare_rei_domandatab.php";
									break;
							}
							
							//$fldPAGE=$sPAGE."?idutente=$fldidutente";
							$fldPAGE=$sPAGE."?_domanda=$fldkey&amp;_idprogetto=$fldidsso_progetto";
							$aATTIVITA=array($fldkey,$flddata,$fldutente,$fldoggetto,$fldidsso_progetto,17,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==18)
				{
					$sPAGE="sicare_beneficiario_csiaprogetto.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{					
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						
						$db->query($sSQL.$sWhere);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_domanda");
							$fldidutente=$db->f("idutente");
							$fldcognome=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
							$fldnome=get_db_value("SELECT nome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
							$fldutente=$fldcognome." ".$fldnome;
							$flddata_firma_progetto=$db->f("data_firma_progetto");
							//$flddata_firma_progetto=invertidata($flddata_firma_progetto,"/","-",2);
							$fldoggetto="FIRMATO PROGETTO SIA";
							
							$fldPAGE=$sPAGE."?_domanda=$fldidtabella";

							$aATTIVITA=array($fldidtabella,$flddata_firma_progetto,$fldutente,$fldoggetto,$fldidtabella,18,$fldPAGE);					
							$aBACHECA[]=$aATTIVITA;

							$next_record=$db->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==19)
				{
					$sPAGE="sicare_comunicazioni_mail_dettaglio.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{					
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
						if ($sWhere)
						  $sWhere.=" and ";
						else
						  $sWhere=" where ";

						$aENTI=responsabileENTE($fldidgen_utente);
						$cWhere="";
						foreach ($aENTI as $fldcentro_territoriale)
						{
							 if ($cWhere)
							   $cWhere.=" or ";	
							 $cWhere.=" idgen_ente='$fldcentro_territoriale'";
						}					  
	
						if ($cWhere)
							$sWhere.=" ($cWhere) ";
						else 
							$sWhere.=" idgen_ente=9999";					
						
						if ($sWhere)
							$sWhere.=" and idgen_destinatario='$fldidgen_utente'";

						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idsso_attivita_operatore");
							$fldidsso_pratica=$db->f("idsso_pratica");
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idsso_anagrafica_utente");
							$fldutente=anagrafica_name_servizi($fldidutente);
							$fldoggetto=$db->f("oggetto_attivita");	
							$fldidsso_tbl_attivita=$db->f("idsso_tbl_attivita");
							$fldPAGE=$sPAGE."?_iddiario=$fldidsso_pratica&amp;_attivita=$fldidtabella&_scrivania=true";
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldkey,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,2,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							$next_record=$db->next_record();
						}
					}
				}

				if ($fldidsso_tbl_bacheca==21)
				{
					$sPAGE="sicare_pai_tab.php";
					$sSQL=get_db_value("select myquery from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");
					if ($sSQL)
					{
						$sSQL=str_replace("\".DBNAME_A.\"",DBNAME_A,$sSQL);
						$sWhere=get_db_value("select mywhere from sso_tbl_bacheca where idsso_tbl_bacheca='$fldidsso_tbl_bacheca'");						      												
						if (!$sWhere && $sql_operatore)
							$sWhere=" where ".$sql_operatore;
						elseif ($sWhere && $sql_operatore)
							$sWhere=$sWhere." and ".$sql_operatore;

						$sSQL=$sSQL." ".$sWhere;
						$db->query($sSQL);
						$next_record=$db->next_record();
						while($next_record)
						{
							$fldidtabella=$db->f("idutente");								
							$fldoggetto="Servizio da approvare ";
							$fldidsso_pratica=$db->f("idsso_domanda_intervento");
							$fldidsso_tbl_servizio=$db->f("idsso_tbl_servizio");
							$fldservizio=get_db_value("select descrizione from sso_tbl_servizio where idsso_tbl_servizio='$fldidsso_tbl_servizio'");
							if ($fldservizio)
								$fldoggetto=$fldoggetto." - ".$fldservizio;
							$flddata=$db->f("data_inizio");
							$fldidutente=$db->f("idutente");
							$fldutente=anagrafica_name_servizi($fldidutente);
								
							$fldPAGE=$sPAGE."?_id=$fldidsso_pratica";
							$fldkey=$fldidtabella.".".$fldidutente.".".$fldidsso_pratica;
							$aATTIVITA=array($fldidtabella,$flddata,$fldutente,$fldoggetto,$fldidsso_pratica,3,$fldPAGE);
							$aBACHECA[]=$aATTIVITA;
							
							$next_record=$db->next_record();
						}
					}	
				}	

			}
		}

		echo $nATTIVITA=count($aBACHECA);
		break;


	case "getbandovalue":
		$pidsso_domanda=get_param("_domanda");
		$fldidutente=get_db_value("select idutente from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$pidsso_tbl_parametro=get_param("_parametro");
		switch($pidsso_tbl_parametro)
		{
			case 1:	// valore isee
				$fldvalore=get_db_value("select valore_isee_famiglia from sso_anagrafica_altro where idsso_anagrafica_utente='$fldidutente'");
				echo $fldvalore;
				break;
			case 2:	// data nascita
				$flddata_nascita=get_db_value("select data_nascita from sso_anagrafica_utente where idutente='$fldidutente'");
				echo invertidata($flddata_nascita,"/","-",2);
				break;
			case 3: // componenti
				$beneficiario=new Beneficiario($fldidutente);
				$fldcodicefiscale=get_db_value("select codicefiscale from sso_anagrafica_utente where idutente='$fldidutente'");
				if ($fldcodicefiscale)
				{
					$pidsso_ente=$beneficiario->idsso_ente;
					if($_SERVER["HTTP_HOST"]=="alghero.sicare.it" || ($_SERVER['HTTP_HOST']=="cartarei.sicare.it" && $pidsso_ente!=2 && $pidsso_ente!=10 && $pidsso_ente!=1))
					{
						$result=get_nucleo($fldcodicefiscale,$pidsso_ente);
						if(is_array($result["dati_componente"]))
							$fldcomponenti=count($result["dati_componente"]);
					}
					else
					{
						//$fldcodice_famiglia=get_db_value("select codice_famiglia from ".DBNAME_A.".gen_anagrafe_popolazione where codice_fiscale='$fldcodicefiscale'");
						$fldcodice_famiglia=$beneficiario->get_codice_famiglia();
						if ($fldcodice_famiglia)
						{
							$fldcomponenti=get_db_value("select count(codice_famiglia) from ".DBNAME_A.".gen_anagrafe_popolazione where codice_famiglia='$fldcodice_famiglia'");
						}
					}
				}
				echo $fldcomponenti;
				break;	
			case 4: // minori
				$beneficiario=new Beneficiario($fldidutente);
				$flddata_protocollo=get_db_value("select data_protocollo from sso_domanda where idsso_domanda='$pidsso_domanda'");
				$fldcodicefiscale=get_db_value("select codicefiscale from sso_anagrafica_utente where idutente='$fldidutente'");
				if ($fldcodicefiscale)
				{
					//$fldcodice_famiglia=get_db_value("select codice_famiglia from ".DBNAME_A.".gen_anagrafe_popolazione where codice_fiscale='$fldcodicefiscale'");
					$fldcodice_famiglia=$beneficiario->get_codice_famiglia();
					if ($fldcodice_famiglia)
					{
						$fldminori=get_db_value("select count(codice_famiglia) from ".DBNAME_A.".gen_anagrafe_popolazione where codice_famiglia='$fldcodice_famiglia' and truncate((datediff(date(now()),data_nascita ) /365 ) , 0)<18");
					}
				}
				echo $fldminori;
				break;	
			case 5: // anziani
				$flddata_protocollo=get_db_value("select data_protocollo from sso_domanda where idsso_domanda='$pidsso_domanda'");
				$fldcodicefiscale=get_db_value("select codicefiscale from sso_anagrafica_utente where idutente='$fldidutente'");
				if ($fldcodicefiscale)
				{
					$fldcodice_famiglia=get_db_value("select codice_famiglia from ".DBNAME_A.".gen_anagrafe_popolazione where codice_fiscale='$fldcodicefiscale'");
					if ($fldcodice_famiglia)
					{
						$fldanziani=get_db_value("select count(codice_famiglia) from ".DBNAME_A.".gen_anagrafe_popolazione where codice_famiglia='$fldcodice_famiglia' and truncate((datediff(date(now()),data_nascita ) /365 ) , 0)>=65");
					}
				}
				echo $fldanziani;
				break;	
			case 10:
				$flddata_protocollo=get_db_value("select data_protocollo from sso_domanda where idsso_domanda='$pidsso_domanda'");
				echo invertidata($flddata_protocollo,"/","-",2);
				break;

			case 13:
				$flddata_nascita=get_db_value("SELECT data_nascita FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
				if(!empty_data($flddata_nascita))
					$age=date_diff(date_create($flddata_nascita), date_create('now'))->y;

				echo $age;
			break;

			case 15:	//IBAN
				$fldidsso_anagrafica_altro=get_db_value("SELECT idsso_anagrafica_altro FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
				if(!empty($fldidsso_anagrafica_altro))
				{
					$fldiban=get_db_value("SELECT iban_pagante FROM sso_anagrafica_altro WHERE idsso_anagrafica_altro='$fldidsso_anagrafica_altro'");
				}
				echo $fldiban;
				break;
		}
		break;	


	case "get_risposta_parametro":
		$pidsso_domanda=get_param("_iddomanda");
		$pidsso_progetto_graduatoria=get_param("_idparametro");

		echo $pidsso_progetto_graduatoria_risposte=get_db_value("SELECT idsso_progetto_graduatoria_risposte FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$pidsso_progetto_graduatoria'");
		
		break;
	case "get_punteggio_risposta":
		$pidsso_progetto_graduatoria_risposte=get_param("_idrisposta");

		echo $punteggio=get_db_value("SELECT punteggio FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$pidsso_progetto_graduatoria_risposte'");
		break;

	case "loadprogetti":
		$fldoggi=date("Y-m-d");
		$db->query("select idsso_progetto,descrizione from ".DBNAME_SS.".sso_progetto where data_scadenza>='$fldoggi' order by descrizione");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_progetto=$db->f("idsso_progetto");
			$flddescrizione=$db->f("descrizione");
						
			$flddescrizione = removeslashes($flddescrizione);
			$flddescrizione=utf8_decode($flddescrizione);

			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$fldidsso_progetto;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break; 



	case "loadoperatori_nominativo":
		$sSql="SELECT * 
		FROM ".DBNAME_A.".utenti ";
		$sWhere='';
		
		$pfiltraente=get_param("_filtraente");	//_filtraente=true filtra l'elenco degli operatori in base agli enti che l'operatore loggato può visualizzare
		if(!empty($pfiltraente))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$sicurezza=new Sicurezza($fldidutente_operatore);
			if($sicurezza->check_operatore_beneficiari())
			{
				echo '[]';
				break;
			}
			else
			{
				$condizione=$sicurezza->get_sql_beneficiari('utenti.idente');
				$sWhere=aggiungi_condizione($sWhere, $condizione);
			}
		}
		
		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder=" order by cognome, nome";
		
		$db->query($sSql.$sWhere.$sOrder);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome=$db->f("cognome");
			$fldnome=$db->f("nome");

			$fldcodicefiscale_data=$fldcodicefiscale;
			
			$fldnominativo=$fldcognome.' '.$fldnome;
			$fldnominativo=removeslashes($fldnominativo);
			$fldnominativo=utf8_decode($fldnominativo);
									
			$record=array();
			$record['value']=$fldnominativo;
			$record['data']=$fldidutente;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	/*
	case "documentocomunicazione":
		$pideso_accreditamento_comunicazione=$_REQUEST["_key"];
		$flddocumento_comunicazione=front_get_db_value("select documento_comunicazione from ".FRONT_ESONAME.".eso_accreditamento_comunicazione where ideso_accreditamento_comunicazione='$pideso_accreditamento_comunicazione'");
		if ($flddocumento_comunicazione)
			echo "documenti/$flddocumento_comunicazione";
		break;	
	*/

		

	case "load_1":
		/*
			Carica tutti i comuni
			- sicare_beneficiario_anagrafica.php
			- esicare_refezione_domanda_trasporto.php
		*/
		$pvalue=get_param("_value");	
		$pswitch=get_param("_switch");	

		$sSelect="SELECT *
		FROM ".DBNAME_A.".comune ";
		
		$sWhere='';
		$sWhere=aggiungi_condizione($sWhere, "comune LIKE '%$pvalue%'");

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
				
		$sSql=$sSelect.$sWhere;
		$db->query($sSql);
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidcomune=$db->f("idcomune");
			$fldcomune=$db->f("comune");
			$fldprovincia=$db->f("provincia");
			$fldcap=$db->f("cap");

			switch($pswitch)
			{
				case 1:
					$value=$fldcomune;
					break;

				default:
					$value=$fldcomune.' ('.$fldprovincia.')';
					break;
			}

			$value=removeslashes($value);
			$value=utf8_decode($value);

			$record=array();
			$record['value']=$value;
			$record['data']=$fldidcomune.'|'.$fldprovincia.'|'.$fldcap;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "caricaISEEmensa":
		$fldidutente=get_param("_idutente");
		$fldanno=get_param("_anno");
		$response=array();
		$anno_1=$fldanno-1;

		$sSQL="SELECT * FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$fldidutente' AND anno='$anno_1'";
		$db->query($sSQL);
		$next_record=$db->next_record();
		
		$fldvalore_isee_1=$db->f("valore_isee");
		if($fldvalore_isee_1==null)
			$fldvalore_isee_1='';
		else
			$fldvalore_isee_1=number_format($fldvalore_isee_1,2,',','');

		$flddata_dichiarazione_1=$db->f("data_dichiarazione");
		$flddata_dichiarazione_1=invertidata($flddata_dichiarazione_1,"/","-",2);

		$fldnumero_dsu_1=$db->f("numero_dsu");
		$flddata_dsu_1=$db->f("data_dsu");
		$flddata_dsu_1=invertidata($flddata_dsu_1,"/","-",2);
		
		$fldflag_isee_non_dichiarato_1=$db->f("isee_non_dichiarato");

		if($fldflag_isee_non_dichiarato_1==1)
			$checked_non_dichiarato_1="checked";

		$anno_2=$fldanno;

		$sSQL="SELECT * FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$fldidutente' AND anno='$anno_2'";
		$db->query($sSQL);
		$next_record=$db->next_record();
		
		$fldvalore_isee_2=$db->f("valore_isee");
		if($fldvalore_isee_2==null)
			$fldvalore_isee_2='';
		else
			$fldvalore_isee_2=number_format($fldvalore_isee_2,2,',','');

		$flddata_dichiarazione_2=$db->f("data_dichiarazione");
		$flddata_dichiarazione_2=invertidata($flddata_dichiarazione_2,"/","-",2);

		$fldnumero_dsu_2=$db->f("numero_dsu");
		$flddata_dsu_2=$db->f("data_dsu");
		$flddata_dsu_2=invertidata($flddata_dsu_2,"/","-",2);
		
		$fldflag_isee_non_dichiarato_2=$db->f("isee_non_dichiarato");

		echo $anno_1.'|'.$fldvalore_isee_1.'|'.$flddata_dichiarazione_1.'|'.$fldnumero_dsu_1.'|'.$flddata_dsu_1.'|'.$fldflag_isee_non_dichiarato_1.';'.$anno_2.'|'.$fldvalore_isee_2.'|'.$flddata_dichiarazione_2.'|'.$fldnumero_dsu_2.'|'.$flddata_dsu_2.'|'.$fldflag_isee_non_dichiarato_2;
		break;

	case "integrazionedomanda":
		$pidsso_domanda=get_param("_domanda");
		$pdescrizione=get_param("_descrizione");

		$user=verifica_utente($chiave);
		$flddata_storico=date("Y-m-d");
		
		$sSQL="SELECT * FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);
		$next_record=$db->next_record();
		
		$fldidsso_tbl_invio_domanda=$db->f("idsso_tbl_invio_domanda");
		
		$fldidutente=$db->f("idutente");		
		$fldidoperatore=$db->f("idoperatore");
		$fldidsso_progetto=$db->f("idsso_progetto");

		$pdescrizione=db_string("Richiesta di integrazione: ".$pdescrizione);
		$pdescrizione=nl2br($pdescrizione);
		if($fldidsso_tbl_invio_domanda==1)	// Back-office
		{
			$fldideso_accreditamento_comunicazione=sicare_invia_comunicazione($user, $fldidoperatore, 0, $pdescrizione, null, null, 0, $pidsso_domanda, 1);

			$fldemail=get_db_value("select email from sso_anagrafica_utente WHERE idutente='$fldidutente'");
			$fldemail="attivazioni@iccs.it";

			$fldinvio="<br>";
						
			$fldoggetto="Nuova comunicazione - $fldnominativo";
			$fldtesto="Gentile $fldnominativo, $fldinvio $fldinvio";
			$fldtesto.=$pdescrizione." $fldinvio $fldinvio";
			$fldtesto.="";
			$fldtesto.="";

			if(!empty($fldemail))
			{
				include("../librerie/mail/lib.mail.php");

				$aEMAIL=array();
				$aEMAIL[0]=$fldemail;
				$aEMAIL[1]=$fldoggetto;
				$aEMAIL[2]=$fldtesto;
				$fldresult=sendMAIL($aEMAIL);
			}

		}
		else 	// Front-office
		{

			$fldemail=get_db_value("select email from sso_anagrafica_utente WHERE idutente='$fldidutente'");
			//$fldemail_pec=get_db_value("select email_pec from sso_anagrafica_utente WHERE idutente='$fldidutente'");
			

			
			$fldidgen_utente=get_idgen_utente($fldidutente);

			$pideso_comunicazione_gruppo_progetto=get_db_value("SELECT comunicazioni_ideso_comunicazione_gruppo FROM sso_progetto WHERE idsso_progetto='$fldidsso_progetto'");

			$fldideso_accreditamento_comunicazione=sicare_invia_comunicazione(0, $fldidgen_utente, $user, $pdescrizione, null, null, 0, $pidsso_domanda, 0, IDPROCEDURA_SICARE, "", "", 0, $pideso_comunicazione_gruppo_progetto);

			$fldinvio="<br>";
			$flddescrizione="La informiamo che e' stata inviata una nuova comunicazione.".$fldinvio."I nuovi messaggi possono essere consultati accedendo alla propria pagina personale e selezionando la voce 'Comunicazioni'.";
			
			$fldoggetto="Nuova comunicazione - $fldnominativo";
			$fldtesto="Gentile $fldnominativo, $fldinvio $fldinvio";
			$fldtesto.="$pdescrizione $fldinvio $fldinvio";
			$fldtesto.="";
			$fldtesto.="";
			if(!empty($fldemail))
			{
				include("../librerie/mail/lib.mail.php");

				$aEMAIL=array();
				$aEMAIL[0]=$fldemail;
				$aEMAIL[1]=$fldoggetto;
				$aEMAIL[2]=$fldtesto;
				$fldresult=sendMAIL($aEMAIL);
			}
		}
		
		$fldprotocollo_ditta=importa_protocollo();
		
		switch($fldprotocollo_ditta)
		{
			case 8:	//	Italsoft
				$italsoft=new Italsoft();

				$response=$italsoft->get_ita_engine_context_token();
				if($response["error_code"]>0)
					$flderror_protocollo=$response["error_desc"].' (error_code '.$response["error_code"].')';
				else
				{
					$fldtoken=$response["token"];

					$response=$italsoft->protocolla_comunicazione($fldideso_accreditamento_comunicazione);
					if($response["error_code"]>0)
						$flderror_protocollo=$response["error_desc"].' (error_code '.$response["error_code"].')';
					else
					{
						$response_protocollazione=$response;
						$fldresult="<b>".$response["response"]["messageResult"]->tipoRisultato."</b>";
						$fldresult.=": ".$response["response"]["messageResult"]->descrizione;

						if(empty($response["response"]["datiProtocollo"]))
							$flderror_protocollo=$fldresult;
						else
						{
							$fldresult_protocollo=$fldresult;
							$data_protocollo=date("Y-m-d");
							$numero_protocollo=$response["response"]["datiProtocollo"]->numeroProtocollo;
							$segnatura_protocollo=$response["response"]["datiProtocollo"]->segnatura;
						}
					}

					$response=$italsoft->DestroyItaEngineContextToken();
					if($response["error_code"]>0)
						$flderror_protocollo=$response["error_desc"].' (error_code '.$response["error_code"].')';
				}
				break;

			default:
				$flderror_protocollo=""; // "Nessun protocollo attivo"
				break;
		}

		if(empty($flderror_protocollo))
		{
			$sSQL="UPDATE eso_accreditamento_comunicazione 
			SET protocollo_comunicazione='$numero_protocollo', 
			data_protocollo_comunicazione='$data_protocollo',
			segnatura_protocollo='$segnatura_protocollo' 
			WHERE ideso_accreditamento_comunicazione='$fldideso_accreditamento_comunicazione'";
			$db_front->query($sSQL);

			//Storicizzo la domanda
			$sSQL="INSERT INTO sso_storico_domanda (idsso_domanda,idsso_ente,idoperatore,idutente,idpresentante,idsso_progetto,motivazione,idsso_tbl_invio_domanda,importo_contributo,numero_protocollo,data_protocollo,ora_protocollo,idsso_tabella_stato_domanda,data_storico,idoperatore_storico) 
			select idsso_domanda,idsso_ente,idoperatore,idutente,idpresentante,idsso_progetto,motivazione,idsso_tbl_invio_domanda,importo_contributo,numero_protocollo,data_protocollo,ora_protocollo,idsso_tabella_stato_domanda,'$flddata_storico','$user' from sso_domanda where sso_domanda.idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);
			$fldidsso_storico_domanda = mysql_insert_id($db->link_id());

			//Storicizzo i requisiti
			$sSQL="INSERT INTO sso_storico_domanda_requisito (idsso_storico_domanda,idsso_domanda,idsso_tbl_servizio_criterio) select '$fldidsso_storico_domanda','$pidsso_domanda',idsso_tbl_servizio_criterio  from sso_domanda_requisito where sso_domanda_requisito.idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);

			//Storicizzo i dati informativi
			$sSQL="INSERT INTO sso_storico_domanda_parametro_graduatoria (idsso_storico_domanda,idsso_domanda,idsso_progetto_graduatoria,idsso_tabella_parametro_graduatoria,numero) select '$fldidsso_storico_domanda','$pidsso_domanda',idsso_progetto_graduatoria,idsso_tabella_parametro_graduatoria,numero from sso_domanda_parametro_graduatoria where sso_domanda_parametro_graduatoria.idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);

			//Storicizzo i documenti
			$sSQL="INSERT INTO sso_storico_domanda_allega (idsso_storico_domanda,idsso_domanda,descrizione,path_file,allegato_name,allegato_ext,idsso_progetto_documento,data) select '$fldidsso_storico_domanda','$pidsso_domanda',descrizione,path_file,allegato_name,allegato_ext,idsso_progetto_documento,data from sso_domanda_allega where sso_domanda_allega.idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);

			//Riporto la domanda con lo stato da inviare
			//$sSQL="update sso_domanda set idsso_tabella_stato_domanda=0,numero_protocollo=NULL,data_protocollo=NULL,ora_protocollo=NULL where idsso_domanda='$pidsso_domanda'";
			//$db->query($sSQL);
			$sSQL="update sso_domanda set idsso_tabella_stato_domanda=7 where idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);
		}
		else
			echo $flderror_protocollo;

		break;	



	case "incorsodomanda":
		$pidsso_domanda=get_param("_domanda");

		$fldidsso_tabella_stato_domanda=get_db_value("SELECT idsso_tabella_stato_domanda FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");
		switch($fldidsso_tabella_stato_domanda)
		{
			case null:
			case '0':
				// In Corso
				$sSQL="UPDATE sso_domanda SET 
				idsso_tabella_stato_domanda='2' 
				where idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
				break;

			default:
				break;
		}
		break;


	case "incorsodomanda_massivo":
		$sDomande=get_param("_id");
		$array_domande=explode("|",$sDomande);

		foreach($array_domande as $pidsso_domanda)
		{
			$fldidsso_tabella_stato_domanda=get_db_value("SELECT idsso_tabella_stato_domanda FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");
			switch($fldidsso_tabella_stato_domanda)
			{
				case null:
				case '0':
					// In Corso
					$sSQL="UPDATE sso_domanda SET 
					idsso_tabella_stato_domanda='2' 
					where idsso_domanda='$pidsso_domanda'";
					$db->query($sSQL);
					break;

				default:
					break;
			}
		}
		
		break;

	case "sospendidomanda":
		$pidsso_domanda=get_param("_domanda");
		$sSQL="update sso_domanda set idsso_tabella_stato_domanda=3 where idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);
		break;



	case "attivadomanda":
		$pidsso_domanda=get_param("_domanda");
		$sSQL="update sso_domanda set idsso_tabella_stato_domanda=4 where idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);
		break;	



	case "loadbudget":
		$user=verifica_utente($chiave);
		$query="select idsso_piano_zona,concat_ws(' - ',anno,descrizione) as area_finanziamento from sso_piano_zona "; 
		$mySicurezza=new Sicurezza($user);
		$swhereENTE=$mySicurezza->get_sql_beneficiari('idsso_comune');
		if ($swhereENTE)
			$query=$query." where ".$swhereENTE;
		$query.=" order by anno desc,descrizione asc";
		$db->query($query);
							
		$res = $db->next_record();
		$response=array();
		while($res)
		{
			$idsso_piano_zona=$db->f('idsso_piano_zona');
			$flddescrizione=$db->f('area_finanziamento');
			
			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$idsso_piano_zona;
			array_push($response, $record);

			$res = $db->next_record();
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);		
		break;	

	case "loadbudgetfront":
		$query="select idsso_piano_zona,concat_ws(' - ',anno,descrizione) as area_finanziamento from sso_piano_zona "; 
		$query.=" order by anno desc,descrizione asc";
		$db->query($query);
							
		$res = $db->next_record();
		$response=array();
		while($res)
		{
			$idsso_piano_zona=$db->f('idsso_piano_zona');
			$flddescrizione=$db->f('area_finanziamento');
			
			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$idsso_piano_zona;
			array_push($response, $record);

			$res = $db->next_record();
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);		
		break;


	case "loadmodelli":
		$user=verifica_utente($chiave);
	
		$query="SELECT * FROM ".DBNAME_A.".gen_tbl_testo WHERE flag_eliminato='0' ORDER BY descrizione"; 
		$db->query($query);
							
		$res = $db->next_record();
		$response=array();
		while($res)
		{
			$idgen_tbl_testo=$db->f('idgen_tbl_testo');
			$flddescrizione=$db->f('descrizione');
			
			$record=array();
			$record['value']=$flddescrizione;
			$record['data']=$idgen_tbl_testo;
			array_push($response, $record);

			$res = $db->next_record();
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);				
		break;	



	case "esegui_pianificata":
		$user=verifica_utente($chiave);
		$pidsso_prestazione_pianificata=get_param("_idsso_prestazione_pianificata");
			
		$prestazione_pianificata=new Prestazione_pianificata($pidsso_prestazione_pianificata);

		$sSql="SELECT * 
		FROM sso_prestazione_pianificata
		WHERE idsso_prestazione_pianificata='$pidsso_prestazione_pianificata'";
		
		$db->query($sSql);
		$res=$db->next_record();
		$fldidsso_domanda_intervento=$db->f("idsso_domanda_intervento");
		$pora_inizio_effettuata=$db->f("orario_inizio");
		$pora_fine_effettuata=$db->f("orario_fine");
		$pdata_effettuata=$db->f("data_pianificata");
		$fldidsso_anagrafica_utente=$db->f("idsso_ente_assistenza");
		$pidsso_anagrafica_operatore=$db->f("idgen_operatore");
		$pidsso_anagrafica_operatore2=$db->f("idgen_operatore2");
		$pidsso_anagrafica_operatore3=$db->f("idgen_operatore3");
		
		$pai=new Pai($fldidsso_domanda_intervento);

		$fldtipologia_um=($pai->idsso_tbl_um != UM_BUDGET) ? get_tipo_um($pai->idsso_tbl_um) : get_tipo_um_catalogo_fornitore($prestazione_pianificata->idsso_ente_assistenza, $prestazione_pianificata->idsso_tbl_prestazione, $prestazione_pianificata->data_pianificata);
		switch($fldtipologia_um)
		{					
			case TIPO_UM_UNITA:
			case TIPO_UM_IMPORTO:
				$quantita=1;
				$minuti=0;
				break;
				
			case TIPO_UM_MINUTI:	
				$quantita=quantita_ore($pora_inizio_effettuata, $pora_fine_effettuata);
				$minuti=elabora_minuti($pora_inizio_effettuata, $pora_fine_effettuata);
				break;

			default:
				$quantita=0;
				$minuti=0;
				break;
		}

		if(!empty($quantita))
		{
			$flddisponibili=get_quantita_disponibile($pdata_effettuata, $pai);
			
			if($quantita<=$flddisponibili)
			{
				$fldquota_variabile=$pai->get_tariffa_rendicontazione($prestazione_pianificata->data_pianificata);
					
				$fldtipologia_um=($pai->idsso_tbl_um != UM_BUDGET) ? get_tipo_um($pai->idsso_tbl_um) : get_tipo_um_catalogo_fornitore($prestazione_pianificata->idsso_ente_assistenza, $prestazione_pianificata->idsso_tbl_prestazione, $prestazione_pianificata->data_pianificata);
				switch($fldtipologia_um)
				{					
					case TIPO_UM_UNITA:
					case TIPO_UM_IMPORTO:
						$importo_prestazione=$fldquota_variabile*$quantita;
						break;
						
					case TIPO_UM_MINUTI:								
						$importo_prestazione=$fldquota_variabile*$minuti/60;
						$importo_prestazione=round($importo_prestazione, getDECIMALI());
						break;
				}

				if($pidsso_anagrafica_operatore==$pidsso_anagrafica_operatore2)
					$pidsso_anagrafica_operatore2='';
				
				if($pidsso_anagrafica_operatore==$pidsso_anagrafica_operatore3)
					$pidsso_anagrafica_operatore3='';
				
				if($pidsso_anagrafica_operatore2==$pidsso_anagrafica_operatore3)
					$pidsso_anagrafica_operatore3='';
				

				$qryInsert="INSERT INTO sso_prestazione_fatta_dettaglio
				(idsso_prestazione_fatta,
				idsso_prestazione_pianificata,
				idutente, 
				data_prestazione, 
				idsso_ente_assistenza,
				idgen_operatore,
				idgen_operatore2,
				idgen_operatore3,
				idsso_tbl_prestazione,
				orario_inizio,
				orario_fine,
				quantita,
				prestazione_minuti,
				tariffa_prestazione,
				importo_prestazione,
				note,
				flag_mobile)
				VALUES
				('0', 
				'$pidsso_prestazione_pianificata',
				'$pai->idutente', 
				'$pdata_effettuata', 
				'$fldidsso_anagrafica_utente', 
				'$pidsso_anagrafica_operatore', 
				'$pidsso_anagrafica_operatore2', 
				'$pidsso_anagrafica_operatore3', 
				'$pai->idsso_tbl_prestazione', 
				'$pora_inizio_effettuata', 
				'$pora_fine_effettuata', 
				'$quantita', 
				'$minuti', 
				'$fldquota_variabile',
				'$importo_prestazione', 
				'$pnote',
				'0')";
				$db->query($qryInsert);
				$pidsso_prestazione_fatta_dettaglio = mysql_insert_id($db->link_id());	
			
				echo "1|0";	// Prestazione inserita correttamente
			}
			else
				echo "2|".formatta_quantita($flddisponibili, null, $pai->get_tipologia_um(null, null)).' '.get_descrizione_um($pai->idsso_tbl_um);	// Impossibile inserire prestazione: quantita non disponibile
		}
		else
			echo "3|0"; 	// Errore

		break;	

//sntnda70t61z133b

	case "calcola_cf":
		$pcognome=get_param("cognome");
		$pnome=get_param("nome");
		$psesso=get_param("sesso");
		$pidgen_comune_nascita=get_param("idgen_comune_nascita");
		$pidamb_nazione=get_param("idamb_nazione");
		$pdata_nascita=get_param("data_nascita");
		
		$cf=new codicefiscale();
		$codicefiscale=$cf->calcola($pnome, $pcognome, $pdata_nascita, $psesso, $pidgen_comune_nascita, $pidamb_nazione);

		if ($cf->hasError()) 
			echo  "0|ERRORE: ".$cf->getError();
		else
			echo "1|".$codicefiscale;

		//echo "$pdata_nascita - $pgiorno_nascita $pmese_nascita $panno_nascita";
		break;	



	case "bandoscaduto":
		$pidsso_progetto=get_param("_avviso");
		$fldoggi=date("Y-m-d");
		$flddata_scadenza=get_db_value("select data_scadenza from sso_progetto where idsso_progetto='$pidsso_progetto'");
		if ($fldoggi>$flddata_scadenza)
			$fldscaduto=true;
		else
			$fldscaduto=false;
		echo $fldscaduto;
		break;



	case "checkpin":
		$fldidgen_operatore=verifica_utente($chiave);
		$ppin=get_param("_pin");
		$fldpin_utente=get_db_value("select pin_firma_debole from ".DBNAME_A.".utenti where idutente='$fldidgen_operatore'");
		if ($ppin && $fldpin_utente && $fldpin_utente==$ppin)
			echo true;
		else
			echo false;
		break;



	case "checkverifica":
		//$fldidgen_operatore=verifica_utente($chiave);
		$pnumero_verifica=get_param("_verifica");
		$sSQL="select idsso_tbl_area,data_inizio,data_fine from sso_fattura where numero='$pnumero_verifica' and tipo_fattura=2";
		$db->query($sSQL);
		if ($db->next_record())
		{
			$fldidsso_tbl_area=$db->f("idsso_tbl_area");
			$flddata_inizio=$db->f("data_inizio");
			$flddata_fine=$db->f("data_fine");

			echo $fldidsso_tbl_area."|".invertidata($flddata_inizio,"/","-",2)."|".invertidata($flddata_fine,"/","-",2);
		}
		break;	



	case "calcolapeg":
		$ptipo=get_param("_tipo");
		$pidsso_piano_zona=get_param("_piano");
		switch($ptipo)
		{
			case 1: // risorse_ambito
				$sSQL="SELECT sum(risorse_ambito) as importo 
				FROM sso_piano_zona_ente 
				WHERE idsso_piano_zona='$pidsso_piano_zona' ";
				/*
				$sSQL="SELECT sum(stanziamento) as importo 
				FROM sso_piano_peg 
				WHERE idsso_piano_zona='$pidsso_piano_zona' 
				AND flag_entrata_uscita=1 ";
				*/
				break;
			case 2: // risorse_comune
				$sSQL="SELECT sum(risorse_comune) as importo 
				FROM sso_piano_zona_ente 
				WHERE idsso_piano_zona='$pidsso_piano_zona' ";
				break;
			case 3: // spese_fisse
				$sSQL="SELECT sum(spese_fisse) as importo 
				FROM sso_piano_zona_ente 
				WHERE idsso_piano_zona='$pidsso_piano_zona'";
				/*
				$sSQL="SELECT sum(stanziamento) as importo 
				FROM sso_piano_peg 
				WHERE idsso_piano_zona='$pidsso_piano_zona' 
				AND flag_entrata_uscita=2 ";
				*/
				break;
		}
		$fldimporto=get_db_value($sSQL);
		$fldimporto=number_format($fldimporto,2,',','');
		echo $fldimporto;
		break;	



	case "savepegente":
		$pidsso_piano_zona=get_param("_piano");
		$pidente=get_param("_ente");
		$fldrisorse_comune=get_param("_comune");
		$fldrisorse_ambito=get_param("_ambito");
		$fldspese_fisse=get_param("_spese");	

		$fldrisorse_comune=str_replace(",",".",$fldrisorse_comune);
		$fldrisorse_ambito=str_replace(",",".",$fldrisorse_ambito);
		$fldspese_fisse=str_replace(",",".",$fldspese_fisse);
		
		$fldrisorse_assegnate=$fldrisorse_comune+$fldrisorse_ambito-$fldspese_fisse;
		
		//Verifico se esiste il record
		$fldidsso_piano_zona_ente=get_db_value("select idsso_piano_zona_ente from sso_piano_zona_ente where idsso_ente='$pidente' and idsso_piano_zona='$pidsso_piano_zona'");
		if ($fldidsso_piano_zona_ente>0)
		{
			$sSQL="UPDATE sso_piano_zona_ente 
			SET risorse_comune='$fldrisorse_comune',
			risorse_ambito='$fldrisorse_ambito',
			spese_fisse='$fldspese_fisse',
			risorse_assegnate='$fldrisorse_assegnate' 
			where idsso_piano_zona_ente='$fldidsso_piano_zona_ente'";			
		}
		else
		{
			$sSQL="INSERT INTO sso_piano_zona_ente 
			(idsso_piano_zona,idsso_ente,risorse_comune,
			risorse_ambito,spese_fisse,risorse_assegnate) 
			values 
			('$pidsso_piano_zona','$pidente','$fldrisorse_comune',
			'$fldrisorse_ambito','$fldspese_fisse','$fldrisorse_assegnate')";			
		}

		$db->query($sSQL);
		break;	



	case "savepegente_reis":
		$pidsso_piano_zona=get_param("_piano");
		$pidente=get_param("_ente");
		$fldrisorse_comune=get_param("_comune");
		$fldrisorse_ambito=get_param("_ambito");
		$fldspese_fisse=get_param("_spese");	
		$pidsso_progetto=get_param("_idprogetto");

		$fldrisorse_comune=str_replace(",",".",$fldrisorse_comune);
		$fldrisorse_ambito=str_replace(",",".",$fldrisorse_ambito);
		$fldspese_fisse=str_replace(",",".",$fldspese_fisse);
		
		$fldrisorse_assegnate=$fldrisorse_comune+$fldrisorse_ambito+$fldspese_fisse;
		
		//Verifico se esiste il record
		$fldidsso_piano_zona_ente=get_db_value("select idsso_piano_zona_ente from sso_piano_zona_ente where idsso_ente='$pidente' and idsso_piano_zona='$pidsso_piano_zona'");
		if ($fldidsso_piano_zona_ente>0)
		{
			$sSQL="UPDATE sso_piano_zona_ente 
			SET risorse_comune='$fldrisorse_comune',
			risorse_ambito='$fldrisorse_ambito',
			spese_fisse='$fldspese_fisse',
			risorse_assegnate='$fldrisorse_assegnate' 
			where idsso_piano_zona_ente='$fldidsso_piano_zona_ente'";			
		}
		else
		{
			$sSQL="INSERT INTO sso_piano_zona_ente 
			(idsso_piano_zona,idsso_ente,risorse_comune,
			risorse_ambito,spese_fisse,risorse_assegnate) 
			values 
			('$pidsso_piano_zona','$pidente','$fldrisorse_comune',
			'$fldrisorse_ambito','$fldspese_fisse','$fldrisorse_assegnate')";			
		}

		$db->query($sSQL);
		break;	



	case "notificadomanda":
		$pidsso_domanda=get_param("_domanda");
		$fldidoperatore=verifica_utente($chiave);
		$flddata=date("Y-m-d");
		$sSQL="UPDATE sso_domanda SET 
		data_invio='$flddata',idgen_operatore_invio='$fldidoperatore' 
		WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);
		break;
	case "statodomanda":
		$pidsso_domanda=get_param("_domanda");
		$fldidoperatore=verifica_utente($chiave);
		$pstato_domanda=get_param("_stato");
		$sSQL="UPDATE sso_domanda 
		SET idsso_tabella_stato_domanda='$pstato_domanda' 
		WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);

		//Verifico se esiste l'intervento
		
		if ($_SERVER['HTTP_HOST']=="familycard.comune.messina.it" && $pidsso_domanda>0)
		{
			$sSQL="select idsso_domanda_intervento,idutente from sso_domanda_intervento where idsso_domanda='$pidsso_domanda'";
			$aINTERVENTI=db_fill_array($sSQL);
			foreach ($aINTERVENTI as $idsso_domanda_intervento => $idutente) 
			{

				//Verifico che non siano stati fatti scarichi
				$idsso_prestazione_fatta_dettaglio=get_db_value("select idsso_prestazione_fatta_dettaglio from sso_prestazione_fatta_dettaglio inner join sso_domanda_prestazione_voucher sso_prestazione_fatta_dettaglio.idsso_domanda_prestazione_voucher=sso_domanda_prestazione_voucher.idsso_domanda_prestazione_voucher inner join sso_domanda_intervento on sso_domanda_prestazione_voucher.idsso_domanda_intervento=sso_domanda_intervento.idsso_domanda_intervento where sso_domanda_intervento.idsso_domanda_intervento='$idsso_domanda_intervento'  ");

				if (empty($idsso_prestazione_fatta_dettaglio))
				{
					$sSQL = "DELETE FROM ".DBNAME_SS.".sso_domanda_prestazione WHERE idsso_domanda_intervento='$idsso_domanda_intervento'";
					$db->query($sSQL);

					$sSQL = "DELETE FROM ".DBNAME_SS.".sso_domanda_intervento WHERE idsso_domanda_intervento='$idsso_domanda_intervento'";
					$db->query($sSQL);

					$sSQL = "DELETE FROM ".DBNAME_SS.".sso_domanda_prestazione_voucher WHERE idsso_domanda_intervento='$idsso_domanda_intervento'";
					$db->query($sSQL);					
				}
			}
		}	
		

		break;
	case "operatoredomanda":
		$pidsso_domanda=get_param("_domanda");
		$pidoperatore=get_param("_operatore");
		$sSQL="UPDATE sso_domanda SET idoperatore='$pidoperatore' WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);

		break;
	case "loadsottoservizi":
		$db->query("select * from ".DBNAME_SS.".sso_tbl_prestazione_sottoservizio order by nome");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldidsso_tbl_prestazione_sottoservizio=$db->f("idsso_tbl_prestazione_sottoservizio");
			$fldnome=$db->f("nome");
						
			$fldnome=removeslashes($fldnome);
			$fldnome=utf8_decode($fldnome);
			
			$record=array();
			$record['value']=$fldnome;
			$record['data']=$fldidsso_tbl_prestazione_sottoservizio;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		echo json_encode($response);
		break;

	

	case "assegnasottoservizio":

		$fldidsso_tbl_prestazione=get_param("_idprestazione");
		$fldidsso_tbl_prestazione_sottoservizio=get_param("_idsottoservizio");
		$inserisci=get_param("_inserisci");

		if($inserisci==1)
		{
			$sSQL="INSERT INTO sso_tbl_prestazione_diario(idsso_tbl_prestazione,idsso_tbl_prestazione_sottoservizio) VALUES('$fldidsso_tbl_prestazione','$fldidsso_tbl_prestazione_sottoservizio')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="DELETE FROM sso_tbl_prestazione_diario WHERE idsso_tbl_prestazione_sottoservizio='$fldidsso_tbl_prestazione_sottoservizio' AND idsso_tbl_prestazione='$fldidsso_tbl_prestazione'";
			$db->query($sSQL);
		}

		echo "ok";

		break;

	case "assegnasottoservizio_pai":

		$pidsso_domanda_intervento=get_param("_id");
		$pidsso_tbl_prestazione_sottoservizio=get_param("_idsottoservizio");
		$inserisci=get_param("_inserisci");

		if($inserisci==1)
		{
			$sSQL="INSERT INTO sso_domanda_sottoservizi(idsso_domanda_intervento,idsso_tbl_prestazione_sottoservizio) VALUES('$pidsso_domanda_intervento','$pidsso_tbl_prestazione_sottoservizio')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="DELETE FROM sso_domanda_sottoservizi WHERE idsso_domanda_intervento='$pidsso_domanda_intervento' AND idsso_tbl_prestazione_sottoservizio='$pidsso_tbl_prestazione_sottoservizio'";
			$db->query($sSQL);
		}

		echo "ok";

		break;

	case "loadtable_diario":
		$id_prestazione=get_param("_idprestazione");
		$id_prestazione_fatta_dettaglio=get_param("_idprestazione_fattadettaglio");
		$table_head= '<center><table data-toggle="table" class="table table-hover" >
		<thead>
		  <tr class="default">
			  <th style="width: 5%;" class="text-info"></th>
			  <th style="width: 60%" class="text-info"><b>Sottoservizio</b></th>
			  <th style="width: 15%" class="text-info"><b>Minuti</b></th>
		  </tr>  
	  </thead>
	  <tbody> ';
		$table="";
		$sSQL="SELECT * 
		FROM sso_tbl_prestazione_diario
		INNER JOIN sso_tbl_prestazione_sottoservizio ON sso_tbl_prestazione_sottoservizio.idsso_tbl_prestazione_sottoservizio=sso_tbl_prestazione_diario.idsso_tbl_prestazione_sottoservizio
		WHERE sso_tbl_prestazione_diario.idsso_tbl_prestazione='$id_prestazione'";
		$db->query($sSQL);
		$next_record=$db->next_record();
		$counter=1;
		$array_id=array();
		while($next_record)
		{
			$fldidsso_tbl_prestazione_diario=$db->f("idsso_tbl_prestazione_diario");			
			$fldnome=$db->f("nome");

			$minuti=get_db_value("SELECT minuti FROM sso_prestazione_fatta_diario WHERE idsso_prestazione_fatta_dettaglio='$id_prestazione_fatta_dettaglio' AND idsso_tbl_prestazione_diario='$fldidsso_tbl_prestazione_diario'");
/*
			if(in_array($fldidsso_tbl_prestazione_diario,$array_sottoservizi_diario))
				$checked="checked";
			else
				$checked="";
*/				
			$array_id[$counter]=$fldidsso_tbl_prestazione_diario;

			$table_now='<tr>
						<th style="color:black">'.$counter.'</th>
						<th style="color:black">'.$fldnome.'</th>
						<th><input name="sottoservizio_'.$fldidsso_tbl_prestazione_diario.'" id="sottoservizio_'.$fldidsso_tbl_prestazione_diario.'" type="text" class="form-control input-xs" value="'.$minuti.'"></th>
						</tr>';

			$counter=$counter+1;

			$table=$table.$table_now;

			$next_record=$db->next_record();			
		}
		
		$table_diario=$table_head.$table."</tbody></table></center>";
		$stringa_id = implode(",", $array_id);
		echo $table_diario."|".$stringa_id;
		break;

		

	case "aggiorna_fattadiario":
		$id_prestazione=get_param("_idprestazione");
		$idsso_tbl_prestazione_diario=get_param("_prestazionediario");
		//$idsso_tbl_prestazione_diario=get_db_value("SELECT idsso_tbl_prestazione_diario FROM sso_tbl_prestazione_diario WHERE idsso_tbl_prestazione='$id_prestazione' AND idsso_tbl_prestazione_sottoservizio='$id_sottoservizio'");
		
		$id_fatta_dettaglio=get_param("_idfattadettaglio");
		$minuti=get_param("_minuti");
		
		$exist=get_db_value("SELECT idsso_prestazione_fatta_diario FROM sso_prestazione_fatta_diario WHERE idsso_tbl_prestazione_diario='$idsso_tbl_prestazione_diario' AND idsso_prestazione_fatta_dettaglio='$id_fatta_dettaglio'");
		if(empty($exist))
		{
			$sSQL="INSERT INTO sso_prestazione_fatta_diario(idsso_prestazione_fatta_dettaglio,idsso_tbl_prestazione_diario,minuti) VALUES('$id_fatta_dettaglio','$idsso_tbl_prestazione_diario','$minuti')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="UPDATE sso_prestazione_fatta_diario SET minuti='$minuti' WHERE idsso_prestazione_fatta_diario='$exist' AND idsso_tbl_prestazione_diario='$idsso_tbl_prestazione_diario'";
			$db->query($sSQL);
		}
		break;
	case "inviamail_equipe":	// sicare_sia_equipe_convoca.php

		include("../librerie/mail/class.phpmailer.php");
		include("../librerie/mail/lib.mail.php");
		include('../librerie/html2pdf.php');

		$pidsso_domanda_seduta=get_param("idsso_domanda_seduta");
		$pidgen_utente=get_param("idgen_utente");
		if (strpos($pidgen_utente,'a')>0)
		{
			$fldidsso_anagrafica_utente=str_replace('a','',$pidgen_utente);
			$fldidgen_utente=0;
			$fldemail=get_db_value("SELECT email FROM ".DBNAME_SS.".sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");
			$fldcognome=get_db_value("SELECT cognome FROM ".DBNAME_SS.".sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");
			$fldnome=get_db_value("SELECT nome FROM ".DBNAME_SS.".sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");			
		}
		else
		{
			$fldemail=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$pidgen_utente'");
			$fldcognome=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$pidgen_utente'");
			$fldnome=get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$pidgen_utente'");			
		}

		$psend_preassessment=get_param("_sendpreassessment");
		$psend_sintesi=get_param("_sendsintesi");
		
		$fldidsso_domanda=get_db_value("SELECT idsso_domanda FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda_seduta='$pidsso_domanda_seduta'");
		$fldidutente=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_domanda WHERE idsso_domanda='$fldidsso_domanda'");
		$flddata_seduta=get_db_value("SELECT data_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldora_seduta=get_db_value("SELECT ora_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldluogo_seduta=get_db_value("SELECT luogo_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
	
		$fldcognome_beneficiario=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
		$fldnome_beneficiario=get_db_value("SELECT nome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");

		$flddata_seduta=invertidata($flddata_seduta,"/","-",2);
		$fldora_seduta=substr($fldora_seduta, 0, 5); 


		$poggetto=get_param("oggetto");
		$poggetto=base64_decode($poggetto);

		$ptesto=get_param("testo");
		$ptesto=base64_decode($ptesto);

		$ptesto=str_replace("{Nome}",$fldnome,$ptesto);
		$ptesto=str_replace("{Cognome}",$fldcognome,$ptesto);
		$ptesto=str_replace("{data}",$flddata_seduta,$ptesto);
		$ptesto=str_replace("{ore}",$fldora_seduta,$ptesto);
		$ptesto=str_replace("{luogo}",$fldluogo_seduta,$ptesto);
		$ptesto=str_replace("{cognome_beneficiario}",$fldcognome_beneficiario,$ptesto);

		$ptesto=nl2br($ptesto);
		$fldresult="";
		if(empty($psend_preassessment) && empty($psend_sintesi))
		{
			if($_SERVER["HTTP_HOST"]=="37.206.216.84" || $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it")
			{
				$request_rest = curl_init();
				curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_mail_mc_equipe.php');

				$params=array();
				$params['email']=$fldemail;
				$params['testo']=$ptesto;
				$params['oggetto']=$poggetto;

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
			elseif ($fldemail)
			{
				$aEMAIL=array();
				$aEMAIL[0]=$fldemail;
				$aEMAIL[1]=$poggetto;
				$aEMAIL[2]=$ptesto;
				$aEMAIL[3]="";
				$fldresult=sendMAIL($aEMAIL);
			}

			if($fldresult=="Messaggio inviato correttamente.")
			{
				$oggi=date("Y-m-d");
				$ora=date("H:i:s");

				if ($pidgen_utente>0)
				{
					$sSQL="UPDATE sso_domanda_equipe 
					SET data_convocazione='$oggi',
					ora_convocazione='$ora'
					WHERE idsso_domanda_seduta='$pidsso_domanda_seduta' 
					AND idgen_utente='$pidgen_utente'";					
				}
				else
				{
					$sSQL="UPDATE sso_domanda_equipe 
					SET data_convocazione='$oggi',
					ora_convocazione='$ora'
					WHERE idsso_domanda_seduta='$pidsso_domanda_seduta' 
					AND idsso_anagrafica_utente='$fldidsso_anagrafica_utente'";						
				}

				$db->query($sSQL);

				$result='mail inviata';
			}
			else
			{
				$result='mail non inviata';
			}

			echo $result;
		}
		else
		{
			if($psend_preassessment==1)
			{
				$content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">

					<body>
						<center><h3>SCHEDA PRE-ASSESSMENT - '.$fldcognome_beneficiario.' '.$fldnome_beneficiario.'</h3></center><br><br><br>';

				$sSql="SELECT * FROM sso_tbl_sia_categoria where flag_servizio=0 ORDER BY categoria_ordine,idsso_tbl_sia_categoria ";	
				$db->query($sSql);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_sia_categoria=$db->f("idsso_tbl_sia_categoria");
					$flddescrizione_categoria=$db->f("descrizione");

					$aINFORMAZIONI=db_fill_array("select idsso_tbl_sia_valore,descrizione from sso_tbl_sia_valore where idsso_tbl_sia_categoria='$fldidsso_tbl_sia_categoria' order by valore_ordine");
					if (is_array($aINFORMAZIONI))
					{
						$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';

						reset($aINFORMAZIONI);
						while(list($fldidsso_tbl_sia_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
						{
							$fldidsso_anagrafica_sia_valore=get_db_value("select idsso_anagrafica_sia from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
							$fldidgen_tbl_dizionario_tipocampo=get_db_value("select idgen_tbl_dizionario_tipocampo from sso_tbl_sia_valore where idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");

							if (empty($fldidsso_anagrafica_sia_valore))
								$fldcheckINFORMAZIONE="[  ]";
							else
								$fldcheckINFORMAZIONE="[X]";		    	

							if (empty($fldidgen_tbl_dizionario_tipocampo))
								$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione.'<br>';  						
							else
							{
								$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione;

								switch($fldidgen_tbl_dizionario_tipocampo)	  
								{
									case 1:
										$fldsia_valore=get_db_value("select sia_valore from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
										if(!empty($fldsia_valore))
											$content=$content.' - '.$fldsia_valore.'<br>';
										else
											$content=$content.'<br>';

										break;

									case 2:
										$fldsia_valore=get_db_value("select sia_valore from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
										$fldvalore=get_db_value("select valore_funzione from sso_tbl_sia_valore where idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");
										$LOV = explode(";", $fldvalore);
										if(sizeof($LOV)%2 != 0) 
										  $array_length = sizeof($LOV) - 1;
										else
										  $array_length = sizeof($LOV);
										reset($LOV);
										for($i = 0; $i < $array_length; $i = $i + 2)
										{
											if ($LOV[$i]==$fldsia_valore)
											{
												if(!empty($LOV[$i]))
													$content=$content.' - '.$LOV[$i+1];
											}
										}    

										$content=$content.'<br>';

										break;	
								}
							}

						}

						$content=$content.'<br>';	  									

					}

					$res=$db->next_record();
				}

				$content=$content.'<br><br><h3>SERVIZI PROPOSTI</h3><br><br>';

				$sSql="SELECT * FROM sso_tbl_sia_categoria where flag_servizio=1 ORDER BY categoria_ordine ";	
				$db->query($sSql);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_sia_categoria=$db->f("idsso_tbl_sia_categoria");
					$flddescrizione_categoria=$db->f("descrizione");	

					$aINFORMAZIONI=db_fill_array("select idsso_tbl_sia_valore,descrizione from sso_tbl_sia_valore where idsso_tbl_sia_categoria='$fldidsso_tbl_sia_categoria' order by valore_ordine");
					if (is_array($aINFORMAZIONI))
					{
						$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';

						reset($aINFORMAZIONI);
						while(list($fldidsso_tbl_sia_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
						{
							$fldidsso_anagrafica_sia_valore=get_db_value("select idsso_anagrafica_sia from sso_anagrafica_sia where idsso_anagrafica_utente='$pidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
							if (empty($fldidsso_anagrafica_sia_valore))
								$fldcheckINFORMAZIONE="[  ]";
							else
								$fldcheckINFORMAZIONE="[X]";			    	

							$fldcodice_valore=get_db_value("select codice_valore from sso_tbl_sia_valore where idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");
								$content=$content.$fldcheckINFORMAZIONE.'  '.$fldcodice_valore.' - '.$flddescrizione_informazione.'<br>';  						
						}
						$content=$content.'<br>';	
					}

					$res=$db->next_record();
				}

				$content=$content."</body></html>";

				$filename="../sicare/documenti/allegato_preassessment_".$fldcognome_beneficiario.' '.$fldnome_beneficiario.".pdf";
				
				if(file_exists ($filename))
					unlink($filename);

				$pdf=new PDF_HTML();
				$pdf->SetFont('Arial','',12);
				$pdf->AddPage();
				$pdf->WriteHTML($content);
				$pdf->Output($filename);
			}

			$content="";

			if($psend_sintesi==1)
			{
				$content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<body>
						<center><h3>SINTESI CARTELLA SOCIALE - '.$fldcognome_beneficiario.' '.$fldnome_beneficiario.'</h3></center><br><br><br>';

				$sSQL="SELECT reddito_familiare,
			  valore_isee_famiglia,
			  patrimonio_immobili,
			  importo_pensione,
			  flag_immobili,
			  flag_pensione,
			  flag_invalidita,
			  idsso_tbl_invalidita,
			  idsso_tbl_sordita,
			  idsso_tbl_cecita,
			  idsso_tbl_talassemia,
			  idsso_tabella_stato_abitativo,
			  idsso_tabella_abitazione,
			  flag_coabitazione,
			  coabitante,
			  condizione_abitativa_nucleo,
			  condizione_abitativa_igiene,
			  idsso_tabella_tipo_occupazione,
			  altra_occupazione,
			  flag_svantaggiata,
			  periodo_svantaggiata,
			  flag_collocamento,
			  periodo_collocamento,
			  idsso_tabella_grado_istruzione,
			  culturaa_altro,
			  titolo_studio,
			  qualifica_professionale,
			  scuola_frequentata,
			  idsso_tbl_scuola_frequenza,
			  idsso_tbl_frequenza_motivo,
			  frequenzam_altro,
			  flag_isostegno,
			  flag_antisociale,
			  flag_relazionepari,
			  relazionepari_altro,
			  flag_relazioneins,
			  relazioneins_altro,
			  idsso_tbl_sanitaria,
			  idsso_tbl_disabilita,
			  flag_dipendenze,
			  dipendenza_altro,
			  idsso_tbl_dipendenza,
			  dipendenza_territorio,
			  idsso_tbl_retefamiliare,
			  idsso_tbl_reteinformale,
			  flag_risorseistituzionali,
			  flag_integrazionesociale,
			  integrazionesociale_altro,
			  flag_integrazioneculturale,
			  integrazioneculturale_altro,
			  flag_giudiziaria,
			  idsso_tbl_giudiziaria,
			  idsso_tbl_detenzione,
			  durata_giudiziaria,
			  giudiziaria_territorio,
			  idsso_tbl_composizionefamiglia,
			  idsso_tbl_composizionefamiglia_dettaglio,
			  flag_documentosoggiorno,
			  data_scadenza,
			  idsso_tabella_condizione_soggiorno,
			  flag_nomade
			FROM sso_anagrafica_altro 
			WHERE idsso_anagrafica_utente='$fldidutente'";

				$db->query($sSQL);
				$next_record=$db->next_record();
				if ($next_record)
				{
					$fldreddito_familiare=$db->f("reddito_familiare");
					$fldvalore_isee_famiglia=$db->f("valore_isee_famiglia");
					$fldpatrimonio_immobili=$db->f("patrimonio_immobili");
					$fldimporto_pensione=$db->f("importo_pensione");
					$fldflag_immobili=$db->f("flag_immobili");
					$fldflag_pensione=$db->f("flag_pensione");
					$fldflag_invalidita=$db->f("flag_invalidita");
					$fldidsso_tbl_invalidita=$db->f("idsso_tbl_invalidita");
					$fldidsso_tbl_sordita=$db->f("idsso_tbl_sordita");
					$fldidsso_tbl_cecita=$db->f("idsso_tbl_cecita");
					$fldidsso_tbl_talassemia=$db->f("idsso_tbl_talassemia");
					
					$fldidsso_tabella_stato_abitativo=$db->f("idsso_tabella_stato_abitativo");
					$fldidsso_tabella_abitazione=$db->f("idsso_tabella_abitazione");
					$fldflag_coabitazione=$db->f("flag_coabitazione");
					$fldcoabitante=$db->f("coabitante");
					$fldcondizione_abitativa_nucleo=$db->f("condizione_abitativa_nucleo");
					$fldcondizione_abitativa_igiene=$db->f("condizione_abitativa_igiene");
					
					$fldidsso_tabella_tipo_occupazione=$db->f("idsso_tabella_tipo_occupazione");
					$fldaltra_occupazione=$db->f("altra_occupazione");
					$fldflag_svantaggiata=$db->f("flag_svantaggiata");
					$fldperiodo_svantaggiata=$db->f("periodo_svantaggiata");
					$fldflag_collocamento=$db->f("flag_collocamento");
					$fldperiodo_collocamento=$db->f("periodo_collocamento");
					
					$fldidsso_tabella_grado_istruzione=$db->f("idsso_tabella_grado_istruzione");
					$fldculturaa_altro=$db->f("culturaa_altro");
					$fldtitolo_studio=$db->f("titolo_studio");
					$fldqualifica_professionale=$db->f("qualifica_professionale");
					
					$fldscuola_frequentata=$db->f("scuola_frequentata");
					$fldidsso_tbl_scuola_frequenza=$db->f("idsso_tbl_scuola_frequenza");
					$fldidsso_tbl_frequenza_motivo=$db->f("idsso_tbl_frequenza_motivo");
					$fldfrequenzam_altro=$db->f("frequenzam_altro");
					$fldflag_isostegno=$db->f("flag_isostegno");
					$fldflag_antisociale=$db->f("flag_antisociale");
					$fldflag_relazionepari=$db->f("flag_relazionepari");
					$fldrelazionepari_altro=$db->f("relazionepari_altro");
					$fldflag_relazioneins=$db->f("flag_relazioneins");
					$fldrelazioneins_altro=$db->f("relazioneins_altro");
					
					$fldidsso_tbl_sanitaria=$db->f("idsso_tbl_sanitaria");
					$fldidsso_tbl_disabilita=$db->f("idsso_tbl_disabilita");
					$fldflag_dipendenze=$db->f("flag_dipendenze");
					$flddipendenza_altro=$db->f("dipendenza_altro");
					$fldidsso_tbl_dipendenza=$db->f("idsso_tbl_dipendenza");
					$flddipendenza_territorio=$db->f("dipendenza_territorio");
					
					$fldidsso_tbl_retefamiliare=$db->f("idsso_tbl_retefamiliare");
					$fldidsso_tbl_reteinformale=$db->f("idsso_tbl_reteinformale");
					$fldflag_risorseistituzionali=$db->f("flag_risorseistituzionali");
					$fldflag_integrazionesociale=$db->f("flag_integrazionesociale");
					$fldintegrazionesociale_altro=$db->f("integrazionesociale_altro");
					$fldflag_giudiziaria=$db->f("flag_giudiziaria");
					$fldidsso_tbl_giudiziaria=$db->f("idsso_tbl_giudiziaria");
					$fldidsso_tbl_detenzione=$db->f("idsso_tbl_detenzione");
					$flddurata_giudiziaria=$db->f("durata_giudiziaria");
					$fldgiudiziaria_territorio=$db->f("giudiziaria_territorio");
					$fldidsso_tbl_composizionefamiglia=$db->f("idsso_tbl_composizionefamiglia");
					$fldidsso_tbl_composizionefamiglia_dettaglio=$db->f("idsso_tbl_composizionefamiglia_dettaglio");

					$fldflag_documento_soggiorno=$db->f("flag_documentosoggiorno");
					$flddata_scadenza=$db->f("data_scadenza");
					$fldidsso_tabella_condizione_soggiorno=$db->f("idsso_tabella_condizione_soggiorno");
					$fldflag_nomade=$db->f("flag_nomade");

					$fldidgen_cittadinanza1=get_db_value("SELECT idgen_cittadinanza1 FROM sso_anagrafica_utente WHERE idutente='$fldkey'");
					$fldidgen_cittadinanza2=get_db_value("SELECT idgen_cittadinanza2 FROM sso_anagrafica_utente WHERE idutente='$fldkey'");
					$fldidamb_nazione=get_db_value("SELECT idamb_nazione FROM sso_anagrafica WHERE idutente='$fldkey'");

					if($fldidamb_nazione>0 || $fldidgen_cittadinanza1>0 || $fldidgen_cittadinanza2>0 || $fldflag_documento_soggiorno>0 || !empty_data($flddata_scadenza) || $fldidsso_tabella_condizione_soggiorno>0 || $fldflag_nomade>0)
					{
						$content=$content.'PERMANENZA LEGALE<br><br>';
					}

					if($fldidamb_nazione>0)
					{
						$fldnazione_nascita=get_db_value("SELECT nazione FROM ".DBNAME_A.".nazione WHERE idnazione='$fldidamb_nazione'");
						$content=$content.'Nazione nascita ------- '.$fldnazione_nascita.'<br><br>';
					}

					if($fldidgen_cittadinanza1>0)
					{
						$fldcittadinanza1=get_db_value("SELECT nazionalita FROM ".DBNAME_A.".nazione WHERE idnazione='$fldidgen_cittadinanza1'");

						$content=$content.'1° Cittadinanza ------- '.$fldcittadinanza1.'<br><br>';
					}

					if($fldidgen_cittadinanza2>0)
					{
						$fldcittadinanza2=get_db_value("SELECT nazionalita FROM ".DBNAME_A.".nazione WHERE idnazione='$fldidgen_cittadinanza2'");

						$content=$content.'2° Cittadinanza ------- '.$fldcittadinanza2.'<br><br>';
					}

					if($fldflag_documento_soggiorno>0)
					{
							if($fldflag_documento_soggiorno==1530) 
								$flddocumento_soggiorno="Si";
							if($fldflag_documento_soggiorno==1540)
								$flddocumento_soggiorno="In attesa di rilascio/rinnovo";
							if($fldflag_documento_soggiorno==1550)
								$flddocumento_soggiorno="No";
							if($fldflag_documento_soggiorno==1560)
								$flddocumento_soggiorno="Non disponibile";

							$content=$content.'Documento di soggiorno ------- '.$flddocumento_soggiorno.'<br><br>';
					}

					if(!empty_data($flddata_scadenza))
					{
							$content=$content.'Data scadenza documento di soggiorno ------- '.invertidata($flddata_scadenza,"/","-",2).'<br><br>';
					}

					if($fldidsso_tabella_condizione_soggiorno>0)
					{
						$fldcondizione_soggiorno=get_db_value("SELECT descrizione FROM ".DBNAME_SS.".sso_tabella_condizione_soggiorno WHERE idsso_tabella_condizione_soggiorno='$fldidsso_tabella_condizione_soggiorno'");
							$content=$content.'Tipo di documento ------- '.$fldcondizione_soggiorno.'<br><br>';
					}

					if($fldflag_nomade>0)
					{

						if($fldflag_nomade==1620)
							$flag_nomade="SI";
						elseif($fldflag_nomade==1630)
							$flag_nomade="NO";

						$content=$content.'Nomade ------- '.$flag_nomade.'<br><br>';
					}

					if($fldreddito_familiare>0 || $fldvalore_isee_famiglia>0 || $fldflag_immobili>0 || $fldpatrimonio_immobili>0 || $fldflag_pensione>0 || $fldimporto_pensione>0 || $fldflag_invalidita>0 || $fldidsso_tbl_invalidita>0 || $fldidsso_tbl_sordita>0 || $fldidsso_tbl_cecita>0 || $fldidsso_tbl_talassemia>0)
					{
						$content=$content.'<br><br>CONDIZIONE ECONOMICA/REDDITUALE<br><br>';
					}

					if ($fldreddito_familiare>0)
					{
						$content=$content.'Reddito mensile familiare ------- '.number_format($fldreddito_familiare,2,',','.').'<br><br>';
					}
					
					if ($fldvalore_isee_famiglia>0)
					{
						$content=$content.'Valore ISEE attuale ------- '.number_format($fldvalore_isee_famiglia,2,',','.').'<br><br>';
					}
					   
					if ($fldflag_immobili>0)
					{
						$content=$content.'Ha beni immobili ------- SI<br><br>';
					}   
					
					if ($fldpatrimonio_immobili>0)
					{
						$content=$content.'Valore patrimonio immobiliare ------- '.number_format($fldpatrimonio_immobili,2,',','.').'<br><br>';
					}     


					if ($fldflag_pensione>0)
					{
						$content=$content.'Trattamento previdenziale ------- SI<br>';
					}   

					if ($fldimporto_pensione>0)
					{
						$content=$content.'Importo trattamento ------- '.number_format($fldimporto_pensione,2,',','.').'<br>';
					}       			
					
					if ($fldflag_invalidita>0)
					{
						$content=$content.'Usufruisce di pensione per invalidita\' civile ------- SI<br><br>';
					}     

					if ($fldidsso_tbl_invalidita>0)
					{
						$fldsso_tbl_invalidita=get_db_value("select descrizione from sso_tbl_invalidita where idsso_tbl_invalidita='$fldidsso_tbl_invalidita'");
						$content=$content.'Tipologia invalidita\' civile ------- '.$fldsso_tbl_invalidita.'<br><br>';
					}   

					if ($fldidsso_tbl_sordita>0)
					{
						$fldsso_tbl_sordita=get_db_value("select descrizione from sso_tbl_sordita where idsso_tbl_sordita='$fldidsso_tbl_sordita'");
						$content=$content.'Provvidenze economiche previste per sordita\' ------- '.$fldidsso_tbl_sordita.'<br><br>';
					}   

					if ($fldidsso_tbl_cecita>0)
					{
						$fldsso_tbl_cecita=get_db_value("select descrizione from sso_tbl_cecita where idsso_tbl_cecita='$fldidsso_tbl_cecita'");
						$content=$content.'Provvidenze economiche previste per i ciechi civili assoluti o ciechi civili ------- '.$fldsso_tbl_cecita.'<br><br>';
					}      

					if ($fldidsso_tbl_talassemia>0)
					{
						//$fldsso_tbl_talassemia=get_db_value("select descrizione from sso_tbl_talassemia where idsso_tbl_talassemia='$fldidsso_tbl_talassemia'");
						$content=$content.'Indennita\' annuale per lavoratori affetti da talassemia major (morbo di Cooley) o drepanocitosi (anemia falciforme) ------- SI<br><br>';
					}     



					if($fldidsso_tabella_stato_abitativo>0 || $fldidsso_tabella_abitazione>0 || $fldflag_coabitazione>0 || $fldcoabitante || $fldcondizione_abitativa_nucleo>0 || $fldcondizione_abitativa_igiene>0)
					{
						$content=$content.'<br><br>CONDIZIONE ABITATIVA<br><br>';
					}

					if ($fldidsso_tabella_stato_abitativo>0)
					{
						$fldsso_tabella_stato_abitativo=get_db_value("select descrizione from sso_tabella_stato_abitativo where idsso_tabella_stato_abitativo='$fldidsso_tabella_stato_abitativo'");
						$content=$content.'Condizione abitativa ------- '.$fldsso_tabella_stato_abitativo.'<br><br>';
					}   

					if ($fldidsso_tabella_abitazione>0)
					{
						$fldsso_tabella_abitazione=get_db_value("select descrizione from sso_tabella_abitazione where idsso_tabella_abitazione='$fldidsso_tabella_abitazione'");
						$content=$content.'Tipologia di alloggio ------- '.$fldsso_tabella_abitazione.'<br><br>';
					}   

					if ($fldflag_coabitazione>0)
					{
						$content=$content.'Coabitazione ------- SI<br><br>';
					}      

					if ($fldcoabitante)
					{
						$content=$content.'Coabitante ------- '.$fldcoabitante.'<br><br>';
					}     

					if ($fldcondizione_abitativa_nucleo>0)
					{
						switch ($fldcondizione_abitativa_nucleo)
						{
							case 1:
								$fldcondizione_abitativa_nucleo="Idonee";
								break;
							case 2:
								$fldcondizione_abitativa_nucleo="Non idonee";
								break;
						}
						$content=$content.'Condizioni abitative in riferimento al rapporto n. componenti nucleo familiari e dimensioni alloggio ------- '.$fldcondizione_abitativa_nucleo.'<br><br>';
					}   

					if ($fldcondizione_abitativa_igiene>0)
					{
						switch ($fldcondizione_abitativa_igiene)
						{
							case 1:
								$fldcondizione_abitativa_igiene="Idonee";
								break;
							case 2:
								$fldcondizione_abitativa_igiene="Non idonee";
								break;
						}    				
						$content=$content.'Condizioni abitative in riferimento all\'igiene ------- '.$fldcondizione_abitativa_igiene.'<br><br>';
					}   



					if($fldidsso_tabella_tipo_occupazione>0 || $fldaltra_occupazione || $fldflag_svantaggiata>0 || $fldperiodo_svantaggiata || $fldflag_collocamento>0 || $fldperiodo_collocamento)
					{
						$content=$content.'<br><br>CONDIZIONE OCCUPAZIONALE<br><br>';
					}

					if ($fldidsso_tabella_tipo_occupazione>0)
					{
						$fldsso_tabella_tipo_occupazione=get_db_value("select descrizione from sso_tabella_tipo_occupazione where idsso_tabella_tipo_occupazione='$fldidsso_tabella_tipo_occupazione'");
						$content=$content.'Condizione occupazionale ------- '.$fldsso_tabella_tipo_occupazione.'<br><br>';
					}      

					if ($fldaltra_occupazione)
					{
						$content=$content.'Altra occupazione ------- '.$fldaltra_occupazione.'<br><br>';
					}     

					if ($fldflag_svantaggiata>0)
					{
						$content=$content.'Disoccupato appartiene a categoria svantaggiata (ex detenuto ) etc. Iscritto al collocamento ------- SI';
					}   

					if ($fldperiodo_svantaggiata)
					{
						$content=$content.'Periodo categoria svantaggiata ------- '.$fldperiodo_svantaggiata.' anni<br><br>';
					}   

					if ($fldflag_collocamento>0)
					{
						$content=$content.'Iscritto al collocamento mirato ------- SI<br><br>';
					}

					if ($fldperiodo_collocamento)
					{
						$content=$content.'Periodo collocamento ------- '.$fldperiodo_collocamento.' anni<br><br>';
					}     



					if($fldidsso_tabella_grado_istruzione>0 || $fldculturaa_altro || $fldtitolo_studio || $fldqualifica_professionale)
					{
						$content=$content.'<br><br>CONDIZIONE CULTURALE/FORMATIVA<br><br>';
					}

					if ($fldidsso_tabella_grado_istruzione>0)
					{
						$fldsso_tabella_grado_istruzione=get_db_value("select descrizione from sso_tabella_grado_istruzione where idsso_tabella_grado_istruzione='$fldidsso_tabella_grado_istruzione'");
						$content=$content.'Grado d\'istruzione ------- '.$fldsso_tabella_grado_istruzione.'<br><br>';
					}   

					if ($fldculturaa_altro)
					{
						$content=$content.'Grado d\'istruzione altro ------- '.$fldculturaa_altro.'<br><br>';
					}   

					if ($fldtitolo_studio)
					{
						$content=$content.'Titolo di Studio ------- '.$fldtitolo_studio.'<br><br>';
					}      

					if ($fldqualifica_professionale)
					{
						$content=$content.'Qualifica professionale ------- '.$fldqualifica_professionale.'<br><br>';
					}     



					if($fldscuola_frequentata || $fldidsso_tbl_scuola_frequenza>0 || $fldidsso_tbl_frequenza_motivo>0 
						|| $fldfrequenzam_altro || $fldflag_isostegno>0 || $fldflag_antisociale>0 || $fldflag_relazionepari>0 
						|| $fldrelazionepari_altro || $fldflag_relazioneins>0 || $fldrelazioneins_altro)
					{
						$content=$content.'<br><br>CONDIZIONE CULTURALE/FORMATIVA MINORE<br><br>';
					}

					if ($fldscuola_frequentata)
					{
						$content=$content.'Scuola frequentata ------- '.$fldscuola_frequentata.'<br><br>';
					}   

					if ($fldidsso_tbl_scuola_frequenza>0)
					{
						$fldsso_tbl_scuola_frequenza=get_db_value("select descrizione from sso_tbl_scuola_frequenza where idsso_tbl_scuola_frequenza='$fldidsso_tbl_scuola_frequenza'");
						$content=$content.'Frequenza ------- '.$fldsso_tbl_scuola_frequenza.'<br><br>';
					}   

					if ($fldidsso_tbl_frequenza_motivo>0)
					{
						$fldsso_tbl_scuola_frequenza=get_db_value("select descrizione from sso_tbl_frequenza_motivo where idsso_tbl_frequenza_motivo='$fldidsso_tbl_frequenza_motivo'");
						$content=$content.'Motivo anomalie nella frequenza del corso di istruzione ------- '.$fldsso_tbl_scuola_frequenza.'<br><br>';
					}      

					if ($fldfrequenzam_altro)
					{
						$content=$content.'Altra anomalia ------- '.$fldfrequenzam_altro.'<br><br>';
					}     

					if ($fldflag_isostegno>0)
					{
						$content=$content.'Presenza di insegnanti di sostegno ------- SI<br><br>';
					}   

					if ($fldflag_antisociale>0)
					{
						$content=$content.'Si sono verificati comportamenti anti-sociali (es. bullismo, vandalismo) ------- SI<br><br>';
					}   

					if ($fldflag_relazionepari>0)
					{
						$content=$content.'Si rilevano difficoltà di relazione con i pari ------- SI<br><br>';
					}      

					if ($fldrelazionepari_altro)
					{
						$content=$content.' ------- '.$fldrelazionepari_altro.'<br><br>';
					}     

					if ($fldflag_relazioneins>0)
					{
						$content=$content.'Si rilevano difficoltà di relazione con gli insegnanti ------- SI<br><br>';
					}   

					if ($fldrelazioneins_altro)
					{
						$content=$content.' ------- '.$fldrelazioneins_altro.'<br><br>';
					}   


					$anagrafica_sanitaria=get_db_value("select count(*) from sso_anagrafica_sanitaria inner join sso_tbl_invalidita on sso_anagrafica_sanitaria.tipo_invalidita=sso_tbl_invalidita.idsso_tbl_invalidita and idsso_anagrafica_utente='$fldkey'");

					if($fldidsso_tbl_sanitaria>0 || $fldidsso_tbl_disabilita>0 || $fldflag_dipendenze>0 || $fldidsso_tbl_dipendenza>0 || $flddipendenza_altro || $flddipendenza_territorio || $anagrafica_sanitaria>0)
					{
						$content=$content.'<br><br>CONDIZIONE PERSONALE E DI SALUTE/AUTONOMIA<br><br>';
					}

					if ($fldidsso_tbl_sanitaria>0)
					{
						$fldsso_tbl_sanitaria=get_db_value("select descrizione from sso_tbl_sanitaria where idsso_tbl_sanitaria='$fldidsso_tbl_sanitaria'");
						$content=$content.'Presenza di patologie ------- '.$fldsso_tbl_sanitaria.'<br><br>';
					}         	

					if (empty($pidsso_storico_anagrafica_utente))
						$sSQL="select sso_anagrafica_sanitaria.*,sso_tbl_invalidita.descrizione from sso_anagrafica_sanitaria inner join sso_tbl_invalidita on sso_anagrafica_sanitaria.tipo_invalidita=sso_tbl_invalidita.idsso_tbl_invalidita and idsso_anagrafica_utente='$fldkey'";
					else
						$sSQL="select sso_storico_anagrafica_sanitaria.*,sso_tbl_invalidita.descrizione from sso_storico_anagrafica_sanitaria inner join sso_tbl_invalidita on sso_storico_anagrafica_sanitaria.tipo_invalidita=sso_tbl_invalidita.idsso_tbl_invalidita and idsso_storico_anagrafica_utente='$fldkey'";	
					$db->query($sSQL);
					$next_record=$db->next_record();
					while($next_record)
					  {
						$fldidsso_anagrafica_sanitaria=$db->f("idsso_anagrafica_sanitaria");
						$flddescrizione=$db->f("descrizione");
						$content=$content.''.$flddescrizione.' ------- SI<br><br>';
						$next_record=$db->next_record();
					  }    			
					
					if ($fldidsso_tbl_disabilita>0)
					{
						$fldsso_tbl_disabilita=get_db_value("select descrizione from sso_tbl_disabilita where idsso_tbl_disabilita='$fldidsso_tbl_disabilita'");
						$content=$content.'Presenza disabilita\' ------- '.$fldsso_tbl_disabilita.'<br><br>';
					}  

					if ($fldflag_dipendenze>0)
					{
						$content=$content.'Dipendenze ------- SI<br><br>';
					}         			
					if ($fldidsso_tbl_dipendenza>0)
					{
						$fldsso_tbl_dipendenza=get_db_value("select descrizione from sso_tbl_dipendenza where idsso_tbl_dipendenza='$fldidsso_tbl_dipendenza'");
						$content=$content.'Dipendenze da------- '.$fldsso_tbl_dipendenza.'<br><br>';
					}  
					
					if ($flddipendenza_altro)
					{
						$content=$content.'Dipendenze altro ------- '.$flddipendenza_altro.'<br><br>';
					}      			
						
					if ($flddipendenza_territorio)
					{
						$content=$content.'Servizi territoriali di cura che hanno in carico la persona ------- '.$flddipendenza_territorio.'<br><br>';
					} 

					$sSQL="select sso_anagrafica_dfamiliare.*,sso_tbl_familiare.descrizione from sso_anagrafica_dfamiliare inner join sso_tbl_familiare on sso_anagrafica_dfamiliare.idsso_tbl_familiare=sso_tbl_familiare.idsso_tbl_familiare where idsso_anagrafica_utente='$fldidutente' ";

					$query1=get_db_value($sSQL);

					$sSQL="select sso_anagrafica_rfamiliare.*,sso_tbl_familiare_risorsa.descrizione from sso_anagrafica_rfamiliare inner join sso_tbl_familiare_risorsa on sso_anagrafica_rfamiliare.idsso_tbl_familiare_risorsa=sso_tbl_familiare_risorsa.idsso_tbl_familiare_risorsa where idsso_anagrafica_utente='$fldidutente'";

					$query2=get_db_value($sSQL);

					$sSQL="select sso_anagrafica_pminore.*,sso_tbl_minore_problema.descrizione from sso_anagrafica_pminore inner join sso_tbl_minore_problema on sso_anagrafica_pminore.idsso_tbl_minore_problema=sso_tbl_minore_problema.idsso_tbl_minore_problema where idsso_anagrafica_utente='$fldidutente'";

					$query3=get_db_value($sSQL);

					$sSQL="select sso_anagrafica_rminore.*,sso_tbl_minore_risorsa.descrizione from sso_anagrafica_rminore inner join sso_tbl_minore_risorsa on sso_anagrafica_rminore.idsso_tbl_minore_risorsa=sso_tbl_minore_risorsa.idsso_tbl_minore_risorsa where idsso_anagrafica_utente='$fldidutente'";

					$query4=get_db_value($sSQL);

					if(!empty($query1) || !empty($query2) || !empty($query3) || !empty($query4) || $fldidsso_tbl_composizionefamiglia)
					{
						$content=$content.'<br><br>PROFILO DEL NUCLEO FAMILIARE<br><br>';
					}

					if($fldidsso_tbl_composizionefamiglia>0)
					{
						$flddescrizione=get_db_value("SELECT descrizione 
											FROM sso_tbl_composizionefamiglia_dettaglio
											WHERE idsso_tbl_composizionefamiglia_dettaglio='$fldidsso_tbl_composizionefamiglia_dettaglio'");
						$content=$content.$flddescrizione.'<br><br>';
					}

					$sSQL="select sso_anagrafica_dfamiliare.*,sso_tbl_familiare.descrizione from sso_anagrafica_dfamiliare inner join sso_tbl_familiare on sso_anagrafica_dfamiliare.idsso_tbl_familiare=sso_tbl_familiare.idsso_tbl_familiare where idsso_anagrafica_utente='$fldidutente' ";
					$db->query($sSQL);
					$next_record=$db->next_record();
					while($next_record)
					{
						$fldidsso_tbl_familiare=$db->f("idsso_anagrafica_dfamiliare");
						$flddescrizione=$db->f("descrizione");
						$content=$content.$flddescrizione.' ------- SI<br><br>';
						$next_record=$db->next_record();
					}    			
				
					$sSQL="select sso_anagrafica_rfamiliare.*,sso_tbl_familiare_risorsa.descrizione from sso_anagrafica_rfamiliare inner join sso_tbl_familiare_risorsa on sso_anagrafica_rfamiliare.idsso_tbl_familiare_risorsa=sso_tbl_familiare_risorsa.idsso_tbl_familiare_risorsa where idsso_anagrafica_utente='$fldidutente'";
					$db->query($sSQL);
					$next_record=$db->next_record();
					while($next_record)
					{
						$fldidsso_tbl_familiare_risorsa=$db->f("idsso_anagrafica_rfamiliare");
						$flddescrizione=$db->f("descrizione");
						$content=$content.$flddescrizione.' ------- SI<br><br>';

						$next_record=$db->next_record();
					}		


					$sSQL="select sso_anagrafica_pminore.*,sso_tbl_minore_problema.descrizione from sso_anagrafica_pminore inner join sso_tbl_minore_problema on sso_anagrafica_pminore.idsso_tbl_minore_problema=sso_tbl_minore_problema.idsso_tbl_minore_problema where idsso_anagrafica_utente='$fldidutente'";
					$db->query($sSQL);
					$next_record=$db->next_record();
					while($next_record)
					{
						$fldidsso_tbl_minore_problema=$db->f("idsso_anagrafica_pminore");
						$flddescrizione=$db->f("descrizione");
						$content=$content.$flddescrizione.' ------- SI<br><br>';

						$next_record=$db->next_record();
					}				      

					$sSQL="select sso_anagrafica_rminore.*,sso_tbl_minore_risorsa.descrizione from sso_anagrafica_rminore inner join sso_tbl_minore_risorsa on sso_anagrafica_rminore.idsso_tbl_minore_risorsa=sso_tbl_minore_risorsa.idsso_tbl_minore_risorsa where idsso_anagrafica_utente='$fldidutente'";
					$db->query($sSQL);
					$next_record=$db->next_record();
					while($next_record)
					{
						$fldidsso_anagrafica_rminore=$db->f("idsso_anagrafica_rminore");
						$flddescrizione=$db->f("descrizione");
						$content=$content.$flddescrizione.' ------- SI<br><br>';

						$next_record=$db->next_record();
					}				      

					if($fldidsso_tbl_retefamiliare>0 || $fldidsso_tbl_reteinformale>0 || $fldflag_risorseistituzionali>0 || $fldflag_integrazionesociale>0 || $fldintegrazionesociale_altro>0)
					{
						$content=$content.'<br><br>RETE SOCIALE/AMIMCALE e dei SERVIZI<br><br>';
					}

					if ($fldidsso_tbl_retefamiliare>0)
					{
						$fldsso_tbl_retefamiliare=get_db_value("select descrizione from sso_tbl_retefamiliare where idsso_tbl_retefamiliare='$fldidsso_tbl_retefamiliare'");
						$content=$content.'Reti familiari ------- '.$fldsso_tbl_retefamiliare.'<br><br>';
					}         			
					if ($fldidsso_tbl_reteinformale>0)
					{
						$fldsso_tbl_reteinformale=get_db_value("select descrizione from sso_tbl_reteinformale where idsso_tbl_reteinformale='$fldidsso_tbl_reteinformale'");
						$content=$content.'Reti informali ------- '.$fldsso_tbl_reteinformale.'<br><br>';
					}         			
					if ($fldflag_risorseistituzionali>0)
					{
						$content=$content.'Capacita\' di utilizzare risorse istituzionali ------- SI<br><br>';
					}         			
					if ($fldflag_integrazionesociale>0)
					{
						$content=$content.'Integrazione con il contesto sociale di appartenenza ------- SI<br><br>';
					}         			
					if ($fldintegrazionesociale_altro>0)
					{
						$content=$content.'Altro ------- '.$fldintegrazionesociale_altro.'<br><br>';
					}


					if($fldflag_giudiziaria>0 || $fldidsso_tbl_giudiziaria>0 || $fldidsso_tbl_detenzione>0 || $flddurata_giudiziaria>0 || $fldgiudiziaria_territorio>0)
					{
						$content=$content.'<br><br>SITUAZIONE GIUDIZIARIA<br><br>';
					}

					if ($fldflag_giudiziaria>0)
					{
						$content=$content.'Ci sono procedimenti e/o provvedimenti giudiziari di natura civile e/o penale in corso a carico del beneficiario o di uno dei componenti del nucleo familiare? ------- SI<br><br>';
					}
					if ($fldidsso_tbl_giudiziaria>0)
					{
						$fldsso_tbl_giudiziaria=get_db_value("select descrizione from sso_tbl_giudiziaria where idsso_tbl_giudiziaria='$fldidsso_tbl_giudiziaria'");
						$content=$content.'Tipo procedimento ------- '.$fldsso_tbl_giudiziaria.'<br><br>';
					}         			         			
					if ($fldidsso_tbl_detenzione>0)
					{
						$fldsso_tbl_detenzione=get_db_value("select descrizione from sso_tbl_detenzione where idsso_tbl_detenzione='$fldidsso_tbl_detenzione'");
						$content=$content.'Misure alternative alla detenzione ------- '.$fldsso_tbl_detenzione.'<br><br>';
					}         			         			
					if ($flddurata_giudiziaria>0)
					{
						$content=$content.'Durata del provvedimento ------- '.$flddurata_giudiziaria.' anni<br><br>';
					}        
					if ($fldgiudiziaria_territorio>0)
					{
						$content=$content.'Servizi territoriali che hanno in carico la persona ------- '.$fldgiudiziaria_territorio.'<br><br>';
					} 


					$fldkey=$fldidutente;

					$fldtable_casellario="sso_anagrafica_casellario";
					$fldtablekey_casellario="idsso_anagrafica_utente";	
					$fldkey_casellario=$fldkey;

					// SINA
					$fldcount=get_db_value("SELECT count(*) 
						FROM ".$fldtable_casellario." 
						INNER JOIN sso_tbl_casellario_campi ON sso_tbl_casellario_campi.idcampo=".$fldtable_casellario.".idcampo  
						WHERE ".$fldtablekey_casellario."='".$fldkey_casellario."' 
						and flag_sina='1'");
					
					if($fldcount>0)
					{
						$content=$content.'<br><br>SINA - inverventi persone non autosufficienti<br><br>';
					}

					$sSql="SELECT * 
					FROM sso_tbl_casellario_campi
					WHERE flag_sina='1'";
					
					$db->query($sSql);
					$res=$db->next_record();
					while($res)
					{
						$fldidcampo=$db->f("idcampo");
						$flddescrizione=$db->f("descrizione");
						
						$idselezione=get_db_value("SELECT idselezione FROM ".$fldtable_casellario." WHERE ".$fldtablekey_casellario."='".$fldkey_casellario."' and idcampo='$fldidcampo'");
						if(!empty($idselezione))
						{
							$descrizione_selezione=get_db_value("SELECT descrizione FROM sso_tbl_casellario WHERE  idcampo='$fldidcampo' AND idselezione='$idselezione'");
							$content=$content.$flddescrizione.' ------- '.$descrizione_selezione.'<br><br>';
						}

						$res=$db->next_record();
					}


					// SINBA
					$fldcount=get_db_value("SELECT count(*) 
						FROM ".$fldtable_casellario." 
						INNER JOIN sso_tbl_casellario_campi ON sso_tbl_casellario_campi.idcampo=".$fldtable_casellario.".idcampo  
						WHERE ".$fldtablekey_casellario."='".$fldkey_casellario."' 
						and flag_sinba='1'");
					
					if($fldcount>0)
					{
						$content=$content.'<br><br>SINBA - cura/protezione bambini e loro famiglie<br><br>';
					}

					$sSql="SELECT * 
					FROM sso_tbl_casellario_campi
					WHERE flag_sinba='1'";
					
					$db->query($sSql);
					$res=$db->next_record();
					while($res)
					{
						$fldidcampo=$db->f("idcampo");
						$flddescrizione=$db->f("descrizione"); 
						$fldflag_valore=$db->f("flag_valore");
						
						if(empty($fldflag_valore))
						{
							$idselezione=get_db_value("SELECT idselezione FROM ".$fldtable_casellario." WHERE ".$fldtablekey_casellario."='".$fldkey_casellario."' and idcampo='$fldidcampo'");
							if(!empty($idselezione))
							{
								$descrizione_selezione=get_db_value("SELECT descrizione FROM sso_tbl_casellario WHERE  idcampo='$fldidcampo' AND idselezione='$idselezione'");
								$content=$content.$flddescrizione.' ------- '.$descrizione_selezione.'<br><br>';
							}
						}
						else
						{
							$fldnote=get_db_value("SELECT note FROM ".$fldtable_casellario." WHERE ".$fldtablekey_casellario."='".$fldkey_casellario."' and idcampo='$fldidcampo'");
							if(!empty($fldnote))
							{
								$content=$content.$flddescrizione.' ------- '.$fldnote.'<br><br>';
							}
						}

						$res=$db->next_record();
					}

			//MANCANO VALUTAZIONI/CONCLUSIONI/ESITO

				$next_record=$db->next_record();

				}

				$content=$content."</body></html>";

				//file_put_contents("../sicare/documenti/allegato_sintesi_".$fldcognome_beneficiario.' '.$fldnome_beneficiario.".html",$content);

				$filename_sintesi="../sicare/documenti/allegato_sintesi_".$fldcognome_beneficiario.' '.$fldnome_beneficiario.".pdf";
				if(file_exists ($filename_sintesi))
					unlink($filename_sintesi);

				$pdf=new PDF_HTML();
				$pdf->SetFont('Arial','',12);
				$pdf->AddPage();
				$pdf->WriteHTML($content);
				$pdf->Output($filename_sintesi);
			}

			if(!empty($psend_preassessment) && !empty($psend_sintesi))
				$attachment=$filename."|".$filename_sintesi;
			if(!empty($psend_preassessment) && empty($psend_sintesi))
				$attachment=$filename;
			if(empty($psend_preassessment) && !empty($psend_sintesi))
				$attachment=$filename_sintesi;

			if($_SERVER["HTTP_HOST"]=="37.206.216.84")
			{
				$request_rest = curl_init();
				curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_mail_mc_equipe.php');

				$params=array();
				$params['email']=$fldemail;
				$params['testo']=$ptesto;
				$params['oggetto']=$poggetto;

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
				$aEMAIL[1]=$poggetto;
				$aEMAIL[2]=$ptesto;
				$aEMAIL[3]=$attachment;
				$fldresult=sendMAIL($aEMAIL);				
			}

			if($fldresult=="Messaggio inviato correttamente.")
			{
				$oggi=date("Y-m-d");
				$ora=date("H:i:s");

				$sSQL="UPDATE sso_domanda_equipe 
				SET data_convocazione='$oggi',
				ora_convocazione='$ora'
				WHERE idsso_domanda_seduta='$pidsso_domanda_seduta' 
				AND idgen_utente='$pidgen_utente'";
				$db->query($sSQL);

				$result='mail inviata';
			}
			else
				$result='mail non inviata';

			echo $result;
		}
		break;


	case "paireis":
		$pidsso_domanda=get_param("_domanda");
		$pidsso_progetto_reis=get_db_value("select idsso_progetto from sso_progetto where codice_inps='REIS'");
		$fldidutente=get_db_value("select idutente from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldidsso_progetto=get_db_value("select idsso_progetto from sso_progetto where codice_inps='REIS'");
		$fldidsso_tbl_area=get_db_value("select idsso_tbl_area from sso_progetto where codice_inps='REIS'");
		$fldidsso_tbl_servizio=get_db_value("select idsso_tbl_servizio from sso_progetto where codice_inps='REIS'");
		$fldidsso_tbl_prestazione=get_db_value("select idsso_tbl_prestazione from sso_progetto where codice_inps='REIS'");
		//$flddata_inizio_prestazione=get_db_value("select data_inizio_prestazione from sso_progetto where codice_inps='REIS'");
		//$flddata_fine_prestazione=get_db_value("select data_fine_prestazione from sso_progetto where codice_inps='REIS'");
		$fldidsso_piano_zona=get_db_value("select idsso_piano_zona from sso_piano_zona where codice_inps='REIS'");
		$fldmesi_progetto=get_db_value("select numero_mesi from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldmese_inizio=get_db_value("select mese_inizio from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$flddata_inizio_prestazione="2017-".str_pad($fldmese_inizio,2,"0",STR_PAD_LEFT)."-01";
		$flddata_fine_prestazione=date("Y-m-d",mktime(0,0,0,$fldmese_inizio+$fldmesi_progetto,0,2017));
		$fldidsso_tabella_stato_intervento=7;
		$pdata=date("Y-m-d");
		$idgen_operatore_incarico=verifica_utente($chiave);
		$idassistente=$idgen_operatore_incarico;
		$pidsso_tabella_motivo_domanda=13;
		$pflag_carattere=2;
		$pidsso_tbl_agevolazione=1;
		$pnumero_protocollo_domanda=get_db_value("select numero_protocollo from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldquantita=1;
		$fldimporto=get_param("_importo");
		$fldidsso_tbl_um=2;
		$pflag_ente_pubblico=1;
		$pidsso_domanda_intervento=get_db_value("SELECT idsso_domanda_intervento FROM sso_domanda_intervento WHERE idsso_domanda='$pidsso_domanda'");
		if(empty($pidsso_domanda_intervento))
		{
			$sSQL="INSERT INTO sso_domanda_intervento 
				(idutente,data,idgen_operatore_incarico,idassistente,idsso_domanda,
				idsso_tbl_area,idsso_tabella_motivo_domanda,idsso_piano_zona,idsso_tbl_servizio,idsso_tabella_stato_intervento,data_inizio,
				data_fine,flag_ente_pubblico,idsso_progetto,
				flag_carattere,idsso_tbl_agevolazione,numero_protocollo_domanda)
				VALUES 
				('$fldidutente','$pdata','$idgen_operatore_incarico','$idassistente','$pidsso_domanda',
				'$fldidsso_tbl_area','$pidsso_tabella_motivo_domanda','$fldidsso_piano_zona','$fldidsso_tbl_servizio','$fldidsso_tabella_stato_intervento','$flddata_inizio_prestazione',
				'$flddata_fine_prestazione','$pflag_ente_pubblico','$pidsso_progetto_reis','$pflag_carattere','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda')";
			$db->query($sSQL);
			$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
					
			$sSQL="insert into sso_domanda_prestazione (
				idsso_domanda_intervento,idutente,idsso_tbl_um,idsso_tbl_prestazione,quantita,importo,tariffa) values(
				'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um','$fldidsso_tbl_prestazione','$fldquantita','$fldimporto','$fldimporto')";
			$db->query($sSQL);
		}
		
		$pai=new Pai($pidsso_domanda_intervento);
		$quantita_totale=$pai->get_quantita_pai();
		$fldtariffa=$pai->tariffa;

		$importo_totale=$fldtariffa*$quantita_totale;
					
		$fldprevisione_saldo=$importo_totale-$fldimporto_compartecipazione;
		$sSQL="update sso_domanda_intervento set prestazione_previsione='$importo_totale',previsione_compartecipazione='$fldimporto_compartecipazione',previsione_saldo='$fldprevisione_saldo' where idsso_domanda_intervento='$pidsso_domanda_intervento'";		
		$db->query($sSQL);
		break;
	case "paireis18":
		$pidsso_domanda=get_param("_domanda");
		$pidsso_progetto_reis=get_db_value("select idsso_progetto from sso_progetto where codice_inps='REIS18'");
		$fldidutente=get_db_value("select idutente from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldidsso_progetto=get_db_value("select idsso_progetto from sso_progetto where codice_inps='REIS18'");
		$fldidsso_tbl_area=get_db_value("select idsso_tbl_area from sso_progetto where codice_inps='REIS18'");
		$fldidsso_tbl_servizio=get_db_value("select idsso_tbl_servizio from sso_progetto where codice_inps='REIS18'");
		$fldidsso_tbl_prestazione=get_db_value("select idsso_tbl_prestazione from sso_progetto where codice_inps='REIS18'");
		//$flddata_inizio_prestazione=get_db_value("select data_inizio_prestazione from sso_progetto where codice_inps='REIS'");
		//$flddata_fine_prestazione=get_db_value("select data_fine_prestazione from sso_progetto where codice_inps='REIS'");
		$fldidsso_piano_zona=get_db_value("select idsso_piano_zona from sso_piano_zona where codice_inps='REIS18'");
		$fldmesi_progetto=get_db_value("select numero_mesi from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldmese_inizio=get_db_value("select mese_inizio from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$flddata_inizio_prestazione="2018-".str_pad($fldmese_inizio,2,"0",STR_PAD_LEFT)."-01";
		$flddata_fine_prestazione=date("Y-m-d",mktime(0,0,0,$fldmese_inizio+$fldmesi_progetto,0,2017));
		$fldidsso_tabella_stato_intervento=7;
		$pdata=date("Y-m-d");
		$idgen_operatore_incarico=verifica_utente($chiave);
		$idassistente=$idgen_operatore_incarico;
		$pidsso_tabella_motivo_domanda=13;
		$pflag_carattere=2;
		$pidsso_tbl_agevolazione=1;
		$pnumero_protocollo_domanda=get_db_value("select numero_protocollo from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$fldquantita=1;
		$fldimporto=get_param("_importo");
		$fldidsso_tbl_um=2;
		$pflag_ente_pubblico=1;
		$pidsso_domanda_intervento=get_db_value("SELECT idsso_domanda_intervento FROM sso_domanda_intervento WHERE idsso_domanda='$pidsso_domanda'");
		if(empty($pidsso_domanda_intervento))
		{
			$sSQL="INSERT INTO sso_domanda_intervento 
				(idutente,data,idgen_operatore_incarico,idassistente,idsso_domanda,
				idsso_tbl_area,idsso_tabella_motivo_domanda,idsso_piano_zona,idsso_tbl_servizio,idsso_tabella_stato_intervento,data_inizio,
				data_fine,flag_ente_pubblico,idsso_progetto,
				flag_carattere,idsso_tbl_agevolazione,numero_protocollo_domanda)
				VALUES 
				('$fldidutente','$pdata','$idgen_operatore_incarico','$idassistente','$pidsso_domanda',
				'$fldidsso_tbl_area','$pidsso_tabella_motivo_domanda','$fldidsso_piano_zona','$fldidsso_tbl_servizio','$fldidsso_tabella_stato_intervento','$flddata_inizio_prestazione',
				'$flddata_fine_prestazione','$pflag_ente_pubblico','$pidsso_progetto_reis','$pflag_carattere','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda')";
			$db->query($sSQL);
			$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
					
			$sSQL="insert into sso_domanda_prestazione (
				idsso_domanda_intervento,idutente,idsso_tbl_um,idsso_tbl_prestazione,quantita,importo,tariffa) values(
				'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um','$fldidsso_tbl_prestazione','$fldquantita','$fldimporto','$fldimporto')";
			$db->query($sSQL);
		}
		
		$pai=new Pai($pidsso_domanda_intervento);
		$quantita_totale=$pai->get_quantita_pai();
		$fldtariffa=$pai->tariffa;

		$importo_totale=$fldtariffa*$quantita_totale;
					
		$fldprevisione_saldo=$importo_totale-$fldimporto_compartecipazione;
		$sSQL="update sso_domanda_intervento set prestazione_previsione='$importo_totale',previsione_compartecipazione='$fldimporto_compartecipazione',previsione_saldo='$fldprevisione_saldo' where idsso_domanda_intervento='$pidsso_domanda_intervento'";		
		$db->query($sSQL);
		break;		
	case "get_data_dichiarazione_anno":
		$fldidutente=get_param("_idutente");
		$fldanno=get_param("_anno");

		$flddata_dichiarazione=get_db_value("SELECT data_dichiarazione FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$fldidutente' AND anno='$fldanno'");
		echo $flddata_dichiarazione=invertidata($flddata_dichiarazione,"/","-",2);
		break;

		

	case "salva_data_dichiarazione_anno":
		$fldidutente=get_param("_idutente");
		$fldanno=get_param("_anno");
		$flddata_dichiarazione=get_param("_datadichiarazione");
		$flddata_dichiarazione=invertidata($flddata_dichiarazione,"-","/",1);

		$fldidsso_anagrafica_isee=get_db_value("SELECT idsso_anagrafica_isee FROM sso_anagrafica_isee WHERE anno='$fldanno' AND idsso_anagrafica_utente='$fldidutente'");
		if($fldidsso_anagrafica_isee>0)
		{
			$sSQL="UPDATE sso_anagrafica_isee SET data_dichiarazione='$flddata_dichiarazione' WHERE idsso_anagrafica_isee='$fldidsso_anagrafica_isee'";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="INSERT INTO sso_anagrafica_isee(idsso_anagrafica_utente,anno,data_dichiarazione) VALUES('$fldidutente','$fldanno','$flddata_dichiarazione')";
			$db->query($sSQL);
		}

		echo "ok";
		break;

	case "rimuovicomponente":
		$pidgen_anagrafe_popolazione=get_param("_idgen_anagrafe_popolazione");

		$sSQL="delete from ".DBNAME_A.".gen_anagrafe_popolazione where idgen_anagrafe_popolazione='$pidgen_anagrafe_popolazione'";
		$db->query($sSQL);

		echo "ok";
		break;

	case "escludicomponente":
		$pidgen_anagrafe_popolazione=get_param("_idgen_anagrafe_popolazione");
		
		$sSQL="UPDATE ".DBNAME_A.".gen_anagrafe_popolazione SET flag_escludi_monitoraggio=1 WHERE idgen_anagrafe_popolazione='$pidgen_anagrafe_popolazione'";
		$db->query($sSQL);

		echo "ok";
		break;

	case "includicomponente":
		$pidgen_anagrafe_popolazione=get_param("_idgen_anagrafe_popolazione");
		
		$sSQL="UPDATE ".DBNAME_A.".gen_anagrafe_popolazione SET flag_escludi_monitoraggio=0 WHERE idgen_anagrafe_popolazione='$pidgen_anagrafe_popolazione'";
		$db->query($sSQL);

		echo "ok";
		break;

	case "getiseeintervento":

		$fldidutente=get_param("_idutente");
		$response='';

		if(!empty($fldidutente))
		{
			$response=$response.'<ul class="dropdown-menu pull-right" aria-labelledby="dropdownMenu1">';

			$sSQL="SELECT * FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$fldidutente' ORDER BY anno";
			$db->query($sSQL);
			$res=$db->next_record();
			$counter=1;
			$aDSU_DICHIARATO=array();
			while($res)
			{
				$fldidsso_anagrafica_isee=$db->f("idsso_anagrafica_isee");
				$fldidutente=$db->f("idsso_anagrafica_utente");
				$fldvalore_isee=$db->f("valore_isee");
				$fldvalore_isee_accertato=$db->f("valore_isee_accertato");
				$fldanno=$db->f("anno");
				$flddata_dsu=$db->f("data_dsu");
				$flddata_dsu=invertidata($flddata_dsu,"/","-",2);
				
				$fldnumero_dsu=$db->f("numero_dsu");
				$aDSU_DICHIARATO[]=$fldnumero_dsu;

				$flddata_dichiarazione=$db->f("data_dichiarazione");
				$flddata_dichiarazione=invertidata($flddata_dichiarazione,"/","-",2);
				
				$fldisee_non_dichiarato=$db->f("isee_non_dichiarato");

				if($fldisee_non_dichiarato)
					$response=$response.'<li><a onclick="get_isee(\''.$fldvalore_isee.'\',\''.$fldidsso_anagrafica_isee.'\');">ISEE non dichiarato - Anno: '.$fldanno.'</a></li>';
				else
					$response=$response.'<li><a onclick="get_isee(\''.$fldvalore_isee.'\',\''.$fldidsso_anagrafica_isee.'\');">ISEE: '.@number_format($fldvalore_isee,2,",",".").' € - DSU: '.$fldnumero_dsu.' - Anno: '.$fldanno.'</a></li>';

				$counter++;
				$res=$db->next_record();
			}

			/*
			$fldisee_ordinario=get_db_value("select valore_isee_famiglia FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			if($fldisee_ordinario!=null)
				$response=$response.'<li><a onclick="get_isee(\''.$fldisee_ordinario.'\');">Valore isee ordinario: '.number_format($fldisee_ordinario,2,",",".").'€</a></li>';
			
			$fldisee_socio_sanitario=get_db_value("select valore_isee_singolo FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
			if($fldisee_socio_sanitario!=null)
				$response=$response.'<li><a onclick="get_isee(\''.$fldisee_socio_sanitario.'\');">Valore isee socio-sanitario: '.number_format($fldisee_socio_sanitario,2,",",".").'€</a></li>';
			*/

			$response=$response."</ul>";

			echo $response;
		}
		else
			echo $response;
		break;

		
	case "getdsu":

		$fldidutente=get_param("_idutente");
		$response='';

		if(!empty($fldidutente))
		{
			$response=$response.'<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">';
			$sSQL="select * from sso_anagrafica_isee where idsso_anagrafica_utente='$fldidutente' and numero_dsu is not null order by data_dsu desc";
			$db->query($sSQL);
			$next_record=$db->next_record();
			while($next_record)
			{
				$fldidsso_anagrafica_isee=$db->f("idsso_anagrafica_isee");
				$fldnumero_dsu=$db->f("numero_dsu");
				$flddata_dsu=$db->f("data_dsu");
				$fldanno=$db->f("anno");
				$flddata_dsu=invertidata($flddata_dsu,"/","-",2);
				if($fldnumero_dsu)
					$response=$response.'<li><a onclick="getDSU(\''.$fldnumero_dsu.'\',\''.$flddata_dsu.'\',\''.$fldanno.'\',\''.$fldidsso_anagrafica_isee.'\');">'.$fldnumero_dsu.' del '.$flddata_dsu.'</a></li>';

				$next_record=$db->next_record();
			}
			
			$response=$response."</ul>";

			echo $response;
		}
		else
			echo $response;
		break;


		
	case "get_target_siuss":

		$fldidutente=get_param("_idutente");
		$fldidsso_domanda_intervento=get_param("_idsso_domanda_intervento");

		$fldidsso_tbl_targetsiuss=0;

		if(!empty($fldidsso_domanda_intervento))
		{
			$pai=new Pai($fldidsso_domanda_intervento);
			$fldidsso_tbl_targetsiuss=$pai->idsso_tbl_targetsiuss;
		}
		else
		{
			if(!empty($fldidutente))
				$fldidsso_tbl_targetsiuss=get_db_value("SELECT idsso_tbl_targetsiuss FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$fldidutente'");
		}


		echo $fldidsso_tbl_targetsiuss;
		break;



	case "firma_patto_sia";
		$pazione=get_param("_azione");
		$pidsso_domanda=get_param("_iddomanda");

		switch($pazione)
		{
			case 1:
				$oggi=date("Y-m-d");
				$sSQL="UPDATE sso_domanda SET data_firma_patto='$oggi' WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
			break;

			case 2:
				$sSQL="UPDATE sso_domanda SET data_firma_patto=null WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
			break;
		}

		$flddata_firma_patto=get_db_value("SELECT data_firma_patto FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");

		echo invertidata($flddata_firma_patto,"/","-",2);
	break;



	case "firma_progetto_sia";
		$pazione=get_param("_azione");
		$pidsso_domanda=get_param("_iddomanda");
		$pdata=get_param("_data");
		if (empty_data($pdata))
			$oggi=date("Y-m-d");
		else
			$oggi=invertidata($pdata,"-","/",1);

		switch($pazione)
		{
			case 1:
				//$oggi=date("Y-m-d");
				$sSQL="UPDATE sso_domanda SET data_firma_progetto='$oggi' WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
				break;

			case 2:
				$sSQL="UPDATE sso_domanda SET data_firma_progetto=null WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
				break;
		}

		$flddata_firma_progetto=get_db_value("SELECT data_firma_progetto FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");

		echo invertidata($flddata_firma_progetto,"/","-",2);
		break;

	case "inviato_progetto_inps";
		$pazione=get_param("_azione");
		$pidsso_domanda=get_param("_iddomanda");

		switch($pazione)
		{
			case 1:
				//$oggi=date("Y-m-d");
				$sSQL="UPDATE sso_domanda SET flag_progetto_inviato_inps='1' WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
				break;

			case 2:
				$sSQL="UPDATE sso_domanda SET flag_progetto_inviato_inps='0' WHERE idsso_domanda='$pidsso_domanda'";
				$db->query($sSQL);
				break;
		}

		echo "1";
		
		break;

	case "tipologia_progetto_rei";
		$pidsso_domanda=get_param("_iddomanda");
		$ptipologia=get_param("_val");

		$sSQL="UPDATE sso_domanda SET idtipologia_progetto='$ptipologia' WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);

		echo "1";
		break;



	case "stampa_preassessment":

		include('../librerie/html2pdf.php');

		$fldidsso_domanda=get_param("_domanda");

		$fldidutente=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_domanda WHERE idsso_domanda='$fldidsso_domanda'");
		$flddata_seduta=get_db_value("SELECT data_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldora_seduta=get_db_value("SELECT ora_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldluogo_seduta=get_db_value("SELECT luogo_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
	
		$fldcognome_beneficiario=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
		$fldnome_beneficiario=get_db_value("SELECT nome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");

		$content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">

					<body>
						<center><h3>SCHEDA PRE-ASSESSMENT - '.$fldcognome_beneficiario.' '.$fldnome_beneficiario.'</h3></center><br><br><br>';

		$sSql="SELECT * FROM sso_tbl_sia_categoria where flag_servizio=0 ORDER BY categoria_ordine,idsso_tbl_sia_categoria ";	
		$db->query($sSql);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_tbl_sia_categoria=$db->f("idsso_tbl_sia_categoria");
			$flddescrizione_categoria=$db->f("descrizione");

			$aINFORMAZIONI=db_fill_array("select idsso_tbl_sia_valore,descrizione from sso_tbl_sia_valore where idsso_tbl_sia_categoria='$fldidsso_tbl_sia_categoria' order by valore_ordine");
			if (is_array($aINFORMAZIONI))
			{
				$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';

				reset($aINFORMAZIONI);
				while(list($fldidsso_tbl_sia_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
				{
					$fldidsso_anagrafica_sia_valore=get_db_value("select idsso_anagrafica_sia from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
					$fldidgen_tbl_dizionario_tipocampo=get_db_value("select idgen_tbl_dizionario_tipocampo from sso_tbl_sia_valore where idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");

					if (empty($fldidsso_anagrafica_sia_valore))
						$fldcheckINFORMAZIONE="[  ]";
					else
						$fldcheckINFORMAZIONE="[X]";		    	

					if (empty($fldidgen_tbl_dizionario_tipocampo))
						$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione.'<br>';  						
					else
					{
						$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione;

						switch($fldidgen_tbl_dizionario_tipocampo)	  
						{
							case 1:
								$fldsia_valore=get_db_value("select sia_valore from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
								if(!empty($fldsia_valore))
									$content=$content.' - '.$fldsia_valore.'<br>';
								else
									$content=$content.'<br>';

								break;

							case 2:
								$fldsia_valore=get_db_value("select sia_valore from sso_anagrafica_sia where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore' ");
								$fldvalore=get_db_value("select valore_funzione from sso_tbl_sia_valore where idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");
								$LOV = explode(";", $fldvalore);
								if(sizeof($LOV)%2 != 0) 
								  $array_length = sizeof($LOV) - 1;
								else
								  $array_length = sizeof($LOV);
								reset($LOV);
								for($i = 0; $i < $array_length; $i = $i + 2)
								{
									if ($LOV[$i]==$fldsia_valore)
									{
										if(!empty($LOV[$i]))
											$content=$content.' - '.$LOV[$i+1];
									}
								}    

								$content=$content.'<br>';

								break;	
						}
					}

				}

				$content=$content.'<br>';	  									

			}

			$res=$db->next_record();
		}

		$content=$content.'<br><br><h3>SERVIZI PROPOSTI</h3><br><br>';

		$sSql="SELECT * 
		FROM sso_tbl_sia_categoria 
		WHERE flag_servizio=1 
		ORDER BY categoria_ordine ";	
		$db->query($sSql);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_tbl_sia_categoria=$db->f("idsso_tbl_sia_categoria");
			$flddescrizione_categoria=$db->f("descrizione");	

			$sSQL="SELECT idsso_tbl_sia_valore,descrizione 
			FROM sso_tbl_sia_valore 
			WHERE idsso_tbl_sia_categoria='$fldidsso_tbl_sia_categoria' 
			ORDER BY valore_ordine";
			$aINFORMAZIONI=db_fill_array($sSQL);
			if (is_array($aINFORMAZIONI))
			{
				$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';

				reset($aINFORMAZIONI);
				while(list($fldidsso_tbl_sia_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
				{
					$sSQL="SELECT idsso_anagrafica_sia 
					FROM sso_anagrafica_sia 
					WHERE idsso_anagrafica_utente='$fldidutente' 
					AND idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'";
					$fldidsso_anagrafica_sia_valore=get_db_value($sSQL);
					if (empty($fldidsso_anagrafica_sia_valore))
						$fldcheckINFORMAZIONE="[  ]";
					else
						$fldcheckINFORMAZIONE="[X]";			    	

					$fldcodice_valore=get_db_value("SELECT codice_valore FROM sso_tbl_sia_valore WHERE idsso_tbl_sia_valore='$fldidsso_tbl_sia_valore'");
						$content=$content.$fldcheckINFORMAZIONE.'  '.$fldcodice_valore.' - '.$flddescrizione_informazione.'<br>';  						
				}
				$content=$content.'<br>';	
			}

			$res=$db->next_record();
		}

		$content=$content."</body></html>";

		$pdf=new PDF_HTML();
		$pdf->SetFont('Arial','',12);
		$pdf->AddPage();
		$pdf->WriteHTML($content);
		$pdf->Output();

		break;

	case "stampa_analisipreliminare":

		include('../librerie/html2pdf.php');
		$pflag_front=get_param("_f");
		if (empty($pflag_front))
		{
			$fldidutente_operatore=verifica_utente($chiave);
			$display_note=true;
		}
		else
		{
			$fldidgen_utente=verifica_eutente($chiave);	
			$display_note=false;
		}
		$fldidsso_domanda=get_param("_domanda");


		$fldidutente=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_domanda WHERE idsso_domanda='$fldidsso_domanda'");
		$flddata_seduta=get_db_value("SELECT data_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldora_seduta=get_db_value("SELECT ora_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
		$fldluogo_seduta=get_db_value("SELECT luogo_seduta FROM ".DBNAME_SS.".sso_domanda_seduta WHERE idsso_domanda='$fldidsso_domanda'");
	
		$fldcognome_beneficiario=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
		$fldnome_beneficiario=get_db_value("SELECT nome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");

		$content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">

					<body>
						<center><h3>SCHEDA DI ANALISI PRELIMINARE - '.$fldcognome_beneficiario.' '.$fldnome_beneficiario.'</h3></center><br><br><br>';
		
		$flddata_analisi=get_db_value("SELECT data_analisipreliminare FROM sso_domanda WHERE idsso_domanda='$fldidsso_domanda'");
		$flddata_analisi=invertidata($flddata_analisi,"/","-",2);

		$content=$content.'Data conclusione analisi: '.$flddata_analisi.'<br><br>';

		$sSql="SELECT * FROM sso_tbl_rei_categoria where flag_servizio=0 ORDER BY categoria_ordine,idsso_tbl_rei_categoria ";	
		$db->query($sSql);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_tbl_rei_categoria=$db->f("idsso_tbl_rei_categoria");
			$flddescrizione_categoria=$db->f("descrizione");

			if(($_SERVER["HTTP_HOST"]=="ambitosociale14.socialiccs.it" || $_SERVER["HTTP_HOST"]=="ambitosociale14.sicare.it") && ($fldidsso_tbl_rei_categoria==3 || $fldidsso_tbl_rei_categoria==13))
			{

			}
			else
			{
				$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';
				
				$aINFORMAZIONI=db_fill_array("select idsso_tbl_rei_valore,descrizione from sso_tbl_rei_valore where idsso_tbl_rei_categoria='$fldidsso_tbl_rei_categoria' and flag_visibile=1 order by valore_ordine");
				if (is_array($aINFORMAZIONI))
				{

					reset($aINFORMAZIONI);
					while(list($fldidsso_tbl_rei_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
					{
						$fldidsso_anagrafica_rei_valore=get_db_value("select idsso_anagrafica_rei from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
						$fldidgen_tbl_dizionario_tipocampo=get_db_value("select idgen_tbl_dizionario_tipocampo from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");

						if (empty($fldidsso_anagrafica_rei_valore))
							$fldcheckINFORMAZIONE="[  ]";
						else
							$fldcheckINFORMAZIONE="[X]";		    	

						if (empty($fldidgen_tbl_dizionario_tipocampo))
							$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione.'<br>';  						
						else
						{
							$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione;

							switch($fldidgen_tbl_dizionario_tipocampo)	  
							{
								case 1:
									$fldrei_valore=get_db_value("select rei_valore from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
									if(!empty($fldrei_valore))
										$content=$content.' - '.$fldrei_valore.'<br>';
									else
										$content=$content.'<br>';

									break;

								case 2:
									$fldrei_valore=get_db_value("select rei_valore from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
									$fldvalore=get_db_value("select valore_funzione from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");
									$LOV = explode(";", $fldvalore);
									if(sizeof($LOV)%2 != 0) 
									  $array_length = sizeof($LOV) - 1;
									else
									  $array_length = sizeof($LOV);
									reset($LOV);
									for($i = 0; $i < $array_length; $i = $i + 2)
									{
										if ($LOV[$i]==$fldrei_valore)
										{
											if(!empty($LOV[$i]))
												$content=$content.' - '.$LOV[$i+1];
										}
									}    

									$content=$content.'<br>';

									break;	
							}
						}

					}

					$content=$content.'<br>';	  									
				}

				if ($display_note)
				{
					$fldnote_categoria=get_db_value("select rei_nota from sso_anagrafica_rei where idsso_tbl_rei_categoria='$fldidsso_tbl_rei_categoria' and idsso_anagrafica_utente='$fldidutente'");
					if(!empty($fldnote_categoria))
					{
						$fldnote_categoria=utf8_decode($fldnote_categoria);
						$content.='Note: '.$fldnote_categoria.'<br><br>';
					}
				}
			}		

			$res=$db->next_record();
		}


		$content=$content.'<br><br><h3>CLASSIFICAZIONE</h3><br><br>';


		$aINFORMAZIONI=db_fill_array("select idsso_tbl_rei_valore,descrizione from sso_tbl_rei_valore where idsso_tbl_rei_categoria='21' order by valore_ordine");
		//print_r($aINFORMAZIONI);
		if (is_array($aINFORMAZIONI))
		{
			reset($aINFORMAZIONI);
			while(list($fldidsso_tbl_rei_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
			{
				$fldidsso_anagrafica_rei_valore=get_db_value("select idsso_anagrafica_rei from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
				$fldidgen_tbl_dizionario_tipocampo=get_db_value("select idgen_tbl_dizionario_tipocampo from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");

				if (empty($fldidsso_anagrafica_rei_valore))
					$fldcheckINFORMAZIONE="[  ]";
				else
					$fldcheckINFORMAZIONE="[X]";	    	
				
				if (empty($fldidgen_tbl_dizionario_tipocampo))
					$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione.'<br>';   						
				else
				{
					$content=$content.$fldcheckINFORMAZIONE.'  '.$flddescrizione_informazione;

					switch($fldidgen_tbl_dizionario_tipocampo)	  
					{
						case 1:
							$fldrei_valore=get_db_value("select rei_valore from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
							$content=$content." - ".$fldrei_valore."<br>";
							break;
						case 2:
							$fldrei_valore=get_db_value("select rei_valore from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
							$fldvalore=get_db_value("select valore_funzione from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");
							$fldaltra_funzione=get_db_value("select altra_funzione from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");

							if(!empty($fldaltra_funzione))
							{
								switch($fldaltra_funzione)
								{
									case 1:		//CENTRI PER L'IMPIEGO
										$sSql="select sso_anagrafica_utente.idutente,sso_anagrafica_utente.cognome as descrizione from sso_anagrafica_utente inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente where idsso_tabella_tipologia_ente='20'";
										$db->query($sSql);
										
										$res = $db->next_record();
										while($res)
										{
											$fld_idutente = $db->f('idutente');
											$flddescrizione = $db->f('descrizione');
											
											if($fldrei_valore==$fld_idutente)
												$content=$content." - ".$flddescrizione;

											$res = $db->next_record();
										}

										$content=$content."<br>";
									break;

									case 2:		//ASSISTENTI SOCIALI
										$sSql="SELECT DISTINCT idutente,concat_ws(' ',cognome,nome) as descrizione FROM ".DBNAME_A.".utenti 
										INNER JOIN sso_tbl_responsabile_ente ON utenti.idutente=sso_tbl_responsabile_ente.idsso_responsabile"; 
										$Where=' WHERE utenti.flag_esterno=0 and idtipo=6'; //Regole di visualizzazione per l'operatore loggato	
										$sOrder=' ORDER BY 2';

										$db->query($sSql.$Where.$sOrder);
										
										$res = $db->next_record();
										while($res)
										{
											$fld_idutente = $db->f('idutente');
											$flddescrizione = $db->f('descrizione');
											
											if($fldrei_valore==$fld_idutente)
												$content=$content." - ".$flddescrizione;

											$res = $db->next_record();
										}

										$content=$content."<br>";
									break;
								}
							}
							else
							{
							    $LOV = explode(";", $fldvalore);
							    if(sizeof($LOV)%2 != 0) 
							      $array_length = sizeof($LOV) - 1;
							    else
							      $array_length = sizeof($LOV);
							    reset($LOV);
							    for($i = 0; $i < $array_length; $i = $i + 2)
							    {
							    	if ($LOV[$i]==$fldrei_valore)
										$content=$content." - ".$LOV[$i+1]."<br>";
							    }  
							}
							
						break;	
					}
				}
			}	

			$fldnote_categoria=get_db_value("select rei_nota from sso_anagrafica_rei where idsso_tbl_rei_categoria='21' and idsso_anagrafica_utente='$fldidutente'");
			if(!empty($fldnote_categoria))
			{
				$fldnote_categoria=utf8_decode($fldnote_categoria);
				$content.='<br>Note: '.$fldnote_categoria.'<br><br>';
			}		
		}


		if ($display_note)
		{
			$content=$content.'<br><br><h3>CONCLUSIONI</h3><br><br>';
			$fldnote_categoria=get_db_value("select rei_nota from sso_anagrafica_rei where idsso_tbl_rei_categoria='23' and idsso_anagrafica_utente='$fldidutente'");
			if(!empty($fldnote_categoria))
			{
				$fldnote_categoria=utf8_decode($fldnote_categoria);
				$content.=$fldnote_categoria.'<br><br>';
			}
		}	

		$content.='<br><br>Data: '.date("d/m/Y").'<br><br>';
		$content.='<br>Firma';
		$content.='<br><br><br>______________________________';

		$content.='<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';

		$content.="<h2><b>Informativa sul trattamento dei dati personali (art. 13 D. Lgs. n. 196/2003)</b></h2><br><br>
Il Comune di ................................., in qualita' di Ente capofila dell'Ambito territoriale sociale di ................................., in qualita' di titolari del trattamento dei dati personali, informa che i dati conferiti, i contenuti del presente progetto, comprensivi del Quadro di analisi, predisposto dalla competente Equipe Multidisciplinare, sono prescritti dalle disposizioni vigenti ai fini dell' erogazione e definizione delle attivita' ivi definite, che altrimenti non potrebbe essere attribuite. I dati verranno utilizzati esclusivamente per tale scopo, con modalita' anche informatizzate o telematiche a cio' strettamente funzionali, da parte, oltre che del titolare del trattamento, del Case Manager, dei membri dell'EEMM, del Comune di residenza, designati responsabili del trattamento dei dati personali nonche' degli incaricati del trattamento. I diritti di cui all'art. 7 del D. Lgs. n. 196/2003 (accesso, aggiornamento, cancellazione, trasformazione, ecc.), potranno essere esercitati rivolgendosi al Comune.<br><br>
Firma";

		//INSERIRE CONSENSO TRATTAMENTO DATI

		/*$content=$content.'<br><br><h3>SERVIZI PROPOSTI</h3><br><br>';

		$sSql="SELECT * FROM sso_tbl_rei_categoria where flag_servizio=1 ORDER BY categoria_ordine ";	
		$db->query($sSql);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_tbl_rei_categoria=$db->f("idsso_tbl_rei_categoria");
			$flddescrizione_categoria=$db->f("descrizione");	
			
			$aINFORMAZIONI=db_fill_array("select idsso_tbl_rei_valore,descrizione from sso_tbl_rei_valore where idsso_tbl_rei_categoria='$fldidsso_tbl_rei_categoria' order by valore_ordine");
			if (is_array($aINFORMAZIONI))
			{
				$content=$content.'<h5>'.$flddescrizione_categoria.'</h5><br><br>';

				reset($aINFORMAZIONI);
				while(list($fldidsso_tbl_rei_valore,$flddescrizione_informazione)=each($aINFORMAZIONI))
				{
					$fldidsso_anagrafica_rei_valore=get_db_value("select idsso_anagrafica_rei from sso_anagrafica_rei where idsso_anagrafica_utente='$fldidutente' and idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore' ");
					if (empty($fldidsso_anagrafica_rei_valore))
						$fldcheckINFORMAZIONE="[  ]";
					else
						$fldcheckINFORMAZIONE="[X]";	

					$fldcodice_valore=get_db_value("select codice_valore from sso_tbl_rei_valore where idsso_tbl_rei_valore='$fldidsso_tbl_rei_valore'");

					$content=$content.$fldcheckINFORMAZIONE.'  '.$fldcodice_valore.' - '.$flddescrizione_informazione.'<br>';  						
				}
				$content=$content.'<br>';	
			}

			$res=$db->next_record();
		}
		*/

		$content=$content."</body></html>";

		$pdf=new PDF_HTML();
		$pdf->SetFont('Arial','',12);
		$pdf->AddPage();
		$pdf->WriteHTML($content);
		$pdf->Output();

		break;
		
	case "update_reis":
		$pidsso_domanda=get_param("_domanda");
		$pvalue=get_param("_value");
		$pfield=get_param("_field");		
		$pidsso_progetto=get_param("_progetto");
		if ($pfield=="data_protocollo")
		{
			$pvalue=invertidata($pvalue,"-","/",1);
		}

		if ($pfield=='isee')
		{
			$fldidsso_progetto_graduatoria=get_db_value("select idsso_progetto_graduatoria from sso_progetto_graduatoria where idsso_progetto='$pidsso_progetto' and idsso_tabella_parametro_graduatoria=1");
			//Verifico se esiste
			$fldidsso_domanda_parametro_graduatoria=get_db_value("select idsso_domanda_parametro_graduatoria from sso_domanda_parametro_graduatoria where idsso_domanda='$pidsso_domanda' and idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria'");
			if (empty($fldidsso_domanda_parametro_graduatoria))
				$sSQL="INSERT INTO sso_domanda_parametro_graduatoria (idsso_domanda,idsso_progetto_graduatoria,idsso_tabella_parametro_graduatoria,risposta_testo,numero) VALUES('$pidsso_domanda','$fldidsso_progetto_graduatoria','1','$pvalue','$pvalue')";
			else
				$sSQL="update sso_domanda_parametro_graduatoria set risposta_testo='$pvalue',numero='$pvalue' where idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
			
			
		}
		else
			$sSQL="update sso_domanda set $pfield='$pvalue' where idsso_domanda='$pidsso_domanda'";

		$db->query($sSQL);
		break;
	case "notifica_segretariato":
		//include("../librerie/mail/class.phpmailer.php");
		require("../librerie/mail/lib.mail.php");
		include('../librerie/html2pdf.php');

		$chiave=get_cookieuser();
		$fldidoperatore_online=verifica_utente($chiave);
		$nominativo_operatore_online=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore_online'")." ".get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore_online'");

		$pidoperatore_ssp=get_param("_idoperatore");
		$pid_utente=get_param("_idutente");

		$psend_sintesi=get_param("_sendsintesi");

		$pidsso_anagrafica_relazionescheda=get_param("_idrelazionescheda");

		$nominativo_utente=get_nominativo_utente($pid_utente);

		$nominativo_operatore=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore_ssp' AND idtipo='".IDTIPO_ASSISTENTE_SOCIALE."'")." ".get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore_ssp' AND idtipo='".IDTIPO_ASSISTENTE_SOCIALE."'");
		$fldemail_operatore=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore_ssp' AND idtipo='".IDTIPO_ASSISTENTE_SOCIALE."'");

		$oggi=date("Y-m-d");

		if($psend_sintesi==1)
		{
			$curren_folder='http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/';
			$page='sicare_beneficiario_csintesi_pdf.php';
			$params="?_user=$chiave&profilo=$profilo&menu=$menu&_utente=$pid_utente&flag_save=true";
			$url=$curren_folder.$page.$params;

			$ch=curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			$curl_result=curl_exec($ch); 
			curl_close($ch);

			if(empty($curl_result))
			{
				echo "0|errore nella generazione della sintesi";
				die;
			}
			else
				$filename_sintesi=$curl_result;
		}
		else
			$filename_sintesi="";

		if(!empty($fldemail_operatore))
		{
			$fldinvio="<br>";

			$aEMAIL=array();
			$aEMAIL[0]=$fldemail_operatore;
			$aEMAIL[1]='Invio al SSP';
			$aEMAIL[2]='Buongiorno '.$nominativo_operatore.','.$fldinvio.'Si trasmette la notifica per eventuale presa in carico del beneficiario '.$nominativo_utente;
			$aEMAIL[3]=$filename_sintesi; 		//$curl_result

			$fldresult1=sendMAIL($aEMAIL);

			//unlink($curl_result);
			
			if($fldresult1=="Messaggio inviato correttamente.")
			{
				if(!empty($pidsso_anagrafica_relazionescheda))
				{
					$update="UPDATE sso_anagrafica_relazionescheda SET 
					data_invio_notifica='$oggi', 
					idoperatore_notifica='$fldidoperatore_online' 
					WHERE idsso_anagrafica_relazionescheda='$pidsso_anagrafica_relazionescheda'";
					$db->query($update);

					echo "1|".$oggi."|".$nominativo_operatore_online;
				}
			}
			else
				echo "0|".$fldresult1;
		}
		else
			echo "0|email dell'operatore mancante";
		break;



	case "delete_parente_svama":

		$pidsso_accoglienza_svama_parente=get_param("_idparente");

		$delete="DELETE FROM sso_accoglienza_svama_parenti WHERE idsso_accoglienza_svama_parenti='$pidsso_accoglienza_svama_parente'";
		$db->query($delete);

		echo "1";
		break;



	case 'get_verifiche_reis':
		$pidsso_progetto=get_param("_idprogetto");
		$pidsso_domanda=get_param("_domanda");

		$fldid_autoveicoli1=get_db_value("SELECT idsso_progetto_istruttoria FROM sso_progetto_istruttoria WHERE idsso_progetto='$pidsso_progetto' AND codice_inps='REIS1' ORDER BY sso_progetto_istruttoria.codice_inps"); 
		$fldvalore_istruttoria_autoveicoli1=get_db_value("SELECT valore_istruttoria from sso_domanda_istruttoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_istruttoria='$fldid_autoveicoli1'");

		$fldid_autoveicoli2=get_db_value("SELECT idsso_progetto_istruttoria FROM sso_progetto_istruttoria WHERE idsso_progetto='$pidsso_progetto' AND codice_inps='REIS2' ORDER BY sso_progetto_istruttoria.codice_inps"); 
		$fldvalore_istruttoria_autoveicoli2=get_db_value("SELECT valore_istruttoria FROM sso_domanda_istruttoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_istruttoria='$fldid_autoveicoli2'");

		$fldid_residenza=get_db_value("SELECT idsso_progetto_istruttoria FROM sso_progetto_istruttoria WHERE idsso_progetto='$pidsso_progetto' AND codice_inps='REIS3' ORDER BY sso_progetto_istruttoria.codice_inps"); 
		$fldvalore_istruttoria_residenza=get_db_value("SELECT valore_istruttoria FROM sso_domanda_istruttoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_istruttoria='$fldid_residenza'");
		
		$fldid_cittadinanza=get_db_value("SELECT idsso_progetto_istruttoria FROM sso_progetto_istruttoria WHERE idsso_progetto='$pidsso_progetto' AND codice_inps='REIS4' ORDER BY sso_progetto_istruttoria.codice_inps"); 
		$fldvalore_istruttoria_cittadinanza=get_db_value("SELECT valore_istruttoria FROM sso_domanda_istruttoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_istruttoria='$fldid_cittadinanza'");
	
		echo $fldvalore_istruttoria_autoveicoli1."|".$fldvalore_istruttoria_autoveicoli2."|".$fldvalore_istruttoria_residenza."|".$fldvalore_istruttoria_cittadinanza;
		break;



	case 'get_esito_reis':
		$pidsso_domanda=get_param("_domanda");

		$fldidsso_domanda_esito=get_db_value("SELECT idsso_domanda_esito FROM sso_domanda_esito WHERE idsso_domanda='$pidsso_domanda' AND flag_ultimo=1");
		if(!empty($fldidsso_domanda_esito))
		{
			$sSQL="SELECT * FROM sso_domanda_esito WHERE idsso_domanda_esito='$fldidsso_domanda_esito'";
			$db->query($sSQL);
			$db->next_record();

			$fldcodice_bimestre=$db->f("codice_bimestre");
			$fldesito_controlli_preliminari=$db->f("esito_controlli_preliminari");
			$fldesito_cittadinanza=$db->f("esito_cittadinanza");
			$fldesito_residenza=$db->f("esito_residenza");
			$fldesito_autoveicoli=$db->f("esito_autoveicoli");
			$fldesito_isee=$db->f("esito_isee");
			$fldesito_trattamenti_economici=$db->f("esito_trattamenti_economici");
			$fldesito_altre_prestazioni=$db->f("esito_altre_prestazioni");
			$fldesito_asdi=$db->f("esito_asdi");
			$fldesito_naspi=$db->f("esito_naspi");
			$fldesito_discoll=$db->f("esito_discoll");
			$fldesito_comune=$db->f("esito_comune");
			$fldpunteggio=$db->f("punteggio");
			$fldnumero_componenti=$db->f("numero_componenti");
			$fldimporto_contributo=$db->f("importo_contributo");

			echo $fldcodice_bimestre."|".$fldesito_controlli_preliminari."|".$fldesito_cittadinanza."|".$fldesito_residenza."|".$fldesito_autoveicoli."|".$fldesito_isee."|".$fldesito_trattamenti_economici."|".$fldesito_altre_prestazioni."|".$fldesito_asdi."|".$fldesito_naspi."|".$fldesito_discoll."|".$fldesito_comune."|".$fldpunteggio."|".$fldnumero_componenti."|".$fldimporto_contributo;
		}
		else
			echo "0";
		break;

	case "esito_reis_istruttoria":

		define("ESITO_POSITIVO", '4');
		define("ESITO_NEGATIVO", '6'); 

		$idoperatore=verifica_utente($chiave);

		$pidsso_domanda=get_param("_domanda");
		$pcodice_bimestre=get_param("codice_bimestre");
		$pcontrolli_preliminari=get_param("controlli_preliminari");
		$pcittadinanza=get_param("cittadinanza");
		$presidenza=get_param("residenza");
		$pautoveicoli=get_param("autoveicoli");
		$pisee=get_param("isee");
		$ptrattamenti_economici=get_param("trattamenti_economici");
		$paltre_prestazioni=get_param("altre_prestazioni");
		$pasdi=get_param("asdi");
		$pnaspi=get_param("naspi");
		$pdiscoll=get_param("discoll");
		$pcomune=get_param("comune");
		$ppunteggio=get_param("punteggio");
		$ppunteggio=str_replace(",",".",$ppunteggio);
		$pnumero_componenti=get_param("numero_componenti");
		$pimporto_contributo=get_param("importo_contributo");
		$pimporto_contributo=str_replace(",",".",$pimporto_contributo);

		if(is_numeric($ppunteggio))
		{
			if($pcodice_bimestre>="2017/03")		//DA MAGGIO IL PUNTEGGIO MINIMO È PASSATO DA 45 a 25
			{
				if($fldpunteggio<25)
					$esito=ESITO_NEGATIVO;
				elseif($fldpunteggio>=25)
					$esito=ESITO_POSITIVO;
			}
			else
			{
				if($fldpunteggio<45)
					$esito=ESITO_NEGATIVO;
				elseif($fldpunteggio>=45)
					$esito=ESITO_POSITIVO;
			}

			$ppunteggio=number_format($ppunteggio,2,".","");
		}
		else
		{
			switch($ppunteggio)
			{
				case 'OK':
				$esito=ESITO_POSITIVO;
				break;
				case 'KO':
				$esito=ESITO_NEGATIVO;
				break;
				case 'NP':
				$esito=ESITO_NEGATIVO;
			}
		}

		if($pcontrolli_preliminari=="KO" or $pcittadinanza=="KO" or $pcomune=="KO" or $presidenza=="KO" or $pautoveicoli=="KO" or $pisee=="KO" or $ptrattamenti_economici=="KO" or $paltre_prestazioni=="KO" or $pasdi=="KO" or $pnaspi=="KO" or $pdiscoll=="KO")
			$esito=ESITO_NEGATIVO;

		$fldmax_cod_bimestre=get_db_value("SELECT codice_bimestre FROM sso_domanda_esito WHERE idsso_domanda='$pidsso_domanda' ORDER BY codice_bimestre DESC, idsso_domanda_esito DESC");
		// Aggiorno la domanda solo con il codice bimestre maggiore
		if($pcodice_bimestre>=$fldmax_cod_bimestre)
		{
			// Aggiorno la domanda 
			$sSQL="UPDATE sso_domanda SET 
			idsso_tabella_stato_domanda='$esito', 
			importo_contributo='$fldimporto_contributo' 
			WHERE idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);
		}

		$flddata=date("Y-m-d");
		$fldora=date("H:i:s");

		// Aggiorno l'esito della domanda con lo stesso codice_bimestre, se presente
		$sSQL="SELECT idsso_domanda_esito 
		FROM sso_domanda_esito  
		WHERE idsso_domanda='$pidsso_domanda' 
		AND codice_bimestre='$pcodice_bimestre'";
		$fldidsso_domanda_esito=get_db_value($sSQL);
		if(!empty($fldidsso_domanda_esito))
		{
			$update="UPDATE sso_domanda_esito SET idoperatore='$idoperatore',
			data='$flddata',
			ora='$fldora',
			esito_controlli_preliminari='$pcontrolli_preliminari',
			esito_cittadinanza='$pcittadinanza',
			esito_residenza='$presidenza',
			esito_autoveicoli='$pautoveicoli',
			esito_isee='$pisee',
			esito_trattamenti_economici='$ptrattamenti_economici',
			esito_altre_prestazioni='$paltre_prestazioni',
			esito_asdi='$pasdi',
			esito_naspi='$pnaspi',
			esito_discoll='$pdiscoll',
			esito_comune='$pcomune',
			punteggio='$ppunteggio',
			numero_componenti='$pnumero_componenti',
			importo_contributo='$pimporto_contributo',
			flag_istruttoria=1
			WHERE idsso_domanda_esito='$fldidsso_domanda_esito'";
			$db->query($update);
		}
		else
		{
			$insert="INSERT INTO sso_domanda_esito
			(idoperatore,data, ora, codice_bimestre,
			idsso_domanda,esito_controlli_preliminari,esito_cittadinanza,
			esito_residenza,esito_autoveicoli,esito_isee,
			esito_trattamenti_economici,esito_altre_prestazioni,punteggio,
			numero_componenti,importo_contributo,esito_comune,esito_asdi,esito_naspi,esito_discoll,flag_istruttoria)
			VALUES 
			('$idoperatore','$flddata','$fldora', '$pcodice_bimestre',
			'$pidsso_domanda','$pcontrolli_preliminari','$pcittadinanza',
			'$presidenza','$pautoveicoli','$pisee',
			'$ptrattamenti_economici','$paltre_prestazioni','$ppunteggio',
			'$pnumero_componenti','$pimporto_contributo','$pcomune','$pasdi','$pnaspi','$pdiscoll',1)";
			$db->query($insert);

			$fldidsso_domanda_esito=mysql_insert_id($db->link_id());
		}

		$db2 = new DB_Sql();
		$db2->Database = DATABASE_NAME;
		$db2->User     = DATABASE_USER;
		$db2->Password = DATABASE_PASSWORD;
		$db2->Host     = DATABASE_HOST;

		$db3 = new DB_Sql();
		$db3->Database = DATABASE_NAME;
		$db3->User     = DATABASE_USER;
		$db3->Password = DATABASE_PASSWORD;
		$db3->Host     = DATABASE_HOST;

		//SISTEMA FLAG_ULTIMO PER LA DOMANDA CORRENTE
		  $update="UPDATE sso_domanda_esito SET flag_ultimo=0 WHERE idsso_domanda='$pidsso_domanda'";
		  $db->query($update);

		  $array_id_esito=array();  
		  $sSQL="SELECT idsso_domanda_esito,codice_bimestre FROM sso_domanda_esito WHERE idsso_domanda='$pidsso_domanda'";
		  $db2->query($sSQL);
		  $result=$db2->next_record();
		  $cont_int=0;
		  while($result)
		  {
			$fldidsso_domanda_esito=$db2->f("idsso_domanda_esito");
			$fldcod_bimestre=$db2->f("codice_bimestre");
			$array_id_esito[$fldidsso_domanda_esito]=$fldcod_bimestre;

			$cont_int=$cont_int+1;
			$result=$db2->next_record();
		  }

		  if(is_array($array_id_esito))
		  {   
			$max_arr=max($array_id_esito);
			foreach($array_id_esito as $id_esito => $cod_bim)
			{
			  if($cod_bim==$max_arr)
			  {
				  $update="UPDATE sso_domanda_esito SET flag_ultimo=1 WHERE idsso_domanda_esito='$id_esito'";
				  $db3->query($update);
			  }
			}
		  }

		  $sSQL="SELECT idsso_domanda_esito,codice_bimestre,flag_ultimo FROM sso_domanda_esito WHERE idsso_domanda='$pidsso_domanda'";
		  $db2->query($sSQL);
		  $result=$db2->next_record();
		  $cont_int=0;
		  $counter_ultimi=0;
		  $array_id_esito=array();  
		  
		  while($result)
		  {
			$fldidsso_domanda_esito=$db2->f("idsso_domanda_esito");
			$fldcod_bimestre=$db2->f("codice_bimestre");
			$fldflag_ultimo=$db2->f("flag_ultimo");
			$array_id_esito[$fldidsso_domanda_esito]=$fldcod_bimestre;

			if($fldflag_ultimo==1)
			  $counter_ultimi=$counter_ultimi+1;

			$cont_int=$cont_int+1;
			$result=$db2->next_record();
		  }

		  //SE NON ESISTE UN FLAG ULTIMO OPPURE SE NE ESISTE PIU' DI UNO ALLORA RIFACCIO IL CICLO
		  if($counter_ultimi!=1)
		  {
			$update="UPDATE sso_domanda_esito SET flag_ultimo=0 WHERE idsso_domanda='$pidsso_domanda'";
			$db2->query($update);
			
			if(is_array($array_id_esito))
			{   
			  $max_arr=max($array_id_esito);
			  foreach($array_id_esito as $id_esito => $cod_bim)
			  {
				if($cod_bim==$max_arr)
				{
					$update="UPDATE sso_domanda_esito SET flag_ultimo=1 WHERE idsso_domanda_esito='$id_esito'";
					$db3->query($update);
				}
			  }
			}
		  }

		$db3->closeCONNECTION();  
		echo "1";
		break;



	case "sia_emolumenti":
		$fldidsso_domanda=get_param("_domanda");
		$fldstato=get_param("_stato");

		$update="UPDATE sso_domanda SET flag_emolumenti='$fldstato' WHERE idsso_domanda='$fldidsso_domanda'";
		$db->query($update);

		echo "1";
		break;



	case "esito_mod_punteggio":
		$fldidsso_domanda_esito=get_param("_domanda_esito");
		$fldpunteggio=get_param("punteggio");

		$update="UPDATE sso_domanda_esito 
		SET punteggio='$fldpunteggio' 
		WHERE idsso_domanda_esito='$fldidsso_domanda_esito'";
		$db->query($update);

		echo "1";
		break;



	case "get_anagrafica_isee":
		$pidutente=get_param("_utente");
		$panno=get_param("_anno");

		$fldidsso_anagrafica_isee=get_db_value("SELECT idsso_anagrafica_isee FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$pidutente' AND anno='$panno'");
		if(empty($fldidsso_anagrafica_isee))
			echo "0";
		else
			echo "1";
		break;



	case "assegnaindennita":

		$pidutente=get_param("_utente");
		$pidsso_tbl_indennita=get_param("_indennita");
		$pinserisci=get_param("_inserisci");

		if($pinserisci==1)
		{
			$sSQL="INSERT INTO sso_anagrafica_indennita (idutente,idsso_tbl_indennita) VALUES('$pidutente','$pidsso_tbl_indennita')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="DELETE FROM sso_anagrafica_indennita WHERE idutente='$pidutente' AND idsso_tbl_indennita='$pidsso_tbl_indennita'";
			$db->query($sSQL);
		}
		break;



	case "aggiungi_componente":

		$fldnumero_persone=get_param("_num");
		$fldnumero_persone=$fldnumero_persone+1;

		echo '<tr>
				<th><input type="text" id="nominativo_'.$fldnumero_persone.'" name="nominativo_'.$fldnumero_persone.'" class="form-control input-sm" value="" ></th>
				<th><input type="text" id="gradoparentela_'.$fldnumero_persone.'" name="gradoparentela_'.$fldnumero_persone.'" class="form-control input-sm" value="" ></th>
				<th><input type="text" id="eta_'.$fldnumero_persone.'" name="eta_'.$fldnumero_persone.'" class="form-control input-sm calendar" value="" ></th>
				<th><input type="text" id="attivita_'.$fldnumero_persone.'" name="attivita_'.$fldnumero_persone.'" class="form-control input-sm" value="" ></th>
				<th><select class="form-control input-sm" style="" name="invalidita_'.$fldnumero_persone.'" id="invalidita_'.$fldnumero_persone.'"><option value="2">NO</option><option value="1">SI</option></select></th>
				<th><select class="form-control input-sm" style="" name="convivenza_'.$fldnumero_persone.'" id="convivenza_'.$fldnumero_persone.'"><option value="1">SI</option><option value="2">NO</option></select></th>
			</tr>';
		break;	

	case "aggiungi_equipe_esito_svamdi":

		$fldnumero_equipe=get_param("_num");
		$fldnumero_equipe=$fldnumero_equipe+1;

		echo '<tr>
				<th><input type="text" id="profilo_professionale_'.$fldnumero_equipe.'" name="profilo_professionale_'.$fldnumero_equipe.'" class="form-control input-sm" value="" ></th>
				<th><input type="text" id="struttura_afferenza_'.$fldnumero_equipe.'" name="struttura_afferenza_'.$fldnumero_equipe.'" class="form-control input-sm" value="" ></th>
				<th><input type="text" id="nominativo_'.$fldnumero_equipe.'" name="nominativo_'.$fldnumero_equipe.'" class="form-control input-sm calendar" value="" ></th>
				<th></th>
			</tr>';
		break;

	case "load_quartieri":

		$pid_comune=get_param("_idcomune");

		$sSql="SELECT * FROM ".DBNAME_A.".quartiere WHERE idcomune='$pid_comune'";
		$db->query($sSql);
		$next_record=$db->next_record();
		
		$reply='[';
		$reply.='{"value":"","data":""},';
		while($next_record)
		{
			$fldidquartiere=$db->f("idquartiere");
			$flddescrizione=$db->f("descrizione");

			$reply.='{"value":"'.$flddescrizione.'","data":"'.$fldidquartiere.'"},';

			$next_record = $db->next_record();  
		}
		$reply=rtrim($reply, ",");

		echo $reply.=']';
		break;



	case "duplicaaccreditamento":
		$pidsso_progetto=get_param("_progetto");
		$sPRESTAZIONI=get_param("_prestazioni");
		$descrizione=get_param("_titolo");
		if ($pidsso_progetto && $sPRESTAZIONI)
		{
			$aPRESTAZIONI=explode(",",$sPRESTAZIONI);
			foreach ($aPRESTAZIONI as $key => $value) 
			{
				if ($pWHERE)
					$pWHERE.=" OR ";
				$pWHERE.=" idsso_tbl_prestazione='$value'"; 
			}
			$anno=date("Y");
			$data=date("Y-m-d");
			$data_scadenza=$data;
			$sSQL="INSERT INTO `sso_progetto` ( 
				idsso_tabella_tipo_progetto, descrizione, data_inizio, 
				data_scadenza, ora_scadenza, anno, 
				descrizione_estesa, accreditato_convenzionato, flag_miniaccreditamento
				) VALUES (
				'2', '$descrizione', '$data_scadenza', 
				'$data', '$ora_scadenza', '$anno', 
				'$descrizione', '1', '$flag_miniaccreditamento'
				);";	
			$db->query($sSQL);
			$idsso_progetto_nuovo=mysql_insert_id($db->link_id());

			$sSQL="INSERT INTO sso_progetto_catalogo (idsso_progetto,idsso_tbl_servizio,idsso_tbl_prestazione,proponenti) select '$idsso_progetto_nuovo',idsso_tbl_servizio,idsso_tbl_prestazione,proponenti from sso_progetto_catalogo where idsso_progetto='$pidsso_progetto' and ($pWHERE)";
			$db->query($sSQL);
						
			$sSQL="INSERT INTO sso_progetto_fornitore 
			(idsso_progetto,idsso_tabella_tipologia_ente,flag_raggruppa,flag_consorzia,flag_associa) 
			 select '$idsso_progetto_nuovo',idsso_tabella_tipologia_ente,flag_raggruppa,flag_consorzia,flag_associa from sso_progetto_fornitore where idsso_progetto='$pidsso_progetto'";
			$db->query($sSQL);

			$sSQL="INSERT INTO  sso_progetto_fornitore_informazione (idsso_progetto,idsso_tabella_tipologia_ente, idsso_accreditamento_informazione,flag_obbligatorio) select '$idsso_progetto_nuovo',idsso_tabella_tipologia_ente, idsso_accreditamento_informazione,flag_obbligatorio from sso_progetto_fornitore_informazione where idsso_progetto='$pidsso_progetto'";
			$db->query($sSQL);

			$sSQL="INSERT INTO  sso_progetto_fornitore_informazione (idsso_progetto,idsso_tabella_tipologia_ente, idsso_accreditamento_informazione,flag_obbligatorio) select '$idsso_progetto_nuovo',idsso_tabella_tipologia_ente, idsso_accreditamento_informazione,flag_obbligatorio from sso_progetto_fornitore_informazione where sso_progetto_fornitore_informazione.idsso_progetto='$pidsso_progetto' ";
			$db->query($sSQL);

			$sSQL="INSERT INTO sso_progetto_requisito (idsso_progetto,idsso_tbl_autodichiarazione,descrizione_requisito,descrizione_sostitutiva,flag_allegato,descrizione_allegato,fornitori,flag_costituita,flag_valore,idsso_accreditamento_informazione) select '$idsso_progetto_nuovo',idsso_tbl_autodichiarazione,descrizione_requisito,descrizione_sostitutiva,flag_allegato,descrizione_allegato,fornitori,flag_costituita,flag_valore,idsso_accreditamento_informazione from sso_progetto_requisito where idsso_progetto='$pidsso_progetto'";
			$db->query($sSQL);

			$sSQL="INSERT INTO sso_progetto_requisito_join_catalogo (idsso_progetto,idsso_tbl_autodichiarazione, idsso_tbl_servizio, idsso_tbl_prestazione) select '$idsso_progetto_nuovo',idsso_tbl_autodichiarazione, idsso_tbl_servizio, idsso_tbl_prestazione from sso_progetto_requisito_join_catalogo where idsso_progetto='$pidsso_progetto' and ($pWHERE) ";
			$db->query($sSQL);
		}
		break;
	case "aggiungi_ws":
		$pidente=get_param("_idente");
		$pidtipo=get_param("idtipo");
		$pwsdl=get_param("wsdl");
		$pwsdl=db_string($pwsdl);
		$pcid=get_param("cid");
		$pcid=db_string($pcid);

		$insert="INSERT INTO ".DBNAME_A.".gen_ente_wsdl(idente,idtipo,wsdl,cid) VALUES('$pidente','$pidtipo','$pwsdl','$pcid')";
		$db->query($insert);
		$pidgen_ente_wsdl=mysql_insert_id($db->link_id());

		switch($pidtipo)
		{
			case '1':
				$selected_1='selected';
				$selected_2='';
				$selected_3='';
				$selected_4='';
				$selected_5='';
				break;

			case '2':
				$selected_1='';
				$selected_2='selected';
				$selected_3='';
				$selected_4='';
				$selected_5='';
				break;

			case '3':
				$selected_1='';
				$selected_2='';
				$selected_3='selected';
				$selected_4='';
				$selected_5='';
				break;

			case '4':
				$selected_1='';
				$selected_2='';
				$selected_3='';
				$selected_4='selected';
				$selected_5='';
				break;

			case '5':
				$selected_1='';
				$selected_2='';
				$selected_3='';
				$selected_4='';
				$selected_5='selected';
				break;
		}

		echo '<tr id="idrow_'.$pidgen_ente_wsdl.'">
		<th>
		<select class="form-control input-sm" style="" name="idtipo_'.$pidgen_ente_wsdl.'" id="idtipo_'.$pidgen_ente_wsdl.'" >
		<option value=""></option>
		<option value="1" '.$selected_1.'>ICARO - infoSoggetto</option>
		<option value="2" '.$selected_2.'>PROTO - ProtocolloSoap</option>
		<option value="3" '.$selected_3.'>ANA - AnaWSSCedafFamigliaAllaDataSoap</option>
		<option value="4" '.$selected_4.'>ANA - AnaWSSCedafRicercaSoggettoSoap</option>
		<option value="5" '.$selected_5.'>ANA - AnaWSSCedafSoggettoAllaDataSoap</option>
		</select>    
		</th> 
		<th>
		<input id="wsdl_'.$pidgen_ente_wsdl.'" name="wsdl_'.$pidgen_ente_wsdl.'" type="text" class="form-control input-sm" value="'.$pwsdl.'">      
		</th>
		<th>
		<input id="cid_'.$pidgen_ente_wsdl.'" name="cid_'.$pidgen_ente_wsdl.'" type="text" class="form-control input-sm" value="'.$pcid.'">      
		</th>
        <th><button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_ws" onclick="aggiorna('.$pidgen_ente_wsdl.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button></th>
		<th><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_ws" onclick="elimina('.$pidgen_ente_wsdl.')">&nbsp;<span class="glyphicon glyphicon-trash span-padding" aria-hidden="true"></span></button></th>
		</tr>';
		break;

	case "aggiungi_tipoproto":
		$pidente=get_param("_idente");
		
		$pdescrizione_proto=get_param("descrizione_proto");
		$pdescrizione_proto=db_string($pdescrizione_proto);
		
		$pclassifica_proto=get_param("classifica_proto");
		$pclassifica_proto=db_string($pclassifica_proto);
		
		$ptipodocumento_proto=get_param("tipodocumento_proto");
		$ptipodocumento_proto=db_string($ptipodocumento_proto);
		
		$pmittenteinterno_proto=get_param("mittenteinterno_proto");
		$pmittenteinterno_proto=db_string($pmittenteinterno_proto);
		
		$pcodiceamministrazione_proto=get_param("codiceamministrazione_proto");
		$pcodiceamministrazione_proto=db_string($pcodiceamministrazione_proto);

		$pcodiceaoo_proto=get_param("codiceaoo_proto");
		$pcodiceaoo_proto=db_string($pcodiceaoo_proto);

		$poggetto_proto=get_param("oggetto_proto");
		$poggetto_proto=db_string($poggetto_proto);

		$pmezzoinvio_proto=get_param("mezzoinvio_proto");
		$pmezzoinvio_proto=db_string($pmezzoinvio_proto);

		$pannopratica_proto=get_param("annopratica_proto");
		$pannopratica_proto=db_string($pannopratica_proto);

		$pnumeropratica_proto=get_param("numeropratica_proto");
		$pnumeropratica_proto=db_string($pnumeropratica_proto);

		$pruolo_proto=get_param("ruolo_proto");
		$pruolo_proto=db_string($pruolo_proto);

		$panagrafiche_proto=get_param("anagrafiche_proto");
		$panagrafiche_proto=db_string($panagrafiche_proto);

		$pincaricoa_proto=get_param("incaricoa_proto");
		$pincaricoa_proto=db_string($pincaricoa_proto);

		$selected_s="";
		$selected_n="";
		$selected_f="";
		switch($panagrafiche_proto)
		{
			case "S":
				$selected_s="selected";
			break;

			case "N":
				$selected_n="selected";
			break;

			case "F":
				$selected_f="selected";
			break;
		}

		$insert="INSERT INTO ".DBNAME_A.".gen_ente_tipo_protocollo(idente,descrizione_proto,classifica_proto,tipodocumento_proto,mittenteinterno_proto,codiceamministrazione_proto,codice_aoo,oggetto_proto,mezzoinvio_proto,annopratica_proto,numeropratica_proto,ruolo_proto,anagrafiche_proto,incaricoa_proto) VALUES('$pidente','$pdescrizione_proto','$pclassifica_proto','$ptipodocumento_proto','$pmittenteinterno_proto','$pcodiceamministrazione_proto','$pcodiceaoo_proto','$poggetto_proto','$pmezzoinvio_proto','$pannopratica_proto','$pnumeropratica_proto','$pruolo_proto','$panagrafiche_proto','$pincaricoa_proto')";
		$db->query($insert);
		$pidgen_ente_tipo_protocollo=mysql_insert_id($db->link_id());

		echo '<tr id="idrow_proto_'.$pidgen_ente_tipo_protocollo.'">
		<th>
			<button type="button" class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#expand_proto_'.$pidgen_ente_tipo_protocollo.'"><i class="fa fa-pencil"></i></button>
		</th>
		<th>
		'.$pdescrizione_proto.' 
		</th>
		<th>
		'.$poggetto_proto.'  
		</th>
		<th>
		'.$pclassifica_proto.'     
		</th>
		<th>
		'.$pruolo_proto.'     
		</th>
		<th>
		'.$pmezzoinvio_proto.'       
		</th>
		<th>
		'.$ptipodocumento_proto.'      
		</th>
		<th>
		'.$pmittenteinterno_proto.'       
		</th>
		<th>
		'.$pincaricoa_proto.'      
		</th>
		<th>
		'.$panagrafiche_proto.'
		</th>
		<th>
		'.$pannopratica_proto.' 
		</th>
		<th>
		'.$pnumeropratica_proto.'      
		</th>
		<th>
		'.$pcodiceamministrazione_proto.'       
		</th>
		<th>
		'.$pcodiceaoo_proto.'      
		</th>
		<th><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_proto" onclick="elimina_proto('.$pidgen_ente_tipo_protocollo.')">&nbsp;<span class="glyphicon glyphicon-trash span-padding" aria-hidden="true"></span></button></th>
		</tr>

		<tr id="idrow_proto_expand_'.$pidgen_ente_tipo_protocollo.'">
	        <th colspan="15">
	            <div class="accordian-body collapse" id="expand_proto_'.$pidgen_ente_tipo_protocollo.'">

	                <table class="table_dett">
	                    <tr>
	                        <td><b>Descrizione</b></td>
	                        <td>
	                            <input id="descrizione_'.$pidgen_ente_tipo_protocollo.'" name="descrizione_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pdescrizione_proto.'">
	                        </td>
	                    <tr>
	                    <tr>
	                        <td><b>Oggetto</b></td>
	                        <td>
	                            <input id="oggetto_'.$pidgen_ente_tipo_protocollo.'" name="oggetto_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$poggetto_proto.'"> 
	                        </td>
	                    <tr>
	                    <tr>
	                        <td><b>Classifica</b></td>
	                        <td>
	                            <input id="classifica_'.$pidgen_ente_tipo_protocollo.'" name="classifica_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pclassifica_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Ruolo</b></td>
	                        <td>
	                            <input id="ruolo_'.$pidgen_ente_tipo_protocollo.'" name="ruolo_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pruolo_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Mezzo invio</b></td>
	                        <td>
	                            <input id="mezzoinvio_'.$pidgen_ente_tipo_protocollo.'" name="mezzoinvio_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pmezzoinvio_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Tipo documento</b></td>
	                        <td>
	                            <input id="tipodocumento_'.$pidgen_ente_tipo_protocollo.'" name="tipodocumento_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$ptipodocumento_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Mittente interno</b></td>
	                        <td>
	                            <input id="mittenteinterno_'.$pidgen_ente_tipo_protocollo.'" name="mittenteinterno_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pmittenteinterno_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>In carico a</b></td>
	                        <td>
	                            <input id="incaricoa_'.$pidgen_ente_tipo_protocollo.'" name="incaricoa_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pincaricoa_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Aggiorna anagrafiche</b></td>
	                        <td>
	                            <select class="form-control input-sm" style="" name="anagrafiche_'.$pidgen_ente_tipo_protocollo.'" id="anagrafiche_'.$pidgen_ente_tipo_protocollo.'" >
	                                <option value="S" '.$selected_s.'>S</option>
	                                <option value="N" '.$selected_n.'>N</option>
	                                <option value="F" '.$selected_f.'>F</option>
	                            </select> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Anno pratica</b></td>
	                        <td>
	                            <input id="annopratica_'.$pidgen_ente_tipo_protocollo.'" name="annopratica_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pannopratica_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Numero pratica</b></td>
	                        <td>
	                            <input id="numeropratica_'.$pidgen_ente_tipo_protocollo.'" name="numeropratica_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pnumeropratica_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Codice amministrazione</b></td>
	                        <td>
	                            <input id="codiceamministrazione_'.$pidgen_ente_tipo_protocollo.'" name="codiceamministrazione_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pcodiceamministrazione_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td><b>Codice AOO</b></td>
	                        <td>
	                            <input id="codiceaoo_'.$pidgen_ente_tipo_protocollo.'" name="codiceaoo_'.$pidgen_ente_tipo_protocollo.'" type="text" class="form-control input-sm" value="'.$pcodiceaoo_proto.'"> 
	                        </td>
	                    <tr>

	                    <tr>
	                        <td colspan="2" class="text-right">
	                            <button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_proto" onclick="aggiorna_proto('.$pidgen_ente_tipo_protocollo.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button>
	                        </td>
	                    </tr>
	                </table>
	            </div>
	        </th>
    	</tr>';
		break;


	case "elimina_ws":
		$pidgen_ente_wsdl=get_param("_id");

		$delete="DELETE FROM ".DBNAME_A.".gen_ente_wsdl WHERE idgen_ente_wsdl='$pidgen_ente_wsdl'";
		$db->query($delete);
		echo "1";
		break;

	case "elimina_tipoproto":
		$pidgen_ente_tipo_protocollo=get_param("_id");

		$delete="DELETE FROM ".DBNAME_A.".gen_ente_tipo_protocollo WHERE idgen_ente_tipo_protocollo='$pidgen_ente_tipo_protocollo'";
		$db->query($delete);
		echo "1";
		break;


	case "loadindirizzi_mantova":

		$db->query("select * from ".DBNAME_A.".gen_stradario_mantova");
		$next_record=$db->next_record();
			
		$response=array();
		while($next_record)
		{
			$fldsicra_codvia=$db->f("sicra_codvia");
			$fldtoponimo = $db->f("toponimo");
			$flddescrizione = $db->f("descrizione");
			$flddatanascita = $db->f("data_nascita");
				
			$fldsicra_codvia = removeslashes($fldsicra_codvia);
			$fldtoponimo = removeslashes($fldtoponimo);
			$flddescrizione = removeslashes($flddescrizione);

			$flddata=$fldtoponimo.' '.$flddescrizione;
			$flddata=utf8_decode($flddata);

			$record=array();
			$record['data']=$fldsicra_codvia;
			$record['value']=$flddata;
			array_push($response, $record);

			$next_record = $db->next_record();  
		}
		
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;



	case "esistecivico":
		$fldcivico=get_param("_civico");
		$fldcodice_via=get_param("_codicevia");
		echo esisteCIVICO('E897',$fldcodice_via,$fldcivico);
		break;



	case "distanzaSIT":
		$cod_civico_from=get_param("_civico");
		$cod_via_from=get_param("_codicevia");
		$fldidsso_istituto=get_param("_istituto");

		$aISTITUTI=db_fill_array("select idsso_istituto,concat_ws(',',sicra_codicevia,sicra_civico) from sso_istituto");
		$aISTITUTISIT=array();

		$string_distance="";

		if ($cod_via_from)
		{	
			foreach ($aISTITUTI as $key => $value) 
			{
				list($cod_via_to,$cod_civico_to)=explode(",",$value);
				$distanzasit=getDISTANCE('E897',$cod_via_from,$cod_civico_from,$cod_via_to,$cod_civico_to);
				if(empty($distanzasit))
					$distanzasit="0";
				$string_distance.=$key.";".$distanzasit."|";
			}

			echo $string_distance;
		}
		break;



	case "email_assistente":
		$fldidoperatore=get_param("_idoperatore");
		echo get_db_value("select email from ".DBNAME_A.".utenti where idutente='$fldidoperatore'");
		break;



	case "notifica_rei_assegnapreliminare":
		include("../librerie/mail/class.phpmailer.php");
		include("../librerie/mail/lib.mail.php");
		include('../librerie/html2pdf.php');

		$pidsso_domanda=get_param("_domanda");
		$fldidoperatore=get_param("_idoperatore");

		$fldcognome=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");
		$fldnome=get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");
		$fldemail=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");

		$poggetto=get_param("oggetto");
		$poggetto=base64_decode($poggetto);

		$ptesto=get_param("testo");
		$ptesto=base64_decode($ptesto);

		$ptesto=str_replace("{Nome}",$fldnome,$ptesto);
		$ptesto=str_replace("{Cognome}",$fldcognome,$ptesto);

		$ptesto=nl2br($ptesto);

		if($_SERVER["HTTP_HOST"]=="37.206.216.84" || $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it")
		{
			$request_rest = curl_init();
			curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_mail_mc_equipe.php');

			$params=array();
			$params['email']=$fldemail;
			$params['testo']=$ptesto;
			$params['oggetto']=$poggetto;

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
			$aEMAIL[1]=$poggetto;
			$aEMAIL[2]=$ptesto;
			$aEMAIL[3]="";
			$fldresult=sendMAIL($aEMAIL);
		}

		if($fldresult=="Messaggio inviato correttamente.")
		{
			$data_invio=date("Y-m-d");
			$ora_invio=date("H:i:s");
			$ora_invio=substr($ora_invio, 0, 5);
			$update="update ".DBNAME_SS.".sso_domanda set idassistente_preliminare='$fldidoperatore', data_invio_preliminare='$data_invio', ora_invio_preliminare='$ora_invio' where idsso_domanda='$pidsso_domanda'";
			$db->query($update);
			echo "1|<b>Notifica inviata il ".date("d/m/Y")." alle ".$ora_invio."</b>";
		}
		else
			echo "0";
		break;

	case "domandaassegna":
		include("../librerie/mail/class.phpmailer.php");
		include("../librerie/mail/lib.mail.php");
		include('../librerie/html2pdf.php');

		$pidsso_domanda=get_param("_domanda");
		$fldidoperatore=get_param("_idoperatore");
		$pdomande=get_param("_domande");

		$fldcognome=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");
		$fldnome=get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");
		$fldemail=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$fldidoperatore'");

		$poggetto=get_param("oggetto");
		$poggetto=base64_decode($poggetto);

		$ptesto=get_param("testo");
		$ptesto=base64_decode($ptesto);

		$ptesto=str_replace("{Nome}",$fldnome,$ptesto);
		$ptesto=str_replace("{Cognome}",$fldcognome,$ptesto);

		$ptesto=nl2br($ptesto);

		$aEMAIL=array();
		$aEMAIL[0]=$fldemail;
		$aEMAIL[1]=$poggetto;
		$aEMAIL[2]=$ptesto;
		$aEMAIL[3]="";
		$fldresult=sendMAIL($aEMAIL);

		$data_invio=date("Y-m-d");
		$ora_invio=date("H:i:s");
		$ora_invio=substr($ora_invio, 0, 5);

		if (empty($pdomande))
		{
			$update="update ".DBNAME_SS.".sso_domanda set idassistente_preliminare='$fldidoperatore',idoperatore='$fldidoperatore',idassistente='$fldidoperatore',data_invio_preliminare='$data_invio', ora_invio_preliminare='$ora_invio',idsso_tabella_stato_domanda=2 where idsso_domanda='$pidsso_domanda'";
			$db->query($update);			
		}
		else
		{
			$aDOMANDE=explode(",",$pdomande);
			foreach ($aDOMANDE as $key => $idsso_domanda) 
			{
				$update="update ".DBNAME_SS.".sso_domanda set idassistente_preliminare='$fldidoperatore',idoperatore='$fldidoperatore',idassistente='$fldidoperatore',data_invio_preliminare='$data_invio', ora_invio_preliminare='$ora_invio',idsso_tabella_stato_domanda=2 where idsso_domanda='$idsso_domanda'";
				$db->query($update);			
			}
		}
		echo "1|<b>Notifica inviata il ".date("d/m/Y")." alle ".$ora_invio."</b>";
		break;
	case "updatecheckversione":
		$pidgen_procedura=get_param("_p");
		$pprocedura_versione=get_param("_v");
		$pvalue=get_param("_value");
		$pidgen_utente=verifica_utente($chiave);
		//Verifico se esiste check
		$idgen_utente_versione=get_db_value("SELECT idgen_utente_versione FROM ".DBNAME_A.".gen_utente_versione WHERE idgen_procedura='$pidgen_procedura' and idgen_utente='$pidgen_utente' and procedura_versione='$pprocedura_versione'");
		if ($idgen_utente_versione)
			$sSQL="update ".DBNAME_A.".gen_utente_versione set check_versione='$idgen_utente_versione' where idgen_utente_versione='$idgen_utente_versione'";
		else
			$sSQL="insert into ".DBNAME_A.".gen_utente_versione (idgen_procedura,idgen_utente,procedura_versione,check_versione) values('$pidgen_procedura','$pidgen_utente','$pprocedura_versione','$pvalue')";
		$db->query($sSQL);
		break;
	


	case "load_tabella_isee":
		$pidutente=get_param("_id");
		$html_table='<table data-toggle="table" class="table table-hover table-condensed" id="table_isee" name="table_isee">
					<thead>
					  <tr class="default">
						  <th style="width: 10%;" class="intestazioneTabella text-info">ISEE non dichiarato</th>
						  <th style="width: 10%;" class="intestazioneTabella text-info">Anno</th>
						  <th style="width: 15%;" class="intestazioneTabella text-info">Valore</th>
						  <th style="width: 25%;" class="intestazioneTabella text-info">Numero DSU</th>
						  <th style="width: 15%;" class="intestazioneTabella text-info">Data DSU</th>
						  <th style="width: 20%;" class="intestazioneTabella text-info">Data dichiarazione</th>
						  <th style="width: 5%;" class="intestazioneTabella text-info"></th>
					  </tr>  
					</thead>  
					<tbody>';

		$sSQL="SELECT * FROM sso_anagrafica_isee WHERE idsso_anagrafica_utente='$pidutente' ORDER BY anno";
		$db->query($sSQL);
		$res=$db->next_record();
		$counter=1;
		$aDSU_DICHIARATO=array();
		while($res)
		{
			$fldidsso_anagrafica_isee=$db->f("idsso_anagrafica_isee");
			$fldidutente=$db->f("idsso_anagrafica_utente");
			$fldvalore_isee=$db->f("valore_isee");
			if($fldvalore_isee!=null)
				$fldvalore_isee=number_format($fldvalore_isee,2,',','');

			$fldvalore_isee_accertato=$db->f("valore_isee_accertato");
			$fldisee_non_dichiarato=$db->f("isee_non_dichiarato");
			if($fldisee_non_dichiarato==1)
				$checked_non_dichiarato="checked";
			else
				$checked_non_dichiarato="";

			$fldanno=$db->f("anno");
			$flddata_dsu=$db->f("data_dsu");
			$fldnumero_dsu=$db->f("numero_dsu");
			$aDSU_DICHIARATO[]=$fldnumero_dsu;
			$flddata_dichiarazione=$db->f("data_dichiarazione");
			$flddata_dsu=invertidata($flddata_dsu,"/","-",2);
		
			$html_table.='<tr id="'.$fldidsso_anagrafica_isee.'">
				<td id="isee_non_dichiarato"><center><input type="checkbox" id="isee_non_dichiarato" name="isee_non_dichiarato" '.$checked_non_dichiarato.' disabled></center></td>
				<td id="anno">'.$fldanno.'</td>
				<td id="valore_isee">'.$fldvalore_isee.'</td>
				<td id="numero_dsu">'.$fldnumero_dsu.'</td>
				<td id="data_dsu">'.$flddata_dsu.'</td>
				<td id="data_dichiarazione">'.invertidata($flddata_dichiarazione,"/","-",2).'</td>					
				<td><button type="button" onClick="modificaISEE('.$fldidsso_anagrafica_isee.')" '.$accesso_modifica.' id="modifica" name="modifica" value="true" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-pencil span-padding" aria-hidden="true"></span> Modifica</button></td>
				</tr>';

			$counter++;
			$res=$db->next_record();
		}

		$html_table.='</tbody>
				</table>';

		echo $html_table;
		break;



	case "elimina_cruscotto":
		$pidsso_cruscotto_flusso=get_param("_id");

		$delete="delete from sso_marche_cruscotto where idsso_marche_cruscotto='$pidsso_cruscotto_flusso'";
		$db->query($delete);

		$delete="delete from sso_marche_cruscotto_invio where idsso_marche_cruscotto='$pidsso_cruscotto_flusso'";
		$db->query($delete);

		echo "1";
		break;

	case "notificaap":								//analisi preliminare rei		
		require("../librerie/mail/lib.mail.php");
		require_once '../librerie/excel/Classes/PHPExcel/IOFactory.php';
		require_once '../librerie/excel/Classes/PHPExcel.php';

		$chiave=get_cookieuser();
		$user=verifica_utente($chiave);		
		$pidoperatore=get_param("_value");
		$pidutente=get_param("_utente");
		$pparametro=get_param("_p");
		$beneficiario=new Beneficiario($pidutente);
		$cognome=$beneficiario->cognome;
		$nome=$beneficiario->nome;
		$cf=$beneficiario->codicefiscale;
		$recapiti=$beneficiario->recapito;
		$email=$beneficiaio->email;

		$operatore=new Utente($user);
		$nome_user=$operatore->nome;
		$cognome_user=$operatore->cognome;
		$telefono_user=$operatore->telefono;
		$email_user=$operatore->email;

		switch($pparametro)
		{
			case 107:
			case 96:	//mrei
				$nominativo_operatore=anagrafica_name_servizi($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM sso_anagrafica_utente WHERE idutente='$pidoperatore'");
				break;
			case 108:
			case 97:	//mrei
				$nominativo_operatore=anagrafica_name($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");
				break;
			case 98:	//mrei
				$nominativo_operatore=anagrafica_name_servizi($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM sso_anagrafica_utente WHERE idutente='$pidoperatore'");
				break;
		}

		$nominativo_beneficiario=anagrafica_name_servizi($pidutente);

		$oggi=date("Y-m-d");

		if(!empty($fldemail_operatore))
		{
			$fldinvio="<br>";

			$excel2 = PHPExcel_IOFactory::createReader('Excel2007');
			$excel2 = $excel2->load('../documenti/sicare/cpirei.xlsx');
			$excel2->setActiveSheetIndex(0);
			$excel2->getActiveSheet()->setCellValue('B5', $cognome);  
			$excel2->getActiveSheet()->setCellValue('C5', $nome);  
			$excel2->getActiveSheet()->setCellValue('D5', $cf);  
			$excel2->getActiveSheet()->setCellValue('E5', $recapiti);  
			$excel2->getActiveSheet()->setCellValue('F5', $email);  
			$excel2->getActiveSheet()->setCellValue('G5', date("d.m.Y"));  
			$excel2->getActiveSheet()->setCellValue('H5', "x");  
			$excel2->getActiveSheet()->setCellValue('I5', "nessuna");  
			$excel2->getActiveSheet()->setCellValue('J5', $nome_user." ".$cognome_user);  
			$excel2->getActiveSheet()->setCellValue('K5', $telefono_user);  
			$excel2->getActiveSheet()->setCellValue('L5', $email_user);  

			$objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel2007');
			$objWriter->save('../documenti/sicare/cpirei'.$pidutente.'.xlsx');			

			$oggetto='Notifica assegnazione analisi preliminare';
			$testo='Buongiorno '.$nominativo_operatore.','.$fldinvio.'Si trasmette la notifica dell\'assegnazione dell\'analisi preliminare del beneficiario <b>'.$nominativo_beneficiario.'</b>';

			if($_SERVER["HTTP_HOST"]=="37.206.216.84" || $_SERVER["HTTP_HOST"]=="172.30.0.87"  || $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it" || $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it")
			{
				$request_rest = curl_init();
				curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_notifica_ap.php');

				$params=array();
				$params['oggetto']=$oggetto;
				$params['testo']=$testo;
				$params['email']=$fldemail_operatore;

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
				$fldresult1=curl_exec($request_rest);		//invio la richiesta e ricevo la risposta
				//print_r($result);
				// close the session
				curl_close($request_rest);		//chiudo la sessione
			}
			else
			{
				$aEMAIL=array();
				$aEMAIL[0]=$fldemail_operatore;
				$aEMAIL[1]=$oggetto;
				$aEMAIL[2]=$testo;
				$aEMAIL[3]='../documenti/sicare/cpirei'.$pidutente.'.xlsx'; 		

				$fldresult1=sendMAIL($aEMAIL);
			}

			if($fldresult1=="Messaggio inviato correttamente.")
			{
				$fldidsso_anagrafica_rei_analisi=get_db_value("select idsso_anagrafica_rei_analisi from sso_anagrafica_rei_analisi where idsso_anagrafica_utente='$pidutente'");
				if(!empty($fldidsso_anagrafica_rei_analisi))
				{
					$update="UPDATE sso_anagrafica_rei_analisi SET 
					analisi_notifica='$oggi', 
					analisi_operatore='$user' 
					WHERE idsso_anagrafica_rei_analisi='$fldidsso_anagrafica_rei_analisi'";
					$db->query($update);
				}
				else
				{
					$insert="INSERT INTO sso_anagrafica_rei_analisi (idsso_anagrafica_utente,analisi_notifica,analisi_operatore) VALUES('$pidutente','$oggi','$user')";
					$db->query($insert);
				}

				echo "1|".$oggi."|".$nominativo_operatore;
			}
			else
				echo "0|".$fldresult1;
		}
		else
			echo "0|email dell'operatore mancante";
		break;

	case "notificaap_integrazione":								//analisi preliminare rei integrazione		
		require("../librerie/mail/lib.mail.php");

		$chiave=get_cookieuser();
		$user=verifica_utente($chiave);		
		$pidoperatore=get_param("_value");
		$pidutente=get_param("_utente");
		$pparametro=get_param("_p");
		$beneficiario=new Beneficiario($pidutente);
		$cognome=$beneficiario->cognome;
		$nome=$beneficiario->nome;
		$cf=$beneficiario->codicefiscale;
		$telefono=$beneficiario->telefono;
		$email=$beneficiaio->email;

		$operatore=new Utente($user);
		$nome_user=$operatore->nome;
		$cognome_user=$operatore->cognome;
		$telefono_user=$operatore->telefono;
		$email_user=$operatore->email;

		switch($pparametro)
		{
			case 107:
			case 96:	//mrei
				$nominativo_operatore=anagrafica_name_servizi($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM sso_anagrafica_utente WHERE idutente='$pidoperatore'");
				break;
			case 108:
			case 97:	//mrei
				$nominativo_operatore=anagrafica_name($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");
				break;
			case 98:	//mrei
				$nominativo_operatore=anagrafica_name_servizi($pidoperatore);
				$fldemail_operatore=get_db_value("SELECT email FROM sso_anagrafica_utente WHERE idutente='$pidoperatore'");
				break;
		}

		$nominativo_beneficiario=anagrafica_name_servizi($pidutente);

		$oggi=date("Y-m-d");

		if(!empty($fldemail_operatore))
		{	
			$fldinvio="<br>";
			$oggetto='Notifica integrazione analisi preliminare';
			$testo='Buongiorno '.$nominativo_operatore.','.$fldinvio.'Si comunica l\'integrazione dell\'analisi preliminare del beneficiario <b>'.$nominativo_beneficiario.'</b>';

			if($_SERVER["HTTP_HOST"]=="37.206.216.84" || $_SERVER["HTTP_HOST"]=="172.30.0.87"  || $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it" || $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it")
			{
				$request_rest = curl_init();
				curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_notifica_ap.php');

				$params=array();
				$params['oggetto']=$oggetto;
				$params['testo']=$testo;
				$params['email']=$fldemail_operatore;

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
				$fldresult1=curl_exec($request_rest);		//invio la richiesta e ricevo la risposta
				//print_r($result);
				// close the session
				curl_close($request_rest);		//chiudo la sessione
			}
			else
			{
				$aEMAIL=array();
				$aEMAIL[0]=$fldemail_operatore;
				$aEMAIL[1]=$oggetto;
				$aEMAIL[2]=$testo;
				$aEMAIL[3]=''; 		

				$fldresult1=sendMAIL($aEMAIL);
			}

			if($fldresult1=="Messaggio inviato correttamente.")
			{
				echo "1|".$oggi."|".$nominativo_operatore;
			}
			else
				echo "0|".$fldresult1;
		}
		else
			echo "0|email dell'operatore mancante";
		break;

	case "notificaap_integrazione_equipe":
		require("../librerie/mail/lib.mail.php");

		$chiave=get_cookieuser();
		$user=verifica_utente($chiave);		
		$pidsso_domanda=get_param("_domanda");

		$counter_inviati=0;
		$sSQL="SELECT idgen_utente FROM sso_domanda_equipe WHERE idsso_domanda='$pidsso_domanda'";
		$db->query($sSQL);
		$res=$db->next_record();
		while($res)
		{
			$fldidgen_utente=$db->f("idgen_utente");
			$operatore=new Utente($fldidgen_utente);
			$fldemail_operatore=$operatore->email;

			if(!empty($fldemail_operatore))
			{	
				$fldinvio="<br>";
				$oggetto='Notifica integrazione analisi preliminare';
				$testo='Buongiorno '.$nominativo_operatore.','.$fldinvio.'Si comunica l\'integrazione dell\'analisi preliminare del beneficiario <b>'.$nominativo_beneficiario.'</b>';

				if($_SERVER["HTTP_HOST"]=="37.206.216.84" || $_SERVER["HTTP_HOST"]=="172.30.0.87"  || $_SERVER["HTTP_HOST"]=="sociali.comune.macerata.it" || $_SERVER["HTTP_HOST"]=="mense.comune.macerata.it")
				{
					$request_rest = curl_init();
					curl_setopt($request_rest, CURLOPT_URL, 'https://demo.sicare.it/sicare/send_notifica_ap.php');

					$params=array();
					$params['oggetto']=$oggetto;
					$params['testo']=$testo;
					$params['email']=$fldemail_operatore;

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
					$fldresult1=curl_exec($request_rest);		//invio la richiesta e ricevo la risposta
					//print_r($result);
					// close the session
					curl_close($request_rest);		//chiudo la sessione
				}
				else
				{
					$aEMAIL=array();
					$aEMAIL[0]=$fldemail_operatore;
					$aEMAIL[1]=$oggetto;
					$aEMAIL[2]=$testo;
					$aEMAIL[3]=''; 		

					$fldresult1=sendMAIL($aEMAIL);
				}

				if($fldresult1=="Messaggio inviato correttamente.")
					$counter_inviati++;
			}

			$res=$db->next_record();
		}

		echo $counter_inviati;

	break;

	case "loadbudgetprestazione":
		$pidsso_tbl_rei_progetto_valore=get_param("_v");
		$pidsso_progetto=get_param("_progetto");	
		$sSQL="select idsso_tbl_prestazione,concat_ws(' ',codice_prestazione,descrizione) from sso_tbl_prestazione 
						WHERE codice_prestazione IS NOT NULL 
						AND (idsso_tbl_servizio='501' or idsso_tbl_servizio='502') 
						AND codice_prestazione!='A1.01' 
						AND codice_prestazione!='A1.02' 
						AND codice_prestazione!='A1.03' 
						AND codice_prestazione!='A1.24' 
						AND codice_prestazione<'A2.15'  
						ORDER BY codice_prestazione ";
		$aSERVIZI=db_fill_array($sSQL);    
		$sSERVIZI='<option value="0" ></option>';
		foreach ($aSERVIZI as $idsso_tbl_prestazione => $descrizione) 
		{
			$descrizione=str_replace("\n", " ", $descrizione);
			$descrizione=str_replace("'", " ", $descrizione);
			$descrizione=str_replace("/", " ", $descrizione);
			$descrizione=str_replace("  ", " ", $descrizione);
			$descrizione=str_replace("  ", " ", $descrizione);
			$descrizione=trim(db_string($descrizione));
			$fldidsso_rei_budget=get_db_value("select idsso_rei_budget from sso_rei_budget where idsso_tbl_prestazione='$idsso_tbl_prestazione' and idsso_progetto='$pidsso_progetto' and idsso_tbl_rei_progetto_valore='$pidsso_tbl_rei_progetto_valore'");
			if ($fldidsso_rei_budget)
				$sSERVIZI.='<option value="'.$idsso_tbl_prestazione.'" selected >'.$descrizione.'</option>';
			else
				$sSERVIZI.='<option value="'.$idsso_tbl_prestazione.'" >'.$descrizione.'</option>';
		}	
		echo $sSERVIZI;					
		break;
	case "budgetprestazione":
		$pidsso_tbl_prestazione=get_param("_p");
		$pidsso_tbl_rei_progetto_valore=get_param("_v");
		$pidsso_progetto=get_param("_progetto");
		$fldidsso_rei_budget=get_db_value("select idsso_rei_budget from sso_rei_budget where idsso_tbl_rei_progetto_valore='$pidsso_tbl_rei_progetto_valore' and idsso_progetto='$pidsso_progetto' ");
		if (!$fldidsso_rei_budget)
		{
			$sSQL="INSERT INTO sso_rei_budget (idsso_tbl_rei_progetto_valore,idsso_tbl_prestazione,idsso_progetto) VALUES ('$pidsso_tbl_rei_progetto_valore','$pidsso_tbl_prestazione','$pidsso_progetto')";
			$db->query($sSQL);
		}
		else
		{

			$sSQL="update sso_rei_budget set idsso_tbl_prestazione='$pidsso_tbl_prestazione' where idsso_rei_budget='$fldidsso_rei_budget' ";
			$db->query($sSQL);
		}

		break;
	case "loadbudgetprestazionesia":
		$pidsso_tbl_sia_progetto_valore=get_param("_v");
		$pidsso_progetto=get_param("_progetto");	
		$sSQL="select idsso_tbl_prestazione,concat_ws(' ',codice_prestazione,descrizione) from sso_tbl_prestazione 
						WHERE codice_prestazione IS NOT NULL 
						AND (idsso_tbl_servizio='501' or idsso_tbl_servizio='502') 
						AND codice_prestazione!='A1.01' 
						AND codice_prestazione!='A1.02' 
						AND codice_prestazione!='A1.03' 
						AND codice_prestazione!='A1.24' 
						AND codice_prestazione<'A2.15'  
						ORDER BY codice_prestazione ";
		$aSERVIZI=db_fill_array($sSQL);    
		$sSERVIZI='<option value="0" ></option>';
		foreach ($aSERVIZI as $idsso_tbl_prestazione => $descrizione) 
		{
			$descrizione=str_replace("\n", " ", $descrizione);
			$descrizione=str_replace("'", " ", $descrizione);
			$descrizione=str_replace("/", " ", $descrizione);
			$descrizione=str_replace("  ", " ", $descrizione);
			$descrizione=str_replace("  ", " ", $descrizione);
			$descrizione=trim(db_string($descrizione));
			$fldidsso_rei_budget=get_db_value("select idsso_sia_budget from sso_sia_budget where idsso_tbl_prestazione='$idsso_tbl_prestazione' and idsso_progetto='$pidsso_progetto' and idsso_tbl_sia_progetto_valore='$pidsso_tbl_sia_progetto_valore'");
			if ($fldidsso_rei_budget)
				$sSERVIZI.='<option value="'.$idsso_tbl_prestazione.'" selected >'.$descrizione.'</option>';
			else
				$sSERVIZI.='<option value="'.$idsso_tbl_prestazione.'" >'.$descrizione.'</option>';
		}	
		echo $sSERVIZI;					
		break;

	case "budgetprestazionesia":
		$pidsso_tbl_prestazione=get_param("_p");
		$pidsso_tbl_sia_progetto_valore=get_param("_v");
		$pidsso_progetto=get_param("_progetto");
		$fldidsso_sia_budget=get_db_value("select idsso_sia_budget from sso_sia_budget where idsso_tbl_sia_progetto_valore='$pidsso_tbl_sia_progetto_valore' and idsso_progetto='$pidsso_progetto' ");
		if (!$fldidsso_rei_budget)
		{
			$sSQL="INSERT INTO sso_sia_budget (idsso_tbl_sia_progetto_valore,idsso_tbl_prestazione,idsso_progetto) VALUES ('$pidsso_tbl_sia_progetto_valore','$pidsso_tbl_prestazione','$pidsso_progetto')";
			$db->query($sSQL);
		}
		else
		{

			$sSQL="update sso_sia_budget set idsso_tbl_prestazione='$pidsso_tbl_prestazione' where idsso_sia_budget='$fldidsso_sia_budget' ";
			$db->query($sSQL);
		}

		break;
	case "eliminabudget_utente":
		$pidsso_anagrafica_budget=get_param("_budget");
		if(!empty($pidsso_anagrafica_budget))
		{
			$sSQL="DELETE FROM sso_anagrafica_budget WHERE idsso_anagrafica_budget='$pidsso_anagrafica_budget'";
			$db->query($sSQL);

			echo '1';
		}
		else
			echo 'Errore nell\'eliminazione del budget!';

		break;

	case "get_budgetcura_intervento":
		
		$pidutente=get_param("_idutente");
		$sSQL="select * from sso_anagrafica_budget where idutente='$pidutente'";
  		$db->query($sSQL);
  		$res=$db->next_record();
  		$string_option='<option value="0"></option>';
  		while($res)
  		{
  			$fldidsso_anagrafica_budget=$db->f("idsso_anagrafica_budget");
  			$flddescrizione=$db->f("descrizione");
  			$fldimporto=$db->f("importo");
			
			$string_option.='<option value="'.$fldidsso_anagrafica_budget.'">'.$flddescrizione.'</option>';

  			$counter++;

  			$res=$db->next_record();					  			
  		}

  		echo $string_option;

	break;

	case "get_impegnato_budgetcura":
		$pidsso_anagrafica_budget=get_param("_budget");
		
		$budget_cura=new Budget_cura($pidsso_anagrafica_budget);

		$fldimporto_totale=$budget_cura->importo;
		$fldimporto_impegnato=$budget_cura->budget_cura_impegnato;
		$fldimporto_residuo=$budget_cura->budget_cura_residuo;
		echo number_format($fldimporto_totale,2,',','.')."|".number_format($fldimporto_impegnato,2,',','.')."|".number_format($fldimporto_residuo,2,',','.');
	break;

	case "inviato_reicom_massivo":
		$stringa_idreicom=get_param("_id");
		$array_reicom=explode("|",$stringa_idreicom);
		$pdata=date("Y-m-d");
		foreach($array_reicom as $idreicom)
		{
			echo $update="update sso_rei_com set codice_stato='2',data_invio='$pdata' where idsso_rei_com='$idreicom'";
			$db->query($update);
		}
	break;

	case "aggiungi_esperienza_includis":

		$fldnumero_esperienze=get_param("_num");
		$fldnumero_esperienze=$fldnumero_esperienze+1;

		echo '<tr>
			<th><input type="text" id="datainizio'.$fldnumero_esperienze.'" name="datainizio'.$fldnumero_esperienze.'" class="form-control input-sm class_datepicker" value="" ></th>
			<th><input type="text" id="datafine'.$fldnumero_esperienze.'" name="datafine'.$fldnumero_esperienze.'" class="form-control input-sm class_datepicker" value="" ></th>
			<th><input type="text" id="nomedatore'.$fldnumero_esperienze.'" name="nomedatore'.$fldnumero_esperienze.'" class="form-control input-sm" value="" ></th>
			<th><input type="text" id="tipoazienda'.$fldnumero_esperienze.'" name="tipoazienda'.$fldnumero_esperienze.'" class="form-control input-sm" value="" ></th>
			<th><input type="text" id="tipoimpiego'.$fldnumero_esperienze.'" name="tipoimpiego'.$fldnumero_esperienze.'" class="form-control input-sm" value="" ></th>
			<th><textarea id="principalimansioni'.$fldnumero_esperienze.'" name="principalimansioni'.$fldnumero_esperienze.'" rows="4"  class="form-control input-sm" maxlength="500" placeholder=""> '.$fldprincipalimansioni.'</textarea></th>
			<th class="text-center" ><i class="fa fa-trash-o fa-2x" aria-hidden="true" onclick="eliminaEsperienza(\''.$fldnumero_esperienze.'\');" data-toggle="tooltip" data-placement="top" title="Elimina" ></i><input type="hidden" id="eliminato_esp'.$fldnumero_esperienze.'" name="eliminato_esp'.$fldnumero_esperienze.'" class="form-control input-sm" value="" ></th>
		</tr>';
	break;	

	case "aggiungi_lingua_includis":

		$fldnumero_lingue=get_param("_num");
		$fldnumero_lingue=$fldnumero_lingue+1;

		echo '<tr>
			<th><input type="text" id="altralingua'.$fldnumero_lingue.'" name="altralingua'.$fldnumero_lingue.'" class="form-control input-sm" value="" ></th>
			<td><center><input type="radio" id="radio_altralingua'.$fldnumero_lingue.'" name="radio_altralingua'.$fldnumero_lingue.'" value="1"></center></td>
			<td><center><input type="radio" id="radio_altralingua'.$fldnumero_lingue.'" name="radio_altralingua'.$fldnumero_lingue.'" value="2"></center></td>
			<td><center><input type="radio" id="radio_altralingua'.$fldnumero_lingue.'" name="radio_altralingua'.$fldnumero_lingue.'" value="3"></center></td>
			<th class="text-center" ><i class="fa fa-trash-o fa-2x" aria-hidden="true" onclick="eliminaLingua(\''.$fldnumero_lingue.'\');" data-toggle="tooltip" data-placement="top" title="Elimina" ></i><input type="hidden" id="eliminato_ling'.$fldnumero_lingue.'" name="eliminato_ling'.$fldnumero_lingue.'" class="form-control input-sm" value="" ></th>
		</tr>';
		break;	
	case "includistirocinio":
		$user=verifica_utente($chiave);
		$pidsso_includis_progetto=get_param("_id");
		$flddata_inizio_tirocinio=get_db_value("select data_inizio_tirocinio from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$flddata_fine_tirocinio=get_db_value("select data_fine_tirocinio from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$fldquantita_tirocinio=get_db_value("select quantita_tirocinio from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$fldidsoggetto_promotore=get_db_value("select idsoggetto_promotore from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$fldidsoggetto_ospitante=get_db_value("select idsoggetto_ospitante from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$fldidsso_tbl_area=get_db_value("select idsso_tbl_area from sso_tbl_area where descrizione='INCLUDIS'");
		$fldidsso_tbl_servizio=get_db_value("select idsso_tbl_servizio from sso_tbl_servizio where idsso_tbl_area='$fldidsso_tbl_area'");
		$fldidsso_domanda=get_db_value("select idsso_domanda from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");
		$fldidutente=get_db_value("select idutente from sso_domanda where idsso_domanda='$fldidsso_domanda' ");

		//Tirocinio INCLUDIS01
		$fldidsso_tbl_prestazione1=get_db_value("select idsso_tbl_prestazione from sso_tbl_prestazione where codice_accreditamento='INCLUDIS01'");
		//Formazione INCLUDIS02
		$fldidsso_tbl_prestazione2=get_db_value("select idsso_tbl_prestazione from sso_tbl_prestazione where codice_accreditamento='INCLUDIS02'");
		//Accompagnamento INCLUDIS03
		$fldidsso_tbl_prestazione3=get_db_value("select idsso_tbl_prestazione from sso_tbl_prestazione where codice_accreditamento='INCLUDIS03'");
		//Tutor INCLUDIS04
		$fldidsso_tbl_prestazione4=get_db_value("select idsso_tbl_prestazione from sso_tbl_prestazione where codice_accreditamento='INCLUDIS04'");

		$fldidsso_domanda=get_db_value("select idsso_domanda from sso_includis_progetto where idsso_includis_progetto='$pidsso_includis_progetto' ");

		$pdata=date("Y-m-d");

		$idgen_operatore_incarico=$user;
		$idassistente=$user;
		$pidsso_tabella_stato_intervento=7;
		$pidsso_progetto=get_db_value("select idsso_progetto from sso_domanda where idsso_domanda='$fldidsso_domanda'");

		// TIROCINIO
		$fldidsso_tbl_um=UM_ORESETTIMANA;
		$sSQL="INSERT INTO sso_domanda_intervento 
			(idutente,data,idgen_operatore_incarico,
			idassistente,idsso_domanda,idsso_tbl_area,
			flag_presa_carico,idsso_tbl_targetsiuss,idsso_piano_zona,idsso_anagrafica_budget,idsso_tbl_servizio,
			flag_ente_pubblico,idsso_tabella_stato_intervento,data_inizio,
			data_fine,flag_recupero,flag_sfondamento_app,
			compartecipazione,compartecipazione_tariffa,
			compartecipazione_importo,note,flag_carattere,
			idsso_tbl_erogazione,idsso_consorzio,idsso_ente_servizio,idsso_tbl_agevolazione,numero_protocollo_domanda,
			data_inizio_recupero,isee_intervento,idsso_istituto,idsso_progetto)
			VALUES 
			('$fldidutente','$pdata','$idgen_operatore_incarico',
			'$idassistente','$fldidsso_domanda','$fldidsso_tbl_area',
			'$pflag_presa_carico','$pidsso_tbl_targetsiuss','$pidsso_piano_zona','$pidsso_anagrafica_budget','$fldidsso_tbl_servizio',
			'$pflag_ente_pubblico','$pidsso_tabella_stato_intervento','$flddata_inizio_tirocinio',
			'$flddata_fine_tirocinio','$pflag_recupero','$pflag_sfondamento_app',
			'$pcompartecipazione','$pcompartecipazione_tariffa',
			'$pcompartecipazione_importo','$pnote','$pflag_carattere',
			'$pidsso_tbl_erogazione','$fldidsoggetto_promotore','$fldidsoggetto_ospitante','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda',
			'$pdata_inizio_recupero','$pisee','$pidsso_istituto','$pidsso_progetto')";
		$db->query($sSQL);
		$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
			
		$sSQL="INSERT INTO sso_domanda_prestazione (
		idsso_domanda_intervento,idutente,idsso_tbl_um,
		idsso_tbl_prestazione,quantita,importo,
		tariffa,quantita_recupero
		) VALUES (
		'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um',
		'$fldidsso_tbl_prestazione1','$fldquantita_tirocinio','$fldimporto',
		'$fldtariffa','$fldquantita_recupero')";
		$db->query($sSQL);

		// FORMAZIONE
		$fldidsso_tbl_um=UM_ORE;
		$fldquantita_tirocinio=12;
		$sSQL="INSERT INTO sso_domanda_intervento 
			(idutente,data,idgen_operatore_incarico,
			idassistente,idsso_domanda,idsso_tbl_area,
			flag_presa_carico,idsso_tbl_targetsiuss,idsso_piano_zona,idsso_anagrafica_budget,idsso_tbl_servizio,
			flag_ente_pubblico,idsso_tabella_stato_intervento,data_inizio,
			data_fine,flag_recupero,flag_sfondamento_app,
			compartecipazione,compartecipazione_tariffa,
			compartecipazione_importo,note,flag_carattere,
			idsso_tbl_erogazione,idsso_consorzio,idsso_ente_servizio,idsso_tbl_agevolazione,numero_protocollo_domanda,
			data_inizio_recupero,isee_intervento,idsso_istituto,idsso_progetto)
			VALUES 
			('$fldidutente','$pdata','$idgen_operatore_incarico',
			'$idassistente','$fldidsso_domanda','$fldidsso_tbl_area',
			'$pflag_presa_carico','$pidsso_tbl_targetsiuss','$pidsso_piano_zona','$pidsso_anagrafica_budget','$fldidsso_tbl_servizio',
			'$pflag_ente_pubblico','$pidsso_tabella_stato_intervento','$flddata_inizio_tirocinio',
			'$flddata_fine_tirocinio','$pflag_recupero','$pflag_sfondamento_app',
			'$pcompartecipazione','$pcompartecipazione_tariffa',
			'$pcompartecipazione_importo','$pnote','$pflag_carattere',
			'$pidsso_tbl_erogazione','$fldidsoggetto_promotore','$fldidsoggetto_ospitante','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda',
			'$pdata_inizio_recupero','$pisee','$pidsso_istituto','$pidsso_progetto')";
		$db->query($sSQL);
		$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
			
		$sSQL="INSERT INTO sso_domanda_prestazione (
		idsso_domanda_intervento,idutente,idsso_tbl_um,
		idsso_tbl_prestazione,quantita,importo,
		tariffa,quantita_recupero
		) VALUES (
		'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um',
		'$fldidsso_tbl_prestazione2','$fldquantita_tirocinio','$fldimporto',
		'$fldtariffa','$fldquantita_recupero')";
		$db->query($sSQL);		

		// ACCOMPAGNAMENTO
		$fldidsso_tbl_um=UM_ORE;
		$fldquantita_tirocinio=15;
		$sSQL="INSERT INTO sso_domanda_intervento 
			(idutente,data,idgen_operatore_incarico,
			idassistente,idsso_domanda,idsso_tbl_area,
			flag_presa_carico,idsso_tbl_targetsiuss,idsso_piano_zona,idsso_anagrafica_budget,idsso_tbl_servizio,
			flag_ente_pubblico,idsso_tabella_stato_intervento,data_inizio,
			data_fine,flag_recupero,flag_sfondamento_app,
			compartecipazione,compartecipazione_tariffa,
			compartecipazione_importo,note,flag_carattere,
			idsso_tbl_erogazione,idsso_consorzio,idsso_ente_servizio,idsso_tbl_agevolazione,numero_protocollo_domanda,
			data_inizio_recupero,isee_intervento,idsso_istituto,idsso_progetto)
			VALUES 
			('$fldidutente','$pdata','$idgen_operatore_incarico',
			'$idassistente','$fldidsso_domanda','$fldidsso_tbl_area',
			'$pflag_presa_carico','$pidsso_tbl_targetsiuss','$pidsso_piano_zona','$pidsso_anagrafica_budget','$fldidsso_tbl_servizio',
			'$pflag_ente_pubblico','$pidsso_tabella_stato_intervento','$flddata_inizio_tirocinio',
			'$flddata_fine_tirocinio','$pflag_recupero','$pflag_sfondamento_app',
			'$pcompartecipazione','$pcompartecipazione_tariffa',
			'$pcompartecipazione_importo','$pnote','$pflag_carattere',
			'$pidsso_tbl_erogazione','$fldidsoggetto_promotore','$fldidsoggetto_ospitante','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda',
			'$pdata_inizio_recupero','$pisee','$pidsso_istituto','$pidsso_progetto')";
		$db->query($sSQL);
		$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
			
		$sSQL="INSERT INTO sso_domanda_prestazione (
		idsso_domanda_intervento,idutente,idsso_tbl_um,
		idsso_tbl_prestazione,quantita,importo,
		tariffa,quantita_recupero
		) VALUES (
		'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um',
		'$fldidsso_tbl_prestazione3','$fldquantita_tirocinio','$fldimporto',
		'$fldtariffa','$fldquantita_recupero')";
		$db->query($sSQL);	

		// TUTOR
		$fldidsso_tbl_um=UM_OREMESE;
		$fldquantita_tirocinio=10;
		$sSQL="INSERT INTO sso_domanda_intervento 
			(idutente,data,idgen_operatore_incarico,
			idassistente,idsso_domanda,idsso_tbl_area,
			flag_presa_carico,idsso_tbl_targetsiuss,idsso_piano_zona,idsso_anagrafica_budget,idsso_tbl_servizio,
			flag_ente_pubblico,idsso_tabella_stato_intervento,data_inizio,
			data_fine,flag_recupero,flag_sfondamento_app,
			compartecipazione,compartecipazione_tariffa,
			compartecipazione_importo,note,flag_carattere,
			idsso_tbl_erogazione,idsso_consorzio,idsso_ente_servizio,idsso_tbl_agevolazione,numero_protocollo_domanda,
			data_inizio_recupero,isee_intervento,idsso_istituto,idsso_progetto)
			VALUES 
			('$fldidutente','$pdata','$idgen_operatore_incarico',
			'$idassistente','$fldidsso_domanda','$fldidsso_tbl_area',
			'$pflag_presa_carico','$pidsso_tbl_targetsiuss','$pidsso_piano_zona','$pidsso_anagrafica_budget','$fldidsso_tbl_servizio',
			'$pflag_ente_pubblico','$pidsso_tabella_stato_intervento','$flddata_inizio_tirocinio',
			'$flddata_fine_tirocinio','$pflag_recupero','$pflag_sfondamento_app',
			'$pcompartecipazione','$pcompartecipazione_tariffa',
			'$pcompartecipazione_importo','$pnote','$pflag_carattere',
			'$pidsso_tbl_erogazione','$fldidsoggetto_promotore','$fldidsoggetto_ospitante','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda',
			'$pdata_inizio_recupero','$pisee','$pidsso_istituto','$pidsso_progetto')";
		$db->query($sSQL);
		$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		
			
		$sSQL="INSERT INTO sso_domanda_prestazione (
		idsso_domanda_intervento,idutente,idsso_tbl_um,
		idsso_tbl_prestazione,quantita,importo,
		tariffa,quantita_recupero
		) VALUES (
		'$pidsso_domanda_intervento','$fldidutente','$fldidsso_tbl_um',
		'$fldidsso_tbl_prestazione4','$fldquantita_tirocinio','$fldimporto',
		'$fldtariffa','$fldquantita_recupero')";
		$db->query($sSQL);		

		break;	
	case "xlsfatture":								//analisi preliminare rei		
		require_once '../librerie/excel/Classes/PHPExcel/IOFactory.php';
		require_once '../librerie/excel/Classes/PHPExcel.php';
		$ptipo_fattura=get_param("tipo_fattura");
		$pidsso_ente=get_param("idsso_ente");

		$fldidsso_anagrafica_utente=get_param("idutente_fornitore");
		$pnumero=get_param("numero");
		$pdata_inizio=get_param("data_inizio");
		$pdata_fine=get_param("data_fine");
		$pperiodo_inizio=get_param("periodo_inizio");
		$pperiodo_fine=get_param("periodo_fine");
		$fldnominativo_fornitore=getNominativoFornitore($fldidsso_anagrafica_utente);

		$pidutente_beneficiario=get_param("idutente_beneficiario");

		$pidsso_piano_zona=get_param("idsso_piano_zona");
		//$pbudget=get_param("budget");
		//if(empty($pbudget))
		//	$pidsso_piano_zona=0;

		$sSql="SELECT *
		FROM sso_fattura 
		LEFT JOIN sso_anagrafica_utente ON sso_fattura.idsso_struttura=sso_anagrafica_utente.idutente 
		LEFT JOIN sso_ente_servizio on sso_fattura.idsso_struttura=sso_ente_servizio.idutente ";			

		$sWhere='';
		$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_anagrafica_utente>0");
		if(!empty($ptipo_fattura))
		{
			switch($ptipo_fattura)
			{
				case 1: 	// Verifica
					$sWhere=aggiungi_condizione($sWhere, "sso_fattura.tipo_fattura='2'");
					break;

				case 2: 	// Documento prestazioni
					$sWhere=aggiungi_condizione($sWhere, "sso_fattura.tipo_fattura='1'");
					$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_tbl_fatturazione='1'");
					break;

				case 3: 	// Documento gestione
					$sWhere=aggiungi_condizione($sWhere, "sso_fattura.tipo_fattura='1'");
					$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_tbl_fatturazione='2'");
					break;
			}			
		}
		
		if ($pidsso_ente)
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_ente='$pidsso_ente'");
		
		if ($pdata_inizio)
		{
			$pdata_inizio=invertidata($pdata_inizio,"-","/",1);
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.data>='$pdata_inizio'");
		}	
		
		if ($pdata_fine)
		{
			$pdata_fine=invertidata($pdata_fine,"-","/",1);
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.data<='$pdata_fine'");
		}
		
		if ($pperiodo_inizio)
		{
			$pperiodo_inizio=invertidata($pperiodo_inizio,"-","/",1);
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.data_inizio>='$pperiodo_inizio'");
		}	
		
		if ($pperiodo_fine)
		{
			$pperiodo_fine=invertidata($pperiodo_fine,"-","/",1);
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.data_inizio<='$pperiodo_fine'");
		}
		
		if ($pnumero)
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.numero LIKE '%$pnumero%'");
		
		if ($fldidsso_anagrafica_utente)
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_struttura='$fldidsso_anagrafica_utente'");

		if ($pidutente_beneficiario)
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_anagrafica_utente='$pidutente_beneficiario'");

		if($pidsso_piano_zona)
			$sWhere=aggiungi_condizione($sWhere, "sso_fattura.idsso_piano_zona='$pidsso_piano_zona'");

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;
		
		$sOrder= " order by sso_fattura.idsso_struttura, sso_fattura.idsso_anagrafica_utente,sso_fattura.data ";		
		
		$sSQL=$sSql.$sWhere.$sOrder;
		
		$db->query($sSQL);

		$excel2 = PHPExcel_IOFactory::createReader('Excel2007');
		$excel2 = $excel2->load('../documenti/sicare/fatture_liquidazione.xlsx');
		$excel2->setActiveSheetIndex(0);
		$next_record=$db->next_record();
		$prog=1;
		$iCounter=4;
		$fornitore_totale=0;
		$totale=0;
		while($next_record)
		{
			$fldidsso_fattura=$db->f("idsso_fattura");
			$fldfornitore=$db->f("cognome");
			$flddata=$db->f("data");
			$fldiban=$db->f("iban");
			$fldnumero=$db->f("numero");
			$fldimporto=$db->f("importo_totale");		
			$fldpiva=$db->f("piva");
			$fldidsso_struttura=$db->f("idsso_struttura");
			$fldidsso_anagrafica_utente=$db->f("idsso_anagrafica_utente");
			$beneficiario=new Beneficiario($fldidsso_anagrafica_utente);
			$beneficiario_nominativo=$beneficiario->cognome." ".$beneficiario->nome;	
			$beneficiario_cf=$beneficiario->codicefiscale;	
			if ($fldidsso_struttura!=$fornitore_precedente && $prog>1)	
			{
				$excel2->getActiveSheet()->setCellValue('E'.$iCounter, $fornitore_totale);  
				$fornitore_totale=$fldimporto;	
				$iCounter++;		
			}
			else
				$fornitore_totale+=$fldimporto;

			$excel2->getActiveSheet()->setCellValue('A'.$iCounter, $prog);  
			$excel2->getActiveSheet()->setCellValue('B'.$iCounter, $beneficiario_nominativo);  
			$excel2->getActiveSheet()->setCellValue('C'.$iCounter, $beneficiario_cf);  
			$excel2->getActiveSheet()->setCellValue('D'.$iCounter, $fldnumero);  
			$excel2->getActiveSheet()->setCellValue('E'.$iCounter, $fldimporto);  
			$excel2->getActiveSheet()->setCellValue('G'.$iCounter, $fldfornitore);  
			$excel2->getActiveSheet()->setCellValue('H'.$iCounter, $fldpiva);  
			$excel2->getActiveSheet()->setCellValue('I'.$iCounter, $fldiban);  
			$prog++;
			$iCounter++;
			$totale+=$fldimporto;
			$fornitore_precedente=$fldidsso_struttura;
			$next_record=$db->next_record();
		}
		$excel2->getActiveSheet()->setCellValue('E'.$iCounter, $fornitore_totale); 
		$iCounter++; 
		$excel2->getActiveSheet()->setCellValue('E'.$iCounter, $totale);  

		$objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel2007');
		echo $filename='../documenti/sicare/temp/xlsfatture'.date("Ymdhis").'.xlsx';
		$objWriter->save($filename);			


		break;

	case "recupera_password":
		require '../librerie/mail/class.phpmailer.php';
		include("../librerie/mail/lib.mail.php");

		$fldprocedura=get_param("_procedura");
		$fldemail=get_param("_e");
		$fldemail=db_string($fldemail);

		$fldnumber_utenti=get_db_value("SELECT COUNT(*) FROM ".DBNAME_A.".utenti WHERE email='$fldemail'");
		if($fldnumber_utenti==1)
		{
			$fldidutente=get_db_value("SELECT idutente FROM ".DBNAME_A.".utenti WHERE email='$fldemail'");
			if(!empty($fldidutente))
			{
				$fldnominativo=get_nominativo_utente_back($fldidutente);
				$fldnome_utente=get_db_value("SELECT login FROM ".DBNAME_A.".utenti WHERE idutente='$fldidutente'");
			
				$password=generaPassword();

				$oggi=date("Y-m-d");
				$adesso=date("H:i:s");

				$fldinvio="<br>";
				$fldoggetto="Recupero Password";
				$fldtesto="Gentile $fldnominativo, $fldinvio";
			 	$fldtesto.="inviamo di seguito, come da sua richiesta all'indirizzo e-mail da lei segnalato, le credenziali temporanee per l'accesso alla piattaforma SiCare.$fldinvio $fldinvio";
			 	$fldtesto.="Le sarà richiesto di impostare una nuova password al momento dell'accesso.$fldinvio $fldinvio";
				$fldtesto.="Credenziali$fldinvio";
				$fldtesto.="Username: <b>$fldnome_utente</b> $fldinvio";
				$fldtesto.="Password temporanea: <b
				>$password</b> $fldinvio $fldinvio";
				$fldtesto.="Privacy $fldinvio";
				$fldtesto.="Con riferimento ai dati che ci hai fornito per l'attivazione del servizio, ricordiamo che i diritti in materia di privacy sono tutelati nel rispetto della vigente normativa (D. lgs 196/2003). $fldinvio";
				
				$aEMAIL=array();
				$aEMAIL[0]=$fldemail;
				$aEMAIL[1]=$fldoggetto;
				$aEMAIL[2]=$fldtesto;
				$aEMAIL[3]="";
				$fldresult=sendMAIL($aEMAIL);

				$password=md5($password);

				//annullo le precedenti richieste di recupero pendenti
				$update="UPDATE ".DBNAME_A.".gen_password_temp SET flag_accesso=1 WHERE idutente='$fldidutente'";
				$db->query($update);

				$insert="INSERT INTO ".DBNAME_A.".gen_password_temp(idutente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso) VALUES('$fldidutente','$oggi','$adesso','$fldprocedura','$fldnome_utente','$password',0)";
				$db->query($insert);
			}
			else
			{
				echo "0";
			}
		}
		else
			echo "1";
		break;

	case "updatenote_intervento":
		$pidsso_domanda_intervento=get_param("_id");
		$pnote=get_param("_note");
		$pnote=db_string($pnote);

		$update="UPDATE sso_domanda_intervento SET note='$pnote' WHERE idsso_domanda_intervento='$pidsso_domanda_intervento'";
		$db->query($update);

		echo "1";
		break;
	case "addcontatto":
		$pidutente=get_param("_u");
		$beneficiario=new Beneficiario($pidutente);
		$pdata_accoglienza=get_param("_d");
		$pdata_accoglienza=invertidata($pdata_accoglienza,"-","/",1);
		$pidsso_ente=$beneficiario->idsso_ente;
		$pidoperatore=verifica_utente($chiave);;
		$pidassistente=$pidoperatore;
		$pidsso_tabella_motivo_domanda=get_param("_motivo");
		$pidsso_tabella_modalita_accesso=get_param("_modalita");
		$pidsso_tabella_segnalante=get_param("_segnalante");
		$pidsso_tbl_segnalazione_modo=get_param("_modo");
		$pidsso_tbl_segnalazione_luogo=get_param("_luogo");
		$pflag_registrazione="1";

		$sSQL = "INSERT INTO sso_accoglienza (idsso_ente,data_accoglienza,idassistente,idoperatore,idutente,idsso_tabella_motivo_domanda,idsso_tabella_modalita_accesso,idsso_tabella_segnalante,idsso_tbl_segnalazione_modo,idsso_tbl_segnalazione_luogo,flag_registrazione,flag_primocontatto)
				VALUES ('$pidsso_ente','$pdata_accoglienza','$pidassistente','$pidoperatore','$pidutente','$pidsso_tabella_motivo_domanda','$pidsso_tabella_modalita_accesso','$pidsso_tabella_segnalante','$pidsso_tbl_segnalazione_modo','$pidsso_tbl_segnalazione_luogo','$pflag_registrazione','2')";
		$db->query($sSQL);
		$pidsso_accoglienza = mysql_insert_id($db->link_id());		
		break;
	case "reis18contributi":
		$pidsso_ente=get_param("_e");
		$pidsso_progetto=get_param("_progetto");
		$pidoperatore=verifica_utente($chiave);
		$pintegrazione1=get_param("_i1");
		$pintegrazione2=get_param("_i2");
		$pintegrazione3=get_param("_i3");
		$pintegrazione4=get_param("_i4");
		$psussidio1=get_param("_s1");
		$psussidio2=get_param("_s2");
		$psussidio3=get_param("_s3");
		$psussidio4=get_param("_s4");
		$pdurata1=get_param("_m1");
		$pdurata2=get_param("_m2");
		$pdurata3=get_param("_m3");
		$pdurata4=get_param("_m4");
		$sSQL="delete from sso_tbl_reis_contributo where idsso_progetto='$pidsso_progetto' and idsso_ente='$pidsso_ente'";
		$db->query($sSQL);
		$sSQL = "INSERT INTO sso_tbl_reis_contributo (idsso_ente,idsso_progetto,contributo,contributo2,contributo_durata,numero_componenti)
				VALUES ('$pidsso_ente','$pidsso_progetto','$pintegrazione1','$psussidio1','$pdurata1','1')";
		$db->query($sSQL);			
		$sSQL = "INSERT INTO sso_tbl_reis_contributo (idsso_ente,idsso_progetto,contributo,contributo2,contributo_durata,numero_componenti)
				VALUES ('$pidsso_ente','$pidsso_progetto','$pintegrazione2','$psussidio2','$pdurata2','2')";
		$db->query($sSQL);			
		$sSQL = "INSERT INTO sso_tbl_reis_contributo (idsso_ente,idsso_progetto,contributo,contributo2,contributo_durata,numero_componenti)
				VALUES ('$pidsso_ente','$pidsso_progetto','$pintegrazione3','$psussidio3','$pdurata3','3')";
		$db->query($sSQL);			
		$sSQL = "INSERT INTO sso_tbl_reis_contributo (idsso_ente,idsso_progetto,contributo,contributo2,contributo_durata,numero_componenti)
				VALUES ('$pidsso_ente','$pidsso_progetto','$pintegrazione4','$psussidio4','$pdurata4','4')";
		$db->query($sSQL);			
		
		break;	
	case "getreis18":
		$pidsso_progetto=get_param("_progetto");
		$pidsso_ente=get_param("_e");
		$pnumero_componenti=get_param("_num");
		$ppriorita=get_param("_p");
		if ($ppriorita=='1')
			$field="contributo";
		else
			$field="contributo2";
		//echo "select ".$field." from sso_tbl_reis_contributo where idsso_progetto='$pidsso_progetto' and idsso_ente='$pidsso_ente' and numero_componenti='$pnumero_componenti'";
		echo $fldreis=get_db_value("select ".$field." from sso_tbl_reis_contributo where idsso_progetto='$pidsso_progetto' and idsso_ente='$pidsso_ente' and numero_componenti='$pnumero_componenti'");
		break;

		case "loadpreavvisi":
			$pidutente=get_param("_u");
			if (!$pidutente)
				$pidutente=0;
			$pidsso_domanda_intervento=get_param("_i");
			if ($pidsso_domanda_intervento)
				$iWhere=" and idtco_dichiarazione='$pidsso_domanda_intervento'";

			header("Content-type:text/xml");
			$fldxml="<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
			$fldxml.="\n <complete>";
			$fldxml.="\n <option value=\"0\"></option>";

			$db->query("select tco_preavviso.* from ".DBNAME_SS.".tco_preavviso inner join tco_preavviso_altro on tco_preavviso.idtco_preavviso=tco_preavviso_altro.idtco_preavviso where tco_preavviso.idtco_contribuente='$pidutente' ".$iWhere." order by tco_preavviso.idtco_preavviso");
			$next_record=$db->next_record();
			while($next_record)
			{
				$fldidtco_preavviso=$db->f("idtco_preavviso");
				$fldidtco_preavviso_rata=get_db_value("select idtco_preavviso_rata from tco_preavviso_rata where idtco_preavviso='$fldidtco_preavviso'");
				$flddata_preavviso=$db->f("data_elaborazione");
				$fldnumero_preavviso=$db->f("numero");
				$importo_totale = $db->f('importo_totale');
				// Verifico se il preavviso e' stato pagato per intero
				$importo_versato=get_db_value("select sum(importo_versato) from tco_versamento where idtco_preavviso_rata='$fldidtco_preavviso_rata'");

				if ($importo_versato<$importo_totale)
				{
					$flddescrizione="Documento n.".$fldnumero_preavviso." del ".invertidata($flddata_preavviso,"/","-",2)." - importo: ".$importo_totale." euro";

					$flddescrizione=removeslashes($flddescrizione);
					$flddescrizione=utf8_decode($flddescrizione);

					$fldxml.="\n <option value=\"".$fldidtco_preavviso."\">".stringXMLClean($flddescrizione)."</option>";
				}	
				$next_record=$db->next_record();
			}

			$fldxml.="\n </complete>";
				
			print($fldxml);
		break;
	case "aggiungi_altraspesa":
		$pidente=get_param("_idsso_ente");
		$pidgen_tbl_iva=get_param("idgen_tbl_iva");
		$pdescrizione=get_param("descrizione");
		$pdescrizione=db_string($pdescrizione);
		$pspesa_importo=get_param("spesa_importo");
		$pspesa_importo=db_double($pspesa_importo);
		$ppercentuale=get_param("percentuale");
		$ppercentuale=db_double($ppercentuale);
		$pcodice_accertamento=get_param("accertamento");

        $aIVA=db_fill_array("select idtabella_iva,descrizione from ".DBNAME_A.".tabella_iva order by idtabella_iva");

		$insert="INSERT INTO ".DBNAME_SS.".sso_tbl_altrespese (idsso_ente,idgen_tbl_iva,descrizione,spesa_importo,codice_accertamento,percentuale) VALUES('$pidente','$pidgen_tbl_iva','$pdescrizione','$pspesa_importo','$pcodice_accertamento','$ppercentuale')";

		$db->query($insert);
		$pidsso_tbl_altrespese=mysql_insert_id($db->link_id());

		echo '<tr>
		<th>
		<select class="form-control input-sm" style="" name="idgen_tbl_iva_'.$pidsso_tbl_altrespese.'" id="idgen_tbl_iva_'.$pidsso_tbl_altrespese.'" >
		<option value=""></option>';
        foreach ($aIVA as $idtabella_iva => $descrizione_iva) 
        {
            if ($idtabella_iva==$pidgen_tbl_iva)
                $selected="selected";
            else
                $selected="";

            echo '<option value="'.$idtabella_iva.'" '.$selected.'>'.$descrizione_iva.'</option>';
        }    
		echo '</select>    
		</th> 
		<th>
		<input id="descrizione_'.$pidsso_tbl_altrespese.'" name="descrizione_'.$pidsso_tbl_altrespese.'" type="text" class="form-control input-sm" value="'.$pdescrizione.'">      
		</th>
		<th>
		<input id="percentuale_'.$pidsso_tbl_altrespese.'" name="percentuale_'.$pidsso_tbl_altrespese.'" type="text" class="form-control input-sm" value="'.$ppercentuale.'">      
		</th>
		<th>
		<input id="spesa_importo_'.$pidsso_tbl_altrespese.'" name="spesa_importo_'.$pidsso_tbl_altrespese.'" type="text" class="form-control input-sm" value="'.$pspesa_importo.'">      
		</th>
		<th>
		<input id="accertamento_'.$pidsso_tbl_altrespese.'" name="accertamento_'.$pidsso_tbl_altrespese.'" type="text" class="form-control input-sm" value="'.$pcodice_accertamento.'">      
		</th>		
        <th><button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_ws" onclick="aggiorna('.$pidsso_tbl_altrespese.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Salva</button></th>
		<th><button type="button" class="btn btn-primary btn-sm" id="btn_elimina_ws" onclick="elimina('.$pidsso_tbl_altrespese.')"><span class="glyphicon glyphicon-remove span-padding" aria-hidden="true"></span>Elimina</button></th>
		</tr>';
		break;		
	case "elimina_altraspesa":
		$pidsso_tbl_altrespese=get_param("_id");

		$delete="DELETE FROM ".DBNAME_SS.".sso_tbl_altrespese WHERE idsso_tbl_altrespese='$pidsso_tbl_altrespese'";
		$db->query($delete);
		echo "1";
		break;		
	case "graduatoriareis_congela":

		$pidsso_progetto=get_param("_progetto");
		$pidsso_domanda=get_param("_domanda");
		$pordine=get_param("ordine");
		$pisee=get_param("isee");
		$pisee=db_double($pisee);
		$pcomponenti=get_param("componenti");
		$pmesi_progetto=get_param("mesi_progetto");
		$pmese_inizio=get_param("mese_inizio");
		$pbudget_nucleo=get_param("budget_nucleo");
		$pidsso_ente=get_param("_ente");
		$pnumero_protocollo=get_param("protocollo");
		$pdata_protocollo=get_param("dtprotocollo");
		$pdata_protocollo=invertidata($pdata_protocollo,"-","/",1);
		$pcontributo_reis=get_param("reis");
		$pcontributo_reis=db_double($pcontributo_reis);

		$pvalore_isre=get_param("isre");
		$pvalore_isre=db_double($pvalore_isre);

		$ppriorita=get_param("priorita_domanda");
		$pprioritasub=get_param("prioritasub_domanda");

		//Verifico se esiste la graduatoria
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto' and idsso_ente='$pidsso_ente'");
		$oggi=date("Y-m-d");
		$user=verifica_utente($chiave);
		if (empty($fldidsso_graduatoria))
		{
			$sSQL="insert into sso_graduatoria (idsso_progetto,data,idgen_operatore,idsso_ente) values('$pidsso_progetto','$oggi','$user','$pidsso_ente')";
			$db->query($sSQL);
			$fldidsso_graduatoria=mysql_insert_id($db->link_id());			

		}

		$domanda=new Domanda($pidsso_domanda);
		$fldidsso_anagrafica_utente=$domanda->idutente;
		$fldidsso_tabella_stato_domanda=$domanda->idsso_tabella_stato_domanda;

		$sSQL="insert into sso_graduatoria_utente(
			idsso_graduatoria,
			idsso_progetto,
			idsso_anagrafica_utente,
			idsso_domanda,
			idsso_tabella_stato_domanda,
			graduatoria_ordine,
			valore_isee,
			parametro1,
			parametro2,
			parametro3,
			parametro4,
			parametro5,
			parametro6,
			parametro7,
			parametro8,
			parametro9,
			parametro10)
			values(
			'$fldidsso_graduatoria',
			'$pidsso_progetto',
			'$fldidsso_anagrafica_utente',
			'$pidsso_domanda',
			'$fldidsso_tabella_stato_domanda',
			'$pordine',
			'$pisee',
			'$pcomponenti',
			'$pmesi_progetto',
			'$pmese_inizio',
			'$pbudget_nucleo',
			'$pnumero_protocollo',
			'$pdata_protocollo',
			'$ppriorita',
			'$pprioritasub',
			'$pcontributo_reis',
			'$pvalore_isre')";
		$db->query($sSQL);
		break;

	case "graduatoriareis_congela_esclusi":

		$pidsso_progetto=get_param("_progetto");
		$pesclusi_graduatoria=get_param("_esclusi");
		$pidsso_ente=get_param("_ente");

		//Verifico se esiste la graduatoria
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto' and idsso_ente='$pidsso_ente'");
		$oggi=date("Y-m-d");
		$user=verifica_utente($chiave);
		if (empty($fldidsso_graduatoria))
		{
			$sSQL="insert into sso_graduatoria (idsso_progetto,data,idgen_operatore,idsso_ente,esclusi) values('$pidsso_progetto','$oggi','$user','$pidsso_ente','$pesclusi_graduatoria')";
			$db->query($sSQL);
			$fldidsso_graduatoria=mysql_insert_id($db->link_id());			
		}

		break;

	case "aggiungi_codice_tipo_debito_pagopa":
		$pidente=get_param("_idente");
		$pdescrizione=get_param("descrizione");
		$pdescrizione=db_string($pdescrizione);
		$pcodice_efil=get_param("codice_efil");
		$pcodice_efil=db_string($pcodice_efil);
		$pcodice_servizio=get_param("codice_servizio");
		$pcodice_servizio=db_string($pcodice_servizio);
		$pdescrizione_utente=get_param("descrizione_utente");
		$pdescrizione_utente=db_string($pdescrizione_utente);
		$pflag_intestatario=get_param("flag_intestatario");
		$pflag_intestatario=db_string($pflag_intestatario);
		if(empty($pflag_intestatario))
			$pflag_intestatario=1;	//Referente

		switch($pflag_intestatario)
       	{
       		case 1:
       			$selected_referente="selected";
       			$selected_alunno="";
       			break;

       		case 2:
       			$selected_referente="";
       			$selected_alunno="selected";
       			break;

       		default:
       			$selected_referente="";
       			$selected_alunno="";
       			break;
       	}

		$insert="INSERT INTO ".DBNAME_SS.".gen_tbl_codici_tipo_debito(descrizione,codice_efil,codice_servizio,descrizione_utente,flag_intestatario) VALUES('$pdescrizione','$pcodice_efil','$pcodice_servizio','$pdescrizione_utente','$pflag_intestatario')";
		$db->query($insert);
		$pidgen_tbl_codici_tipo_debito=mysql_insert_id($db->link_id());

		echo '<tr>
		<th>
		<input id="codice_servizio_'.$pidgen_tbl_codici_tipo_debito.'" name="codice_servizio_'.$pidgen_tbl_codici_tipo_debito.'" type="text" class="form-control input-sm" value="'.$pcodice_servizio.'">      
		</th>
		<th>
		<input id="descrizione_'.$pidgen_tbl_codici_tipo_debito.'" name="descrizione_'.$pidgen_tbl_codici_tipo_debito.'" type="text" class="form-control input-sm" value="'.$pdescrizione.'">      
		</th>
		<th>
		<input id="descrizione_utente_'.$pidgen_tbl_codici_tipo_debito.'" name="descrizione_utente_'.$pidgen_tbl_codici_tipo_debito.'" type="text" class="form-control input-sm" value="'.$pdescrizione_utente.'">      
		</th>
		<th>
		<input id="codice_efil_'.$pidgen_tbl_codici_tipo_debito.'" name="codice_efil_'.$pidgen_tbl_codici_tipo_debito.'" type="text" class="form-control input-sm" value="'.$pcodice_efil.'">      
		</th>
		<th>
            <select id="flag_intestatario_'.$pidgen_tbl_codici_tipo_debito.'" name="flag_intestatario_'.$pidgen_tbl_codici_tipo_debito.'" type="text" class="form-control input-sm">
            	<option value=""></option>
            	<option value="1" '.$selected_referente.'>Referente</option>
            	<option value="2" '.$selected_alunno.'>Alunno</option>
            </select>  
        </th>
        <th><button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_debito" onclick="aggiorna('.$pidgen_tbl_codici_tipo_debito.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button></th>
        <th><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_debito" onclick="elimina('.$pidgen_tbl_codici_tipo_debito.')"><span class="glyphicon glyphicon-remove span-padding" aria-hidden="true"></span>Elimina</button></th>
		</tr>';
		break;

	case "elimina_codice_tipo_debito_pagopa":
		$pidgen_tbl_codici_tipo_debito=get_param("_id");

		$delete="DELETE FROM ".DBNAME_SS.".gen_tbl_codici_tipo_debito WHERE idgen_tbl_codici_tipo_debito='$pidgen_tbl_codici_tipo_debito'";
		$db->query($delete);
		echo "1";
		break;


	case "aggiungi_servizio_partenopay":
		$pidente=get_param("_idente");
		
		$pdescrizione=get_param("descrizione");
		$pdescrizione=db_string($pdescrizione);
		
		$pid_servizio=get_param("id_servizio");
		$pid_servizio=db_string($pid_servizio);

		$insert="INSERT INTO ".DBNAME_SS.".gen_tbl_servizi_partenopay(descrizione,id_servizio) VALUES('$pdescrizione','$pid_servizio')";
		$db->query($insert);
		$pidgen_tbl_servizi_partenopay=mysql_insert_id($db->link_id());

		echo '<tr id="idrow_'.$pidgen_tbl_servizi_partenopay.'">
		<th>
		<input id="descrizione_'.$pidgen_tbl_servizi_partenopay.'" name="descrizione_'.$pidgen_tbl_servizi_partenopay.'" type="text" class="form-control input-sm" value="'.$pdescrizione.'">      
		</th>
		<th>
		<input id="id_servizio_'.$pidgen_tbl_servizi_partenopay.'" name="id_servizio_'.$pidgen_tbl_servizi_partenopay.'" type="text" class="form-control input-sm" value="'.$pid_servizio.'">      
		</th>
        <th><button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_servizio" onclick="aggiorna('.$pidgen_tbl_servizi_partenopay.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button></th>
        <th><button type="button" class="btn btn-default btn-sm" id="btn_metadati_servizio" onclick="metadati('.$pidgen_tbl_servizi_partenopay.')"><span class="glyphicon glyphicon-cog span-padding" aria-hidden="true"></span>Metadati</button></th>
	    <th><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_servizio" onclick="elimina('.$pidgen_tbl_servizi_partenopay.')">&nbsp;<span class="glyphicon glyphicon-trash span-padding" aria-hidden="true"></span></button></th>
		</tr>';
		break;

	case "elimina_servizio_partenopay":
		$pidgen_tbl_servizi_partenopay=get_param("_id");

		$delete="DELETE FROM ".DBNAME_SS.".gen_tbl_servizi_partenopay WHERE idgen_tbl_servizi_partenopay='$pidgen_tbl_servizi_partenopay'";
		$db->query($delete);
		echo "1";
		break;

	case "aggiungi_metadato_partenopay":
		$pidgen_tbl_servizi_partenopay=get_param("_id");
		
		$pidgen_tbl_partenopay_metadati=get_param("_idmetadato");
		
		$pidgen_tbl_servizi_partenopay_metadati=get_db_value("SELECT idgen_tbl_servizi_partenopay_metadati FROM gen_tbl_servizi_partenopay_metadati WHERE idgen_tbl_servizi_partenopay='$pidgen_tbl_servizi_partenopay' AND idgen_tbl_partenopay_metadati='$pidgen_tbl_partenopay_metadati'");
		if(empty($pidgen_tbl_servizi_partenopay_metadati))
		{
			$insert="INSERT INTO ".DBNAME_SS.".gen_tbl_servizi_partenopay_metadati(idgen_tbl_servizi_partenopay,idgen_tbl_partenopay_metadati) VALUES('$pidgen_tbl_servizi_partenopay','$pidgen_tbl_partenopay_metadati')";
			$db->query($insert);
			$pidgen_tbl_servizi_partenopay_metadati=mysql_insert_id($db->link_id());

			echo '<tr id="idrow_'.$pidgen_tbl_servizi_partenopay_metadati.'">
			<th>
				<select class="form-control input-sm" style="" name="idgen_tbl_partenopay_metadati_'.$pidgen_tbl_servizi_partenopay_metadati.'" name="idgen_tbl_partenopay_metadati_'.$pidgen_tbl_servizi_partenopay_metadati.'">
							<option value="" selected ></option>';

						$query="SELECT * FROM ".DBNAME_SS.".gen_tbl_partenopay_metadati"; 
						$db->query($query);
											
						$res = $db->next_record();
						while($res)
						{
							$idgen_tbl_partenopay_metadati = $db->f('idgen_tbl_partenopay_metadati');
							$flddescrizione = $db->f('descrizione');
							
							if($pidgen_tbl_partenopay_metadati==$idgen_tbl_partenopay_metadati)
								echo "\n <option value='$idgen_tbl_partenopay_metadati' selected >$flddescrizione</option>";
							else
								echo "\n <option value='$idgen_tbl_partenopay_metadati' >$flddescrizione</option>";

							$res = $db->next_record();
						}

			echo '</select>    
			</th>
		    <th><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_metadato" onclick="eliminaMETADATO('.$pidgen_tbl_servizi_partenopay_metadati.')">&nbsp;<span class="glyphicon glyphicon-trash span-padding" aria-hidden="true"></span></button></th>
			</tr>';
		}
		else
			echo "0";

		break;

	case "elimina_metadato_partenopay":
		$pidgen_tbl_servizi_partenopay_metadati=get_param("_id");

		$delete="DELETE FROM ".DBNAME_SS.".gen_tbl_servizi_partenopay_metadati WHERE idgen_tbl_servizi_partenopay_metadati='$pidgen_tbl_servizi_partenopay_metadati'";
		$db->query($delete);
		echo "1";
		break;

	case "loadbeneficiario_matricola":
		$pvalue=get_param("value");
		$fldesiste=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE idutente='$pvalue'");
		if($fldesiste)
		{
			$beneficiario=new Beneficiario($pvalue);
			echo $beneficiario->idutente."|".$beneficiario->nominativo."|".$beneficiario->codicefiscale;
		}
		break;
	case "esisteCF":
		$pvalue=substr(get_param("value"),0,16);

		echo $idutente=get_db_value("select idutente from ".DBNAME_SS.".sso_anagrafica_utente where codicefiscale='$pvalue'");
		break;

	case "aggiungi_ufficio":
		$pidente=get_param("_idente");
		$pdescrizione_ufficio=get_param("descrizione_ufficio");
		$pdescrizione_ufficio=db_string($pdescrizione_ufficio);
		$pcodice_ufficio=get_param("codice_ufficio");
		$pcodice_ufficio=db_string($pcodice_ufficio);

		$insert="INSERT INTO ".DBNAME_A.".gen_ente_ufficio(idente,descrizione,codice_ufficio) VALUES('$pidente','$pdescrizione_ufficio','$pcodice_ufficio')";
		$db->query($insert);
		$pidgen_ente_ufficio=mysql_insert_id($db->link_id());

		echo '<tr>
		<th></th>
		<th>
		<input id="descrizione_'.$pidgen_ente_ufficio.'" name="descrizione_'.$pidgen_ente_ufficio.'" type="text" class="form-control input-sm" value="'.$pdescrizione_ufficio.'">      
		</th> 
		<th>
		<input id="codice_ufficio_'.$pidgen_ente_ufficio.'" name="codice_ufficio_'.$pidgen_ente_ufficio.'" type="text" class="form-control input-sm" value="'.$pcodice_ufficio.'">      
		</th>
        <th><button type="button" class="btn btn-primary btn-sm" id="btn_aggiorna_ufficio" onclick="aggiorna('.$pidgen_ente_ufficio.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button></th>
		<th><button type="button" class="btn btn-primary btn-sm" id="btn_elimina_ufficio" onclick="elimina('.$pidgen_ente_ufficio.')"><span class="glyphicon glyphicon-remove span-padding" aria-hidden="true"></span>Elimina</button></th>
		</tr>';
		break;

	case "elimina_ufficio":
		$pidgen_ente_ufficio=get_param("_id");

		$delete="DELETE FROM ".DBNAME_A.".gen_ente_ufficio WHERE idgen_ente_ufficio='$pidgen_ente_ufficio'";
		$db->query($delete);
		echo "1";
		break;

	case "aggiungi_reddito":
		$pidutente=get_param("_utente");
		$fldidsso_anagrafica_altro=get_db_value("SELECT idsso_anagrafica_altro FROM sso_anagrafica_altro WHERE idsso_anagrafica_utente='$pidutente' ORDER BY idsso_anagrafica_altro");

		$panno=get_param("_anno");
		$preddito=get_param("_reddito");
		$preddito=db_double($preddito);

		$fldsso_anagrafica_altro_reddito=get_db_value("SELECT idsso_anagrafica_altro_reddito FROM ".DBNAME_SS.".sso_anagrafica_altro_reddito WHERE anno='$panno' AND idsso_anagrafica_altro='$fldidsso_anagrafica_altro'");
		if(empty($fldsso_anagrafica_altro_reddito))
		{
			$insert="INSERT INTO ".DBNAME_SS.".sso_anagrafica_altro_reddito(idsso_anagrafica_altro,anno,reddito) VALUES('$fldidsso_anagrafica_altro','$panno','$preddito')";
			$db->query($insert);
			$pidsso_anagrafica_altro_reddito=mysql_insert_id($db->link_id());

			$preddito=number_format($preddito,2,",",".");

			$selected2017="";
			$selected2018="";
			$selected2019="";

			switch($panno)
			{
				case 2017:
					$selected2017="selected";
				break;

				case 2018:
					$selected2018="selected";
				break;

				case 2019:
					$selected2019="selected";
				break;
			}

			echo '<tr id="idrow_'.$pidsso_anagrafica_altro_reddito.'">
				<td></td>
				<th>
	                <select class="form-control input-sm" name="anno_'.$pidsso_anagrafica_altro_reddito.'" id="anno_'.$pidsso_anagrafica_altro_reddito.'">
		        		<option value="2019" '.$selected2019.'>2019</option>
		        		<option value="2018" '.$selected2018.'>2018</option>
		        		<option value="2017" '.$selected2017.'>2017</option>
					</select>	         
	            </th>
	            <th>
	                <input id="reddito_'.$pidsso_anagrafica_altro_reddito.'" name="reddito_'.$pidsso_anagrafica_altro_reddito.'" type="text" class="form-control input-sm" value="'.$preddito.'">   
	            </th>
				<td><button type="button" class="btn btn-success btn-sm" id="btn_aggiorna_reddito" onclick="aggiorna('.$pidsso_anagrafica_altro_reddito.')"><span class="glyphicon glyphicon-floppy-disk span-padding" aria-hidden="true"></span>Aggiorna</button></td>
	            <td><button type="button" class="btn btn-danger btn-sm" id="btn_elimina_reddito" onclick="elimina('.$pidsso_anagrafica_altro_reddito.')"><span class="glyphicon glyphicon-remove span-padding" aria-hidden="true"></span>Elimina</button></td>
			</tr>';
		}
		else
			echo "0";
		break;

	case "elimina_reddito":
		$pidsso_anagrafica_altro_reddito=get_param("_id");

		$delete="DELETE FROM ".DBNAME_SS.".sso_anagrafica_altro_reddito WHERE idsso_anagrafica_altro_reddito='$pidsso_anagrafica_altro_reddito'";
		$db->query($delete);
		echo "1";
		break;

	case "compartecipazione_fatturapa":
		$pidtco_preavviso_elaborazione=get_param("_id");

		$pformato_trasmissione = get_param('formato_trasmissione');
		$pidgen_ente_ufficio = get_param('_idufficio');
		$pidgen_regime_fiscale = get_param('_regimefiscale');
		$pidgen_tipo_documento = get_param('_tipodocumento');
		$pidgen_natura_iva = get_param('_naturaiva');

		$sSelect="SELECT tco_preavviso.* ";
		$sFrom=" FROM tco_preavviso 
		INNER JOIN tco_preavviso_rata ON tco_preavviso_rata.idtco_preavviso=tco_preavviso.idtco_preavviso
		INNER JOIN sso_anagrafica_utente ON sso_anagrafica_utente.idutente=tco_preavviso.idtco_contribuente ";

		$sWhere='';

		$sWhere=aggiungi_condizione($sWhere, "(tco_preavviso.flag_nota_credito='0' OR tco_preavviso.flag_nota_credito IS NULL)");

		$sWhere=aggiungi_condizione($sWhere, "tco_preavviso.idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'");
		$sWhere=aggiungi_condizione($sWhere, "CHAR_LENGTH(sso_anagrafica_utente.codicefiscale)=16");

		if(!empty($sWhere))
			$sWhere=" WHERE ".$sWhere;

		$SQL=$sSelect.$sFrom.$sWhere;
		$db->query($SQL);
		$next_record=$db->next_record();
		$counter=1;
		while($next_record)
		{
			$fldidtco_preavviso=$db->f("idtco_preavviso");
			$fldformato_trasmissione=$db->f("formato_trasmissione");
			$fldidgen_ente_ufficio=$db->f("idgen_ente_ufficio");
			$fldidgen_regime_fiscale=$db->f("idgen_regime_fiscale");
			$fldidgen_tipo_documento=$db->f("idgen_tipo_documento");
			$fldidgen_natura_iva=$db->f("idgen_natura_iva");

			if(empty($fldformato_trasmissione))
			{
				$update="UPDATE tco_preavviso SET formato_trasmissione='$pformato_trasmissione' WHERE idtco_preavviso='$fldidtco_preavviso'";
				$db2->query($update);
			}

			if(empty($fldidgen_ente_ufficio))
			{
				$update="UPDATE tco_preavviso SET idgen_ente_ufficio='$pidgen_ente_ufficio' WHERE idtco_preavviso='$fldidtco_preavviso'";
				$db2->query($update);
			}

			if(empty($fldidgen_regime_fiscale))
			{
				$update="UPDATE tco_preavviso SET idgen_regime_fiscale='$pidgen_regime_fiscale' WHERE idtco_preavviso='$fldidtco_preavviso'";
				$db2->query($update);
			}

			if(empty($fldidgen_tipo_documento))
			{
				$update="UPDATE tco_preavviso SET idgen_tipo_documento='$pidgen_tipo_documento' WHERE idtco_preavviso='$fldidtco_preavviso'";
				$db2->query($update);
			}

			if(empty($fldidgen_natura_iva))
			{
				$update="UPDATE tco_preavviso SET idgen_natura_iva='$pidgen_natura_iva' WHERE idtco_preavviso='$fldidtco_preavviso'";
				$db2->query($update);
			}

			$next_record=$db->next_record();
		}

		$fldflag_fatturapa=get_db_value("SELECT flag_fatturapa FROM tco_preavviso_elaborazione WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'");
		if(empty($fldflag_fatturapa))
		{
			$update="UPDATE tco_preavviso_elaborazione SET flag_fatturapa=1 WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'";
			$db->query($update);
		}

		echo "1";
		break;

	case "conferma_periodi_siuss":
		$pstringa_periodi=get_param("_s");

		$periodo_fatta=explode("|",$pstringa_periodi);

		if(!empty($periodo_fatta))
		{
			foreach($periodo_fatta as $dettaglio_periodo)
			{
				$dettaglio=explode(";",$dettaglio_periodo);
				$fldidsso_prestazione_fatta_dettaglio=$dettaglio[0];
				$fldperiodo_inizio=invertidata($dettaglio[1],"-","/",1);
				$fldperiodo_fine=invertidata($dettaglio[2],"-","/",1);

				$update="UPDATE sso_prestazione_fatta_dettaglio SET periodo_inizio='$fldperiodo_inizio', periodo_fine='$fldperiodo_fine' WHERE idsso_prestazione_fatta_dettaglio='$fldidsso_prestazione_fatta_dettaglio'";
				$db->query($update);
			}
		}

		echo "1";

		break;

	case "load_interventi_rei":
		$pidutente=get_param("_idutente");
		$pidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='REI'");
		$aCATEGORIE=db_fill_array("select idsso_tbl_rei_progetto_mcategoria,descrizione from sso_tbl_rei_progetto_mcategoria where idsso_tbl_rei_progetto_marea='3' order by idsso_tbl_rei_progetto_mcategoria");

		foreach ($aCATEGORIE as $idsso_tbl_rei_progetto_mcategoria => $descrizione_categoria) 
		{
			$fldnote_categoria=get_db_value("select rei_nota from sso_anagrafica_rei_mprogetto where idsso_anagrafica_utente='$pidutente' and idsso_tbl_rei_progetto_mcategoria='$idsso_tbl_rei_progetto_mcategoria'");
			$aVALORI=db_fill_array("select idsso_tbl_rei_progetto_mvalore,descrizione from sso_tbl_rei_progetto_mvalore where idsso_tbl_rei_progetto_mcategoria='$idsso_tbl_rei_progetto_mcategoria' order by idsso_tbl_rei_progetto_mcategoria,valore_ordine");

			foreach ($aVALORI as $idsso_tbl_rei_progetto_mvalore => $descrizione_valore) 
			{
				$fldidsso_anagrafica_rei_progetto_mvalore=get_db_value("select idsso_anagrafica_rei_mprogetto from sso_anagrafica_rei_mprogetto where idsso_anagrafica_utente='$pidutente' and idsso_tbl_rei_progetto_mvalore='$idsso_tbl_rei_progetto_mvalore' and idsso_progetto='$pidsso_progetto' ");
				$fldidgen_tbl_dizionario_tipocampo=get_db_value("select idgen_tbl_dizionario_tipocampo from sso_tbl_rei_progetto_valore where idsso_tbl_rei_progetto_valore='$idsso_tbl_rei_progetto_mvalore'");

				if (!empty($fldidsso_anagrafica_rei_progetto_mvalore))
				{
					if($idsso_tbl_rei_progetto_mcategoria==20)	//POLITICHE DEL LAVORO
						$aINTERVENTI[2]++;
					elseif($idsso_tbl_rei_progetto_mcategoria==21  || strpos($descrizione_valore, 'A2.09')!==false)	//FORMAZIONE LAVORO
						$aINTERVENTI[3]++;
					elseif((strpos($descrizione_valore, 'A2.11') !== false) || (strpos($descrizione_valore, 'A2.02') !== false) || (strpos($descrizione_valore, 'A9.04.01') !== false) || (strpos($descrizione_valore, 'A2.10') !== false))
						$aINTERVENTI[1]++;
					else					
						$aINTERVENTI[4]++;
				}
			}
		}

		foreach($aINTERVENTI as $tipologia=>$counter_interventi)
		{
			$response.=$tipologia.";".$counter_interventi."|";
		}

		echo $response;

		break;
	case "savefinetermine":
		$pidsso_intervento_termine=get_param("_k");
		$pidsso_tbl_area=get_param("_area");
		$pidsso_tbl_servizio=get_param("_servizio");
		$pidsso_tbl_prestazione=get_param("_prestazione");
		$pidsso_tbl_budget=get_param("_budget");
		$pgg=get_param("_gg");
		$ppercentualemax=get_param("_percmax");
		if (!$pidsso_intervento_termine)
		{
			$sSQL="insert into sso_intervento_termine (idsso_tbl_area,idsso_tbl_servizio,idsso_tbl_prestazione,idsso_tbl_budget,giornodelmese,percentualemax) values('$pidsso_tbl_area','$pidsso_tbl_servizio','$pidsso_tbl_prestazione','$pidsso_tbl_budget','$pgg','$ppercentualemax')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="update sso_intervento_termine set idsso_tbl_area='$pidsso_tbl_area',idsso_tbl_servizio='$pidsso_tbl_servizio',idsso_tbl_prestazione='$pidsso_tbl_prestazione',idsso_tbl_budget='$pidsso_tbl_budget',giornodelmese='$pgg',percentualemax='$ppercentualemax' where idsso_intervento_termine='$pidsso_intervento_termine'";
			$db->query($sSQL);

		}

		break;	
	case "savefatturabeneficiario":
		list($pidutente,$pidsso_domanda_intervento,$ptipocampo)=explode("|",get_param("_k"));
		$pvalue=get_param("_value");
		$pidsso_fattura=get_param("_f");
		switch($ptipocampo)
		{
			case 'fattura':
				$pfield="fattura_numero";
				break;
			case 'liquidazionen':
				$pfield="liquidazione_numero";
				break;
			case 'liquidazioned':
				$pfield="liquidazione_data";
				$pvalue=invertidata($pvalue,"-","/",1);
				break;
		}
		$idsso_fattura_intervento=get_db_value("select idsso_fattura_intervento from sso_fattura_intervento where idsso_fattura='$pidsso_fattura' and idsso_anagrafica_utente='$pidutente' and idsso_domanda_intervento='$pidsso_domanda_intervento'");
		if (!$idsso_fattura_intervento)
		{
			$sSQL="insert into sso_fattura_intervento (idsso_fattura,idsso_anagrafica_utente,idsso_domanda_intervento,".$pfield.") values('$pidsso_fattura','$pidutente','$pidsso_domanda_intervento','$pvalue')";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="update sso_fattura_intervento set ".$pfield."='$pvalue' where idsso_fattura_intervento='$idsso_fattura_intervento'";
			$db->query($sSQL);

		}

		break;			
	case "liquidafatture":
		$pidsso_fattura=get_param("_fattura");
		$pliquidazione_numero=get_param("_numero");
		$pliquidazione_data=get_param("_data");
		$pliquidazione_data=invertidata($pliquidazione_data,"-","/",1);
		$sSQL="update sso_fattura_intervento set liquidazione_numero='$pliquidazione_numero',liquidazione_data='$pliquidazione_data' where idsso_fattura='$pidsso_fattura'";
		$db->query($sSQL);
		break;
	case "notificaFATTURA":
		$pidsso_fattura=db_string(get_param("_k"));		

		$sSQL="update sso_fattura set idsso_tbl_fattura_stato=1,data_protocollo='".date("Y-m-d")."' where idsso_fattura='$pidsso_fattura' and idsso_tbl_fattura_stato=1";
		$db->query($sSQL);

		$sSQL="insert into sso_fattura_verifica (idsso_fattura,data_richiesta,idsso_tbl_fattura_verifica) values('$pidsso_fattura','".date("Y-m-d")."',4) ";
		$db->query($sSQL);

		break;
	case "notifiche_fatture":
		$sSQL="select count(idsso_fattura) from ".DBNAME_SS.".sso_fattura where idsso_tbl_fattura_stato=1 and data_protocollo is not null";
		echo $notifiche_fatture=get_db_value($sSQL);
		break;	
	case "graduatoriaapprova":
		$pidsso_progetto=get_param("_p");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto'");
		if ($pidsso_progetto>0 and $fldidsso_graduatoria>0)
		{
			$sSQL="update sso_graduatoria set idsso_tbl_graduatoria_stato=6,data_approva='$oggi',idgen_operatore_approva='$user' where idsso_graduatoria='$fldidsso_graduatoria'";
			$db->query($sSQL);

			$sSQL="update sso_domanda inner join sso_graduatoria_utente on sso_domanda.idsso_domanda=sso_graduatoria_utente.idsso_domanda set sso_domanda.idsso_tabella_stato_domanda=4 where sso_domanda.idsso_progetto='$pidsso_progetto'";
			$db->query($sSQL);
			
			$graduatoria=new Graduatoria($fldidsso_graduatoria);
			$fldidsso_graduatoria_storico=$graduatoria->storico($pidsso_progetto,$user);

		}

		break;	
	case "graduatoriacongela":
		$pidsso_progetto=get_param("_p");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto'");
		if ($pidsso_progetto>0 and $fldidsso_graduatoria>0)
		{
			$sSQL="update sso_graduatoria set idsso_tbl_graduatoria_stato=11, data_congela='$oggi',idgen_operatore_congela='$user' where idsso_graduatoria='$fldidsso_graduatoria'";
			$db->query($sSQL);

			$sSQL="update sso_domanda inner join sso_graduatoria_utente on sso_domanda.idsso_domanda=sso_graduatoria_utente.idsso_domanda set sso_domanda.idsso_tabella_stato_domanda=4 where sso_domanda.idsso_progetto='$pidsso_progetto'";
			$db->query($sSQL);

			$graduatoria=new Graduatoria($fldidsso_graduatoria);
			$fldidsso_graduatoria_storico=$graduatoria->storico($pidsso_progetto,$user);

		}		
		break;	
	case "statoGraduatoriaPubblica":
		$pidsso_progetto=get_param("_p");
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto'");
		if ($pidsso_progetto>0 and $fldidsso_graduatoria>0)
		{
			$stato=get_db_value("select graduatoria_pubblica from sso_graduatoria where idsso_graduatoria='$fldidsso_graduatoria'");
		}	

		if(empty($stato))
			$stato=0;

		echo $stato;
		break;	
	case "graduatoriapubblica":
		$pidsso_progetto=get_param("_p");
		$pstato=get_param("_stato");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto'");
		if ($pidsso_progetto>0 and $fldidsso_graduatoria>0)
		{
			$sSQL="update sso_graduatoria set graduatoria_pubblica='$pstato' where idsso_graduatoria='$fldidsso_graduatoria'";
			$db->query($sSQL);
		}	
		break;	
	case "graduatoriacertifica":
		$pidsso_progetto=get_param("_p");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		$fldidsso_graduatoria=get_db_value("select idsso_graduatoria from sso_graduatoria where idsso_progetto='$pidsso_progetto'");
		if ($pidsso_progetto>0 and $fldidsso_graduatoria>0)
		{
			$sSQL="update sso_graduatoria set idsso_tbl_graduatoria_stato=5 where idsso_graduatoria='$fldidsso_graduatoria'";
			$db->query($sSQL);

			$graduatoria=new Graduatoria($fldidsso_graduatoria);
			$fldidsso_graduatoria_storico=$graduatoria->storico($pidsso_progetto,$user);
		}


		break;	
	case "paidiniego":
		$pidsso_domanda_intervento=get_param("_i");
		$user=verifica_utente($chiave);
		$pdiniego_data=invertidata(get_param("_data"),"-","/",1);
		$pdiniego_numero=get_param("_numero");
		$sSQL="update sso_domanda_intervento set diniego_data='$pdiniego_data',diniego_numero='$pdiniego_numero',idsso_tabella_stato_intervento=13,diniego_operatore='$user' where idsso_domanda_intervento='$pidsso_domanda_intervento'";
		$db->query($sSQL);
		break;
	case "liquidacontributi":
		$pimporto_prestazione=get_param("_i");
		$pimporto_prestazione=db_double($pimporto_prestazione);
		$pdata_prestazione=get_param("_d");
		$pdata_prestazione=invertidata($pdata_prestazione,"-","/",1);	

		$mese=date("m", strtotime($pdata_prestazione));
		$anno=date("Y", strtotime($pdata_prestazione));

		$pINTERVENTI=get_param("_l");
		$aINTERVENTI=explode("|",$pINTERVENTI);

		$counter_scaricati=0;
		$counter_scartati=0;

		if(!is_numeric($pimporto_prestazione))
		{
			$alert_numeric=true;
		}
		else
		{
			if($pimporto_prestazione==0)
				$alert_importo_nullo=true;
		}

		foreach ($aINTERVENTI as $key => $idsso_domanda_intervento) 
		{
			$pai=new Pai($idsso_domanda_intervento);

			$alert_importo=false;

			if(($pimporto_prestazione-$pai->get_quantita_rimanente(false))> 0.0099)
				$alert_importo=true;
				
			if(!$alert_importo && !$alert_importo_nullo && !$alert_numeric)
			{
				$sSQL="INSERT INTO sso_prestazione_pianificata 
					(idsso_domanda_intervento, idsso_domanda_prestazione, idutente, data_pianificata, mese, anno, idsso_tbl_prestazione, orario_inizio, orario_fine, quantita)
					VALUES 
					('$pai->idsso_domanda_intervento','$pai->idsso_domanda_prestazione','$pai->idutente','$pdata_prestazione','$mese','$anno','$pai->idsso_tbl_prestazione','$orario_inizio','$orario_inizio', '1')";
				$db->query($sSQL);
				$fldidsso_prestazione_pianificata = mysql_insert_id($db->link_id());
				
				$sSQL="INSERT INTO sso_prestazione_fatta_dettaglio (
				idsso_prestazione_pianificata,idutente,data_prestazione,
				idsso_tbl_prestazione,orario_inizio,orario_fine,
				quantita,tariffa_prestazione,importo_prestazione,
				note,numero_mesi,importo_mensile,periodo_inizio,periodo_fine
				) VALUES (
				'$fldidsso_prestazione_pianificata','$pai->idutente','$pdata_prestazione',
				'$pai->idsso_tbl_prestazione','$orario_inizio','$orario_inizio',
				'1','$pimporto_prestazione','$pimporto_prestazione',
				'$pnote','$pnumero_mesi','$pimporto_mensile','$pdata_prestazione','$pdata_prestazione'
				)";
				$db->query($sSQL);
				$fldidsso_prestazione_fatta_dettaglio = mysql_insert_id($db->link_id());

				$counter_scaricati++;
			}
			else
			{
				$aSCARTI[]=$idsso_domanda_intervento;
				$counter_scartati++;
			}
		}

		echo "1|".$counter_scaricati."|".$counter_scartati."|".json_encode($aSCARTI);

		break;

	case "pai_info":
		$pidsso_domanda_intervento=get_param("_id");

		$pai=new Pai($pidsso_domanda_intervento);

		echo $pai->beneficiario->nominativo."|".$pai->prestazione->descrizione."|".$pai->data_inizio_formattata."|".$pai->data_fine_formattata."|".$pai->get_quantita_rimanente(true);

	break;

	case "load_percorsi_cura_dettaglio":
		$pidpercorso=get_param("_idpercorso");

		header("Content-type:text/xml");
		$fldxml="<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
		$fldxml.="\n <complete>";
		$fldxml.="\n <option value=\"0\"> </option>";

		$sSQL = "SELECT idsso_tbl_svamdi_percorso_cura_dettaglio,descrizione 
		FROM sso_tbl_svamdi_percorso_cura_dettaglio 
		WHERE idsso_tbl_svamdi_percorso_cura='$pidpercorso' 
		ORDER BY descrizione";
		$db->query($sSQL);
		$next_record=$db->next_record();
		while($next_record)
		{
			$fldidsso_tbl_svamdi_percorso_cura_dettaglio=$db->f("idsso_tbl_svamdi_percorso_cura_dettaglio");
			$flddescrizione=$db->f("descrizione");
			$fldxml.="\n <option value=\"".$fldidsso_tbl_svamdi_percorso_cura_dettaglio."\">".stringXMLClean($flddescrizione)."</option>";
			$next_record=$db->next_record();
		}
		$fldxml.="\n </complete>";
			
		print($fldxml);
		break;

	case "protocolla_documento_paleo":
		$pfile=get_param("file");
		$pidfile=get_param("idfile");
		$ptable=get_param("table");

		if(!empty($pfile) && !empty($pidfile) && !empty($ptable))
		{
			require_once("../librerie/class.paleo.php");

			$paleo=new Paleo();

			$array_file=explode("/",$pfile);
			$aVALUES=array_values(array_slice($array_file, -1));
			$filename=$aVALUES[0];
			$ext = pathinfo($filename, PATHINFO_EXTENSION);

			$aPARAMS["oggetto"]="Protocollazione documento per conservazione";
			$aPARAMS["filename"]=$filename;
			$aPARAMS["file"]=$pfile;
			$aPARAMS["file_ext"]=$ext;
			$aPARAMS["filemime"]=mime_content_type($filename);

			$response=$paleo->ProtocollazioneEntrata($aPARAMS);
			//print_r($response);

			if(!empty($response["Numero"]))
			{
				$insert="INSERT INTO sso_paleo_documento(iddocumento,tabella,DocNumber,Oggetto,DataProtocollazione,Numero,Registro,Segnatura,Mittente) VALUES('$pidfile','$ptable','".$response["DocNumber"]."','".$response["Oggetto"]."','".$response["DataProtocollazione"]."','".$response["Numero"]."','".$response["Registro"]."','".$response["Segnatura"]."','".$response["Mittente"]."')";
				$db->query($insert);
				echo "1";
			}
		}
		else
			echo "0";
	break;

	case "archivia_documento_paleo":
		$pfile=get_param("file");
		$pidfile=get_param("idfile");
		$ptable=get_param("table");

		if(!empty($pfile) && !empty($pidfile) && !empty($ptable))
		{
			require_once("../librerie/class.paleo.php");

			$paleo=new Paleo();

			$array_file=explode("/",$pfile);

			$aVALUES=array_values(array_slice($array_file, -1));
			$filename=$aVALUES[0];
			$ext = pathinfo($filename, PATHINFO_EXTENSION);

			$aPARAMS["oggetto"]="Archiviazione documento per conservazione";
			$aPARAMS["filename"]=$filename;
			$aPARAMS["file"]=$pfile;
			$aPARAMS["file_ext"]=$ext;
			$aPARAMS["filemime"]=mime_content_type($filename);

			$response=$paleo->ArchiviaDocumentoInterno($aPARAMS);
			//print_r($response);

			if(!empty($response["DocNumber"]))
			{
				$insert="INSERT INTO sso_paleo_archiviazione_documento(iddocumento,tabella,DocNumber,Oggetto,DataDocumento,SegnaturaDocumento) VALUES('$pidfile','$ptable','".$response["DocNumber"]."','".$response["Oggetto"]."','".$response["DataDocumento"]."','".$response["SegnaturaDocumento"]."')";
				$db->query($insert);
				echo "1";
			}
			else
				echo "0";
		}
		else
			echo "0";
	break;

	case "protocolla_documento_paleo_list":
		$plist=get_param("list");

		if(!empty($plist))
		{
			require_once("../librerie/class.paleo.php");

			$paleo=new Paleo();

			$aLIST=explode("|",$plist);

			foreach($aLIST as $dett_doc)
			{
				$aFILE=explode("-",$dett_doc);
				$pidfile=$aFILE[0];
				$ptable=$aFILE[1];

				if(!empty($pidfile) && !empty($ptable))
				{
					$field_idtable="id".$ptable;
					$pfile=get_db_value("SELECT allegato_name FROM ".$ptable." WHERE ".$field_idtable."='$pidfile'");
					$array_file=explode("/",$pfile);

					$aVALUES=array_values(array_slice($array_file, -1));
					$filename=$aVALUES[0];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);

					$aPARAMS["oggetto"]="Protocollazione documento per conservazione";
					$aPARAMS["filename"]=$filename;
					$aPARAMS["file"]=$pfile;
					$aPARAMS["file_ext"]=$ext;
					$aPARAMS["filemime"]=mime_content_type($filename);

					$response=$paleo->ProtocollazioneEntrata($aPARAMS);
					//print_r($response);

					if(!empty($response["Numero"]))
					{
						$insert="INSERT INTO sso_paleo_documento(iddocumento,tabella,DocNumber,Oggetto,DataProtocollazione,Numero,Registro,Segnatura,Mittente) VALUES('$pidfile','$ptable','".$response["DocNumber"]."','".$response["Oggetto"]."','".$response["DataProtocollazione"]."','".$response["Numero"]."','".$response["Registro"]."','".$response["Segnatura"]."','".$response["Mittente"]."')";
						$db->query($insert);
					}
				}
			}

			echo "1";
		}
		else
			echo "0";
	break;

	case "archivia_documento_paleo_list":
		$plist=get_param("list");

		if(!empty($plist))
		{
			require_once("../librerie/class.paleo.php");

			$paleo=new Paleo();

			$aLIST=explode("|",$plist);

			foreach($aLIST as $dett_doc)
			{
				$aFILE=explode("-",$dett_doc);
				$pidfile=$aFILE[0];
				$ptable=$aFILE[1];

				if(!empty($pidfile) && !empty($ptable))
				{
					$field_idtable="id".$ptable;
					$pfile=get_db_value("SELECT allegato_name FROM ".$ptable." WHERE ".$field_idtable."='$pidfile'");
					$array_file=explode("/",$pfile);

					$aVALUES=array_values(array_slice($array_file, -1));
					$filename=$aVALUES[0];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);

					$aPARAMS["oggetto"]="Archiviazione documento per conservazione";
					$aPARAMS["filename"]=$filename;
					$aPARAMS["file"]=$pfile;
					$aPARAMS["file_ext"]=$ext;
					$aPARAMS["filemime"]=mime_content_type($filename);

					$response=$paleo->ArchiviaDocumentoInterno($aPARAMS);
					//print_r($response);

					if(!empty($response["DocNumber"]))
					{
						$insert="INSERT INTO sso_paleo_archiviazione_documento(iddocumento,tabella,DocNumber,Oggetto,DataDocumento,SegnaturaDocumento) VALUES('$pidfile','$ptable','".$response["DocNumber"]."','".$response["Oggetto"]."','".$response["DataDocumento"]."','".$response["SegnaturaDocumento"]."')";
						$db->query($insert);
					}
				}
			}

			echo "1";
		}
		else
			echo "0";
	break;

	case "unset_user_back":
		$chiave=get_cookieuser();
		$fldidgen_utente=verifica_utente($chiave);

		$data=tosql(date("Y-m-d"),"Text");
        $ora=tosql(date("H:i:s"),"Text");
		$ip=$_SERVER['REMOTE_ADDR'];
		$ip = tosql($ip,'Text');

        $db->query("INSERT INTO log_utente (ip,data,ora,chiave,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave','$fldidgen_utente','logout')");

		setcookie('iccsuser', null , time()+18000 , "/", $_SERVER['HTTP_HOST'], false, true);
	break;

	case "unset_user_front":
		$chiave_front=get_cookieuser();
		$fldidgen_utente=verifica_eutente($chiave_front);

		$data=tosql(date("Y-m-d"),"Text");
        $ora=tosql(date("H:i:s"),"Text");
		$ip=$_SERVER['REMOTE_ADDR'];
		$ip = tosql($ip,'Text');
			
        $db->query("INSERT INTO log_utente (ip,data,ora,chiave_front,idutente,attivita) VALUES ($ip,$data,$ora,'$chiave_front','$fldidgen_utente','logout')");

		setcookie('iccsuser_front', null , time()+18000 , "/", $_SERVER['HTTP_HOST'], false, true);
	break;

	case "check_date_intervento":
		$pidsso_domanda_intervento=get_param("_id");

		$pdata_inizio=get_param("_inizio");
		$pdata_fine=get_param("_fine");

		$pdata_inizio=invertidata($pdata_inizio,"-","/",1);
		$pdata_fine=invertidata($pdata_fine,"-","/",1);

		$timpestamp_inizio=strtotime($pdata_inizio);
		$timestamp_fine=strtotime($pdata_fine);

		$nonvalide=0;
		
		$sSQL="SELECT sso_prestazione_fatta_dettaglio.idsso_prestazione_fatta_dettaglio,sso_prestazione_fatta_dettaglio.data_prestazione
		FROM sso_prestazione_fatta_dettaglio 
		INNER JOIN sso_prestazione_pianificata ON sso_prestazione_pianificata.idsso_prestazione_pianificata=sso_prestazione_fatta_dettaglio.idsso_prestazione_pianificata
		INNER JOIN sso_domanda_intervento ON sso_domanda_intervento.idsso_domanda_intervento= sso_prestazione_pianificata.idsso_domanda_intervento
		WHERE sso_domanda_intervento.idsso_domanda_intervento='$pidsso_domanda_intervento'";
		$db->query($sSQL);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_prestazione_fatta_dettaglio=$db->f("idsso_prestazione_fatta_dettaglio");
			$flddata_prestazione=$db->f("data_prestazione");

			$timestamp_prestazione=strtotime($flddata_prestazione);

			if($timestamp_prestazione<$timpestamp_inizio || $timestamp_prestazione>$timestamp_fine)
				$nonvalide++;

			$res=$db->next_record();
		}

		if($nonvalide>0)
			echo "false|".$nonvalide;
		else
			echo "true";
	break;

	case "modifica_note":
		$pidtco_preavviso_elaborazione=get_param("_id");
		$pnote=get_param("_note");
		$pnote=db_string($pnote);

		if(!empty($pidtco_preavviso_elaborazione))
		{
			$update="UPDATE tco_preavviso_elaborazione SET note='$pnote' WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'";
			$db->query($update);
			echo "1";
		}
		break;

	case "modifica_data_elaborazione":
		$pidtco_preavviso_elaborazione=get_param("_id");
		$pdata=get_param("_data");
		$pdata=db_string($pdata);
		$pdata=invertidata($pdata,"-","/",1);

		if(!empty($pidtco_preavviso_elaborazione))
		{
			$update="UPDATE tco_preavviso_elaborazione SET data_elaborazione='$pdata' WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'";
			$db->query($update);

			$update="UPDATE tco_preavviso SET data_elaborazione='$pdata' WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'";
			$db->query($update);
			
			echo "1";
		}
		break;

	case "rsamovimento":
		$pidsso_domanda_intervento=get_param("_intervento");
		$pai=new Pai($pidsso_domanda_intervento);
		$pdata_movimento=invertidata(get_param("_data"),"-","/",1);
		$ptipo_movimento=get_param("_tipo");
		$sSQL="insert into sso_prestazione_pianificata (
		idsso_domanda_intervento,
		idsso_domanda_prestazione,
		idutente,
		data_pianificata,
		idsso_ente_assistenza,
		idsso_tbl_prestazione,
		quantita,
		idsso_tbl_pianificata_motivo) 
		values(".
		$pai->idsso_domanda_intervento.",".
		$pai->idsso_domanda_prestazione.",".
		$pai->idutente.",'".
		$pdata_movimento."',".
		$pai->idsso_ente_servizio.",".
		$pai->idsso_tbl_prestazione.",".
		"1,'".
		$ptipo_movimento."')";
		$db->query($sSQL);

		$pidsso_prestazione_pianificata=mysql_insert_id($db->link_id());

		$sSQL="insert into sso_prestazione_fatta_dettaglio (
		idsso_prestazione_pianificata,
		idutente,
		data_prestazione,
		idsso_ente_assistenza,
		idsso_tbl_prestazione,
		quantita) 
		values(".
		$pidsso_prestazione_pianificata.",".
		$pai->idutente.",'".
		$pdata_movimento."',".
		$pai->idsso_ente_servizio.",".
		$pai->idsso_tbl_prestazione.",".
		"1)";		
		$db->query($sSQL);

		switch($ptipo_movimento)
		{
			case 2:	//decesso
				// Aggiorno decesso
				$sSQL="update sso_anagrafica_utente set data_decesso='$pdata_movimento' where idutente='".$pai->idutente."'";
				$db->query($sSQL);				
				//$pdata_movimento = date("Y-m-d", strtotime("-1 day", strtotime($pdata_movimento)));
				$sSQL="update sso_domanda_intervento set data_fine='$pdata_movimento' where idsso_domanda_intervento='$pidsso_domanda_intervento'";
				$db->query($sSQL);
				break;
			case 9:	//dimissione
				// chiudo il PAI
				$sSQL="update sso_domanda_intervento set data_fine='$pdata_movimento' where idsso_domanda_intervento='$pidsso_domanda_intervento'";
				$db->query($sSQL);
				break;
		}

		break;
	case "oremese":
		
		$pore=get_param("_ore");	
		$pmese=get_param("_mese");
		$panno=get_param("_anno");
		$fldidsso_domanda_intervento=get_param("_intervento");
		if($fldidsso_domanda_intervento>0)
		{
			$pai=new Pai($fldidsso_domanda_intervento);
			$result=$pai->scaricaOREMESE($pmese,$panno,$pore);
		}

		/*
		$pore=str_replace(".", ",", $pore);
		$sORE=explode(",",$pore);
		$fldhh=str_pad($sORE[0],2,"0",STR_PAD_LEFT);
		$fldmm=str_pad($sORE[1],2,"0",STR_PAD_LEFT);
		$pore=$fldhh.":".$fldmm;
		if ($pore=="00:00")
			$pore=NULL;

		$pora_inizio="00:00";
		$pidsso_domanda_prestazione=$pai->idsso_domanda_prestazione;
		$pidutente=$pai->idutente;	
		$periodo_inizio=$panno."-".str_pad($pmese,2,"0",STR_PAD_LEFT)."-01";
		$periodo_fine=$panno."-".str_pad($pmese,2,"0",STR_PAD_LEFT)."-".str_pad(date("t",$time),2,"0",STR_PAD_LEFT);
		$pdata_pianificata=$panno."-".str_pad($pmese, 2,"0",STR_PAD_LEFT)."-".date("t",mktime(0,0,0,$pmese,1,$panno));

		if($fldidsso_domanda_intervento>0)
		{
			
			$fldtipologia_um=($pai->idsso_tbl_um != UM_BUDGET) ? get_tipo_um($pai->idsso_tbl_um) : get_tipo_um_catalogo_fornitore($pai->idsso_ente_servizio, $pai->idsso_tbl_prestazione, $pdata_pianificata);
			switch($fldtipologia_um)
			{					
				case TIPO_UM_UNITA:
				case TIPO_UM_IMPORTO:
					switch($pai->idsso_tbl_um)
					{
						case UM_GIORNO:
							$quantita=str_replace(",",".",get_param("_ore"));
							$minuti=0;
							break;
						default:
							$quantita=1;
							$minuti=0;
							break;
					}
					
					break;
					
				case TIPO_UM_MINUTI:	
					$quantita=quantita_ore("00:00", $pore);
					$minuti=elabora_minuti("00:00", $pore);

					break;

				default:
					$quantita=0;
					$minuti=0;
					break;
			}

			if(!empty($quantita))
			{
				$fldidsso_prestazione_pianificata=get_db_value("select idsso_prestazione_pianificata from sso_prestazione_pianificata where idsso_domanda_intervento='$fldidsso_domanda_intervento' and data_pianificata='$pdata_pianificata' and mese='$pmese' and anno='$panno' and orario_inizio='00:00:00'");
				$fldidsso_prestazione_fatta_dettaglio=get_db_value("SELECT idsso_prestazione_fatta_dettaglio FROM sso_prestazione_fatta_dettaglio where idsso_prestazione_pianificata='$fldidsso_prestazione_pianificata'");
				

				$flddisponibili=get_quantita_disponibile($pdata_pianificata, $pai);
				
				//if($quantita<=$flddisponibili)
				//{
					$fldquota_variabile=$pai->get_tariffa_rendicontazione($pdata_pianificata);
					$importo_prestazione=$fldquota_variabile*$quantita;
					$fldtipologia_um=($pai->idsso_tbl_um != UM_BUDGET) ? get_tipo_um($pai->idsso_tbl_um) : get_tipo_um_catalogo_fornitore($pai->idsso_ente_servizio, $pai->idsso_tbl_prestazione, $pdata_pianificata);
					switch($fldtipologia_um)
					{					
						case TIPO_UM_UNITA:
						case TIPO_UM_IMPORTO:
							$importo_prestazione=$fldquota_variabile*$quantita;
							break;
							
						case TIPO_UM_MINUTI:								
							$importo_prestazione=$fldquota_variabile*$minuti/60;
							$importo_prestazione=round($importo_prestazione, getDECIMALI());
							break;
					}
						
					if ($fldidsso_prestazione_fatta_dettaglio>0)
					{
						$sSQL="update sso_prestazione_fatta_dettaglio set orario_inizio='$pora_inizio',orario_fine='$pore',quantita='$quantita',prestazione_minuti='$minuti',tariffa_prestazione='$fldquota_variabile',importo_prestazione='$importo_prestazione' where idsso_prestazione_fatta_dettaglio='$fldidsso_prestazione_fatta_dettaglio'";
						$db->query($sSQL);
					}
					else
					{
						$qryInsert="INSERT INTO sso_prestazione_pianificata
						(idsso_domanda_intervento, 
						idsso_domanda_prestazione, 
						idutente, 
						data_pianificata, 
						mese, 
						anno, 
						idsso_ente_assistenza, 
						idgen_operatore, 
						idsso_tbl_prestazione, 
						orario_inizio, 
						orario_fine, 
						quantita, 
						note)
						VALUES
						('$fldidsso_domanda_intervento', 
						'$pidsso_domanda_prestazione', 
						'$pidutente', 
						'$pdata_pianificata', 
						'$pmese', 
						'$panno', 
						'$pai->idsso_ente_servizio', 
						'$pidgen_operatore', 
						'$pai->idsso_tbl_prestazione', 
						'$pora_inizio', 
						'$pore', 
						'$quantita', 
						'$pnote')";
						$db->query($qryInsert);
						$pidsso_prestazione_pianificata = mysql_insert_id($db->link_id());	

						$qryInsert="INSERT INTO sso_prestazione_fatta_dettaglio
						(idsso_prestazione_fatta,
						idsso_prestazione_pianificata,
						idutente, 
						data_prestazione, 
						idsso_ente_assistenza,
						idgen_operatore,
						idsso_tbl_prestazione,
						orario_inizio,
						orario_fine,
						quantita,
						prestazione_minuti,
						tariffa_prestazione,
						importo_prestazione,
						note,
						flag_mobile,
						periodo_inizio,
						periodo_fine)
						VALUES
						('0', 
						'$pidsso_prestazione_pianificata',
						'$pidutente', 
						'$pdata_pianificata', 
						'$pai->idsso_ente_servizio', 
						'$pidgen_operatore', 
						'$pai->idsso_tbl_prestazione', 
						'$pora_inizio', 
						'$pore', 
						'$quantita', 
						'$minuti',
						'$fldquota_variabile',
						'$importo_prestazione', 
						'$pnote',
						'0',
						'$periodo_inizio',
						'$pdata_pianificata')";
						$db->query($qryInsert);
					}	
					
				//}	
				//else
				//{
				//	$alert_quantita=true;
				//	$errore="errore";
				//}
			}
			else
			{
				$alert_orario=true;
				$errore="errore";
			}
		}
		else
		{
			$alert_check=true;	
			$errore="errore";
		}
		*/

		break;
	case "deletefatta":
		$pidsso_prestazione_fatta_dettaglio=get_param("_fatta");
		$prestazioneFATTA=new Prestazione_eseguita($pidsso_prestazione_fatta_dettaglio);
		$idsso_prestazione_pianificata=$prestazioneFATTA->idsso_prestazione_pianificata;
		$sSQL="delete from sso_prestazione_fatta_dettaglio where idsso_prestazione_fatta_dettaglio='$pidsso_prestazione_fatta_dettaglio'";
		$db->query($sSQL);
		$sSQL="delete from sso_prestazione_pianificata where idsso_prestazione_pianificata='$idsso_prestazione_pianificata'";
		$db->query($sSQL);
		break;
	case "preavvisoricalcola":
	case "ricalcolapreavviso":
			$pidtco_preavviso=get_param("_p");
			$preavviso=new Preavviso($pidtco_preavviso);
			$fldidsso_domanda_intervento=$preavviso->preavviso_altro[0]['idtco_dichiarazione'];

			$dataigenerali=new datiGENERALI();
			$pflag_rsa=$dataigenerali->flag_rsa;

			$pai=new Pai($fldidsso_domanda_intervento);
			$prestazione=new Prestazione($pai->idsso_tbl_prestazione);
			$codice_prestazione=$prestazione->codice_prestazione;
			
			$compartecipazione_tariffa=loadTariffaRendicontazione($fldidsso_domanda_intervento, $pai->idsso_tbl_prestazione, $pai->idsso_ente_servizio, '0', $pai->data_inizio, $pai->data_fine, $pai->idsso_tbl_um, $pai->idutente);

			if($pflag_rsa)
			{
				$quota_sociale=true;
				$fldcompartecipazione_tariffa=$pai->compartecipazione_tariffa;
				if(empty($preavviso->data_inizio) || empty($preavviso->data_fine))
				{
					$mese_inizio=$preavviso->mese_inizio;
					$mese_fine=$preavviso->mese_fine;
					$anno_imposta=$preavviso->anno_imposta;

					$flddata_inizio="01-".str_pad($mese_inizio, 2, "0", STR_PAD_LEFT)."-".$anno_imposta;
					$flddata_fine=date("t-m-Y", strtotime($anno_imposta."-".str_pad($mese_fine, 2, "0", STR_PAD_LEFT)."-01"));
				}
				else
				{
					$flddata_inizio=$preavviso->data_inizio;
					$flddata_fine=$preavviso->data_fine;

					//$flddata_inizio=date("d-m-Y", strtotime($flddata_inizio));
					//$flddata_fine=date("d-m-Y", strtotime($flddata_inizio));
				}

				$giorni=giorni(invertidata($flddata_inizio,"/","-",2),invertidata($flddata_fine,"/","-",2));

				list($movimentiRSA,$ggMOVIMENTI)=explode("|",getMOVIMENTIRSA($fldidsso_domanda_intervento,$flddata_inizio,$flddata_fine,true,$quota_sociale));
				$data15=substr($flddata_inizio,0,8)."15";
				if ($codice_prestazione=='A3.02' or $codice_prestazione=='A3.01')
				{
						
					$quota_ssn=$pai->get_tariffa_ssn();

					$fldquantita_fatta=$giorni;

					switch($prestazione->idgen_tbl_um_variabile)
					{
						case 2:
							$fldquantita_fatta=1;
							break;
						case 15:
							if (($pai->data_inizio>=$flddata_inizio and $pai->data_inizio<=$flddata_fine) && ($pai->data_fine>=$flddata_inizio and $pai->data_fine<=$flddata_fine))
							{
								
								$fldquantita_fatta=giorni($pai->data_inizio_formattata,$pai->data_fine_formattata);
							}
							elseif($pai->data_inizio>=$flddata_inizio and $pai->data_inizio<=$flddata_fine)
							{
								
								$fldquantita_fatta=giorni($pai->data_inizio_formattata,invertidata($flddata_fine,"/","-",2));
							}
							elseif($pai->data_fine>=$flddata_inizio and $pai->data_fine<=$flddata_fine)
							{
								
								$fldquantita_fatta=giorni(invertidata($flddata_inizio,"/","-",2),$pai->data_fine_formattata);
							}
							$fldquantita_fatta=$fldquantita_fatta-$ggMOVIMENTI;
							break;
					}	
					
					//Verifico se la data inizio o fine è > o < del 15 del mese
					if ($prestazione->codice_prestazione=="A3.01" && $fldcompartecipazione_tariffa>0)
					{
					
						if ($pai->data_inizio>$data15 || $pai->data_fine<=$data15)
						{
							$fldcompartecipazione_tariffa=round($fldcompartecipazione_tariffa/2,2);
						}
					}

					if ($fldcompartecipazione>0)
					{
						$importo_compartecipazione=$fldimporto_fatta*$fldcompartecipazione/100;
					}
					elseif($fldcompartecipazione_tariffa>0)
					{
						$importo_compartecipazione=$fldquantita_fatta*$fldcompartecipazione_tariffa;
					}
					else
						$importo_compartecipazione=0;

					if ($codice_prestazione=="A3.01")
					{
						if (($pai->data_inizio>=$flddata_inizio and $pai->data_inizio<=$flddata_fine) && ($pai->data_fine>=$flddata_inizio and $pai->data_fine<=$flddata_fine))
						{
							$fldquantita_fatta=giorni($pai->data_inizio_formattata,$pai->data_fine_formattata);
						}
						elseif($pai->data_inizio>=$flddata_inizio and $pai->data_inizio<=$flddata_fine)
						{
							$fldquantita_fatta=giorni($pai->data_inizio_formattata,invertidata($flddata_fine,"/","-",2));
						}
						elseif($pai->data_fine>=$flddata_inizio and $pai->data_fine<=$flddata_fine)
						{
							$fldquantita_fatta=giorni(invertidata($flddata_inizio,"/","-",2),$pai->data_fine_formattata);
						}
						else
						{
							$fldquantita_fatta=giorni(invertidata($flddata_inizio,"/","-",2),invertidata($flddata_fine,"/","-",2));
						}
						$fldquantita_fatta=$fldquantita_fatta-$ggMOVIMENTI;

					}

					$fldimporto_fatta=$db->f("compartecipazione_tariffa");
				}

				//$importo_compartecipazione=$fldquantita_fatta*$compartecipazione_tariffa;
				$fldsaldo=$importo_compartecipazione;
			}
			else
				$fldsaldo=$compartecipazione_tariffa*(-1);

			//echo $fldsaldo;
			$sSQL="update tco_preavviso set imposta='$fldsaldo',importo_totale='$fldsaldo',importo_totalea='$fldsaldo' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			$sSQL="update tco_preavviso_altro set imposta='$fldsaldo' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			$sSQL="update tco_preavviso_rata set imposta='$fldsaldo' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			break;

		case "modificaimporto_preavviso":
			$fldidgen_operatore=verifica_utente($chiave);
			$pidtco_preavviso=get_param("_p");
			$pimporto=get_param("_importo");

			$pimporto=db_double($pimporto);
			//$pimporto=$pimporto*(-1);

			$pimporto_vecchio=get_db_value("select importo_totale from tco_preavviso where idtco_preavviso='$pidtco_preavviso'");
			$pimporto_vecchio=abs($pimporto_vecchio);

			$update="update tco_preavviso set imposta='$pimporto', importo_totale='$pimporto', importo_totalea='$pimporto' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($update);

			$update="update tco_preavviso_altro set imposta='$pimporto' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($update);

			$update="update tco_preavviso_rata set imposta='$pimporto' where idtco_preavviso='$pidtco_preavviso'";
			$db->query($update);

			$pimporto=abs($pimporto);
			$pdata=date("Y-m-d");
			$pora=date("H:i:s");

			$insert="insert into log_preavviso(idoperatore,data,ora,idtco_preavviso,importo_nuovo,importo_vecchio) values('$fldidgen_operatore','$pdata','$pora','$pidtco_preavviso','$pimporto','$pimporto_vecchio')";
			$db->query($insert);
		break;

		case "eliminapreavviso":
			$pidtco_preavviso=get_param("_p");
			$sSQL="delete from tco_preavviso where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			$sSQL="delete from tco_preavviso_rata where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			$sSQL="delete from tco_preavviso_altro where idtco_preavviso='$pidtco_preavviso'";
			$db->query($sSQL);
			break;

		case "check_cellulare":
			$pcodicefiscale=get_param("_cf");
			$pnumero=get_param("_cellulare");

			$fldcellulare=get_db_value("SELECT cellulare FROM sso_anagrafica_utente WHERE codicefiscale='$pcodicefiscale'");
			$fldtelefono=get_db_value("SELECT telefono FROM sso_anagrafica_utente WHERE codicefiscale='$pcodicefiscale'");
			if(!empty($fldcellulare))
			{
				if($fldcellulare==$pnumero)
					echo "true";
				else
				{
					if($fldtelefono==$pnumero)
						echo "true";
					else
						echo "false";
				}
			}
			else
			{
				if(!empty($fldtelefono))
				{
					if($fldtelefono==$pnumero)
						echo "true";
					else
						echo "false";
				}
				else
					echo "false";
			}
			break;		
		case "editpelaborazione":
			list($pidtco_preavviso_elaborazione,$pfield)=explode("|",get_param("_k"));
			$pvalue=get_param("_value");

			if(!empty($pidtco_preavviso_elaborazione) && is_numeric($pidtco_preavviso_elaborazione) && $pfield)
			{

				$update="UPDATE tco_preavviso_elaborazione SET $pfield='$pvalue' WHERE idtco_preavviso_elaborazione='$pidtco_preavviso_elaborazione'";
				$db->query($update);
				
			}
			break;

		case "mergeelaborazione":
			$pidtco_preavviso_origine=get_param("_id");
			$pidtco_preavviso_destinazione=get_param("_new");
			if ($pidtco_preavviso_origine && $pidtco_preavviso_destinazione)
			{
				$sSQL="update tco_preavviso set idtco_preavviso_elaborazione='$pidtco_preavviso_destinazione' where idtco_preavviso_elaborazione='$pidtco_preavviso_origine'";
				$db->query($sSQL);
				$sSQL="update tco_preavviso_altro set idtco_preavviso_elaborazione='$pidtco_preavviso_destinazione' where idtco_preavviso_elaborazione='$pidtco_preavviso_origine'";
				$db->query($sSQL);
				$sSQL="update tco_preavviso_rata set idtco_preavviso_elaborazione='$pidtco_preavviso_destinazione' where idtco_preavviso_elaborazione='$pidtco_preavviso_origine'";
				$db->query($sSQL);
				//$sSQL="update tco_preavviso_prestazionefatta set idtco_preavviso_elaborazione='$pidtco_preavviso_destinazione' where idtco_preavviso_elaborazione='$pidtco_preavviso_origine'";
				//$db->query($sSQL);
				$sSQL="delete from tco_preavviso_elaborazione where idtco_preavviso_elaborazione='$pidtco_preavviso_origine'";
				$db->query($sSQL);

			}
			break;

		case "load_plessi_eta":
			$pidsso_progetto=get_param("_idprogetto");
			$panno=get_param("_anno");
			$pdata=get_param("_data");
			$pdata=invertidata($pdata,"-","/",1);

			$aISTITUTI=getIstitutiNascita($pdata,$panno);
			if(empty($aISTITUTI))
				echo "0";
			else
			{
				$fldnumero_priorita_istituti=get_db_value("SELECT numero_priorita_istituti FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");

				$fldflag_anticipo=get_db_value("SELECT flag_anticipo FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
				if($fldflag_anticipo)
				{
					$flddescrizione_anticipo=get_db_value("SELECT descrizione_anticipo FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
					$style_anticipo='';
				}
				else
				{
					$style_anticipo='display:none;';
				}

				$fldflag_posticipo=get_db_value("SELECT flag_posticipo FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
				if($fldflag_posticipo)
				{
					$flddescrizione_posticipo=get_db_value("SELECT descrizione_posticipo FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
					$style_posticipo='';
				}
				else
				{
					$style_posticipo='display:none;';
				}

				$fldflag_tempo_prolungato=get_db_value("SELECT flag_tempo_prolungato FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
				if($fldflag_tempo_prolungato)
				{
					$flddescrizione_tempo_proluganto=get_db_value("SELECT descrizione_tempo_prolungato FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
					$style_tempo_prolungato='';
				}
				else
				{
					$style_tempo_prolungato='display:none;"';
				}

				$response='<div class="form-group">
					<div class="col-sm-9 col-sm-offset-2">
						<br>
						<table id="tbl_plessi" data-toggle="table" class="table table-hover table-condensed" >
							<thead>
								<tr class="default">
									<th style="width: 5%;" class="intestazioneTabella text-info"></th>
									<th style="width: 15%;" class="intestazioneTabella text-info">Priorità</th>
									<th style="width: 15%;" class="intestazioneTabella text-info">Ciclo d\'istruzione</th>
									<th style="width: 15%;" class="intestazioneTabella text-info">Plesso scolastico</th>
									<th style="width: 10%; '.$style_anticipo.'" class="intestazioneTabella text-info">
										'.$flddescrizione_anticipo.'
									</th>
									<th style="width: 10%; '.$style_posticipo.'" class="intestazioneTabella text-info">
										'.$flddescrizione_posticipo.'
									</th>
									<th style="width: 15%; '.$style_tempo_prolungato.'" class="intestazioneTabella text-info">
										'.$flddescrizione_tempo_proluganto.'
									</th>
								</tr> 
							</thead>
							<tbody>';

				//print_r($aISTITUTI);
				$sSelect="select sso_istituto.* 
				from ".DBNAME_SS.".sso_istituto ";

				$sWhere="";
				foreach($aISTITUTI as $idistituto)
				{
					$sWhere=aggiungi_condizione_or($sWhere, "sso_istituto.idsso_istituto='$idistituto'");
				}

				if(!empty($sWhere))
					$sWhere=" WHERE ".$sWhere;

				$sOrder=" ORDER BY sso_istituto.denominazione";

				$sSQL=$sSelect.$sWhere.$sOrder;

				$number_plessi=get_db_value("SELECT COUNT(*) FROM (".$sSQL.") AS TEMP");

				$db->query($sSQL);
				$res = $db->next_record();
				$righe="";
				$pcounter_istituti=0;
				while($res)
				{
					$fldistituto=$db->f("idsso_istituto");
					$flddenominazione=$db->f("denominazione");

					$fldora_inizio=$db->f("ora_inizio");
					$fldora_fine=$db->f("ora_fine");
					$fldflag_tipo_istituto=$db->f("flag_tipo_istituto");
					$fldflag_anticipo=$db->f("flag_anticipo");
					$fldflag_posticipo=$db->f("flag_posticipo");
					$fldflag_tempo_prolungato=$db->f("flag_tempo_prolungato");
					$cod_via_to=$db->f("sicra_codicevia");
					$cod_civico_to=$db->f("sicra_civico");
					$fldidsso_tabella_tipologia_istituto=$db->f("idsso_tabella_tipologia_istituto");
					$fldtipologia_istituto=get_db_value("SELECT descrizione FROM sso_tabella_tipologia_istituto WHERE idsso_tabella_tipologia_istituto='$fldidsso_tabella_tipologia_istituto'");

					$selected="";
					$disabled_priorita="disabled";

					if($fldflag_anticipo==2)
					{
						$checked_anticipo="";
						$disabled_anticipo="disabled";

						$field_anticipo='<input type="checkbox" '.$disabled_anticipo.' '.$checked_anticipo.' '.$accesso_modifica.' '.$disabled.' id="flag_anticipo'.$fldistituto.'" name="flag_anticipo'.$fldistituto.'" class="class_anticipo"  onclick="check_anticipo_posticipo('.$fldistituto.')" />';
					}
					else
					{
						$field_anticipo='';
					}

					if($fldflag_posticipo==2)
					{
						$checked_posticipo="";
						$disabled_posticipo="disabled";

						$field_posticipo='<input type="checkbox" '.$disabled_posticipo.' '.$checked_posticipo.' '.$accesso_modifica.' '.$disabled.' id="flag_posticipo'.$fldistituto.'" name="flag_posticipo'.$fldistituto.'" class="class_posticipo" onclick="check_anticipo_posticipo('.$fldistituto.')" />';
					}
					else
					{
						$field_posticipo='';
					}

					if($fldflag_tempo_prolungato==2)
					{
						$checked_prolungato="";
						$disabled_prolungato="disabled";
						
						$field_prolungato='<input type="checkbox" '.$disabled_prolungato.' '.$checked_prolungato.' '.$accesso_modifica.' '.$disabled.' id="flag_tempo_prolungato'.$fldistituto.'" name="flag_tempo_prolungato'.$fldistituto.'" class="class_prolungato" />';
					}
					else
					{
						$field_prolungato='';
					}
							
					$fldora_inizio = substr($fldora_inizio, 0, 5);
					$fldora_fine = substr($fldora_fine, 0, 5);

					$fldorario=$fldora_inizio." - ".$fldora_fine;

					$checkbox='<input type="checkbox" id="check_istituto'.$fldistituto.'"  name="check_istituti[]" onClick="check_istituto_all('.$fldistituto.')" value="'.$fldistituto.'" '.$selected.' '.$accesso_modifica.' '.$disabled.'/>';

					$response.='<tr id="'.$fldistituto.'">
						<td>'.$checkbox.'</td>';

					$response.='<td>
					<select class="form-control input-sm" name="priorita_'.$fldistituto.'" id="priorita_'.$fldistituto.'" '.$disabled_priorita.' on style="width: 100px;">';
					
					for($i=0; $i<=$number_plessi; $i++)
					{
						if(empty($i))
							$option_value="";
						else
							$option_value=$i;

						if($option_value==$fldpriorita)
							$selected_priorita="selected";
						else
							$selected_priorita="";

						$response.='<option value="'.$option_value.'" '.$selected_priorita.'>'.$option_value.'</option>';
					}

					$response.='</select>
					</td>';

					$response.='<td>'.$fldtipologia_istituto.'</td>
						<td>'.$flddenominazione.'</td>
						<td class="text-center" style="'.$style_anticipo.'">'.$field_anticipo.'</td>
						<td class="text-center" style="'.$style_posticipo.'">'.$field_posticipo.'</td>
						<td class="text-center" style="'.$style_tempo_prolungato.'">'.$field_prolungato.'</td>
						</tr>';

					$pcounter_istituti++;
					$res = $db->next_record();
				}		

				$response.='</tbody>
						</table>
		 	 		</div>
				</div>';

				echo $response;
			}
			break;

	case "salva_fonte":
		$pfonte=get_param("_fonte");
		$pfonte=db_string($pfonte);

		if(!empty($pfonte))
		{
			$insert="INSERT INTO sso_tbl_fonti_normative(descrizione) VALUES('$pfonte')";
			$db->query($insert);

			echo "1";
		}
		break;

	case "load_multi_fonte":
			$response.='<div class="col-sm-2">
				<select id="fonte_normative" name="fonte_normative" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_fonti_normative";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_fonti_normative=$db->f("idsso_tbl_fonti_normative");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_fonti_normative.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="fonte_normative_string" name="fonte_normative_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;



		case "salva_status":
		$pstatus=get_param("_status");
		$pstatus=db_string($pstatus);

		if(!empty($pstatus))
		{
			$insert="INSERT INTO sso_tbl_status_civile(descrizione) VALUES('$pstatus')";
			$db->query($insert);

			echo "1";
		}
		break;

	case "load_multi_status_civile":
			$response.='<div class="col-sm-6">
				<select id="status_civile" name="status_civile" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_status_civile";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_status_civile=$db->f("idsso_tbl_status_civile");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_status_civile.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="status_civile_string" name="status_civile_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;


	case "salva_causa":
		$pcausa=get_param("_causa");
		$pcausa=db_string($pcausa);

		if(!empty($pcausa))
		{
			$insert="INSERT INTO sso_tbl_cause_condizioni(descrizione) VALUES('$pcausa')";
			$db->query($insert);

			echo "1";
		}
		break;

	case "load_multi_cause":
			$response.='<div class="col-sm-6">
				<select id="cause_condizioni" name="cause_condizioni" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_cause_condizioni";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_fonti_normative=$db->f("idsso_tbl_cause_condizioni");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_cause_condizioni.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="cause_condizioni_string" name="cause_condizioni_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

	case "salva_certificazione":
		$pcertificazione=get_param("_certificazione");
		$pcertificazione=db_string($pcertificazione);

		if(!empty($pcertificazione))
		{
			$insert="INSERT INTO sso_tbl_certificazione(descrizione) VALUES('$pcertificazione')";
			$db->query($insert);

			echo "1";
		}
		break;

	case "load_multi_certificazione":
			$response.='<div class="col-sm-6">
				<select id="certificazione" name="certificazione" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_certificazione";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_certificazione=$db->f("idsso_tbl_certificazione");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_certificazione.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="certificazione_string" name="certificazione_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

		case "salva_benefici":
		$pbenefici=get_param("_benefici");
		$pbenefici=db_string($pbenefici);

		if(!empty($pbenefici))
		{
			$insert="INSERT INTO sso_tbl_benefici_agevolazioni(descrizione) VALUES('$pbenefici')";
			$db->query($insert);

			echo "1";
		}
		break;

		case "load_multi_benefici":
			$response.='<div class="col-sm-6">
				<select id="benefici_agevolazioni" name="benefici_agevolazioni" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_benefici_agevolazioni";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_benefici_agevolazioni=$db->f("idsso_tbl_benefici_agevolazioni");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_benefici_agevolazioni.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="benefici_agevolazioni_string" name="benefici_agevolazioni_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

		case "salva_beneficiari":
		$pbeneficiari=get_param("_beneficiari");
		$pbeneficiari=db_string($pbeneficiari);

		if(!empty($pbeneficiari))
		{
			$insert="INSERT INTO sso_tbl_beneficiari(descrizione) VALUES('$pbeneficiari')";
			$db->query($insert);

			echo "1";
		}
		break;

		case "load_multi_beneficiari":
			$response.='<div class="col-sm-6">
				<select id="beneficiari" name="beneficiari" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_beneficiari";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_beneficiari=$db->f("idsso_tbl_beneficiari");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_beneficiari.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record();
				}

				$response.='</select>
				<input id="beneficiari_string" name="beneficiari_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

		case "salva_domanda":
		$paltra_domanda=get_param("_altra_domanda");
		$paltra_domanda=db_string($paltra_domanda);

		if(!empty($paltra_domanda))
		{
			$insert="INSERT INTO sso_tbl_domanda(descrizione) VALUES('$paltra_domanda')";
			$db->query($insert);

			echo "1";
		}
		break;

		case "salva_ente":
		$paltro_ente=get_param("_altro_ente");
		$paltro_ente=db_string($paltro_ente);

		if(!empty($paltro_ente))
		{
			$insert="INSERT INTO sso_tbl_enti_preposti(descrizione) VALUES('$paltro_ente')";
			$db->query($insert);

			echo "1";
		}
		break;

		case "load_multi_domanda":
			$response.='<div class="col-sm-6">
				<select id="domanda" name="domanda" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_domanda";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_domanda=$db->f("idsso_tbl_domanda");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_domanda.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record(); 
				}

				$response.='</select>
				<input id="domanda_string" name="domanda_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

		case "load_multi_ente":
			$response.='<div class="col-sm-6">
				<select id="enti_preposti" name="enti_preposti" class="form-control input-sm" multiple="multiple">';
					
				$sSQL="SELECT * FROM sso_tbl_enti_preposti";
				$db->query($sSQL);
				$res=$db->next_record();
				while($res)
				{
					$fldidsso_tbl_enti_preposti=$db->f("idsso_tbl_enti_preposti");
					$flddescrizione=$db->f("descrizione");

					$response.='<option value="'.$fldidsso_tbl_enti_preposti.'" '.$selected.'>'.$flddescrizione.'</option>';
				
					$res=$db->next_record(); 
				}

				$response.='</select>
				<input id="enti_preposti_string" name="enti_preposti_string" type="hidden" class="form-control input-sm col-sm-1" value="">
			</div>';

			echo $response;
		break;

	case "sollecitomail":
		$fldidgen_operatore=verifica_utente($chiave);		
		$pidsso_domanda_intervento=get_param("_id");
		$pai=new Pai($pidsso_domanda_intervento);
		$pmovimento_tipo=get_param("_movimento");

		$fldidutente=$pai->idutente;
		//$fldmese=get_param("_mese");
		//$fldanno=get_param("_anno");
		$fldsaldo=get_param("_saldo");
		$fldtesto=get_param("_testo");

		//$fldsaldo=abs(get_param("_saldo"));
		$fldsaldo=ltrim($fldsaldo,"-");
		//$fldsaldo=number_format($fldsaldo,2,",",".");
		
		$beneficiario=new Beneficiario($fldidutente);
		$fldnominativo=stripslashes($beneficiario->get_nominativo());
		$fldmatricola=str_pad($fldidutente, 5, "0", STR_PAD_LEFT);
		$fldmail=trim($beneficiario->email);
		
		$fldtesto=nl2br($fldtesto);
		$fldtesto=stripslashes($fldtesto);

		$fldtesto=str_replace("[saldo]",$fldsaldo,$fldtesto);
		$fldtesto=str_replace("[matricola]",$fldmatricola,$fldtesto);
		$fldtesto=str_replace("[nominativo]",$fldnominativo,$fldtesto);
		
		$fldoggetto="COMUNICAZIONE";
		$fldmail="rosario.dolce@iccs.it";
		if(!empty($fldmail))
		{
			$aEMAIL=array();
			$aEMAIL[0]=$fldmail;
			$aEMAIL[1]=$fldoggetto;
			$aEMAIL[2]=$fldtesto;
			$aEMAIL[3]="";
			//$fldresult=sendMAIL($aEMAIL);

			$fldtesto=db_string($fldtesto);

			$sSQL="INSERT INTO `sso_anagrafica_mail` (
			`area`, `idutente`, `idsso_domanda_intervento`, 
			`mail`, `data`, `orario`, 
			`idgen_operatore`, `descrizione`) 
			VALUES
			('SICARE_SOLLECITI','$fldidutente','$pidsso_domanda_intervento',
			'$fldmail','".date("Y-m-d")."','".date("H:i:s")."',
			'$fldidgen_operatore','$fldtesto')";						
			$db->query($sSQL);

			//Inserisco il movimento
			interventoMOVIMENTO($pidsso_domanda_intervento,$pmovimento_tipo,date("Y-m-d"));

		}

		echo ($fldresult=="Messaggio inviato correttamente.") ? 'true' : 'false';	

		break;	
	case "savepianificata":
		list($pidsso_prestazione_pianificata,$ptipocampo)=explode("|",get_param("_k"));
		$pvalue=get_param("_value");
		$pidsso_fattura=get_param("_f");
		switch($ptipocampo)
		{
			case 'pianificatanp':
				$pfield="pianificata_protocollonumero";
				break;
			case 'pianificatadp':
				$pfield="pianificata_protocollodata";
				$pvalue=invertidata($pvalue,"-","/",1);
				break;
			case 'pianificatann':
				$pfield="pianificata_notificanumero";
				break;
			case 'pianificatadn':
				$pfield="pianificata_notificadata";
				$pvalue=invertidata($pvalue,"-","/",1);
				break;
		}

		$sSQL="update sso_prestazione_pianificata set ".$pfield."='$pvalue' where idsso_prestazione_pianificata='$pidsso_prestazione_pianificata'";
		$db->query($sSQL);
		break;		
	case "graduatoriarinuncia":
		$pidsso_progetto=get_param("_p");
		$pidsso_domanda=get_param("_k");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		if ($pidsso_progetto>0 and $pidsso_domanda>0)
		{
			$sSQL="update sso_domanda set sso_domanda.idsso_tabella_stato_domanda=5,data_cessazione='$oggi' where sso_domanda.idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);		

			$sSQL="UPDATE sso_domanda SET idsso_tabella_stato_domanda_bandi='7' WHERE idsso_domanda='$pidsso_domanda'";
			$db->query($sSQL);				
		}

		break;

	case "check_fasciaoraria_conservazione":
		$pidsso_ente=get_param("_idente");

		$fldflag_digip=get_db_value("SELECT flag_digip FROM ".DBNAME_A.".enti WHERE idente='$pidsso_ente'");
		$fldora_inizio_digip=get_db_value("SELECT ora_inizio_digip FROM ".DBNAME_A.".enti WHERE idente='$pidsso_ente'");
		$fldora_fine_digip=get_db_value("SELECT ora_fine_digip FROM ".DBNAME_A.".enti WHERE idente='$pidsso_ente'");

		$adesso = date("H:i");
		$inizio_versamento = substr($fldora_inizio_digip,0,5);
		$fine_versamento = substr($fldora_fine_digip,0,5);

		$adesso_check = DateTime::createFromFormat('H:i', $adesso);
		$inizio_check = DateTime::createFromFormat('H:i', $inizio_versamento);
		$fine_check = DateTime::createFromFormat('H:i', $fine_versamento);
		if ($adesso_check > $inizio_check && $adesso_check < $fine_check)
			$state="true";
		else
			$state="false";

		echo $state."|".$inizio_versamento."|".$fine_versamento;

		break;					
	case "loadALLOGGIO":
		$fldidsso_tbl_prestazione=659;
		$sSQL = "select distinct sso_anagrafica_utente.idutente, sso_anagrafica_utente.cognome, sso_anagrafica_utente.indirizzo, sso_anagrafica_utente.civico   
							from sso_anagrafica_utente 
							inner join sso_ente_servizio on sso_anagrafica_utente.idutente=sso_ente_servizio.idutente 
							WHERE idsso_tabella_tipologia_ente='23'";
		$db->query($sSQL);
		$res=$db->next_record();
		$response=array();

		while($res)
		{
			$fldidutente=$db->f("idutente");
			$fldcognome=$db->f("cognome");
			$fldindirizzo=$db->f("indirizzo");
			$fldcivico=$db->f("civico");
			$fldpalazzina=get_db_value("select palazzina from sso_anagrafica_utente where idutente='$fldidutente' ");
			$fldpiano=get_db_value("select piano from sso_anagrafica_utente where idutente='$fldidutente' ");
			$fldinterno=get_db_value("select interno from sso_anagrafica_utente where idutente='$fldidutente' ");
			$fldscala=get_db_value("select scala from sso_anagrafica_utente where idutente='$fldidutente' ");
			$altridati="";
			if ($fldpiano)
				$altridati.=" piano ".$fldpiano;
			if ($fldinterno)
				$altridati.=" interno ".$fldinterno;
			if ($fldscala)
				$altridati.=" scala ".$fldscala;
			$record=array();
			$record['value']=$fldcognome." - ".$fldindirizzo." ".$fldcivico.$altridati;
			$record['data']=$fldidutente.'|'.$fldcognome." ".$fldindirizzo." ".$fldcivico.$altridati;
			array_push($response, $record);

			$res=$db->next_record();
		}
	
		array_walk_recursive($response, 'encode_items'); // http://stackoverflow.com/questions/3912930/applying-a-function-all-values-in-an-array
		echo json_encode($response);
		break;	
	case "casamovimento":
		$pidsso_domanda_intervento=get_param("_intervento");
		$pdata_movimento=get_param("_data");
		$ptipo_movimento=get_param("_tipo");

		break;		
	case "domandamovimento":
		$pidsso_domanda=get_param("_domanda");
		$pdata_movimento=invertidata(get_param("_data"),"-","/",1);
		$ptipo_movimento=get_param("_tipo");
		$oggi=date("Y-m-d");
		$user=verifica_utente($chiave);
		$sSQL="insert into sso_domanda_fase (idsso_domanda,fase_datainserimento,fase_operatore,fase_data,idsso_tbl_domanda_fase) values('$pidsso_domanda','$oggi','$user','$pdata_movimento','$ptipo_movimento')";
		$db->query($sSQL);
		break;
	case "documentomovimento":
		$pidsso_anagrafica_allega=get_param("_allega");
		$pdata_movimento=invertidata(get_param("_data"),"-","/",1);
		$ptipo_movimento=get_param("_tipo");
		$pnote=db_string(get_param("_note"));
		$oggi=date("Y-m-d");
		$user=verifica_utente($chiave);
		$sSQL="insert into sso_domanda_fase (idsso_anagrafica_allega,fase_datainserimento,fase_operatore,fase_data,idsso_tbl_domanda_fase,fase_note) values('$pidsso_anagrafica_allega','$oggi','$user','$pdata_movimento','$ptipo_movimento','$pnote')";
		$db->query($sSQL);
		break;		
	case "savedomandafase":
		list($pidsso_domanda_fase,$pfield)=explode("|",get_param("_k"));		
		$pvalue=get_param("_value");
		if (strpos($pfield,"data")>0)
			$pvalue=invertidata($pvalue,"-","/",1);
		$sSQL="update sso_domanda_fase set $pfield='$pvalue' where idsso_domanda_fase='$pidsso_domanda_fase'";
		$db->query($sSQL);
		break;
	case "deletedomandafase":
		$pidsso_domanda_fase=get_param("_fase");
		$sSQL="delete from sso_domanda_fase where idsso_domanda_fase='$pidsso_domanda_fase'";
		$db->query($sSQL);
		break;
	case "domandastoricizza":
		$pidsso_domanda=get_param("_domanda");
		if ($pidsso_domanda)
		{
			storicizzaDOMANDA($pidsso_domanda);
		}	

		break;	

	case "load_data_beneficiario_bandi":

		$fldidutente=get_param("_id");
		
		if(!empty($fldidutente))
		{
			$sSql="select * from sso_anagrafica_utente where idutente='$fldidutente'";

			$db->query($sSql);
			$next_record=$db->next_record();
				
			$response=array();
			while($next_record)
			{
				$fldidutente=$db->f("idutente");
				$fldcognome = $db->f("cognome");
				$fldnome = $db->f("nome");
				$flddatanascita = $db->f("data_nascita");

				if(!empty_data($flddatanascita))
					$flddatanascita=invertidata($flddatanascita,"/","-",2);
				else
					$flddatanascita='';

				$fldcodicefiscale = $db->f("codicefiscale");
				$fldemail = $db->f("email");
				$fldtelefono = $db->f("telefono");
				$fldcellulare = $db->f("cellulare");
				$fldindirizzo = $db->f("indirizzo");
				$fldcivico = $db->f("civico");
				$fldsesso = $db->f("sesso");

				$fldidgen_cittadinanza1 = $db->f("idgen_cittadinanza1");

				$fldpalazzina = $db->f("palazzina");
				$fldscala = $db->f("scala");
				$fldinterno = $db->f("interno");
				$fldpiano = $db->f("piano");
				$fldflag_ascensore = $db->f("flag_ascensore");
				$fldcitofonare = $db->f("citofonare");
				$fldasl_residenza = $db->f("asl_residenza");

				$fldidamb_comune_residenza = $db->f("idamb_comune_residenza");
				if(!empty($fldidamb_comune_residenza))
					$fldcitta=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidamb_comune_residenza'");
				else
					$fldcitta = $db->f("citta");
				
				$fldprov = $db->f("prov");
				$fldcap = $db->f("cap");
				$fldprov_nascita = $db->f("prov_nascita");

				$fldidgen_comune_nascita = $db->f("idgen_comune_nascita");
				$fldidamb_nazione=get_db_value("select idamb_nazione from sso_anagrafica where idutente='$fldidutente'");
				if($fldidamb_nazione=="122")
					$fldcomune_nascita=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
				else
					$fldcomune_nascita = $db->f("comune_nascita");

				$fldidsso_tabella_stato_civile=get_db_value("select idsso_tabella_stato_civile from sso_anagrafica where idutente='$fldidutente'");

				$fldflag_domicilio_differente=get_db_value("select flag_domicilio_differente from sso_anagrafica where idutente='$fldidutente'");

				$fldcodicefiscale = removeslashes($fldcodicefiscale);
				if(empty($fldcodicefiscale))
					$fldcodicefiscale=' ';
				
				$fldcognome = removeslashes($fldcognome);
				$fldnome = removeslashes($fldnome);
				$fldnominativo=$fldcognome." ".$fldnome;

				$record=$fldidutente.'|'.
				$fldnominativo.'|'.
				$fldcodicefiscale.'|'.
				$fldidamb_nazione.'|'.
				$fldidgen_cittadinanza1.'|'.
				$fldsesso.'|'.
				$flddatanascita."|".
				$fldidgen_comune_nascita."|".
				$fldcomune_nascita."|".
				$fldprov_nascita.'|'.
				$fldindirizzo.'|'.
				$fldcivico.'|'.
				$fldcitta.'|'.
				$fldidamb_comune_residenza.'|'.
				$fldprov.'|'.
				$fldcellulare.'|'.
				$fldemail;

				$next_record = $db->next_record();  
			}

			echo $record;
		}
		else
			echo "error";
	break;

	case "load_cognome_nome_utente":
		$fldidutente=get_param("_idutente");
		$fldcognome=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
		$fldnome=get_db_value("SELECT nome FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
		
		echo $fldcognome."|".$fldnome;
		break;

	case "dati_comune_ente":
		$pidsso_ente=get_param("_idente");
		$ente=new Ente($pidsso_ente);

		if(empty($ente->idcomune))
			echo "error";
		else
			echo $ente->idcomune."|".$ente->comune."|".$ente->provincia;

		break;

	case "documenti_progetto":
		$pidsso_ente=get_param("_idente");
		
		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			if($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{		
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");		
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");		
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");		
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");	
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");	
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");					
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																	
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");						
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");				
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");		
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");
			}
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");				
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");				
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");								
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");	
		}					
		
		$ndocumenti=get_db_value("SELECT COUNT(*) FROM sso_progetto_documento WHERE idsso_progetto='$fldidsso_progetto'");

		if(empty($ndocumenti))
			echo "0";
		else
			echo $ndocumenti;

		break;

	case "dati_informativi_progetto":
		$pidsso_ente=get_param("_idente");
		
		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			if($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{		
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");				
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");			
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");	
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");	
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																			
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");						
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");							
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");	
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");	
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");				
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");		
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");	
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");

			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");		
		}	
		
		$ninformativi=get_db_value("SELECT COUNT(*) FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");

		if(empty($ninformativi))
			echo "0";
		else
			echo $ninformativi;

		break;
	
	case "load_informativi":
		$pidsso_ente=get_param("_idente");

		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			if($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");		
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");			
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");				
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");	
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");										
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");						
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");						
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");			
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");								
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");				
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");		
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");	
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");	
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");	
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");
		}	

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		$count_risposte_params=0;
		while($res)
		{
			$fldidsso_progetto_graduatoria_dipendenze=$db->f("idsso_progetto_graduatoria_dipendenze");
			
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			// ***************************************************
			// Qesto array non viene visualizzato da nessuna parte
			$aPARAMS=array();
			foreach($aRISPOSTE as $idrisposta)
			{
				$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
				$aPARAMS[]=$idparametro_risposta;
			}
			$aPARAMS=array_unique($aPARAMS);
			//print_r($aPARAMS);
			//echo "<br>";
			// ***************************************************

			$aPARAMS_TEMP=array();
			
			foreach($aPARAMETRI as $idparametro)
			{
				$aPARAMETRI_CONTROLLATI[]=$idparametro;

				$sql="SELECT idsso_domanda_parametro_graduatoria 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda='$pidsso_domanda' 
					AND idsso_progetto_graduatoria='$idparametro'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
				else
				{
					$counter_risp=0;
					$str_parametri_risposta='';
					foreach($aRISPOSTE as $idrisposta)
					{
						$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						$fldidsso_progetto_graduatoria_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_progetto_graduatoria_risposte='$idrisposta' AND idsso_domanda='$pidsso_domanda'");
						if(!empty($fldidsso_progetto_graduatoria_risposte))
						{
							$counter_risp++;
						}

						$str_parametri_risposta.=$idparametro_risposta."|";
					}

					$str_parametri_risposta=rtrim($str_parametri_risposta,"|");

					$count_attese=count($aRISPOSTE);

					$aVISUALIZZA[$idparametro][$count_risposte_params]["DATE"]=$counter_risp;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["ATTESE"]=$count_attese;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["PARAMETRI"]=$str_parametri_risposta;

					$count_risposte_params++;
				}
			}

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria]=$aPARAMETRI;
			}

			$res=$db->next_record();
		}

		if(!empty($aVISUALIZZA))
		{
			foreach($aVISUALIZZA as $idparametro=>$dettaglio)
			{
				$visualizza=true;
				foreach($dettaglio as $dett)
				{
					if($dett["DATE"]!=$dett["ATTESE"])
					{
						//se le attese sono tutte dello stesso parametro allora ne basta 1
						$aPARAMS=explode("|",$dett["PARAMETRI"]);
						$aPARAMS=array_unique($aPARAMS);
						if(count($aPARAMS)!=1 || $dett["DATE"]!=1)
							$visualizza=false;
					}
				}

				if($visualizza && !in_array($idparametro,$aPARAMETRI_VISUALIZZA))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
			}
		}

		$aPARAMETRI_BANDO=db_fill_array("SELECT idsso_progetto_graduatoria,idsso_progetto_graduatoria FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_BANDO))
		{
			foreach($aPARAMETRI_BANDO as $idsso_progetto_graduatoria)
			{
				if(!@in_array($idsso_progetto_graduatoria,$aPARAMETRI_CONTROLLATI))
					$aPARAMETRI_VISUALIZZA[]=$idsso_progetto_graduatoria;
			}
		}

		//print_r_formatted($aPARAMETRI_VISUALIZZA);

		$response="";

		$sSQL="SELECT sso_progetto_graduatoria.*
		FROM ".DBNAME_SS.".sso_progetto_graduatoria
		WHERE sso_progetto_graduatoria.idsso_progetto='$fldidsso_progetto' AND sso_progetto_graduatoria.tipologia_parametro!=13
		ORDER BY sso_progetto_graduatoria.ordine ASC";
		$db->query($sSQL);
		$next_record=$db->next_record();
		while($next_record)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");

			$aRISPOSTE=array();
			$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
			if(!empty($aPARAMETRI_GRUPPI))
			{
				foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
				{
					$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
					if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
					{
						$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
						break;
					}
					else
						$fldidsso_progetto_graduatoria_gruppi=null;
				}
			}
			else
				$fldidsso_progetto_graduatoria_gruppi=null;

			$dipendenze=false;
			if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				//echo "fa parte di un gruppo: ".$fldidsso_progetto_graduatoria_gruppi."<br>";
				if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
				{
					//echo "<br>DIPENDENZE:<br><br>";
					foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($parametri);
								$first_key = key($parametri);
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}
			else
			{
				if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
				{
					$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
					foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);

								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}

			$fldidsso_tabella_parametro_graduatoria=$db->f("idsso_tabella_parametro_graduatoria");
			$flddescrizione_parametro=$db->f("descrizione_parametro");
			$fldinfo_tooltip=$db->f("info_tooltip");
			if(!empty($fldinfo_tooltip))
			{
				$fldinfo_tooltip=str_replace(array("\r\n", "\r", "\n"),"<br>",$fldinfo_tooltip);
				$info_tooltip=genera_tooltip($fldinfo_tooltip, '<i class="fa fa-question-circle fa-lg span-padding" aria-hidden="true"></i>', null, "auto");
			}
			else
				$info_tooltip="";

			$fldtipologia_parametro=$db->f("tipologia_parametro");
			$fldflag_obbligatorio=$db->f("flag_obbligatorio");
			if($fldflag_obbligatorio==1)
			{
				$required="required";
				$obbligatorio="*";
			}
			else
			{
				$required="";
				$obbligatorio="";
			}

			$fldidsso_parametro_gruppo_visualizzazione=$db->f("idsso_parametro_gruppo_visualizzazione");
			$fldidsso_parametro_gruppo_valore_visualizzazione=$db->f("idsso_parametro_gruppo_valore_visualizzazione");

			if(@in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_VISUALIZZA))
				$display_visibilita="";
			else
			{
				$display_visibilita="display:none;";
				//$required="";
				//$obbligatorio="";
			}

			$response.='<div id="div'.$fldidsso_progetto_graduatoria.'" style="'.$display_visibilita.'">';

			if($dipendenze)
			{
				//print_r_formatted($aRISPOSTE);

				$sPARAMETRI.=$fldidsso_progetto_graduatoria."|";
				$function_onchange="onChange=\"changeDIPENDENZE('".$fldidsso_progetto_graduatoria."','1')\"";
			}
			else
				$function_onchange='onChange="valueChange(this.id)"';

			$fldlabel_precedente=$db->f("label_precedente");
			$fldlabel_color=$db->f("label_color");
			if(empty($fldlabel_color))
				$fldlabel_color="#000000";

			if($fldlabel_precedente=="<hr>")
				$response.='<hr class="hr_step3">';
			elseif(!empty($fldlabel_precedente))
				$response.='<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><h4 style="color:'.$fldlabel_color.';">'.$fldlabel_precedente.'<h4></div></div>';

			if($fldtipologia_parametro==1)
			{
				$fldflag_controlli=$db->f("flag_controlli");

				switch($fldflag_controlli)
				{
					case 1:
						$maxlength=10;
					break;

					case 2:
						$maxlength=16;
					break;

					case 3:
						$maxlength=8;
					break;

					case 4:
						$maxlength=5;
					break;

					default:
						$maxlength=$db->f("maxlength");
						if(empty($maxlength))
							$maxlength=null;
					break;
				}
				if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
				{
					/*
					$response.='<div class="row">
						<label class="col-sm-6 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-2">';
					$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control" maxlength="'.$maxlength.'" value="" '.$disabled.'>';
					$response.='</div><div class="col-sm-4"></div>
					</div>';		
					*/
					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">';
					$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control" maxlength="'.$maxlength.'" value="" '.$disabled.'>';
					$response.='</div>
					</div>';								
				}
				else
				{	
					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">';
					$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control" maxlength="'.$maxlength.'" value="" '.$disabled.'>';
					$response.='</div>
					</div>';
				}	
			}
			elseif($fldtipologia_parametro==2)
			{
				$response.='<div class="form-group">
							<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">
							<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-sm btn-success class_multi_dip" multiple="multiple" '.$accesso_modifica.' '.$disabled.' '.$required.'> ';

							$sSQL="SELECT *
								FROM sso_progetto_graduatoria_risposte 
								WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' ORDER BY descrizione";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");
								$fldlabel_altradomanda=$db2->f("label_altradomanda");
								$fldflag_altradomanda=$db2->f("flag_altradomanda");
								$fldidsso_domanda_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto_graduatoria_risposte='$fldidsso_progetto_graduatoria_risposta'");
								
								if(empty($fldidsso_domanda_risposte))
									$selected='';
								else
									$selected='selected';

								$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" '.$selected.'>'.$flddescrizione_risposta.'</option>';
							
								$result=$db2->next_record();
							}
				$response.='</select>
					</div>
				</div>';

				$response.='<script type="text/javascript">
					$(document).ready(function() {

						$(\'#_valore'.$fldidsso_progetto_graduatoria.'\').multiselect({
							allSelectedText: "Tutte le opzioni",
							nonSelectedText: "Nessuna opzione selezionata",
							nSelectedText: " opzioni selezionate",

							buttonWidth: "100%",
							onChange: function(element, checked) {
								aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
							}
						});

						aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
					});
				</script>';

				$response.='<input type="hidden" id="selected_'.$fldidsso_progetto_graduatoria.'" name="selected_'.$fldidsso_progetto_graduatoria.'" value=""/>';
			}
			elseif($fldtipologia_parametro==3)
			{
				$fldlabel_altradomanda='';
				$fldflag_altradomanda='';

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">
						<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm" '.$disabled.' '.$function_onchange.'>
							<option value=""></option>';

							$sSQL="SELECT * 
							FROM sso_progetto_graduatoria_risposte 
							WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
							ORDER BY punteggio ASC";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");

								$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

								$result=$db2->next_record();
							}
				$response.='</select>
					</div>

					<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
					$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==5)
			{
				if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
				{
					$response.='<div class="row">';
					$response.='<table data-toggle="table" class="table table-condensed" ><tbody><tr style="vertical-align:middle" class="text-left"><th><input type="checkbox" class="check_info" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs" onChange="check_valore(\'_valore'.$fldidsso_progetto_graduatoria.'\')" '.$required.'><input type="hidden" id="_check_valore'.$fldidsso_progetto_graduatoria.'" name="_check_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs">';

					$response.='&nbsp;&nbsp;&nbsp;&nbsp;'.$flddescrizione_parametro.$obbligatorio.'</th></tr></tbody></table>';					
					$response.='</div>';
				}
				else
				{
					$response.='<div class="row">';
					$response.='<table data-toggle="table" class="table table-condensed" ><tbody><tr style="vertical-align:middle" class="text-left"><th><input type="checkbox" class="check_info" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs" onChange="check_valore(\'_valore'.$fldidsso_progetto_graduatoria.'\')" '.$required.'><input type="hidden" id="_check_valore'.$fldidsso_progetto_graduatoria.'" name="_check_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs"></th>';


					$response.='<th>'.$flddescrizione_parametro.$obbligatorio.'</th></tr></tbody></table>';					
					$response.='</div>';					
					/*
					$response.='<div class="form-group">
							<div class="col-sm-1">';
					$response.='<input type="checkbox" class="check_info" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs" onChange="check_valore(\'_valore'.$fldidsso_progetto_graduatoria.'\')">';
					$response.='<input type="hidden" id="_check_valore'.$fldidsso_progetto_graduatoria.'" name="_check_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs">';
					$response.='</div>';
					$response.='<label class="col-sm-10 control-label pull-left" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.'</label>';
					$response.='</div>';
					*/				
				}
			}
			elseif($fldtipologia_parametro==6)
			{
				$descrizione_temp=str_replace(" ","",$flddescrizione_parametro);
				if(!empty($descrizione_temp))
				{
					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.'</label>
						</div>';
				}

				$response.='<div class="form-group">';

				$fldnumero_righe=$db->f("numero_righe");

				$aColonne=db_fill_array("SELECT idsso_progetto_graduatoria_tabelle,descrizione FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto='$fldidsso_progetto' ORDER BY idsso_progetto_graduatoria_tabelle ASC");
				//print_r($aColonne);
				
				if(is_array($aColonne))
				{
					$response.='<div style="margin-left:10%; margin-right:10%;">';
					$response.='<table data-toggle="table" class="table table-hover table-condensed" >
					<thead>
						<tr class="default">';

					foreach($aColonne as $idcolonna=>$intestazione)
					{
						$response.='<th style="width: 10%;" class="intestazioneTabella text-info">'.$intestazione.'</th>';
					}	

					$response.='</tr>
					</thead>';

					
					$counter=1;
					while($fldnumero_righe>0)
					{
						$response.='<tr>';
						foreach($aColonne as $idcolonna=>$intestazione)
						{
							$fldflag_colonna_tipologia=get_db_value("SELECT flag_tipologia_colonna FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

							switch($fldflag_colonna_tipologia)
							{
								case 1:
									$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
									break;

								case 2:
									$response.='<td><select class="form-control input-sm" style="" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" '.$disabled.'>';
									
									$response.='\n <option value="" selected></option>';

									$fldcolonna_scelta=get_db_value("SELECT colonna_scelta FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

									$colonna_scelta=explode("|",$fldcolonna_scelta);
									foreach ($colonna_scelta as $key => $value) 
									{
										list($altrokey,$altrovalue)=explode(";",$value);
										
										$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
									}

									$response.='</select></td>';
									break;

								default:
									$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
									break;
							}
							
						}

						$response.='<tr>';

						$counter++;

						$fldnumero_righe--;
					}
				
					$response.='</table>';

					//script per controlli su tabella
					$fldnumero_righe=$db->f("numero_righe");
					$counter=1;

					/*
					while($fldnumero_righe>0)
					{
						foreach($aColonne as $idcolonna=>$intestazione)
						{
							$fldflag_controlli=get_db_value("SELECT flag_controlli FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

							switch($fldflag_controlli)
							{
								case 1:		//data
									$response.='<script>

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").datepicker({
											language: "it",
											todayBtn: "linked",
											todayHighlight: true,
											autoclose: true,
											orientation: "auto"
										});

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
											if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
											{
												e.preventDefault();
											}
										});

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").blur(function() {
											
											var data=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

											if(!isEmpty(data))
											{
												check_data("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'")
											}
										});

									</script>';

									break;

								case 2:		//codice fiscale
									$response.='<script>

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

											var cf=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

											if(!isEmpty(cf))
											{
												check_cf("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
											}
										});

										</script>';

									break;

								case 3:
									$response.='<script>

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

											var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

											if(!isEmpty(orario))
											{
												check_hour1("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
											}
										});

										</script>';
									break;

								case 4:
									$response.='<script>

										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

											var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

											if(!isEmpty(orario))
											{
												check_hour2("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
											}
										});

										</script>';
									break;

								case 5:
									$response.='<script>
										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
												if (e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
												{
													e.preventDefault();
												}
											});

									</script>';

									break;

								case 6:
									$response.='<script>
										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
											if(e.which == 44)
											{
												var stringa_conenuta=$(this).val()
												if(stringa_conenuta.indexOf(',') > -1)
													e.preventDefault();
											}

											if (e.which != 44 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
												e.preventDefault();
										});
									</script>';

									break;

								case 7:
									$response.='<script>
										$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

											var email=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

											if(!isEmpty(email))
											{
												check_email("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
											}
										});
									</script>';

								break;
							}
						}

						$counter++;
						$fldnumero_righe--;
					}
					*/
				}
				
				
				$response.='</div></div>';
			}
			elseif($fldtipologia_parametro==7)
			{
				$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-6">';
				$response.='<textarea type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' rows="6" style="width:100%" class="form-control input-sm" '.$disabled.'></textarea>';
				$response.='</div>
				</div>';
			}

			/*
			switch($fldtipologia_parametro)
			{
				case 1: 	// Campo testuale
					$fldflag_controlli=$db->f("flag_controlli");

					switch($fldflag_controlli)
					{
						case 1:
							$maxlength=10;
						break;

						case 2:
							$maxlength=16;
						break;

						case 3:
							$maxlength=8;
						break;

						case 4:
							$maxlength=5;
						break;

						default:
							$maxlength=$db->f("maxlength");
							if(empty($maxlength))
								$maxlength=null;
						break;
					}

					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">';
					$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control" maxlength="'.$maxlength.'" value="" '.$disabled.'>';
					$response.='</div>
					</div>';

					switch($fldflag_controlli)
					{
						case 1:		//data
							$response.='<script>

								$("#_valore'.$fldidsso_progetto_graduatoria.'").datepicker({
									language: "it",
									todayBtn: "linked",
									todayHighlight: true,
									autoclose: true,
									orientation: "auto"
								});

								$("#_valore'.$fldidsso_progetto_graduatoria.'").keypress(function(e) {
									if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
									{
										e.preventDefault();
									}
								});

								$("#_valore'.$fldidsso_progetto_graduatoria.'").blur(function() {
									
									var data=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

									if(!isEmpty(data))
									{
										check_data("_valore'.$fldidsso_progetto_graduatoria.'")
									}
								});

							</script>';

							break;

						case 2:		//codice fiscale
							$response.='<script>

								$("#_valore'.$fldidsso_progetto_graduatoria.'").on("blur",function(){

									var cf=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

									if(!isEmpty(cf))
									{
										check_cf("_valore'.$fldidsso_progetto_graduatoria.'");
									}
								});

								</script>';

							break;

						case 3:
							$response.='<script>

								$("#_valore'.$fldidsso_progetto_graduatoria.'").on("blur",function(){

									var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

									if(!isEmpty(orario))
									{
										check_hour1("_valore'.$fldidsso_progetto_graduatoria.'");
									}
								});

								</script>';
							break;

						case 4:
							$response.='<script>

								$("#_valore'.$fldidsso_progetto_graduatoria.'").on("blur",function(){

									var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

									if(!isEmpty(orario))
									{
										check_hour2("_valore'.$fldidsso_progetto_graduatoria.'");
									}
								});

								</script>';
							break;

						case 5:
							$response.='<script>
								$("#_valore'.$fldidsso_progetto_graduatoria.'").keypress(function(e) {
										if (e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
										{
											e.preventDefault();
										}
									});

							</script>';

							break;

						case 6:
							$response.='<script>
								$("#_valore'.$fldidsso_progetto_graduatoria.'").keypress(function(e) {
									if(e.which == 44)
									{
										var stringa_conenuta=$(this).val()
										if(stringa_conenuta.indexOf(',') > -1)
											e.preventDefault();
									}

									if (e.which != 44 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
										e.preventDefault();
								});
							</script>';

						break;	

						case 7:
							$response.='<script>
								$("#_valore'.$fldidsso_progetto_graduatoria.'").on("blur",function(){

									var email=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

									if(!isEmpty(email))
									{
										check_email("_valore'.$fldidsso_progetto_graduatoria.'");
									}
								});
							</script>';

						break;
					}

					break;

				case 2:		// Selezione multipla
					$response.='<div class="form-group">
							<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
							<div class="col-sm-3">
									<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm btn-success class_multi_dip" multiple="multiple" '.$accesso_modifica.' '.$disabled.'> ';

										$sSQL="SELECT *
											FROM sso_progetto_graduatoria_risposte 
											WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' ORDER BY descrizione";
										$db2->query($sSQL);
										$result=$db2->next_record();
										while($result)
										{
											$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
											$flddescrizione_risposta=$db2->f("descrizione");
											$fldlabel_altradomanda=$db2->f("label_altradomanda");
											$fldflag_altradomanda=$db2->f("flag_altradomanda");
											$fldidsso_domanda_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto_graduatoria_risposte='$fldidsso_progetto_graduatoria_risposta'");
											
											if(empty($fldidsso_domanda_risposte))
												$selected='';
											else
												$selected='selected';

											$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" '.$selected.'>'.$flddescrizione_risposta.'</option>';
										
											$result=$db2->next_record();
										}
						$response.='</select>
							</div>
						</div>';

					$response.='<script type="text/javascript">
								$(document).ready(function() {

									$(\'#_valore'.$fldidsso_progetto_graduatoria.'\').multiselect({
										allSelectedText: "Tutte le opzioni",
										nonSelectedText: "Nessuna opzione selezionata",
										nSelectedText: " opzioni selezionate",

										buttonWidth: "100%",
										onChange: function(element, checked) {
											aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
										}
									});

									aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
								});
							</script>';

					$response.='<input type="hidden" id="selected_'.$fldidsso_progetto_graduatoria.'" name="selected_'.$fldidsso_progetto_graduatoria.'" value=""/>';

					break;
				
				case 3:		// Lista di scelte

					$fldlabel_altradomanda='';
					$fldflag_altradomanda='';

					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">
							<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm tipologia3" '.$disabled.'>
								<option value=""></option>';

								$sSQL="SELECT * 
								FROM sso_progetto_graduatoria_risposte 
								WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
								ORDER BY punteggio ASC";
								$db2->query($sSQL);
								$result=$db2->next_record();
								while($result)
								{
									$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
									$flddescrizione_risposta=$db2->f("descrizione");

									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

									$result=$db2->next_record();
								}
					$response.='</select>
						</div>

						<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
						$response.='</div>
					</div>';
					break;	
				
				case 5:
					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-1">';
					$response.='<input type="checkbox" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs">';
					$response.='</div>
					</div>';
					break;

				case 6:
					$descrizione_temp=str_replace(" ","",$flddescrizione_parametro);
					if(!empty($descrizione_temp))
					{
						$response.='<div class="form-group">
							<label class="col-sm-11 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.'</label>
							</div>';
					}

					$response.='<div class="form-group">';

					$fldnumero_righe=$db->f("numero_righe");

					$aColonne=db_fill_array("SELECT idsso_progetto_graduatoria_tabelle,descrizione FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto='$fldidsso_progetto' ORDER BY idsso_progetto_graduatoria_tabelle ASC");
					//print_r($aColonne);
					if(is_array($aColonne))
					{
						$response.='<div style="margin-left:10%; margin-right:10%;">';
						$response.='<table data-toggle="table" class="table table-hover table-condensed" >
						<thead>
							<tr class="default">';

						foreach($aColonne as $idcolonna=>$intestazione)
						{
							$response.='<th style="width: 10%;" class="intestazioneTabella text-info">'.$intestazione.'</th>';
						}	

						$response.='</tr>
						</thead>';

						$counter=1;
						while($fldnumero_righe>0)
						{
							$response.='<tr>';
							foreach($aColonne as $idcolonna=>$intestazione)
							{
								$fldflag_colonna_tipologia=get_db_value("SELECT flag_tipologia_colonna FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

								switch($fldflag_colonna_tipologia)
								{
									case 1:
										$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
										break;

									case 2:
										$response.='<td><select class="form-control input-sm" style="" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" '.$disabled.'>';
										
										$response.='\n <option value="" selected></option>';

										$fldcolonna_scelta=get_db_value("SELECT colonna_scelta FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

										$colonna_scelta=explode("|",$fldcolonna_scelta);
										foreach ($colonna_scelta as $key => $value) 
										{
											list($altrokey,$altrovalue)=explode(";",$value);
											
											$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
										}

										$response.='</select></td>';
										break;

									default:
										$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
										break;
								}
								
							}

							$response.='<tr>';

							$counter++;

							$fldnumero_righe--;
						}

						$response.='</table>';

						//script per controlli su tabella
						$fldnumero_righe=$db->f("numero_righe");
						$counter=1;

						while($fldnumero_righe>0)
						{
							foreach($aColonne as $idcolonna=>$intestazione)
							{
								$fldflag_controlli=get_db_value("SELECT flag_controlli FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

								switch($fldflag_controlli)
								{
									case 1:		//data
										$response.='<script>

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").datepicker({
												language: "it",
												todayBtn: "linked",
												todayHighlight: true,
												autoclose: true,
												orientation: "auto"
											});

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
												if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
												{
													e.preventDefault();
												}
											});

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").blur(function() {
												
												var data=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

												if(!isEmpty(data))
												{
													check_data("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'")
												}
											});

										</script>';

										break;

									case 2:		//codice fiscale
										$response.='<script>

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

												var cf=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

												if(!isEmpty(cf))
												{
													check_cf("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
												}
											});

											</script>';

										break;

									case 3:
										$response.='<script>

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

												var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

												if(!isEmpty(orario))
												{
													check_hour1("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
												}
											});

											</script>';
										break;

									case 4:
										$response.='<script>

											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

												var orario=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

												if(!isEmpty(orario))
												{
													check_hour2("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
												}
											});

											</script>';
										break;

									case 5:
										$response.='<script>
											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
													if (e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
													{
														e.preventDefault();
													}
												});

										</script>';

										break;

									case 6:
										$response.='<script>
											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").keypress(function(e) {
												if(e.which == 44)
												{
													var stringa_conenuta=$(this).val()
													if(stringa_conenuta.indexOf(',') > -1)
														e.preventDefault();
												}

												if (e.which != 44 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
													e.preventDefault();
											});
										</script>';

										break;

									case 7:
										$response.='<script>
											$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").on("blur",function(){

												var email=$("#_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'").val();

												if(!isEmpty(email))
												{
													check_email("_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'");
												}
											});
										</script>';

									break;
								}

							}

							$counter++;
							$fldnumero_righe--;
						}
					}
					
					$response.='</div></div>';

					break;
				
				case 7:
					$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-6">';
					$response.='<textarea type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' rows="6" style="width:100%" class="form-control input-sm" '.$disabled.'></textarea>';
					$response.='</div>
					</div>';
					break;	
			}
			*/

			$response.='</div>';

			$next_record=$db->next_record();
		}

		if($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
		{
			$response.='<script>$("#_valore208").change(function() {
				var risposta=$("#_valore208").val();

				switch(risposta)
				{
					case "238":
					case "239":
						$("#_valore206").val("");
						$("#_valore206").prop(\'readonly\',true);
						$("#_valore206").prop(\'required\',false);
						$("#_valore211").val("");
						$("#_valore211").prop(\'readonly\',true);
						$("#_valore211").prop(\'required\',false);
						$("#_valore212").val("");
						$("#_valore212").prop(\'readonly\',true);
						$("#_valore212").prop(\'required\',false);
						break;

					case "240":
					default:
						$("#_valore206").prop(\'readonly\',false);
						$("#_valore206").prop(\'required\',true);
						$("#_valore211").prop(\'readonly\',false);
						$("#_valore211").prop(\'required\',true);
						$("#_valore212").prop(\'readonly\',false);
						$("#_valore212").prop(\'required\',true);
						break;
				}
			});
			</script>';
		}
		elseif($pidsso_ente==102)
		{
			$response.="<script>$('#_valore268').change(function () {
				if($('#_valore268').is(':checked'))
				{
					$('#_valore269').prop('required',true);
					$('#_valore296').prop('required',true);
				}
				else
				{
					$('#_valore269').prop('required',false);
					$('#_valore296').prop('required',false);
				}
			});
			\n
			$('#_valore267').change(function () {
				if($('#_valore267').is(':checked'))
				{
					$('#_valore297').prop('required',true);
				}
				else
				{
					$('#_valore297').prop('required',false);
				}
			});
			\n
			$('#_valore265').keypress(function(e) {
				if(e.which == 44)
				{
					var stringa_conenuta=$(this).val()
					if(stringa_conenuta.indexOf(',') > -1)
						e.preventDefault();
				}

				if (e.which != 44 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
					e.preventDefault();
			});
			\n
			$('#_valore296').keypress(function(e) {
				if(e.which == 44)
				{
					var stringa_conenuta=$(this).val()
					if(stringa_conenuta.indexOf(',') > -1)
						e.preventDefault();
				}

				if (e.which != 44 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
					e.preventDefault();
			});
			</script>";
		}

		echo $response;
		break;
		
	case "load_dichiarazioni":
		$pidsso_ente=get_param("_idente");
		

		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			if($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{

			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");				
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");			
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");				
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");				
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																			
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");															
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");						
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");			
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");							
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");	
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");	
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");		
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");		
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");		
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");	
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");
		}


		$response="";

		$response.='<div class="form-group">
						<table data-toggle="table" class="table table-hover table-condensed" >
				 			<tbody>';
		$query="SELECT *,sso_progetto_criterio.codice_inps AS cod_inps,sso_progetto_criterio.flag_obbligatorio AS obbligatorio
				FROM ".DBNAME_SS.".sso_progetto_criterio
				INNER JOIN ".DBNAME_SS.".sso_tbl_requisito on sso_progetto_criterio.idsso_tbl_requisito=sso_tbl_requisito.idsso_tbl_requisito
				WHERE idsso_progetto='$fldidsso_progetto'
				ORDER BY sso_progetto_criterio.codice_inps";
		$db->query($query);
		$res = $db->next_record();
		while($res)
		{
			$fldidsso_progetto_criterio=$db->f("idsso_progetto_criterio");
			$flddescrizione=$db->f("descrizione");

			$fldflag_obbligatorio=$db->f("obbligatorio");
			if($fldflag_obbligatorio==1)
			{
				$class_check="check_dich";
				$obbligatorio=" *";
			}
			else
			{
				$class_check="";
				$obbligatorio="";
			}

			$fldpath_file=$db->f("path_file");
			$fldfilename=$db->f("filename");

			$filename="";
			if(strpos($flddescrizione,"privacy"))
			{
				if(file_exists("../documenti/modelli/privacy".$pidsso_ente.".pdf"))
					$filename="../documenti/modelli/privacy".$pidsso_ente.".pdf";
				elseif(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
					$filename=$fldpath_file.$fldfilename;
			}
			else
			{
				if(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
					$filename=$fldpath_file.$fldfilename;
			}

			if(!empty($filename))
			{
				$flddescrizione=str_replace("<doc>",'<a href="#" onClick="Documento=window.open(\''.$filename.'\',\'Documento\',width=150,height=75); return false;">',$flddescrizione);
				$flddescrizione=str_replace("</doc>","</a>",$flddescrizione);
			}

			if(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
			{
				$flddescrizione=str_replace("<doc>",'<a href="#" onClick="Documento=window.open(\''.$fldpath_file.$fldfilename.'\',\'Documento\',width=150,height=75); return false;">',$flddescrizione);
				$flddescrizione=str_replace("</doc>","</a>",$flddescrizione);
			}

			$flddescrizione=utf8_encode($flddescrizione);

			$fldidsso_domanda_requisito=get_db_value("SELECT idsso_domanda_requisito FROM sso_domanda_requisito WHERE idsso_domanda='$pidsso_domanda' AND idsso_tbl_servizio_criterio='$fldidsso_progetto_criterio'");
			if(empty($fldidsso_domanda_requisito))
				$fldchecked='';
			else
				$fldchecked=' checked ';

			$response.='<tr>
					<th style="vertical-align:middle" class="text-right">   
						<input type="checkbox" class="'.$class_check.'" name="check_'.$fldidsso_progetto_criterio.'" id="check_'.$fldidsso_progetto_criterio.'" '.$fldchecked.' data-toggle="toggle" data-size="mini" data-onstyle="success" data-offstyle="danger" data-on="SI" data-off="NO" '.$disabled.'>
					</th>
					<th>'.$flddescrizione.$obbligatorio.'</th>
				</tr>';

			$res = $db->next_record();
		}

		$response.="</tbody></table></div>";

		echo $response;
		break;

	case "load_documenti":
		$pidsso_ente=get_param("_idente");

		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			if($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");				
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");	
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");	
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");		
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																			
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");	
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");			
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");							
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");	
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");					
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");		
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");

			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");
		}

		$response="";

		$response.='<div class="form-group">
						<table data-toggle="table" class="table table-hover table-condensed" >
				 			<tbody>';
		$query="SELECT * FROM ".DBNAME_SS.".sso_progetto_documento
						WHERE idsso_progetto='$fldidsso_progetto' ".$pWhere."
						ORDER BY idsso_progetto_documento";
		$db->query($query);
		$res = $db->next_record();
		while($res)
		{
			$fldidsso_progetto_documento=$db->f("idsso_progetto_documento");
			$flddescrizione=$db->f("descrizione");
			$fldflag_obbligatorio=$db->f("flag_obbligatorio");
			$fldidsso_parametro_obbligatorio=$db->f("idsso_parametro_obbligatorio");
			$fldidsso_parametro_risposta_obbligatorio=$db->f("idsso_parametro_risposta_obbligatorio");
			$fldidsso_parametro_riga_obbligatorio=$db->f("idsso_parametro_riga_obbligatorio");
			$fldpath_file=$db->f("path_file");
			$fldfilename=$db->f("filename");
			$fldobbligatorio="NO";
			$class="";
			if($fldflag_obbligatorio==1)
			{
				$obbligatorio="*";
				$fldobbligatorio="SI";
				$class="file_obbligatorio";
			}
			elseif(!empty($fldidsso_parametro_obbligatorio) && !empty($fldidsso_parametro_risposta_obbligatorio))
			{
				$fldidsso_domanda_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$fldidsso_parametro_obbligatorio' AND idsso_progetto_graduatoria_risposte='$fldidsso_parametro_risposta_obbligatorio'");
				if(!empty($fldidsso_domanda_risposte))
				{
					$obbligatorio="*";
					$fldobbligatorio="SI";
					$class="file_obbligatorio";
				}
				else
				{
					$obbligatorio="";
					$fldobbligatorio="NO";
					$class="";
				}
			}
			elseif(!empty($fldidsso_parametro_obbligatorio) && !empty($fldidsso_parametro_riga_obbligatorio))
			{
				//controllo se la riga della tabella è compilata
				$riga_compilata=rigaCompilata($pidsso_domanda,$fldidsso_parametro_obbligatorio,$fldidsso_parametro_riga_obbligatorio);
				if($riga_compilata)
				{
					$obbligatorio="*";
					$fldobbligatorio="SI";
					$class="file_obbligatorio";
				}
				else
				{
					$obbligatorio="";
					$fldobbligatorio="NO";
					$class="";
				}
			}
			else
			{
				$obbligatorio="";
				$fldobbligatorio="NO";
				$class="";
			}

			/*
			$button_modifica='<button type="button" class="btn btn-primary btn-150 btn-xs" onclick="modificaDocumento(\''.$fldidsso_progetto.'\')" '.$accesso_modifica.' '.$disabled.' ><span class="glyphicon glyphicon-pencil span-padding" aria-hidden="true"></span>Allega documento</button>';
			*/

			$button_modifica='<input class="file-path validate '.$class.'" type="file" placeholder="" name="documento'.$fldidsso_progetto_documento.'" id="documento'.$fldidsso_progetto_documento.' ">';

			$response.='<tr>
						<th style="width: 50%" class="grassetto">'.$flddescrizione.$obbligatorio.'</th>
						<th style="width: 20%">'.$flddoc.'</th>
						<th style="width: 20%">'.$flddata_allegato.'</th>
						<th style="width: 10%" class="text-right">'.$button_modifica.'</th>
					</tr>';


			$res = $db->next_record();
		}

		$response.="</tbody></table></div>";

		echo $response;
		break;
	case "genera_otp":
		$pidsso_domanda_prestazione_voucher=get_param("_idvoucher");

		$generazione=date("Y-m-d H:i:s");
		$currentDate = strtotime($generazione);
		$scadenza = $currentDate+(60*5);

		$data_generazione=date("Y-m-d",$currentDate);
		$ora_generazione=date("H:i:s",$currentDate);

		$data_scadenza=date("Y-m-d",$scadenza);
		$ora_scadenza=date("H:i:s",$scadenza);

		$codice_otp=str_pad(rand(0000, 9999), 4, "0", STR_PAD_LEFT);

		//Elimino tutti gli OTP inutilizzati per quel voucher
		$delete="DELETE sso_domanda_prestazione_otp 
		FROM sso_domanda_prestazione_otp 
		LEFT JOIN sso_prestazione_fatta_dettaglio ON sso_prestazione_fatta_dettaglio.idsso_domanda_prestazione_otp=sso_domanda_prestazione_otp.idsso_domanda_prestazione_otp 
		WHERE sso_domanda_prestazione_otp.idsso_domanda_prestazione_voucher='$pidsso_domanda_prestazione_voucher' 
		AND sso_prestazione_fatta_dettaglio.idsso_prestazione_fatta_dettaglio IS NULL 
		AND (sso_domanda_prestazione_otp.flag_permanente=0 OR sso_domanda_prestazione_otp.flag_permanente IS NULL)";
		$db->query($delete);

		$insert="INSERT INTO sso_domanda_prestazione_otp(idsso_domanda_prestazione_voucher,codice_otp,data_generazione,ora_generazione,data_scadenza,ora_scadenza) VALUES('$pidsso_domanda_prestazione_voucher','$codice_otp','$data_generazione','$ora_generazione','$data_scadenza','$ora_scadenza')";
		$db->query($insert);

		echo "1|".$codice_otp."|".invertidata($data_scadenza,"/","-",2)."|".$ora_scadenza;

		break;

	case "annulla_otp":
		$pidsso_domanda_prestazione_voucher=get_param("_idvoucher");

		//Elimino tutti gli OTP inutilizzati per quel voucher
		$delete="DELETE sso_domanda_prestazione_otp 
		FROM sso_domanda_prestazione_otp 
		LEFT JOIN sso_prestazione_fatta_dettaglio ON sso_prestazione_fatta_dettaglio.idsso_domanda_prestazione_otp=sso_domanda_prestazione_otp.idsso_domanda_prestazione_otp 
		WHERE sso_domanda_prestazione_otp.idsso_domanda_prestazione_voucher='$pidsso_domanda_prestazione_voucher' 
		AND sso_prestazione_fatta_dettaglio.idsso_prestazione_fatta_dettaglio IS NULL 
		AND (sso_domanda_prestazione_otp.flag_permanente=0 OR sso_domanda_prestazione_otp.flag_permanente IS NULL)";
		$db->query($delete);

		echo "1";

		break;

	case "stampa_voucher":
		$pidsso_domanda_prestazione_voucher=get_param("_idvoucher");
		$codice_voucher=get_db_value("SELECT codice_voucher FROM sso_domanda_prestazione_voucher WHERE idsso_domanda_prestazione_voucher='$pidsso_domanda_prestazione_voucher'");

		$filename_voucer=getBARCORDE($codice_voucher);
		if(file_exists($filename_voucer))
		{
			echo '<object width="250" height="100" id="voucher" name="voucher" style="'.$style_voucher.'" type="image/jpeg" data="'.$filename_voucer.'">
				</object>
				<p id="p_codice_voucher"><b>'.$codice_voucher.'</b></p>
				<br><br>
				<button type="button" class="btn btn-primary btn-md" id="btn_otp" onclick="generaOTP('.$pidsso_domanda_prestazione_voucher.')" >Genera OTP</button>
				<h4 id="p_otp" style="display:none;"></h4>';
		}
		else
			echo "Errore nella generazione del voucher!";

		break;

	case "check_voucher":
		$fldidsso_anagrafica_utente=get_param("_idfornitore");
		if(!empty($fldidsso_anagrafica_utente))
		{
			$fldidsso_ente_fornitore=get_db_value("SELECT idsso_ente FROM sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");
			$fldflag_buonispesa_cf=get_db_value("SELECT flag_buonispesa_cf FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente_fornitore'");

			$pcodice_voucher=get_param("_codice");
			$pcodice_voucher=strtoupper($pcodice_voucher);

			$response="";

			if($fldflag_buonispesa_cf==1)
			{
				$oggi=date("Y-m-d");

				$fldidsso_domanda_prestazione_voucher=get_db_value("SELECT sso_domanda_prestazione_voucher.idsso_domanda_prestazione_voucher 
					FROM sso_domanda_prestazione_voucher 
					INNER JOIN sso_domanda_intervento ON sso_domanda_intervento.idsso_domanda_intervento=sso_domanda_prestazione_voucher.idsso_domanda_intervento
					WHERE sso_domanda_prestazione_voucher.codice_voucher='$pcodice_voucher' AND sso_domanda_intervento.data_inizio<='$oggi' AND sso_domanda_intervento.data_fine>='$oggi' AND sso_domanda_prestazione_voucher.flag_elimina=0");
			}
			else
			{
				$fldidsso_domanda_prestazione_voucher=get_db_value("SELECT idsso_domanda_prestazione_voucher FROM sso_domanda_prestazione_voucher WHERE codice_voucher='$pcodice_voucher' AND flag_elimina=0");
			}

			if(!empty($fldidsso_domanda_prestazione_voucher))
			{
				$fldidsso_domanda_intervento=get_db_value("SELECT idsso_domanda_intervento FROM sso_domanda_prestazione_voucher WHERE idsso_domanda_prestazione_voucher='$fldidsso_domanda_prestazione_voucher'");
				
				$pai=new Pai($fldidsso_domanda_intervento);

				$spendi_voucher=false;
				
				if($_SERVER["HTTP_HOST"]=="familycard.comune.messina.it")
				{
					$spendi_voucher=true;
				}
				elseif($pai->beneficiario->idsso_ente==50)	//SE GENGA PUOI SPENDERE ANCHE A FABRIANO E SASSOFERRATO
				{
					if($fldidsso_ente_fornitore==48 || $fldidsso_ente_fornitore==51 || $fldidsso_ente_fornitore==50)
						$spendi_voucher=true;
				}
				elseif($pai->beneficiario->idsso_ente==46 || $pai->beneficiario->idsso_ente==105)	//RENDE o RENDE2
				{
					if($fldidsso_ente_fornitore==46 || $fldidsso_ente_fornitore==105)
						$spendi_voucher=true;
				}
				else
				{
					if($pai->beneficiario->idsso_ente==$fldidsso_ente_fornitore)
						$spendi_voucher=true;
				}

				if($spendi_voucher)
				{
					$check_data=false;
					if(empty_data($pai->data_fine))
					{
						//il voucher non scade mai
						$check_data=true;
					}
					else
					{
						if($_SERVER["HTTP_HOST"]=="familycard.comune.messina.it")
							$check_data=true;
						else
						{
							if($pai->data_fine>=date("Y-m-d"))
								$check_data=true;
						}
					}

					if($check_data)
					{
						$quantita_rimanente=$pai->get_quantita_rimanente(false);
						$quantita_rimanente=round($quantita_rimanente,2);

						if(empty($quantita_rimanente) || $quantita_rimanente==0 || $quantita_rimanente=='0.00')
						{
							$response.='<div class="form-group"><h4 class="text-danger">Attenzione! l\'importo associato al Voucher <b>'.$pcodice_voucher.'</b> selezionato è terminato.</h4></div>';
						}
						else
						{
							$response.='<div class="form-group">
								<div class="col-sm-1 col-sm-offset-1 bg-primary">&nbsp;</div>
								<div class="col-sm-9 text-left grassetto bg-primary">Voucher: '.$pcodice_voucher.'</div>
							</div>';

				            $response.='<div class="col-sm-10 col-sm-offset-1">
				            	<table id="table_elenco" name="table_elenco" data-toggle="table" class="table table-hover table-condensed" >
								<thead>
									<tr class="default">
										<th style="width: 5%;" class="intestazioneTabella text-info"></th>
										<th style="width: 20%;" class="intestazioneTabella text-info text-left">Beneficiario</th>
										<th style="width: 10%;" class="intestazioneTabella text-info text-left">Residuo</th>
										<th style="width: 10%;" class="intestazioneTabella text-info text-left">Spendibile dal</th>
										<th style="width: 10%;" class="intestazioneTabella text-info text-left">al</th>
									</tr>  
								</thead>
								<tbody>';

							/*
							<button type="button" class="btn btn-primary btn-xs" onclick="scaricaVALORE('.$fldidsso_domanda_intervento.')">Scarica</button>
							*/
							
							$response.='<tr>
									<td></td>
									<td class="text-left">'.$pai->beneficiario->nominativo.' </td>
									<td class="text-left">'.number_format($quantita_rimanente,2,",","").' €</td>
									<td class="text-left">'.$pai->data_inizio_formattata.'</td>
									<td class="text-left">'.$pai->data_fine_formattata.'</td>
						  		</tr>';

						  	$response.='</tbody></table></div>';


						}
					}
					else
					{
						$response.='<div class="form-group"><h4 class="text-danger">Attenzione! Voucher non più utilizzabile, è scaduto il: <b>'.$pai->data_fine_formattata.'</b></h4></div>';
					}
				}
				else
					$response.='<div class="form-group"><h4 class="text-danger">Attenzione! non è possibile spendere il voucher: <b>'.$pcodice_voucher.'</b> in quanto il beneficiario risiede in un Comune diverso da quello per cui ha dato adesione.</h4></div>';
			}
			else
			{
				$flag_elimina=get_db_value("SELECT idsso_domanda_prestazione_voucher FROM sso_domanda_prestazione_voucher WHERE codice_voucher='$pcodice_voucher' and flag_elimina=1");
				if (empty($flag_elimina))
					$response.='<div class="form-group"><h4 class="text-danger">Attenzione! nessun Voucher trovato con codice: <b>'.$pcodice_voucher.'</b></h4></div>';
				else
					$response.='<div class="form-group"><h3 class="text-danger">Attenzione! Verifica in corso sul voucher <b>'.$pcodice_voucher.'<br> I buoni spesa sono stati sospesi per verificare la dichiarazione resa</b></h3></div>';
			}
		}
		else
			$response.='<div class="form-group"><h4 class="text-danger">Attenzione! anomalia nella lettura del voucher.</h4></div>';

		echo $response;

		break;

	case "get_intervento_voucher":
		$fldidsso_anagrafica_utente=get_param("_idfornitore");

		$fldidsso_ente_fornitore=get_db_value("SELECT idsso_ente FROM sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");
		$fldflag_buonispesa_cf=get_db_value("SELECT flag_buonispesa_cf FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente_fornitore'");

		$pcodice_voucher=get_param("_codice");
		$pcodice_voucher=strtoupper($pcodice_voucher);

		$response="";

		$oggi=date("Y-m-d");

		if($fldflag_buonispesa_cf==1)
		{
			$fldidsso_domanda_intervento=get_db_value("SELECT sso_domanda_prestazione_voucher.idsso_domanda_intervento 
				FROM sso_domanda_prestazione_voucher 
				INNER JOIN sso_domanda_intervento ON sso_domanda_intervento.idsso_domanda_intervento=sso_domanda_prestazione_voucher.idsso_domanda_intervento
				WHERE sso_domanda_prestazione_voucher.codice_voucher='$pcodice_voucher' AND sso_domanda_intervento.data_inizio<='$oggi' AND sso_domanda_intervento.data_fine>='$oggi' AND sso_domanda_prestazione_voucher.flag_elimina=0");
		}
		else
		{
			if($_SERVER["HTTP_HOST"]=="familycard.comune.messina.it")
			{
				//PER MESSINA NON DEVE CONTROLLARE LA DATA DI SCADENZA
				$fldidsso_domanda_intervento=get_db_value("SELECT sso_domanda_prestazione_voucher.idsso_domanda_intervento 
				FROM sso_domanda_prestazione_voucher 
				INNER JOIN sso_domanda_intervento ON sso_domanda_intervento.idsso_domanda_intervento=sso_domanda_prestazione_voucher.idsso_domanda_intervento 
				WHERE sso_domanda_prestazione_voucher.codice_voucher='$pcodice_voucher' AND (sso_domanda_prestazione_voucher.flag_elimina=0 OR sso_domanda_prestazione_voucher.flag_elimina IS NULL)");
			}
			else
			{
				$fldidsso_domanda_intervento=get_db_value("SELECT sso_domanda_prestazione_voucher.idsso_domanda_intervento 
				FROM sso_domanda_prestazione_voucher 
				INNER JOIN sso_domanda_intervento ON sso_domanda_intervento.idsso_domanda_intervento=sso_domanda_prestazione_voucher.idsso_domanda_intervento 
				WHERE sso_domanda_prestazione_voucher.codice_voucher='$pcodice_voucher' AND (sso_domanda_prestazione_voucher.flag_elimina=0 OR sso_domanda_prestazione_voucher.flag_elimina IS NULL) AND (sso_domanda_intervento.data_fine>='$oggi' OR sso_domanda_intervento.data_fine IS NULL OR sso_domanda_intervento.data_fine='0000-00-00')");
			}
		}

		if(empty($fldidsso_domanda_intervento))
			$response=0;
		else
		{
			$response=0;

			$pai=new Pai($fldidsso_domanda_intervento);
			$quantita_rimanente=$pai->get_quantita_rimanente(false);	
			//SE GENGA PUOI SPENDERE ANCHE A FABRIANO E SASSOFERRATO
			if(empty($quantita_rimanente) || $quantita_rimanente==0 || $quantita_rimanente=='0.00')
			{
				$response=0;
			}
			else
			{
				if($pai->beneficiario->idsso_ente==50)
				{
					if($fldidsso_ente_fornitore==48 || $fldidsso_ente_fornitore==51 || $fldidsso_ente_fornitore==50)
						$response=$fldidsso_domanda_intervento;
				}
				else
				{
					if($pai->beneficiario->idsso_ente==$fldidsso_ente_fornitore)
						$response=$fldidsso_domanda_intervento;
				}	
					
				$response=$fldidsso_domanda_intervento;		
			}
			
		}

		echo $response;
		break;

	case "validate_otp":
		$pcodice_voucher=get_param("_codice");
		$pcodice_voucher=strtoupper($pcodice_voucher);
		
		$pcodice_otp=get_param("_otp");

		$pidsso_domanda_intervento=get_param("_idintervento");

		$fldidsso_domanda_prestazione_voucher=get_db_value("SELECT idsso_domanda_prestazione_voucher FROM sso_domanda_prestazione_voucher WHERE codice_voucher='$pcodice_voucher' AND idsso_domanda_intervento='$pidsso_domanda_intervento'");
		if(!empty($fldidsso_domanda_prestazione_voucher))
		{
			//prendo in considerazione solo l'ultimo generato
			$fldidsso_domanda_prestazione_otp=get_db_value("SELECT idsso_domanda_prestazione_otp FROM sso_domanda_prestazione_otp WHERE idsso_domanda_prestazione_voucher='$fldidsso_domanda_prestazione_voucher' AND codice_otp='$pcodice_otp' ORDER BY idsso_domanda_prestazione_otp DESC");
			if(!empty($fldidsso_domanda_prestazione_otp))
			{
				//controllo se è scaduto
				$flddata_scadenza=get_db_value("SELECT data_scadenza FROM sso_domanda_prestazione_otp WHERE idsso_domanda_prestazione_otp='$fldidsso_domanda_prestazione_otp'");
				$fldora_scadenza=get_db_value("SELECT ora_scadenza FROM sso_domanda_prestazione_otp WHERE idsso_domanda_prestazione_otp='$fldidsso_domanda_prestazione_otp'");
				$fldflag_permanente=get_db_value("SELECT flag_permanente FROM sso_domanda_prestazione_otp WHERE idsso_domanda_prestazione_otp='$fldidsso_domanda_prestazione_otp'");

				$timestamp_scadenza=strtotime($flddata_scadenza." ".$fldora_scadenza);
				$timestamp_now=time();

				//se non è scaduto oppure se è un PIN permanente
				if($timestamp_now<=$timestamp_scadenza || $flag_permanente==1)
				{
					echo "true|".$fldidsso_domanda_prestazione_otp;
				}
				else
				{
					echo "false|Il Codice OTP inserito è scaduto. È necessario generarne un altro.";
				}
			}
			else
			{
				echo "false|Il Codice OTP inserito non è valido.";
			}
		}
		else
		{
			echo "false|Errore nella verifica dell'OTP.";
		}

		break;

	case "scarica_valore_otp":
		$pidsso_domanda_intervento=get_param("_idintervento");
		$pai=new Pai($pidsso_domanda_intervento);

		$fldquantita_disponibile=$pai->get_valore_rimanente(false);
		$fldquantita_disponibile=round($fldquantita_disponibile,2);

		$pcodice_voucher=get_param("_codice");
		$pcodice_voucher=strtoupper($pcodice_voucher);
		
		$fldidsso_domanda_prestazione_voucher=get_db_value("SELECT idsso_domanda_prestazione_voucher FROM sso_domanda_prestazione_voucher WHERE codice_voucher='$pcodice_voucher' AND idsso_domanda_intervento='$pidsso_domanda_intervento'");

		$pidsso_ente_servizio=get_param("_idfornitore");

		if(!empty($pidsso_ente_servizio))
		{
			$pidsso_domanda_prestazione_otp=get_param("_idotp");
			$pimporto_prestazione=get_param("_importo");
			$pimporto_prestazione=db_double($pimporto_prestazione);
			
			//$pdata_prestazione=get_param("_data");

			$pdata_prestazione=date("Y-m-d");
			$mese=date("m", strtotime($pdata_prestazione));
			$anno=date("Y", strtotime($pdata_prestazione));
			$orario_inizio=date("H:i:s");

			if(($fldquantita_disponibile-$pimporto_prestazione)>=0)
			{
				if($pidsso_ente_servizio!=$pai->idutente)
				{
					//SE C'È GIÀ UNO SCARICO PER QUELLA DATA PER QUEL FORNITORE PER QUELL'IMPORTO NON PROSEGUO
					$fldidsso_prestazione_fatta_dettaglio=get_db_value("SELECT idsso_prestazione_fatta_dettaglio FROM sso_prestazione_fatta_dettaglio WHERE idutente='$pai->idutente' AND idsso_ente_assistenza='$pidsso_ente_servizio' AND data_prestazione='$pdata_prestazione' AND importo_prestazione='$pimporto_prestazione' AND idsso_domanda_prestazione_voucher='$fldidsso_domanda_prestazione_voucher' AND TIMEDIFF('$orario_inizio',orario_inizio)<'00:02:00' AND TIMEDIFF('$orario_inizio',orario_inizio)>'-00:02:00'");
					if(empty($fldidsso_prestazione_fatta_dettaglio))
					{
						$sSQL="INSERT INTO sso_prestazione_pianificata (
							idsso_domanda_intervento,
							idsso_domanda_prestazione,
							idutente,
							data_pianificata,
							mese,
							anno,
							idsso_ente_assistenza,
							idsso_tbl_prestazione,
							orario_inizio,
							orario_fine,
							quantita
							)
							VALUES (
							'$pai->idsso_domanda_intervento',
							'$pai->idsso_domanda_prestazione',
							'$pai->idutente',
							'$pdata_prestazione',
							'$mese',
							'$anno',
							'$pidsso_ente_servizio',
							'$pai->idsso_tbl_prestazione',
							'$orario_inizio',
							'$orario_inizio',
							'1')";
						$db->query($sSQL);
						$fldidsso_prestazione_pianificata = mysql_insert_id($db->link_id());
						
						$sSQL="INSERT INTO sso_prestazione_fatta_dettaglio (
							idsso_prestazione_pianificata,
							idutente,
							data_prestazione,
							idsso_ente_assistenza,
							idsso_tbl_prestazione,
							orario_inizio,
							orario_fine,
							quantita,
							tariffa_prestazione,
							importo_prestazione,
							idsso_domanda_prestazione_otp,
							idsso_domanda_prestazione_voucher,
							note
							)
							VALUES (
							'$fldidsso_prestazione_pianificata',
							'$pai->idutente',
							'$pdata_prestazione',
							'$pidsso_ente_servizio',
							'$pai->idsso_tbl_prestazione',
							'$orario_inizio',
							'$orario_inizio',
							'1',
							'$pimporto_prestazione',
							'$pimporto_prestazione',
							'$pidsso_domanda_prestazione_otp',
							'$fldidsso_domanda_prestazione_voucher',
							'$pnote')";
						$db->query($sSQL);
						$fldidsso_prestazione_fatta_dettaglio = mysql_insert_id($db->link_id());
					
						$fldidutente=$pai->idutente;
						$fldidsso_ente=get_db_value("SELECT idsso_ente FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
						$flagflag_scarico_sms=get_db_value("SELECT flag_scarico_sms FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente'");
						if($flagflag_scarico_sms==1)
						{
							include_once ("../librerie/SmsHostingSms.php");

							$recipients=get_db_value("SELECT cellulare FROM sso_anagrafica_utente WHERE idutente='$fldidutente'");
							if(!empty($recipients))
							{
								$recipients=trim($recipients);
								$recipients=str_replace("+39", "", $recipients);
								$recipients=str_replace(" ", "", $recipients);
								$recipients=str_replace("-", "", $recipients);
								$recipients=str_replace("/", "", $recipients);
								$recipients= "39".$recipients;

								$fldente=get_db_value("SELECT ente FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente'");
								$fldfornitore=get_db_value("SELECT cognome FROM sso_anagrafica_utente WHERE idutente='$pidsso_ente_servizio'");
								$pdescrizione="COMUNE DI ".strtoupper($fldente)."\nAutorizzato utilizzo voucher.\nImporto: ".$pimporto_prestazione." euro presso: ".$fldfornitore;

								$pdescrizione=db_string($pdescrizione);

								$ptesto=stripslashes($pdescrizione);

								if(smsSTATE($fldidsso_ente))
								{
									$fldsmsmittente=get_db_value("SELECT smsmittente FROM ".DBNAME_A.".enti WHERE idente='$fldidsso_ente'");
									if(empty($fldsmsmittente))
										$fldsmsmittente="BUONISPESA";

									$smsh_sms = new SmsHostingSms ( 'SMSHVD4VJAUAE30I7LCND', 'WQ9B8RI4PO1C3WJXVUXMHBP1TWYH9NHQ' );
									$response = $smsh_sms->smsSend ( $recipients, $fldsmsmittente, NULL, $ptesto,'H', NULL, NULL, 'false', NULL, NULL );

									if ($response && $response->errorCode == 0) 
										$flag_success=true;
									else
										$flag_success=false;
								}
								else
									$flag_success=false;

								if($flag_success)
								{
									$oggi=date("Y-m-d");
									$adesso=date("H:i:s");

									$insert="INSERT INTO sso_anagrafica_sms(
										idsso_anagrafica_utente,
										descrizione,
										data_sms,
										ora_sms,
										idsso_ente
									) VALUES(
										'$fldidutente',
										'$pdescrizione',
										'$oggi',
										'$adesso',
										'$fldidsso_ente'
									)";
									$db_lib_servizi->query($insert);
								}
							}
						}

						$quantita_rimanente=$pai->get_quantita_rimanente(true);

						echo "1|".$quantita_rimanente;
					}
					else
						echo "0";
				}
				else
					echo "0";
			}
			else
				echo "0";
		}
		else
			echo "0";

		break;
	case "scarica_valore_otpback":
		$pidsso_domanda_intervento=get_param("_idintervento");
		$pai=new Pai($pidsso_domanda_intervento);

		$fldquantita_disponibile=$pai->get_valore_rimanente(false);
		$fldquantita_disponibile=round($fldquantita_disponibile,2);

		$pcodice_voucher=get_param("_codice");
		$pcodice_voucher=strtoupper($pcodice_voucher);
		
		$fldidsso_domanda_prestazione_voucher=get_db_value("SELECT idsso_domanda_prestazione_voucher FROM sso_domanda_prestazione_voucher WHERE codice_voucher='$pcodice_voucher' AND idsso_domanda_intervento='$pidsso_domanda_intervento'");

		$pidsso_ente_servizio=get_param("_idfornitore");

		
		if(!empty($pidsso_ente_servizio))
		{
			$pidsso_domanda_prestazione_otp=get_param("_idotp");
			$pimporto_prestazione=get_param("_importo");
			$pimporto_prestazione=db_double($pimporto_prestazione);
			$pdata_prestazione=get_param("_data");
			$pdata_prestazione=invertidata($pdata_prestazione,"-","/",1);
			$mese=date("m", strtotime($pdata_prestazione));
			$anno=date("Y", strtotime($pdata_prestazione));
			$orario_inizio=date("H:i:s");

			if(($fldquantita_disponibile-$pimporto_prestazione)>=0)
			{
				if($pidsso_ente_servizio!=$pai->idutente)
				{
					//SE C'È GIÀ UNO SCARICO PER QUELLA DATA PER QUEL FORNITORE PER QUELL'IMPORTO NON PROSEGUO
					$fldidsso_prestazione_fatta_dettaglio=get_db_value("SELECT idsso_prestazione_fatta_dettaglio FROM sso_prestazione_fatta_dettaglio WHERE idutente='$pai->idutente' AND idsso_ente_assistenza='$pidsso_ente_servizio' AND data_prestazione='$pdata_prestazione' AND importo_prestazione='$pimporto_prestazione' AND TIMEDIFF('$orario_inizio',orario_inizio)<'00:02:00' AND TIMEDIFF('$orario_inizio',orario_inizio)>'-00:02:00'");
					if(empty($fldidsso_prestazione_fatta_dettaglio))
					{
						$sSQL="INSERT INTO sso_prestazione_pianificata (
							idsso_domanda_intervento,
							idsso_domanda_prestazione,
							idutente,
							data_pianificata,
							mese,
							anno,
							idsso_ente_assistenza,
							idsso_tbl_prestazione,
							orario_inizio,
							orario_fine,
							quantita
							)
							VALUES (
							'$pai->idsso_domanda_intervento',
							'$pai->idsso_domanda_prestazione',
							'$pai->idutente',
							'$pdata_prestazione',
							'$mese',
							'$anno',
							'$pidsso_ente_servizio',
							'$pai->idsso_tbl_prestazione',
							'$orario_inizio',
							'$orario_inizio',
							'1')";
						$db->query($sSQL);
						$fldidsso_prestazione_pianificata = mysql_insert_id($db->link_id());
						
						$sSQL="INSERT INTO sso_prestazione_fatta_dettaglio (
							idsso_prestazione_pianificata,
							idutente,
							data_prestazione,
							idsso_ente_assistenza,
							idsso_tbl_prestazione,
							orario_inizio,
							orario_fine,
							quantita,
							tariffa_prestazione,
							importo_prestazione,
							idsso_domanda_prestazione_otp,
							idsso_domanda_prestazione_voucher,
							note
							)
							VALUES (
							'$fldidsso_prestazione_pianificata',
							'$pai->idutente',
							'$pdata_prestazione',
							'$pidsso_ente_servizio',
							'$pai->idsso_tbl_prestazione',
							'$orario_inizio',
							'$orario_inizio',
							'1',
							'$pimporto_prestazione',
							'$pimporto_prestazione',
							'$pidsso_domanda_prestazione_otp',
							'$fldidsso_domanda_prestazione_voucher',
							'$pnote')";
						$db->query($sSQL);
						$fldidsso_prestazione_fatta_dettaglio = mysql_insert_id($db->link_id());
						
						$quantita_rimanente=$pai->get_quantita_rimanente(true);
						
						echo "1|".$quantita_rimanente;
					}
					else
						echo "0";
				}
				else
					echo "0";
			}
			else
				echo "0";
		}
		else
			echo "0";
		

		break;
	
	case "get_dipendenze_bs":
		$pidsso_ente=get_param("_idente");

		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{		
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");		
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");			
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");	
			elseif($pidsso_ente==64)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");	
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																			
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");	
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");				
			elseif($pidsso_ente==101)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONIMARSALA'");			
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");							
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");							
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");	
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");										
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");	
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");		
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");
		}

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		$count_risposte_params=0;
		while($res)
		{
			$fldidsso_progetto_graduatoria_dipendenze=$db->f("idsso_progetto_graduatoria_dipendenze");
			
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			// ***************************************************
			// Qesto array non viene visualizzato da nessuna parte
			$aPARAMS=array();
			foreach($aRISPOSTE as $idrisposta)
			{
				$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
				$aPARAMS[]=$idparametro_risposta;
			}
			$aPARAMS=array_unique($aPARAMS);
			//print_r($aPARAMS);
			//echo "<br>";
			// ***************************************************

			$aPARAMS_TEMP=array();
			
			foreach($aPARAMETRI as $idparametro)
			{
				$aPARAMETRI_CONTROLLATI[]=$idparametro;

				$sql="SELECT idsso_domanda_parametro_graduatoria 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda='$pidsso_domanda' 
					AND idsso_progetto_graduatoria='$idparametro'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
				else
				{
					$counter_risp=0;
					$str_parametri_risposta='';
					foreach($aRISPOSTE as $idrisposta)
					{
						$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						$fldidsso_progetto_graduatoria_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_progetto_graduatoria_risposte='$idrisposta' AND idsso_domanda='$pidsso_domanda'");
						if(!empty($fldidsso_progetto_graduatoria_risposte))
						{
							$counter_risp++;
						}

						$str_parametri_risposta.=$idparametro_risposta."|";
					}

					$str_parametri_risposta=rtrim($str_parametri_risposta,"|");

					$count_attese=count($aRISPOSTE);

					$aVISUALIZZA[$idparametro][$count_risposte_params]["DATE"]=$counter_risp;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["ATTESE"]=$count_attese;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["PARAMETRI"]=$str_parametri_risposta;

					$count_risposte_params++;
				}
			}

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria]=$aPARAMETRI;
			}

			$res=$db->next_record();
		}

		if(!empty($aVISUALIZZA))
		{
			foreach($aVISUALIZZA as $idparametro=>$dettaglio)
			{
				$visualizza=true;
				foreach($dettaglio as $dett)
				{
					if($dett["DATE"]!=$dett["ATTESE"])
					{
						//se le attese sono tutte dello stesso parametro allora ne basta 1
						$aPARAMS=explode("|",$dett["PARAMETRI"]);
						$aPARAMS=array_unique($aPARAMS);
						if(count($aPARAMS)!=1 || $dett["DATE"]!=1)
							$visualizza=false;
					}
				}

				if($visualizza && !in_array($idparametro,$aPARAMETRI_VISUALIZZA))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
			}
		}

		$aPARAMETRI_BANDO=db_fill_array("SELECT idsso_progetto_graduatoria,idsso_progetto_graduatoria FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_BANDO))
		{
			foreach($aPARAMETRI_BANDO as $idsso_progetto_graduatoria)
			{
				if(!@in_array($idsso_progetto_graduatoria,$aPARAMETRI_CONTROLLATI))
					$aPARAMETRI_VISUALIZZA[]=$idsso_progetto_graduatoria;
			}
		}

		//print_r_formatted($aDIPENDENZE);

		$sPARAMETRI="";
		$sSQL="SELECT sso_progetto_graduatoria.*
		FROM ".DBNAME_SS.".sso_progetto_graduatoria
		WHERE sso_progetto_graduatoria.idsso_progetto='$fldidsso_progetto' AND sso_progetto_graduatoria.tipologia_parametro!=13
		ORDER BY sso_progetto_graduatoria.ordine ASC";
		$db->query($sSQL);
		$next_record=$db->next_record();
		while($next_record)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");

			$aRISPOSTE=array();
			$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
			if(!empty($aPARAMETRI_GRUPPI))
			{
				foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
				{
					$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
					if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
					{
						$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
						break;
					}
					else
						$fldidsso_progetto_graduatoria_gruppi=null;
				}
			}
			else
				$fldidsso_progetto_graduatoria_gruppi=null;

			$dipendenze=false;
			if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				//echo "fa parte di un gruppo: ".$fldidsso_progetto_graduatoria_gruppi."<br>";
				if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
				{
					//echo "<br>DIPENDENZE:<br><br>";
					foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($parametri);
								$first_key = key($parametri);
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}
			else
			{
				if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
				{
					$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
					foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);

								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}

			if($dipendenze)
			{
				//print_r_formatted($aRISPOSTE);
				$sPARAMETRI.=$fldidsso_progetto_graduatoria."|";
			}

			$next_record=$db->next_record();
		}
		
		echo $sPARAMETRI;
		break;
	
	case "get_dipendenze_bs_param":

		$pidsso_ente=get_param("_idente");
		if($_SERVER["HTTP_HOST"]=="care.immediaspa.com")
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
		}
		else
		{		
			if($pidsso_ente==1)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");
			elseif($pidsso_ente>=2 && $pidsso_ente<=10)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS8'");
			elseif($pidsso_ente==11 || $pidsso_ente==17)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARIETI'");
			elseif($pidsso_ente==36)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAVSP'");
			elseif($pidsso_ente==41)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATOLENTINO'");		
			elseif($pidsso_ente==45 || $pidsso_ente==81 || $pidsso_ente==82 || $pidsso_ente==83 || $pidsso_ente==84 || $pidsso_ente==85)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAFAMILYCARD'");
			elseif($pidsso_ente==46)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDE'");
			elseif($pidsso_ente==47)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAALGHERO'");				
			elseif($pidsso_ente==48)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==49)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==50)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==51)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");
			elseif($pidsso_ente==52)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAATS10'");		
			elseif($pidsso_ente==53)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAPESCARA'");
			elseif($pidsso_ente==60 || $pidsso_ente==61)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASPINETOLI'");				
			elseif($pidsso_ente==62)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABRINDISI'");
			elseif($pidsso_ente==64)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACF'");

				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACERVIGNANO'");
			}			
			elseif($pidsso_ente==72 || $pidsso_ente==74 || $pidsso_ente==75 || $pidsso_ente==63 || $pidsso_ente==71 || $pidsso_ente==73 || $pidsso_ente==76 || $pidsso_ente==77)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAUDC'");	
			elseif($pidsso_ente==66)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACARTOCETO'");	
			elseif($pidsso_ente==68)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALAMEZIA'");
			elseif($pidsso_ente==69)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACESANO'");																			
			elseif($pidsso_ente==78 || $pidsso_ente==67)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESALATINA'");	
			elseif($pidsso_ente==80 || $pidsso_ente==59)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");																		
			elseif($pidsso_ente==90 || $pidsso_ente==91 || $pidsso_ente==97 || $pidsso_ente==104 || $pidsso_ente==110 || $pidsso_ente==114 || $pidsso_ente==117 || $pidsso_ente==118 || $pidsso_ente==120)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");		
			elseif($pidsso_ente==121 || $pidsso_ente==122)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA2'");
			elseif($pidsso_ente==92)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABORGOVB'");				
			elseif($pidsso_ente==96)
			{
				//$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASICILIA'");	
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAGIARRE3'");	
			}
			elseif($pidsso_ente==102)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESACL'");
			elseif($pidsso_ente==103)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESANOTO'");			
			elseif($pidsso_ente==105)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESARENDEBIS'");		
			elseif($pidsso_ente==107)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESATREIA'");					
			elseif($pidsso_ente==111)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABENEVENTO'");
			elseif($pidsso_ente==112)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESASGV'");			
			elseif($pidsso_ente==113)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAENNA'");
			elseif($pidsso_ente==115)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAMONTEGRANARO'");
			elseif($pidsso_ente==116)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESABELPASSO'");
			elseif($pidsso_ente==119)
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESAANGRI'");			
			else
				$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='BUONISPESA'");
		}			

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria][]=$aPARAMETRI;
			}

			$res=$db->next_record(); 
		}

		$fldidsso_progetto_graduatoria=get_param("_idparametro");

		$aRISPOSTE=array();
		$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_GRUPPI))
		{
			foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
			{
				$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
				if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
				{
					$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
					break;
				}
				else
					$fldidsso_progetto_graduatoria_gruppi=null;
			}
		}
		else
			$fldidsso_progetto_graduatoria_gruppi=null;

		$dipendenze=false;

		if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
		{
			if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
			{
				foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
				{
					$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
					foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
					{
						if($id==0)
						{
							reset($parametri);
							$first_key = key($parametri);
							//echo "idsso_progetto_graduatoria_dipendente: ".$idparametro."<br>";
							$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;

							//$aRISPOSTE[$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$idparametro;
						}
						else
						{
							$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
							foreach($aPARAMETRI_GRUPPO as $idparametro)
							{
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
							}
						}
					}
				}

				$dipendenze=true;
			}
		}
		else
		{
			if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
				foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
				{
					foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
					{
						if($id==0)
						{
							reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
							//$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
							//$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							
							if(count($aPARAMETRO_GRUPPO_DIPENDENTE[0])>1)
							{
								foreach($aPARAMETRO_GRUPPO_DIPENDENTE[0] as $key_inside=>$array_inside)
								{
									$aRISPOSTE[$idrisposta][]=$key_inside;	
								}
							}
							else
							{
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;	
							}
						}
						else
						{
							$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
							foreach($aPARAMETRI_GRUPPO as $idparametro)
							{
								$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
							}
						}
					}
				}

				$dipendenze=true;
			}
		}

		if($dipendenze)
		{
			echo json_encode($aRISPOSTE);
		}
		
		break;

	case "get_dipendenze_progetto":
		$palias_progetto=get_param("_a");
		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");		

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		$count_risposte_params=0;
		while($res)
		{
			$fldidsso_progetto_graduatoria_dipendenze=$db->f("idsso_progetto_graduatoria_dipendenze");
			
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			// ***************************************************
			// Qesto array non viene visualizzato da nessuna parte
			$aPARAMS=array();
			foreach($aRISPOSTE as $idrisposta)
			{
				$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
				$aPARAMS[]=$idparametro_risposta;
			}
			$aPARAMS=array_unique($aPARAMS);
			//print_r($aPARAMS);
			//echo "<br>";
			// ***************************************************

			$aPARAMS_TEMP=array();
			
			foreach($aPARAMETRI as $idparametro)
			{
				$aPARAMETRI_CONTROLLATI[]=$idparametro;

				$sql="SELECT idsso_domanda_parametro_graduatoria 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda='$pidsso_domanda' 
					AND idsso_progetto_graduatoria='$idparametro'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
				else
				{
					$counter_risp=0;
					$str_parametri_risposta='';
					foreach($aRISPOSTE as $idrisposta)
					{
						$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						$fldidsso_progetto_graduatoria_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_progetto_graduatoria_risposte='$idrisposta' AND idsso_domanda='$pidsso_domanda'");
						if(!empty($fldidsso_progetto_graduatoria_risposte))
						{
							$counter_risp++;
						}

						$str_parametri_risposta.=$idparametro_risposta."|";
					}

					$str_parametri_risposta=rtrim($str_parametri_risposta,"|");

					$count_attese=count($aRISPOSTE);

					$aVISUALIZZA[$idparametro][$count_risposte_params]["DATE"]=$counter_risp;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["ATTESE"]=$count_attese;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["PARAMETRI"]=$str_parametri_risposta;

					$count_risposte_params++;
				}
			}

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria]=$aPARAMETRI;
			}

			$res=$db->next_record();
		}

		if(!empty($aVISUALIZZA))
		{
			foreach($aVISUALIZZA as $idparametro=>$dettaglio)
			{
				$visualizza=true;
				foreach($dettaglio as $dett)
				{
					if($dett["DATE"]!=$dett["ATTESE"])
					{
						//se le attese sono tutte dello stesso parametro allora ne basta 1
						$aPARAMS=explode("|",$dett["PARAMETRI"]);
						$aPARAMS=array_unique($aPARAMS);
						if(count($aPARAMS)!=1 || $dett["DATE"]!=1)
							$visualizza=false;
					}
				}

				if($visualizza && !in_array($idparametro,$aPARAMETRI_VISUALIZZA))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
			}
		}

		$aPARAMETRI_BANDO=db_fill_array("SELECT idsso_progetto_graduatoria,idsso_progetto_graduatoria FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_BANDO))
		{
			foreach($aPARAMETRI_BANDO as $idsso_progetto_graduatoria)
			{
				if(!@in_array($idsso_progetto_graduatoria,$aPARAMETRI_CONTROLLATI))
					$aPARAMETRI_VISUALIZZA[]=$idsso_progetto_graduatoria;
			}
		}

		//print_r_formatted($aDIPENDENZE);

		$sPARAMETRI="";
		$sSQL="SELECT sso_progetto_graduatoria.*
		FROM ".DBNAME_SS.".sso_progetto_graduatoria
		WHERE sso_progetto_graduatoria.idsso_progetto='$fldidsso_progetto' AND sso_progetto_graduatoria.tipologia_parametro!=13
		ORDER BY sso_progetto_graduatoria.ordine ASC";
		$db->query($sSQL);
		$next_record=$db->next_record();
		while($next_record)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");

			$aRISPOSTE=array();
			$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
			if(!empty($aPARAMETRI_GRUPPI))
			{
				foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
				{
					$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
					if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
					{
						$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
						break;
					}
					else
						$fldidsso_progetto_graduatoria_gruppi=null;
				}
			}
			else
				$fldidsso_progetto_graduatoria_gruppi=null;

			$dipendenze=false;
			if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				//echo "fa parte di un gruppo: ".$fldidsso_progetto_graduatoria_gruppi."<br>";
				if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
				{
					//echo "<br>DIPENDENZE:<br><br>";
					foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($parametri);
								$first_key = key($parametri);
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}
			else
			{
				if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
				{
					$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
					foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);

								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}

			if($dipendenze)
			{
				//print_r_formatted($aRISPOSTE);
				$sPARAMETRI.=$fldidsso_progetto_graduatoria."|";
			}

			$next_record=$db->next_record();
		}
		
		echo $sPARAMETRI;
		break;
	
	case "get_dipendenze_progetto_param":

		$palias_progetto=get_param("_a");
		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");		
		

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		while($res)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria][]=$aPARAMETRI;
			}

			$res=$db->next_record(); 
		}

		$fldidsso_progetto_graduatoria=get_param("_idparametro");

		$aRISPOSTE=array();
		$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_GRUPPI))
		{
			foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
			{
				$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
				if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
				{
					$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
					break;
				}
				else
					$fldidsso_progetto_graduatoria_gruppi=null;
			}
		}
		else
			$fldidsso_progetto_graduatoria_gruppi=null;

		$dipendenze=false;

		if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
		{
			if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
			{
				foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
				{
					$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
					foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
					{
						if($id==0)
						{
							reset($parametri);
							$first_key = key($parametri);
							//echo "idsso_progetto_graduatoria_dipendente: ".$idparametro."<br>";
							$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;

							//$aRISPOSTE[$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$idparametro;
						}
						else
						{
							$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
							foreach($aPARAMETRI_GRUPPO as $idparametro)
							{
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
							}
						}
					}
				}

				$dipendenze=true;
			}
		}
		else
		{
			if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
				foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
				{
					foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
					{
						if($id==0)
						{
							reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
							//$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
							//$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							
							if(count($aPARAMETRO_GRUPPO_DIPENDENTE[0])>1)
							{
								foreach($aPARAMETRO_GRUPPO_DIPENDENTE[0] as $key_inside=>$array_inside)
								{
									$aRISPOSTE[$idrisposta][]=$key_inside;	
								}
							}
							else
							{
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;	
							}
						}
						else
						{
							$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
							foreach($aPARAMETRI_GRUPPO as $idparametro)
							{
								$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
							}
						}
					}
				}

				$dipendenze=true;
			}
		}

		if($dipendenze)
		{
			echo json_encode($aRISPOSTE);
		}
		
		break;

	case "load_data_beneficiario_buonispesa":

		$fldcodicefiscale=get_param("_cf");
		$fldidsso_ente=get_param("_idente");

		$continue=false;
		if($_SERVER["HTTP_HOST"]=="bonus800ras.sicare.it")
		{
			if($fldidsso_ente==2)
			{
				$fldidutente=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE codicefiscale='$fldcodicefiscale' AND idsso_ente='$fldidsso_ente'");
				$ndomande=get_db_value("SELECT COUNT(*) FROM sso_domanda WHERE idutente='$fldidutente' AND idsso_tabella_stato_domanda=5");
				if($ndomande==1)
					$continue=true;
			}
		}
		elseif($fldidsso_ente==72 || $fldidsso_ente==74 || $fldidsso_ente==75 || $fldidsso_ente==63 || $fldidsso_ente==71 || $fldidsso_ente==73 || $fldidsso_ente==76 || $fldidsso_ente==77)
		{
			$fldidutente=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE codicefiscale='$fldcodicefiscale' AND idsso_ente='$fldidsso_ente'");
			$ndomande=get_db_value("SELECT COUNT(*) FROM sso_domanda WHERE idutente='$fldidutente' AND idsso_progetto=1 AND idsso_tabella_stato_domanda=5");
			if($ndomande==1)
				$continue=true;
		}
		else
			$continue=false;

		if($continue)
		{
		    $fldidutente=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE codicefiscale='$fldcodicefiscale' AND idsso_ente='$fldidsso_ente'");
			if(!empty($fldidutente))
			{
				$sSql="select * from sso_anagrafica_utente where idutente='$fldidutente'";

				$db->query($sSql);
				$next_record=$db->next_record();
					
				$response=array();
				while($next_record)
				{
					$fldidutente=$db->f("idutente");
					
					$beneficiario=new Beneficiario($fldidutente);

					$record=$fldidutente.'|'.
					$beneficiario->cognome.'|'.
					$beneficiario->nome.'|'.
					$beneficiario->codicefiscale.'|'.
					$beneficiario->idamb_nazione.'|'.
					$beneficiario->idgen_cittadinanza1.'|'.
					$beneficiario->sesso.'|'.
					$beneficiario->data_nascita_formattata."|".
					$beneficiario->idgen_comune_nascita."|".
					$beneficiario->comune_nascita."|".
					$beneficiario->prov_nascita.'|'.
					$beneficiario->indirizzo.'|'.
					$beneficiario->civico.'|'.
					$beneficiario->citta.'|'.
					$beneficiario->idamb_comune_residenza.'|'.
					$beneficiario->prov.'|'.
					$beneficiario->cellulare.'|'.
					$beneficiario->email.'|'.
					$beneficiario->idsso_tabella_condizione_soggiorno.'|'.
					$beneficiario->documento_numero.'|'.
					$beneficiario->idsso_tbl_documento_ente.'|'.
					$beneficiario->comune_documento.'|'.
					$beneficiario->idgen_comune_documento.'|'.
					$beneficiario->documento_data_rilascio_formattata.'|'.
					$beneficiario->data_scadenza_formattata;

					$next_record = $db->next_record();  
				}

				echo $record;
			}
			else
				echo "error";
		}
		else
			echo "error";
		break;
	case "load_buonispesa_data":

		$fldcodicefiscale=get_param("_cf");
		$fldidsso_ente=get_param("_idente");

		$citta=get_db_value("select comune from ".DBNAME_A.".enti where idente='$fldidsso_ente'");
		$provincia=get_db_value("select provincia from ".DBNAME_A.".enti where idente='$fldidsso_ente'");
		$idcomune=get_db_value("select idcomune from ".DBNAME_A.".enti where idente='$fldidsso_ente'");

		$sSql="select * from gen_anagrafe_popolazione where codice_fiscale='$fldcodicefiscale'";
		$db->query($sSql);
		$next_record=$db->next_record();
			
		$response=array();
		if($next_record)
		{
			$fldidgen_anagrafe_popolazione=$db->f("idgen_anagrafe_popolazione");
			$codide_famiglia=$db->f("codice_famiglia");
			$cf=$db->f("codice_fiscale");
			$codicecatasto=substr($cf,-5,4);
			if ($codicecatasto)
			{
				$idgen_comune_nascita=get_db_value("select idcomune from ".DBNAME_A.".comune where belfiore='$codicecatasto'");
				$comune_nascita=get_db_value("select comune from ".DBNAME_A.".comune where belfiore='$codicecatasto'");
				$provincia_nascita=get_db_value("select provincia from ".DBNAME_A.".comune where belfiore='$codicecatasto'");
			}	
			$record=$fldidgen_anagrafe_popolazione.'|'.
			$db->f("cognome").'|'.
			$db->f("nome").'|'.
			$db->f("codice_fiscale").'|'.
			$db->f("idamb_nazione").'|'.
			$db->f("idamb_nazione").'|'.
			$db->f("sesso").'|'.
			invertidata($db->f("data_nascita"),"/","-",2)."|".
			$idgen_comune_nascita."|".
			$comune_nascita."|".
			$provincia_nascita.'|'.
			$db->f("indirizzo").'|'.
			$db->f("civico").'|'.
			$citta.'|'.
			$idcomune.'|'.
			$provincia;

			echo $record;
		}
		else
			echo "error";

		break;
	case "load_buonispesa_famiglia":

		$pcodice_fiscale=get_param("_cf");
		$fldidsso_ente=get_param("_idente");

		$codicefamiglia=get_db_value("select codice_famiglia from gen_anagrafe_popolazione where codice_fiscale='$pcodice_fiscale' ");
		$record="";
		if (!empty($codicefamiglia))
		{
			$citta=get_db_value("select comune from ".DBNAME_A.".enti where idente='$fldidsso_ente'");

			$sSQL="select * from gen_anagrafe_popolazione where codice_famiglia='$codicefamiglia'";
			$db->query($sSQL);
			$next_record=$db->next_record();
			
			
			while($next_record)
			{

				if ($record)
					$record.="|";
				
				$record.=$db->f("cognome").' '.$db->f("nome").';'.
						 $db->f("codice_fiscale").';'.
						 $db->f("comune_nascita").' '.invertidata($db->f("data_nascita"),"/","-",2).';'.
						 $citta.';'.
						 $db->f("relazione_parentela");
				
				$next_record=$db->next_record();
				
			}
		}	

		echo $record;
		break;
/*******************************************************************************/
	case "progetto_documenti":
		$pidsso_ente=get_param("_idente");
		
		$palias_progetto=get_param("_a");

		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto_territorio WHERE idsso_comune='$pidsso_ente'");
		if (empty($fldidsso_progetto))	
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");
		}	
		
		$ndocumenti=get_db_value("SELECT COUNT(*) FROM sso_progetto_documento WHERE idsso_progetto='$fldidsso_progetto'");

		if(empty($ndocumenti))
			echo "0";
		else
			echo $ndocumenti;

		break;

	case "progetto_dati_informativi":
		$pidsso_ente=get_param("_idente");
		$palias_progetto=get_param("_a");

		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto_territorio WHERE idsso_comune='$pidsso_ente'");
		if (empty($fldidsso_progetto))	
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");
		}

		
		$ninformativi=get_db_value("SELECT COUNT(*) FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");

		if(empty($ninformativi))
			echo "0";
		else
			echo $ninformativi;

		break;
	case "progetto_load_informativi":
		$pidsso_ente=get_param("_idente");

		$palias_progetto=get_param("_a");

		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto_territorio WHERE idsso_comune='$pidsso_ente'");
		if (empty($fldidsso_progetto))	
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");
		}

		$sSQL="SELECT * FROM ".DBNAME_SS.".sso_progetto_graduatoria_dipendenze WHERE idsso_progetto='$fldidsso_progetto'";
		$db->query($sSQL);
		$res=$db->next_record();
		$count_risposte_params=0;
		while($res)
		{
			$fldidsso_progetto_graduatoria_dipendenze=$db->f("idsso_progetto_graduatoria_dipendenze");
			
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");
			$fldidsso_progetto_graduatoria_gruppi=$db->f("idsso_progetto_graduatoria_gruppi");

			$aPARAMETRI=array();

			if(!empty($fldidsso_progetto_graduatoria_gruppi))
			{
				$fldparametri_gruppo=get_db_value("SELECT parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto_graduatoria_gruppi='$fldidsso_progetto_graduatoria_gruppi'");
				$aPARAMETRI=explode(",",$fldparametri_gruppo);
			}
			else
				$aPARAMETRI[]=$fldidsso_progetto_graduatoria;

			$fldidsso_progetto_graduatoria_capo=$db->f("idsso_progetto_graduatoria_capo");
			$fldidsso_progetto_graduatoria_gruppi_capo=$db->f("idsso_progetto_graduatoria_gruppi_capo");

			$aRISPOSTE=array();

			$fldrisposte=$db->f("risposte");
			$aRISPOSTE=explode(",",$fldrisposte);

			// ***************************************************
			// Qesto array non viene visualizzato da nessuna parte
			$aPARAMS=array();
			foreach($aRISPOSTE as $idrisposta)
			{
				$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");
				$aPARAMS[]=$idparametro_risposta;
			}
			$aPARAMS=array_unique($aPARAMS);
			//print_r($aPARAMS);
			//echo "<br>";
			// ***************************************************

			$aPARAMS_TEMP=array();
			
			foreach($aPARAMETRI as $idparametro)
			{
				$aPARAMETRI_CONTROLLATI[]=$idparametro;

				$sql="SELECT idsso_domanda_parametro_graduatoria 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda='$pidsso_domanda' 
					AND idsso_progetto_graduatoria='$idparametro'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
				else
				{
					$counter_risp=0;
					$str_parametri_risposta='';
					foreach($aRISPOSTE as $idrisposta)
					{
						$idparametro_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						$fldidsso_progetto_graduatoria_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_progetto_graduatoria_risposte='$idrisposta' AND idsso_domanda='$pidsso_domanda'");
						if(!empty($fldidsso_progetto_graduatoria_risposte))
						{
							$counter_risp++;
						}

						$str_parametri_risposta.=$idparametro_risposta."|";
					}

					$str_parametri_risposta=rtrim($str_parametri_risposta,"|");

					$count_attese=count($aRISPOSTE);

					$aVISUALIZZA[$idparametro][$count_risposte_params]["DATE"]=$counter_risp;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["ATTESE"]=$count_attese;
					$aVISUALIZZA[$idparametro][$count_risposte_params]["PARAMETRI"]=$str_parametri_risposta;

					$count_risposte_params++;
				}
			}

			foreach($aRISPOSTE as $idrisposta)
			{
				$aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi_capo][$fldidsso_progetto_graduatoria_capo][$idrisposta][$fldidsso_progetto_graduatoria_gruppi][$fldidsso_progetto_graduatoria]=$aPARAMETRI;
			}

			$res=$db->next_record();
		}

		if(!empty($aVISUALIZZA))
		{
			foreach($aVISUALIZZA as $idparametro=>$dettaglio)
			{
				$visualizza=true;
				foreach($dettaglio as $dett)
				{
					if($dett["DATE"]!=$dett["ATTESE"])
					{
						//se le attese sono tutte dello stesso parametro allora ne basta 1
						$aPARAMS=explode("|",$dett["PARAMETRI"]);
						$aPARAMS=array_unique($aPARAMS);
						if(count($aPARAMS)!=1 || $dett["DATE"]!=1)
							$visualizza=false;
					}
				}

				if($visualizza && !in_array($idparametro,$aPARAMETRI_VISUALIZZA))
					$aPARAMETRI_VISUALIZZA[]=$idparametro;
			}
		}

		$aPARAMETRI_BANDO=db_fill_array("SELECT idsso_progetto_graduatoria,idsso_progetto_graduatoria FROM sso_progetto_graduatoria WHERE idsso_progetto='$fldidsso_progetto'");
		if(!empty($aPARAMETRI_BANDO))
		{
			foreach($aPARAMETRI_BANDO as $idsso_progetto_graduatoria)
			{
				if(!@in_array($idsso_progetto_graduatoria,$aPARAMETRI_CONTROLLATI))
					$aPARAMETRI_VISUALIZZA[]=$idsso_progetto_graduatoria;
			}
		}

		//print_r_formatted($aPARAMETRI_VISUALIZZA);

		$response="";

		$sSQL="SELECT sso_progetto_graduatoria.*
		FROM ".DBNAME_SS.".sso_progetto_graduatoria
		WHERE sso_progetto_graduatoria.idsso_progetto='$fldidsso_progetto' AND sso_progetto_graduatoria.tipologia_parametro!=13 AND sso_progetto_graduatoria.flag_front=1
		ORDER BY sso_progetto_graduatoria.ordine ASC";
		$db->query($sSQL);
		$next_record=$db->next_record();
		while($next_record)
		{
			$fldidsso_progetto_graduatoria=$db->f("idsso_progetto_graduatoria");

			$aRISPOSTE=array();
			$aPARAMETRI_GRUPPI=db_fill_array("SELECT idsso_progetto_graduatoria_gruppi,parametri_gruppo FROM sso_progetto_graduatoria_gruppi WHERE idsso_progetto='$fldidsso_progetto'");
			if(!empty($aPARAMETRI_GRUPPI))
			{
				foreach($aPARAMETRI_GRUPPI as $idsso_progetto_graduatoria_gruppi=>$parametri)
				{
					$aPARAMETRI_DETTAGLIO=explode(",",$parametri);
					if(in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_DETTAGLIO))
					{
						$fldidsso_progetto_graduatoria_gruppi=$idsso_progetto_graduatoria_gruppi;
						break;
					}
					else
						$fldidsso_progetto_graduatoria_gruppi=null;
				}
			}
			else
				$fldidsso_progetto_graduatoria_gruppi=null;

			$dipendenze=false;
			if(!empty($fldidsso_progetto_graduatoria_gruppi) && empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
			{
				//echo "fa parte di un gruppo: ".$fldidsso_progetto_graduatoria_gruppi."<br>";
				if(!empty($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi]))
				{
					//echo "<br>DIPENDENZE:<br><br>";
					foreach($aDIPENDENZE[$fldidsso_progetto_graduatoria_gruppi][0] as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						$fldidsso_progetto_graduatoria_gruppi_risposta=get_db_value("SELECT idsso_progetto_graduatoria FROM sso_progetto_graduatoria_risposte WHERE idsso_progetto_graduatoria_risposte='$idrisposta'");

						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($parametri);
								$first_key = key($parametri);
								$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][]=$first_key;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$fldidsso_progetto_graduatoria_gruppi."g"][$idrisposta][$fldidsso_progetto_graduatoria_gruppi_risposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}
			else
			{
				if(!empty($aDIPENDENZE[0][$fldidsso_progetto_graduatoria]))
				{
					$aDIPENDENZE_PARAMETRO=$aDIPENDENZE[0][$fldidsso_progetto_graduatoria];
					foreach($aDIPENDENZE_PARAMETRO as $idrisposta=>$aPARAMETRO_GRUPPO_DIPENDENTE)
					{
						foreach($aPARAMETRO_GRUPPO_DIPENDENTE as $id=>$parametri)
						{
							if($id==0)
							{
								reset($aPARAMETRO_GRUPPO_DIPENDENTE[0]);
								$fldidsso_progetto_graduatoria_dipendente = key($aPARAMETRO_GRUPPO_DIPENDENTE[0]);

								$aRISPOSTE[$idrisposta][]=$fldidsso_progetto_graduatoria_dipendente;
							}
							else
							{
								$aPARAMETRI_GRUPPO=$aPARAMETRO_GRUPPO_DIPENDENTE[$id][0];
								foreach($aPARAMETRI_GRUPPO as $idparametro)
								{
									$aRISPOSTE[$idrisposta][$id."g"][]=$idparametro;
								}
							}
						}
					}

					$dipendenze=true;
				}
			}

			$fldidsso_tabella_parametro_graduatoria=$db->f("idsso_tabella_parametro_graduatoria");
			$flddescrizione_parametro=$db->f("descrizione_parametro");
			$fldinfo_tooltip=$db->f("info_tooltip");
			if(!empty($fldinfo_tooltip))
			{
				$fldinfo_tooltip=str_replace(array("\r\n", "\r", "\n"),"<br>",$fldinfo_tooltip);
				$info_tooltip=genera_tooltip($fldinfo_tooltip, '<i class="fa fa-question-circle fa-lg span-padding" aria-hidden="true"></i>', null, "auto");
			}
			else
				$info_tooltip="";

			$fldtipologia_parametro=$db->f("tipologia_parametro");
			$fldflag_obbligatorio=$db->f("flag_obbligatorio");
			if($fldflag_obbligatorio==1)
			{
				$required="required";
				$obbligatorio="*";
			}
			else
			{
				$required="";
				$obbligatorio="";
			}

			$fldidsso_parametro_gruppo_visualizzazione=$db->f("idsso_parametro_gruppo_visualizzazione");
			$fldidsso_parametro_gruppo_valore_visualizzazione=$db->f("idsso_parametro_gruppo_valore_visualizzazione");

			if(@in_array($fldidsso_progetto_graduatoria,$aPARAMETRI_VISUALIZZA))
				$display_visibilita="";
			else
			{
				$display_visibilita="display:none;";
				//$required="";
				//$obbligatorio="";
			}

			$response.='<div id="div'.$fldidsso_progetto_graduatoria.'" style="'.$display_visibilita.'">';

			if($dipendenze)
			{
				//print_r_formatted($aRISPOSTE);

				$sPARAMETRI.=$fldidsso_progetto_graduatoria."|";
				$function_onchange="onChange=\"changeDIPENDENZE('".$fldidsso_progetto_graduatoria."','1')\"";
			}
			else
				$function_onchange='onChange="valueChange(this.id)"';

			$fldlabel_precedente=$db->f("label_precedente");
			$fldlabel_color=$db->f("label_color");
			if(empty($fldlabel_color))
				$fldlabel_color="#000000";

			if($fldlabel_precedente=="<hr>")
				$response.='<hr class="hr_step3">';
			elseif(!empty($fldlabel_precedente))
				$response.='<div class="form-group"><div class="col-sm-8 col-sm-offset-2"><h4 style="color:'.$fldlabel_color.';">'.$fldlabel_precedente.'<h4></div></div>';

			if($fldtipologia_parametro==1)
			{
				$fldflag_controlli=$db->f("flag_controlli");

				switch($fldflag_controlli)
				{
					case 1:
						$maxlength=10;
					break;

					case 2:
						$maxlength=16;
					break;

					case 3:
						$maxlength=8;
					break;

					case 4:
						$maxlength=5;
					break;

					default:
						$maxlength=$db->f("maxlength");
						if(empty($maxlength))
							$maxlength=null;
					break;
				}

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">';
				$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control" maxlength="'.$maxlength.'" value="" '.$disabled.'>';
				$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==2)
			{
				$response.='<div class="form-group">
							<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-3">
							<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-sm btn-success class_multi_dip" multiple="multiple" '.$accesso_modifica.' '.$disabled.' '.$required.'> ';

							$sSQL="SELECT *
								FROM sso_progetto_graduatoria_risposte 
								WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' ORDER BY descrizione";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");
								$fldlabel_altradomanda=$db2->f("label_altradomanda");
								$fldflag_altradomanda=$db2->f("flag_altradomanda");
								$fldidsso_domanda_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto_graduatoria_risposte='$fldidsso_progetto_graduatoria_risposta'");
								
								if(empty($fldidsso_domanda_risposte))
									$selected='';
								else
									$selected='selected';

								$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" '.$selected.'>'.$flddescrizione_risposta.'</option>';
							
								$result=$db2->next_record();
							}
				$response.='</select>
					</div>
				</div>';

				$response.='<script type="text/javascript">
					$(document).ready(function() {

						$(\'#_valore'.$fldidsso_progetto_graduatoria.'\').multiselect({
							allSelectedText: "Tutte le opzioni",
							nonSelectedText: "Nessuna opzione selezionata",
							nSelectedText: " opzioni selezionate",

							buttonWidth: "100%",
							onChange: function(element, checked) {
								aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
							}
						});

						aggiornaMultiselect('.$fldidsso_progetto_graduatoria.')
					});
				</script>';

				$response.='<input type="hidden" id="selected_'.$fldidsso_progetto_graduatoria.'" name="selected_'.$fldidsso_progetto_graduatoria.'" value=""/>';
			}
			elseif($fldtipologia_parametro==3)
			{
				$fldlabel_altradomanda='';
				$fldflag_altradomanda='';

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">
						<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm" '.$disabled.' '.$function_onchange.'>
							<option value=""></option>';

							$sSQL="SELECT * 
							FROM sso_progetto_graduatoria_risposte 
							WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
							ORDER BY punteggio ASC";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");

								$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

								$result=$db2->next_record();
							}
				$response.='</select>
					</div>

					<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
					$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==5)
			{
				$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-1">';
				$response.='<input type="checkbox" class="check_info" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs" onChange="check_valore(\'_valore'.$fldidsso_progetto_graduatoria.'\')">';

				$response.='<input type="hidden" id="_check_valore'.$fldidsso_progetto_graduatoria.'" name="_check_valore'.$fldidsso_progetto_graduatoria.'" class="form-control input-xs">';

				$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==6)
			{
				$descrizione_temp=str_replace(" ","",$flddescrizione_parametro);
				if(!empty($descrizione_temp))
				{
					$response.='<div class="form-group">
						<label class="col-sm-11 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.'</label>
						</div>';
				}

				$response.='<div class="form-group">';

				$fldnumero_righe=$db->f("numero_righe");

				$aColonne=db_fill_array("SELECT idsso_progetto_graduatoria_tabelle,descrizione FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' AND idsso_progetto='$fldidsso_progetto' ORDER BY idsso_progetto_graduatoria_tabelle ASC");
				//print_r($aColonne);
				
				if(is_array($aColonne))
				{
					$response.='<div style="margin-left:10%; margin-right:10%;">';
					$response.='<table data-toggle="table" class="table table-hover table-condensed" >
					<thead>
						<tr class="default">';

					foreach($aColonne as $idcolonna=>$intestazione)
					{
						$response.='<th style="width: 10%;" class="intestazioneTabella text-info">'.$intestazione.'</th>';
					}	

					$response.='</tr>
					</thead>';

					
					$counter=1;
					while($fldnumero_righe>0)
					{
						$response.='<tr>';
						foreach($aColonne as $idcolonna=>$intestazione)
						{
							$fldflag_colonna_tipologia=get_db_value("SELECT flag_tipologia_colonna FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

							switch($fldflag_colonna_tipologia)
							{
								case 1:
									$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
									break;

								case 2:
									$response.='<td><select class="form-control input-sm" style="" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" '.$disabled.'>';
									
									$response.='\n <option value="" selected></option>';

									$fldcolonna_scelta=get_db_value("SELECT colonna_scelta FROM sso_progetto_graduatoria_tabelle WHERE idsso_progetto_graduatoria_tabelle='$idcolonna'");

									$colonna_scelta=explode("|",$fldcolonna_scelta);
									foreach ($colonna_scelta as $key => $value) 
									{
										list($altrokey,$altrovalue)=explode(";",$value);
										
										$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
									}

									$response.='</select></td>';
									break;

								default:
									$response.='<td><input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" name="_valore'.$fldidsso_progetto_graduatoria.'_'.$idcolonna.'_'.$counter.'" class="form-control input-sm" value="" '.$disabled.'></td>';
									break;
							}
							
						}

						$response.='<tr>';

						$counter++;

						$fldnumero_righe--;
					}
				
					$response.='</table>';

					//script per controlli su tabella
					$fldnumero_righe=$db->f("numero_righe");
					$counter=1;

				}
				
				
				$response.='</div></div>';
			}
			elseif($fldtipologia_parametro==7)
			{
				$response.='<div class="form-group">
						<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
						<div class="col-sm-6">';
				$response.='<textarea type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' rows="6" style="width:100%" class="form-control input-sm" '.$disabled.'></textarea>';
				$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==8)
			{
				$fldmodalita=get_db_value("SELECT COUNT(*) FROM sso_progetto_graduatoria_istituti_risposte WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria'");
				
				$sql="SELECT idsso_domanda_parametro_graduatoria 
				FROM sso_domanda_parametro_graduatoria 
				WHERE idsso_domanda='$pidsso_domanda' 
				AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
				{
					$sql="SELECT idsso_progetto_graduatoria_risposte 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
					$fldidsso_progetto_graduatoria_risposte_temp=get_db_value($sql);
				}

				$fldlabel_altradomanda='';
				$fldflag_altradomanda='';

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">
						<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm tipologia3 '.$class_empty.'" '.$accesso_modifica.' '.$disabled.' '.$function_onchange.'>
							<option value=""></option>';

							$sSQL="SELECT * 
							FROM sso_progetto_graduatoria_risposte 
							WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
							ORDER BY punteggio ASC";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");
			
								if($array_graduatoria[$fldidsso_progetto_graduatoria]==$fldidsso_progetto_graduatoria_risposta)
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" selected>'.$flddescrizione_risposta.'</option>';
								else
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

								if($fldidsso_progetto_graduatoria_risposta==$fldidsso_progetto_graduatoria_risposte_temp)
								{
									$fldlabel_altradomanda=$db2->f("label_altradomanda");
									$fldflag_altradomanda=$db2->f("flag_altradomanda");
									$fldflag_tipologia_altradomanda=$db2->f("flag_tipologia_altradomanda");
									$fldaltradomanda_scelta=$db2->f("altradomanda_scelta");
									$fldflag_controlli=$db2->f("flag_controlli");

									$fldidsso_progetto_graduatoria_risposta_domanda=$fldidsso_progetto_graduatoria_risposte_temp;

									switch($fldflag_controlli)
									{
										case 1:
											$altrarisposta_class="altrarisposta_data";
											$blur_function='check_data(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 2:
											$altrarisposta_class="altrarisposta_cf";
											$blur_function='check_cf(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 3:
											$altrarisposta_class="altrarisposta_orario1";
											$blur_function='check_hour1(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 4:
											$altrarisposta_class="altrarisposta_orario2";
											$blur_function='check_hour2(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 5:
											$altrarisposta_class="altrarisposta_numero";
											$blur_function='';
										break;

										case 6:
											$altrarisposta_class="altrarisposta_isee";
											$blur_function='';
										break;

										case 7:
											$altrarisposta_class="altrarisposta_email";
											$blur_function='check_email(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										default:
											$altrarisposta_class="";
											$blur_function='';
										break;
									}
								}

								$result=$db2->next_record();
							}
					
				$response.='</select>
					</div>

					<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
						if(!empty($fldidsso_domanda_parametro_graduatoria))
						{
							$sql="SELECT altrarisposta_testo 
							FROM sso_domanda_parametro_graduatoria 
							WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
							$fldaltrarisposta_testo=get_db_value($sql);

							if($fldflag_tipologia_altradomanda==1)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><input type="text" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$altrarisposta_class.' '.$class_empty.'" value="'.$fldaltrarisposta_testo.'" '.$accesso_modifica.' '.$disabled.' '.$required.'></div>';
							}
							elseif($fldflag_tipologia_altradomanda==2)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><textarea id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$class_empty.'" rows="6" cols="10" maxlength="1000" style="" placeholder="" '.$required.'>'.$fldaltrarisposta_testo.'</textarea></div>';
							}
							elseif($fldflag_tipologia_altradomanda==3)
							{										
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4">';
								
								$response.='<select class="form-control input-sm '.$class_empty.'" style="" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" '.$accesso_modifica.' '.$disabled.'>';
								$aaltradomanda_scelta=explode("|",$fldaltradomanda_scelta);
								foreach ($aaltradomanda_scelta as $key => $value) 
								{
									list($altrokey,$altrovalue)=explode(";",$value);
									if ($fldaltrarisposta_testo==$altrokey)
										$response.='\n <option value=\''.$altrokey.'\' selected>'.$altrovalue.'</option>';
									else
										$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
								}
								
								$response.='</select>';
								
								$response.='</div>';
							}
							elseif($fldflag_tipologia_altradomanda==4)
							{
								$response.='<div class="col-sm-5 col-sm-offset-1">'.$fldlabel_altradomanda.'</div>';
							}
						}

					$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==9)
			{
				$sql="SELECT idsso_domanda_parametro_graduatoria 
				FROM sso_domanda_parametro_graduatoria 
				WHERE idsso_domanda='$pidsso_domanda' 
				AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
				{
					$sql="SELECT idsso_progetto_graduatoria_risposte 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
					$fldidsso_progetto_graduatoria_risposte_temp=get_db_value($sql);
				}

				$fldlabel_altradomanda='';
				$fldflag_altradomanda='';

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">
						<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm tipologia3 '.$class_empty.'" '.$accesso_modifica.' '.$disabled.' '.$function_onchange.'>
							<option value=""></option>';

							$sSQL="SELECT * 
							FROM sso_progetto_graduatoria_risposte 
							WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
							ORDER BY punteggio ASC";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");
			
								if($array_graduatoria[$fldidsso_progetto_graduatoria]==$fldidsso_progetto_graduatoria_risposta)
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" selected>'.$flddescrizione_risposta.'</option>';
								else
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

								if($fldidsso_progetto_graduatoria_risposta==$fldidsso_progetto_graduatoria_risposte_temp)
								{
									$fldlabel_altradomanda=$db2->f("label_altradomanda");
									$fldflag_altradomanda=$db2->f("flag_altradomanda");
									$fldflag_tipologia_altradomanda=$db2->f("flag_tipologia_altradomanda");
									$fldaltradomanda_scelta=$db2->f("altradomanda_scelta");
									$fldflag_controlli=$db2->f("flag_controlli");

									$fldidsso_progetto_graduatoria_risposta_domanda=$fldidsso_progetto_graduatoria_risposte_temp;

									switch($fldflag_controlli)
									{
										case 1:
											$altrarisposta_class="altrarisposta_data";
											$blur_function='check_data(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 2:
											$altrarisposta_class="altrarisposta_cf";
											$blur_function='check_cf(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 3:
											$altrarisposta_class="altrarisposta_orario1";
											$blur_function='check_hour1(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 4:
											$altrarisposta_class="altrarisposta_orario2";
											$blur_function='check_hour2(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 5:
											$altrarisposta_class="altrarisposta_numero";
											$blur_function='';
										break;

										case 6:
											$altrarisposta_class="altrarisposta_isee";
											$blur_function='';
										break;

										case 7:
											$altrarisposta_class="altrarisposta_email";
											$blur_function='check_email(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										default:
											$altrarisposta_class="";
											$blur_function='';
										break;
									}
								}

								$result=$db2->next_record();
							}
					
				$response.='</select>
					</div>

					<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
						if(!empty($fldidsso_domanda_parametro_graduatoria))
						{
							$sql="SELECT altrarisposta_testo 
							FROM sso_domanda_parametro_graduatoria 
							WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
							$fldaltrarisposta_testo=get_db_value($sql);

							if($fldflag_tipologia_altradomanda==1)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><input type="text" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$altrarisposta_class.' '.$class_empty.'" value="'.$fldaltrarisposta_testo.'" '.$accesso_modifica.' '.$disabled.' '.$required.'></div>';
							}
							elseif($fldflag_tipologia_altradomanda==2)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><textarea id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$class_empty.'" rows="6" cols="10" maxlength="1000" style="" placeholder="" '.$required.'>'.$fldaltrarisposta_testo.'</textarea></div>';
							}
							elseif($fldflag_tipologia_altradomanda==3)
							{										
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4">';
								
								$response.='<select class="form-control input-sm '.$class_empty.'" style="" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" '.$accesso_modifica.' '.$disabled.'>';
								$aaltradomanda_scelta=explode("|",$fldaltradomanda_scelta);
								foreach ($aaltradomanda_scelta as $key => $value) 
								{
									list($altrokey,$altrovalue)=explode(";",$value);
									if ($fldaltrarisposta_testo==$altrokey)
										$response.='\n <option value=\''.$altrokey.'\' selected>'.$altrovalue.'</option>';
									else
										$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
								}
								
								$response.='</select>';
								
								$response.='</div>';
							}
							elseif($fldflag_tipologia_altradomanda==4)
							{
								$response.='<div class="col-sm-5 col-sm-offset-1">'.$fldlabel_altradomanda.'</div>';
							}
						}

					$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==10)
			{
				$sql="SELECT idsso_domanda_parametro_graduatoria 
				FROM sso_domanda_parametro_graduatoria 
				WHERE idsso_domanda='$pidsso_domanda' 
				AND idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria'";
				$fldidsso_domanda_parametro_graduatoria=get_db_value($sql);
				$fldidsso_progetto_graduatoria_risposte_temp=0;
				if(!empty($fldidsso_domanda_parametro_graduatoria))
				{
					$sql="SELECT idsso_progetto_graduatoria_risposte 
					FROM sso_domanda_parametro_graduatoria 
					WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
					$fldidsso_progetto_graduatoria_risposte_temp=get_db_value($sql);
				}

				$fldlabel_altradomanda='';
				$fldflag_altradomanda='';

				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">
						<select id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm tipologia3 '.$class_empty.'" '.$accesso_modifica.' '.$disabled.' '.$function_onchange.'>
							<option value=""></option>';

							$sSQL="SELECT * 
							FROM sso_progetto_graduatoria_risposte 
							WHERE idsso_progetto_graduatoria='$fldidsso_progetto_graduatoria' 
							ORDER BY punteggio ASC";
							$db2->query($sSQL);
							$result=$db2->next_record();
							while($result)
							{
								$fldidsso_progetto_graduatoria_risposta=$db2->f("idsso_progetto_graduatoria_risposte");;
								$flddescrizione_risposta=$db2->f("descrizione");
			
								if($array_graduatoria[$fldidsso_progetto_graduatoria]==$fldidsso_progetto_graduatoria_risposta)
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'" selected>'.$flddescrizione_risposta.'</option>';
								else
									$response.='<option value="'.$fldidsso_progetto_graduatoria_risposta.'">'.$flddescrizione_risposta.'</option>';

								if($fldidsso_progetto_graduatoria_risposta==$fldidsso_progetto_graduatoria_risposte_temp)
								{
									$fldlabel_altradomanda=$db2->f("label_altradomanda");
									$fldflag_altradomanda=$db2->f("flag_altradomanda");
									$fldflag_tipologia_altradomanda=$db2->f("flag_tipologia_altradomanda");
									$fldaltradomanda_scelta=$db2->f("altradomanda_scelta");
									$fldflag_controlli=$db2->f("flag_controlli");

									$fldidsso_progetto_graduatoria_risposta_domanda=$fldidsso_progetto_graduatoria_risposte_temp;

									switch($fldflag_controlli)
									{
										case 1:
											$altrarisposta_class="altrarisposta_data";
											$blur_function='check_data(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 2:
											$altrarisposta_class="altrarisposta_cf";
											$blur_function='check_cf(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 3:
											$altrarisposta_class="altrarisposta_orario1";
											$blur_function='check_hour1(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 4:
											$altrarisposta_class="altrarisposta_orario2";
											$blur_function='check_hour2(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										case 5:
											$altrarisposta_class="altrarisposta_numero";
											$blur_function='';
										break;

										case 6:
											$altrarisposta_class="altrarisposta_isee";
											$blur_function='';
										break;

										case 7:
											$altrarisposta_class="altrarisposta_email";
											$blur_function='check_email(\'_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'\')';
										break;

										default:
											$altrarisposta_class="";
											$blur_function='';
										break;
									}
								}

								$result=$db2->next_record();
							}
					
				$response.='</select>
					</div>

					<div id="altradomanda_'.$fldidsso_progetto_graduatoria.'">';
						if(!empty($fldidsso_domanda_parametro_graduatoria))
						{
							$sql="SELECT altrarisposta_testo 
							FROM sso_domanda_parametro_graduatoria 
							WHERE idsso_domanda_parametro_graduatoria='$fldidsso_domanda_parametro_graduatoria'";
							$fldaltrarisposta_testo=get_db_value($sql);

							if($fldflag_tipologia_altradomanda==1)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><input type="text" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$altrarisposta_class.' '.$class_empty.'" value="'.$fldaltrarisposta_testo.'" '.$accesso_modifica.' '.$disabled.' '.$required.'></div>';
							}
							elseif($fldflag_tipologia_altradomanda==2)
							{
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4"><textarea id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" class="form-control input-sm '.$class_empty.'" rows="6" cols="10" maxlength="1000" style="" placeholder="" '.$required.'>'.$fldaltrarisposta_testo.'</textarea></div>';
							}
							elseif($fldflag_tipologia_altradomanda==3)
							{										
								$response.='<label class="col-sm-2 control-label">'.$fldlabel_altradomanda.$obbligatorio.'</label>
								<div class="col-sm-4">';
								
								$response.='<select class="form-control input-sm '.$class_empty.'" style="" name="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" id="_altrarisposta'.$fldidsso_progetto_graduatoria_risposta_domanda.'" '.$accesso_modifica.' '.$disabled.'>';
								$aaltradomanda_scelta=explode("|",$fldaltradomanda_scelta);
								foreach ($aaltradomanda_scelta as $key => $value) 
								{
									list($altrokey,$altrovalue)=explode(";",$value);
									if ($fldaltrarisposta_testo==$altrokey)
										$response.='\n <option value=\''.$altrokey.'\' selected>'.$altrovalue.'</option>';
									else
										$response.='\n <option value=\''.$altrokey.'\' >'.$altrovalue.'</option>';
								}
								
								$response.='</select>';
								
								$response.='</div>';
							}
							elseif($fldflag_tipologia_altradomanda==4)
							{
								$response.='<div class="col-sm-5 col-sm-offset-1">'.$fldlabel_altradomanda.'</div>';
							}
						}

					$response.='</div>
				</div>';
			}
			elseif($fldtipologia_parametro==11 || $fldtipologia_parametro==12)
			{
				$fldflag_controlli=$db->f("flag_controlli");

				$maxlength=10;
				
				$response.='<div class="form-group">
					<label class="col-sm-3 control-label" id="label'.$fldidsso_progetto_graduatoria.'">'.$info_tooltip.$flddescrizione_parametro.$obbligatorio.':</label>
					<div class="col-sm-3">';
				$response.='<input type="text" id="_valore'.$fldidsso_progetto_graduatoria.'" name="_valore'.$fldidsso_progetto_graduatoria.'" '.$required.' class="form-control input-sm '.$class_empty.'" maxlength="'.$maxlength.'" value="'.$array_graduatoria[$fldidsso_progetto_graduatoria].'" '.$accesso_modifica.' '.$disabled.'>';
				$response.='</div>
				</div>';

				$response.='<script>

					$("#_valore'.$fldidsso_progetto_graduatoria.'").datepicker({
						language: "it",
						todayBtn: "linked",
						todayHighlight: true,
						autoclose: true,
						orientation: "auto"
					});

					$("#_valore'.$fldidsso_progetto_graduatoria.'").keypress(function(e) {
						if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
						{
							e.preventDefault();
						}
					});

					$("#_valore'.$fldidsso_progetto_graduatoria.'").blur(function() {
						
						var data=$("#_valore'.$fldidsso_progetto_graduatoria.'").val();

						if(!isEmpty(data))
						{
							check_data("_valore'.$fldidsso_progetto_graduatoria.'")
						}
					});

				</script>';
			}

			$response.='</div>';

			$next_record=$db->next_record();
		}


		echo $response;
		break;
		
	case "progetto_load_dichiarazioni":
		$pidsso_ente=get_param("_idente");
		$palias_progetto=get_param("_a");

		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto_territorio WHERE idsso_comune='$pidsso_ente'");
		if (empty($fldidsso_progetto))	
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");
		}
		
		$response="";

		$response.='<div class="form-group">
						<table data-toggle="table" class="table table-hover table-condensed" >
				 			<tbody>';
		$query="SELECT *,sso_progetto_criterio.codice_inps AS cod_inps,sso_progetto_criterio.flag_obbligatorio AS obbligatorio
				FROM ".DBNAME_SS.".sso_progetto_criterio
				INNER JOIN ".DBNAME_SS.".sso_tbl_requisito on sso_progetto_criterio.idsso_tbl_requisito=sso_tbl_requisito.idsso_tbl_requisito
				WHERE idsso_progetto='$fldidsso_progetto'
				ORDER BY sso_progetto_criterio.codice_inps";
		$db->query($query);
		$res = $db->next_record();
		while($res)
		{
			$fldidsso_progetto_criterio=$db->f("idsso_progetto_criterio");
			$flddescrizione=$db->f("descrizione");

			$fldflag_obbligatorio=$db->f("obbligatorio");
			if($fldflag_obbligatorio==1)
			{
				$class_check="check_dich";
				$obbligatorio=" *";
			}
			else
			{
				$class_check="";
				$obbligatorio="";
			}

			$fldpath_file=$db->f("path_file");
			$fldfilename=$db->f("filename");

			$filename="";
			if(strpos($flddescrizione,"privacy"))
			{
				if(file_exists("../documenti/modelli/privacy".$pidsso_ente.".pdf"))
					$filename="../documenti/modelli/privacy".$pidsso_ente.".pdf";
				elseif(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
					$filename=$fldpath_file.$fldfilename;
			}
			else
			{
				if(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
					$filename=$fldpath_file.$fldfilename;
			}

			if(!empty($filename))
			{
				$flddescrizione=str_replace("<doc>",'<a href="#" onClick="Documento=window.open(\''.$filename.'\',\'Documento\',width=150,height=75); return false;">',$flddescrizione);
				$flddescrizione=str_replace("</doc>","</a>",$flddescrizione);
			}

			if(!empty($fldfilename) && file_exists($fldpath_file.$fldfilename))
			{
				$flddescrizione=str_replace("<doc>",'<a href="#" onClick="Documento=window.open(\''.$fldpath_file.$fldfilename.'\',\'Documento\',width=150,height=75); return false;">',$flddescrizione);
				$flddescrizione=str_replace("</doc>","</a>",$flddescrizione);
			}

			$flddescrizione=utf8_encode($flddescrizione);

			$fldidsso_domanda_requisito=get_db_value("SELECT idsso_domanda_requisito FROM sso_domanda_requisito WHERE idsso_domanda='$pidsso_domanda' AND idsso_tbl_servizio_criterio='$fldidsso_progetto_criterio'");
			if(empty($fldidsso_domanda_requisito))
				$fldchecked='';
			else
				$fldchecked=' checked ';

			$response.='<tr>
					<th style="vertical-align:middle" class="text-right">   
						<input type="checkbox" class="'.$class_check.'" name="check_'.$fldidsso_progetto_criterio.'" id="check_'.$fldidsso_progetto_criterio.'" '.$fldchecked.' data-toggle="toggle" data-size="mini" data-onstyle="success" data-offstyle="danger" data-on="SI" data-off="NO" '.$disabled.'>
					</th>
					<th>'.$flddescrizione.$obbligatorio.'</th>
				</tr>';

			$res = $db->next_record();
		}

		$response.="</tbody></table></div>";

		echo $response;
		break;

	case "progetto_load_documenti":
		$pidsso_ente=get_param("_idente");
		$palias_progetto=get_param("_a");

		$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto_territorio WHERE idsso_comune='$pidsso_ente'");
		if (empty($fldidsso_progetto))	
		{
			$fldidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_progetto WHERE codice_inps='$palias_progetto'");
		}

		$response="";

		$response.='<div class="form-group">
						<table data-toggle="table" class="table table-hover table-condensed" >
				 			<tbody>';
		$query="SELECT * FROM ".DBNAME_SS.".sso_progetto_documento
						WHERE idsso_progetto='$fldidsso_progetto' ".$pWhere."
						ORDER BY descrizione";
		$db->query($query);
		$res = $db->next_record();
		while($res)
		{
			$fldidsso_progetto_documento=$db->f("idsso_progetto_documento");
			$flddescrizione=$db->f("descrizione");
			$fldflag_obbligatorio=$db->f("flag_obbligatorio");
			$fldidsso_parametro_obbligatorio=$db->f("idsso_parametro_obbligatorio");
			$fldidsso_parametro_risposta_obbligatorio=$db->f("idsso_parametro_risposta_obbligatorio");
			$fldidsso_parametro_riga_obbligatorio=$db->f("idsso_parametro_riga_obbligatorio");
			$fldpath_file=$db->f("path_file");
			$fldfilename=$db->f("filename");
			$fldobbligatorio="NO";
			$class="";
			if($fldflag_obbligatorio==1)
			{
				$obbligatorio="*";
				$fldobbligatorio="SI";
				$class="file_obbligatorio";
			}
			elseif(!empty($fldidsso_parametro_obbligatorio) && !empty($fldidsso_parametro_risposta_obbligatorio))
			{
				$fldidsso_domanda_risposte=get_db_value("SELECT idsso_domanda_parametro_graduatoria FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$fldidsso_parametro_obbligatorio' AND idsso_progetto_graduatoria_risposte='$fldidsso_parametro_risposta_obbligatorio'");
				if(!empty($fldidsso_domanda_risposte))
				{
					$obbligatorio="*";
					$fldobbligatorio="SI";
					$class="file_obbligatorio";
				}
				else
				{
					$obbligatorio="";
					$fldobbligatorio="NO";
					$class="";
				}
			}
			elseif(!empty($fldidsso_parametro_obbligatorio) && !empty($fldidsso_parametro_riga_obbligatorio))
			{
				//controllo se la riga della tabella è compilata
				$riga_compilata=rigaCompilata($pidsso_domanda,$fldidsso_parametro_obbligatorio,$fldidsso_parametro_riga_obbligatorio);
				if($riga_compilata)
				{
					$obbligatorio="*";
					$fldobbligatorio="SI";
					$class="file_obbligatorio";
				}
				else
				{
					$obbligatorio="";
					$fldobbligatorio="NO";
					$class="";
				}
			}
			else
			{
				$obbligatorio="";
				$fldobbligatorio="NO";
				$class="";
			}

			/*
			$button_modifica='<button type="button" class="btn btn-primary btn-150 btn-xs" onclick="modificaDocumento(\''.$fldidsso_progetto.'\')" '.$accesso_modifica.' '.$disabled.' ><span class="glyphicon glyphicon-pencil span-padding" aria-hidden="true"></span>Allega documento</button>';
			*/

			$button_modifica='<input class="file-path validate '.$class.'" type="file" placeholder="" name="documento'.$fldidsso_progetto_documento.'" id="documento'.$fldidsso_progetto_documento.' ">';

			$response.='<tr>
						<th style="width: 50%" class="grassetto">'.$flddescrizione.$obbligatorio.'</th>
						<th style="width: 20%">'.$flddoc.'</th>
						<th style="width: 20%">'.$flddata_allegato.'</th>
						<th style="width: 10%" class="text-right">'.$button_modifica.'</th>
					</tr>';


			$res = $db->next_record();
		}

		$response.="</tbody></table></div>";

		echo $response;
		break;
	case "bonus800ras":
		$pidsso_ente=get_param("_ente");

		switch ($pidsso_ente)
		 {
			case '2':	// NUORO
				$idsso_progetto=2;
				break;
			case '3':	// OLBIA
				$idsso_progetto=1;	
				break;
			case '5':	// PORTO TORRES	
				$idsso_progetto=3;	
				break;
			case '6':	// ALGHERO
				$idsso_progetto=3;	
				break;
			case '7':	// TORTOLI'	
				$idsso_progetto=1;	
				break;
			case '8':	// BONORVA
				$idsso_progetto=1;	
				break;
			default:
				$idsso_progetto=1;	
				break;
		}	

		$idsso_progetto_istruttoria=get_db_value("select idsso_progetto_istruttoria from sso_progetto_istruttoria where idsso_progetto='$idsso_progetto' and codice_inps='BONUS800RAS01'");
		
		$fldidgen_operatore=$user;
		$fldidsso_tabella_stato_intervento=3;
		
		$fldidsso_tbl_agevolazione=3;
		$fldidsso_tabella_motivo_domanda=11;


		$pflag_ente_pubblico=1;
		$pidsso_tbl_area=20; //Casellario assistenza
		$pidsso_tbl_servizio=501;	// Contributi economici
		$fldidsso_tbl_prestazione=881;

		$pidsso_tbl_agevolazione=3;
		$pidsso_tabella_motivo_domanda=11;
		$fldquantita=1;
		$pflag_carattere=2;

		$pidsso_tabella_stato_intervento=7;	// approvato
		$fldidsso_tbl_um=UM_VALORE;
		$pidsso_tbl_erogazione=2;

		$pdata=date("Y-m-d");
		$pdata_inizio=$pdata;
		$pdata_fine=$pdata;
		//$pdata_fine=date("Y-m-d",mktime(0,0,0,date("m")+1,date("d"),date("Y")));
		
		$sSQL="select sso_domanda.idsso_domanda,sso_domanda.idutente from sso_domanda inner join sso_domanda_istruttoria on sso_domanda.idsso_domanda=sso_domanda_istruttoria.idsso_domanda where sso_domanda.idsso_ente='$pidsso_ente' and idsso_progetto_istruttoria='$idsso_progetto_istruttoria' and istruttoria_sino=1 order by sso_domanda.idsso_domanda";
		$aDOMANDE=db_fill_array($sSQL);
		$aUTENTI=array();
		foreach ($aDOMANDE as $idsso_domanda => $idutente) 
		{
			$fldidsso_tabella_stato_domanda=get_db_value("select idsso_tabella_stato_domanda from sso_domanda where idsso_domanda='$idsso_domanda'");
			if ($fldidsso_tabella_stato_domanda==5)	// Annullata
			{

			}
			else
			{
				$contributo=calcolaBONUS800RAS($idsso_domanda,$idutente,true);
				if ($contributo>0 && !in_array($idutente, $aUTENTI))
				{
					$aUTENTI[]=$idutente;
					$idsso_piano_zona=get_db_Value("select idsso_piano_zona from sso_piano_zona where idsso_comune='$pidsso_ente'");

					$sSQL="INSERT INTO sso_domanda_intervento 
						(idutente,idsso_ente,data,idgen_operatore_incarico,
						idassistente,idsso_domanda,idsso_tbl_area,
						idsso_tbl_servizio,flag_ente_pubblico,idsso_tabella_stato_intervento,
						data_inizio,data_fine,note,idsso_tabella_motivo_domanda,
						flag_carattere,idsso_tbl_agevolazione,numero_protocollo_domanda,idsso_piano_zona,
						idsso_anagrafica_isee,flag_presa_carico,idsso_tbl_targetsiuss,prestazione_previsione,previsione_saldo,idsso_tbl_erogazione)
						VALUES 
						('$idutente','$pidsso_ente','$pdata','1',
						'1','$idsso_domanda','$pidsso_tbl_area',
						'$pidsso_tbl_servizio','$pflag_ente_pubblico','$pidsso_tabella_stato_intervento',
						'".$pdata_inizio."','".$pdata_fine."','$pnote','$pidsso_tabella_motivo_domanda',
						'$pflag_carattere','$pidsso_tbl_agevolazione','$pnumero_protocollo_domanda','$pidsso_piano_zona',
						'$pidsso_anagrafica_isee','$pflag_presa_carico','$pidsso_tbl_targetsiuss','$contributo','$contributo','$pidsso_tbl_erogazione')";
					$db->query($sSQL);
					$pidsso_domanda_intervento = mysql_insert_id($db->link_id());		

					$sSQL="INSERT INTO sso_domanda_prestazione (
					idsso_domanda_intervento,idutente,idsso_tbl_um,
					idsso_tbl_prestazione,quantita,importo,
					tariffa 
					) VALUES (
					'$pidsso_domanda_intervento','$idutente','$fldidsso_tbl_um',
					'$fldidsso_tbl_prestazione','$fldquantita','$contributo',
					'$contributo')";
					$db->query($sSQL);
					$fldidsso_domanda_prestazione = mysql_insert_id($db->link_id());					

				}
			}
		}
		echo "elaborazione conclusa con successo.";

		break;
	case "verificadomandautenze":
		$pcodicefiscale=get_param("_cf");
  		$pcodicefiscale=stripslashes($pcodicefiscale);
      	$pcodicefiscale=mysql_real_escape_string($pcodicefiscale);		

      	// Controllo se ci sono altre domande
      	
      	$codice_famiglia=get_db_value("select codice_famiglia from gen_anagrafe_popolazione where codice_fiscale='$pcodicefiscale'");
      	if (!empty($codice_famiglia))
      	{
	      	$aFAMIGLIA=db_fill_array("select codice_fiscale,data_nascita from gen_anagrafe_popolazione where codice_famiglia='$codice_famiglia'");
	      	$nDOMANDE=0;
	      	$famigliaRDC=false;
	      	$oggi=date("Y-m-d");
	      	$maggiorenne=date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")-18));
	      	$nADULTI=0;
	      	$nMINORI=0;
	      	$nRDC=0;
	      	$contributo=0;
	      	foreach ($aFAMIGLIA as $codice_fiscale_famiglia => $data_nascita_famiglia) 
	      	{
	      		$idutente_famiglia=get_db_value("select idutente from sso_anagrafica_utente where codicefiscale='$codice_fiscale_famiglia'");      		
	      		$idsso_domanda_famiglia=get_db_value("select idsso_domanda from sso_domanda where idutente='$idutente_famiglia' and idsso_progetto='27'");
	      		if (!empty($idsso_domanda_famiglia))
	      			$nDOMANDE++;

	      		//Verfica RDC
				$fldidsso_inps_rdc=get_db_value("select idsso_inps_rdc from sso_inps_rdc where rdc_codicefiscale='$codice_fiscale_famiglia'");
				if (!empty($fldidsso_inps_rdc))
					$nRDC++;

				
				if ($data_nascita_famiglia<=$maggiorenne)
					$nADULTI=$nADULTI+1;
				else
					$nMINORI=$nMINORI+1;
	      	}

	      	$contributo=calcolaUTENZEMESSINA($nADULTI,$nMINORI);
	      	$contributo=number_format($contributo,2,".","");
	      	echo $nDOMANDE."|".$nRDC."|".$contributo."|".$nADULTI."|".$nMINORI."|SI";
	    }
	    else  	
	    	echo $nDOMANDE."|".$nRDC."|".$contributo."|".$nADULTI."|".$nMINORI."|NO";
		break;
	case "pdf_domanda_bandi":
		$pidsso_domanda=get_param("_iddomanda");
		$pidsso_progetto=get_db_value("SELECT idsso_progetto FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");
		$fldidutente=get_db_value("SELECT idutente FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");

		$flag_documento_previsto=false;
		$attachment="";

			$fldidgen_tbl_testo=get_db_value("SELECT idgen_tbl_testo FROM sso_progetto WHERE idsso_progetto='$pidsso_progetto'");
		if(!empty($fldidgen_tbl_testo))
		{
			$flag_documento_previsto=true;

			$request_rest = curl_init();
			curl_setopt($request_rest, CURLOPT_URL, 'https://'.$_SERVER["HTTP_HOST"].'/panel/panel_editor.php?_tableid='.$pidsso_domanda.'&_idtesto='.$fldidgen_tbl_testo.'&_progetto='.$pidsso_progetto.'&save=true&flag_curl=true&_convert=true');

			ignore_user_abort(true);

			curl_setopt($request_rest, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($request_rest, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($request_rest, CURLOPT_SSL_VERIFYHOST, true);
			curl_setopt($request_rest, CURLOPT_VERBOSE, true);
		    curl_setopt($request_rest, CURLINFO_HEADER_OUT,true);
			curl_setopt($request_rest, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($request_rest, CURLOPT_TIMEOUT_MS, 80000);
			curl_setopt($request_rest, CURLOPT_NOSIGNAL, 1);

			$result=curl_exec($request_rest);
			$httpcode = curl_getinfo($request_rest, CURLINFO_HTTP_CODE);

			if($httpcode==200)
			{
				$aFILE=json_decode($result);
				$aFILE=json_decode(json_encode($aFILE), true);
				
				$attachment=$aFILE["path"].$aFILE["filename"];
			}
		}
		

		if($flag_documento_previsto)
		{
			if(!empty($attachment))
				echo "1|".$attachment;
			else
				echo "0";
		}
		else
			echo "1";

		break;	

	case "comunicazionilette":
		$fldidutente=verifica_utente($chiave);
		echo $notifiche=sicare_comunicazioni_get_notifiche_back($fldidutente);
		break; 

	case "save_dettaglio_utenza":
		$pidsso_domanda=get_param("_iddomanda");
		$pidsso_progetto=get_param("_idprogetto");
		$ptipologia=get_param("_tipologia");
		$pimporto=db_string(get_param("_importo"));
		$pnote=db_string(get_param("_note"));

		switch($ptipologia)
		{
			case "elettrica":
				$idsso_progetto_graduatoria_importo=277;
				$idsso_progetto_graduatoria_note=283;
				break;

			case "gas":
				$idsso_progetto_graduatoria_importo=278;
				$idsso_progetto_graduatoria_note=284;
				break;

			case "bombole":
				$idsso_progetto_graduatoria_importo=279;
				$idsso_progetto_graduatoria_note=285;
				break;

			case "acqua":
				$idsso_progetto_graduatoria_importo=280;
				$idsso_progetto_graduatoria_note=286;
				break;

			case "acqua2":
				$idsso_progetto_graduatoria_importo=281;
				$idsso_progetto_graduatoria_note=287;
				break;

			case "altro":
				$idsso_progetto_graduatoria_importo=282;
				$idsso_progetto_graduatoria_note=288;
				break;
		}

		$delete="DELETE FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$idsso_progetto_graduatoria_importo'";
		$db->query($delete);

		$delete="DELETE FROM sso_domanda_parametro_graduatoria WHERE idsso_domanda='$pidsso_domanda' AND idsso_progetto_graduatoria='$idsso_progetto_graduatoria_note'";
		$db->query($delete);

		if(!empty($pimporto))
		{
			$insert="INSERT INTO ".DBNAME_SS.".sso_domanda_parametro_graduatoria(idsso_domanda,idsso_progetto_graduatoria,idsso_tabella_parametro_graduatoria,numero,risposta_testo) VALUES('$pidsso_domanda','$idsso_progetto_graduatoria_importo','0','$pimporto','$pimporto')";
			$db->query($insert);
		}

		if(!empty($pnote))
		{
			$insert="INSERT INTO ".DBNAME_SS.".sso_domanda_parametro_graduatoria(idsso_domanda,idsso_progetto_graduatoria,idsso_tabella_parametro_graduatoria,numero,risposta_testo) VALUES('$pidsso_domanda','$idsso_progetto_graduatoria_note','0','$pnote','$pnote')";
			$db->query($insert);
		}

		echo "1";

		break;
	case "equipeminore":
		$pidsso_anagrafica_allega=get_param("_k");
		$user=verifica_utente($chiave);
		$oggi=date("Y-m-d");
		$sSQL="update ".DBNAME_SS.".sso_anagrafica_allega set equipe_dataattivazione='$oggi',equipe_operatore='$user' where idsso_anagrafica_allega='$pidsso_anagrafica_allega'";
		$db->query($sSQL);
		break;	

	case "get_stato_domanda":
		$pidsso_domanda=get_param("_id");
		echo $fldidsso_tabella_stato_domanda=get_db_value("SELECT idsso_tabella_stato_domanda FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");
		break;
	case "get_stati_domanda":
		$pidsso_domanda=get_param("_id");
		$fldidsso_tabella_stato_domanda=get_db_value("SELECT idsso_tabella_stato_domanda FROM sso_domanda WHERE idsso_domanda='$pidsso_domanda'");

		$response.='<select class="form-control input-sm" style="" name="varia_stato" id="varia_stato" ><option value="" ></option>';
		$sSql="SELECT idsso_tabella_stato_domanda,descrizione FROM sso_tabella_stato_domanda ORDER BY idsso_tabella_stato_domanda";
		$db->query($sSql);
		$res = $db->next_record();
		while($res)
		{
			$idsso_tabella_stato_domanda=$db->f("idsso_tabella_stato_domanda");
			$flddescrizione=$db->f("descrizione");
			if($idsso_tabella_stato_domanda==$fldidsso_tabella_stato_domanda)
				$response.='<option value="'.$idsso_tabella_stato_domanda.'" selected>'.$flddescrizione.'</option>';
			else
				$response.='<option value="'.$idsso_tabella_stato_domanda.'">'.$flddescrizione.'</option>';
			$res = $db->next_record();
		}

		$response.='</select>';

		echo $response;
		
		break;
	case "calcolaformula":
		$pidsso_domanda=get_param("_domanda");
		$pidsso_progetto_graduatoria=get_param("_parametro");

		$graduatoria_formula=get_db_value("select graduatoria_formula from sso_progetto_graduatoria where idsso_progetto_graduatoria='$pidsso_progetto_graduatoria'");
		$pidsso_progetto=get_db_value("select idsso_progetto from sso_domanda where idsso_domanda='$pidsso_domanda'");
		$aPARAMETRI=db_fill_array("select sso_progetto_graduatoria.idsso_progetto_graduatoria,sso_progetto_graduatoria.descrizione_parametro from sso_progetto_graduatoria where idsso_progetto='$pidsso_progetto' order by sso_progetto_graduatoria.idsso_progetto_graduatoria ");

		foreach ($aPARAMETRI as $idsso_progetto_graduatoria => $descrizione) 
		{

			$valore=get_db_value("select sso_domanda_parametro_graduatoria.numero from sso_domanda_parametro_graduatoria  where idsso_progetto_graduatoria='$idsso_progetto_graduatoria' and idsso_domanda='$pidsso_domanda' order by idsso_progetto_graduatoria ");
			if (empty($valore))
				$valore=0;
			$valore=str_replace(",",".",$valore);
			$graduatoria_formula=str_replace("[valore".$idsso_progetto_graduatoria."]", $valore, $graduatoria_formula);
		}
		$risultato = eval('return '.$graduatoria_formula.';');
		$risultato=number_format($risultato,2,".","");
		echo $risultato;
		break;

	case "nesperienze_lavorative":
		$chiave=get_cookieuserFront();

		$fldidgen_utente=verifica_eutente($chiave);
		$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
		if(empty($fldidutente) || empty($fldidgen_utente))
		    die("Attenzione! sessione scaduta");

		$pidsl_curriculum=get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
		if(!empty($pidsl_curriculum))
		{
			$nESPERIENZE=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsl_curriculum'");
			if(empty($nESPERIENZE))
				echo "0";
			else
				echo "1";
		}
		else
			echo "1";
		
		break;

	case "ntitoli_studio":
		$chiave=get_cookieuserFront();

		$fldidgen_utente=verifica_eutente($chiave);
		$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
		if(empty($fldidutente) || empty($fldidgen_utente))
			die("Attenzione! sessione scaduta");

		$pidsl_curriculum=get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
		if(!empty($pidsl_curriculum))
		{
			$nTITOLI=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsl_curriculum'");
			if(empty($nTITOLI))
				echo "0";
			else
				echo "1";
		}
		else
			echo "1";
		
		break;

	case "add_lingua":
		$chiave=get_cookieuserFront();

		$fldidgen_utente=verifica_eutente($chiave);
		$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
		if(empty($fldidutente) || empty($fldidgen_utente))
		    die("Attenzione! sessione scaduta");

		$pidsl_lingue=get_param("_idlingua");
		$pflag_rating_scritto=get_param("_ratingscritto");
		$pflag_rating_parlato=get_param("_ratingparlato");

		$pidsl_curriculum=get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
		if(!empty($pidsl_curriculum))
		{
			$fldidsl_curriculum_lingue=get_db_value("SELECT idsibada_curriculum_lingue FROM sibada_curriculum_lingue WHERE idsibada_curriculum='$pidsl_curriculum' AND idsibada_lingue='$pidsl_lingue'");
			if(empty($fldidsl_curriculum_lingue))
			{
				$insert="INSERT INTO sibada_curriculum_lingue(idsibada_curriculum,idsibada_lingue,flag_rating_scritto,flag_rating_parlato) VALUES('$pidsl_curriculum','$pidsl_lingue','$pflag_rating_scritto','$pflag_rating_parlato')";
				$db->query($insert);
				$fldidsl_curriculum_lingue=mysql_insert_id($db->link_id());

				$fldlingua=get_db_value("SELECT descrizione FROM sibada_lingue WHERE idsibada_lingue='$pidsl_lingue'");

				$selected1="";
				$selected2="";
				$selected3="";
				$selected4="";
				$selected5="";

				switch($pflag_rating_scritto)
				{
					case 1:
						$selected1="selected";
						break;

					case 2:
						$selected2="selected";
						break;

					case 3:
						$selected3="selected";
						break;

					case 4:
						$selected4="selected";
						break;

					case 5:
						$selected5="selected";
						break;

					case 6:
						$selected6="selected";
						break;
				}

				$selected1_parlato="";
				$selected2_parlato="";
				$selected3_parlato="";
				$selected4_parlato="";
				$selected5_parlato="";

				switch($pflag_rating_parlato)
				{
					case 1:
						$selected1_parlato="selected";
						break;

					case 2:
						$selected2_parlato="selected";
						break;

					case 3:
						$selected3_parlato="selected";
						break;

					case 4:
						$selected4_parlato="selected";
						break;

					case 5:
						$selected5_parlato="selected";
						break;

					case 6:
						$selected6_parlato="selected";
						break;
				}

				echo '<tr id="tr_lingua'.$fldidsl_curriculum_lingue.'"><td>'.$fldlingua.'</td><td><select id="flag_rating_scritto'.$fldidsl_curriculum_lingue.'" name="flag_rating_scritto'.$fldidsl_curriculum_lingue.'" class="rating"><option value=""></option><option value="1" '.$selected1.'>A1</option><option value="2" '.$selected2.'>A2</option><option value="3" '.$selected3.'>B1</option><option value="4" '.$selected4.'>B2</option><option value="5" '.$selected5.'>C1</option><option value="6" '.$selected6.'>C2</option></select></td><td><select id="flag_rating_parlato'.$fldidsl_curriculum_lingue.'" name="flag_rating_parlato'.$fldidsl_curriculum_lingue.'" class="rating"><option value=""></option><option value="1" '.$selected1_parlato.'>A1</option><option value="2" '.$selected2_parlato.'>A2</option><option value="3" '.$selected3_parlato.'>B1</option><option value="4" '.$selected4_parlato.'>B2</option><option value="5" '.$selected5_parlato.'>C1</option><option value="6" '.$selected6_parlato.'>C2</option></select></td><td><button type="button" class="btn btn-xs btn-outline-warning" onclick="updateLINGUA('.$fldidsl_curriculum_lingue.')"><svg class="icon icon-xs icon-warning"><use xlink:href="static/img/sprite.svg#it-pencil"></use></svg>&nbsp;Modifica</button></td><td><button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteLINGUA('.$fldidsl_curriculum_lingue.')"><svg class="icon icon-xs icon-danger"><use xlink:href="static/img/sprite.svg#it-delete"></use></svg>&nbsp;Elimina</button></td></tr><script>$(".rating").barrating({
					theme: "bars-square",
					showValues: true,
					showSelectedRating: false
				});</script>';
			}
		}

		break;

	case "delete_lingua":
		$pidsl_curriculum_lingue=get_param("_idlingua");

		$delete="DELETE FROM sibada_curriculum_lingue WHERE idsibada_curriculum_lingue='$pidsl_curriculum_lingue'";
		$db->query($delete);
		
		break;

	case "cv_osservato":
		$pidsl_curriculum=get_param("_id");
		$pidsso_anagrafica_utente=get_param("_idfornitore");
		$pstato=get_param("_stato");

		switch($pstato)
		{
			case 1:
				$table="sl_curriculum_osservato";
				break;

			case 2:
				$table="sl_curriculum_contattare";
				break;

			case 3:
				$table="sl_curriculum_appuntamento";
				break;

			case 4:
				$table="sl_curriculum_noninteressato";
				break;
		}

		$delete="DELETE FROM sl_curriculum_osservato WHERE idsl_curriculum='$pidsl_curriculum' AND idsso_anagrafica_utente='$pidsso_anagrafica_utente'";
		$db->query($delete);

		$delete="DELETE FROM sl_curriculum_contattare WHERE idsl_curriculum='$pidsl_curriculum' AND idsso_anagrafica_utente='$pidsso_anagrafica_utente'";
		$db->query($delete);

		$delete="DELETE FROM sl_curriculum_appuntamento WHERE idsl_curriculum='$pidsl_curriculum' AND idsso_anagrafica_utente='$pidsso_anagrafica_utente'";
		$db->query($delete);

		$delete="DELETE FROM sl_curriculum_noninteressato WHERE idsl_curriculum='$pidsl_curriculum' AND idsso_anagrafica_utente='$pidsso_anagrafica_utente'";
		$db->query($delete);


		$insert="INSERT INTO ".$table."(idsibada_curriculum,idsso_anagrafica_utente) VALUES('$pidsl_curriculum','$pidsso_anagrafica_utente')";
		$db->query($insert);

		echo "1";
		break;

	case "get_prov":
		$pidcomune=get_param("_idcomune");
		echo $prov=get_db_value("SELECT provincia FROM ".DBNAME_A.".comune WHERE idcomune='$pidcomune'");
		break;

}

$db2->closeCONNECTION();

?>
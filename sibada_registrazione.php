<?php
include("./common.php");
include("../librerie/librerie.php");

require '../librerie/mail/class.phpmailer.php';
include("../librerie/mail/lib.mail.php");

global $db;
global $db_front;

$upload_max = ini_get('upload_max_filesize');
$upload_max_parsed = parse_size(ini_get('upload_max_filesize'));

if(get_param("_conferma"))
{
	$fldcognome=get_param("cognome");
	$fldcognome=db_string($fldcognome);
	$fldcognome=strtoupper($fldcognome);

	$fldnome=get_param("nome");
	$fldnome=db_string($fldnome);
	$fldnome=strtoupper($fldnome);

	$fldcodicefiscale=get_param("codicefiscale");
	$fldcodicefiscale=db_string($fldcodicefiscale);
	$fldcodicefiscale=strtoupper($fldcodicefiscale);

	$fldsesso=get_param("sesso");
	$fldstato=get_param("idsl_disponibilita");

	$fldidgen_nazione_nascita=get_param("idgen_nazione_nascita"); 

	$flddata_nascita=get_param("data_nascita");
	$flddata_nascita=invertidata($flddata_nascita,"-","/",1);

	$fldidgen_cittadinanza1=get_param("idgen_cittadinanza1");
	$fldidgen_cittadinanza1=db_string($fldidgen_cittadinanza1);

	$fldidgen_comune_nascita=get_param("idgen_comune_nascita");   
	if(!empty($fldidgen_comune_nascita))
	{
		$fldcomune_nascita=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
		$fldcomune_nascita=db_string($fldcomune_nascita);
		$fldcomune_nascita=strtoupper($fldcomune_nascita);
		//$fldprov_nascita=get_param("prov_nascita");   
		$fldprov_nascita=get_db_value("SELECT provincia FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune_nascita'");
		$fldprov_nascita=strtoupper($fldprov_nascita);
	}
	else
	{
		$fldcomune_nascita=get_param("comune_nascita");   
		$fldcomune_nascita=db_string($fldcomune_nascita);
		$fldcomune_nascita=strtoupper($fldcomune_nascita);
		$fldprov_nascita="EE";
	}

	$fldidgen_comune=get_param("idgen_comune");   
	if(!empty($fldidgen_comune))
	{
		$fldcomune_residenza=get_db_value("SELECT comune FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune'");
		$fldcomune_residenza=db_string($fldcomune_residenza);
		$fldcomune_residenza=strtoupper($fldcomune_residenza);

		$fldcap=get_db_value("SELECT cap FROM ".DBNAME_A.".comune WHERE idcomune='$fldidgen_comune'");
		$fldcomune_residenza=db_string($fldcomune_residenza);
		$fldcomune_residenza=strtoupper($fldcomune_residenza);
	}
	else
	{
		$fldcomune_residenza=db_string($fldcomune_residenza);
		$fldcomune_residenza=strtoupper($fldcomune_residenza);
	}

	$fldprov_residenza=get_param("prov_residenza");   
	$fldprov_residenza=db_string($fldprov_residenza);
	$fldprov_residenza=strtoupper($fldprov_residenza);

	$fldindirizzo=get_param("indirizzo");
	$fldindirizzo=db_string($fldindirizzo);
	$fldindirizzo=strtoupper($fldindirizzo);

	$fldcivico=get_param("civico");
	$fldcivico=db_string($fldcivico);
	$fldcivico=strtoupper($fldcivico);

	$fldcellulare=get_param("cellulare");

	$fldtelefono=get_param("telefono");

	$fldemail=get_param("email");
	if (!filter_var($fldemail, FILTER_VALIDATE_EMAIL))
		$alert_email=true;
  
	$flddocumento=get_param("documento");	
	$fldidgen_tbl_documento=get_param("idgen_tbl_documento");

	$flddata_documento=get_param("data_documento");
	$flddata_documento=invertidata($flddata_documento,"-","/",1);

	$fldnumero_documento=get_param("numero_documento");
	$fldnumero_documento=db_string($fldnumero_documento);
	$fldnumero_documento=strtoupper($fldnumero_documento);

	$fldarticolo76=1;
	$flddlgs196=1;
	$fldprivacy=1;

	$flddata_richiesta=date("Y-m-d");
	$fldorario_richiesta=date("H:i");

	$fldicodicefiscale_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE codicefiscale='$fldcodicefiscale'");
	if(!empty($fldicodicefiscale_registrato))
	{
		$codicefiscale_registrato=front_get_db_value("SELECT codicefiscale FROM gen_utente WHERE idgen_utente='$fldicodicefiscale_registrato'");
		$alert_codicefiscale_registrato=true;
	}

	$fldemail_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE email='$fldemail'");
	if (!empty($fldemail_registrato))
	{
		$email_registrata=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$fldemail_registrato'");
		$alert_email_registrato=true;
	}

	if(!$alert_codicefiscale_registrato && !$alert_email_registrato && !$alert_email)
	{
		$sSQL="INSERT INTO ".FRONT_ESONAME.".gen_utente 
		(cognome,nome,sesso,idgen_nazione_nascita,
		idgen_comune_nascita,comune_nascita,prov_nascita,
		data_nascita,indirizzo,civico,citta,cap,
		idgen_comune,provincia,codicefiscale,
		cellulare,telefono,data_richiesta,
		ora_richiesta,flag_beneficiario,email,documento_name,
		idgen_tbl_documento,data_documento,numero_documento,flag_abilitato,flag_sl,idsibada_disponibilita
		) values (
		'$fldcognome','$fldnome','$fldsesso','$fldidgen_nazione_nascita',
		'$fldidgen_comune_nascita','$fldcomune_nascita','$fldprov_nascita',
		'$flddata_nascita','$fldindirizzo','$fldcivico','$fldcomune_residenza','$fldcap',
		'$fldidgen_comune','$fldprov_residenza','$fldcodicefiscale',
		'$fldcellulare','$fldtelefono','$flddata_richiesta',
		'$fldorario_richiesta','1','$fldemail','$flddocumento',
		'$fldidgen_tbl_documento','$flddata_documento','$fldnumero_documento',1,1,'$fldstato')";
		$db_front->query($sSQL); 
		$pidgen_utente=mysql_insert_id($db_front->link_id());   

		$fldidsso_anagrafica_utente=get_db_value("SELECT idutente FROM ".DBNAME_SS.".sso_anagrafica_utente WHERE codicefiscale='$fldcodicefiscale'");
		if(empty($fldidsso_anagrafica_utente))
		{
			$pidsso_ente=1;
			$fldidtipo=9;
			$sSQL="INSERT INTO sso_anagrafica_utente 
			(cognome,nome,sesso,data_nascita,
			comune_nascita,idnazione,idgen_comune_nascita,
			prov_nascita,citta,cap,idamb_comune_residenza,
			prov,indirizzo,civico,codicefiscale,
			cellulare,telefono,email,
			idtipo,idsso_ente,idgen_cittadinanza1
			) values (
			'$fldcognome','$fldnome','$fldsesso','$flddata_nascita',
			'$fldcomune_nascita','$fldidgen_nazione_nascita','$fldidgen_comune_nascita',
			'$fldprov_nascita','$fldcomune_residenza','$fldcap','$fldidgen_comune',
			'$fldprov_residenza','$fldindirizzo','$fldcivico','$fldcodicefiscale',
			'$fldcellulare','$fldtelefono','$fldemail',
			'$fldidtipo','$pidsso_ente','$fldidgen_cittadinanza1')";      
			$db->query($sSQL);

			$fldidsso_anagrafica_utente=mysql_insert_id($db->link_id());
			//$fldidsso_anagrafica_utente=get_db_value("select max(idutente) from sso_anagrafica_utente");          
		}    
		else
		{
			$sSQL="UPDATE sso_anagrafica_utente SET 
			telefono='$fldtelefono', 
			cellulare='$fldcellulare',
			email='$fldemail' 
			WHERE idutente='$fldidsso_anagrafica_utente'";  
			$db->query($sSQL);
		}

		$fldidsso_anagrafica=get_db_value("SELECT idsso_anagrafica FROM ".DBNAME_SS.".sso_anagrafica WHERE idutente='$fldidsso_anagrafica_utente'");
		if(empty($fldidsso_anagrafica))
		{
			$sSQL="INSERT INTO sso_anagrafica 
			(idutente,idamb_nazione) 
			values 
			('$fldidsso_anagrafica_utente','$fldidgen_nazione_nascita')";     
			$db->query($sSQL);

			$sSQL="INSERT INTO sso_anagrafica_altro(
			idsso_anagrafica_utente,
			documento_data_rilascio,
			data_scadenza,
			idsso_tabella_condizione_soggiorno,
			documento_numero,
			idgen_comune_documento,
			idsso_tbl_documento_ente) 
			VALUES (
			'$fldidsso_anagrafica_utente',
			'$pdata_rilascio',
			'$flddata_documento',
			'$fldidgen_tbl_documento',
			'$fldnumero_documento',
			'$pidgen_comune_documento',
			'$pidsso_tbl_documento_ente'
			)";
			$db->query($sSQL);
		}
		else
		{
			$sSQL="UPDATE sso_anagrafica SET 
			idamb_nazione='$fldidgen_nazione_nascita'
			WHERE idsso_anagrafica='$fldidsso_anagrafica'"; 
			$db->query($sSQL);
		}

		$fldideso_join_anagrafica=front_get_db_value("SELECT ideso_join_anagrafica FROM eso_join_anagrafica WHERE idgen_utente='$pidgen_utente' AND idsso_anagrafica_utente='$fldidsso_anagrafica_utente'");
		if(empty($fldideso_join_anagrafica))
		{
			$sSQL="INSERT INTO ".FRONT_ESONAME.".eso_join_anagrafica (idgen_utente,idsso_anagrafica_utente) values('$pidgen_utente','$fldidsso_anagrafica_utente')";
			$db_front->query($sSQL);
		}

		$pnome_originale=basename($_FILES["documento"]["name"]);
		$pnome_originale=explode(".",$pnome_originale);
		$fldestensione=$pnome_originale[count($pnome_originale)-1];

		$fldpath="./documenti/"; 
		$fldnome_allegato_name=md5("ESO_DOCUMENTO_".$pidgen_utente.'_'.date("Ymd").date("Hi")).".".$fldestensione;
		echo $fldnome_allegato_name;

		$fldestensione=strtolower($fldestensione);

		if($fldestensione=="p7m" || $fldestensione=="pdf" || $fldestensione=="jpg" || $fldestensione=="jpeg" || $fldestensione=="png")
		{
			copy($_FILES["documento"]["tmp_name"],$fldpath.$fldnome_allegato_name); 
			if(file_exists($fldpath.$fldnome_allegato_name))
			{
				$sSQL="INSERT INTO sso_anagrafica_allega (idsso_anagrafica,idsso_tabella_allega,descrizione,data,allegato_name,flag_salva) VALUES ('$fldidsso_anagrafica_utente','11','Documento di riconoscimento','$flddata_richiesta','$fldnome_allegato_name',1)";
				$db->query($sSQL);
				$alert_file_success=true;
			}
			else
			{
				$alert_file=true;
			}
		}
		else
			$alert_nofile=true;     

		$password=generaPassword();

		$fldinvio="<br>";
		$fldoggetto="Rilascio credenziali - Sibada";
		$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
		$fldtesto.="la registrazione è avvenuta con successo. Riportiamo di seguito le credenziali per l'accesso alla piattaforma. $fldinvio $fldinvio";
		$fldtesto.="Username: <b>$fldcodicefiscale</b> $fldinvio";
		$fldtesto.="Password: <b>$password</b>";

		$aEMAIL=array();
		$aEMAIL[0]=$fldemail;
		$aEMAIL[1]=$fldoggetto;
		$aEMAIL[2]=$fldtesto;
		$aEMAIL[3]="";
		$mail_result=sendMAIL($aEMAIL);

		$oggi=date("Y-m-d");
		$adesso=date("H:i:s");

		$password=md5($password);

		$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$pidgen_utente'";
		$db_front->query($update);

		$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso,flag_registrazione) VALUES('$pidgen_utente','$oggi','$adesso','".IDPROCEDURA_SICARE."','$fldcodicefiscale','$password',0,1)";
		$db_front->query($insert);

		$sSQL="UPDATE ".FRONT_ESONAME.".gen_utente set login='$fldcodicefiscale', idtabella_stato=1, flag_abilitato=1, password='$password', data_password='$oggi' where idgen_utente='$pidgen_utente'";
		$db_front->query($sSQL);

		$sSQL="INSERT INTO ".FRONT_ESONAME.".gen_utente_profilo (idgen_utente,idgen_profilo,idgen_installazione) VALUES('$pidgen_utente',2,2)";
		$db_front->query($sSQL);

		$alert_success=true;
	}
}
else
	$fldidgen_nazione_nascita=122;

?>
<!doctype html>
<html lang="it">
    <head>
        <title>Registrazione</title>
        <?php echo get_importazioni_sibada_header(); ?>    
    </head> 
<body class="push-body" data-ng-app="ponmetroca">
    <div class="body_wrapper push_container clearfix" id="page_top">

        <?php echo get_header_sibada_out(); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">                   
								<li class="breadcrumb-item"><a href="sibada_login.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
								<li class="breadcrumb-item active" aria-current="page"><a>Registrazione</a></li>
								</ol>
							</nav>

							<?php
								$testo='<b>La registrazione è avvenuta con successo.</b><br> A breve verrà inviata una mail alla casella di posta elettronica <b>'.$fldemail.'</b> contenente i dati per accedere ai servizi.';
								if($alert_success) echo(get_alert(4,$testo));

								if($alert_mittente) echo (get_alert(0,'<b>Impossibile inviare credenziali:</b> email mittente non disponibile.'));
								if($alert_destinatario) echo (get_alert(0,'<b>Impossibile inviare credenziali:</b> email destinatario non disponibile.'));

								if($alert_codicefiscale_registrato) echo (get_alert(0,'<b>Attenzione:</b> il codice fiscale <b>'.$fldcodicefiscale.'</b> è già registrato. 
								<br>Le credenziali per l\'accesso alla piattaforma sono state inviate in fase di registrazione all\'indirizzo mail <b>'.$email_registrata.'</b>.'));

								if($alert_email_registrato) echo (get_alert(0,'<b>Attenzione:</b> l\'indirizzo mail <b>'.$email_registrata.'</b> è già registrato. 
								<br>Controllare la propria casella di posta per recuperare le credenziali per l\'accesso alla piattaforma.'));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_registrazione" style="display:none;"></div>

					<form id="registrazione" method="post" enctype="multipart/form-data" action="sibada_registrazione.php" class="form-horizontal">
						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="cognome" name="cognome" style="text-transform: uppercase;" value="<?php echo $fldcognome;?>">
									<label for="cognome">Cognome*</label>
								</div>
							</div>

							<div class="form-group col-4">
								<input type="text" class="form-control" id="nome" name="nome" style="text-transform: uppercase;" value="<?php echo $fldnome;?>">
								<label for="nome">Nome*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div >
									<input type="text" class="form-control" style="text-transform: uppercase;" id="codicefiscale" name="codicefiscale" maxlength="16" value="<?php echo $fldcodicefiscale;?>">
									<label for="codicefiscale">Codice fiscale*</label>
								</div>
							</div>
							<div class="form-group col-4">
				                      <label class="form-label active" for="idsl_disponibilita">Qualifica*</label>
				                      <select class="form-control shadow" title="Scegli il comune di residenza" id="idsl_disponibilita" name="idsl_disponibilita" data-live-search="true" data-live-search-placeholder="Cerca" onchange="changeCOMUNE(2);" >
				                        <option value=""></option>
				                        <?php
				                            $sSQL="SELECT * FROM sibada_disponibilita";
				                            $db->query($sSQL);
				                            $next_record=$db->next_record();
				                              
				                            $response=array();
				                            while($next_record)
				                            {
												$idsl_disponibilita=$db->f("idsibada_disponibilita");
												$flddescrizione=$db->f("descrizione");
				                                
				                              if($idsl_disponibilita==$fldidsibada_disponibilita) 
				                              	echo '<option value="'.$idsl_disponibilita.'" selected>'.$flddescrizione.'</option>';
				                              else
				                              	echo '<option value="'.$idsl_disponibilita.'">'.$flddescrizione.'</option>';

				                              $next_record = $db->next_record();  
				                            }
				                        ?>
				                      </select>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div class="it-datepicker-wrapper theme-dark"> 
									<input class="form-control it-date-datepicker" id="data_nascita" name="data_nascita" type="text" value="<?php echo invertidata($flddata_nascita,"/","-",2);?>">
									<label for="data_nascita">Data di nascita*</label>
								</div>
							</div>
							<div class="form-group col-4 bootstrap-select-wrapper">
								<select class="form-control input-sm" title="Scegli il sesso" name="sesso" id="sesso">
									<?php     
										if(!$fldsesso) 
											$sel1 = 'selected';

										if($fldsesso=='M')
											$sel2 = 'selected';

										if($fldsesso=='F')
											$sel3 = 'selected';

										echo "\n <option value='' ".$sel1."></option>
										\n <option value='M' ".$sel2.">M</option>
										\n <option value='F' ".$sel3.">F</option>";                 
									?>
								</select>
								<label for="sesso">Genere*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
				                    <div class="bootstrap-select-wrapper">
				                      <label for="idgen_nazione_nascita">Nazione di nascita*</label>
				                      <select title="Scegli la nazione di nascita" id="idgen_nazione_nascita" name="idgen_nazione_nascita" data-live-search="true" onChange="changeNazione()">
				                        <option value=""></option>
				                        <?php
				                            $sSQL="SELECT *FROM ".DBNAME_A.".nazione ORDER BY nazione";
				                            $db->query($sSQL);
				                            $next_record=$db->next_record();
				                              
				                            $response=array();
				                            while($next_record)
				                            {
				                              $fldidnazione=$db->f("idnazione");
				                              $fldnazione=$db->f("nazione");
				                              
				                              if($fldidnazione==$fldidgen_nazione_nascita)                
				                              	echo '<option value="'.$fldidnazione.'" selected>'.$fldnazione.'</option>';
				                              else 
				                              	echo '<option value="'.$fldidnazione.'">'.$fldnazione.'</option>';

				                              $next_record = $db->next_record();  
				                            }
				                        ?>
				                      </select>
				                    </div>
				                </div>
							</div>

							<div class="form-group col-sm-4">
			                  <div>
			                    <div class="bootstrap-select-wrapper">
			                      <label for="idgen_cittadinanza1">Nazione di nascita*</label>
			                      <select title="Scegli la cittadinanza" id="idgen_cittadinanza1" name="idgen_cittadinanza1" data-live-search="true" data-live-search-placeholder="Cerca">
			                        <option value=""></option>
			                        <?php
			                            $sSQL="SELECT *FROM ".DBNAME_A.".nazione ORDER BY nazione";
			                            $db->query($sSQL);
			                            $next_record=$db->next_record();
			                              
			                            $response=array();
			                            while($next_record)
			                            {
			                              $fldidnazione=$db->f("idnazione");
			                              $fldnazione=$db->f("nazione");
			                              $fldnazionalita=$db->f("nazionalita");
			                              
			                              if($fldidnazione==$fldidgen_cittadinanza1)                 
			                              	echo '<option value="'.$fldidnazione.'" selected>'.$fldnazionalita.' ('.$fldnazione.')</option>';
			                              else
			                              	echo '<option value="'.$fldidnazione.'">'.$fldnazionalita.' ('.$fldnazione.')</option>';

			                              $next_record = $db->next_record();  
			                            }
			                        ?>
			                      </select>
			                    </div>
			                  </div>
			                </div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
				                    <div class="bootstrap-select-wrapper" id="div_comune_nascita">
										<select title="Scegli il comune di nascita" id="idgen_comune_nascita" name="idgen_comune_nascita" data-live-search="true" data-live-search-placeholder="Cerca" onchange="changeCOMUNE(1);">
											<option value=""></option>
											<?php
											    $sSQL="SELECT * FROM ".DBNAME_A.".comune ORDER BY comune";
											    $db->query($sSQL);
											    $next_record=$db->next_record();
											      
											    $response=array();
											    while($next_record)
											    {
											      $fldidcomune=$db->f("idcomune");
											      $fldcomune=$db->f("comune");
											      $fldprovincia=$db->f("provincia");
											      
											      $value=$fldcomune.' ('.$fldprovincia.')';
											      
											      if($fldidcomune==$fldidgen_comune_nascita) 
											      	echo '<option value="'.$fldidcomune.'" selected>'.$value.'</option>';
											  	  else
											      	echo '<option value="'.$fldidcomune.'">'.$value.'</option>';

											      $next_record = $db->next_record();  
											    }
											?>
										</select>
										<label for="idgen_comune_nascita">Comune di nascita*</label>
									</div>
								</div>
								<div id="div_comune_nascita_desc" style="display:none;">
									<input type="text" class="form-control" style="text-transform: uppercase;" id="comune_nascita" name="comune_nascita" value="<?php echo $fldcomune_nascita;?>"">
									<label for="comune_nascita" id="label_comune_nascita">Comune di nascita*</label>
								</div>
							</div>
							<div class="form-group col-4" id="div_prov_nascita">
								<input type="text" class="form-control" style="text-transform: uppercase;" id="prov_nascita" name="prov_nascita" value="<?php echo $fldprov_nascita;?>" maxlength="2">
								<label for="prov_nascita">Provincia di nascita*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
				                    <div class="bootstrap-select-wrapper">
				                      <label for="idgen_comune">Comune di residenza*</label>
				                      <select title="Scegli il comune di residenza" id="idgen_comune" name="idgen_comune" data-live-search="true" data-live-search-placeholder="Cerca" onchange="changeCOMUNE(2);">
				                        <option value=""></option>
				                        <?php
				                            $sSQL="SELECT *FROM ".DBNAME_A.".comune ORDER BY comune";
				                            $db->query($sSQL);
				                            $next_record=$db->next_record();
				                              
				                            $response=array();
				                            while($next_record)
				                            {
				                              $fldidcomune_res=$db->f("idcomune");
				                              $fldcomune=$db->f("comune");
				                              $fldprovincia=$db->f("provincia");
				                              
				                              $value=$fldcomune.' ('.$fldprovincia.')';
				                                
				                              if($fldidcomune_res==$fldidgen_comune) 
				                              	echo '<option value="'.$fldidcomune_res.'" selected>'.$value.'</option>';
				                              else
				                              	echo '<option value="'.$fldidcomune_res.'">'.$value.'</option>';

				                              $next_record = $db->next_record();  
				                            }
				                        ?>
				                      </select>
				                    </div>
				                </div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" style="text-transform: uppercase;" id="prov_residenza" name="prov_residenza" value="<?php echo $fldprov_residenza;?>" maxlength="2">
								<label for="prov_residenza">Provincia di residenza*</label>
							</div>
						</div>


						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" style="text-transform: uppercase;" id="indirizzo" name="indirizzo" value="<?php echo $fldindirizzo;?>">
									<label for="indirizzo">Indirizzo di residenza*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" style="text-transform: uppercase;" id="civico" name="civico" value="<?php echo $fldcivico;?>">
								<label for="civico">Numero civico di residenza*</label>
							</div>
						</div>


						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" style="text-transform: uppercase;" id="cellulare" name="cellulare" maxlength="20" value="<?php echo $fldcellulare;?>">
									<label for="cellulare">Cellulare*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" style="text-transform: uppercase;" id="telefono" name="telefono" maxlength="20" value="<?php echo $fldtelefono;?>">
								<label for="telefono">Telefono</label>
							</div>
						</div>


						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="email" name="email" maxlength="80" value="<?php echo $fldemail;?>">
									<label for="email">Email*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="email2" name="email2" maxlength="80" value="<?php echo $fldemail; ?>">
								<label for="email2">Ripeti Email*</label>
							</div>
						</div>


						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<!--label for="">Documento di identità  in corso di validità*</label-->
									<input type="file" class="form-control" id="documento" name="documento">
									<small id="" class="form-text text-muted">Documento di identità in corso di validità</small>
								</div>
							</div>
							<div class="form-group col-4 bootstrap-select-wrapper">
								<label for="idgen_tbl_documento">Tipo di documento di identità*</label>
								<select class="form-control input-sm" name="idgen_tbl_documento" id="idgen_tbl_documento" title="Scegli il tipo di documento di identità">
									<?php
										if(!$fldidgen_tbl_documento)
											echo '<option value="" selected></option>';
										else
											echo '<option value=""></option>';

										$query="select idgen_tbl_documento, descrizione from ".DBNAME_A.".gen_tbl_documento order by 2"; 
										$db->query($query);

										$res = $db->next_record();
										while($res)
										{
		  									$idgen_tbl_documento = $db->f('idgen_tbl_documento');
		  									$flddescrizione = $db->f('descrizione');

		  									if($fldidgen_tbl_documento==$idgen_tbl_documento)
		  									 echo '\n <option value="'.$idgen_tbl_documento.'" selected >'.$flddescrizione.'</option>';
		  									else
		  									 echo '\n <option value="'.$idgen_tbl_documento.'" >'.$flddescrizione.'</option>';

		  									$res = $db->next_record();
										}
									?>
								</select>  
							</div>
						</div>


						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" style="text-transform: uppercase;" id="numero_documento" name="numero_documento" value="<?php echo $fldnumero_documento;?>">
									<label for="numero_documento">Numero documento*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input class="form-control it-date-datepicker" id="data_documento" name="data_documento" type="text" value="<?php echo invertidata($flddata_documento,"/","-",2);?>">
								<label for="data_documento">Data scadenza documento*</label>
							</div>
						</div>

						<br>

						<center>
							<h5>CHIEDE</h5> 
							<h5>Il rilascio delle CREDENZIALI per l'accesso alle procedure ad evidenza pubblica in linea dell'Ente.</h5><br>
							<h5>DICHIARA</h5> 
							<table class="container px-3 my-3" style="border-collapse: separate; border-spacing: 2em;">
								<tr>
								  <td><input type="checkbox" id="articolo76" name="articolo76" <?php if($fldarticolo76) echo 'checked'; ?> ></td>
								  <td>ai sensi e per gli effetti dell'art 47 d.P.R. 28/12/2000, n. 445 e ss.mm., di essere consapevole delle sanzioni penali richiamate dall'art. 76 d.P.R. 28/12/2000, n. 445 e ss.mm. nel caso di dichiarazioni non veritiere, di formazione o uso di atti falsi.</td>
								</tr>
								<tr>
								  <td><input type="checkbox" id="dlgs196" name="dlgs196" <?php if($flddlgs196) echo 'checked'; ?> ></td>
								  <td>Che i dati personali saranno trattati nel rispetto di quanto previsto dalle vigenti disposizioni normative e regolamentari in materia (GDPR 2016/679), esclusivamente nell'ambito del procedimento dell'istanza.</td>
								</tr>
								<tr>
								  <td><input type="checkbox" id="privacy" name="privacy" <?php if($fldprivacy) echo 'checked'; ?> ></td>
								  <td>Di aver preso visione dell'<a href="../documenti/sibada/informativa_privacy.pdf" target="_blank">informativa</a>.</td>
								</tr>
							</table>
							<button name="_conferma" id="_conferma" type="submit" class="btn btn-primary btn-lg" value="conferma" <?php if($alert_success) echo 'disabled'; ?> >Conferma</button>
						</center>
						<input type="hidden" id="flag_omocodia_cf" name="flag_omocodia_cf" value="false">
					</form>  
				</main>
			</section>

			<div class="it-example-modal">
				<div class="modal" tabindex="-1" role="dialog" id="modal_file">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Attenzione!
								</h5>
							</div>
							<div class="modal-body">
								<p>Il file che si sta cercando di caricare supera la dimensione massima consentita di <?php echo $upload_max; ?></p>
							</div>
							<div class="modal-footer">
								<button class="btn btn-outline-primary btn-sm" type="button" onclick="" data-dismiss="modal">Continua</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php echo get_footer_sibada(); ?>  
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
$(document).ready(function() {
    $('.it-date-datepicker').datepicker({
      inputFormat: ["dd/MM/yyyy"],
      outputFormat: 'dd/MM/yyyy',
    });

    $('#email2').bind("cut copy paste",function(e) {
        e.preventDefault();
    });

    $('#codicefiscale2').bind("cut copy paste",function(e) {
        e.preventDefault();
    });
});


function changeNazione()
{
	var idnazione=$("#idgen_nazione_nascita").val();
	if(idnazione=="122")
	{
		$("#div_comune_nascita_desc").hide();
		$("#div_comune_nascita").show();
		$("#idgen_comune_nascita").show();
		$("#div_prov_nascita").show();
		$("#prov_nascita").val("");
		$("#prov_nascita").show();
	}
	else
	{
		$("#div_comune_nascita_desc").show();
		$("#div_comune_nascita").hide();
		$("#idgen_comune_nascita").hide();
		$("#div_prov_nascita").hide();
		$("#prov_nascita").val("EE");
		$("#prov_nascita").hide();
	}
}

function changeCOMUNE(type)
{	
	if(type=="1")
	{
		var idcomune=$("#idgen_comune_nascita").val();
		var obj_prov="prov_nascita";
	}
	else if(type=="2")
	{
		var idcomune=$("#idgen_comune").val();
		var obj_prov="prov_residenza";
	}

	if(idcomune!='' && idcomune!='0' && idcomune!=0)
	{
		var page="sibada_action.php";
		var params="_user=<?php echo $chiave;?>&_action=get_prov&_idcomune="+idcomune;
		var loader = dhtmlxAjax.postSync(page,params);  
		myParam=loader.xmlDoc.responseText;
		$("#"+obj_prov).trigger("click");
		$("#"+obj_prov).val(myParam)
	}
}

$('#documento').bind('change', function() {
	var size_file=this.files[0].size;

	var max_upload=parseInt('<?php echo $upload_max_parsed; ?>');

	if(size_file>=max_upload)
	{
		$('#documento').val("");
		$('#modal_file').modal('show');
		return false;
	}
});

$("#registrazione").submit(function(event) {

	var errors=0;
	var string_errors="";

	var cognome=$("#cognome").val();
	if(cognome=="")
	{
		string_errors=string_errors+"- Cognome; <br>"
		errors++;
	} 

	var nome=$("#nome").val();
	if(nome=="")
	{
		string_errors=string_errors+"- Nome; <br>"
		errors++;
	}

	var codicefiscale=$("#codicefiscale").val();
	if(codicefiscale=="")
	{
		string_errors=string_errors+"- Codice Fiscale; <br>"
		errors++;
	}

	var codicefiscale2=$("#codicefiscale2").val();
	if(codicefiscale2=="")
	{
		string_errors=string_errors+"- Ripetere il Codice Fiscale; <br>"
		errors++;
	}


	var idgen_cittadinanza1=$("#idgen_cittadinanza1").val();  
	if(idgen_cittadinanza1=="")
	{
		string_errors=string_errors+"- Cittadinanza; <br>"
		errors++;
	}

	var sesso=$("#sesso").val();
	if(sesso=="")
	{
		string_errors=string_errors+"- Sesso; <br>"
		errors++;
	}

	var idamb_nazione=$("#idgen_nazione_nascita").val();  
	if(idamb_nazione=="")
	{
		string_errors=string_errors+"- Nazione di nascita; <br>"
		errors++;
	}

	var data_nascita=$("#data_nascita").val();
	if(data_nascita=="")
	{
		string_errors=string_errors+"- Data di nascita; <br>"
		errors++;
	}

	if(idamb_nazione==122 || idamb_nazione=="122")
	{
		var idgen_comune_nascita=$("#idgen_comune_nascita").val();  
		if(idgen_comune_nascita=="")
		{
			string_errors=string_errors+"- Comune di nascita; <br>"
			errors++;
		}

		var provincia_nascita=$("#prov_nascita").val(); 
		if(provincia_nascita=="")
		{
			string_errors=string_errors+"- Provincia di nascita; <br>"
			errors++;
		}
	}
	else
	{
		var idgen_comune_nascita='';
		var comune_nascita=$("#comune_nascita").val();  
		if(comune_nascita=="")
		{
			string_errors=string_errors+"- Comune di nascita; <br>"
			errors++;
		}
	}

	var indirizzo=$("#indirizzo").val();  
	if(indirizzo=="")
	{
		string_errors=string_errors+"- Indirizzo di residenza; <br>"
		errors++;
	}

	var civico=$("#civico").val();  
	if(civico=="")
	{
		string_errors=string_errors+"- Civico di residenza; <br>"
		errors++;
	}

	var provincia_residenza=$("#prov_residenza").val(); 
	if(provincia_residenza=="")
	{
		string_errors=string_errors+"- Provincia di residenza; <br>"
		errors++;
	}

	var cellulare=$("#cellulare").val();  
	if(cellulare=="")
	{
		string_errors=string_errors+"- Cellulare; <br>"
		errors++;
	}

	var comune_residenza=$("#comune_residenza").val();  
	if(comune_residenza=="")
	{
		string_errors=string_errors+"- Comune di residenza; <br>"
		errors++;
	}

	var email=$("#email").val();  
	if(email=="")
	{
		string_errors=string_errors+"- Indirizzo email; <br>"
		errors++;
	}

	var email2=$("#email2").val();  
	if(email2=="")
	{
		string_errors=string_errors+"- Ripetere l'indirizzo email; <br>"
		errors++;
	}

	var nome_file=document.getElementById("documento").value;
	if(nome_file=="")
	{
		string_errors=string_errors+"- Documento di riconoscimento; <br>"
		errors++;
	}
	else
	{
		var ext=nome_file.substr(nome_file.length - 4);
		ext=ext.toLowerCase();
		if(ext!=".p7m" && ext!=".pdf" && ext!=".jpg" && ext!=".png")
		{
			string_errors=string_errors+"- Estensione documento di riconoscimento non valida; <br>"
			errors++;
		}
	}

	var data_documento=$("#data_documento").val();
	if(data_documento=="")
	{
		string_errors=string_errors+"- Data scadenza documento di riconoscimento; <br>"
		errors++;
	} 

	var numero_documento=$("#numero_documento").val();
	if(numero_documento=="")
	{
		string_errors=string_errors+"- Numero documento di riconoscimento; <br>"
		errors++;
	}     

	if(!$('#articolo76').is(":checked") || !$('#dlgs196').is(":checked") || !$('#privacy').is(":checked"))
	{
		string_errors=string_errors+"- Tutte le dichiarazioni sono obbligatorie; <br>"
		errors++;
	}

	if(errors==0)
	{
		var flag_email=false;
		var flag_codicefiscale=false;

		if($("#email").val().toLowerCase() !== $("#email2").val().toLowerCase())
		{
			flag_email=true;
		}

		if($("#codicefiscale").val().length!=16 || ($("#codicefiscale").val().toLowerCase() !== $("#codicefiscale2").val().toLowerCase()))
		{
			flag_codicefiscale=true;
		}

		var cognome=$("#cognome").val();
		var nome=$("#nome").val();
		var codicefiscale=$("#codicefiscale").val();
		var sesso=$("#sesso").val();
		var idgen_comune_nascita=$("#idgen_comune_nascita").val();
		var idamb_nazione=$("#idgen_nazione_nascita").val();
		var data_nascita=$("#data_nascita").val();

		var page="sibada_action.php";
		var params="_user=<?php echo $chiave;?>&_action=calcola_cf&cognome="+cognome+"&nome="+nome+"&sesso="+sesso+"&idgen_comune_nascita="+idgen_comune_nascita+"&idamb_nazione="+idamb_nazione+"&data_nascita="+data_nascita;
		var loader = dhtmlxAjax.postSync(page,params);  
		myParam=loader.xmlDoc.responseText; 

		var result=myParam.split("|");
		if(result[0]=="0")
		{
			var string_errors='Il sistema ha elaborato un codice fiscale diverso da quello inserito. <br>Controllare i dati immessi, quindi premere nuovamente \'Conferma\'.';
			visualizzaAlert(string_errors);
			return false;
		}
		else
		{
			if(codicefiscale.toUpperCase()!=result[1].toUpperCase())
			{
				var flag_omocodia_cf=$("#flag_omocodia_cf").val();

				if(flag_omocodia_cf=="false")
				{
					var string_error_cf='- Codice Fiscale(errori formali); <button name="btn_assegnato" id="btn_assegnato" type="button" onClick="check_assegnato(\''+codicefiscale+'\',\''+result[1]+'\')" class="btn btn-success btn-xs" style="width: 180px;"><span><i class="fa fa-cog"></i>&nbsp;Assegnato da Ag. Entrate</span></button><br>';
				  
					visualizzaAlert(string_error_cf);
					return false;
				}
			}
		}

		if(flag_email)
		{
			visualizzaAlert("<b>Attenzione</b> gli indirizzi email inseriti non coincidono.");
			return false;
		}
		else if(flag_codicefiscale)
		{
			visualizzaAlert("<b>Attenzione</b> i Codici Fiscali inseriti non coincidono.");
			return false;
		}
	}
	else
	{
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}
});


function visualizzaAlert(alert_message)
{
	$("#alert_registrazione").show();
	$("#alert_registrazione").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}





function omocodia(char_cf,char_cf_temp)
{
  /*
  0 = L 4 = Q 8 = U
  1 = M 5 = R 9 = V
  2 = N 6 = S  
  3 = P 7 = T
  */
  console.log(char_cf+" "+char_cf_temp)

  switch(char_cf_temp)
  {
    case "0":
      if(char_cf=="L" || char_cf==char_cf_temp)
        return true;
    break;

    case "1":
      if(char_cf=="M" || char_cf==char_cf_temp)
        return true;
    break;

    case "2":
      if(char_cf=="N" || char_cf==char_cf_temp)
        return true;
    break;

    case "3":
      if(char_cf=="P" || char_cf==char_cf_temp)
        return true;
    break;

    case "4":
      if(char_cf=="Q" || char_cf==char_cf_temp)
        return true;
    break;

    case "5":
      if(char_cf=="R" || char_cf==char_cf_temp)
        return true;
    break;

    case "6":
      if(char_cf=="S" || char_cf==char_cf_temp)
        return true;
    break;

    case "7":
      if(char_cf=="T" || char_cf==char_cf_temp)
        return true;
    break;

    case "8":
      if(char_cf=="U" || char_cf==char_cf_temp)
        return true;
    break;

    case "9":
      if(char_cf=="V" || char_cf==char_cf_temp)
        return true;
    break;

    default:
      console.log("sono qui")
    break;  
  }

  return false;
}

</script>

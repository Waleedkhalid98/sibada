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
	$pidsso_ente = 1; 

	$pnominativo_rl = get_param("nominativo_rl"); 
	$pnominativo_rl = db_string($pnominativo_rl);
	$pnominativo_rl = strtoupper($pnominativo_rl);

	$pcodicefiscale_rl = get_param("codicefiscale_rl");
	$pcodicefiscale_rl = db_string($pcodicefiscale_rl);
	$pcodicefiscale_rl=strtoupper($pcodicefiscale_rl);

	$pcomune_nascita_rl = get_param("comune_nascita_rl");
	$pcomune_nascita_rl = db_string($pcomune_nascita_rl);
	$pcomune_nascita_rl=strtoupper($pcomune_nascita_rl);

	$pdata_nascita_rl = get_param("data_nascita_rl");
	$pdata_nascita_rl = invertidata($pdata_nascita_rl,"-","/",1);

	$pcomune_residenza_rl=get_param("comune_residenza_rl");
	$pcomune_residenza_rl = db_string($pcomune_residenza_rl);
	$pcomune_residenza_rl=strtoupper($pcomune_residenza_rl);

	$pindirizzo_rl=get_param("indirizzo_rl");
	$pindirizzo_rl = db_string($pindirizzo_rl);
	$pindirizzo_rl=strtoupper($pindirizzo_rl);

	$ptelefono_rl=get_param("telefono_rl");
	$ptelefono_rl = db_string($ptelefono_rl);

	$pemail_rl=get_param("email_rl");
	$pemail_rl = db_string($pemail_rl);

	$ppec_rl=get_param("pec_rl");
	$ppec_rl = db_string($ppec_rl);

	$pidgen_tbl_documento=get_param("idgen_tbl_documento");
	
	$pnumero_documento=get_param("numero_documento");
	$pnumero_documento = db_string($pnumero_documento);
	$pnumero_documento=strtoupper($pnumero_documento);
	
	$pdata_documento=get_param("data_documento");
	$pdata_documento = db_string($pdata_documento);
	$pdata_documento = invertidata($pdata_documento,"-","/",1);

	$pqualita_rl=get_param("qualita_rl");

	$pidsso_tabella_tipologia_ente=get_param("idsso_tabella_tipologia_ente");

	$pcognome = get_param("cognome"); 
	$pcognome = db_string($pcognome);

	$pcitta = get_param("citta"); 
	$pcitta = db_string($pcitta);
	$pcitta=strtoupper($pcitta);

	$pindirizzo = get_param("indirizzo"); 
	$pindirizzo = db_string($pindirizzo);
	$pindirizzo=strtoupper($pindirizzo);

	$ppiva = get_param("piva"); 
	$ppiva = db_string($ppiva);

	$pcodicefiscale = get_param("codicefiscale"); 
	$pcodicefiscale = db_string($pcodicefiscale);

	$pluogo_ccia = get_param("luogo_ccia"); 
	$pluogo_ccia = db_string($pluogo_ccia);
	$pluogo_ccia=strtoupper($pluogo_ccia);

	$pnumero_ccia = get_param("numero_ccia"); 
	$pnumero_ccia = db_string($pnumero_ccia);
	$pnumero_ccia=strtoupper($pnumero_ccia);

	$pdata_ccia = get_param("data_ccia"); 
	$pdata_ccia = invertidata($pdata_ccia,"-","/",1);

	$pattivita_ccia = get_param("attivita_ccia"); 
	$pattivita_ccia = db_string($pattivita_ccia);
	$pattivita_ccia=strtoupper($pattivita_ccia);

	$ptelefono = get_param("telefono"); 
	$ptelefono = db_string($ptelefono);

	$pemail = get_param("email"); 
	$pemail = db_string($pemail);

	$ppec = get_param("pec"); 
	$ppec = db_string($ppec);

	$psettore = get_param("settore"); 
	$psettore = db_string($psettore);

	$pdescrizione_attivita = get_param("descrizione_attivita"); 
	$pdescrizione_attivita = db_string($pdescrizione_attivita);

	$fldarticolo76=1;
	$flddlgs196=1;
	$fldprivacy=1;

	$alert_codicefiscale_registrato=false;
	$alert_piva_registrato=false;
	$alert_email_registrato=false;
	$alert_pec_registrato=false;

	$fldicodicefiscale_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE codicefiscale='$pcodicefiscale'");
	$fldpiva_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE piva='$ppiva'");
	$fldemail_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE email='$pemail'");

	if(!empty($ppec))
		$fldemail_pec_registrato=front_get_db_value("SELECT idgen_utente FROM gen_utente WHERE email_pec='$ppec'");
	else
		$fldemail_pec_registrato=null;

	if(!empty($fldicodicefiscale_registrato))
	{
		$codicefiscale_registrato=front_get_db_value("SELECT codicefiscale FROM gen_utente WHERE idgen_utente='$fldicodicefiscale_registrato'");
		$alert_codicefiscale_registrato=true;
	}
	elseif(!empty($fldpiva_registrato))
	{
		$piva_registrata=front_get_db_value("SELECT piva FROM gen_utente WHERE idgen_utente='$fldpiva_registrato'");
		$alert_piva_registrato=true;
	}
	elseif(!empty($fldemail_registrato))
	{
		$email_registrata=front_get_db_value("SELECT email FROM gen_utente WHERE idgen_utente='$fldemail_registrato'");
		$alert_piva_registrato=true;
	}
	elseif(!empty($fldemail_pec_registrato))
	{
		$pec_registrata=front_get_db_value("SELECT email_pec FROM gen_utente WHERE idgen_utente='$fldemail_pec_registrato'");
		$alert_pec_registrato=true;
	}

	if(!$alert_codicefiscale_registrato && !$alert_piva_registrato && !$alert_email_registrato && !$alert_pec_registrato)
	{
		$flddata_richiesta=date("Y-m-d");
		$fldorario_richiesta=date("H:i:s");

		$sSQL = "INSERT INTO ".FRONT_ESONAME.".gen_utente (
		  idsso_tabella_tipologia_ente,
		  cognome,
		  indirizzo,
		  citta,
		  codicefiscale,
		  piva,
		  telefono,
		  data_richiesta,
		  ora_richiesta,
		  email_pec,
		  email,
		  luogo_ccia,
		  numero_ccia,
		  data_ccia,
		  attivita_ccia,
		  iban,
		  nominativo_rl,
		  comune_nascita_rl,
		  data_nascita_rl,
		  codicefiscale_rl,
		  comune_residenza_rl,
		  indirizzo_rl,
		  telefono_rl,
		  qualita_rl,
		  email_rl,
		  pec_rl,
		  flag_beneficiario,
		  settore,
		  descrizione_attivita,
		  flag_sl
		) 
		VALUES (
		  '$pidsso_tabella_tipologia_ente',
		  '$pcognome',
		  '$pindirizzo',
		  '$pcitta',
		  '$pcodicefiscale',
		  '$ppiva',
		  '$ptelefono',
		  '$flddata_richiesta',
		  '$fldorario_richiesta',
		  '$ppec',
		  '$pemail',
		  '$pluogo_ccia',
		  '$pnumero_ccia',
		  '$pdata_ccia',
		  '$pattivita_ccia',
		  '$piban',
		  '$pnominativo_rl',
		  '$pcomune_nascita_rl',
		  '$pdata_nascita_rl',
		  '$pcodicefiscale_rl',
		  '$pcomune_residenza_rl',
		  '$pindirizzo_rl',
		  '$ptelefono_rl',
		  '$pqualita_rl',
		  '$pemail_rl',
		  '$ppec_rl',
		  '0',
		  '$psettore',
		  '$pdescrizione_attivita',
		  '1')";
		$db_front->query($sSQL); 
		$fldidgen_utente=mysql_insert_id($db_front->link_id());

		if(!empty($pcodicefiscale))
			$username=$pcodicefiscale;
		else
			$username=$ppiva;

		$sSQL="UPDATE ".FRONT_ESONAME.".gen_utente SET login='$username' WHERE idgen_utente='$fldidgen_utente'";
		$db_front->query($sSQL);

		$pnome_originale=basename($_FILES["documento"]["name"]);
		$pnome_originale = explode(".",$pnome_originale);
		$fldestensione = $pnome_originale[count($pnome_originale)-1];
		$fldpath="../documenti/sibada/anagrafica/";  
		$fldnome_allegato_name=md5("ESO_DOCUMENTO_".$fldidgen_utente.'_'.date("Ymd").date("Hi")).".".$fldestensione;
    
		copy($_FILES["documento"]["tmp_name"],$fldpath.$fldnome_allegato_name); 
		if(file_exists($fldpath.$fldnome_allegato_name))
		{
			$sSQL="UPDATE ".FRONT_ESONAME.".gen_utente SET documento_name='$fldnome_allegato_name' WHERE idgen_utente='$fldidgen_utente'";
			$db_front->query($sSQL);

			$alert_file_success = true;
		}
		else
			$alert_file=true;

		$fldidsso_anagrafica_utente=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE codicefiscale='$pcodicefiscale'");
		if(empty($fldidsso_anagrafica_utente))
			$fldidsso_anagrafica_utente=get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE piva='$ppiva'");

		if (empty($fldidsso_anagrafica_utente))
		{
			$fldidtipo=103;
			$sSQL="INSERT INTO sso_anagrafica_utente (
			cognome,
			citta,
			indirizzo,
			codicefiscale,
			piva,
			telefono,
			email_pec,
			email,
			idtipo,
			idsso_ente
			) VALUES(
			'$pcognome',
			'$pcitta',
			'$pindirizzo',
			'$pcodicefiscale',
			'$ppiva',
			'$ptelefono',
			'$ppec',
			'$pemail',
			'$fldidtipo',
			'1'
			)";

			$db->query($sSQL);
			$fldidsso_anagrafica_utente=mysql_insert_id($db->link_id());
		}

		$fldidsso_ente_servizio=get_db_value("SELECT idsso_ente_servizio FROM sso_ente_servizio WHERE idutente='$fldidsso_anagrafica_utente'");
		if(!$fldidsso_ente_servizio)
		{ 
		  $sSQL="INSERT INTO sso_ente_servizio (
		    idutente,
		    idsso_tabella_tipologia_ente,
		    nominativo_rl,
		    codicefiscale_rl,
		    comune_nascita_rl,
		    comune_residenza_rl,
		    indirizzo_rl,
		    telefono_rl,
		    idsso_tbl_qualifica_rl,
		    data_nascita_rl,
		    email_rl,
		    pec_rl,
		    iban,
		    luogo_ccia,
		    numero_ccia,
		    data_ccia,
		    attivita_ccia,
		    settore,
		    descrizione_attivita
		  ) VALUES(
		    '$fldidsso_anagrafica_utente',
		    '$pidsso_tabella_tipologia_ente',
		    '$pnominativo_rl',
		    '$pcodicefiscale_rl',
		    '$pcomune_nascita_rl',
		    '$pcomune_residenza_rl',
		    '$pindirizzo_rl',
		    '$ptelefono_rl',
		    '$pqualita_rl',
		    '$pdata_nascita_rl',
		    '$pemail_rl',
		    '$ppec_rl',
		    '$piban',
		    '$pluogo_ccia',
		    '$pnumero_ccia',
		    '$pdata_ccia',
		    '$pattivita_ccia',
		    '$psettore',
		    '$pdescrizione_attivita'
		  )";
		  $db->query($sSQL);
		  $fldidsso_ente_servizio=mysql_insert_id($db->link_id());
		}

		$fldideso_join_anagrafica=front_get_db_value("SELECT ideso_join_anagrafica FROM ".FRONT_ESONAME.".eso_join_anagrafica WHERE idgen_utente='$fldidgen_utente' AND idsso_anagrafica_utente='$fldidsso_anagrafica_utente'");
		if (!$fldideso_join_anagrafica)
		{
			$sSQL="INSERT INTO ".FRONT_ESONAME.".eso_join_anagrafica (idgen_utente,idsso_anagrafica_utente) VALUES('$fldidgen_utente','$fldidsso_anagrafica_utente')";
			$db_front->query($sSQL);
		}

		$password=generaPassword();

		$fldinvio="<br>";
		$fldoggetto="Rilascio credenziali - Sibada";
		$fldtesto="Gentile $pnominativo_rl, $fldinvio";
		$fldtesto.="la registrazione è avvenuta con successo. Riportiamo di seguito le credenziali per l'accesso alla piattaforma. $fldinvio $fldinvio";
		$fldtesto.="Riepilogo dei dati della registrazione $fldinvio";
		$fldtesto.="Username: <b>$username</b> $fldinvio";
		$fldtesto.="Password: <b>$password</b>";

		$aEMAIL=array();
		$aEMAIL[0]=$pemail;
		$aEMAIL[1]=$fldoggetto;
		$aEMAIL[2]=$fldtesto;
		$aEMAIL[3]="";

		$mail_result=sendMAIL($aEMAIL);

		$oggi=date("Y-m-d");
		$adesso=date("H:i:s");

		$password=md5($password);

		$update="UPDATE ".FRONT_ESONAME.".gen_password_temp SET flag_accesso=1 WHERE idgen_utente='$fldidgen_utente'";
		$db_front->query($update);

		$insert="INSERT INTO ".FRONT_ESONAME.".gen_password_temp(idgen_utente,data_richiesta,ora_richiesta,procedura,login,password_temp,flag_accesso,flag_registrazione) VALUES('$fldidgen_utente','$oggi','$adesso','".IDPROCEDURA_SICARE."','$pcodicefiscale','$password',0,1)";
		$db_front->query($insert);

		$sSQL="UPDATE ".FRONT_ESONAME.".gen_utente set idtabella_stato=1, flag_abilitato=1, password='$password', data_password='$oggi' where idgen_utente='$fldidgen_utente'";
		$db_front->query($sSQL);

		$sSQL="INSERT INTO ".FRONT_ESONAME.".gen_utente_profilo (idgen_utente,idgen_profilo,idgen_installazione) VALUES('$fldidgen_utente',2,2)";
		$db_front->query($sSQL);

		$alert_success=true;
	}
}
?>
<!DOCTYPE html>
<html lang="it" style="background: #FFFFFF;">
<head>
	<title>Registrazione Ditta</title>
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
			                      <li class="breadcrumb-item active" aria-current="page"><a>Registrazione Ditta</a></li>
			                      <?php
									$testo='<b>La registrazione è avvenuta con successo.</b><br> A breve verrà inviata una mail alla casella di posta elettronica <b>'.$fldemail.'</b> contenente i dati per accedere ai servizi.';
									if($alert_success) echo(get_alert(4,$testo));

									if($alert_mittente) echo (get_alert(0,'<b>Impossibile inviare credenziali:</b> email mittente non disponibile.'));
									if($alert_destinatario) echo (get_alert(0,'<b>Impossibile inviare credenziali:</b> email destinatario non disponibile.'));

									if($alert_codicefiscale_registrato) echo (get_alert(0,'<b>Attenzione:</b> il codice fiscale <b>'.$fldcodicefiscale.'</b> è già registrato. 
									<br>Le credenziali per l\'accesso alla piattaforma sono state inviate in fase di registrazione all\'indirizzo mail <b>'.$email_registrata.'</b>.'));

									if($alert_email_registrato) echo (get_alert(0,'<b>Attenzione:</b> l\'indirizzo mail <b>'.$email_registrata.'</b> è già registrato.<br>Controllare la propria casella di posta per recuperare le credenziali per l\'accesso alla piattaforma.'));
			                      ?>
			                  </ol>
			              </nav>
			          </div>
			      </div>
			  </div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<div id="alert_registrazione" style="display:none;"></div>
					<form id="registrazione" method="post" enctype="multipart/form-data" action="sibada_registrazione_ditte.php" class="form-horizontal">

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="nominativo_rl" name="nominativo_rl" style="text-transform: uppercase;" value="<?php echo $pnominativo_rl;?>">
									<label for="nominativo_rl">Il/la sottoscritto/a*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="codicefiscale_rl" name="codicefiscale_rl" style="text-transform: uppercase;" value="<?php echo $pcodicefiscale_rl;?>">
								<label for="codicefiscale_rl">Codice Fiscale*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="comune_nascita_rl" name="comune_nascita_rl"  maxlength="16" value="<?php echo $pcomune_nascita_rl;?>" style="text-transform: uppercase;">
									<label for="comune_nascita_rl">Nato/a a*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="data_nascita_rl" name="data_nascita_rl"  maxlength="16" value="<?php echo invertidata($pdata_nascita_rl,"/","-",2);?>">
								<label for="data_nascita_rl">Nato/a il*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div class="it-datepicker-wrapper theme-dark"> 
									<input class="form-control it-date-datepicker" id="comune_residenza_rl" name="comune_residenza_rl" type="text" value="<?php echo $pcomune_residenza_rl; ?>" style="text-transform: uppercase;">
									<label for="comune_residenza_rl">Residente in*</label>
								</div>
							</div>
							<div class="form-group col-sm-4">
								<div class="it-datepicker-wrapper theme-dark"> 
									<input class="form-control it-date-datepicker" id="indirizzo_rl" name="indirizzo_rl" type="text" value="<?php echo $pindirizzo_rl; ?>" style="text-transform: uppercase;">
									<label for="indirizzo_rl">Indirizzo di residenza*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" id="telefono_rl" name="telefono_rl" value="<?php echo $ptelefono_rl; ?>">
									<label for="telefono_rl">Cellulare*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<input type="text" class="form-control" id="email_rl" name="email_rl" value="<?php echo $pemail_rl;?>">
								<label for="email_rl">E-mail*</label>
							</div>
							<div class="form-group col-4">
								<div>
									<input type="text" class="form-control" id="pec_rl" name="pec_rl" value="<?php echo $ppec_rl; ?>">
									<label for="pec_rl">PEC (facoltativa)</label>
								</div>
							</div>  
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="file" class="form-control" id="documento" name="documento">
									<small id="" class="form-text text-muted">Documento di identità in corso di validità</small>
								</div>
							</div>
							<div class="form-group col-4 bootstrap-select-wrapper">
								<label for="idgen_tbl_documento">Tipo di documento di identita'*</label>
								<select class="form-control input-sm" name="idgen_tbl_documento" id="idgen_tbl_documento">
								<?php
								$query="select idgen_tbl_documento, descrizione from ".DBNAME_A.".gen_tbl_documento order by 2"; 
								$db->query($query);
								$res = $db->next_record();
								while($res)
								{
									$idgen_tbl_documento = $db->f('idgen_tbl_documento');
									$flddescrizione = $db->f('descrizione');

									if($pidgen_tbl_documento==$idgen_tbl_documento)
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
									<input type="text" class="form-control" id="numero_documento" name="numero_documento" style="text-transform: uppercase;" value="<?php echo $pnumero_documento;?>">
									<label for="numero_documento">Numero documento*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input class="form-control it-date-datepicker" id="data_documento" name="data_documento" type="text" value="<?php echo invertidata($pdata_documento,"/","-",2);?>">
								<label for="data_documento">Data scadenza documento*</label>
							</div>
						</div>

						<br>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<label for="" class="col-sm-1 col-sm-offset-2 control-label">In qualità di:*</label>
								<div class="col-sm-12 offset-sm-4" align="left">
									<input type="radio" name="qualita_rl" id="qualita_rl" value="1" <?php if($pqualita_rl==1) echo 'checked'; ?> <?php echo $disabled; ?>> Titolare   <br>
									<input type="radio" name="qualita_rl" id="qualita_rl" value="2" <?php if($pqualita_rl==2) echo 'checked'; ?> <?php echo $disabled; ?>> Legale rappresentante<br> 
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-8 offset-sm-2">
								<center><i class="text-primary">dell'azienda (Denominazione)*</i></center>
								<input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo $pcognome;?>">
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
				                <div class="bootstrap-select-wrapper">
									<label for="idsso_tabella_tipologia_ente">Natura giuridica*</label>
									<select class="form-control" name="idsso_tabella_tipologia_ente" id="idsso_tabella_tipologia_ente">
										<option value=""></option>
										<?php
											$query="SELECT idsso_tabella_tipologia_ente, descrizione  FROM sso_tabella_tipologia_ente WHERE idsso_tabella_tipologia_ente!=23 ORDER BY descrizione"; 
											$db->query($query);
											
											$res = $db->next_record();
											while($res)
											{
												$idsso_tabella_tipologia_ente = $db->f("idsso_tabella_tipologia_ente");
												$descrizione = $db->f("descrizione");

												if($pidsso_tabella_tipologia_ente==$idsso_tabella_tipologia_ente)
													echo "\n <option name='$idsso_tabella_tipologia_ente' id='$idsso_tabella_tipologia_ente' value='$idsso_tabella_tipologia_ente' selected >$descrizione</option>";
												else
													echo "\n <option name='$idsso_tabella_tipologia_ente' id='$idsso_tabella_tipologia_ente' value='$idsso_tabella_tipologia_ente' >$descrizione</option>";

												$res = $db->next_record();
											}
										?>
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="citta" name="citta" value="<?php echo $pcitta;?>" style="text-transform: uppercase;">
									<label for="citta">Con sede legale in*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="indirizzo" name="indirizzo" value="<?php echo $pindirizzo;?>" style="text-transform: uppercase;">
								<label for="indirizzo">Indirizzo*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div >
									<input type="text" class="form-control" id="piva" name="piva" value="<?php echo $ppiva;?>">
									<label for="piva">Partita IVA*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="codicefiscale" name="codicefiscale" value="<?php echo $pcodicefiscale;?>">
								<label for="codicefiscale">Codice Fiscale*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div >
									<input type="text" class="form-control" id="luogo_ccia" name="luogo_ccia" value="<?php echo $pluogo_ccia;?>" style="text-transform: uppercase;">
									<label for="luogo_ccia">Iscritta presso la C.C.I.A. di*</label>
								</div>
							</div>
							<div class="form-group col-4">
								<input type="text" class="form-control" id="numero_ccia" name="numero_ccia" value="<?php echo $pnumero_ccia;?>" style="text-transform: uppercase;">
								<label for="numero_ccia">Numero iscrizione C.C.I.A.*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="data_ccia" name="data_ccia" value="<?php invertidata($pdata_ccia,"/","-",2);?>">
									<label for="data_ccia">Data iscrizione C.C.I.A.*</label>
								</div>
							</div>
								<div class="form-group col-4">
								<input type="text" class="form-control" id="attivita_ccia" name="attivita_ccia" value="<?php echo $pattivita_ccia;?>" style="text-transform: uppercase;">
								<label for="attivita_ccia">Attività iscrizione C.C.I.A.*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $ptelefono;?>">
									<label for="telefono">Telefono*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<input type="text" class="form-control" id="email" name="email" value="<?php echo $pemail;?>">
								<label for="email">E-mail*</label>
							</div>
							<div class="form-group col-4">
								<div>
									<input type="text" class="form-control" id="pec" name="pec" value="<?php echo $ppec;?>">
									<label for="pec">PEC</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<input type="text" class="form-control" id="settore" name="settore" value="<?php echo $psettore;?>">
								<label for="settore">Settore attività*</label>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-8 offset-sm-2">
								<textarea id="descrizione_attivita" name="descrizione_attivita" rows="6" style="width:100%" class="form-control input-sm" maxlength="" placeholder="Inserire una breve descrizione delle attività svolte dall'azienda (minimo 50 caratteri)"><?php echo $pdescrizione_attivita; ?></textarea>
								<label for="descrizione_attivita">Descrizione attività*</label>
							</div>
						</div>

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

	$("#data_nascita_rl").datepicker({
		language: "it",
		todayBtn: "linked",
		todayHighlight: true,
		autoclose: true,
		orientation: "auto"
	}); 

	$("#data_ccia").datepicker({
		language: "it",
		todayBtn: "linked",
		todayHighlight: true,
		autoclose: true,
		orientation: "auto"
	}); 

	$("#data_documento").datepicker({
		language: "it",
		todayBtn: "linked",
		todayHighlight: true,
		autoclose: true,
		orientation: "auto"
	}); 

	$('#codicefiscale').keyup(function() {
		this.value = this.value.toUpperCase();
	});

	$('#codicefiscale_rl').keyup(function() {
		this.value = this.value.toUpperCase();
	});
});

$("#telefono_rl").keypress(function(e) {
	if (e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
	{
		e.preventDefault();
	}
});

$("#telefono").keypress(function(e) {
	if (e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
	{
		e.preventDefault();
	}
});

$("#data_nascita_rl").keypress(function(e) {
	if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
	{
		e.preventDefault();
	}
});

$("#data_ccia").keypress(function(e) {
	if (e.which != 47 && e.which != 48 && e.which != 49 && e.which != 50 && e.which != 51 && e.which != 52 && e.which != 53 && e.which != 54 && e.which != 55 && e.which != 56 && e.which != 57)
	{
		e.preventDefault();
	}
});

$("#data_nascita_rl").blur(function() {
	var data_nascita=$("#data_nascita_rl").val()
	if(data_nascita.length!=10)
		$("#data_nascita_rl").val('')
});

$("#data_ccia").blur(function() {
	var data_ccia=$("#data_ccia").val()
	if(data_ccia.length!=10)
		$("#data_ccia").val('')
});

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

$("#registrazione").submit(function(){
	var errors=0;
	var string_errors=''

	var nominativo_rl=$("#nominativo_rl").val();
	if(nominativo_rl=="")
	{
		string_errors=string_errors+"- Nominativo; <br>"
		errors++;
	}

	var codicefiscale_rl=$("#codicefiscale_rl").val();
	if(codicefiscale_rl=="")
	{
		string_errors=string_errors+"- Codice Fiscale; <br>"
		errors++;
	}

	var comune_nascita_rl=$("#comune_nascita_rl").val();
	if(comune_nascita_rl=="")
	{
		string_errors=string_errors+"- Comune nascita; <br>"
		errors++;
	}

	var data_nascita_rl=$("#data_nascita_rl").val();  
	if(data_nascita_rl=="")
	{
		string_errors=string_errors+"- Data di nascita; <br>"
		errors++;
	}

	var comune_residenza_rl=$("#comune_residenza_rl").val();  
	if(comune_residenza_rl=="")
	{
		string_errors=string_errors+"- Comune di residenza; <br>"
		errors++;
	}

	var indirizzo_rl=$("#indirizzo_rl").val();  
	if(indirizzo_rl=="")
	{
		string_errors=string_errors+"- Indirizzo di residenza; <br>"
		errors++;
	}

	var telefono_rl=$("#telefono_rl").val();  
	if(telefono_rl=="")
	{
		string_errors=string_errors+"- Cellulare; <br>"
		errors++;
	}

	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

	var email_rl=$("#email_rl").val();  
	if(email_rl=="" || !email_rl.match(re))
	{
		if(email_rl=="")
		{
			string_errors=string_errors+"- E-mail; <br>"
			errors++;
		}

		if(!email_rl.match(re) && email_rl!="")
		{
			string_errors=string_errors+"- E-mail(formato non valido); <br>"
			errors++;
		}
	}

	var nome_file=$("#documento").val();  
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

	var idgen_tbl_documento=$("#idgen_tbl_documento").val();  
	if(idgen_tbl_documento=="")
	{
		string_errors=string_errors+"- Tipo di documento d'identità; <br>"
		errors++;
	}

	var numero_documento=$("#numero_documento").val();  
	if(numero_documento=="")
	{
		string_errors=string_errors+"- Numero documento; <br>"
		errors++;
	}

	var data_documento=$("#data_documento").val();  
	if(data_documento=="")
	{
		string_errors=string_errors+"- Data scadenza documento di riconoscimento; <br>"
		errors++;
	}

	var idsso_tabella_tipologia_ente=$("#idsso_tabella_tipologia_ente").val();  
	if(idsso_tabella_tipologia_ente=="")
	{
		string_errors=string_errors+"- Natura giuridica; <br>"
		errors++;
	}

	var citta=$("#citta").val();  
	if(citta=="")
	{
		string_errors=string_errors+"- Comune sede legale; <br>"
		errors++;
	}

	var indirizzo=$("#indirizzo").val();  
	if(indirizzo=="")
	{
		string_errors=string_errors+"- Indirizzo sede legale; <br>"
		errors++;
	}

	var piva=$("#piva").val();  
	if(piva=="")
	{
		string_errors=string_errors+"- Partita IVA; <br>"
		errors++;
	}

	var codicefiscale=$("#codicefiscale").val();  
	if(codicefiscale=="")
	{
		string_errors=string_errors+"- Codice Fiscale; <br>"
		errors++;
	}

	var luogo_ccia=$("#luogo_ccia").val();  
	if(luogo_ccia=="")
	{
		string_errors=string_errors+"- Luogo iscrizione C.C.I.A.; <br>"
		errors++;
	}

	var numero_ccia=$("#numero_ccia").val();  
	if(numero_ccia=="")
	{
		string_errors=string_errors+"- Numero iscrizione C.C.I.A.; <br>"
		errors++;
	}

	var data_ccia=$("#data_ccia").val();  
	if(data_ccia=="")
	{
		string_errors=string_errors+"- Data iscrizione C.C.I.A.; <br>"
		errors++;
	}

	var attivita_ccia=$("#attivita_ccia").val();  
	if(attivita_ccia=="")
	{
		string_errors=string_errors+"- Attività C.C.I.A.; <br>"
		errors++;
	}

	var telefono=$("#telefono").val();  
	if(telefono=="")
	{
		string_errors=string_errors+"- Telefono; <br>"
		errors++;
	}

	var email=$("#email").val();  
	if(email=="" || !email.match(re))
	{
		if(email=="")
		{
			string_errors=string_errors+"- e-mail; <br>"
			errors++;
		}

		if(!email.match(re) && email!="")
		{
			string_errors=string_errors+"- e-mail(formato non valido); <br>"
			errors++;
		}
	}

	/*
	var pec=$("#pec").val();  
	if(pec=="")
	{
		string_errors=string_errors+"- PEC; <br>"
		errors++;
	}
	*/

	var settore=$("#settore").val();  
	if(settore=="")
	{
		string_errors=string_errors+"- Settore attività; <br>"
		errors++;
	}

	var descrizione_attivita=$("#descrizione_attivita").val();  
	if(descrizione_attivita=="")
	{
		string_errors=string_errors+"- Descrizione attività; <br>"
		errors++;
	}
	else
	{
		if(descrizione_attivita.length<50)
		{
			string_errors=string_errors+"- Inserire una descrizione delle attività di almeno 50 caratteri; <br>"
			errors++;
		}
	}

	if(!$('#articolo76').is(":checked") || !$('#dlgs196').is(":checked") || !$('#privacy').is(":checked"))
	{
		string_errors=string_errors+"- Tutte le dichiarazioni sono obbligatorie; <br>"
		errors++;
	}

	if(errors==0)
	{
		var validate_cf=false;
		var validate_data_nascita=false;
		var validate_data_ccia=false;
		var string_errors_interna='';

		if(codicefiscale_rl.length==16)
		{
		  validate_cf=true;
		}
		else
		  string_errors_interna=string_errors_interna+"- Codice Fiscale troppo corto, sono stati inseriti "+codicefiscale.length+" caratteri; <br>"

		re = /^\d{1,2}\/\d{1,2}\/\d{4}$/; 
		if(data_nascita_rl.match(re))
			var validate_data_nascita=true;

		if(data_ccia.match(re))
			var validate_data_ccia=true;

		if(validate_cf && validate_data_nascita && validate_data_ccia)
		{
			$("#registrazione").submit();
		}
		else
		{
			if(!validate_cf)
			{
				$("#fg_codicefiscale_rl").addClass("has-error");
				string_errors_interna=string_errors_interna+"- Codice Fiscale(errori formali); <br>"
			}

			if(!validate_data_nascita)
			{
				$("#fg_data_nascita").addClass("has-error");
				string_errors_interna=string_errors_interna+"- Data di nascita(formato non valido); <br>"
			}

			if(!validate_data_ccia)
			{
				$("#fg_data_ccia").addClass("has-error");
				string_errors_interna=string_errors_interna+"- Data iscrizione C.C.I.A.(formato non valido); <br>"
			}

			string_errors_interna = string_errors_interna.replace(/,\s*$/, "");
			visualizzaAlert("<b>Attenzione</b><br>"+string_errors_interna);
			return false;
			
		}
	}
	else
	{
		string_errors = string_errors.replace(/,\s*$/, "");
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}
});

function visualizzaAlert(alert_message)
{
	$("#alert_registrazione").show();
	$("#alert_registrazione").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br><br>');
	window.scrollTo(0,0);
}

function consultaDOCUMENTO(myFILE)
{
  settings=window_center(580,950);
  settings+=",resizable=yes";
  
  window.open(myFILE,'documento',settings);
  if(win.window.focus){win.window.focus();}
}
</script>

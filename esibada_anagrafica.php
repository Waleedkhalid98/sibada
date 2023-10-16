<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$upload_max = ini_get('upload_max_filesize');
$upload_max_parsed = parse_size(ini_get('upload_max_filesize'));

$chiave = get_cookieuserFront();

$fldidgen_utente = verifica_eutente($chiave);
$fldidutente = front_get_db_value("select idsso_anagrafica_utente from " . FRONT_ESONAME . ".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if (empty($fldidutente) || empty($fldidgen_utente))
	die("Attenzione! sessione scaduta");
if (get_param("_elimina")) {
	$sSQL = "UPDATE " . FRONT_ESONAME . ".gen_utente SET 
	flag_sl='0'
	WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($sSQL);

}
if (get_param("_torna")) {
	$sSQL = "UPDATE " . FRONT_ESONAME . ".gen_utente SET 
	flag_sl='1'
	WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($sSQL);

}
if (get_param("_conferma")) {
	$fldcognome = get_param("cognome");
	$fldcognome = db_string($fldcognome);
	$fldcognome = strtoupper($fldcognome);

	$fldnome = get_param("nome");
	$fldnome = db_string($fldnome);
	$fldnome = strtoupper($fldnome);

	$fldcodicefiscale = get_param("codicefiscale");
	$fldcodicefiscale = db_string($fldcodicefiscale);
	$fldcodicefiscale = strtoupper($fldcodicefiscale);

	$fldsesso = get_param("sesso");

	$fldidgen_nazione_nascita = get_param("idgen_nazione_nascita");

	$flddata_nascita = get_param("data_nascita");
	$flddata_nascita = invertidata($flddata_nascita, "-", "/", 1);

	$fldidgen_cittadinanza1 = get_param("idgen_cittadinanza1");
	$fldidgen_cittadinanza1 = db_string($fldidgen_cittadinanza1);

	$fldidgen_comune_nascita = get_param("idgen_comune_nascita");
	if (!empty($fldidgen_comune_nascita)) {
		$fldcomune_nascita = get_db_value("SELECT comune FROM " . DBNAME_A . ".comune WHERE idcomune='$fldidgen_comune_nascita'");
		$fldcomune_nascita = db_string($fldcomune_nascita);
		$fldcomune_nascita = strtoupper($fldcomune_nascita);
		//$fldprov_nascita=get_param("prov_nascita");   
		$fldprov_nascita = get_db_value("SELECT provincia FROM " . DBNAME_A . ".comune WHERE idcomune='$fldidgen_comune_nascita'");
		$fldprov_nascita = strtoupper($fldprov_nascita);
	} else {
		$fldcomune_nascita = get_param("comune_nascita");
		$fldcomune_nascita = db_string($fldcomune_nascita);
		$fldcomune_nascita = strtoupper($fldcomune_nascita);
		$fldprov_nascita = "EE";
	}

	$fldidgen_comune = get_param("idgen_comune");
	if (!empty($fldidgen_comune)) {
		$fldcomune_residenza = get_db_value("SELECT comune FROM " . DBNAME_A . ".comune WHERE idcomune='$fldidgen_comune'");
		$fldcomune_residenza = db_string($fldcomune_residenza);
		$fldcomune_residenza = strtoupper($fldcomune_residenza);

		$fldcap = get_db_value("SELECT cap FROM " . DBNAME_A . ".comune WHERE idcomune='$fldidgen_comune'");
		$fldcomune_residenza = db_string($fldcomune_residenza);
		$fldcomune_residenza = strtoupper($fldcomune_residenza);
	} else {
		$fldcomune_residenza = db_string($fldcomune_residenza);
		$fldcomune_residenza = strtoupper($fldcomune_residenza);
	}

	$fldprov_residenza = get_param("prov_residenza");
	$fldprov_residenza = db_string($fldprov_residenza);
	$fldprov_residenza = strtoupper($fldprov_residenza);

	$fldindirizzo = get_param("indirizzo");
	$fldindirizzo = db_string($fldindirizzo);
	$fldindirizzo = strtoupper($fldindirizzo);

	$fldcivico = get_param("civico");
	$fldcivico = db_string($fldcivico);
	$fldcivico = strtoupper($fldcivico);

	$fldcellulare = get_param("cellulare");

	$fldtelefono = get_param("telefono");

	$fldemail = get_param("email");

	$fldidgen_tbl_documento = get_param("idgen_tbl_documento");

	$flddata_documento = get_param("data_documento");
	$flddata_documento = invertidata($flddata_documento, "-", "/", 1);

	$fldnumero_documento = get_param("numero_documento");
	$fldnumero_documento = db_string($fldnumero_documento);
	$fldnumero_documento = strtoupper($fldnumero_documento);



	$pmultidisp = get_param("idsl_disponibilita");
	if (!empty($pmultidisp)) {
		$aDISPONIBILITA = explode(",", $pmultidisp);

		if (!empty($aDISPONIBILITA)) {
			print_r($aDISPONIBILITA);
			foreach ($aDISPONIBILITA as $iddisp) {
				$insert = "INSERT INTO sibada_curriculum_disponibilita(idsibada_curriculum,idsibada_disponibilita) VALUES('$pidsibada_curriculum','$iddisp')";
				$db->query($insert);
			}
		}
	}

	$sSQL = "UPDATE " . FRONT_ESONAME . ".gen_utente SET 
	telefono='$fldtelefono',
	email='$fldemail',
	cognome='$fldcognome',
	nome='$fldnome',
	idgen_comune_nascita='$fldidgen_comune_nascita',
	comune_nascita='$fldcomune_nascita',
	prov_nascita='$fldprov_nascita',
	data_nascita='$flddata_nascita',
	codicefiscale='$fldcodicefiscale',
	citta='$fldcomune_residenza',
	idgen_comune='$fldidgen_comune',
	provincia='$fldprov_residenza',
	indirizzo='$fldindirizzo',
	cellulare='$fldcellulare',
	civico='$fldcivico',
	sesso='$fldsesso',
	idgen_nazione_nascita='$fldidgen_nazione_nascita'

	WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($sSQL);

	$fldidutente = front_get_db_value("SELECT idsso_anagrafica_utente FROM eso_join_anagrafica WHERE idgen_utente='$fldidgen_utente'");

	$sSQL = "UPDATE " . DBNAME_SS . ".sso_anagrafica_utente SET 
	email='$fldemail',
	indirizzo='$fldindirizzo',
	cellulare='$fldcellulare',
	telefono='$fldtelefono',
	civico='$fldcivico',
	data_nascita='$flddata_nascita',
	idgen_comune_nascita='$fldidgen_comune_nascita',
	comune_nascita='$fldcomune_nascita',
	prov_nascita='$fldprov_nascita',
	idamb_comune_residenza='$fldidgen_comune',
	citta='$fldcomune_residenza',
	prov='$fldprov_residenza',
	sesso='$fldsesso',
	idgen_cittadinanza1='$fldidgen_cittadinanza1'
	WHERE idutente='$fldidutente'";
	$db->query($sSQL);

	$sSQL = "UPDATE " . DBNAME_SS . ".sso_anagrafica SET idamb_nazione='$fldidgen_nazione_nascita' WHERE idutente='$fldidutente'";
	$db->query($sSQL);



	if (!empty($_FILES["documento"]["name"])) {
		$delete = "DELETE FROM sso_anagrafica_allega WHERE descrizione='Documento di riconoscimento' AND idsso_anagrafica='$fldidutente' AND idsso_tabella_allega=11";
		$db->query($delete);

		$pnome_originale = basename($_FILES["documento"]["name"]);
		$pnome_originale = explode(".", $pnome_originale);
		$fldestensione = $pnome_originale[count($pnome_originale) - 1];

		$fldpath = "../documenti/sibada/anagrafica/";
		$fldnome_allegato_name = md5("ESO_DOCUMENTO_" . $pidgen_utente . '_' . date("Ymd") . date("Hi")) . "." . $fldestensione;

		$fldestensione = strtolower($fldestensione);

		if ($fldestensione == "p7m" || $fldestensione == "pdf" || $fldestensione == "jpg" || $fldestensione == "jpeg" || $fldestensione == "png") {
			copy($_FILES["documento"]["tmp_name"], $fldpath . $fldnome_allegato_name);
			if (file_exists($fldpath . $fldnome_allegato_name)) {
				$sSQL = "INSERT INTO sso_anagrafica_allega (idsso_anagrafica,idsso_tabella_allega,descrizione,data,allegato_name,flag_salva) VALUES ('$fldidutente','11','Documento di riconoscimento','$flddata_richiesta','$fldnome_allegato_name',1)";
				$db->query($sSQL);
				$alert_file_success = true;
			}
		}
	}

	$alert_success = true;
}

if (!empty($fldidgen_utente)) {
	$sSQL = "SELECT *,idgen_nazione_nascita AS idnazione_nascita FROM " . FRONT_ESONAME . ".gen_utente WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($sSQL);
	if ($db_front->next_record()) {
		$fldidgen_utente = $db_front->f("idgen_utente");
		$fldmatricola = front_get_db_value("SELECT idsso_anagrafica_utente FROM eso_join_anagrafica WHERE idgen_utente='$fldidgen_utente'");
		$fldstato = $db_front->f("flag_sl");
		$fldemail = $db_front->f("email");
		$fldcognome = $db_front->f("cognome");
		$fldidsibada_disponibilita = $db_front->f("idsibada_disponibilita");
		$fldnome = $db_front->f("nome");
		$fldcomune_nascita = $db_front->f("comune_nascita");

		$fldidamb_nazione = $db_front->f("idnazione_nascita");

		$fldidgen_comune_nascita = $db_front->f("idgen_comune_nascita");
		if (empty($fldcomune_nascita))
			$fldcomune_nascita = getDescrizioneComune($fldidgen_comune_nascita);

		$fldsesso = $db_front->f("sesso");
		$fldprov_nascita = $db_front->f("prov_nascita");

		$flddata_nascita = $db_front->f("data_nascita");
		$flddata_nascita = invertidata($flddata_nascita, "/", "-", 2);

		$fldcodicefiscale = $db_front->f("codicefiscale");
		$fldcomune_residenza = $db_front->f("citta");

		$fldidgen_comune = $db_front->f("idgen_comune");
		if (empty($fldcomune_residenza))
			$fldcomune_residenza = getDescrizioneComune($fldidgen_comune);

		$fldindirizzo = $db_front->f("indirizzo");
		$fldtelefono = $db_front->f("telefono");
		$fldcellulare = $db_front->f("cellulare");
		$fldcivico = $db_front->f("civico");
		$fldidamb_comune_residenza = $db_front->f("idgen_comune");
		$fldprov_residenza = $db_front->f("provincia");

		$fldidamb_nazione = get_db_value("SELECT idamb_nazione FROM sso_anagrafica WHERE idutente='$fldmatricola'");
		$fldidgen_cittadinanza1 = get_db_value("SELECT idgen_cittadinanza1 FROM sso_anagrafica_utente WHERE idutente='$fldmatricola'");

		$fldidsso_tabella_condizione_soggiorno = $db_front->f("idgen_tbl_documento");
		$flddocumento_numero = $db_front->f("numero_documento");
		$flddata_scadenza = $db_front->f("data_documento");
		$flddata_scadenza = invertidata($flddata_scadenza, "/", "-", 2);

		$fldallegato_name = get_db_value("SELECT allegato_name FROM sso_anagrafica_allega WHERE idsso_anagrafica='$fldmatricola' AND idsso_tabella_allega=11 AND descrizione='Documento di riconoscimento'");
		if (!empty($fldstato)) {
			$buttonControllo = '<button name="_elimina" id="_elimina" type="submit" class="btn btn-danger btn-md mt-3" value="elimina">Elimina candidatura</button>';
		} else {
			$buttonControllo = '<button name="_torna" id="_torna" type="submit" class="btn btn-success btn-md mt-3" value="torna">Ricandidati</button>';
		}
	}
}


?>
<!doctype html>
<html lang="it">

<head>
	<title>Sibada - I miei dati</title>
	<?php echo get_importazioni_sibada_header(); ?>
</head>

<body class="push-body" data-ng-app="ponmetroca">
	<div class="body_wrapper push_container clearfix" id="page_top">

		<?php echo get_header_sibadafront(); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="esibada_home.php" title="Vai alla pagina Home"
											class="">Home</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a>I miei dati</a></li>
								</ol>
							</nav>

							<?php
							if ($alert_success)
								echo (get_alert(4, "Salvataggio avvenuto con successo."));
							if ($alert_file_success)
								echo (get_alert(4, "Documento di riconoscimento aggiornato correttamente."));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">

				<h3 class="text-center">I tuoi dati anagrafici</h3>
				<h5 class="text-center">Qui potrai modificare i tuoi dati e ritirare la tua candidatura tramite il
					bottone in fondo.</h5>

				<main id="main-content" class="container px-4 my-4">
					<div id="alert_anagrafica" style="display:none;"></div>
					<div class="col-md-10 offset-md-1">
						<div class="card-wrapper card-space">
							<div class="card card-bg card-big ">
								<div class="card-body">

									<form id="registrazione" method="post" enctype="multipart/form-data"
										action="esibada_anagrafica.php" class="form-horizontal ">
										<div class="row">
											<div class="form-group col-sm-6 ">
												<label for="cognome" class="form-label active">Cognome</label>
												<input type="text" class="form-control shadow" id="cognome"
													name="cognome" placeholder="" value="<?php echo $fldcognome; ?>"
													readonly>
											</div>

											<div class="form-group col-6">
												<label for="nome" class="form-label active">Nome</label>
												<input type="text" class="form-control shadow" id="nome" name="nome"
													placeholder="" value="<?php echo $fldnome; ?>" readonly>
											</div>
										</div>

										<div class="row">
											<div class="form-group col-sm-6">
												<label for="codicefiscale" class="form-label active">Codice
													Fiscale</label>
												<input type="text" class="form-control shadow" id="codicefiscale"
													name="codicefiscale" placeholder=""
													value="<?php echo $fldcodicefiscale; ?>" readonly>
											</div>

											<div class="form-group col-sm-6">
												<div class="it-datepicker-wrapper theme-dark">
													<label for="data_nascita" class="form-label active">Data di
														nascita*</label>
													<input class="form-control it-date-datepicker" id="data_nascita"
														name="data_nascita" type="text"
														value="<?php echo $flddata_nascita; ?>" readonly>

												</div>
											</div>
										</div>



										<div class="row">
											<div class="form-group col-sm-6">
												<div>
													<div>
														<label for="idgen_nazione_nascita"
															class="form-label active">Nazione di nascita*</label>
														<select class="form-control shadow"
															title="Scegli la nazione di nascita"
															id="idgen_nazione_nascita" name="idgen_nazione_nascita"
															data-live-search="true" onChange="changeNazione()" readonly>
															<option value=""></option>
															<?php
															$sSQL = "SELECT *FROM " . DBNAME_A . ".nazione ORDER BY nazione";
															$db->query($sSQL);
															$next_record = $db->next_record();

															$response = array();
															while ($next_record) {
																$fldidnazione = $db->f("idnazione");
																$fldnazione = $db->f("nazione");

																if ($fldidnazione == $fldidamb_nazione)
																	echo '<option value="' . $fldidnazione . '" selected>' . $fldnazione . '</option>';
																else
																	echo '<option value="' . $fldidnazione . '">' . $fldnazione . '</option>';

																$next_record = $db->next_record();
															}
															?>
														</select>
													</div>
												</div>
											</div>

											<div class="form-group col-sm-6">
												<div>
													<div>
														<label class="form-label active"
															for="idgen_cittadinanza1">Cittadinanza*</label>
														<select class="form-control shadow"
															title="Scegli la cittadinanza" id="idgen_cittadinanza1"
															name="idgen_cittadinanza1" data-live-search="true"
															data-live-search-placeholder="Cerca" readonly>
															<option value=""></option>
															<?php
															$sSQL = "SELECT *FROM " . DBNAME_A . ".nazione ORDER BY nazione";
															$db->query($sSQL);
															$next_record = $db->next_record();

															$response = array();
															while ($next_record) {
																$fldidnazione = $db->f("idnazione");
																$fldnazione = $db->f("nazione");
																$fldnazionalita = $db->f("nazionalita");

																if ($fldidnazione == $fldidgen_cittadinanza1)
																	echo '<option value="' . $fldidnazione . '" selected>' . $fldnazionalita . ' (' . $fldnazione . ')</option>';
																else
																	echo '<option value="' . $fldidnazione . '">' . $fldnazionalita . ' (' . $fldnazione . ')</option>';

																$next_record = $db->next_record();
															}
															?>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="form-group col-sm-6">
												<div>
													<div id="div_comune_nascita">
														<select class="form-control shadow"
															title="Scegli il comune di nascita"
															id="idgen_comune_nascita" name="idgen_comune_nascita"
															data-live-search="true" data-live-search-placeholder="Cerca"
															onchange="changeCOMUNE(1);" readonly>
															<option value=""></option>
															<?php
															$sSQL = "SELECT * FROM " . DBNAME_A . ".comune ORDER BY comune";
															$db->query($sSQL);
															$next_record = $db->next_record();

															$response = array();
															while ($next_record) {
																$fldidcomune = $db->f("idcomune");
																$fldcomune = $db->f("comune");
																$fldprovincia = $db->f("provincia");

																$value = $fldcomune . ' (' . $fldprovincia . ')';

																if ($fldidcomune == $fldidgen_comune_nascita)
																	echo '<option value="' . $fldidcomune . '" selected>' . $value . '</option>';
																else
																	echo '<option value="' . $fldidcomune . '">' . $value . '</option>';

																$next_record = $db->next_record();
															}
															?>
														</select>
														<label class="form-label active"
															for="idgen_comune_nascita">Comune di nascita*</label>
													</div>
												</div>

											</div>
											<div class="form-group col-6" id="div_prov_nascita">
												<label class="form-label active" for="prov_nascita">Provincia di
													nascita*</label>
												<input type="text" class="form-control"
													style="text-transform: uppercase;" id="prov_nascita"
													name="prov_nascita" value="<?php echo $fldprov_nascita; ?>"
													maxlength="2" readonly>
											</div>
										</div>

										<div class="row">
											<div class="form-group col-6 ">
												<label for="sesso" class="form-label active">Genere*</label>
												<select class="form-control " title="Scegli il sesso" name="sesso"
													id="sesso" readonly>
													<?php
													if (!$fldsesso)
														$sel1 = 'selected';

													if ($fldsesso == 'M')
														$sel2 = 'selected';

													if ($fldsesso == 'F')
														$sel3 = 'selected';

													echo "\n <option value='' " . $sel1 . "></option>
										\n <option value='M' " . $sel2 . ">M</option>
										\n <option value='F' " . $sel3 . ">F</option>";
													?>
												</select>
											</div>
											<div class="form-group col-sm-6">
												<div>
													<label class="form-label active" for="email">E-mail*</label>
													<input type="text" class="form-control shadow" id="email"
														name="email" maxlength="80" value="<?php echo $fldemail; ?>">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="form-group col-sm-6">
												<div>
													<div class="bootstrap-select-wrapper">
														<label for="idgen_comune">Comune di residenza*</label>
														<select class="form-control shadow"
															title="Scegli il comune di residenza" id="idgen_comune"
															name="idgen_comune" data-live-search="true"
															data-live-search-placeholder="Cerca"
															onchange="changeCOMUNE(2);">
															<option value=""></option>
															<?php
															$sSQL = "SELECT *FROM " . DBNAME_A . ".comune ORDER BY comune";
															$db->query($sSQL);
															$next_record = $db->next_record();

															$response = array();
															while ($next_record) {
																$fldidcomune_res = $db->f("idcomune");
																$fldcomune = $db->f("comune");
																$fldprovincia = $db->f("provincia");

																$value = $fldcomune . ' (' . $fldprovincia . ')';

																if ($fldidcomune_res == $fldidgen_comune)
																	echo '<option style="color: #17324d;" value="' . $fldidcomune_res . '" selected>' . $value . '</option>';
																else
																	echo '<option value="' . $fldidcomune_res . '">' . $value . '</option>';

																$next_record = $db->next_record();
															}
															?>
														</select>
													</div>
												</div>
											</div>
											<div class="form-group col-6">
												<label class="form-label active" for="prov_residenza">Provincia di
													residenza*</label>
												<input type="text" class="form-control shadow"
													style="text-transform: uppercase;" id="prov_residenza"
													name="prov_residenza" value="<?php echo $fldprov_residenza; ?>"
													maxlength="2">
											</div>
										</div>


										<div class="row">
											<div class="form-group col-sm-6">
												<div>
													<label class="form-label active" for="indirizzo">Indirizzo di
														residenza*</label>
													<input type="text" class="form-control shadow"
														style="text-transform: uppercase;" id="indirizzo"
														name="indirizzo" value="<?php echo $fldindirizzo; ?>">
												</div>
											</div>
											<div class="form-group col-6">
												<label class="form-label active" for="civico">Numero civico di
													residenza*</label>
												<input type="text" class="form-control shadow"
													style="text-transform: uppercase;" id="civico" name="civico"
													value="<?php echo $fldcivico; ?>">
											</div>
										</div>


										<div class="row">
											<div class="form-group col-sm-6">
												<div>
													<label c for="cellulare" class="form-label active">Cellulare</label>
													<input type="text" class="form-control shadow" id="cellulare"
														name="cellulare" placeholder=""
														value="<?php echo $fldcellulare; ?>">
												</div>
											</div>
											<div class="form-group col-6">
												<label class="form-label active" for="telefono">Telefono</label>
												<input type="text" class="form-control shadow"
													style="text-transform: uppercase;" id="telefono" name="telefono"
													maxlength="20" value="<?php echo $fldtelefono; ?>">
											</div>
										</div>






										<center>
											<button name="_conferma" id="_conferma" type="submit"
												class="btn btn-primary btn-md mt-3" value="conferma">Aggiorna i tuoi dati nel
												sistema</button>
											<?php echo $buttonControllo; ?>
										</center>

										<input type="hidden" id="flag_omocodia_cf" name="flag_omocodia_cf"
											value="false">
									</form>
								</div>
							</div>
						</div>
					</div>
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
								<p>Il file che si sta cercando di caricare supera la dimensione massima consentita di
									<?php echo $upload_max; ?>
								</p>
							</div>
							<div class="modal-footer">
								<button class="btn btn-outline-primary btn-sm" type="button" onclick=""
									data-dismiss="modal">Continua</button>
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
	$(document).ready(function () {
		$('.it-date-datepicker').datepicker({
			inputFormat: ["dd/MM/yyyy"],
			outputFormat: 'dd/MM/yyyy',
		});
	});


	function changeNazione() {
		var idnazione = $("#idgen_nazione_nascita").val();
		if (idnazione == "122") {
			$("#div_comune_nascita_desc").hide();
			$("#div_comune_nascita").show();
			$("#idgen_comune_nascita").show();
			$("#div_prov_nascita").show();
			$("#prov_nascita").val("");
			$("#prov_nascita").show();
		}
		else {
			$("#div_comune_nascita_desc").show();
			$("#div_comune_nascita").hide();
			$("#idgen_comune_nascita").hide();
			$("#div_prov_nascita").hide();
			$("#prov_nascita").val("EE");
			$("#prov_nascita").hide();
		}
	}

	function changeCOMUNE(type) {
		if (type == "1") {
			var idcomune = $("#idgen_comune_nascita").val();
			var obj_prov = "prov_nascita";
		}
		else if (type == "2") {
			var idcomune = $("#idgen_comune").val();
			var obj_prov = "prov_residenza";
		}

		if (idcomune != '' && idcomune != '0' && idcomune != 0) {
			var page = "sibada_action.php";
			var params = "_user=<?php echo $chiave; ?>&_action=get_prov&_idcomune=" + idcomune;
			var loader = dhtmlxAjax.postSync(page, params);
			myParam = loader.xmlDoc.responseText;

			$("#" + obj_prov).val(myParam)
		}
	}

	$('#documento').bind('change', function () {
		var size_file = this.files[0].size;

		var max_upload = parseInt('<?php echo $upload_max_parsed; ?>');

		if (size_file >= max_upload) {
			$('#documento').val("");
			$('#modal_file').modal('show');
			return false;
		}
	});

	$("#registrazione").submit(function (event) {

		var errors = 0;
		var string_errors = "";

		var cognome = $("#cognome").val();
		if (cognome == "") {
			string_errors = string_errors + "- Cognome; <br>"
			errors++;
		}

		var nome = $("#nome").val();
		if (nome == "") {
			string_errors = string_errors + "- Nome; <br>"
			errors++;
		}

		var codicefiscale = $("#codicefiscale").val();
		if (codicefiscale == "") {
			string_errors = string_errors + "- Codice Fiscale; <br>"
			errors++;
		}


		var idgen_cittadinanza1 = $("#idgen_cittadinanza1").val();
		if (idgen_cittadinanza1 == "") {
			string_errors = string_errors + "- Cittadinanza; <br>"
			errors++;
		}

		var sesso = $("#sesso").val();
		if (sesso == "") {
			string_errors = string_errors + "- Sesso; <br>"
			errors++;
		}

		var idamb_nazione = $("#idgen_nazione_nascita").val();
		if (idamb_nazione == "") {
			string_errors = string_errors + "- Nazione di nascita; <br>"
			errors++;
		}

		var data_nascita = $("#data_nascita").val();
		if (data_nascita == "") {
			string_errors = string_errors + "- Data di nascita; <br>"
			errors++;
		}

		if (idamb_nazione == 122 || idamb_nazione == "122") {
			var idgen_comune_nascita = $("#idgen_comune_nascita").val();
			if (idgen_comune_nascita == "") {
				string_errors = string_errors + "- Comune di nascita; <br>"
				errors++;
			}

			var provincia_nascita = $("#prov_nascita").val();
			if (provincia_nascita == "") {
				string_errors = string_errors + "- Provincia di nascita; <br>"
				errors++;
			}
		}
		else {
			var idgen_comune_nascita = '';
			var comune_nascita = $("#comune_nascita").val();
			if (comune_nascita == "") {
				string_errors = string_errors + "- Comune di nascita; <br>"
				errors++;
			}
		}

		var indirizzo = $("#indirizzo").val();
		if (indirizzo == "") {
			string_errors = string_errors + "- Indirizzo di residenza; <br>"
			errors++;
		}

		var civico = $("#civico").val();
		if (civico == "") {
			string_errors = string_errors + "- Civico di residenza; <br>"
			errors++;
		}

		var provincia_residenza = $("#prov_residenza").val();
		if (provincia_residenza == "") {
			string_errors = string_errors + "- Provincia di residenza; <br>"
			errors++;
		}

		var cellulare = $("#cellulare").val();
		if (cellulare == "") {
			string_errors = string_errors + "- Cellulare; <br>"
			errors++;
		}

		var comune_residenza = $("#comune_residenza").val();
		if (comune_residenza == "") {
			string_errors = string_errors + "- Comune di residenza; <br>"
			errors++;
		}

		var email = $("#email").val();
		if (email == "") {
			string_errors = string_errors + "- Indirizzo email; <br>"
			errors++;
		}

		// if('<?php echo $documento; ?>'=='false' || $("#documento").is(":visible"))
		// {
		// 	var nome_file=document.getElementById("documento").value;
		// 	var ext=nome_file.substr(nome_file.length - 4);
		// 	ext=ext.toLowerCase();
		// 	if(ext!=".p7m" && ext!=".pdf" && ext!=".jpg" && ext!=".png")
		// 	{
		// 		string_errors=string_errors+"- Estensione documento di riconoscimento non valida; <br>"
		// 		errors++;
		// 	}
		// }

		var data_documento = $("#data_documento").val();
		if (data_documento == "") {
			string_errors = string_errors + "- Data rilascio documento di riconoscimento; <br>"
			errors++;
		}

		var numero_documento = $("#numero_documento").val();
		if (numero_documento == "") {
			string_errors = string_errors + "- Numero documento di riconoscimento; <br>"
			errors++;
		}

		if (errors == 0) {
			var flag_codicefiscale = false;

			if ($("#codicefiscale").val().length != 16) {
				flag_codicefiscale = true;
			}

			var cognome = $("#cognome").val();
			var nome = $("#nome").val();
			var codicefiscale = $("#codicefiscale").val();
			var sesso = $("#sesso").val();
			var idgen_comune_nascita = $("#idgen_comune_nascita").val();
			var idamb_nazione = $("#idgen_nazione_nascita").val();
			var data_nascita = $("#data_nascita").val();

			var page = "sibada_action.php";
			var params = "_user=<?php echo $chiave; ?>&_action=calcola_cf&cognome=" + cognome + "&nome=" + nome + "&sesso=" + sesso + "&idgen_comune_nascita=" + idgen_comune_nascita + "&idamb_nazione=" + idamb_nazione + "&data_nascita=" + data_nascita;
			var loader = dhtmlxAjax.postSync(page, params);
			myParam = loader.xmlDoc.responseText;

			var result = myParam.split("|");
			if (result[0] == "0") {
				var string_errors = 'Il sistema ha elaborato un codice fiscale diverso da quello inserito. <br>Controllare i dati immessi, quindi premere nuovamente \'Conferma\'.';
				visualizzaAlert(string_errors);
				return false;
			}
			else {
				if (codicefiscale.toUpperCase() != result[1].toUpperCase()) {
					var flag_omocodia_cf = $("#flag_omocodia_cf").val();

					if (flag_omocodia_cf == "false") {
						var string_error_cf = '- Codice Fiscale(errori formali); <button name="btn_assegnato" id="btn_assegnato" type="button" onClick="check_assegnato(\'' + codicefiscale + '\',\'' + result[1] + '\')" class="btn btn-success btn-xs" style="width: 180px;"><span><i class="fa fa-cog"></i>&nbsp;Assegnato da Ag. Entrate</span></button><br>';

						visualizzaAlert(string_error_cf);
						return false;
					}
				}
			}

			if (flag_codicefiscale) {
				visualizzaAlert("<b>Attenzione</b> il Codice Fiscale inserito non è di 16 caratteri.");
				return false;
			}
		}
		else {
			visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>" + string_errors);
			return false;
		}
	});


	function visualizzaAlert(alert_message) {
		$("#alert_anagrafica").show();
		$("#alert_anagrafica").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">' + alert_message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
		window.scrollTo(0, 0);
	}


	function check_assegnato(cf, cf_temp) {
		var codicefiscale_split = cf.split("");
		var codicefiscale_temp_split = cf_temp.split("");

		if (codicefiscale_split[0] == codicefiscale_temp_split[0])
			c1 = true;
		else
			c1 = false;

		if (codicefiscale_split[1] == codicefiscale_temp_split[1])
			c2 = true;
		else
			c2 = false;

		if (codicefiscale_split[2] == codicefiscale_temp_split[2])
			c3 = true;
		else
			c3 = false;

		if (codicefiscale_split[3] == codicefiscale_temp_split[3])
			c4 = true;
		else
			c4 = false;

		if (codicefiscale_split[4] == codicefiscale_temp_split[4])
			c5 = true;
		else
			c5 = false;

		if (codicefiscale_split[5] == codicefiscale_temp_split[5])
			c6 = true;
		else
			c6 = false;

		if (codicefiscale_split[8] == codicefiscale_temp_split[8])
			c9 = true;
		else
			c9 = false;

		if (codicefiscale_split[11] == codicefiscale_temp_split[11])
			c12 = true;
		else
			c12 = false;

		var c7 = omocodia(codicefiscale_split[6], codicefiscale_temp_split[6])
		var c8 = omocodia(codicefiscale_split[7], codicefiscale_temp_split[7])
		var c10 = omocodia(codicefiscale_split[9], codicefiscale_temp_split[9])
		var c11 = omocodia(codicefiscale_split[10], codicefiscale_temp_split[10])
		var c13 = omocodia(codicefiscale_split[12], codicefiscale_temp_split[12])
		var c14 = omocodia(codicefiscale_split[13], codicefiscale_temp_split[13])
		var c15 = omocodia(codicefiscale_split[14], codicefiscale_temp_split[14])

		console.log(c7 + " " + c8 + " " + c10 + " " + c11 + " " + c13 + " " + c14 + " " + c15)
		if (c1 && c2 && c3 && c4 && c5 && c6 && c7 && c8 && c9 && c10 && c11 && c12 && c13 && c14 && c15) {
			BootstrapDialog.closeAll()
			$("#flag_omocodia_cf").val("true");
			$("#_conferma").click();
		}
		else {
			$("#flag_omocodia_cf").val("false");

			var string_errors = "Codice Fiscale non assegnato dall'agenzia delle entrate.";
			visualizzaAlert(string_errors);

		}
	}

	function omocodia(char_cf, char_cf_temp) {
		/*
		0 = L 4 = Q 8 = U
		1 = M 5 = R 9 = V
		2 = N 6 = S  
		3 = P 7 = T
		*/
		console.log(char_cf + " " + char_cf_temp)

		switch (char_cf_temp) {
			case "0":
				if (char_cf == "L" || char_cf == char_cf_temp)
					return true;
				break;

			case "1":
				if (char_cf == "M" || char_cf == char_cf_temp)
					return true;
				break;

			case "2":
				if (char_cf == "N" || char_cf == char_cf_temp)
					return true;
				break;

			case "3":
				if (char_cf == "P" || char_cf == char_cf_temp)
					return true;
				break;

			case "4":
				if (char_cf == "Q" || char_cf == char_cf_temp)
					return true;
				break;

			case "5":
				if (char_cf == "R" || char_cf == char_cf_temp)
					return true;
				break;

			case "6":
				if (char_cf == "S" || char_cf == char_cf_temp)
					return true;
				break;

			case "7":
				if (char_cf == "T" || char_cf == char_cf_temp)
					return true;
				break;

			case "8":
				if (char_cf == "U" || char_cf == char_cf_temp)
					return true;
				break;

			case "9":
				if (char_cf == "V" || char_cf == char_cf_temp)
					return true;
				break;

			default:
				console.log("sono qui")
				break;
		}

		return false;
	}

	function consultaDOCUMENTO(file) {
		settings = window_center(580, 950);
		settings += ",resizable=yes";

		window.open(file, 'documento', settings);
		if (win.window.focus) { win.window.focus(); }
	}

	function eliminaDOCUMENTO() {
		$("#doc_button").hide();
		$("#doc_loader").show();
	}
</script>
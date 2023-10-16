<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave = get_cookieuserFront();

$fldidgen_utente = verifica_eutente($chiave);
$fldidutente = front_get_db_value("select idsso_anagrafica_utente from " . FRONT_ESONAME . ".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if (empty($fldidutente) || empty($fldidgen_utente))
	die("Attenzione! sessione scaduta");

$pidsibada_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
$fldidsibada_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum_orari WHERE idsibada_curriculum='$pidsibada_curriculum'");




/*
echo "RAND: ".rand()."<br>";
echo "IDUTENTE: ".$fldidutente;
echo "<br>";
echo "ID_CURRICULUM: ".$pidsibada_curriculum;
*/


$sSQL = "SELECT * FROM sibada_curriculum_orari WHERE idsibada_curriculum='$pidsibada_curriculum'";
$db->query($sSQL);

$res = $db->next_record();
while ($res) {

	$fldreferenza_lm = $db->f('lunedi_mattina');
	$fldreferenza_lp = $db->f('lunedi_pomeriggio');
	$fldreferenza_ls = $db->f('lunedi_sera');

	$fldreferenza_mm = $db->f('martedi_mattina');
	$fldreferenza_mp = $db->f('martedi_pomeriggio');
	$fldreferenza_ms = $db->f('martedi_sera');

	$fldreferenza_mem = $db->f('mercoledi_mattina');
	$fldreferenza_mep = $db->f('mercoledi_pomeriggio');
	$fldreferenza_mes = $db->f('mercoledi_sera');

	$fldreferenza_gm = $db->f('giovedi_mattina');
	$fldreferenza_gp = $db->f('giovedi_pomeriggio');
	$fldreferenza_gs = $db->f('giovedi_sera');

	$fldreferenza_vm = $db->f('venerdi_mattina');
	$fldreferenza_vp = $db->f('venerdi_pomeriggio');
	$fldreferenza_vs = $db->f('venerdi_sera');

	$fldreferenza_sm = $db->f('sabato_mattina');
	$fldreferenza_sp = $db->f('sabato_pomeriggio');
	$fldreferenza_ss = $db->f('sabato_sera');

	$fldreferenza_dm = $db->f('domenica_mattina');
	$fldreferenza_dp = $db->f('domenica_pomeriggio');
	$fldreferenza_ds = $db->f('domenica_sera');
	$fldnote = $db->f('note');
	



	$res = $db->next_record();
}

if (get_param("_conferma")) {
	//print_r_formatted($_POST);

	$pcapacita_professionali = get_param("capacita_professionali");
	$pcapacita_professionali = db_string($pcapacita_professionali);
	$preferenze_lm = get_param("referenze_lm");
	$preferenze_lp = get_param("referenze_lp");
	$preferenze_ls = get_param("referenze_ls");

	$preferenze_mm = get_param("referenze_mm");
	$preferenze_mp = get_param("referenze_mp");
	$preferenze_ms = get_param("referenze_ms");

	$preferenze_mem = get_param("referenze_mem");
	$preferenze_mep = get_param("referenze_mep");
	$preferenze_mes = get_param("referenze_mes");

	$preferenze_gm = get_param("referenze_gm");
	$preferenze_gp = get_param("referenze_gp");
	$preferenze_gs = get_param("referenze_gs");

	$preferenze_vm = get_param("referenze_vm");
	$preferenze_vp = get_param("referenze_vp");
	$preferenze_vs = get_param("referenze_vs");

	$preferenze_sm = get_param("referenze_sm");
	$preferenze_sp = get_param("referenze_sp");
	$preferenze_ss = get_param("referenze_ss");

	$preferenze_dm = get_param("referenze_dm");
	$preferenze_dp = get_param("referenze_dp");
	$preferenze_ds = get_param("referenze_ds");
	$pnote = get_param("note");
	$pnote = db_string($pnote);

	$pauto = get_param("auto");

	if (!empty($fldidsibada_curriculum)) 
	{
		$update = "UPDATE sibada_curriculum_orari
			SET
			  lunedi_mattina = '$preferenze_lm',
			  lunedi_pomeriggio = '$preferenze_lp',
			  lunedi_sera = '$preferenze_ls',
			  martedi_mattina = '$preferenze_mm',
			  martedi_pomeriggio = '$preferenze_mp',
			  martedi_sera = '$preferenze_ms',
			  mercoledi_mattina = '$preferenze_mem',
			  mercoledi_pomeriggio = '$preferenze_mep',
			  mercoledi_sera = '$preferenze_mes',
			  giovedi_mattina = '$preferenze_gm',
			  giovedi_pomeriggio = '$preferenze_gp',
			  giovedi_sera = '$preferenze_gs',
			  venerdi_mattina = '$preferenze_vm',
			  venerdi_pomeriggio = '$preferenze_vp',
			  venerdi_sera = '$preferenze_vs',
			  sabato_mattina = '$preferenze_sm',
			  sabato_pomeriggio = '$preferenze_sp',
			  sabato_sera = '$preferenze_ss',
			  domenica_mattina = '$preferenze_dm',
			  domenica_pomeriggio = '$preferenze_dp',
			  domenica_sera = '$preferenze_ds',
			  note= '$pnote'
			WHERE
			  idsibada_curriculum = '$pidsibada_curriculum'";
		$db->query($update);

	} 
	else {
		$insert = "INSERT INTO sibada_curriculum_orari(idsibada_curriculum,lunedi_mattina,lunedi_pomeriggio,lunedi_sera,martedi_mattina,martedi_pomeriggio,martedi_sera,mercoledi_mattina,mercoledi_pomeriggio,mercoledi_sera,giovedi_mattina,giovedi_pomeriggio,giovedi_sera,venerdi_mattina,venerdi_pomeriggio,venerdi_sera,sabato_mattina,sabato_pomeriggio,sabato_sera,domenica_mattina,domenica_pomeriggio,domenica_sera,note) VALUES('$pidsibada_curriculum','$preferenze_lm','$preferenze_lp','$preferenze_ls','$preferenze_mm','$preferenze_mp','$preferenze_ms','$preferenze_mem','$preferenze_mep','$preferenze_mes','$preferenze_gm','$preferenze_gp','$preferenze_gs','$preferenze_vm','$preferenze_vp','$preferenze_vs','$preferenze_sm','$preferenze_sp','$preferenze_ss','$preferenze_dm','$preferenze_dp','$preferenze_ds','$pnote')";
		$db->query($insert);
	}

	$update = "UPDATE sibada_curriculum SET capacita_professionali='$pcapacita_professionali' WHERE idsibada_curriculum='$pidsibada_curriculum'";
	$db->query($update);

	$delete = "DELETE FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum='$pidsibada_curriculum'";
	$db->query($delete);

	$pauto = get_param("auto");
	$pmultidisp = get_param("_multidisp");
	if (!empty($pmultidisp)) {
		$aDISPONIBILITA = explode(",", $pmultidisp);

		if (!empty($aDISPONIBILITA)) {
			foreach ($aDISPONIBILITA as $iddisp) {
				$insert = "INSERT INTO sibada_curriculum_disponibilita(idsibada_curriculum,idsibada_disponibilita,flag_auto) VALUES('$pidsibada_curriculum','$iddisp','$pauto')";
				$db->query($insert);
			}
		}
	}

	updateCVSiBada($pidsibada_curriculum);
	$alert_insert=true;
	echo "<script>parent.loadSTEP(5)</script>";
}

if (!empty($pidsibada_curriculum)) {
	$fldcapacita_professionali = get_db_value("SELECT capacita_professionali FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
	$aDISPONIBILITA = db_fill_array("SELECT idsibada_curriculum_disponibilita,idsibada_disponibilita FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum='$pidsibada_curriculum'");
	$fldauto= get_db_value("SELECT flag_auto FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum='$pidsibada_curriculum'");
}

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">

<head>
	<title>Sibada - Disponibilità</title>
	<?php echo get_importazioni_sibada_header(); ?>
</head>

<body class="push-body bg-white" data-ng-app="ponmetroca">
	<div class="body_wrapper push_container clearfix" id="page_top">

		<main id="main_container">

			<?php
			if (empty($pidsibada_curriculum)) {
				echo "<br><br><br>" . (get_alert(0, "Attenzione! per procedere con la compilazione del Curriculum Vitae è necessario iniziale dalla sezione \"Dati di contatto\"."));
				echo get_importazioni_sibada();
				die;
			}

			if ($alert_delete)
				echo (get_alert(4, "Esperienza lavorativa eliminata con successo."));
			if ($alert_insert)
				echo (get_alert(4, "Esperienza lavorativa inserita con successo."));
			if ($alert_update)
				echo (get_alert(4, "Esperienza lavorativa modificata con successo."));
			?>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step4" style="display:none;"></div>

					<form id="step4" method="post" enctype="multipart/form-data" action="esibada_curriculum_step4.php"
						class="form-horizontal" onSubmit="return multiDISP()">

						<div class="row">
							<div id="title_disponibilita" class="form-group col-sm-8 offset-sm-2">
							</div>
						</div>

						<div class="row">
							<div class="bootstrap-select-wrapper form-group col-sm-4 ">
								<label for="multi_disponibilita">Sono disponibile per i seguenti lavori</label>
								<select id="multi_disponibilita" name="multi_disponibilita"
									title="Scegli una o più opzioni" multiple="true" data-multiple-separator="">
									<?php
									$sSQL = "SELECT * FROM sibada_disponibilita";
									$db->query($sSQL);
									$res = $db->next_record();
									while ($res) {
										$fldidsibada_disponibilita = $db->f("idsibada_disponibilita");
										$flddescrizione = $db->f("descrizione");

										if (in_array($fldidsibada_disponibilita, $aDISPONIBILITA))
											echo '<option value="' . $fldidsibada_disponibilita . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $flddescrizione . '</span></span>"></option>';
										else
											echo '<option value="' . $fldidsibada_disponibilita . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $flddescrizione . '</span></span>"></option>';

										$res = $db->next_record();
									}
									?>
								</select>
							</div>

							<div class="bootstrap-select-wrapper form-group col-sm-4">
								<label for="auto">Sono automunito:</label>
								<select id="auto" name="auto">

									<?php
									$aSCELTE = array(1 => "SI", 2 => "NO");

									foreach ($aSCELTE as $key => $value) {
										if ($fldauto == $value)
											echo '<option value="' . $value . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
										else
											echo '<option value="' . $value . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';

									}
									?>
								</select>
							</div>

						</div>

						<h5>
							<center><b>Sono disponibile nei seguenti giorni ed orari</b></center>
						</h5>
						<table class="table table-sm">
							<tr>
								<th><b>Giorni</b></th>
								<th><b>Mattina</b></th>
								<th><b>Pomeriggio</b></th>
								<th><b>Notte</b></th>
							</tr>
							<tr>
								<td><b>Lunedì</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_lm" name="referenze_lm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_lm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';

											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_lp" name="referenze_lp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_lp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_ls" name="referenze_ls">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_ls == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Martedì</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_mm" name="referenze_mm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_mm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';

											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_mp" name="referenze_mp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_mp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';

											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_ms" name="referenze_ms">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_ms == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';

											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Mercoledì</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_mem" name="referenze_mem">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_mem == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_mep" name="referenze_mep">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");
											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_mep == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_mes" name="referenze_mes">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");
											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_mes == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Giovedì</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_gm" name="referenze_gm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");
											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_gm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}

											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_gp" name="referenze_gp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_gp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_gs" name="referenze_gs">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_gs == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Venerdì</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_vm" name="referenze_vm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_vm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_vp" name="referenze_vp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_vp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_vs" name="referenze_vs">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_vs == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Sabato</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_sm" name="referenze_sm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_sm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_sp" name="referenze_sp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_sp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_ss" name="referenze_ss">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_ss == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
							<tr>
								<td><b>Domenica</b></td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_dm" name="referenze_dm">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_dm == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_dp" name="referenze_dp">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_dp == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select  class="form-control form-control-sm" id="referenze_ds" name="referenze_ds">

											<?php
											$aSCELTE = array(1 => "SI", 2 => "NO");

											foreach ($aSCELTE as $key => $value) {
												if ($fldreferenza_ds == $key)
													echo '<option value="' . $key . '" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
												else
													echo '<option value="' . $key . '" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>' . $value . '</span></span>"></option>';
											}
											?>
										</select>
									</div>
								</td>
							</tr>
						</table>
                        <div class="form-group border">
							<label for="note">Note</label>
							<textarea class="form-control" id="note" name="note"  rows="3"><?php echo $fldnote; ?></textarea>
						</div>
						<center>
							<button class="btn btn-primary" type="button"
								onclick="parent.loadSTEP(3);">Indietro</button>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma"
								value="true">Continua</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsibada_curriculum; ?>">
						<input type="hidden" id="_multidisp" name="_multidisp" value="">

					</form>
				</main>
			</section>
		</main>
	</div>

	<div class="it-example-modal">
		<div class="modal" tabindex="-1" role="dialog" id="modal_delete">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Attenzione!
						</h5>
					</div>
					<div class="modal-body">
						<p>Selezionare una o più opzioni nel campo "Sono disponibile per i seguenti lavori".</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-outline-primary btn-sm" type="button" onclick=""
							data-dismiss="modal">Continua</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>

	function multiDISP() {
		var foo1 = [];

		$('#multi_disponibilita :selected').each(function (i, selected) {
			foo1[i] = $(selected).val();
		});

		if (foo1 != '')
			$("#_multidisp").val(foo1);
		else {
			$('#modal_delete').modal('show');
			return false;
		}
	}

</script>
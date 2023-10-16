<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave = get_cookieuserFront();

$fldidgen_utente = verifica_eutente($chiave);
$fldidutente = front_get_db_value("SELECT idsso_anagrafica_utente from " . FRONT_ESONAME . ".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if (empty($fldidutente) || empty($fldidgen_utente))
	die("Attenzione! sessione scaduta");

$pidsibada_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");

/*
echo "RAND: ".rand()."<br>";
echo "IDUTENTE: ".$fldidutente;
echo "<br>";
echo "ID_CURRICULUM: ".$pidsibada_curriculum;
*/

if (get_param("_conferma")) {
	$fldcognome = get_param("cognome");
	$fldcognome = db_string($fldcognome);
	$fldcognome = strtoupper($fldcognome);

	$fldnome = get_param("nome");
	$fldnome = db_string($fldnome);
	$fldnome = strtoupper($fldnome);

	$fldidamb_comune_residenza = get_param("idamb_comune_residenza");
	if (!empty($fldidamb_comune_residenza)) {
		$fldcomune_residenza = get_db_value("SELECT comune FROM " . DBNAME_A . ".comune WHERE idcomune='$fldidamb_comune_residenza'");
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
	$fldcellulare = db_string($fldcellulare);

	$fldemail = get_param("email");
	$fldemail = db_string($fldemail);

	$update = "UPDATE sso_anagrafica_utente SET 
		cognome='$fldcognome',
		nome='$fldnome',
		idamb_comune_residenza='$fldidamb_comune_residenza',
		citta='$fldcomune_residenza',
		prov='$fldprov_residenza',
		indirizzo='$fldindirizzo',
		civico='$fldcivico',
		cellulare='$fldcellulare',
		email='$fldemail' 
		WHERE idutente='$fldidutente'";
	$db->query($update);

	$update = "UPDATE " . FRONT_ESONAME . ".gen_utente SET
		cognome='$fldcognome',
		nome='$fldnome',
		idgen_comune='$fldidamb_comune_residenza',
		citta='$fldcomune_residenza',
		provincia='$fldprov_residenza',
		indirizzo='$fldindirizzo',
		civico='$fldcivico',
		cellulare='$fldcellulare',
		email='$fldemail' 
		WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($update);

	if (empty($pidsibada_curriculum)) {
		$oggi = date("Y-m-d");
		$adesso = date("H:i:s");
		$insert = "INSERT sibada_curriculum(idutente,data_inserimento,ora_inserimento) VALUES('$fldidutente','$oggi','$adesso')";
		$db->query($insert);
		$pidsibada_curriculum = mysql_insert_id($db->link_id());
	}

	if (!empty($pidsibada_curriculum)) {
		$pnome_originale = basename($_FILES["foto"]["name"]);
		if (!empty($pnome_originale)) {
			$pnome_originale = explode(".", $pnome_originale);
			$fldestensione = $pnome_originale[count($pnome_originale) - 1];

			$fldpath = "../documenti/";
			$fldnome_allegato_name = md5("FOTO_" . $fldidutente . '_' . date("Ymd") . date("Hi")) . "." . $fldestensione;

			$fldestensione = strtolower($fldestensione);

			if ($fldestensione == "jpg" || $fldestensione == "jpeg" || $fldestensione == "png") {
				copy($_FILES["foto"]["tmp_name"], $fldpath . $fldnome_allegato_name);
				if (file_exists($fldpath . $fldnome_allegato_name)) {
					$flddata_richiesta = date("Y-m-d");
					$sSQL = "UPDATE sibada_curriculum SET path_file='$fldpath', filename='$fldnome_allegato_name' WHERE idsibada_curriculum='$pidsibada_curriculum'";
					$db->query($sSQL);
				}
			} else
				$alert_estensione_file = true;
		}
	}

	updateCVSiBada($pidsibada_curriculum);

	echo "<script>parent.loadSTEP(2)</script>";
}

$beneficiario = new Beneficiario($fldidutente);

if (!empty($pidsibada_curriculum)) {
	$fldpath_file = get_db_value("SELECT path_file FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
	$fldfilename = get_db_value("SELECT filename FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
	if (file_exists($fldpath_file . $fldfilename)) {
		$file_avatar = $fldpath_file . $fldfilename;
	} else {
		$file_avatar = "./foto/avatar.png";
	}
} else
	$file_avatar = "./foto/avatar.png";
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">

<head>
	<title>Sibada - Dati utente</title>
	<?php echo get_importazioni_sibada_header(); ?>
</head>

<body class="push-body bg-white" data-ng-app="ponmetroca">
	<div class="body_wrapper push_container clearfix" id="page_top">

		<main id="main_container">
			<?php
			if ($alert_estensione_file)
				echo (get_alert(0, "Attenzione! è possibile caricare file in formato .png, .jpg o .jpeg."));
			?>
			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step1" style="display:none;"></div>

					<form id="step1" method="post" enctype="multipart/form-data" action="esibada_curriculum_step1.php"
						class="form-horizontal">

						<div class="row text-center">
							<div class="form-group col-sm-4 offset-sm-4">
								<div class="avatar size-xxl">
									<img src="<?php echo $file_avatar; ?>" alt="Avatar">
								</div>
							</div>
						</div>

						<div class="row mt-5">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<label class="form-label active" for="cognome">Cognome*</label>
									<input type="text" class="form-control" id="cognome" name="cognome"
										style="text-transform: uppercase;"
										value="<?php echo $beneficiario->cognome; ?>">
								</div>
							</div>

							<div class="form-group col-4">
								<label class="form-label active" for="nome">Nome*</label>
								<input type="text" class="form-control" id="nome" name="nome"
									style="text-transform: uppercase;" value="<?php echo $beneficiario->nome; ?>">
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-2">
								<div>
									<div class="bootstrap-select-wrapper">
										<label for="idamb_comune_residenza">Comune di residenza*</label>
										<select title="Scegli il comune di residenza" id="idamb_comune_residenza"
											name="idamb_comune_residenza" data-live-search="true"
											data-live-search-placeholder="Cerca">
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

												if ($fldidcomune_res == $beneficiario->idamb_comune_residenza)
													echo '<option value="' . $fldidcomune_res . '" selected>' . $fldcomune . '</option>';
												else
													echo '<option value="' . $fldidcomune_res . '">' . $fldcomune . '</option>';

												$next_record = $db->next_record();
											}
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group col-2">
								<label class="form-label active" for="prov_residenza">Provincia*</label>
								<input type="text" class="form-control" style="text-transform: uppercase;"
									id="prov_residenza" name="prov_residenza" value="<?php echo $beneficiario->prov; ?>"
									maxlength="2">
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-2">
								<div>
									<label class="form-label active" for="indirizzo">Indirizzo
										residenza/domicilio*</label>
									<input type="text" class="form-control" style="text-transform: uppercase;"
										id="indirizzo" name="indirizzo" value="<?php echo $beneficiario->indirizzo; ?>">
								</div>
							</div>
							<div class="form-group col-sm-2">
								<div>
									<label class="form-label active" for="civico">Civico*</label>
									<input type="text" class="form-control" style="text-transform: uppercase;"
										id="civico" name="civico" maxlength="16"
										value="<?php echo $beneficiario->civico; ?>">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-2">
								<div>
									<label class="form-label active" for="cellulare">Cellulare*</label>
									<input type="text" class="form-control" style="text-transform: uppercase;"
										id="cellulare" name="cellulare" maxlength="15"
										value="<?php echo $beneficiario->cellulare; ?>">
								</div>
							</div>
							<div class="form-group col-4">
								<label class="form-label active" for="email">E-mail*</label>
								<input type="text" class="form-control" id="email" name="email"
									value="<?php echo $beneficiario->email; ?>">
							</div>
						</div>

						 <div class="row">
											<div class="form-group col-sm-8 offset-sm-2">
												<div>
													<input type="file" class="form-control" id="foto" name="foto">
													<small id="" class="form-text text-muted">Foto profilo (.png, .jpg,
														.jpeg)</small>
												</div>
											</div>
										</div> 
						<center>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma"
								value="true">Continua</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsibada_curriculum; ?>">

					</form>

				</main>
			</section>
		</main>
	</div>

	<?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>

	$("#step1").submit(function (event) {

		var errors = 0;
		var string_errors = "";

		var cognome = $("#cognome").val();
		if (cognome == "") {
			string_errors = string_errors + "- Cognome; <br>";
			errors++;
		}

		var nome = $("#nome").val();
		if (nome == "") {
			string_errors = string_errors + "- Nome; <br>";
			errors++;
		}

		var idamb_comune_residenza = $("#idamb_comune_residenza").val();
		if (idamb_comune_residenza == "") {
			string_errors = string_errors + "- Comune di residenza; <br>";
			errors++;
		}

		var prov_residenza = $("#prov_residenza").val();
		if (prov_residenza == "") {
			string_errors = string_errors + "- Provincia; <br>";
			errors++;
		}

		var indirizzo = $("#indirizzo").val();
		if (indirizzo == "") {
			string_errors = string_errors + "- Indirizzo residenza/domicilio; <br>";
			errors++;
		}

		var civico = $("#civico").val();
		if (civico == "") {
			string_errors = string_errors + "- Civico; <br>";
			errors++;
		}

		var cellulare = $("#cellulare").val();
		if (cellulare == "") {
			string_errors = string_errors + "- Cellulare; <br>";
			errors++;
		}

		var email = $("#email").val();
		if (email == "") {
			string_errors = string_errors + "- e-mail; <br>";
			errors++;
		}

		if (errors > 0) {
			visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>" + string_errors);
			return false;
		}
	});


	function visualizzaAlert(alert_message) {
		$("#alert_step1").show();
		$("#alert_step1").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">' + alert_message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
		window.scrollTo(0, 0);
	}

</script>
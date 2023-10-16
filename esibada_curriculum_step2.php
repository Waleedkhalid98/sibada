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



/*
echo "RAND: ".rand()."<br>";
echo "IDUTENTE: ".$fldidutente;
echo "<br>";
echo "ID_CURRICULUM: ".$pidsibada_curriculum;
*/

if (get_param("_conferma")) {
	//print_r_formatted($_POST);
	$pinserimento = get_param("inserimento");
	
	if (!empty($pinserimento)) {
		$pqualifica = get_param("qualifica");
		$pqualifica = db_string($pqualifica);

		$pdatore_lavoro = get_param("datore_lavoro");
		$pdatore_lavoro = db_string($pdatore_lavoro);

		$pcomune_lavoro = get_param("comune_lavoro");
		$pcomune_lavoro = db_string($pcomune_lavoro);
		$pcomune_lavoro = strtoupper($pcomune_lavoro);

		$pprov_lavoro = get_param("prov_lavoro");
		$pprov_lavoro = db_string($pprov_lavoro);
		$pprov_lavoro = strtoupper($pprov_lavoro);

		$pdata_inizio = get_param("data_inizio");
		$pdata_inizio = invertidata($pdata_inizio, "-", "/", 1);

		$pdata_fine = get_param("data_fine");
		$pdata_fine = invertidata($pdata_fine, "-", "/", 1);

		$pflag_corrente = get_param("flag_corrente");
		if (!empty($pflag_corrente)) {
			$pdata_fine = null;
			$pflag_corrente = 1;
		} else
			$pflag_corrente = 0;

		$pdescrizione_lavoro = get_param("descrizione_lavoro");
		$pdescrizione_lavoro = db_string($pdescrizione_lavoro);

		$insert = "INSERT INTO sibada_curriculum_lavoro(
			idsibada_curriculum,
			qualifica,
			datore_lavoro,
			comune_lavoro,
			prov_lavoro,
			data_inizio,
			data_fine,
			flag_corrente,
			descrizione_lavoro
		) VALUES(
			'$pidsibada_curriculum',
			'$pqualifica',
			'$pdatore_lavoro',
			'$pcomune_lavoro',
			'$pprov_lavoro',
			'$pdata_inizio',
			'$pdata_fine',
			'$pflag_corrente',
			'$pdescrizione_lavoro'
		)";

		$db->query($insert);
		updateCVSiBada($pidsibada_curriculum);
		$alert_insert = true;
		
	} else {
		updateCVSiBada($pidsibada_curriculum);
		echo "<script>parent.loadSTEP(3)</script>";
	}
}

if (get_param("_update")) {
	$alert_update = true;
}

if (get_param("_delete")) {
	$pidsibada_curriculum_lavoro_del = get_param("idesperienza_delete");
	if (!empty($pidsibada_curriculum_lavoro_del)) {
		$delete = "DELETE FROM sibada_curriculum_lavoro WHERE idsibada_curriculum_lavoro='$pidsibada_curriculum_lavoro_del'";
		$db->query($delete);

		updateCVSiBada($pidsibada_curriculum);

		$alert_delete = true;
	}
}

if (!empty($pidsibada_curriculum)) {
	$nESPERIENZE = get_db_value("SELECT COUNT(*) FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsibada_curriculum'");
	if ($nESPERIENZE > 0) {
		$display_esperienza = "display:none;";
		$display_btn_esperienza = "";
		$pinserimento=0;
	} else {
		$display_esperienza = "";
		$display_btn_esperienza = "display:none;";
		$pinserimento=1;
	}
}

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

					<div id="alert_step2" style="display:none;"></div>

					<form id="step2" method="post" enctype="multipart/form-data" action="esibada_curriculum_step2.php"
						class="form-horizontal">

						<div id="lista_esperienze" class="mb-4">
							<?php
							$sSQL = "SELECT * FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsibada_curriculum'";
							$db->query($sSQL);
							$res = $db->next_record();
							while ($res) {
								$fldidsibada_curriculum_lavoro = $db->f("idsibada_curriculum_lavoro");
								$fldqualifica = $db->f("qualifica");
								$flddatore_lavoro = $db->f("datore_lavoro");
								$fldcomune_lavoro = $db->f("comune_lavoro");
								$fldprov_lavoro = $db->f("prov_lavoro");

								$flddata_inizio = $db->f("data_inizio");
								$flddata_inizio = invertidata($flddata_inizio, "/", "-", 2);

								$flddata_fine = $db->f("data_fine");
								$flddata_fine = invertidata($flddata_fine, "/", "-", 2);

								$fldflag_corrente = $db->f("flag_corrente");
								if ($fldflag_corrente == 1) {
									$checked_corrente = "checked";
									$string_periodo = 'dal ' . $flddata_inizio . ' ad oggi';
								} else {
									$checked_corrente = "";
									$string_periodo = 'dal ' . $flddata_inizio . ' al ' . $flddata_fine;
								}

								echo '<div class="col-md-12">							
					                        <div class="card card-img rounded shadow" style="height:140px;">
					                            <div class="card-body">
				                                    <div class="card-text"> 
				                                    	<div class="d-flex align-content-center flex-wrap">                                
					                                        <div class="text-left col-8">
					                                            <p>' . $fldqualifica . '<br>presso ' . $flddatore_lavoro . ' - ' . $fldcomune_lavoro . ' (' . $fldprov_lavoro . ')<br>' . $string_periodo . '<br></p>
					                                        </div>
					                                        <div class="col-1"></div>
					                                        <div class="text-right col-3">
					                                        	<button type="button" class="btn btn-xs btn-outline-warning" onclick="updateESPERIENZA(' . $fldidsibada_curriculum_lavoro . ')">
					                                        	<svg class="icon icon-xs icon-warning">
												            		<use xlink:href="static/img/sprite.svg#it-pencil"></use>
												            	</svg>
													            &nbsp;Modifica
													            </button>
	  															<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteESPERIENZA(' . $fldidsibada_curriculum_lavoro . ')">
	  															<svg class="icon icon-xs icon-danger">
												            		<use xlink:href="static/img/sprite.svg#it-delete"></use>
												            	</svg>
													            &nbsp;Elimina</button>
					                                        </div>
					                                    </div>
					                                </div>
					                            </div>
					                        </div>
										</div>
									<br>';

								$res = $db->next_record();
							}
							?>
						</div>

						<div id="div_esperienza" style="<?php echo $display_esperienza; ?>">
							<div class="row">
								<div id="title_esperienza" class="form-group col-sm-8 offset-sm-2">
								</div>
							</div>
							<br>

							<div class="row">
								<div class="form-group col-sm-4 offset-sm-2">
									<div>
										<input type="text" class="form-control" id="qualifica" name="qualifica"
											placeholder="Inserire la qualifica" value="">
										<label for="qualifica">Qualifica*</label>
									</div>
								</div>

								<div class="form-group col-sm-4">
									<input type="text" class="form-control" id="datore_lavoro" name="datore_lavoro" placeholder="Inserire la denominazione" value="<?php echo $flddatore_lavoro; ?>" >
									<label for="datore_lavoro">Datore di lavoro(facoltativo)</label>
								</div>
								
							</div>


							<div class="row">
								<div class="form-group col-sm-4 offset-sm-2">
									<div>
										<input type="text" class="form-control" id="comune_lavoro" name="comune_lavoro"
											placeholder="Inserire la Città" value="">
										<label for="comune_lavoro">Città*</label>
									</div>
								</div>

								<div class="form-group col-4">
									<input type="text" class="form-control" id="prov_lavoro" name="prov_lavoro"
										placeholder="Inserire la provincia" value="" maxlength="2">
									<label for="prov_lavoro">Provincia*</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-4 offset-sm-2">
									<div>
										<input type="text" class="form-control" id="data_inizio" name="data_inizio"
											value="" placeholder="Inserire data di inizio dell'esperienza">
										<label for="data_inizio">Data di inizio*</label>
									</div>
								</div>

								<div class="form-group col-4">
									<input type="text" class="form-control" id="data_fine" name="data_fine" value=""
										placeholder="Inserire data di fine dell'esperienza">
									<label for="data_fine">Data di fine*</label>
								</div>

								<div>
									<div class="form-check">
										<input id="flag_corrente" name="flag_corrente" type="checkbox">
										<label for="flag_corrente">ad oggi</label>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-8 offset-sm-2">
									<textarea id="descrizione_lavoro" name="descrizione_lavoro" rows="4"
										style="width:100%" class="form-control input-sm border" maxlength=""
										placeholder="Inserire la descrizione delle attività svolte per questa esperienza lavorativa"></textarea>
									<label for="descrizione_lavoro">Di cosa ti sei occupato?</label>
								</div>
							</div>
						</div>

						<div id="btn_esperienza" class="text-right" style="<?php echo $display_btn_esperienza; ?>">
							<button id="btn_add_esperienza" class="btn btn-xs btn-primary" type="button">Aggiungi
								esperienza lavorativa</button>
						</div>

						<div id="btn_annulla_esperienza" class="text-right" style="display:none;">
							<button id="btn_canc_esperienza" class="btn btn-xs btn-primary" type="button">Annulla
								inserimento</button>
						</div>

						<br>

						<center>
							<button class="btn btn-primary" type="button"
								onclick="parent.loadSTEP(1);">Indietro</button>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma"
								value="true">Continua</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsibada_curriculum; ?>">
						<input type="hidden" id="idesperienza_delete" name="idesperienza_delete" value="">
						<input type="hidden" id="inserimento" name="inserimento" value="<?php echo $pinserimento; ?>">
						<input type="hidden" id="no_esp" name="no_esp" value="">
						<input type="hidden" id="n_esperienze" name="n_esperienze" value="<?php echo $nESPERIENZE; ?>">
						

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
						<p>Vuoi eliminare questa voce?<br>L'operazione non potrà essere annullata.</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-outline-primary btn-sm" type="button" onclick="annullaDEL_ESP()"
							data-dismiss="modal">Annulla</button>
						<button class="btn btn-primary btn-sm" type="button" onclick="deleteESP()"
							data-dismiss="modal">Continua</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" tabindex="-1" role="dialog" id="modal_noesperienze">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Attenzione!
						</h5>
					</div>
					<div class="modal-body">
						<p>Non hai inserito nessuna esperienza lavorativa.<br>Se puoi inseriscine almeno una.</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-outline-primary btn-sm" type="button"
							data-dismiss="modal">Annulla</button>
						<button class="btn btn-primary btn-sm" type="button" onclick="continuaNOESP()"
							data-dismiss="modal">Non ho esperienze lavorative</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>

	$('#data_inizio').datepicker({
		inputFormat: ["dd/MM/yyyy"],
		outputFormat: 'dd/MM/yyyy',
	});

	$('#data_fine').datepicker({
		inputFormat: ["dd/MM/yyyy"],
		outputFormat: 'dd/MM/yyyy',
	});

	$("#btn_add_esperienza").click(function () {
		$("#div_esperienza").show();
		$("#btn_esperienza").hide();
		$("#btn_add_esperienza").hide();
		$("#lista_esperienze").hide();
		$("#btn_annulla_esperienza").show();
		$("#btn_canc_esperienza").show();
		$("#title_esperienza").html("Inserimento di una nuova attività lavorativa");
		$("#qualifica").val("");
		$("#datore_lavoro").val("");
		$("#idreferenze").val("");
		$("#comune_lavoro").val("");
		$("#prov_lavoro").val("");
		$("#data_inizio").val("");
		$("#data_fine").val("");
		$("#inserimento").val("1");
	});

	$("#btn_canc_esperienza").click(function () {
		$("#div_esperienza").hide();
		$("#btn_esperienza").show();
		$("#btn_add_esperienza").show();
		$("#btn_annulla_esperienza").hide();
		$("#btn_canc_esperienza").hide();
		$("#lista_esperienze").show();
		$("#title_esperienza").html("");
		$("#qualifica").val("");
		$("#datore_lavoro").val("");
		$("#idreferenze").val("");
		$("#comune_lavoro").val("");
		$("#prov_lavoro").val("");
		$("#data_inizio").val("");
		$("#data_fine").val("");
		$("#inserimento").val("0");
	});

	
	function updateESPERIENZA(idesperienza) {
		settings = window_center(700, 950);
		settings += ",resizable=yes";

		var page = "esibada_curriculum_step2_esperienza.php";
		var params = "?_id=" + idesperienza;

		window.open(page + params, 'modifica_esperienza', settings);
		if (win.window.focus) { win.window.focus(); }
	}

	function deleteESPERIENZA(idesperienza) {
		$("#idesperienza_delete").val(idesperienza)
		$('#modal_delete').modal('show');
	}

	function deleteESP() {
		var idesperienza = $("#idesperienza_delete").val()

		var page = "esibada_curriculum_step2.php";
		var params = "?_delete=true&idesperienza_delete=" + idesperienza;
		window.location = (page + params)
	}

	function annullaDEL_ESP() {
		$("#idesperienza_delete").val("")
	}

	$("#step2").submit(function (event) {

		var no_esp = $("#no_esp").val();
		if (no_esp == "1") {
			parent.loadSTEP(3);
		}
		else {
			var errors = 0;
			var string_errors = "";

			var inserimento = $("#inserimento").val();
			if (inserimento == "1") {
				var qualifica = $("#qualifica").val();
				if (qualifica == "") {
					string_errors = string_errors + "- Qualifica; <br>";
					errors++;
				}

				/*
				var datore_lavoro = $("#datore_lavoro").val();
				if (datore_lavoro == "") {
					string_errors = string_errors + "- Datore di lavoro; <br>";
					errors++;
				}
				*/

				/*
				var idreferenze = $("#idreferenze").val();
				if (idreferenze == "") {
					string_errors = string_errors + "- Referenze; <br>";
					errors++;
				}
				*/

				var comune_lavoro = $("#comune_lavoro").val();
				if (comune_lavoro == "") {
					string_errors = string_errors + "- Città; <br>";
					errors++;
				}

				var prov_lavoro = $("#prov_lavoro").val();
				if (prov_lavoro == "") {
					string_errors = string_errors + "- Provincia; <br>";
					errors++;
				}

				var data_inizio = $("#data_inizio").val();
				if (data_inizio == "") {
					string_errors = string_errors + "- Data di inizio; <br>";
					errors++;
				}

				var data_fine = $("#data_fine").val();
				if (data_fine == "") {
					if (!$('#flag_corrente').is(":checked")) {
						string_errors = string_errors + "- Data di fine; <br>";
						errors++;
					}
				}
				else {
					if ($('#flag_corrente').is(":checked")) {
						string_errors = string_errors + "- Indicare la data di fine o spuntare la casella \"ad oggi\"; <br>";
						errors++;
					}
				}

				var page = "sibada_action.php";
				var params = "_user=<?php echo $chiave; ?>&_action=nesperienze_lavorative";
				var loader = dhtmlxAjax.postSync(page, params);
				myParam = loader.xmlDoc.responseText;
				if (myParam == "0") {
					var qualifica = $("#qualifica").val();
					var datore_lavoro = $("#datore_lavoro").val();
					var comune_lavoro = $("#comune_lavoro").val();
					var prov_lavoro = $("#prov_lavoro").val();
					var data_inizio = $("#data_inizio").val();
					var data_fine = $("#data_fine").val();

					if (qualifica == '' && datore_lavoro == '' && comune_lavoro == '') {
						$('#modal_noesperienze').modal('show');
						return false;
					}
					else {
						$("#inserimento").val("1");
					}
				}

				if (errors > 0) {
					visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>" + string_errors);
					return false;
				}
			}
			
		}
	});

	function continuaNOESP() {
		$("#no_esp").val("1");
		$("#step2").submit();
	}

	function visualizzaAlert(alert_message) {
		$("#alert_step2").show();
		$("#alert_step2").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">' + alert_message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
		window.scrollTo(0, 0);
	}

</script>
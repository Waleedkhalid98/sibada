<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave = get_cookieuser();
$fldidgen_utente = verifica_utente($chiave);

$pricerca = get_param("_ricerca");
$pnominativo = get_param("nominativo");
$psesso = get_param("sesso");
$pflag_cv = get_param("flag_cv");
$pqualifica = get_param("idsl_disponibilita");
$plingue= get_param("idsl_lingue");
$pauto= get_param("auto");


$sPAGE = str_replace(".", "_", basename($_SERVER['PHP_SELF']));
if (empty($pricerca) && empty($_SESSION[$sPAGE])) {
	//Primo caricamento della pagina: non faccio nulla
} else {
	$aCOOKIE = array();
	$aCOOKIE["nominativo"] = $pnominativo;
	$aCOOKIE["sesso"] = $psesso;
	$aCOOKIE["flag_cv"] = $pflag_cv;
	$sCOOKIE = serialize($aCOOKIE);
	$_SESSION[$sPAGE] = $sCOOKIE;
}


?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">

<head>
	<title>Sibada - Elenco utenti registrati</title>
	<?php echo get_importazioni_sibada_header(); ?>

	<STYLE TYPE="text/css">
		td {
			padding-left: 7px;
			padding-right: 7px;
		}
	</STYLE>
	<style>
		.dt-buttons button {
		font-family: 'Open Sans', sans-serif;
		font-size: 16px;
		padding: 8px 12px; 
		border-radius: 4px;
		background: #e7e7e7; 
        color: #333333;
		}
		
	</style>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans" rel="stylesheet">

</head>

<body class="push-body" data-ng-app="ponmetroca">
	<div class="body_wrapper push_container clearfix" id="page_top">

		<?php echo get_header_sibada(1); ?>


		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="sibada_home.php" title="Vai alla pagina Home"
											class="">Home</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a>Elenco utenti
											registrati</a></li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<form action="sibada_utenti_registrati.php" method="post">

						<table width="100%">
							<tr>
								<td width="22%">Nominativo</td>
								<td width="16%">Sesso</td>
								<td width="16%">Curriculum inserito</td>
								<td width="16%">Qualifica</td>
								<td width="16%">Lingue</td>
								<td width="16%">Patente</td>
								<td width="16%"></td>
							</tr>
							<tr>
								<td>
									<input name="nominativo" id="nominativo" type="text" class="form-control input-xs"
										value="<?php echo $pnominativo; ?>">
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select id="sesso" name="sesso">
											<option value=""></option>
											<option value="M" <?php if ($psesso == "M")
												echo "selected"; ?>>M</option>
											<option value="F" <?php if ($psesso == "F")
												echo "selected"; ?>>F</option>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select id="flag_cv" name="flag_cv">
											<option value=""></option>
											<option value="1" <?php if ($pflag_cv == 1)
												echo "selected"; ?>>SI</option>
											<option value="2" <?php if ($pflag_cv == 2)
												echo "selected"; ?>>NO</option>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select title="Nessuna selezione" id="idsl_disponibilita"
											name="idsl_disponibilita" onchange="changeCOMUNE(2);">
											<option value=""></option>
											<?php
											$sSQL = "SELECT * FROM sibada_disponibilita";
											$db->query($sSQL);
											$next_record = $db->next_record();
											$response = array();
											while ($next_record) {
												$idsl_disponibilita = $db->f("idsibada_disponibilita");
												$flddescrizione = $db->f("descrizione");

												if ($idsl_disponibilita == $pqualifica)
													echo '<option value="' . $idsl_disponibilita . '" selected>' . $flddescrizione . '</option>';
												else
													echo '<option value="' . $idsl_disponibilita . '">' . $flddescrizione . '</option>';

												$next_record = $db->next_record();
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select title="Nessuna selezione" id="idsl_lingue"
											name="idsl_lingue" >
											<option value=""></option>
											<?php
											$sSQL = "SELECT * FROM sibada_lingue";
											$db->query($sSQL);
											$next_record = $db->next_record();
											$response = array();
											while ($next_record) {
												$idsl_lingue = $db->f("idsibada_lingue");
												$flddescrizione = $db->f("descrizione");

												if ($idsl_lingue == $plingue)
													echo '<option value="' . $idsl_lingue . '" selected>' . $flddescrizione . '</option>';
												else
													echo '<option value="' . $idsl_lingue . '">' . $flddescrizione . '</option>';

												$next_record = $db->next_record();
											}
											?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
									<select id="auto" name="auto">
									<option value=""></option>

										<?php
										$idsl_disponibilita_auto = array(1 => "SI", 2 => "NO");

										foreach ($idsl_disponibilita_auto as $key => $value) {
											
											if ($value == $pauto)
													echo '<option value="' . $value . '" selected>' . $value . '</option>';
												else
													echo '<option value="' . $value . '">' . $value . '</option>';
												

										}
										?>
										</select>
									</div>
								</td>
								<td class="text-center">
									<button type="submit" name="_ricerca" id="_ricerca" value="true"
										class="btn btn-xs btn-outline-primary">
										<svg class="icon icon-xs icon-primary">
											<use xlink:href="static/img/sprite.svg#it-search"></use>
										</svg>
										&nbsp;Avvia ricerca
									</button>
								</td>
								<td>
								</td>
							</tr>
						</table>
					</form>

					<br><br>
					<div class="table-responsive-md">
					<table id="table_utenti" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Nominativo</th>
								<th scope="col" width="10%">Codice Fiscale</th>
								<th scope="col" width="10%">Residente a</th>
								<th scope="col" width="10%">Recapiti</th>
								<th scope="col" width="10%">Qualifica</th>
								<th scope="col" width="10%">Lingue</th>
								<th scope="col" width="10%">Automunito</th>
								<th scope="col" width="10%">Orari</th>
								<th scope="col" width="10%">CV</th>
							</tr>
						</thead>
						<tbody style="font-size: 15px;">
							<?php

							$sSelect = "SELECT gen_utente.*, eso_join_anagrafica.idsso_anagrafica_utente ";
							$sFrom = " FROM " . FRONT_ESONAME . ".gen_utente 
							INNER JOIN " . FRONT_ESONAME . ".gen_utente_profilo on gen_utente.idgen_utente=gen_utente_profilo.idgen_utente 
							INNER JOIN " . FRONT_ESONAME . ".eso_join_anagrafica on gen_utente.idgen_utente=eso_join_anagrafica.idgen_utente";

							$sWhere = "";

							$sWhere = aggiungi_condizione($sWhere, "gen_utente.flag_sl='1'");
							$sWhere = aggiungi_condizione($sWhere, "gen_utente_profilo.idgen_profilo=2");
							$sWhere = aggiungi_condizione($sWhere, "gen_utente.flag_beneficiario=1");

							if (!empty($pnominativo)) {
								$pnominativo = db_string($pnominativo);
								$sWhere = aggiungi_condizione($sWhere, "CONCAT_WS(' '," . FRONT_ESONAME . ".gen_utente.cognome," . FRONT_ESONAME . ".gen_utente.nome) LIKE '%$pnominativo%'");
							}

							if (!empty($psesso)) {
								$psesso = db_string($psesso);
								$sWhere = aggiungi_condizione($sWhere, "gen_utente.sesso='$psesso'");
							}

							if (!empty($sWhere))
								$sWhere = " WHERE " . $sWhere;

							$sOrder = " ORDER BY gen_utente.cognome, gen_utente.nome";

							$sSQL = $sSelect . $sFrom . $sWhere . $sOrder;

							$db_front->query($sSQL);
							$next_record = $db_front->next_record();
							$counter = 1;
							$aUTENTI = array();
							while ($next_record) 
							{
								$fldidgen_utente = $db_front->f("idgen_utente");
								$fldidsso_anagrafica_utente = $db_front->f("idsso_anagrafica_utente");
								$fldidsibada_disponibilita = $db_front->f("idsibada_disponibilita");
								$flddescrizione_disponibilita = get_db_value("SELECT descrizione FROM sibada_disponibilita WHERE idsibada_disponibilita='$fldidsibada_disponibilita'");

								$fldidutente = get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");

								$pidsibada_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
							
								if (!in_array($fldidgen_utente, $aUTENTI) && !empty($fldidutente)) 
								{
									$aUTENTI[] = $fldidgen_utente;

									$beneficiario = new Beneficiario($fldidutente);

									$fldidsl_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente' AND flag_pubblica=1");

									if (!empty($fldidsl_curriculum)) 
									{
										$btn_cv = '<button type="button" name="btn_curriculum" id="btn_curriculum" class="btn btn-xs btn-outline-warning" onclick="stampaCV(' . $fldidsl_curriculum . ')">
												<svg class="icon icon-xs icon-warning">
								            		<use xlink:href="static/img/sprite.svg#it-print"></use>
								            	</svg>
									            &nbsp;Stampa CV
									        </button>';
									} 
									else
										$btn_cv = "";

									$visualizza = false;
									if (!empty($pflag_cv)) 
									{
										switch ($pflag_cv) 
										{
											case 1: //curriculum pubblicati
												if (!empty($fldidsl_curriculum))
													$visualizza = true;
												break;

											case 2: //curriculum non inseriti o non pubblicati
												if (empty($fldidsl_curriculum))
													$visualizza = true;
												break;
										}
									}
									else
										$visualizza = true;

									if(!empty($pqualifica) && $visualizza)
									{
										$idsibada_curriculum_disponibilita=get_db_value('SELECT idsibada_curriculum_disponibilita FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum = "'.$pidsibada_curriculum.'" and idsibada_disponibilita='.$pqualifica);
										if(empty($idsibada_curriculum_disponibilita))
											$visualizza=false;
									}	

									if(!empty($plingue) && $visualizza )
									{
										$idsibada_curriculum_lingue=get_db_value('SELECT idsibada_curriculum_lingue FROM sibada_curriculum_lingue WHERE idsibada_curriculum = "'.$pidsibada_curriculum.'" and idsibada_lingue='.$plingue);
										if(empty($idsibada_curriculum_lingue))
											$visualizza=false;

									}

									if(!empty($pauto)&& $visualizza)
									{
										$idsibada_curriculum_auto=get_db_value('SELECT flag_auto FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum = "'.$pidsibada_curriculum.'" and flag_auto ="'.$pauto.'"');
										if(empty($idsibada_curriculum_auto))
										    $visualizza=false;
									}


									if ($visualizza) 
									{


										$fldauto=get_db_value('SELECT flag_auto FROM sibada_curriculum_disponibilita where idsibada_curriculum = "'.$pidsibada_curriculum.'"');

										$aLINGUE=db_fill_array('SELECT idsibada_curriculum_lingue, sibada_lingue.descrizione FROM sibada_curriculum_lingue INNER JOIN sibada_lingue ON sibada_curriculum_lingue.idsibada_lingue=sibada_lingue.idsibada_lingue WHERE idsibada_curriculum = "'.$pidsibada_curriculum.'"');
										$flddescrizione_lingue="";
										if(is_array($aLINGUE))
										{
											foreach ($aLINGUE as $idsibada_lingue => $value) {
												{
													if($flddescrizione_lingue)
													{
														$flddescrizione_lingue.='<br>';
													}
													$flddescrizione_lingue.=$value;
												}
											}
										}

										$aDISPONIBILITA = db_fill_array('SELECT idsibada_curriculum_disponibilita, sibada_disponibilita.descrizione FROM sibada_curriculum_disponibilita INNER JOIN sibada_disponibilita ON sibada_curriculum_disponibilita.idsibada_disponibilita =sibada_disponibilita.idsibada_disponibilita WHERE idsibada_curriculum = "'.$pidsibada_curriculum.'"');
										$flddescrizione_disponibilita= "";
										if(is_array($aDISPONIBILITA))
										{
											foreach ($aDISPONIBILITA as $idsibada_curriculm_disponibilita => $value) 
											{
												if($flddescrizione_disponibilita)
												{
													$flddescrizione_disponibilita.='<br>';
												}
												$flddescrizione_disponibilita.=$value;
											}
										}
										echo '<tr>
											<td>' . $counter . '</td>
											<td>' . $beneficiario->nominativo . '</td>
											<td>' . $beneficiario->codicefiscale . '</td>
											<td>' . $beneficiario->citta . '</td>
											<td>' . $beneficiario->recapito . '</td>
											<td>' . $flddescrizione_disponibilita . '</td>
											<td>' . $flddescrizione_lingue . '</td>
											<td>' . $fldauto . '</td>
											<td><button type="button" class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#exampleModalLongFixed"  onclick="aprimodal('.$pidsibada_curriculum.')">
													<svg class="icon icon-xs icon-warning">
														<use xlink:href="static/img/sprite.svg#it-clock"></use>
													</svg>
										          &nbsp;Vedi orari
											    </button>
											</td>
											<td>
												' . $btn_cv . '
										    </td>
											
										</tr>';

										$counter++;
									}
								}			
								$next_record = $db_front->next_record();
							}
							?>
						</tbody>
					</table>
					</div>

				</main>
			</section>
			<br><br><br><br><br><br><br><br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>
		</main>
	</div>

	<div class="modal it-dialog-scrollable fade" tabindex="-1" role="dialog" id="exampleModalLongFixed"
		aria-labelledby="exampleModalLongFixedTitle">
		<div class="modal-dialog modal-lg" role="document" >
			<div class="modal-content" >
				<div class="modal-header">
					<h2 class="modal-title h5 " id="exampleModalLongFixedTitle">Intestazione modale</h2>
				</div>
				<div class="modal-body" >
					<iframe id="frameORARI" width="100%" height="90%" frameborder="0" allowtransparency="true" src=""></iframe>

				</div>
				<div class="modal-footer">
					<button class="btn btn-outline-primary btn-sm" type="button"
						data-bs-dismiss="modal" onclick="chiudimodal()">Chiudi</button>
					
				</div>
			</div>
		</div>
	</div>

	<?php echo get_importazioni_sibada(); ?>
	
        <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <script>
 $('#table_utenti').DataTable( {
		"paging":   true,
		"pageLength": 50,
		"info":     true,
		"searching": true,
		dom: 'Bfrtip',
		buttons: [
        	'excel', 'pdf'
    	]
	});
    </script>
</body>

</html>

<script>

	function aprimodal(idsibada_curriculum){

		var page = "sibada_orari.php";
		var params = "?_user=<?php echo $chiave; ?>&_k=" + idsibada_curriculum;
		$('#frameORARI').attr("src", page + params)
		$('#exampleModalLongFixed').modal('show');
		 


	}
	function chiudimodal(){
		$('#exampleModalLongFixed').modal('hide')
	}
	
	function stampaCV(idcv) {
		settings = window_center(1100, 800);
		settings += ",resizable=yes";

		var page = "sibada_curriculum_stampa.php";
		var params = "?_id=" + idcv;
		win = window.open(page + params, "CV", settings);
		if (win.window.focus) { win.window.focus(); }
	}
</script>
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
<!DOCTYPE html>
<html lang="en">
<head>
<title>Sibada - Elenco utenti registrati</title>
	<?php echo get_importazioni_sibada_header(); ?>

	<STYLE TYPE="text/css">
		td {
			padding-left: 7px;
			padding-right: 7px;
		}
	</STYLE>
</head>
<body>
<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<form action="sibada_utenti_registrati.php" method="post">
						<table width="100%">
							<tr>
								<td width="22%">Nominativo</td>
								<td width="22%">Sesso</td>
								<td width="22%">Curriculum inserito</td>
								<td width="22%">Qualifica</td>
								<td width="22%"></td>
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

												if ($idsl_disponibilita == $fldidsibada_disponibilita)
													echo '<option value="' . $idsl_disponibilita . '" selected>' . $flddescrizione . '</option>';
												else
													echo '<option value="' . $idsl_disponibilita . '">' . $flddescrizione . '</option>';

												$next_record = $db->next_record();
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

					<table id="table_utenti" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Nominativo</th>
								<th scope="col" width="10%">Codice Fiscale</th>
								<th scope="col" width="10%">Residente a</th>
								<th scope="col" width="10%">Indirizzo</th>
								<th scope="col" width="10%">Recapiti</th>
								<th scope="col" width="10%">Qualifica</th>
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

							if (!empty($pqualifica)) {
								$pqualifica = db_string($pqualifica);
								$sWhere = aggiungi_condizione($sWhere, "gen_utente.idsibada_disponibilita='$pqualifica'");
							}

							if (!empty($sWhere))
								$sWhere = " WHERE " . $sWhere;

							$sOrder = " ORDER BY gen_utente.cognome, gen_utente.nome";

							$sSQL = $sSelect . $sFrom . $sWhere . $sOrder;

							$db_front->query($sSQL);
							$next_record = $db_front->next_record();
							$counter = 1;
							$aUTENTI = array();
							$file = fopen('C:\\Users\\Waleed\\Desktop\\prova\\data.xls', 'w');
							while ($next_record) {
								$fldidgen_utente = $db_front->f("idgen_utente");
								$fldidsso_anagrafica_utente = $db_front->f("idsso_anagrafica_utente");
								$fldidsibada_disponibilita = $db_front->f("idsibada_disponibilita");
								$flddescrizione_disponibilita = get_db_value("SELECT descrizione FROM sibada_disponibilita WHERE idsibada_disponibilita='$fldidsibada_disponibilita'");

								$fldidutente = get_db_value("SELECT idutente FROM sso_anagrafica_utente WHERE idutente='$fldidsso_anagrafica_utente'");

								$pidsibada_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");
							
								if (!in_array($fldidgen_utente, $aUTENTI) && !empty($fldidutente)) {
									$aUTENTI[] = $fldidgen_utente;

									$beneficiario = new Beneficiario($fldidutente);

									$fldidsl_curriculum = get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente' AND flag_pubblica=1");

									if (!empty($fldidsl_curriculum)) {
										$btn_cv = '<button type="button" name="btn_curriculum" id="btn_curriculum" class="btn btn-xs btn-outline-warning" onclick="stampaCV(' . $fldidsl_curriculum . ')">
												<svg class="icon icon-xs icon-warning">
								            		<use xlink:href="static/img/sprite.svg#it-print"></use>
								            	</svg>
									            &nbsp;Stampa CV
									        </button>';
									} else
										$btn_cv = "";

									$visualizza = false;
									if (!empty($pflag_cv)) {
										switch ($pflag_cv) {
											case 1: //curriculum pubblicati
												if (!empty($fldidsl_curriculum))
													$visualizza = true;
												break;

											case 2: //curriculum non inseriti o non pubblicati
												if (empty($fldidsl_curriculum))
													$visualizza = true;
												break;
										}
									} else
										$visualizza = true;

									if ($visualizza) {
										echo '<tr>
											<td>' . $counter . '</td>
											<td>' . $beneficiario->nominativo . '</td>
											<td>' . $beneficiario->codicefiscale . '</td>
											<td>' . $beneficiario->citta . '</td>
											<td>' . $beneficiario->indirizzo . ' ' . $beneficiario->civico . '</td>
											<td>' . $beneficiario->recapito . '</td>
											<td>' . $flddescrizione_disponibilita . '</td>
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

								    fwrite($file, "NOMINATIVO\tCODICE FISCALE\tCITTA'\n");
										// Scrivi i valori nel file Excel
										fwrite($file, $beneficiario->nominativo . "\t");
										fwrite($file, $beneficiario->codicefiscale . "\t"); 
										fwrite($file, $beneficiario->citta . "\n");
										
										
									  
									 
									

									
								$next_record = $db_front->next_record();
							}
							// Chiude il file
							fclose($file);
							?>
						</tbody>
					</table>
				</main>
			</section>
</body>
</html>
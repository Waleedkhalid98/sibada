<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();

$fldidgen_utente=verifica_utente($chiave);

$pidsso_anagrafica_fornitore=get_param("_id");

if(get_param("_conferma"))
{
	//print_r_formatted($_POST);

	$pcognome=get_param("cognome");
	$pcognome=db_string($pcognome);

	$pidsso_tabella_tipologia_ente=get_param("idsso_tabella_tipologia_ente");

	$pcitta=get_param("citta");
	$pcitta=strtoupper($pcitta);
	$pcitta=db_string($pcitta);

	$pindirizzo=get_param("indirizzo");
	$pindirizzo=strtoupper($pindirizzo);
	$pindirizzo=db_string($pindirizzo);

	$ppiva=get_param("piva");
	$ppiva=db_string($ppiva);

	$pcodicefiscale=get_param("codicefiscale");
	$pcodicefiscale=db_string($pcodicefiscale);

	$pluogo_ccia=get_param("luogo_ccia");
	$pluogo_ccia=strtoupper($pluogo_ccia);
	$pluogo_ccia=db_string($pluogo_ccia);

	$pnumero_ccia=get_param("numero_ccia");
	$pnumero_ccia=strtoupper($pnumero_ccia);
	$pnumero_ccia=db_string($pnumero_ccia);

	$pdata_ccia=get_param("data_ccia");
	$pdata_ccia=invertidata($pdata_ccia,"-","/",1);

	$pattivita_ccia=get_param("attivita_ccia");
	$pattivita_ccia=strtoupper($pattivita_ccia);
	$pattivita_ccia=db_string($pattivita_ccia);

	$ptelefono=get_param("telefono");
	$ptelefono=db_string($ptelefono);

	$pemail=get_param("email");
	$pemail=db_string($pemail);

	$ppec=get_param("pec");
	$ppec=db_string($ppec);

	$psettore=get_param("settore");
	$psettore=strtoupper($psettore);
	$psettore=db_string($psettore);

	$pdescrizione_attivita=get_param("descrizione_attivita");
	$pdescrizione_attivita=db_string($pdescrizione_attivita);

	$pnominativo_rl=get_param("nominativo_rl");
	$pnominativo_rl=strtoupper($pnominativo_rl);
	$pnominativo_rl=db_string($pnominativo_rl);

	$pcodicefiscale_rl=get_param("codicefiscale_rl");
	$pcodicefiscale_rl=strtoupper($pcodicefiscale_rl);
	$pcodicefiscale_rl=db_string($pcodicefiscale_rl);

	$pcomune_nascita_rl=get_param("comune_nascita_rl");
	$pcomune_nascita_rl=strtoupper($pcomune_nascita_rl);
	$pcomune_nascita_rl=db_string($pcomune_nascita_rl);

	$pdata_nascita_rl=get_param("data_nascita_rl");
	$pdata_nascita_rl=invertidata($pdata_nascita_rl,"-","/",1);

	$pcomune_residenza_rl=get_param("comune_residenza_rl");
	$pcomune_residenza_rl=strtoupper($pcomune_residenza_rl);
	$pcomune_residenza_rl=db_string($pcomune_residenza_rl);

	$pindirizzo_rl=get_param("indirizzo_rl");
	$pindirizzo_rl=strtoupper($pindirizzo_rl);
	$pindirizzo_rl=db_string($pindirizzo_rl);

	$ptelefono_rl=get_param("telefono_rl");
	$ptelefono_rl=db_string($ptelefono_rl);

	$pemail_rl=get_param("email_rl");
	$pemail_rl=db_string($pemail_rl);

	$ppec_rl=get_param("pec_rl");
	$ppec_rl=db_string($ppec_rl);


	$update="UPDATE ".FRONT_ESONAME.".gen_utente SET 
		idsso_tabella_tipologia_ente='$pidsso_tabella_tipologia_ente',
		cognome='$pcognome',
		indirizzo='$pindirizzo',
		citta='$pcitta',
		codicefiscale='$pcodicefiscale',
		piva='$ppiva',
		telefono='$ptelefono',
		email_pec='$ppec',
		email='$pemail',
		luogo_ccia='$pluogo_ccia',
		numero_ccia='$pnumero_ccia',
		data_ccia='$pdata_ccia',
		attivita_ccia='$pattivita_ccia',
		nominativo_rl='$pnominativo_rl',
		comune_nascita_rl='$pcomune_nascita_rl',
		data_nascita_rl='$pdata_nascita_rl',
		codicefiscale_rl='$pcodicefiscale_rl',
		comune_residenza_rl='$pcomune_residenza_rl',
		indirizzo_rl='$pindirizzo_rl',
		telefono_rl='$ptelefono_rl',
		email_rl='$pemail_rl',
		pec_rl='$ppec_rl',
		settore='$psettore',
		descrizione_attivita='$pdescrizione_attivita'
	WHERE idgen_utente='$fldidgen_utente'";
	$db_front->query($update);


	$update="UPDATE sso_anagrafica_utente SET 
		cognome='$pcognome',
		citta='$pcitta',
		indirizzo='$pindirizzo',
		codicefiscale='$pcodicefiscale',
		piva='$ppiva',
		telefono='$ptelefono',
		email_pec='$ppec',
		email='$pemail'
	WHERE idutente='$pidsso_anagrafica_fornitore'";
	$db->query($update);


	$fldidsso_ente_servizio=get_db_value("SELECT idsso_ente_servizio FROM sso_ente_servizio WHERE idutente='$pidsso_anagrafica_fornitore'");
	$update="UPDATE sso_ente_servizio SET 
		idsso_tabella_tipologia_ente='$pidsso_tabella_tipologia_ente',
	    nominativo_rl='$pnominativo_rl',
	    codicefiscale_rl='$pcodicefiscale_rl',
	    comune_nascita_rl='$pcomune_nascita_rl',
	    comune_residenza_rl='$pcomune_residenza_rl',
	    indirizzo_rl='$pindirizzo_rl',
	    telefono_rl='$ptelefono_rl',
	    data_nascita_rl='$pdata_nascita_rl',
	    email_rl='$pemail_rl',
	    pec_rl='$ppec_rl',
	    luogo_ccia='$pluogo_ccia',
	    numero_ccia='$pnumero_ccia',
	    data_ccia='$pdata_ccia',
	    attivita_ccia='$pattivita_ccia',
	    settore='$psettore',
	    descrizione_attivita='$pdescrizione_attivita'
	WHERE idsso_ente_servizio='$fldidsso_ente_servizio'";
	$db->query($update);

	$alert_success=true;

}

$fornitore=new Fornitore($pidsso_anagrafica_fornitore);

$disabled="disabled";
$display_salva="display:none;";

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
<head>
	<title>Sibada - Dettaglio azienda</title>
    <?php echo get_importazioni_sibada_header(); ?>
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
									<li class="breadcrumb-item"><a href="sibada_home.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
									<li class="breadcrumb-item"><a href="sibada_aziende_elenco.php" title="Vai all'elenco delle aziende" class="">Aziende</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a><?php echo $fornitore->cognome; ?></a></li>
								</ol>
							</nav>

							<?php
								if($alert_success) echo(get_alert(4,"Salvataggio avvenuto con successo."));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<ul class="nav nav-tabs nav-tabs-icon-text auto">
						<li class="nav-item"><a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="true"><svg class="icon icon-white"><use xlink:href="static/img/sprite.svg#it-box"></use></svg>Dati azienda</a></li>
						<li class="nav-item"><a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false"><svg class="icon icon-white"><use xlink:href="static/img/sprite.svg#it-user"></use></svg>Dati rappresentante legale</a></li>
						<li class="nav-item"><a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab2" aria-selected="false"><svg class="icon icon-white"><use xlink:href="static/img/sprite.svg#it-list"></use></svg>Posizioni aperte</a></li>
					</ul>

					<br>

					<form id="frm_fornitore" method="post" enctype="multipart/form-data" action="esibada_aziende_dettaglio.php" class="form-horizontal">
						<div class="tab-content" id="myTabContent">
							<div class="tab-pane p-4 fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
								<div class="row">
									<div class="form-group col-sm-8 offset-sm-2">
										<label for="cognome">Denominazione*</label>
										<input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo $fornitore->cognome;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
						                <div class="bootstrap-select-wrapper">
											<label for="idsso_tabella_tipologia_ente">Natura giuridica*</label>
											<select class="form-control" name="idsso_tabella_tipologia_ente" id="idsso_tabella_tipologia_ente" <?php echo $disabled; ?>>
												<option value=""></option>
												<?php
													$query="SELECT descrizione, idsso_tabella_tipologia_ente FROM ".DBNAME_SS.".sso_tabella_tipologia_ente where idsso_tabella_tipologia_ente!=23"; 
													$db->query($query);
													$res = $db->next_record();
													while($res)
													{
														$descrizione = $db->f("descrizione");
														$idsso_tabella_tipologia_ente = $db->f("idsso_tabella_tipologia_ente");

														if($fornitore->idsso_tabella_tipologia_ente==$idsso_tabella_tipologia_ente)
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
											<input type="text" class="form-control" id="citta" name="citta" value="<?php echo $fornitore->citta;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
											<label for="citta">Con sede legale in*</label>
										</div>
									</div>
									<div class="form-group col-4">
										<input type="text" class="form-control" id="indirizzo" name="indirizzo" value="<?php echo $fornitore->indirizzo;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
										<label for="indirizzo">Indirizzo*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div >
											<input type="text" class="form-control" id="piva" name="piva" value="<?php echo $fornitore->piva;?>" <?php echo $disabled; ?>>
											<label for="piva">Partita IVA*</label>
										</div>
									</div>
									<div class="form-group col-4">
										<input type="text" class="form-control" id="codicefiscale" name="codicefiscale" value="<?php echo $fornitore->codicefiscale;?>" <?php echo $disabled; ?>>
										<label for="codicefiscale">Codice Fiscale*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div >
											<input type="text" class="form-control" id="luogo_ccia" name="luogo_ccia" value="<?php echo $fornitore->luogo_ccia;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
											<label for="luogo_ccia">Iscritta presso la C.C.I.A. di*</label>
										</div>
									</div>
									<div class="form-group col-4">
										<input type="text" class="form-control" id="numero_ccia" name="numero_ccia" value="<?php echo $fornitore->numero_ccia;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
										<label for="numero_ccia">Numero iscrizione C.C.I.A.*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div>
											<input type="text" class="form-control" id="data_ccia" name="data_ccia" value="<?php echo $fornitore->data_ccia_formattata;?>" <?php echo $disabled; ?>>
											<label for="data_ccia">Data iscrizione C.C.I.A.*</label>
										</div>
									</div>
										<div class="form-group col-4">
										<input type="text" class="form-control" id="attivita_ccia" name="attivita_ccia" value="<?php echo $fornitore->attivita_ccia;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
										<label for="attivita_ccia">Attività iscrizione C.C.I.A.*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div>
											<input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $fornitore->telefono;?>" <?php echo $disabled; ?>>
											<label for="telefono">Telefono*</label>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<input type="text" class="form-control" id="email" name="email" value="<?php echo $fornitore->email;?>" <?php echo $disabled; ?>>
										<label for="email">E-mail*</label>
									</div>
									<div class="form-group col-4">
										<div>
											<input type="text" class="form-control" id="pec" name="pec" value="<?php echo $fornitore->email_pec; ?>" <?php echo $disabled; ?>>
											<label for="pec">PEC</label>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<input type="text" class="form-control" id="settore" name="settore" value="<?php echo $fornitore->settore;?>" <?php echo $disabled; ?>>
										<label for="settore">Settore attività*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-8 offset-sm-2">
										<textarea id="descrizione_attivita" name="descrizione_attivita" rows="6" style="width:100%" class="form-control input-sm" maxlength="" placeholder="Inserire una breve descrizione delle attività svolte dall'azienda (minimo 50 caratteri)" <?php echo $disabled; ?>><?php echo $fornitore->descrizione_attivita; ?></textarea>
										<label for="descrizione_attivita">Descrizione attività*</label>
									</div>
								</div>
							</div>

							<div class="tab-pane p-4 fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div>
											<input type="text" class="form-control" id="nominativo_rl" name="nominativo_rl" style="text-transform: uppercase;" value="<?php echo $fornitore->nominativo_rl;?>" <?php echo $disabled; ?>>
										<label for="nominativo_rl">Nominativo*</label>
										</div>
									</div>
									<div class="form-group col-4">
										<input type="text" class="form-control" id="codicefiscale_rl" name="codicefiscale_rl" style="text-transform: uppercase;" value="<?php echo $fornitore->codicefiscale_rl;?>" <?php echo $disabled; ?>>
										<label for="codicefiscale_rl">Codice Fiscale*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div>
											<input type="text" class="form-control" id="comune_nascita_rl" name="comune_nascita_rl" value="<?php echo $fornitore->comune_nascita_rl;?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
											<label for="comune_nascita_rl">Nato/a a*</label>
										</div>
									</div>
									<div class="form-group col-4">
										<input type="text" class="form-control it-date-datepicker" id="data_nascita_rl" name="data_nascita_rl"  maxlength="16" value="<?php echo $fornitore->data_nascita_rl_formattata; ?>" <?php echo $disabled; ?>>
										<label for="data_nascita_rl">Nato/a il*</label>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div class="it-datepicker-wrapper theme-dark"> 
											<input class="form-control it-date-datepicker" id="comune_residenza_rl" name="comune_residenza_rl" type="text" value="<?php echo $fornitore->comune_residenza_rl; ?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
											<label for="comune_residenza_rl">Residente in*</label>
										</div>
									</div>
									<div class="form-group col-sm-4">
										<div class="it-datepicker-wrapper theme-dark"> 
											<input class="form-control it-date-datepicker" id="indirizzo_rl" name="indirizzo_rl" type="text" value="<?php echo $fornitore->indirizzo_rl; ?>" style="text-transform: uppercase;" <?php echo $disabled; ?>>
											<label for="indirizzo_rl">Indirizzo di residenza*</label>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<div>
											<input type="text" class="form-control" id="telefono_rl" name="telefono_rl" value="<?php echo $fornitore->telefono_rl; ?>" <?php echo $disabled; ?>>
											<label for="telefono_rl">Cellulare*</label>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="form-group col-sm-4 offset-sm-2">
										<input type="text" class="form-control" id="email_rl" name="email_rl" value="<?php echo $fornitore->email_rl;?>" <?php echo $disabled; ?>>
										<label for="email_rl">E-mail*</label>
									</div>
									<div class="form-group col-4">
										<div>
											<input type="text" class="form-control" id="pec_rl" name="pec_rl" value="<?php echo $fornitore->pec_rl; ?>" <?php echo $disabled; ?>>
											<label for="pec_rl">PEC</label>
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane p-4 fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
								
									<?php
										$nPOSIZIONI=get_db_value("SELECT * FROM sl_posizioni WHERE idsso_anagrafica_utente='$pidsso_anagrafica_fornitore' ORDER BY idsl_posizioni ASC");

										if($nPOSIZIONI>0)
										{
											echo '<table id="table_posizioni" class="table">
											<thead>
												<tr>
													<th scope="col" width="5%"></th>
													<th scope="col" width="10%">Posizione</th>
													<th scope="col" width="40%">Descrizione</th>
													<th scope="col" width="5%">Stato</th>
													<th scope="col" width="20%"></th>
												</tr>
											</thead>
											<tbody>';
											$sSQL="SELECT * FROM sl_posizioni WHERE idsso_anagrafica_utente='$pidsso_anagrafica_fornitore' ORDER BY idsl_posizioni ASC";
											$db->query($sSQL);
											$next_record=$db->next_record();
											$counter=1;
											while($next_record)
											{
												$fldidsl_posizioni=$db->f("idsl_posizioni");
												$fldidsl_disponibilita=$db->f("idsl_disponibilita");
												$flddisponibilita=get_db_value("SELECT descrizione FROM sl_disponibilita WHERE idsl_disponibilita='$fldidsl_disponibilita'");
												$flddescrizione=$db->f("descrizione");

												$fldflag_stato=$db->f("flag_stato");
												switch($fldflag_stato)
												{
													case 0:
														$stato="Aperta";
														$class_stato="text-success";
														break;

													case 1:
														$stato="Chiusa";
														$class_stato="text-danger";
														break;
												}

												echo '<tr>
													<td>'.$counter.'</td>
													<td>'.$flddisponibilita.'</td>
													<td>'.$flddescrizione.'</td>
													<td class="'.$class_stato.'"><b>'.$stato.'</b></td>
													<td></td>
												</tr>';

												$counter++;

												$next_record=$db->next_record();
											}

											echo '</tbody>
											</table>';
										}
										else
										{
											echo "<br><br>";
											echo(get_alert(0,"Non risultano posizioni aperte da questa azienda."));
											echo "<br><br><br><br><br><br><br>";
										}
									?>
							</div>
						</div>

						<center>
							<button name="_indietro" id="_indietro" type="button" class="btn btn-primary btn-md" onclick="indietro()">Indietro</button>
							<button name="_conferma" id="_conferma" value="true" type="submit" class="btn btn-primary btn-md" style="<?php echo $display_salva; ?>">Salva</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsso_anagrafica_fornitore; ?>">
					</form>
				</main>
			</section>

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
});

function indietro()
{
	window.location.href=("./sibada_aziende_elenco.php");
}
</script>

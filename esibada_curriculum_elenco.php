<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuserFront();

$fldidgen_utente=verifica_eutente($chiave);
$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if(empty($fldidutente) || empty($fldidgen_utente))
    die("Attenzione! sessione scaduta");

$pricerca=get_param("_ricerca");
$pnominativo=get_param("nominativo");
$psesso=get_param("sesso");
$pidsl_lingue=get_param("idsl_lingue");
$pmultilingue=get_param("_multilingue");
$aMULTILINGUE=explode(",",$pmultilingue);
$pflag_esperienza=get_param("flag_esperienza");
$pistruzione=get_param("_istruzione");
$aISTRUZIONE=explode(",",$pistruzione);
$pdisponibilita=get_param("_disponibilita");
$aDISPONIBILITA=explode(",",$pdisponibilita);
$pstato=get_param("stato");

$sPAGE=str_replace(".","_",basename($_SERVER['PHP_SELF']));
if(empty($pricerca) && empty($_SESSION[$sPAGE]))
{
	//Primo caricamento della pagina: non faccio nulla
}
else
{
	$aCOOKIE=array();	
	$aCOOKIE["nominativo"]=$pnominativo;
	$aCOOKIE["sesso"]=$psesso;
	$aCOOKIE["idsl_lingue"]=$pidsl_lingue;
	$aCOOKIE["_multilingue"]=$pmultilingue;
	$aCOOKIE["flag_esperienza"]=$pflag_esperienza;
	$aCOOKIE["_istruzione"]=$pistruzione;
	$aCOOKIE["_disponibilita"]=$pdisponibilita;
	$aCOOKIE["stato"]=$pstato;
	$sCOOKIE=serialize($aCOOKIE);
	$_SESSION[$sPAGE]=$sCOOKIE;
}

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Aziende</title>
        <?php echo get_importazioni_sibada_header(); ?>

		<STYLE TYPE="text/css">
		
			td { padding-left: 7px; padding-right: 7px;} 
		
		</STYLE>
    </head> 
<body class="push-body" data-ng-app="ponmetroca">
    <div class="body_wrapper push_container clearfix" id="page_top">

        <?php echo get_header_sibada(); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">                   
								<li class="breadcrumb-item"><a href="esibada_home_ditta.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
								<li class="breadcrumb-item active" aria-current="page"><a>Elenco Curricula</a></li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<form action="esibada_curriculum_elenco.php" method="post" onSubmit="loadMULTI()">
						<table width="100%">
							<tr>
								<td width="22%">Nominativo</td>
								<td width="22%">Sesso</td>
								<td width="22%">Madrelingua</td>
								<td width="22%">Altre lingue</td>
							</tr>
							<tr>
								<td>
									<input name="nominativo" id="nominativo" type="text" class="form-control input-xs" value="<?php echo $pnominativo; ?>">
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
				  						<select class="form-control input-sm" name="sesso" id="sesso" title="">
				  							<option value=""></option>
				  							<option value="M" <?php if($psesso=="M") echo "selected"; ?>>M</option>
				  							<option value="F" <?php if($psesso=="F") echo "selected"; ?>>F</option>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
				  						<select class="form-control input-sm" name="idsl_lingue" id="idsl_lingue" title="">
				  							<option value=""></option>
				  							<?php
				  								$sSQL="SELECT * FROM sibada_lingue";
				  								$db->query($sSQL);
				  								$res=$db->next_record();
				  								while($res)
				  								{
				  									$idsl_lingue=$db->f("idsibada_lingue");
				  									$flddescrizione=$db->f("descrizione");

				  									if($idsl_lingue==$pidsl_lingue)
				  										echo '<option value="'.$idsl_lingue.'" selected>'.$flddescrizione.'</value>';
				  									else
				  										echo '<option value="'.$idsl_lingue.'">'.$flddescrizione.'</value>';

				  									$res=$db->next_record();
				  								}
				  							?>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select id="multi_lingue" name="multi_lingue" title="Scegli una o più opzioni" multiple="true" data-multiple-separator="">
											<?php
				  								$sSQL="SELECT * FROM sibada_lingue";
				  								$db->query($sSQL);
				  								$res=$db->next_record();
				  								while($res)
				  								{
				  									$idsl_lingue=$db->f("idsibada_lingue");
				  									$flddescrizione=$db->f("descrizione");

				  									if(in_array($idsl_lingue,$aMULTILINGUE))
				  										echo '<option value="'.$idsl_lingue.'" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';
				  									else
				  										echo '<option value="'.$idsl_lingue.'" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';

				  									$res=$db->next_record();
				  								}
				  							?>
										</select>
									</div>
									<input type="hidden" id="_multilingue" name="_multilingue" value="">
								</td>
							</tr>
							<tr>
								<td colspan="5">&nbsp;</td>
							</tr>
							<tr>
								<td width="22%">Esperienza lavorativa</td>
								<td width="22%">Titolo di studio</td>
								<td width="22%">Disponibilità</td>
								<td width="22%">Stato</td>
								<td width="22%"></td>
							</tr>
							<tr>
								<td>
									<div class="bootstrap-select-wrapper">
				  						<select class="form-control input-sm" name="flag_esperienza" id="flag_esperienza" title="">
				  							<option value=""></option>
				  							<option value="1" <?php if($pflag_esperienza==1) echo "selected"; ?>>Con esperienza</option>
				  							<option value="2" <?php if($pflag_esperienza==2) echo "selected"; ?>>Senza esperienza</option>
										</select>
									</div>
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
										<select id="multi_istruzione" name="multi_istruzione" title="Scegli una o più opzioni" multiple="true" data-multiple-separator="">
											<?php
				  								$sSQL="SELECT * FROM sibada_grado_istruzione";
				  								$db->query($sSQL);
				  								$res=$db->next_record();
				  								while($res)
				  								{
				  									$idsl_grado_istruzione=$db->f("idsibada_grado_istruzione");
				  									$flddescrizione=$db->f("descrizione");

				  									if(in_array($idsl_grado_istruzione,$aISTRUZIONE))
				  										echo '<option value="'.$idsl_grado_istruzione.'" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';
				  									else
				  										echo '<option value="'.$idsl_grado_istruzione.'" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';

				  									$res=$db->next_record();
				  								}
				  							?>
										</select>
									</div>
									<input type="hidden" id="_istruzione" name="_istruzione" value="">
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
									  <select id="multi_disponibilita" name="multi_disponibilita" title="Scegli una o più opzioni" multiple="true" data-multiple-separator="">
									  	<?php
									  		$sSQL="SELECT * FROM sibada_disponibilita";
									  		$db->query($sSQL);
									  		$res=$db->next_record();
									  		while($res)
									  		{
									  			$idsl_disponibilita=$db->f("idsibada_disponibilita");
									  			$flddescrizione=$db->f("descrizione");

									  			if(in_array($idsl_disponibilita,$aDISPONIBILITA))
									    			echo '<option value="'.$idsl_disponibilita.'" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';
									  			else
									    			echo '<option value="'.$idsl_disponibilita.'" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';

									  			$res=$db->next_record();
									  		}
									  	?>
									  </select>
									</div>
									<input type="hidden" id="_disponibilita" name="_disponibilita" value="">
								</td>
								<td>
									<div class="bootstrap-select-wrapper">
				  						<select class="form-control input-sm" name="stato" id="stato" title="">
				  							<option value=""></option>
				  							<option value="1" <?php if($pstato==1) echo "selected"; ?>>Osservati</value>
				  							<option value="2" <?php if($pstato==2) echo "selected"; ?>>Da contattare</value>
				  							<option value="3" <?php if($pstato==3) echo "selected"; ?>>Fissato appuntamento</value>
				  							<option value="4" <?php if($pstato==4) echo "selected"; ?>>Non interessati</value>
										</select>
									</div>
								</td>
								<td class="text-center">
									<button type="submit" name="_ricerca" id="_ricerca" value="true" class="btn btn-xs btn-outline-primary">
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

					<table id="table_curriculum" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Nominativo</th>
								<th scope="col" width="10%">Codice Fiscale</th>
								<th scope="col" width="10%">Residenza</th>
								<th scope="col" width="10%">Indirizzo</th>
								<th scope="col" width="10%">Telefono</th>
								<th scope="col" width="10%">Email</th>
								<th scope="col" width="10%"></th>
								<th scope="col" width="15%"></th>
							</tr>
						</thead>
						<tbody style="font-size: 13px;">
						<?php
							if((!empty($pricerca) || $_SESSION[$sPAGE]))
							{
								$sSelect="SELECT DISTINCT sibada_curriculum.idsibada_curriculum ";
								
								$sFrom=" FROM sibada_curriculum 
								INNER JOIN sso_anagrafica_utente ON sso_anagrafica_utente.idutente=sibada_curriculum.idutente ";
								
								$sWhere=aggiungi_condizione($sWhere, "sibada_curriculum.flag_pubblica='1'");

								if(!empty($pnominativo))
								{
									$pnominativo=db_string($pnominativo);
									$sWhere=aggiungi_condizione($sWhere, "(concat(sso_anagrafica_utente.cognome,concat(' ',sso_anagrafica_utente.nome)) like '%$pnominativo%' or sso_anagrafica_utente.cognome like '%$pnominativo%')");
								}

								if(!empty($psesso))
									$sWhere=aggiungi_condizione($sWhere, "sso_anagrafica_utente.sesso='$psesso'");

								if(!empty($pidsl_lingue))
									$sWhere=aggiungi_condizione($sWhere, "sibada_curriculum.idsibada_lingue_madre='$pidsl_lingue'");

								if(!empty($pmultilingue))
								{
									if(!empty($aMULTILINGUE))
									{
										$sFrom.=" INNER JOIN sibada_curriculum_lingue ON sibada_curriculum_lingue.idsibada_curriculum=sibada_curriculum.idsibada_curriculum";
										$condizione_lingue="";
										foreach($aMULTILINGUE as $idlingua)
										{
											$condizione_lingue.="sibada_curriculum_lingue.idsibada_lingue='$idlingua' OR ";
										}

										$condizione_lingue = rtrim($condizione_lingue, " OR ");

										$sWhere=aggiungi_condizione($sWhere, "(".$condizione_lingue.")");
									}
								}

								if(!empty($pistruzione))
								{
									if(!empty($aISTRUZIONE))
									{
										$sFrom.=" INNER JOIN sibada_curriculum_studio ON sibada_curriculum_studio.idsibada_curriculum=sibada_curriculum.idsibada_curriculum";
										$condizione_istruzione="";
										foreach($aISTRUZIONE as $idistruzione)
										{
											$condizione_istruzione.="sibada_curriculum_studio.idsibada_grado_istruzione='$idistruzione' OR ";
										}

										$condizione_istruzione = rtrim($condizione_istruzione, " OR ");

										$sWhere=aggiungi_condizione($sWhere, "(".$condizione_istruzione.")");
									}
								}

								if(!empty($pdisponibilita))
								{
									if(!empty($aDISPONIBILITA))
									{
										$sFrom.=" INNER JOIN sibada_curriculum_disponibilita ON sibada_curriculum_disponibilita.idsibada_curriculum=sibada_curriculum.idsibada_curriculum";
										$condizione_disponibilita="";
										foreach($aDISPONIBILITA as $iddisponibilita)
										{
											$condizione_disponibilita.="sibada_curriculum_disponibilita.idsibada_disponibilita='$iddisponibilita' OR ";
										}

										$condizione_disponibilita = rtrim($condizione_disponibilita, " OR ");

										$sWhere=aggiungi_condizione($sWhere, "(".$condizione_disponibilita.")");
									}
								}

								if(!empty($pflag_esperienza))
								{
									switch($pflag_esperienza)
									{
										case 1:		//con esperienza
											$sFrom.=" INNER JOIN sibada_curriculum_lavoro ON sibada_curriculum_lavoro.idsibada_curriculum=sibada_curriculum.idsibada_curriculum";
											break;

										case 2: 	//senza esperienza
											$sFrom.=" LEFT JOIN sibada_curriculum_lavoro ON sibada_curriculum_lavoro.idsibada_curriculum=sibada_curriculum.idsibada_curriculum";
											$sWhere=aggiungi_condizione($sWhere, "sibada_curriculum_lavoro.idsibada_curriculum_lavoro IS NULL");
											break;
									}
								}

								if(!empty($pstato))
								{
									switch($pstato)
									{
										case 1:
											$table="sibada_curriculum_osservato";
											break;

										case 2:
											$table="sibada_curriculum_contattare";
											break;

										case 3:
											$table="sibada_curriculum_appuntamento";
											break;

										case 4:
											$table="sibada_curriculum_noninteressato";
											break;
																}

									$sFrom.=" INNER JOIN ".$table." ON ".$table.".idsibada_curriculum=sibada_curriculum.idsibada_curriculum";

									$sWhere=aggiungi_condizione($sWhere, $table.".idsso_anagrafica_utente='$fldidutente'");;
								}

								if(!empty($sWhere))
									$sWhere=" WHERE ".$sWhere;
								
								$sOrder=" ORDER BY cognome";
												
								$sSQL=$sSelect.$sFrom.$sWhere.$sOrder;
								$db->query($sSQL);
								$next_record=$db->next_record();
								$counter=1;
								$aCURRICULUM=array();
								while($next_record)
								{
									$fldidsl_curriculum=$db->f("idsibada_curriculum");
									if(!in_array($fldidsl_curriculum,$aCURRICULUM))
									{
										$aCURRICULUM[]=$fldidsl_curriculum;

										$curriculum=new Curriculum($fldidsl_curriculum);

										$fldidsl_curriculum_osservato=get_db_value("SELECT idsibada_curriculum_osservato FROM sibada_curriculum_osservato WHERE idsibada_curriculum='$fldidsl_curriculum' AND idsso_anagrafica_utente='$fldidutente'");
										if(!empty($fldidsl_curriculum_osservato))
											$class_osservato=SL_STATO_SELEZIONATO;
										else
											$class_osservato=SL_STATO_NONSELEZIONATO;

										$fldidsl_curriculum_contattare=get_db_value("SELECT idsibada_curriculum_contattare FROM sibada_curriculum_contattare WHERE idsibada_curriculum='$fldidsl_curriculum' AND idsso_anagrafica_utente='$fldidutente'");
										if(!empty($fldidsl_curriculum_contattare))
											$class_contattare=SL_STATO_SELEZIONATO;
										else
											$class_contattare=SL_STATO_NONSELEZIONATO;

										$fldidsl_curriculum_appuntamento=get_db_value("SELECT idsibada_curriculum_appuntamento FROM sibada_curriculum_appuntamento WHERE idsibada_curriculum='$fldidsl_curriculum' AND idsso_anagrafica_utente='$fldidutente'");
										if(!empty($fldidsl_curriculum_appuntamento))
											$class_appuntamento=SL_STATO_SELEZIONATO;
										else
											$class_appuntamento=SL_STATO_NONSELEZIONATO;

										$fldidsl_curriculum_noninteressato=get_db_value("SELECT idsibada_curriculum_noninteressato FROM sibada_curriculum_noninteressato WHERE idsibada_curriculum='$fldidsl_curriculum' AND idsso_anagrafica_utente='$fldidutente'");
										if(!empty($fldidsl_curriculum_noninteressato))
											$class_noninteressato=SL_STATO_SELEZIONATO;
										else
											$class_noninteressato=SL_STATO_NONSELEZIONATO;

										echo '<tr>
											<td>'.$counter.'</td>
											<td>'.$curriculum->intestatario->nominativo.'</td>
											<td>'.$curriculum->intestatario->codicefiscale.'</td>
											<td>'.$curriculum->intestatario->citta.' ('.$curriculum->intestatario->prov.')</td>
											<td>'.$curriculum->intestatario->indirizzo.' '.$curriculum->intestatario->civico.'</td>
											<td>'.$curriculum->intestatario->recapito.'</td>
											<td>'.$curriculum->intestatario->email.'</td>
											<td>
												<button type="button" name="btn_curriculum" id="btn_curriculum" class="btn btn-xs btn-outline-warning" onclick="stampaCV('.$fldidsl_curriculum.')">
													<svg class="icon icon-xs icon-warning">
									            		<use xlink:href="static/img/sprite.svg#it-print"></use>
									            	</svg>
										            &nbsp;Stampa
										        </button>
										    </td>
										    <td>
									   		 <span style="cursor: pointer;" data-toggle="tooltip" title="Osservato" id="stato_osservato'.$fldidsl_curriculum.'" class="badge badge-pill '.$class_osservato.' class_stato'.$fldidsl_curriculum.'" onclick="cvSTATO('.$fldidsl_curriculum.',1)"><svg class="icon icon-xs icon-white">
							            		<use xlink:href="static/img/sprite.svg#it-password-visible"></use>
							            	</svg></span>
							            	<span style="cursor: pointer;" data-toggle="tooltip" title="Da contattare"  id="stato_contattare'.$fldidsl_curriculum.'" class="badge badge-pill '.$class_contattare.' class_stato'.$fldidsl_curriculum.'" onclick="cvSTATO('.$fldidsl_curriculum.',2)"><svg class="icon icon-xs icon-white">
							            		<use xlink:href="static/img/sprite.svg#it-telephone"></use>
							            	</svg></span>
							            	<span style="cursor: pointer;" data-toggle="tooltip" title="Fissato appuntamento" id="stato_appuntamento'.$fldidsl_curriculum.'" class="badge badge-pill '.$class_appuntamento.' class_stato'.$fldidsl_curriculum.'" onclick="cvSTATO('.$fldidsl_curriculum.',3)"><svg class="icon icon-xs icon-white">
							            		<use xlink:href="static/img/sprite.svg#it-calendar"></use>
							            	</svg></span>
							            	<span style="cursor: pointer;" data-toggle="tooltip" title="Non interessato" id="stato_noninteressato'.$fldidsl_curriculum.'" class="badge badge-pill '.$class_noninteressato.' class_stato'.$fldidsl_curriculum.'" onclick="cvSTATO('.$fldidsl_curriculum.',4)"><svg class="icon icon-xs icon-white">
							            		<use xlink:href="static/img/sprite.svg#it-close"></use>
							            	</svg></span>
									    </td>
										</tr>';
										
										$counter++;
										$next_record = $db->next_record();
									}
								}	
							}
						?>
						</tbody>
					</table>
				</main>
			</section>
			<br><br><br><br>
			<?php echo get_footer_sibada(); ?>
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

function stampaCV(idcv)
{
	settings=window_center(1100,800);
	settings+=",resizable=yes";

	var page="sibada_curriculum_stampa.php";
	var params="?_id="+idcv;
	win=window.open(page+params,"CV",settings);
	if(win.window.focus){win.window.focus();}
}

function loadMULTI()
{
	var foo1 = []; 

	$('#multi_lingue :selected').each(function(i, selected){ 
		foo1[i] = $(selected).val(); 
	});

	$("#_multilingue").val(foo1);

	var foo2 = []; 

	$('#multi_istruzione :selected').each(function(i, selected){ 
		foo2[i] = $(selected).val(); 
	});

	$("#_istruzione").val(foo2);
	

	var foo3 = []; 

	$('#multi_disponibilita :selected').each(function(i, selected){ 
		foo3[i] = $(selected).val(); 
	});

	$("#_disponibilita").val(foo3);
}

function cvSTATO(idcv,stato)
{	
	var page="sibada_action.php";
	var params="_action=cv_osservato&_id="+idcv+"&_stato="+stato+"&_idfornitore=<?php echo $fldidutente; ?>";
	var loader = dhtmlxAjax.postSync(page,params);  
	myParam=loader.xmlDoc.responseText;
	//alert(myParam)
	if(myParam=="1")
	{
		switch(stato)
		{
			case 1:
				var obj_stato="stato_osservato"+idcv;
				break;

			case 2:
				var obj_stato="stato_contattare"+idcv;
				break;

			case 3:
				var obj_stato="stato_appuntamento"+idcv;
				break;

			case 4:
				var obj_stato="stato_noninteressato"+idcv;
				break;
		}
		$(".class_stato"+idcv).attr("class","badge badge-pill neutral-1-bg-a3 class_stato"+idcv);
		$("#"+obj_stato).removeClass("neutral-1-bg-a3");
		$("#"+obj_stato).addClass("primary-bg-c5");
	}
}
</script>

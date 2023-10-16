<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuserFront();

$fldidgen_utente=verifica_eutente($chiave);
$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if(empty($fldidutente) || empty($fldidgen_utente))
    die("Attenzione! sessione scaduta");

$pidsibada_curriculum=get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");



/*
echo "RAND: ".rand()."<br>";
echo "IDUTENTE: ".$fldidutente;
echo "<br>";
echo "ID_CURRICULUM: ".$pidsibada_curriculum;
*/

if(get_param("_conferma"))
{
	//print_r_formatted($_POST);
	$pinserimento = get_param("inserimento");

	if(!empty($pinserimento))
	{
		$pistituto_scolastico=get_param("istituto_scolastico");
		$pistituto_scolastico=db_string($pistituto_scolastico);

		$pcomune_istituto=get_param("comune_istituto");
		$pcomune_istituto=db_string($pcomune_istituto);

		$pidsibada_grado_istruzione=get_param("idsibada_grado_istruzione");

		$pcampo_studio=get_param("campo_studio");
		$pcampo_studio=db_string($pcampo_studio);
		$pcampo_studio=strtoupper($pcampo_studio);

		$pmese=get_param("mese");
		$panno=get_param("anno");

		$pdescrizione_titolo=get_param("descrizione_titolo");
		$pdescrizione_titolo=db_string($pdescrizione_titolo);

		$insert="INSERT INTO sibada_curriculum_studio(
			idsibada_curriculum,
			istituto_scolastico,
			comune_istituto,
			idsibada_grado_istruzione,
			campo_studio,
			mese,
			anno,
			descrizione_titolo
		) VALUES(
			'$pidsibada_curriculum',
			'$pistituto_scolastico',
			'$pcomune_istituto',
			'$pidsibada_grado_istruzione',
			'$pcampo_studio',
			'$pmese',
			'$panno',
			'$pdescrizione_titolo'
		)";

		$db->query($insert);
		updateCVSiBada($pidsibada_curriculum);
		$alert_insert=true;

	}
	else
	{
		updateCVSiBada($pidsibada_curriculum);
		echo "<script>parent.loadSTEP(4)</script>";
	}
}

if(get_param("_update"))
{
	$alert_update=true;
}

if(get_param("_delete"))
{
	$pidsibada_curriculum_lavoro_del=get_param("idstudio_delete");
	if(!empty($pidsibada_curriculum_lavoro_del))
	{
		$delete="DELETE FROM sibada_curriculum_studio WHERE idsibada_curriculum_studio='$pidsibada_curriculum_lavoro_del'";
		$db->query($delete);

		updateCVSiBada($pidsibada_curriculum);
		
		$alert_delete=true;
	}
}

if(!empty($pidsibada_curriculum))
{
	$nTITOLI=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsibada_curriculum'");
	if($nTITOLI>0)
	{
		$display_titolo="display:none;";
		$display_btn_titolo="";
		$pinserimento=0;
	}
	else
	{
		$display_titolo="";
		$display_btn_titolo="display:none;";
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
				if(empty($pidsibada_curriculum))
				{
					echo "<br><br><br>".(get_alert(0,"Attenzione! per procedere con la compilazione del Curriculum Vitae è necessario iniziale dalla sezione \"Dati di contatto\"."));
    				echo get_importazioni_sibada();
					die;
				}

				if($alert_delete) echo(get_alert(4,"Titolo di studio eliminato con successo.")); 
				if($alert_insert) echo(get_alert(4,"Titolo di studio inserito con successo.")); 
				if($alert_update) echo(get_alert(4,"Titolo di studio modificato con successo.")); 
			?>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step3" style="display:none;"></div>

					<form id="step3" method="post" enctype="multipart/form-data" action="esibada_curriculum_step3.php" class="form-horizontal">

						<div id="lista_studio">
							<?php
								$aMESI_LETTERE=array(1=>"Gennaio",2=>"Febbraio",3=>"Marzo",4=>"Aprile",5=>"Maggio",6=>"Giugno",7=>"Luglio",8=>"Agosto",9=>"Settembre",10=>"Ottobre",11=>"Novembre",12=>"Dicembre");
								$sSQL="SELECT * FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsibada_curriculum'";
								$db->query($sSQL);
								$res=$db->next_record();
								while($res)
								{
									$fldidsibada_curriculum_studio=$db->f("idsibada_curriculum_studio");
									$fldistituto_scolastico=$db->f("istituto_scolastico");
									$fldcomune_istituto=$db->f("comune_istituto");
									$fldidsibada_grado_istruzione=$db->f("idsibada_grado_istruzione");
									$fldtitolo_studio=get_db_value("SELECT descrizione FROM sibada_grado_istruzione WHERE idsibada_grado_istruzione='$fldidsibada_grado_istruzione'");
									$fldcampo_studio=$db->f("campo_studio");
									if(!empty($fldcampo_studio))
										$fldcampo_studio="<br>Campo di studio: ".$fldcampo_studio;
									$fldmese=$db->f("mese");
									$fldanno=$db->f("anno");
									$flddescrizione_titolo=$db->f("descrizione_titolo");

									echo '<div class="col-md-12">			
					                        <div class="card card-img rounded shadow" style="height:140px;">
					                            <div class="card-body">
				                                    <div class="card-text"> 
				                                    	<div class="d-flex align-content-center flex-wrap">                                
					                                        <div class="text-left col-8">
					                                            <p>'.$fldtitolo_studio.$fldcampo_studio.'<br>presso '.$fldistituto_scolastico.' - '.$aMESI_LETTERE[$fldmese].'/'.$fldanno.'<br>'.$fldcomune_istituto.'<br></p>
					                                        </div>
					                                        <div class="col-1"></div>
					                                        <div class="text-right col-3">
					                                        	<button type="button" class="btn btn-xs btn-outline-warning" onclick="updateSTUDIO('.$fldidsibada_curriculum_studio.')">
					                                        	<svg class="icon icon-xs icon-warning">
												            		<use xlink:href="static/img/sprite.svg#it-pencil"></use>
												            	</svg>
													            &nbsp;Modifica
													            </button>
	  															<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteSTUDIO('.$fldidsibada_curriculum_studio.')">
	  															<svg class="icon icon-xs icon-danger">
												            		<use xlink:href="static/img/sprite.svg#it-delete"></use>
												            	</svg>
													            &nbsp;Elimina
													            </button>
					                                        </div>
					                                    </div>
					                                </div>
					                            </div>
					                        </div>
										</div>
									<br>';
									
									$res=$db->next_record();
								}
							?>
						</div>

						<div id="div_studio" style="<?php echo $display_titolo; ?>">
							<div class="row">
								<div id="title_studio" class="form-group col-sm-8 offset-sm-2">
								</div>
							</div>
							<br>

							<div class="row">
								<div class="form-group col-sm-4 offset-sm-2">
									<div>
										<input type="text" class="form-control" id="istituto_scolastico" name="istituto_scolastico" placeholder="es. Università La Sapienza" value="" >
										<label for="istituto_scolastico">Nome dell'istituto scolastico*</label>
									</div>
								</div>

								<div class="form-group col-4">
									<input type="text" class="form-control" id="comune_istituto" name="comune_istituto" placeholder="es. Roma" value="" >
									<label for="comune_istituto">Luogo dell'istituto scolastico*</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-4 offset-sm-2 bootstrap-select-wrapper">
									<select class="form-control input-sm" title="Scegli il titolo di studio" name="idsibada_grado_istruzione" id="idsibada_grado_istruzione">
										<?php     
											$sSQL="SELECT * FROM sibada_grado_istruzione ORDER BY idsibada_grado_istruzione ASC";
											$db->query($sSQL);
											$res=$db->next_record();
											while($res)
											{
												$fldidsibada_grado_istruzione=$db->f("idsibada_grado_istruzione");
												$flddescrizione=$db->f("descrizione");

												echo "\n <option value=\"".$fldidsibada_grado_istruzione."\">".$flddescrizione."</option>";

												$res=$db->next_record();
											}          
										?>
									</select>
									<label for="idsibada_grado_istruzione">Titolo di studio*</label>
								</div>

								<div class="form-group col-4">
									<input type="text" class="form-control" id="campo_studio" name="campo_studio" placeholder="es.Economia" value="">
									<label for="campo_studio">Campo di studio</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-4 offset-sm-2 bootstrap-select-wrapper">
									<label for="mese">Mese di conseguimento del titolo*</label>
									<select class="form-control input-sm" title="Mese titolo di studio" name="mese" id="mese">
										<?php     
											foreach($aMESI_LETTERE as $idmese=>$mese)
											{
												echo "\n <option value=\"".$idmese."\">".$mese."</option>";
											}
										?>
									</select>
								</div>

								<div class="form-group col-4 bootstrap-select-wrapper">
									<label for="anno">Anno di conseguimento del titolo*</label>
									<select class="form-control input-sm" title="Anno titolo di studio" name="anno" id="anno">
										<?php
											$aANNI = range(2020, 1955);
											foreach($aANNI as $anno)
											{
												echo "\n <option value=\"".$anno."\">".$anno."</option>";
											}
										?>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-8 offset-sm-2">
									<textarea id="descrizione_titolo" name="descrizione_titolo" rows="4" style="width:100%" class="form-control input-sm border" maxlength="" placeholder="Inserire eventuali note sul titolo di studio conseguito"></textarea>
									<label for="descrizione_titolo">Note titolo di studio</label>
								</div>
							</div>
						</div>

						<div id="btn_studio" class="text-right" style="<?php echo $display_btn_titolo; ?>">
							<button id="btn_add_studio" class="btn btn-xs btn-primary" type="button">Aggiungi titolo di studio</button>
						</div>

						<div id="btn_annulla_studio" class="text-right" style="display:none;">
							<button id="btn_canc_studio" class="btn btn-xs btn-primary" type="button">Annulla inserimento</button>
						</div>

						<br>

						<center>
							<button class="btn btn-primary" type="button" onclick="parent.loadSTEP(2);">Indietro</button>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma" value="true">Continua</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsibada_curriculum; ?>">
						<input type="hidden" id="idstudio_delete" name="idstudio_delete" value="">
						<input type="hidden" id="inserimento" name="inserimento" value="<?php echo $pinserimento; ?>">
						<input type="hidden" id="no_stud" name="no_stud" value="">
						<input type="hidden" id="n_titoli" name="n_titoli" value="<?php echo $nTITOLI; ?>">


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
						<button class="btn btn-outline-primary btn-sm" type="button" onclick="annullaDEL_STU()" data-dismiss="modal">Annulla</button>
						<button class="btn btn-primary btn-sm" type="button" onclick="deleteSTU()" data-dismiss="modal">Continua</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" tabindex="-1" role="dialog" id="modal_nostudio">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Attenzione!
						</h5>
					</div>
					<div class="modal-body">
						<p>Non hai inserito nessun titolo di studio.<br>Se puoi inseriscine almeno uno.</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-outline-primary btn-sm" type="button" data-dismiss="modal">Annulla</button>
						<button class="btn btn-primary btn-sm" type="button" onclick="continuaNOSTU()" data-dismiss="modal">Non ho titoli di studio</button>
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

$("#btn_add_studio").click(function() {
	$("#div_studio").show();
	$("#btn_studio").hide();
	$("#btn_add_studio").hide();
	$("#lista_studio").hide();
	$("#btn_annulla_studio").show();
	$("#btn_canc_studio").show();
	$("#title_studio").html("Inserimento di un nuovo titolo di studio");
	$("#istituto_scolastico").val("");
	$("#comune_istituto").val("");
	$("#idsibada_grado_istruzione").val("");
	$("#campo_studio").val("");
	$("#mese").val("");
	$("#anno").val("");
	$("#inserimento").val("1");
});

$("#btn_canc_studio").click(function() {
	$("#div_studio").hide();
	$("#btn_studio").show();
	$("#btn_add_studio").show();
	$("#btn_annulla_studio").hide();
	$("#btn_canc_studio").hide();
	$("#lista_studio").show();
	$("#title_esperienza").html("");
	$("#istituto_scolastico").val("");
	$("#comune_istituto").val("");
	$("#idsibada_grado_istruzione").val("");
	$("#campo_studio").val("");
	$("#mese").val("");
	$("#anno").val("");
	$("#inserimento").val("0");
});





function updateSTUDIO(idstudio)
{
	settings=window_center(700,950);
	settings+=",resizable=yes";
	
	var page="esibada_curriculum_step3_studio.php";
	var params="?_id="+idstudio;

	window.open (page+params,'modifica_titolo',settings);
	if(win.window.focus){win.window.focus();}
}

function deleteSTUDIO(idstudio)
{
	$("#idstudio_delete").val(idstudio)
	$('#modal_delete').modal('show');
}

function deleteSTU()
{
	var idstudio=$("#idstudio_delete").val()
	
	var page="esibada_curriculum_step3.php";
	var params="?_delete=true&idstudio_delete="+idstudio;
	window.location=(page+params)
}

function annullaDEL_ESP()
{
	$("#idstudio_delete").val("")
}

$("#step3").submit(function(event) {

	var no_stud=$("#no_stud").val();
	if(no_stud=="1")
	{
		parent.loadSTEP(4);
	}
	else
	{
		var errors=0;
		var string_errors="";

		var inserimento=$("#inserimento").val();
		if(inserimento=="1")
		{
			var istituto_scolastico=$("#istituto_scolastico").val();
			if(istituto_scolastico=="")
			{
				string_errors=string_errors+"- Nome dell'istituto scolastico; <br>";
				errors++;
			}

			var comune_istituto=$("#comune_istituto").val();
			if(comune_istituto=="")
			{
				string_errors=string_errors+"- Luogo dell'istituto scolastico; <br>";
				errors++;
			} 

			var idsibada_grado_istruzione=$("#idsibada_grado_istruzione").val();
			if(idsibada_grado_istruzione=="")
			{
				string_errors=string_errors+"- Titolo di studio; <br>";
				errors++;
			} 

			var mese=$("#mese").val();
			if(mese=="")
			{
				string_errors=string_errors+"- Mese di conseguimento del titolo; <br>";
				errors++;
			}

			var anno=$("#anno").val();
			if(anno=="")
			{
				string_errors=string_errors+"- Anno di conseguimento del titolo; <br>";
				errors++;
			}

			var page="sibada_action.php";
			var params="_user=<?php echo $chiave;?>&_action=ntitoli_studio";
			var loader = dhtmlxAjax.postSync(page,params);  
			myParam=loader.xmlDoc.responseText;
			if(myParam=="0")
			{
				var istituto_scolastico=$("#istituto_scolastico").val();
				var comune_istituto=$("#comune_istituto").val();
				var idsibada_grado_istruzione=$("#idsibada_grado_istruzione").val();
				var mese=$("#mese").val();
				var anno=$("#anno").val();
				
				if(istituto_scolastico=='' && comune_istituto=='' && idsibada_grado_istruzione=='')
				{
					$('#modal_nostudio').modal('show');
					return false;
				}
				else
				{
					$("#inserimento").val("1");
				}
			} 

			if(errors>0)
			{
				visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
				return false;
			}
		}
		
		
			
		
	}
});

function continuaNOSTU()
{
	$("#no_stud").val("1");
	$("#step3").submit();
}

function visualizzaAlert(alert_message)
{
	$("#alert_step3").show();
	$("#alert_step3").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}

</script>

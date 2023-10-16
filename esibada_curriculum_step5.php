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
	$pidsibada_lingue_madre=get_param("idsibada_lingue_madre");
	if($pidsibada_lingue_madre==8)
	{
		$pmadrelingua_altro=get_param("madrelingua_altro");
		$pmadrelingua_altro=db_string($pmadrelingua_altro);
	}
	else
		$pmadrelingua_altro="";

	$update="UPDATE sibada_curriculum SET idsibada_lingue_madre='$pidsibada_lingue_madre', madrelingua_altro='$pmadrelingua_altro' WHERE idsibada_curriculum='$pidsibada_curriculum'";
	$db->query($update);

	updateCVSiBada($pidsibada_curriculum);

	echo "<script>parent.loadSTEP(6)</script>";
}

if(get_param("_update"))
{
	$pidsibada_curriculum_lingue=get_param("_idlingua");
	$pflag_rating_scritto=get_param("_ratingscritto");
	$pflag_rating_parlato=get_param("_ratingparlato");

	$update="UPDATE sibada_curriculum_lingue SET flag_rating_scritto='$pflag_rating_scritto',flag_rating_parlato='$pflag_rating_parlato' WHERE idsibada_curriculum_lingue='$pidsibada_curriculum_lingue'";
	$db->query($update);
	
	updateCVSiBada($pidsibada_curriculum);

	$alert_update=true;
}

if(!empty($pidsibada_curriculum))
{
	$fldidsibada_lingue_madre=get_db_value("SELECT idsibada_lingue_madre FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
	if($fldidsibada_lingue_madre==8)
	{
		$fldmadrelingua_altro=get_db_value("SELECT madrelingua_altro FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
		$diplay_lingua_altro="";
	}
	else
		$diplay_lingua_altro="display:none;";
}
else
	$diplay_lingua_altro="display:none;";
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Lingue</title>
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

				if($alert_success) echo(get_alert(4,"Salvataggio avvenuto con successo.")); 
				if($alert_update) echo(get_alert(4,"Modifica avvenuta con successo.")); 
			?>

			<br>
    		<br>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step5" style="display:none;"></div>

					<form id="step5" method="post" enctype="multipart/form-data" action="esibada_curriculum_step5.php" class="form-horizontal">

						<div class="row">
							<div class="form-group col-4 bootstrap-select-wrapper">
								<select class="form-control input-sm" title="" name="idsibada_lingue_madre" id="idsibada_lingue_madre" onchange="changeLINGUA()">
									<option value=""></option>
									<?php     
										$sSQL="SELECT * FROM sibada_lingue ORDER BY idsibada_lingue ASC";
										$db->query($sSQL);
										$res=$db->next_record();
										while($res)
										{
											$fldidsibada_lingue=$db->f("idsibada_lingue");
											$flddescrizione=$db->f("descrizione");

											if($fldidsibada_lingue_madre==$fldidsibada_lingue)
												echo "\n <option value=\"".$fldidsibada_lingue."\" selected>".$flddescrizione."</option>";
											else
												echo "\n <option value=\"".$fldidsibada_lingue."\">".$flddescrizione."</option>";

											$res=$db->next_record();
										}          
									?>
								</select>
								<label for="idsibada_lingue_madre">Madrelingua*</label>
							</div>
							<div class="form-group col-4 offset-sm-2" id="div_linguaaltro" style="<?php echo $diplay_lingua_altro; ?>">
								<input type="text" class="form-control" id="madrelingua_altro" name="madrelingua_altro" placeholder="Indicare la lingua" value="<?php echo $fldmadrelingua_altro; ?>" >
								<label for="madrelingua_altro">Specificare*</label>
							</div>
						</div>

						<div id="alert_step5_lingue" style="display:none;"></div>
						
						<table id="table_lingue" class="table table-responsive">
							<thead>
								<tr>
									<th scope="col" width="20%">Lingua</th>
									<th scope="col" width="30%">Livello scritto</th>
									<th scope="col" width="30%">Livello parlato</th>
									<th scope="col" width="10%"></th>
									<th scope="col" width="10%"></th>
								</tr>
							</thead>
							<tbody>
								<?php
									$sSQL="SELECT * FROM sibada_curriculum_lingue WHERE idsibada_curriculum='$pidsibada_curriculum' ORDER BY idsibada_curriculum_lingue";
									$db->query($sSQL);
									$res=$db->next_record();
									while($res)
									{
										$fldidsibada_curriculum_lingue=$db->f("idsibada_curriculum_lingue");
										$fldidsibada_lingue=$db->f("idsibada_lingue");
										$fldlingua=get_db_value("SELECT descrizione FROM sibada_lingue WHERE idsibada_lingue='$fldidsibada_lingue'");
										$fldflag_rating_scritto=$db->f("flag_rating_scritto");
										$fldflag_rating_parlato=$db->f("flag_rating_parlato");

										$selected1="";
										$selected2="";
										$selected3="";
										$selected4="";
										$selected5="";

										switch($fldflag_rating_scritto)
										{
											case 1:
												$selected1="selected";
												break;

											case 2:
												$selected2="selected";
												break;

											case 3:
												$selected3="selected";
												break;

											case 4:
												$selected4="selected";
												break;

											case 5:
												$selected5="selected";
												break;

											case 6:
												$selected6="selected";
												break;
										}

										$selected1_parlato="";
										$selected2_parlato="";
										$selected3_parlato="";
										$selected4_parlato="";
										$selected5_parlato="";
										$selected6_parlato="";

										switch($fldflag_rating_parlato)
										{
											case 1:
												$selected1_parlato="selected";
												break;

											case 2:
												$selected2_parlato="selected";
												break;

											case 3:
												$selected3_parlato="selected";
												break;

											case 4:
												$selected4_parlato="selected";
												break;

											case 5:
												$selected5_parlato="selected";
												break;

											case 6:
												$selected6_parlato="selected";
												break;
										}

										echo '<tr id="tr_lingua'.$fldidsibada_curriculum_lingue.'">
											<td>'.$fldlingua.'</td>
											<td>
												<select id="flag_rating_scritto'.$fldidsibada_curriculum_lingue.'" name="flag_rating_scritto'.$fldidsibada_curriculum_lingue.'" class="rating" disabled>
													<option value=""></option>
													<option value="1" '.$selected1.'>A1</option>
													<option value="2" '.$selected2.'>A2</option>
													<option value="3" '.$selected3.'>B1</option>
													<option value="4" '.$selected4.'>B2</option>
													<option value="5" '.$selected5.'>C1</option>
													<option value="6" '.$selected6.'>C2</option>
												</select>
											</td>
											<td>
												<select id="flag_rating_parlato'.$fldidsibada_curriculum_lingue.'" name="flag_rating_parlato'.$fldidsibada_curriculum_lingue.'" class="rating" disabled>
													<option value=""></option>
													<option value="1" '.$selected1_parlato.'>A1</option>
													<option value="2" '.$selected2_parlato.'>A2</option>
													<option value="3" '.$selected3_parlato.'>B1</option>
													<option value="4" '.$selected4_parlato.'>B2</option>
													<option value="5" '.$selected5_parlato.'>C1</option>
													<option value="6" '.$selected6_parlato.'>C2</option>
												</select>
											</td>
											<td>
	  											<button type="button" id="my_button" class="btn btn-xs btn-outline-warning"  onclick="updateLINGUA('.$fldidsibada_curriculum_lingue.')">
												  <div class=row >
												  <div class=col-12>
	  												<svg class="icon icon-xs icon-warning">
									            		<use xlink:href="static/img/sprite.svg#it-check"></use>
									            	</svg>
													</div> 
													<div class=col-12>
										            &nbsp;Salva
													</div> 
													</div> 
										            </button>
											</td>
											<td>
	  											<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteLINGUA('.$fldidsibada_curriculum_lingue.')">
	  												<svg class="icon icon-xs icon-danger">
									            		<use xlink:href="static/img/sprite.svg#it-delete"></use>
									            	</svg>
										            &nbsp;Elimina
	  											</button>
											</td>
										</tr>';

										$res=$db->next_record();
									}
								?>
							</tbody>
							<tfoot>
								<!--tr>
									<td colspan="4" class="text-primary">Aggiungi lingua</td>
								</tr-->
								<tr>
									<td>
										<select id="idsibada_lingue" name="idsibada_lingue" class="form-control input-sm">
											<option value=""></option>
											<?php
												$sSQL="SELECT * FROM sibada_lingue WHERE idsibada_lingue";
												$db->query($sSQL);
												$res=$db->next_record();
												while($res)
												{
													$fldidsibada_lingue=$db->f("idsibada_lingue");
													$flddescrizione=$db->f("descrizione");

													echo '<option value="'.$fldidsibada_lingue.'">'.$flddescrizione.'</option>';

													$res=$db->next_record();
												}
											?>
										</select>
									</td>
									<td>
										<select id="flag_rating_scritto" name="flag_rating_scritto" class="rating">
											<option value=""></option>
											<option value="1">A1</option>
											<option value="2">A2</option>
											<option value="3">B1</option>
											<option value="4">B2</option>
											<option value="5">C1</option>
											<option value="6">C2</option>
										</select>
									</td>
									<td>
										<select id="flag_rating_parlato" name="flag_rating_parlato" class="rating">
											<option value=""></option>
											<option value="1">A1</option>
											<option value="2">A2</option>
											<option value="3">B1</option>
											<option value="4">B2</option>
											<option value="5">C1</option>
											<option value="6">C2</option>
										</select>
									</td>
									<td>
	  									<button type="button" class="btn btn-xs btn-outline-primary" onclick="addLINGUA()">
	  										<svg class="icon icon-xs icon-primary">
							            		<use xlink:href="static/img/sprite.svg#it-plus"></use>
							            	</svg>
								            &nbsp;Aggiungi
								        </button>
									</td>
									<td></td>
								</tr>
							</tfoot>
						</table>

						<br>

						<center>
							<button class="btn btn-primary" type="button" onclick="parent.loadSTEP(4);">Indietro</button>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma" value="true">Continua</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsibada_curriculum; ?>">
						<input type="hidden" id="idlingua_delete" name="idlingua_delete" value="">

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
						<button class="btn btn-outline-primary btn-sm" type="button" onclick="annullaDEL_LIN()" data-dismiss="modal">Annulla</button>
						<button class="btn btn-primary btn-sm" type="button" onclick="deleteLIN()" data-dismiss="modal">Continua</button>
					</div>
				</div>
			</div>
		</div>
	</div>

    <?php echo get_importazioni_sibada(); ?>

</body>

</html>

<script>
const table = document.querySelector("table");



$(function() {
	$('.rating').barrating({
		theme: 'bars-square',
		showValues: true,
        showSelectedRating: false
	});
});


$("#step5").submit(function(event) {
	var errors=0;
	var string_errors="";

	var idsibada_lingue_madre=$("#idsibada_lingue_madre").val();
	if(idsibada_lingue_madre=="")
	{
		string_errors=string_errors+"- Madrelingua; <br>";
		errors++;
	}
	else
	{
		if(idsibada_lingue_madre=="8")
		{
			var madrelingua_altro=$("#madrelingua_altro").val();
			if(madrelingua_altro=="")
			{
				string_errors=string_errors+"- Specificare la lingua; <br>";
				errors++;
			}
		}
	}

	if(errors>0)
	{
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}

});

function changeLINGUA()
{
	var idsibada_lingue_madre=$("#idsibada_lingue_madre").val();
	if(idsibada_lingue_madre=="8")
	{
		$("#div_linguaaltro").show();
		$("#madrelingua_altro").val("");
	}
	else
	{
		$("#div_linguaaltro").hide();
		$("#madrelingua_altro").val("");
	}
}


function addLINGUA()
{
	var errors=0;
	var string_errors="";

	var idsibada_lingue=$("#idsibada_lingue").val();
	if(idsibada_lingue=="")
	{
		string_errors=string_errors+"- Lingua; <br>";
		errors++;
	}

	var flag_rating_scritto=$("#flag_rating_scritto").val();
	if(flag_rating_scritto=="")
	{
		string_errors=string_errors+"- Livello scritto; <br>";
		errors++;
	}

	var flag_rating_parlato=$("#flag_rating_parlato").val();
	if(flag_rating_parlato=="")
	{
		string_errors=string_errors+"- Livello parlato; <br>";
		errors++;
	}

	if(errors>0)
	{
		visualizzaAlertLingue("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
	}
	else
	{
		var page="sibada_action.php";
		var params="profilo=<?php echo $profilo;?>&menu=<?php echo $menu;?>";
		params+="&_action=add_lingua";
		params+="&_idlingua="+idsibada_lingue;
		params+="&_ratingscritto="+flag_rating_scritto;
		params+="&_ratingparlato="+flag_rating_parlato;
		$.ajax({
			type: "POST",
			url: page,
			data: params, 
			dataType: "html",
			success: function(result)
			{
				$("#table_lingue tbody").append(result);
				$("#idsibada_lingue").val("");
				$("#flag_rating").val("");

				$("#flag_rating_scritto .br-selected").removeClass("br-selected");
				$("#flag_rating_parlato .br-selected").removeClass("br-selected");
			},
			error: function()
			{
				console.log("Chiamata fallita, si prega di riprovare...");
			}
		});
	}
}

function visualizzaAlert(alert_message)
{
	$("#alert_step5").show();
	$("#alert_step5").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}

function visualizzaAlertLingue(alert_message)
{
	$("#alert_step5_lingue").show();
	$("#alert_step5_lingue").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}

function deleteLINGUA(idlingua)
{
	$("#idlingua_delete").val(idlingua)
	$('#modal_delete').modal('show');
}

function deleteLIN()
{
	var idlingua=$("#idlingua_delete").val()
	if(idlingua!='')
	{
		var page="sibada_action.php";
		var params="profilo=<?php echo $profilo;?>&menu=<?php echo $menu;?>";
		params+="&_action=delete_lingua";
		params+="&_idlingua="+idlingua;
		$.ajax({
			type: "POST",
			url: page,
			data: params, 
			dataType: "html",
			success: function(result)
			{
				$("#tr_lingua"+idlingua).hide();
			},
			error: function()
			{
				console.log("Chiamata fallita, si prega di riprovare...");
			}
		});
	}
}

function annullaDEL_LIN()
{
	$("#idlingua_delete").val("")
}

function updateLINGUA(idlingua)
{	
	var flag_rating_scritto=$("#flag_rating_scritto"+idlingua).val();
	var flag_rating_parlato=$("#flag_rating_parlato"+idlingua).val();

	var page="esibada_curriculum_step5.php";
	var params="?_update=true&_idlingua="+idlingua+"&_ratingscritto="+flag_rating_scritto+"&_ratingparlato="+flag_rating_parlato;
	window.location=(page+params)
}
</script>

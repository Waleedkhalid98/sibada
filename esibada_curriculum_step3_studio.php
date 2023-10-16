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

$pidsl_curriculum=get_db_value("SELECT idsibada_curriculum FROM sibada_curriculum WHERE idutente='$fldidutente'");

$pidsl_curriculum_titolo=get_param("_id");

/*
echo "RAND: ".rand()."<br>";
echo "IDUTENTE: ".$fldidutente;
echo "<br>";
echo "ID_CURRICULUM: ".$pidsl_curriculum;
echo "<br>";
echo "ID_CURRICULUM_LAVORO: ".$pidsl_curriculum_lavoro;
*/

if(get_param("_conferma"))
{
	$pistituto_scolastico=get_param("istituto_scolastico");
	$pistituto_scolastico=db_string($pistituto_scolastico);

	$pcomune_istituto=get_param("comune_istituto");
	$pcomune_istituto=db_string($pcomune_istituto);

	$pidsl_grado_istruzione=get_param("idsl_grado_istruzione");

	$pcampo_studio=get_param("campo_studio");
	$pcampo_studio=db_string($pcampo_studio);
	$pcampo_studio=strtoupper($pcampo_studio);

	$pmese=get_param("mese");
	$panno=get_param("anno");

	$pdescrizione_titolo=get_param("descrizione_titolo");
	$pdescrizione_titolo=db_string($pdescrizione_titolo);

	if(!empty($pidsl_curriculum_titolo))
	{
		$update="UPDATE sibada_curriculum_studio SET 
			istituto_scolastico='$pistituto_scolastico', 
			comune_istituto='$pcomune_istituto', 
			idsibada_grado_istruzione='$pidsl_grado_istruzione', 
			campo_studio='$pcampo_studio', 
			mese='$pmese', 
			anno='$panno', 
			descrizione_titolo='$pdescrizione_titolo'
			WHERE idsibada_curriculum_studio='$pidsl_curriculum_titolo'";
		$db->query($update);

		updateCVSiBada($pidsl_curriculum);
		
		echo '<script>
			window.opener.document.location.assign("./esibada_curriculum_step3.php?_update=true");
			window.close();
		</script>';
	}
}

if(!empty($pidsl_curriculum_titolo))
{
	$sSQL="SELECT * FROM sibada_curriculum_studio WHERE idsibada_curriculum_studio='$pidsl_curriculum_titolo'";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_curriculum_studio=$db->f("idsibada_curriculum_studio");
		$fldistituto_scolastico=$db->f("istituto_scolastico");
		$fldcomune_istituto=$db->f("comune_istituto");
		$fldidsl_grado_istruzione=$db->f("idsibada_grado_istruzione");
		$fldcampo_studio=$db->f("campo_studio");
		$fldmese=$db->f("mese");
		$fldanno=$db->f("anno");
		$flddescrizione_titolo=$db->f("descrizione_titolo");

		$res=$db->next_record();
	}
}

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Modifica titolo di studio</title>
        <?php echo get_importazioni_sibada_header(); ?>
    </head> 

<body class="push-body bg-white" data-ng-app="ponmetroca">
    <div class="body_wrapper push_container clearfix" id="page_top">

		<main id="main_container">

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step3_titolo" style="display:none;"></div>

					<form id="step3_titolo" method="post" enctype="multipart/form-data" action="esibada_curriculum_step3_studio.php" class="form-horizontal">

						<div id="div_studio">
							<div class="row">
								<div id="title_studio" class="form-group col-sm-8 offset-sm-2">
								</div>
							</div>
							<br>

							<div class="row">
								<div class="form-group col-sm-5 offset-sm-1">
									<div>
									  <label class="form-label active"for="istituto_scolastico">Nome dell'istituto scolastico*</label>
										<input type="text" class="form-control" id="istituto_scolastico" name="istituto_scolastico" placeholder="es. Università La Sapienza" value="<?php echo $fldistituto_scolastico; ?>" >
									</div>
								</div>

								<div class="form-group col-sm-5">
								    <label class="form-label active" for="">Luogo dell'istituto scolastico*</label>
									<input type="text" class="form-control" id="comune_istituto" name="comune_istituto" placeholder="es. Roma" value="<?php echo $fldcomune_istituto; ?>" >
								</div>
							</div>

							<div class="row">
								<div class="form-group col-5 offset-sm-1 bootstrap-select-wrapper">
									<select class="form-control input-sm" title="Scegli il titolo di studio" name="idsl_grado_istruzione" id="idsl_grado_istruzione">
										<?php     
											$sSQL="SELECT * FROM sibada_grado_istruzione ORDER BY idsibada_grado_istruzione ASC";
											$db->query($sSQL);
											$res=$db->next_record();
											while($res)
											{
												$idsl_grado_istruzione=$db->f("idsibada_grado_istruzione");
												$flddescrizione=$db->f("descrizione");

												if($idsl_grado_istruzione==$fldidsl_grado_istruzione)
													echo "\n <option value=\"".$idsl_grado_istruzione."\" selected>".$flddescrizione."</option>";
												else
													echo "\n <option value=\"".$idsl_grado_istruzione."\">".$flddescrizione."</option>";

												$res=$db->next_record();
											}          
										?>
									</select>
									<label for="">Titolo di studio*</label>
								</div>

								<div class="form-group col-sm-5">
									<input type="text" class="form-control" id="campo_studio" name="campo_studio" placeholder="es.Economia" value="<?php echo $fldcampo_studio; ?>">
									<label for="">Campo di studio</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-5 offset-sm-1 bootstrap-select-wrapper">
									<label for="idsl_grado_istruzione">Mese di conseguimento del titolo*</label>
									<select class="form-control input-sm" title="Mese titolo di studio" name="mese" id="mese">
										<?php     
											$aMESI_LETTERE=array(1=>"Gennaio",2=>"Febbraio",3=>"Marzo",4=>"Aprile",5=>"Maggio",6=>"Giugno",7=>"Luglio",8=>"Agosto",9=>"Settembre",10=>"Ottobre",11=>"Novembre",12=>"Dicembre");
											foreach($aMESI_LETTERE as $idmese=>$mese)
											{
												if($idmese==$fldmese)
													echo "\n <option value=\"".$idmese."\" selected>".$mese."</option>";
												else
													echo "\n <option value=\"".$idmese."\">".$mese."</option>";
											}
										?>
									</select>
								</div>

								<div class="form-group col-5 bootstrap-select-wrapper">
									<label for="">Anno di conseguimento del titolo*</label>
									<select class="form-control input-sm" title="Anno titolo di studio" name="anno" id="anno">
										<?php
											$aANNI = range(2020, 1955);
											foreach($aANNI as $anno)
											{
												if($fldanno==$anno)
													echo "\n <option value=\"".$anno."\" selected>".$anno."</option>";
												else
													echo "\n <option value=\"".$anno."\">".$anno."</option>";
											}
										?>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-10 offset-sm-1">
									<textarea id="descrizione_titolo" name="descrizione_titolo" rows="4" style="width:100%" class="form-control input-sm border" maxlength="" placeholder="Inserire eventuali note sul titolo di studio conseguito"><?php echo $flddescrizione_titolo; ?></textarea>
									<label for="">Note titolo di studio</label>
								</div>
							</div>
						</div>

						<center>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma" value="true">Salva</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsl_curriculum_titolo; ?>">

					</form>
				</main>
			</section>
	    </main>
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

$("#step3_titolo").submit(function(event) {

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

		var idsl_grado_istruzione=$("#idsl_grado_istruzione").val();
		if(idsl_grado_istruzione=="")
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
	}
	if(errors>0)
	{
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}
});


function visualizzaAlert(alert_message)
{
	$("#alert_step3_titolo").show();
	$("#alert_step3_titolo").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}

</script>

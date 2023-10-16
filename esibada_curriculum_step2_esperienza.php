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

$pidsl_curriculum_lavoro=get_param("_id");

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
	$pqualifica=get_param("qualifica");
	$pqualifica=db_string($pqualifica);

	$pdatore_lavoro=get_param("datore_lavoro");
	$pdatore_lavoro=db_string($pdatore_lavoro);

	$pcomune_lavoro=get_param("comune_lavoro");
	$pcomune_lavoro=db_string($pcomune_lavoro);
	$pcomune_lavoro=strtoupper($pcomune_lavoro);

	$pprov_lavoro=get_param("prov_lavoro");
	$pprov_lavoro=db_string($pprov_lavoro);
	$pprov_lavoro=strtoupper($pprov_lavoro);

	$pdata_inizio=get_param("data_inizio");
	$pdata_inizio=invertidata($pdata_inizio,"-","/",1);

	$pdata_fine=get_param("data_fine");
	$pdata_fine=invertidata($pdata_fine,"-","/",1);

	$pflag_corrente=get_param("flag_corrente");
	if(!empty($pflag_corrente))
	{
		$pdata_fine=null;
		$pflag_corrente=1;
	}
	else
		$pflag_corrente=0;

	$pdescrizione_lavoro=get_param("descrizione_lavoro");
	$pdescrizione_lavoro=db_string($pdescrizione_lavoro);

	if(!empty($pidsl_curriculum_lavoro))
	{
		$update="UPDATE sibada_curriculum_lavoro SET 
			qualifica='$pqualifica', 
			datore_lavoro='$pdatore_lavoro', 
			comune_lavoro='$pcomune_lavoro', 
			prov_lavoro='$pprov_lavoro', 
			data_inizio='$pdata_inizio', 
			data_fine='$pdata_fine', 
			flag_corrente='$pflag_corrente',
			descrizione_lavoro='$pdescrizione_lavoro'
			WHERE idsibada_curriculum_lavoro='$pidsl_curriculum_lavoro'";
		$db->query($update);

		updateCVSiBada($pidsl_curriculum);
		
		echo '<script>
			window.opener.document.location.assign("./esibada_curriculum_step2.php?_update=true");
			window.close();
		</script>';
	}
}

if(!empty($pidsl_curriculum_lavoro))
{
	$sSQL="SELECT * FROM sibada_curriculum_lavoro WHERE idsibada_curriculum_lavoro='$pidsl_curriculum_lavoro'";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_curriculum_lavoro=$db->f("idsibada_curriculum_lavoro");
		$fldqualifica=$db->f("qualifica");
		$flddatore_lavoro=$db->f("datore_lavoro");
		$fldcomune_lavoro=$db->f("comune_lavoro");
		$fldprov_lavoro=$db->f("prov_lavoro");
		$flddata_inizio=$db->f("data_inizio");
		$flddata_inizio=invertidata($flddata_inizio,"/","-",2);
		$flddata_fine=$db->f("data_fine");
		$flddata_fine=invertidata($flddata_fine,"/","-",2);
		$fldflag_corrente=$db->f("flag_corrente");
		if($fldflag_corrente==1)
			$checked_corrente="checked";
		else
			$checked_corrente="";

		$flddescrizione_lavoro=$db->f("descrizione_lavoro");

		$res=$db->next_record();
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

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_step2_lavoro" style="display:none;"></div>

					<form id="step2_lavoro" method="post" enctype="multipart/form-data" action="esibada_curriculum_step2_esperienza.php" class="form-horizontal">

						<div id="div_esperienza" style="<?php echo $display_esperienza; ?>">
							<!-- <div class="row">
								<div id="title_esperienza" class="form-group col-sm-8 ">
									Esperienza lavorativa
								</div>
							</div> -->

							<div class="row mt-5">
								<div class="form-group col-sm-5">
									<div>
										<input type="text" class="form-control" id="qualifica" name="qualifica" placeholder="Inserire la qualifica" value="<?php echo $fldqualifica; ?>" >
										<label for="qualifica">Qualifica*</label>
									</div>
								</div>

								<div class="form-group col-5">
									<input type="text" class="form-control" id="datore_lavoro" name="datore_lavoro" placeholder="Inserire la denominazione" value="<?php echo $flddatore_lavoro; ?>" >
									<label for="datore_lavoro">Datore di lavoro*</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-5">
									<div>
										<input type="text" class="form-control" id="comune_lavoro" name="comune_lavoro" placeholder="Inserire la Città" value="<?php echo $fldcomune_lavoro; ?>">
										<label for="comune_lavoro">Città*</label>
									</div>
								</div>

								<div class="form-group col-5">
									<input type="text" class="form-control" id="prov_lavoro" name="prov_lavoro" placeholder="Inserire la provincia" value="<?php echo $fldprov_lavoro; ?>">
									<label for="prov_lavoro">Provincia*</label>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-5">
									<div>
										<input type="text" class="form-control" id="data_inizio" name="data_inizio" value="<?php echo $flddata_inizio; ?>" placeholder="Inserire data di inizio dell'esperienza">
										<label for="data_inizio">Data di inizio*</label>
									</div>
								</div>

								<div class="form-group col-5">
									<input type="text" class="form-control" id="data_fine" name="data_fine" value="<?php echo $flddata_fine; ?>" placeholder="Inserire data di fine dell'esperienza">
									<label for="data_fine">Data di fine*</label>
								</div>

								<div>
									<div class="form-check">
										<input id="flag_corrente" name="flag_corrente" type="checkbox" <?php echo $checked_corrente; ?>>
										<label for="flag_corrente">ad oggi</label>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-sm-10 border">
									<textarea id="descrizione_lavoro" name="descrizione_lavoro" rows="4" style="width:100%" class="form-control input-sm" maxlength="" placeholder="Inserire la descrizione delle attività svolte per questa esperienza lavorativa"><?php echo $flddescrizione_lavoro; ?></textarea>
									<label for="descrizione_lavoro">Di cosa ti sei occupato?</label>
								</div>
							</div>
						</div>

						<center>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma" value="true">Salva</button>
						</center>

						<input type="hidden" id="_id" name="_id" value="<?php echo $pidsl_curriculum_lavoro; ?>">

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

$("#step2_lavoro").submit(function(event) {

	var errors=0;
	var string_errors="";

	var qualifica=$("#qualifica").val();
	if(qualifica=="")
	{
		string_errors=string_errors+"- Qualifica; <br>";
		errors++;
	}

	var datore_lavoro=$("#datore_lavoro").val();
	if(datore_lavoro=="")
	{
		string_errors=string_errors+"- Datore di lavoro; <br>";
		errors++;
	} 

	var comune_lavoro=$("#comune_lavoro").val();
	if(comune_lavoro=="")
	{
		string_errors=string_errors+"- Città; <br>";
		errors++;
	} 

	var prov_lavoro=$("#prov_lavoro").val();
	if(prov_lavoro=="")
	{
		string_errors=string_errors+"- Provincia; <br>";
		errors++;
	}

	var data_inizio=$("#data_inizio").val();
	if(data_inizio=="")
	{
		string_errors=string_errors+"- Data di inizio; <br>";
		errors++;
	}

	var data_fine=$("#data_fine").val();
	if(data_fine=="")
	{
		if(!$('#flag_corrente').is(":checked"))
		{
			string_errors=string_errors+"- Data di fine; <br>";
			errors++;
		}
	}
	else
	{
		if($('#flag_corrente').is(":checked"))
		{
			string_errors=string_errors+"- Indicare la data di fine o spuntare la casella \"ad oggi\"; <br>";
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
	$("#alert_step2_lavoro").show();
	$("#alert_step2_lavoro").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}

</script>

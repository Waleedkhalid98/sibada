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
	$update="UPDATE sibada_curriculum SET flag_pubblica=1 WHERE idsibada_curriculum='$pidsibada_curriculum'";
	$db->query($update);

	updateCVSiBada($pidsibada_curriculum);
	
	$alert_success=true;
}

$stato_incompleto='<div style="color: '.RED.';">Incompleto</div>';
$stato_completo='<div style="color: '.GREEN.';">Completo</div>';
$stato_attenzione='<div style="color: '.ORANGE.';">Non compilato</div>';

$flag_step1_completo=true;

$beneficiario=new Beneficiario($fldidutente);

if(empty($beneficiario->cognome))
	$flag_step1_completo=false;
elseif(empty($beneficiario->nome))
	$flag_step1_completo=false;
elseif(empty($beneficiario->idamb_comune_residenza))
	$flag_step1_completo=false;
elseif(empty($beneficiario->prov))
	$flag_step1_completo=false;
elseif(empty($beneficiario->indirizzo))
	$flag_step1_completo=false;
elseif(empty($beneficiario->civico))
	$flag_step1_completo=false;
elseif(empty($beneficiario->cellulare))
	$flag_step1_completo=false;
elseif(empty($beneficiario->email))
	$flag_step1_completo=false;

if($flag_step1_completo)
	$step1=$stato_completo;
else
	$step1=$stato_incompleto;

$flag_step2_completo=true;
$nESPERIENZE=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsibada_curriculum'");
if($nESPERIENZE>0)
	$step2=$stato_completo;
else
	$step2=$stato_attenzione;

$flag_step3_completo=true;
$nTITOLI=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsibada_curriculum'");
if($nTITOLI>0)
	$step3=$stato_completo;
else
	$step3=$stato_attenzione;

$flag_step4_completo=true;

$nDISPONIBILITA=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum='$pidsibada_curriculum'");
if($nDISPONIBILITA>0)
	$step4=$stato_completo;
else
{
	$flag_step4_completo=false;
	$step4=$stato_incompleto;
}

$flag_step5_completo=true;

$fldidsibada_lingue_madre=get_db_value("SELECT idsibada_lingue_madre FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
if(!empty($fldidsibada_lingue_madre))
	$step5=$stato_completo;
else
{
	$flag_step5_completo=false;
	$step5=$stato_incompleto;
}

$fldflag_pubblica=get_db_value("SELECT flag_pubblica FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
if($fldflag_pubblica==1)
{
	$display_pubblica="display:none";
	$display_stampa="";
}
else
{
	$display_pubblica="";
	$display_stampa="display:none";
}

if(!$flag_step1_completo || !$flag_step4_completo || !$flag_step5_completo)
	$disabled="disabled";
else
	$disabled="";
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
				if($alert_success) echo(get_alert(4,"Curriculum pubblicato con successo.")); 
			?>
			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					
					<div id="alert_step6" style="display:none;"></div>

					<form id="step1" method="post" enctype="multipart/form-data" action="esibada_curriculum_step6.php" class="form-horizontal">

						<center>
							<table data-toggle="table" class="table table-hover table-condensed" style="width:60%" >
								<tbody>
									<tr>
										<th style="width: 50%" class="grassetto">Step 1 - Dati di contatto</th>
										<th style="width: 50%" class="text-right"><?php echo $step1; ?></th>
									</tr>
									<tr>
										<th style="width: 50%" class="grassetto">Step 2 - Esperienze lavorative</th>
										<th style="width: 50%" class="text-right"><?php echo $step2; ?></th>
									</tr>
									<tr>
										<th style="width: 50%" class="grassetto">Step 3 - Istruzione</th>
										<th style="width: 50%" class="text-right"><?php echo $step3; ?></th>
									</tr>
									<tr>
										<th style="width: 50%" class="grassetto">Step 4 - Disponibilità</th>
										<th style="width: 50%" class="text-right"><?php echo $step4; ?></th>
									</tr>
									<tr>
										<th style="width: 50%" class="grassetto">Step 5 - Lingue</th>
										<th style="width: 50%" class="text-right"><?php echo $step5; ?></th>
									</tr>
								</tbody>
							</table>

							<br>

							<button class="btn btn-primary" type="button" onclick="parent.loadSTEP(5);">Indietro</button>
							<button class="btn btn-primary" type="submit" id="_conferma" name="_conferma" value="true" style="<?php echo $display_pubblica; ?>" <?php echo $disabled; ?>>Ho terminato l'inserimento del mio CV</button>
							<button class="btn btn-primary" type="button" id="_stampa" name="_stampa" value="true" onClick="stampaCV(<?php echo $pidsibada_curriculum; ?>)" style="<?php echo $display_stampa; ?>">Stampa CV</button>
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
function stampaCV(idcv)
{
	settings=window_center(1100,800);
	settings+=",resizable=yes";

	var page="sibada_curriculum_stampa.php";
	var params="?_id="+idcv;
	win=window.open(page+params,"CV",settings);
	if(win.window.focus){win.window.focus();}
}
</script>

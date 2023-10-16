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

$pidsl_posizione=get_param("_idposizione");

if(get_param("_conferma"))
{
	//print_r_formatted($_POST);

	$pidsl_disponibilita=get_param("idsl_disponibilita");

	$pdescrizione=get_param("descrizione");
	$pdescrizione=db_string($pdescrizione);

	$pflag_stato=get_param("flag_stato");

	if(!empty($pidsl_posizione))
	{
		$update="UPDATE sibada_posizioni SET idsibada_disponibilita='$pidsl_disponibilita', descrizione='$pdescrizione', flag_stato='$pflag_stato' WHERE idsl_posizioni='$pidsl_posizione'";
		$db->query($update);
	}
	else
	{
		$insert="INSERT INTO sl_posizioni(idsso_anagrafica_utente,idsibada_disponibilita,descrizione,flag_stato) VALUES('$fldidutente','$pidsl_disponibilita','$pdescrizione','$pflag_stato')";
		$db->query($insert);
		$pidsl_posizione=mysql_insert_id($db->link_id());
	}

	$alert_success=true;
}

if(!empty($pidsl_posizione))
{
	$sSQL="SELECT * FROM ".DBNAME_SS.".sl_posizioni WHERE idsibada_posizioni='$pidsl_posizione'";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_disponibilita=$db->f("idsibada_disponibilita");
		$flddescrizione_posizione=$db->f("descrizione");
		$fldflag_stato=$db->f("flag_stato");

		$res=$db->next_record();
	}
}

?>
<!doctype html>
<html lang="it">
    <head>
        <title>Sibada - I miei dati</title>
        <?php echo get_importazioni_sibada_header(); ?>
       	<!--style>
		.footer {
		  position: fixed;
		  left: 0;
		  bottom: 0;
		  width: 100%;
		}
		</style-->
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
									<li class="breadcrumb-item"><a href="esibada_posizioni_elenco.php" title="Vai all'elenco delle posizioni ricercate" class="">Posizioni aperte</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a>Dettaglio posizione</a></li>
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

					<div id="alert_posizione" style="display:none;"></div>

					<br>

					<form id="posizione" method="post" enctype="multipart/form-data" action="esibada_posizione_dettaglio.php" class="form-horizontal">
						<div class="row">
							<div class="bootstrap-select-wrapper form-group col-sm-8 offset-sm-2">
							  <label>Posizione ricercata*</label>
							  <select id="idsl_disponibilita" name="idsl_disponibilita" title="Scegli un'opzione">
							  	<?php
							  		$sSQL="SELECT * FROM sibada_disponibilita";
							  		$db->query($sSQL);
							  		$res=$db->next_record();
							  		while($res)
							  		{
							  			$idsl_disponibilita=$db->f("idsibada_disponibilita");
							  			$flddescrizione=$db->f("descrizione");

							  			if($idsl_disponibilita==$fldidsl_disponibilita)
							    			echo '<option value="'.$idsl_disponibilita.'" selected>'.$flddescrizione.'</option>';
							  			else
							    			echo '<option value="'.$idsl_disponibilita.'">'.$flddescrizione.'</option>';

							  			$res=$db->next_record();
							  		}
							  	?>
							  </select>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-8 offset-sm-2">
								<textarea id="descrizione" name="descrizione" rows="6" style="width:100%" class="form-control input-sm" maxlength="" placeholder="Inserire una descrizione per la posizione che si sta cercando"><?php echo $flddescrizione_posizione; ?></textarea>
								<label for="">Descrizione*</label>
							</div>
						</div>

						<div class="row">
							<div class="bootstrap-select-wrapper form-group col-sm-8 offset-sm-2">
							  <label>Stato*</label>
							  <select id="flag_stato" name="flag_stato" title="Scegli un'opzione">
							  	<option value="0" <?php if(empty($fldflag_stato)) echo "selected"; ?>>Aperta</option>
							  	<option value="1" <?php if($fldflag_stato==1) echo "selected"; ?>>Chiusa</option>
							  </select>
							</div>
						</div>

						<center>
							<button name="_indietro" id="_indietro" type="button" class="btn btn-primary btn-md" onclick="indietro()">Indietro</button>
							<button name="_conferma" id="_conferma" type="submit" class="btn btn-primary btn-md" value="conferma">Salva</button>
						</center>

						<input type="hidden" name="_idposizione" id="_idposizione" value="<?php echo $pidsl_posizione; ?>">
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
function indietro()
{
	window.location.href=("esibada_posizioni_elenco.php");
}

$("#posizione").submit(function(event) {

	var errors=0;
	var string_errors="";

	var idsl_disponibilita=$("#idsl_disponibilita").val();
	if(idsl_disponibilita=="")
	{
		string_errors=string_errors+"- Posizione ricercata; <br>"
		errors++;
	} 

	var descrizione=$("#descrizione").val();
	if(descrizione=="")
	{
		string_errors=string_errors+"- Descrizione; <br>"
		errors++;
	}

	var flag_stato=$("#flag_stato").val();
	if(flag_stato=="")
	{
		string_errors=string_errors+"- Stato; <br>"
		errors++;
	}

	if(errors>0)
	{
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}
});

function visualizzaAlert(alert_message)
{
	$("#alert_posizione").show();
	$("#alert_posizione").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}
</script>

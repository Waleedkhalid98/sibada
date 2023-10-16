<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();

$fldidgen_utente=verifica_utente($chiave);

$pidoperatore=get_param("_id");

if(get_param("_conferma"))
{
	$fldcognome=get_param("cognome");
	$fldcognome=db_string($fldcognome);
	$fldcognome=strtoupper($fldcognome);

	$fldnome=get_param("nome");
	$fldnome=db_string($fldnome);
	$fldnome=strtoupper($fldnome);

	$fldcodicefiscale=get_param("codicefiscale");
	$fldcodicefiscale=db_string($fldcodicefiscale);
	$fldcodicefiscale=strtoupper($fldcodicefiscale);

	$fldcellulare=get_param("cellulare");
	$fldcellulare=db_string($fldcellulare);

	$fldemail=get_param("email");
	$fldemail=db_string($fldemail);

	if(!empty($pidoperatore))
	{
		$sSQL="UPDATE ".DBNAME_A.".utenti SET
			cognome='$fldcognome', 
			nome='$fldnome',
			codicefiscale='$fldcodicefiscale',
			cellulare='$fldcellulare',
			email='$fldemail',
			idtabella_stato='1'
			WHERE idutente = '$pidoperatore'";
		$db->query($sSQL);
	}
	else
	{
		$sSQL="INSERT INTO ".DBNAME_A.".utenti(
			idtabella_stato,
			cognome,
			nome,
			codicefiscale,
			cellulare,
			email
		) 
		VALUES (
			'1',
			'$fldcognome',
			'$fldnome',
			'$fldcodicefiscale',
			'$fldcellulare',
			'$fldemail'
		)";		
		$db->query($sSQL);
		$pidoperatore=mysql_insert_id($db->link_id());
	}

	$alert_success=true;
}

if(!empty($pidoperatore))
{
	$sSQL="SELECT * FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldcognome=$db->f("cognome");
		$fldnome=$db->f("nome");
		$fldcodicefiscale=$db->f("codicefiscale");
		$fldcellulare=$db->f("cellulare");
		$fldemail=$db->f("email");

		$res=$db->next_record();
	}
}

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
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

        <?php echo get_header_sibada(1); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="sibada_home.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
									<li class="breadcrumb-item"><a href="sibada_configurazione_menu.php" title="Vai alla pagina Configurazione" class="">Configurazione</a><span class="separator">/</span></li>
									<li class="breadcrumb-item"><a href="sibada_operatori_elenco.php" title="Vai all'elenco degli operatori" class="">Operatori</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a>Dettaglio Operatore</a></li>
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

					<div id="alert_operatore" style="display:none;"></div>

					<br>

					<form id="operatore" method="post" enctype="multipart/form-data" action="sibada_operatore_dettaglio.php" class="form-horizontal">
						
						<div class="row">
							<div class="form-group col-sm-6 offset-sm-3">
								<div>
									<input type="text" class="form-control" id="cognome" name="cognome" style="text-transform: uppercase;" value="<?php echo $fldcognome;?>">
									<label for="cognome">Cognome*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-3">
								<div>
									<input type="text" class="form-control" id="nome" name="nome" style="text-transform: uppercase;" value="<?php echo $fldnome;?>">
									<label for="nome">Nome*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-3">
								<div>
									<input type="text" class="form-control" id="codicefiscale" name="codicefiscale" style="text-transform: uppercase;" value="<?php echo $fldcodicefiscale;?>" maxlength="16">
									<label for="codicefiscale">Codice Fiscale*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-3">
								<div>
									<input type="text" class="form-control" id="cellulare" name="cellulare" value="<?php echo $fldcellulare;?>">
									<label for="cellulare">Cellulare</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-3">
								<div>
									<input type="text" class="form-control" id="email" name="email" value="<?php echo $fldemail;?>">
									<label for="email">E-mail*</label>
								</div>
							</div>
						</div>

						<center>
							<button name="_indietro" id="_indietro" type="button" class="btn btn-primary btn-md" onclick="indietro()">Indietro</button>
							<button name="_conferma" id="_conferma" type="submit" class="btn btn-primary btn-md" value="conferma">Salva</button>
							<?php
								if(!empty($pidoperatore) && !empty($fldemail))
									echo '<button name="_credenziali" id="_credenziali" type="button" onclick="openCREDENZIALI()" class="btn btn-primary btn-md">Invia credenziali</button>';
							?>
						</center>

						<input type="hidden" name="_id" id="_id" value="<?php echo $pidoperatore; ?>">
					</form>  
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
function indietro()
{
	window.location.href=("sibada_operatori_elenco.php");
}

$("#operatore").submit(function(event) {

	var errors=0;
	var string_errors="";

	var cognome=$("#cognome").val();
	if(cognome=="")
	{
		string_errors=string_errors+"- Cognome; <br>"
		errors++;
	} 

	var nome=$("#nome").val();
	if(nome=="")
	{
		string_errors=string_errors+"- Nome; <br>"
		errors++;
	}

	var codicefiscale=$("#codicefiscale").val();
	if(codicefiscale=="")
	{
		string_errors=string_errors+"- Codice Fiscale; <br>"
		errors++;
	}

	var email=$("#email").val();
	if(email=="")
	{
		string_errors=string_errors+"- E-mail; <br>"
		errors++;
	}

	if(errors>0)
	{
		visualizzaAlert("<b>Attenzione</b> I seguenti dati sono obbligatori:<br>"+string_errors);
		return false;
	}
});


function openCREDENZIALI()
{
	settings=window_center(400,650)
	win=window.open("sibada_operatore_password.php?_id=<?php echo $pidoperatore;?>",'credenziali',settings);
	if(win.window.focus){win.window.focus();}
}

function visualizzaAlert(alert_message)
{
	$("#alert_operatore").show();
	$("#alert_operatore").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}
</script>

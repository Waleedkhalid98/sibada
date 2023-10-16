<?php
include("./common.php");
include("../librerie/librerie.php");
include('../librerie/mail/lib.mail.php');

global $db;
global $db_front;

$chiave=get_cookieuser();

$fldidgen_utente=verifica_utente($chiave);

$pidoperatore=get_param("_id");

if(get_param("_conferma"))
{
	$pusername=get_param("username");
	$pusername=db_string($pusername);
	$ppassword=generaPassword(8,true);

	$fldemail_operatore=get_db_value("SELECT email FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");

	$fldcognome=get_db_value("SELECT cognome FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");
	$fldnome=get_db_value("SELECT nome FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");

	$fldinvio="<br>";
	$fldoggetto="Rilascio credenziali";
	$fldtesto="Gentile $fldcognome $fldnome, $fldinvio";
	$fldtesto.="di seguito le credenziali per accedere alla piattaforma: $fldinvio";
	$fldtesto.="- nome utente: <b>$pusername</b> $fldinvio";
	$fldtesto.="- password: <b>$ppassword</b> $fldinvio";


	$aEMAIL=array();
	$aEMAIL[0]=$fldemail_operatore;
	$aEMAIL[1]=$fldoggetto;
	$aEMAIL[2]=$fldtesto;
	$aEMAIL[3]="";
	$fldresult=sendMAIL($aEMAIL);

	if($fldresult=="Messaggio inviato correttamente.")
		$flag_email=true;
	else
		$flag_email=false;

	$oggi=date("Y-m-d");
	$ppassword=md5($ppassword);
	$sSQL = "UPDATE ".DBNAME_A.".utenti SET login='$pusername',password='$ppassword',data_password='$oggi' WHERE idutente='$pidoperatore'";
	$db->query($sSQL);

	$alert_success=true;
}

$pusername=get_db_value("SELECT login FROM ".DBNAME_A.".utenti WHERE idutente='$pidoperatore'");

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

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<?php
								if($alert_success && $flag_email)
									echo(get_alert(4,'Salvataggio effettuato correttamente.<br>Nuovo username: <b>'.$pusername.'.</b><br>Email con le credenziali inviata correttamente all\'indirizzo: <b>'.$fldemail_operatore.'</b>.'));
								elseif($alert_success && !$flag_email)
									echo (get_alert(0,'Salvataggio effettuato correttamente.<br>Nuovo username: <b>'.$pusername.'.</b><br><b>Attenzione:</b> l\'email con le credenziali non è stata inviata.'));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_operatore_pwd" style="display:none;"></div>

					<form id="operatore_pwd" method="post" enctype="multipart/form-data" action="sibada_operatore_password.php" class="form-horizontal">
						
						<div class="row">
							<div class="form-group col-sm-8 offset-sm-2">
								<div>
									<input type="text" class="form-control" id="username" name="username" value="<?php echo $pusername;?>">
									<label for="username">Username*</label>
								</div>
							</div>
						</div>

						<div class="col-sm-12 text-center">
							<div class="panel panel-warning">
								<div class="panel-heading">
									<h4 class="panel-title grassetto">Attenzione</h4>
								</div>
								<div class="panel-body">
									La password verrà creata automaticamente dal sistema ai sensi di Legge per quanto previsto dal GDPR 2016/679 ed inviata mediante mail all'operatore selezionato.
								</div>
							</div>
						</div>

						<br><br>

						<center>
							<button name="_conferma" id="_conferma" type="submit" class="btn btn-primary btn-md" value="true" <?php if($flag_email) echo 'disabled'; ?>>Invia</button>
						</center>

						<input type="hidden" name="_id" id="_id" value="<?php echo $pidoperatore; ?>">
					</form>  
				</main>
			</section>
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
$("#operatore_pwd").submit(function(event) {

	var errors=0;
	var string_errors="";

	var username=$("#username").val();
	if(username=="")
	{
		string_errors=string_errors+"- Username; <br>"
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
	$("#alert_operatore_pwd").show();
	$("#alert_operatore_pwd").html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+alert_message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><br>');
	window.scrollTo(0,0);
}
</script>

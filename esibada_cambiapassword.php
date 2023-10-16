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

$pflag_fornitore=get_param("_f");
if($pflag_fornitore=="true")
	$homepage="esibada_home_ditta.php";
else
	$homepage="esibada_home.php";

if (get_param("_conferma"))
{
	$ppassword_old=get_param("password_old");
	$ppassword_old=trim($ppassword_old);
	$ppassword_old=md5($ppassword_old);

	$ppassword_1=get_param("password_1");
	$ppassword_1=trim($ppassword_1);

	$ppassword_2=get_param("password_2");
	$ppassword_2=trim($ppassword_2);

	$fldpassword_old = front_get_db_value("SELECT password FROM ".FRONT_ESONAME.".gen_utente WHERE idgen_utente='$fldidgen_utente'");
	if(strcmp($ppassword_old,$fldpassword_old)!=0)
		$alert_password_old = true;
		
	if(strcmp($ppassword_1,$ppassword_2)!=0)
		$alert_password_new_1 = true;
			
	if( strlen($ppassword_1) < 8)
		$alert_password_new_length = true;

	if (!preg_match('/[A-Z]/', $ppassword_1) || !preg_match('/[a-z]/', $ppassword_1) || !preg_match('/[0-9]/', $ppassword_1) || !ctype_alnum($ppassword_1))
		$alert_password_new_check = true;

	if(!$alert_password_old && !$alert_password_new_1 && !$alert_password_new_length && !$alert_password_new_check)
	{
		$ppassword_1=md5($ppassword_1);

		$sSQL = "UPDATE ".FRONT_ESONAME.".gen_utente SET password='$ppassword_1' WHERE idgen_utente='$fldidgen_utente'";
		$db_front->query($sSQL);
		$alert_success = true;
	}
}


?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Cambia password</title>
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
								<li class="breadcrumb-item"><a href="<?php echo $homepage; ?>" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
								<li class="breadcrumb-item active" aria-current="page"><a>Cambia password</a></li>
								</ol>
							</nav>

							<?php
								if($alert_success) echo(get_alert(4,"Password aggiornata con successo."));
								if($alert_username) echo (get_alert(0,'<b>Attenzione:</b> l\'username inserita non è corretta.'));
								if($alert_password_old) echo (get_alert(0,'<b>Attenzione:</b> la vecchia password immessa non è corretta.'));
								if($alert_password_new_1) echo (get_alert(0,'<b>Attenzione:</b> le nuove password immesse non coincidono.'));
								if($alert_password_new_length) echo (get_alert(0,'<b>Attenzione:</b> la nuova password deve contenere almeno 8 caratteri.'));
								if($alert_password_new_check) echo (get_alert(0,'<b>Attenzione:</b> la password deve contenere almeno un carattere maiuscolo, <br> uno minuscolo ed un carattere numerico. <br>Non può contenere caratteri speciali.'));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="alert_anagrafica" style="display:none;"></div>

					<form id="registrazione" method="post" enctype="multipart/form-data" action="esibada_cambiapassword.php" class="form-horizontal">
						<div class="row">
							<div class="form-group col-sm-4 offset-sm-4">
								<div>
									<input type="text" class="form-control" id="password_old" name="password_old" value="">
									<label for="password_old">Vecchia password*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6 offset-sm-4">
								<h7> <b>Attenzione:</b> la password deve contenero almeno 8 caratteri, <br > di cui almeno un carattere maiuscolo, uno minuscolo <br /> ed un carattere numerico.</h7>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-4">
								<div>
									<input type="password" class="form-control" id="password_1" name="password_1" value="">
									<label for="password_1">Nuova password*</label>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-4 offset-sm-4">
								<div>
									<input type="password" class="form-control" id="password_2" name="password_2" value="">
									<label for="password_2">Ripeti password*</label>
								</div>
							</div>
						</div>

						<center>
							<button name="_conferma" id="_conferma" type="submit" class="btn btn-primary btn-md" value="conferma">Aggiorna password</button>
						</center>

						<input type="hidden" name="_f" id="_f" value="<?php echo $pflag_fornitore; ?>">
					</form>  
				</main>
			</section>
			<br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>  
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>

</script>

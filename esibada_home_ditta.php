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
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>	
        <title>Sibada - Home</title>
        <?php echo get_importazioni_sibada_header(); ?>

        <style>
		.footer {
		  position: fixed;
		  left: 0;
		  bottom: 0;
		  width: 100%;
		}
		</style>
	
    </head>	

    <body class="push-body" data-ng-app="ponmetroca">
        <div class="body_wrapper push_container clearfix" id="page_top">

	        <?php echo get_header_sibada(); ?>   

			<main id="main_container">
				<section id="sezioni-inevidenza" class="">
				    <div class="container">
				        <div class="widget">							
							<div class="row row-eq-height">
								<div class="col-md-4">								
			                        <div class="card  card-img rounded shadow ">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="esibada_aziende_dettaglio.php?_id=<?php echo $fldidutente; ?>&_f=true">
			                                            <img src="./foto/silavora_imieidati1.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>        
			                            </div>
			                            <div class="card-body">
			                                <a href="esibada_aziende_dettaglio.php?_id=<?php echo $fldidutente; ?>&_f=true">
			                                    <h5 style="color:rgb(0, 0, 0);" class="card-title">
			                                    I miei dati
			                                    </h5>
			                                </a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa sezione permette di gestire e modificare i propri dati</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="esibada_aziende_dettaglio.php?_id=<?php echo $fldidutente; ?>&_f=true">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
								</div>
				                <div class="col-md-4">
			                        <div class="card  card-img rounded shadow ">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="./esibada_curriculum_elenco.php">
			                                            <img src="./foto/silavora_cv.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>
			                            </div>
										<div class="card-body">
											<a href="./esibada_curriculum_elenco.php">
												<h5 style="color:rgb(0, 0, 0);" class="card-title" href="./esibada_curriculum_elenco.php">
												Elenco Curricula
												</h5>
											</a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa sezione permette di consultare i Curricula presenti nel sistema</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="./esibada_curriculum_elenco.php">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
				                </div>
				                <div class="col-md-4">
			                        <div class="card  card-img rounded shadow ">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="./esibada_posizioni_elenco.php">
			                                            <img src="./foto/silavora_aziende.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>
			                            </div>
										<div class="card-body">
											<a href="./esibada_posizioni_elenco.php">
												<h5 style="color:rgb(0, 0, 0);" class="card-title" href="./esibada_curriculum_elenco.php">
												Posizioni aperte
												</h5>
											</a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa sezione permette di gestire le posizioni aperte</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="./esibada_posizioni_elenco.php">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
				                </div>
							</div>
				        </div>
			        </div>
			    </div>
			</section>	
		</main>
		<br><br><br><br><br><br>
    </div>
	<?php echo get_footer_sibada(); ?>
	<?php echo get_importazioni_sibada(); ?>
    
    <style>
    .card:after{
		background: #FFFFFF;
	}
	</style>

</body>
</html>
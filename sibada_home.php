<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();
$fldidutente=verifica_utente($chiave);

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>	
        <title>Sibada - Home</title>
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
				<section id="sezioni-inevidenza" class="">
				    <div class="container">
				        <div class="widget">							
							<div class="row row-eq-height justify-content-center">
								<div class="col-md-4 mt-3">								
			                        <div class="card  card-img rounded shadow ">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="sibada_utenti_registrati.php">
			                                            <img src="./foto/silavora_imieidati1.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>        
			                            </div>
			                            <div class="card-body">
			                                <a href="sibada_utenti_registrati.php">
			                                    <h5 style="color:rgb(0, 0, 0);" class="card-title" href="sibada_utenti_registrati.php">
			                                    Elenco utenti registrati
			                                    </h5>
			                                </a>
		                                    <div class="card-text">
		                                        <div>
		                                            <p>Questa funzione permette di consultare l'elenco degli utenti registrati</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="sibada_utenti_registrati.php">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
								</div>
				                <!-- <div class="col-md-4 mt-3">                              
			                        <div class="card  card-img rounded shadow">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="./sibada_aziende_elenco.php">
			                                            <img src="./foto/silavora_aziende.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>        
			                            </div>
			                            <div class="card-body">
											<a href="./sibada_aziende_elenco.php">
												<h5 style="color:rgb(0, 0, 0);" class="card-title" href="./sibada_aziende_elenco.php">
												Elenco ditte registrate
												</h5>
											</a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa funzione permette di consultare l'elenco delle ditte registrate</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="./sibada_aziende_elenco.php">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
				                </div> -->
				                <div class="col-md-4 mt-3">                              
			                        <div class="card  card-img rounded shadow">
			                            <div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="./sibada_configurazione_menu.php">
			                                            <img src="./foto/silavora_configurazione.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="card-body">
											<a href="./sibada_configurazione_menu.php">
												<h5 style="color:rgb(0, 0, 0);" class="card-title" href="./sibada_configurazione_menu.php">
												Configurazione
												</h5>
											</a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa funzione permette di gestire la configurazione della piattaforma</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="./sibada_configurazione_menu.php">
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
			<br><br><br><br><br><br><br>
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
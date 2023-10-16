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
        <title>Sibada - Configurazione</title>
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

	        <?php echo get_header_sibada(1); ?>   

			<main id="main_container">
				<section id="briciole">
					<div class="container">
						<div class="row">
							<div class="offset-lg-1 col-lg-10 col-md-12">
								<nav class="breadcrumb-container" aria-label="breadcrumb">
									<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="sibada_home.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
									<li class="breadcrumb-item active" aria-current="page"><a>Configurazione</a></li>
									</ol>
								</nav>
							</div>
						</div>
					</div>
				</section>

				<section id="sezioni-inevidenza" >
				    <div class="container">
				        <div class="widget">							
							<div class="row row-eq-height">
								<div class="col-md-4">								
			                        <div class="card  card-img rounded shadow ">
			                            <!--div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="sibada_operatori_elenco.php">
			                                            <img src="./foto/sibada_utenti.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>        
			                            </div-->
			                            <div class="card-body">
			                                <a href="sibada_operatori_elenco.php">
			                                    <h5 style="color:rgb(0, 0, 0);" class="card-title" href="sibada_operatori_elenco.php">
			                                    Elenco operatori
			                                    </h5>
			                                </a>
		                                    <div class="card-text">
		                                        <div>
		                                            <p>Questa funzione permette di consultare l'elenco degli operatori abilitati ad operare in piattaforma</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="sibada_operatori_elenco.php">
	                                            <span class="text">Prosegui</span>
	                                            <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_forward"></use></svg>
	                                        </a>
			                            </div>
			                        </div>
								</div>
				                <div class="col-md-4">                     
			                        <div class="card  card-img rounded shadow">
			                            <!--div class="img-responsive-wrapper">
			                                <div class="img-responsive">
			                                    <div class="img-wrapper">
			                                        <a href="./sibada_mansioni_elenco.php">
			                                            <img src="./foto/sibada_aziende.png" class="img-fluid" style="border: 0px  ;" alt="" title="">
			                                        </a>
			                                    </div>
			                                </div>        
			                            </div-->
			                            <div class="card-body">
											<a href="./sibada_mansioni_elenco.php">
												<h5 style="color:rgb(0, 0, 0);" class="card-title" href="./sibada_mansioni_elenco.php">
												Qualifiche
												</h5>
											</a>
		                                    <div class="card-text">                                 
		                                        <div>
		                                            <p>Questa sezione permette di gestire le qualifiche nel sistema</p>
		                                        </div>
		                                    </div>
	                                        <a class="read-more" href="./sibada_mansioni_elenco.php">
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
			<br><br><br><br>
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
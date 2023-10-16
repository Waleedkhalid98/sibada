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

?>
<!doctype html>
<html lang="it">
    <head>
        <title>Sibada - Curriculum</title>
        <?php echo get_importazioni_sibada_header(); ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
		<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>

		<style>
			 .container_frame {
			    position: relative;
			    width: 100%;
			    height: 0;
			    padding-bottom: 56.25%;
			 }
			 .iframe_step {
			    position: absolute;
			    top: 0;
			    left: 0;
			    width: 100%;
			    height: 100%;
			 }

			 iframe {
				overflow: hidden;
			}
			@media (max-width: 576px) {
			.bs-stepper-header {
				overflow-x: auto;
				white-space: nowrap; 
			}
			}
		</style>
    </head> 
<body class="push-body" data-ng-app="ponmetroca">
    <div class="body_wrapper push_container clearfix" id="page_top">

        <?php echo get_header_sibadafront(); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">                   
								<li class="breadcrumb-item"><a href="esibada_home.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
								<li class="breadcrumb-item active" aria-current="page"><a>Curriculum</a></li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<div id="stepper1" class="bs-stepper">
						<div class="bs-stepper-header " role="tablist">
							<div class="step" data-target="#test-l-1" onclick="loadSTEP(1)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="test-l-1">
								<span class="bs-stepper-circle">1</span>
								<span class="bs-stepper-label">Dati di contatto</span>
								</button>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-2" onclick="loadSTEP(2)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="test-l-2">
								<span class="bs-stepper-circle">2</span>
								<span class="bs-stepper-label">Esperienze lavorative</span>
								</button>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-3" onclick="loadSTEP(3)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="test-l-3">
								<span class="bs-stepper-circle">3</span>
								<span class="bs-stepper-label">Istruzione</span>
								</button>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-4" onclick="loadSTEP(4)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger4" aria-controls="test-l-4">
								<span class="bs-stepper-circle">4</span>
								<span class="bs-stepper-label">Disponibilità</span>
								</button>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-5" onclick="loadSTEP(5)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger5" aria-controls="test-l-5">
								<span class="bs-stepper-circle">5</span>
								<span class="bs-stepper-label">Lingue</span>
								</button>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-6" onclick="loadSTEP(6)">
								<button type="button" class="step-trigger" role="tab" id="stepper1trigger6" aria-controls="test-l-6">
								<span class="bs-stepper-circle">6</span>
								<span class="bs-stepper-label">Riepilogo</span>
								</button>
							</div>
						</div>
						<div class="bs-stepper-content">
							<form onSubmit="return false">
								
								<div id="test-l-1" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger1">
									<div id="step1" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step1" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>

								<div id="test-l-2" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger2">
									<div id="step2" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step2" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>

								<div id="test-l-3" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger3">
									<div id="step3" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step3" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>

								<div id="test-l-4" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger4">
									<div id="step4" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step4" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>

								<div id="test-l-5" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger5">
									<div id="step5" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step5" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>

								<div id="test-l-6" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger6">
									<div id="step6" class="embed-responsive embed-responsive-4by3">
										<iframe id="frame_step6" class="embed-responsive-item" src=""></iframe>
									</div>
								</div>
							</form>
						</div>
					</div>

				</main>
			</section>

			<?php echo get_footer_sibada(); ?>  
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
var stepper1Node = document.querySelector('#stepper1')
var stepper1 = new Stepper(document.querySelector('#stepper1'))

stepper1Node.addEventListener('show.bs-stepper', function (event) {
	console.warn('show.bs-stepper', event)
})

stepper1Node.addEventListener('shown.bs-stepper', function (event) {
	console.warn('shown.bs-stepper', event)
})

<?php 
if(!empty($pidsibada_curriculum))
{
	$fldflag_pubblica=get_db_value("SELECT flag_pubblica FROM sibada_curriculum WHERE idsibada_curriculum='$pidsibada_curriculum'");
	if($fldflag_pubblica==1)
		echo 'loadSTEP(6)';
	else
		echo 'loadSTEP(1)';
}
else
	echo 'loadSTEP(1)';
?>

function loadSTEP(idstep)
{
	var page="esibada_curriculum_step"+idstep+".php";

	//alert(page)

	$("#frame_step"+idstep).attr("src",page);
	stepper1.to(idstep);
}

</script>

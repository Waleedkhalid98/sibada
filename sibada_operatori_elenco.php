<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();
$fldidgen_utente=verifica_utente($chiave);
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Operatori</title>
        <?php echo get_importazioni_sibada_header(); ?>

		<STYLE TYPE="text/css">
		 
			td { padding-left: 7px; padding-right: 7px;} 
		
		</STYLE>
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
									<li class="breadcrumb-item active" aria-current="page"><a>Elenco operatori</a></li>
								</ol>
							</nav>

							<br>

							<?php
								if($alert_success) echo(get_alert(4,"Mansione inserita con successo."));
								if($alert_esiste) echo(get_alert(0,"Attenzione! mansione già presente in archivio."));
								if($alert_delete) echo(get_alert(4,"Mansione eliminata con successo."));
								if($alert_delete_cv) echo(get_alert(0,"Attenzione! impossibile eliminare la mansione in quanto è stata utilizzata in <b>".$nUSE_CV."</b> curricula."));
								if($alert_delete_posizioni) echo(get_alert(0,"Attenzione! impossibile eliminare la mansione in quanto è stata utilizzata in <b>".$nUSE_POSIZIONI."</b> posizioni aperte dalle aziende."));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<br>

					<table id="table_operatori" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Nominativo</th>
								<th scope="col" width="10%">Codice Fiscale</th>
								<th scope="col" width="10%">E-mail</th>
								<th scope="col" width="10%">Cellulare</th>
								<th scope="col" width="10%">
									<button type="button" class="btn btn-xs btn-outline-primary" onclick="openOPERATORE(0)">
						            	<svg class="icon icon-xs icon-primary">
						            		<use xlink:href="static/img/sprite.svg#it-plus"></use>
						            	</svg>
							            &nbsp;Aggiungi
							        </button>
								</th>
							</tr>
						</thead>
						<tbody style="font-size: 15px;">
						<?php
							$sSQL="SELECT * FROM ".DBNAME_A.".utenti";
							$db->query($sSQL);
							$next_record=$db->next_record();
							$counter=1;
							$aUTENTI=array();
							while($next_record)
							{
								$fldidutente=$db->f("idutente");
								$fldcognome=$db->f("cognome");
								$fldnome=$db->f("nome");
								$fldcodicefiscale=$db->f("codicefiscale");
								$fldemail=$db->f("email");
								$fldcellulare=$db->f("cellulare");
								
								echo '<tr>
									<td>'.$counter.'</td>
									<td>'.$fldcognome.' '.$fldnome.'</td>
									<td>'.$fldcodicefiscale.'</td>
									<td>'.$fldcellulare.'</td>
									<td>'.$fldemail.'</td>
									<td>
										<button type="button" class="btn btn-xs btn-outline-warning" onclick="openOPERATORE('.$fldidutente.')">
							            	<svg class="icon icon-xs icon-warning">
							            		<use xlink:href="static/img/sprite.svg#it-pencil"></use>
							            	</svg>
								            &nbsp;Modifica
								        </button>
								    </td>
								</tr>';

								$counter++;

								$next_record=$db->next_record();
							}
						?>
						</tbody>
					</table>
				</main>
			</section>
			<br><br><br><br><br><br><br><br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
function openOPERATORE(id)
{
	window.location.href=("sibada_operatore_dettaglio.php?_id="+id);
}
</script>

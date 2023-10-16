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

if(get_param("_delete"))
{
	$pidsl_posizione=get_param("_idposizione");

	if(!empty($pidsl_posizione))
	{
		$delete="DELETE FROM sl_posizioni WHERE idsibada_posizioni='$pidsl_posizione'";
		$db->query($delete);

		$alert_delete=true;
	}
}
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Aziende</title>
        <?php echo get_importazioni_sibada_header(); ?>

		<STYLE TYPE="text/css">
		 
			td { padding-left: 7px; padding-right: 7px;} 
		
		</STYLE>
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
								<li class="breadcrumb-item active" aria-current="page"><a>Posizioni aperte</a></li>
								</ol>
							</nav>

							<?php
								if($alert_delete) echo(get_alert(4,"Posizione eliminata con successo."));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">

					<table id="table_posizioni" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Posizione</th>
								<th scope="col" width="40%">Descrizione</th>
								<th scope="col" width="5%">Stato</th>
								<th scope="col" width="20%"></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$sSQL="SELECT * FROM sl_posizioni WHERE idsso_anagrafica_utente='$fldidutente' ORDER BY idsibada_posizioni ASC";
							$db->query($sSQL);
							$next_record=$db->next_record();
							$counter=1;
							while($next_record)
							{
								$fldidsl_posizioni=$db->f("idsibada_posizioni");
								$fldidsl_disponibilita=$db->f("idsibada_disponibilita");
								$flddisponibilita=get_db_value("SELECT descrizione FROM sibada_disponibilita WHERE idsibada_disponibilita='$fldidsl_disponibilita'");
								$flddescrizione=$db->f("descrizione");
								if(strlen($flddescrizione)>100)
									$flddescrizione = substr($flddescrizione,0,100).'...';

								$fldflag_stato=$db->f("flag_stato");
								switch($fldflag_stato)
								{
									case 0:
										$stato="Aperta";
										$class_stato="text-success";
										break;

									case 1:
										$stato="Chiusa";
										$class_stato="text-danger";
										break;
								}

								echo '<tr>
									<td>'.$counter.'</td>
									<td>'.$flddisponibilita.'</td>
									<td>'.$flddescrizione.'</td>
									<td class="'.$class_stato.'"><b>'.$stato.'</b></td>
									<td>
										<button type="button" class="btn btn-xs btn-outline-warning" onclick="openPOSIZIONE('.$fldidsl_posizioni.')">
							            	<svg class="icon icon-xs icon-warning">
							            		<use xlink:href="static/img/sprite.svg#it-pencil"></use>
							            	</svg>
								            &nbsp;Modifica
								        </button>
										<button type="button" class="btn btn-xs btn-outline-danger" onclick="deletePOSIZIONE('.$fldidsl_posizioni.')">
							            	<svg class="icon icon-xs icon-danger">
							            		<use xlink:href="static/img/sprite.svg#it-delete"></use>
							            	</svg>
								            &nbsp;Elimina
								        </button>
									</td>
								</tr>';

								$counter++;

								$next_record=$db->next_record();
							}	
						?>
						</tbody>
					</table>

					<br><br>
					<center>
						<button type="button" class="btn btn-md btn-primary" onclick="openPOSIZIONE(0)">Aggiungi posizione</button>
				    </center>
				</main>
			</section>
			<br><br><br><br><br><br><br><br><br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
function openPOSIZIONE(idposizione)
{
	window.location.href=("esibada_posizione_dettaglio.php?_idposizione="+idposizione);
}

function deletePOSIZIONE(idposizione)
{
	window.location.href=("esibada_posizioni_elenco.php?_delete=true&_idposizione="+idposizione);
}
</script>

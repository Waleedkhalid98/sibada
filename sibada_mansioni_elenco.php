<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();

$fldidgen_utente=verifica_utente($chiave);

if(get_param("_delete"))
{
	$pidsl_disponibilita=get_param("_id");
	if(!empty($pidsl_disponibilita))
	{
		$nUSE_CV=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_disponibilita WHERE idsibada_disponibilita='$pidsl_disponibilita'");
		$nUSE_POSIZIONI=get_db_value("SELECT COUNT(*) FROM sl_posizioni WHERE idsibada_disponibilita='$pidsl_disponibilita'");

		if(empty($nUSE_CV) && empty($nUSE_POSIZIONI))
		{
			$delete="DELETE FROM sibada_disponibilita WHERE idsibada_disponibilita='$pidsl_disponibilita'";
			$db->query($delete);
			$alert_delete=true;
		}
		else
		{
			if(!empty($nUSE_CV))
				$alert_delete_cv=true;

			if(!empty($nUSE_POSIZIONI))
				$alert_delete_posizioni=true;
		}
	}
}

if(get_param("_add"))
{
	$pdescrizione=get_param("_desc");
	$pdescrizione=db_string($pdescrizione);
	if(!empty($pdescrizione))
	{
		$fldidsl_disponibilita=get_db_value("SELECT idsibada_disponibilita FROM sibada_disponibilita WHERE descrizione LIKE '$pdescrizione'");
		if(empty($fldidsl_disponibilita))
		{
			$insert="INSERT INTO sibada_disponibilita(descrizione) VALUES('$pdescrizione')";
			$db->query($insert);
			$alert_success=true;
		}
		else
			$alert_esiste=true;
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
									<li class="breadcrumb-item active" aria-current="page"><a>Mansioni</a></li>
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

					<table id="table_posizioni" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="40%">Descrizione</th>
								<th scope="col" width="10%"></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$sSQL="SELECT * FROM sibada_disponibilita ORDER BY descrizione";
							$db->query($sSQL);
							$next_record=$db->next_record();
							$counter=1;
							while($next_record)
							{
								$fldidsl_disponibilita=$db->f("idsibada_disponibilita");
								$flddescrizione=$db->f("descrizione");
								
								echo '<tr>
									<td>'.$counter.'</td>
									<td>'.$flddescrizione.'</td>
									<td>
										<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteMANSIONE('.$fldidsl_disponibilita.')">
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
						<tfoot>
							<tr>
								<td></td>
								<td>
									<input type="text" class="input-sm" id="descrizione" name="descrizione" value="">
								</td>
								<td>
									<button type="button" class="btn btn-xs btn-outline-primary" onclick="addMANSIONE()">
						            	<svg class="icon icon-xs icon-primary">
						            		<use xlink:href="static/img/sprite.svg#it-plus"></use>
						            	</svg>
							            &nbsp;Aggiungi
							        </button>
								</td>
							</tr>
						</tfoot>
					</table>
				</main>
			</section>
			<br><br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
function addMANSIONE()
{
	var descrizione=$("#descrizione").val();
	if(descrizione!="")
	{
		window.location.href=("sibada_mansioni_elenco.php?_add=true&_desc="+descrizione);
	}
}

function deleteMANSIONE(id)
{
	window.location.href=("sibada_mansioni_elenco.php?_delete=true&_id="+id);
}
</script>

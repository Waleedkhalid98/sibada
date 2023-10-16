<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuser();

$fldidgen_utente=verifica_utente($chiave);

$pricerca=get_param("_ricerca");
$pdenominazione=get_param("denominazione");

$sPAGE=str_replace(".","_",basename($_SERVER['PHP_SELF']));
if(empty($pricerca) && empty($_SESSION[$sPAGE]))
{
	//Primo caricamento della pagina: non faccio nulla
}
else
{
	$aCOOKIE=array();	
	$aCOOKIE["denominazione"]=$pdenominazione;
	$sCOOKIE=serialize($aCOOKIE);
	$_SESSION[$sPAGE]=$sCOOKIE;
}

?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Sibada - Aziende</title>
        <?php echo get_importazioni_sibada_header(); ?>
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
								<li class="breadcrumb-item active" aria-current="page"><a>Aziende</a></li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<form action="sibada_aziende_elenco.php" method="post">
						<table width="100%">
							<tr>
								<td width="20%" valign="bottom" class="intestazioneTabella">Denominazione</td>
								<td width="2%"></td>
								<td width="20%" valign="bottom" class="intestazioneTabella"></td>
							</tr>
							<tr>
								<td>
									<input name="denominazione" id="denominazione" type="text" class="form-control input-xs" value="<?php echo $pdenominazione; ?>">
								</td>
								<td></td>
								<td>
						            <button type="submit" name="_ricerca" id="_ricerca" value="true" class="btn btn-xs btn-outline-primary">
						            	<svg class="icon icon-xs icon-primary">
						            		<use xlink:href="static/img/sprite.svg#it-search"></use>
						            	</svg>
							            &nbsp;Avvia ricerca
							        </button>
								</td> 
							</tr>
						</table>

					</form>

					<div id="alert_aziende" style="display:none;"></div>

					<br><br>

					<table id="table_fornitori" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="15%">Denominazione</th>
								<th scope="col" width="10%">Partita IVA</th>
								<th scope="col" width="10%">Città</th>
								<th scope="col" width="5%"></th>
							</tr>
						</thead>
						<tbody style="font-size: 15px;">
						<?php
							if((!empty($pricerca) || $_SESSION[$sPAGE]))
							{
								$sSelect="SELECT sso_anagrafica_utente.idutente,sso_ente_servizio.settore ";
								
								$sFrom=" FROM sso_anagrafica_utente 
								INNER JOIN sso_ente_servizio ON sso_anagrafica_utente.idutente=sso_ente_servizio.idutente";
								
								if(!empty($pidsso_tabella_tipologia_ente))
								{
									$sWhere=aggiungi_condizione($sWhere, "idsso_tabella_tipologia_ente='$pidsso_tabella_tipologia_ente'");
								}

								if(!empty($pdenominazione))
								{
									$pdenominazione=db_string($pdenominazione);
									$sWhere=aggiungi_condizione($sWhere, "cognome LIKE '%$pdenominazione%'");
								}

								if(!empty($psettore))
								{
									$psettore=db_string($psettore);
									$sWhere=aggiungi_condizione($sWhere, "settore LIKE '%$psettore%'");
								}

								if(!empty($sWhere))
									$sWhere=" WHERE ".$sWhere;
								
								$sOrder=" ORDER BY cognome";
												
								$sSQL=$sSelect.$sFrom.$sWhere.$sOrder;
								$db->query($sSQL);
								$next_record=$db->next_record();
								$counter=1;

								while($next_record)
								{
									$fldidutente=$db->f("idutente");
									$fornitore=new Fornitore($fldidutente);

									echo '<tr>
										<td>'.$counter.'</td>
										<td>'.$fornitore->cognome.'</td>
										<td>'.$fornitore->piva.'</td>
										<td>'.strtoupper($fornitore->citta).'</td>
										<td>
											<button type="submit" name="btn_fornitore" id="btn_fornitore" class="btn btn-xs btn-outline-warning" onclick="openFORNITORE('.$fldidutente.')">
											<svg class="icon icon-xs icon-warning">
							            		<use xlink:href="static/img/sprite.svg#it-pencil"></use>
							            	</svg>
								            &nbsp;Dettaglio
									        </button>
									    </td>
									</tr>';

									$counter++;
									$next_record = $db->next_record();  
								}	

							}
						?>
						</tbody>
					</table>
				</main>
			</section>
			<br><br><br><br><br><br><br><br><br><br><br>
			<?php echo get_footer_sibada(); ?>  
	    </main>
    </div>

    <?php echo get_importazioni_sibada(); ?>
</body>

</html>

<script>
function openFORNITORE(idfornitore)
{
	window.location.href=("./sibada_aziende_dettaglio.php?_id="+idfornitore);
}
</script>

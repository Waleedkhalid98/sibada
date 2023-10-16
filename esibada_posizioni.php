<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;

$chiave=get_cookieuserFront();

$fldidgen_utente=verifica_eutente($chiave);
$fldidutente=front_get_db_value("select idsso_anagrafica_utente from ".FRONT_ESONAME.".eso_join_anagrafica where idgen_utente='$fldidgen_utente'");
if(empty($fldidutente) || empty($fldidgen_utente))
    die("Attenzione! sessione scaduta");

$pricerca=get_param("_ricerca");
$pdenominazione=get_param("denominazione");
$pdisponibilita=get_param("_disponibilita");
$aDISPONIBILITA=explode(",",$pdisponibilita);

$sPAGE=str_replace(".","_",basename($_SERVER['PHP_SELF']));
if(empty($pricerca) && empty($_SESSION[$sPAGE]))
{
	//Primo caricamento della pagina: non faccio nulla
}
else
{
	$aCOOKIE=array();	
	$aCOOKIE["denominazione"]=$pdenominazione;
	$aCOOKIE["_disponibilita"]=$pdisponibilita;
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

        <?php echo get_header_sibada(); ?>

		<main id="main_container">
			<section id="briciole">
				<div class="container">
					<div class="row">
						<div class="offset-lg-1 col-lg-10 col-md-12">
							<nav class="breadcrumb-container" aria-label="breadcrumb">
								<ol class="breadcrumb">                   
								<li class="breadcrumb-item"><a href="esibada_home.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
								<li class="breadcrumb-item active" aria-current="page"><a>Aziende</a></li>
								</ol>
							</nav>

							<?php
								if($alert_success) echo(get_alert(4,"Salvataggio avvenuto con successo."));
								if($alert_file_success) echo(get_alert(4,"Documento di riconoscimento aggiornato correttamente."));
							?>
						</div>
					</div>
				</div>
			</section>

			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 my-4">
					<form action="esibada_posizioni.php" method="post" onSubmit="loadMULTI()">
						<table width="100%">
							<tr>
								<td width="20%" valign="bottom" class="intestazioneTabella">Denominazione</td>
								<td width="2%"></td>
								<td width="20%" valign="bottom" class="intestazioneTabella">Mansione</td>
								<td width="2%"></td>
								<td width="20%" valign="bottom" class="intestazioneTabella"></td>
							</tr>
							<tr>
								<td>
									<input name="denominazione" id="denominazione" type="text" class="form-control input-xs" value="<?php echo $pdenominazione; ?>">
								</td>
								<td></td>
								<td>
									<div class="bootstrap-select-wrapper">
									  <select id="multi_disponibilita" name="multi_disponibilita" title="Scegli una o più opzioni" multiple="true" data-multiple-separator="">
									  	<?php
									  		$sSQL="SELECT * FROM sibada_disponibilita";
									  		$db->query($sSQL);
									  		$res=$db->next_record();
									  		while($res)
									  		{
									  			$idsl_disponibilita=$db->f("idsibada_disponibilita");
									  			$flddescrizione=$db->f("descrizione");

									  			if(in_array($idsl_disponibilita,$aDISPONIBILITA))
									    			echo '<option value="'.$idsl_disponibilita.'" selected data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';
									  			else
									    			echo '<option value="'.$idsl_disponibilita.'" data-content="<span class=\'select-pill\'><span class=\'select-pill-text\'>'.$flddescrizione.'</span></span>"></option>';

									  			$res=$db->next_record();
									  		}
									  	?>
									  </select>
									</div>
									<input type="hidden" id="_disponibilita" name="_disponibilita" value="">
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

					<div id="alert_posizioni" style="display:none;"></div>

					<br><br>

					<table id="table_fornitori" class="table">
						<table id="table_posizioni" class="table">
						<thead>
							<tr>
								<th scope="col" width="5%"></th>
								<th scope="col" width="10%">Denominazione</th>
								<th scope="col" width="10%">Recapiti</th>
								<th scope="col" width="10%">Città</th>
								<th scope="col" width="10%">Mansione</th>
								<th scope="col" width="45%">Descrizione</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$sSelect="SELECT * FROM sl_posizioni 
								INNER JOIN sso_anagrafica_utente ON sso_anagrafica_utente.idutente=sl_posizioni.idsso_anagrafica_utente ";

								$sWhere=aggiungi_condizione($sWhere, "(sl_posizioni.flag_stato='0' OR sl_posizioni.flag_stato IS NULL)");

								if(!empty($pdisponibilita))
								{
									if(!empty($aDISPONIBILITA))
									{
										$condizione_disponibilita="";
										foreach($aDISPONIBILITA as $iddisponibilita)
										{
											$condizione_disponibilita.="sl_posizioni.idsibada_disponibilita='$iddisponibilita' OR ";
										}

										$condizione_disponibilita = rtrim($condizione_disponibilita, " OR ");

										$sWhere=aggiungi_condizione($sWhere, "(".$condizione_disponibilita.")");
									}
								}

								if(!empty($sWhere))
									$sWhere=" WHERE ".$sWhere;
								
								$sOrder=" ORDER BY idsibada_posizioni ASC";

								$sSQL=$sSelect.$sWhere.$sOrder;
								$db->query($sSQL);
								$res=$db->next_record();
								$counter=1;
								while($res)
								{
									$fldidsl_posizioni=$db->f("idsibada_posizioni");
									$fldidsso_anagrafica_utente=$db->f("idsso_anagrafica_utente");
									$fornitore=new Fornitore($fldidsso_anagrafica_utente);
									$fldidsl_disponibilita=$db->f("idsibada_disponibilita");
									$flddisponibilita=get_db_value("SELECT descrizione FROM sibada_disponibilita WHERE idsibada_disponibilita='$fldidsl_disponibilita'");
									$flddescrizione=$db->f("descrizione");

									echo '<tr>
										<td>'.$counter.'</td>
										<td>'.$fornitore->cognome.'</td>
										<td>'.$fornitore->recapito.'</td>
										<td>'.$fornitore->citta.'</td>
										<td>'.$flddisponibilita.'</td>
										<td>'.$flddescrizione.'</td>
									</tr>';

									$counter++;


									$res=$db->next_record();
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
function loadMULTI()
{
	var foo1 = []; 

	$('#multi_disponibilita :selected').each(function(i, selected){ 
		foo1[i] = $(selected).val(); 
	});

	$("#_disponibilita").val(foo1);
}
</script>

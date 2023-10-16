<?php
include("./common.php");
include("../librerie/librerie.php");
//require_once('../librerie/fpdf/fpdf.php');
//require_once('../librerie/fpdf/fpdi.php');
require('../fpdf/mc_table.php');

global $db;
global $db_front;

$db2 = new DB_Sql();
$db2->Database = DATABASE_NAME;
$db2->User     = DATABASE_USER;
$db2->Password = DATABASE_PASSWORD;
$db2->Host     = DATABASE_HOST;

$pidsl_curriculum=get_param("_id");

if(empty($pidsl_curriculum))
	die("Attenzione! impossibile stampare il Curriculum Vitae");

$pdf = new PDF_MC_Table();
$pdf->addPage();

$fldpath_file=get_db_value("SELECT path_file FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
$fldfilename=get_db_value("SELECT filename FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
if(file_exists($fldpath_file.$fldfilename))
{
    $file_avatar=$fldpath_file.$fldfilename;
}
else
{
    $file_avatar="../sibada/foto/avatar.png";
}

if(file_exists($file_avatar))
{
    $pdf->Image($file_avatar,0,0,60,60);
}

$fldidutente=get_db_value("SELECT idutente FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
$beneficiario=new beneficiario($fldidutente);

$pdf->AddFont('RobotoMono-Regular','','RobotoMono-Regular.php');
$pdf->AddFont('RobotoMono-Bold','','RobotoMono-Bold.php');
$pdf->AddFont('TitilliumWeb-Black','','TitilliumWeb-Black.php');
$pdf->AddFont('TitilliumWeb-Regular','','TitilliumWeb-Regular.php');
$pdf->AddFont('TitilliumWeb-Bold','','TitilliumWeb-Bold.php');

$pdf->setFillColor(35,66,149);
$pdf->Rect(0, 60, 60, 240,'F');

$pdf->SetFont("TitilliumWeb-Bold",'',16);
$pdf->SetXY(5,60);
$pdf->SetTextColor(255,255,255);
$pdf->MultiCell(250,30,"Dati di contatto",0,'L');

$pdf->SetFont("TitilliumWeb-Regular",'',10);
$pdf->SetXY(5,70);
$pdf->SetTextColor(255,255,255);
$pdf->MultiCell(250,30,$beneficiario->indirizzo." ".$beneficiario->civico,0,'L');

$pdf->SetXY(5,77);
$pdf->SetTextColor(255,255,255);
$pdf->MultiCell(250,30,$beneficiario->recapito,0,'L');

$pdf->SetXY(5,84);
$pdf->SetTextColor(255,255,255);
$pdf->MultiCell(250,30,$beneficiario->email,0,'L');

//$pdf->SetFont('ocrb','',18);
$pdf->SetFont("TitilliumWeb-Bold",'',20);
$pdf->SetTextColor(35,66,149);
$pdf->SetXY(70,5);
$pdf->MultiCell(250,30,$beneficiario->nominativo,0,'L');

$pdf->SetFont("TitilliumWeb-Bold",'',14);
$pdf->SetTextColor(35,66,149);
$pdf->SetX(70);
$pdf->MultiCell(250,10,"Esperienze lavorative",0,'L');

$nLAVORO=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsl_curriculum'");
if($nLAVORO>0)
{
	$sSQL="SELECT * FROM sibada_curriculum_lavoro WHERE idsibada_curriculum='$pidsl_curriculum' ORDER BY idsibada_curriculum_lavoro DESC";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_curriculum_lavoro=$db->f("idsibada_curriculum_lavoro");
		$fldqualifica=$db->f("qualifica");
		$flddatore_lavoro=$db->f("datore_lavoro");
		$fldcomune_lavoro=$db->f("comune_lavoro");
		$fldprov_lavoro=$db->f("prov_lavoro");

		$flddata_inizio=$db->f("data_inizio");
		$flddata_inizio=invertidata($flddata_inizio,"/","-",2);

		$flddata_fine=$db->f("data_fine");
		$flddata_fine=invertidata($flddata_fine,"/","-",2);

		$fldflag_corrente=$db->f("flag_corrente");
		if($fldflag_corrente==1)
		{
			$checked_corrente="checked";
			$string_periodo='dal '.$flddata_inizio.' ad oggi';
		}
		else
		{
			$checked_corrente="";
			$string_periodo='dal '.$flddata_inizio.' al '.$flddata_fine;
		}

		$flddescrizione_lavoro=$db->f("descrizione_lavoro");

		$pdf->SetFont("TitilliumWeb-Regular",'',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetX(70);

		$stringa_esperienza="- ".$fldqualifica."\npresso ".$flddatore_lavoro." - ".$fldcomune_lavoro." (".$fldprov_lavoro.")\n".$string_periodo."\n".$stringa_esperienza;
		$stringa_esperienza=iconv('UTF-8', 'windows-1252',$stringa_esperienza);
		$pdf->Row_nb(array($stringa_esperienza));

		$pdf->ln();
		
		$res=$db->next_record();
	}
}
else
{
	$pdf->SetFont("TitilliumWeb-Regular",'',10);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetX(70);
	$pdf->Row_nb(array("Nessuna esperienza lavorativa."));

	$pdf->ln();
}

$pdf->SetFont("TitilliumWeb-Bold",'',14);
$pdf->SetTextColor(35,66,149);
$pdf->SetX(70);
$pdf->MultiCell(250,10,"Istruzione",0,'L');

$nISTRUZIONE=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsl_curriculum'");
if($nISTRUZIONE>0)
{
	$aMESI_LETTERE=array(1=>"Gennaio",2=>"Febbraio",3=>"Marzo",4=>"Aprile",5=>"Maggio",6=>"Giugno",7=>"Luglio",8=>"Agosto",9=>"Settembre",10=>"Ottobre",11=>"Novembre",12=>"Dicembre");
	$sSQL="SELECT * FROM sibada_curriculum_studio WHERE idsibada_curriculum='$pidsl_curriculum' ORDER BY idsibada_curriculum_studio DESC";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_curriculum_studio=$db->f("idsibada_curriculum_studio");
		$fldistituto_scolastico=$db->f("istituto_scolastico");
		$fldcomune_istituto=$db->f("comune_istituto");
		$fldidsl_grado_istruzione=$db->f("idsibada_grado_istruzione");
		$fldtitolo_studio=get_db_value("SELECT descrizione FROM sibada_grado_istruzione WHERE idsibada_grado_istruzione='$fldidsl_grado_istruzione'");
		$fldcampo_studio=$db->f("campo_studio");
		if(!empty($fldcampo_studio))
			$fldcampo_studio="\nCampo di studio: ".$fldcampo_studio;
		$fldmese=$db->f("mese");
		$fldanno=$db->f("anno");
		$flddescrizione_titolo=$db->f("descrizione_titolo");

		$pdf->SetFont("TitilliumWeb-Regular",'',10);
		$pdf->SetTextColor(0,0,0);

		$pdf->SetX(70);

		$stringa_istruzione="- ".$fldtitolo_studio.$fldcampo_studio."\npresso ".$fldistituto_scolastico." conseguito a ".$aMESI_LETTERE[$fldmese]."/".$fldanno."\n".$flddescrizione_titolo;
		$stringa_istruzione=iconv('UTF-8', 'windows-1252',$stringa_istruzione);
		$pdf->Row_nb(array($stringa_istruzione));

		$pdf->ln();

		$res=$db->next_record();
	}
}
else
{
	$pdf->SetFont("TitilliumWeb-Regular",'',10);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetX(70);
	$pdf->Row_nb(array("Nessun titolo d'istruzione."));

	$pdf->ln();
}

$pdf->SetX(70);

$pdf->SetFont("TitilliumWeb-Bold",'',14);
$pdf->SetTextColor(35,66,149);
$pdf->SetX(70);
$pdf->MultiCell(250,10,"Disponibilita'",0,'L');

$aDISPONIBILITA=db_fill_array("SELECT idsibada_curriculum_disponibilita,idsibada_disponibilita FROM sibada_curriculum_disponibilita WHERE idsibada_curriculum='$pidsl_curriculum'");
if(!empty($aDISPONIBILITA))
{
	$pdf->SetFont("TitilliumWeb-Regular",'',10);
	$pdf->SetTextColor(0,0,0);

	$pdf->SetX(70);
	$pdf->Row_nb(array("Sono disponibile per i seguenti lavori:\n"));

	foreach($aDISPONIBILITA as $iddisp)
	{
		$flddisponibilita=get_db_value("SELECT descrizione FROM sibada_disponibilita WHERE idsibada_disponibilita='$iddisp'");
		$flddisponibilita=iconv('UTF-8', 'windows-1252',$flddisponibilita);

		$pdf->SetX(70);
		$pdf->Row_nb(array("- ".$flddisponibilita));
	}
}

$fldcapacita_professionali=get_db_value("SELECT capacita_professionali FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
if(!empty($fldcapacita_professionali))
{
	$pdf->SetFont("TitilliumWeb-Regular",'',10);
	$pdf->SetTextColor(0,0,0);

	$pdf->SetX(70);
	$pdf->Row_nb(array("\n".$fldcapacita_professionali."\n\n"));
}

$pdf->ln();

$pdf->SetFont("TitilliumWeb-Bold",'',14);
$pdf->SetTextColor(35,66,149);
$pdf->SetX(70);
$pdf->MultiCell(250,10,"Lingue",0,'L');

$fldidsl_lingue_madre=get_db_value("SELECT idsibada_lingue_madre FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
if($fldidsl_lingue_madre==8)
	$fldmadrelingua=get_db_value("SELECT madrelingua_altro FROM sibada_curriculum WHERE idsibada_curriculum='$pidsl_curriculum'");
else
	$fldmadrelingua=get_db_value("SELECT descrizione FROM sibada_lingue WHERE idsibada_lingue='$fldidsl_lingue_madre'");

$pdf->SetFont("TitilliumWeb-Regular",'',10);
$pdf->SetTextColor(0,0,0);

$fldmadrelingua=iconv('UTF-8', 'windows-1252',$fldmadrelingua);

$pdf->SetX(70);
$pdf->Row_nb(array("Madrelingua: ".$fldmadrelingua."\n"));

$nLingue=get_db_value("SELECT COUNT(*) FROM sibada_curriculum_lingue WHERE idsibada_curriculum='$pidsl_curriculum'");
if($nLingue>0)
{
	$pdf->ln();

	$pdf->SetFont("TitilliumWeb-Regular",'',10);
	$pdf->SetTextColor(0,0,0);

	$stringa_abilita=iconv('UTF-8', 'windows-1252',"Abilità con altre lingue:");
	
	$pdf->SetX(70);
	$pdf->Row_nb(array($stringa_abilita."\n"));


	$sSQL="SELECT * FROM sibada_curriculum_lingue WHERE idsibada_curriculum='$pidsl_curriculum' ORDER BY idsibada_curriculum_lingue";
	$db->query($sSQL);
	$res=$db->next_record();
	while($res)
	{
		$fldidsl_curriculum_lingue=$db->f("idsibada_curriculum_lingue");
		$fldidsl_lingue=$db->f("idsibada_lingue");
		$fldlingua=get_db_value("SELECT descrizione FROM sibada_lingue WHERE idsibada_lingue='$fldidsl_lingue'");
		$fldflag_rating_scritto=$db->f("flag_rating_scritto");
		$fldflag_rating_parlato=$db->f("flag_rating_parlato");

		switch($fldflag_rating_scritto)
		{
			case 1:
				$livello_scritto="A1";
				break;

			case 2:
				$livello_scritto="A2";
				break;

			case 3:
				$livello_scritto="B1";
				break;

			case 4:
				$livello_scritto="B2";
				break;

			case 5:
				$livello_scritto="C1";
				break;

			case 6:
				$livello_scritto="C2";
				break;
		}

		switch($fldflag_rating_parlato)
		{
			case 1:
				$livello_parlato="A1";
				break;

			case 2:
				$livello_parlato="A2";
				break;

			case 3:
				$livello_parlato="B1";
				break;

			case 4:
				$livello_parlato="B2";
				break;

			case 5:
				$livello_parlato="C1";
				break;

			case 6:
				$livello_parlato="C2";
				break;
		}

		$valutazione="livello scritto: ".$livello_scritto." - livello parlato: ".$livello_parlato;

		$pdf->SetX(70);
		$pdf->Row_nb(array("- ".$fldlingua." - ".$valutazione));

		$res=$db->next_record();
	}
}

$pdf->Output();

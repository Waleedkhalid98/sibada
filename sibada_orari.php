<?php
session_start();

include("./common.php");
include("../librerie/librerie.php");

global $db;
$pidsibada_curriculum = get_param("_k");


?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">

<head>
    <title>Sibada - Elenco utenti registrati</title>
    <?php echo get_importazioni_sibada_header(); ?>

    <STYLE TYPE="text/css">
        td {
            padding-left: 7px;
            padding-right: 7px;
        }
    </STYLE>
</head>

<body class="push-body" data-ng-app="ponmetroca">

    <?php
    $sSQL = "SELECT * FROM sibada_curriculum_orari WHERE idsibada_curriculum='$pidsibada_curriculum'";
    $db->query($sSQL);

    $res = $db->next_record();
    while ($res) {

        $fldreferenza_lm = $db->f('lunedi_mattina');
        $fldreferenza_lp = $db->f('lunedi_pomeriggio');
        $fldreferenza_ls = $db->f('lunedi_sera');

        $fldreferenza_mm = $db->f('martedi_mattina');
        $fldreferenza_mp = $db->f('martedi_pomeriggio');
        $fldreferenza_ms = $db->f('martedi_sera');

        $fldreferenza_mem = $db->f('mercoledi_mattina');
        $fldreferenza_mep = $db->f('mercoledi_pomeriggio');
        $fldreferenza_mes = $db->f('mercoledi_sera');

        $fldreferenza_gm = $db->f('giovedi_mattina');
        $fldreferenza_gp = $db->f('giovedi_pomeriggio');
        $fldreferenza_gs = $db->f('giovedi_sera');

        $fldreferenza_vm = $db->f('venerdi_mattina');
        $fldreferenza_vp = $db->f('venerdi_pomeriggio');
        $fldreferenza_vs = $db->f('venerdi_sera');

        $fldreferenza_sm = $db->f('sabato_mattina');
        $fldreferenza_sp = $db->f('sabato_pomeriggio');
        $fldreferenza_ss = $db->f('sabato_sera');

        $fldreferenza_dm = $db->f('domenica_mattina');
        $fldreferenza_dp = $db->f('domenica_pomeriggio');
        $fldreferenza_ds = $db->f('domenica_sera');
        $fldnote = $db->f('note');


        $res = $db->next_record();
    }

    ?>
    <table class="table">
        <tr>
            <th><b>Giorni</b></th>
            <th><b>Mattina</b></th>
            <th><b>Pomeriggio</b></th>
            <th><b>Notte</b></th>
        </tr>
        <tr>
            <td><b>Lunedì</b></td>
            <td>
                <?php
                if ($fldreferenza_lm == 1) {
                    echo "si";
                } else if ($fldreferenza_lm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_lp == 1) {
                    echo "si";
                } else if ($fldreferenza_lp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_ls == 1) {
                    echo "si";
                } else if ($fldreferenza_ls == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Martedì</b></td>

            <td>
                <?php
                if ($fldreferenza_mm == 1) {
                    echo "si";
                } else if ($fldreferenza_mm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_mp == 1) {
                    echo "si";
                } else if ($fldreferenza_mp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_ms == 1) {
                    echo "si";
                } else if ($fldreferenza_ms == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Mercoledì</b></td>

            <td>
                <?php
                if ($fldreferenza_mem == 1) {
                    echo "si";
                } else if ($fldreferenza_mem == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_mep == 1) {
                    echo "si";
                } else if ($fldreferenza_mep == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_mes == 1) {
                    echo "si";
                } else if ($fldreferenza_mes == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Giovedì</b></td>

            <td>
                <?php
                if ($fldreferenza_gm == 1) {
                    echo "si";
                } else if ($fldreferenza_gm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_gp == 1) {
                    echo "si";
                } else if ($fldreferenza_gp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_gs == 1) {
                    echo "si";
                } else if ($fldreferenza_gs == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Venerdì</b></td>

            <td>
                <?php
                if ($fldreferenza_vm == 1) {
                    echo "si";
                } else if ($fldreferenza_vm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_vp == 1) {
                    echo "si";
                } else if ($fldreferenza_vp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_vs == 1) {
                    echo "si";
                } else if ($fldreferenza_vs == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Sabato</b></td>

            <td>
                <?php
                if ($fldreferenza_sm == 1) {
                    echo "si";
                } else if ($fldreferenza_sm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_sp == 1) {
                    echo "si";
                } else if ($fldreferenza_sp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_ss == 1) {
                    echo "si";
                } else if ($fldreferenza_ss == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>

        <tr>
            <td><b>Domenica</b></td>

            <td>
                <?php
                if ($fldreferenza_dm == 1) {
                    echo "si";
                } else if ($fldreferenza_dm == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_dp == 1) {
                    echo "si";
                } else if ($fldreferenza_dp == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

            <td>
                <?php
                if ($fldreferenza_ds == 1) {
                    echo "si";
                } else if ($fldreferenza_ds == 2) {
                    echo "no";
                } else {
                    echo "non inserito";
                }
                ?>
            </td>

        </tr>
    </table>

    <div class="form-group mt-5 border">

        <label class="form-label active" for="note">Note</label>
        <textarea class="form-control" id="note" name="note" rows="3" readonly><?php echo $fldnote; ?></textarea>
    </div>

</body>

</html>
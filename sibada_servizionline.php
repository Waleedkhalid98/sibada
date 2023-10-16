<!doctype html>
<html lang="it">
    <head>
        <!--[if IE]><script type="text/javascript">
                (function() {
                        var baseTag = document.getElementsByTagName('base')[0];
                        baseTag.href = baseTag.href;
                })();
        </script><![endif]-->		
        <title>cimiteri.comune.messina.it - Servizi online</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="" />
        <meta name="keywords" content="" />

        <link rel="apple-touch-icon" sizes="57x57" href="static/img/apple-icon-57x57.png" />
        <link rel="apple-touch-icon" sizes="60x60" href="static/img/apple-icon-60x60.png" />
        <link rel="apple-touch-icon" sizes="72x72" href="static/img/apple-icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="76x76" href="static/img/apple-icon-76x76.png" />
        <link rel="apple-touch-icon" sizes="114x114" href="static/img/apple-icon-114x114.png" />
        <link rel="apple-touch-icon" sizes="120x120" href="static/img/apple-icon-120x120.png" />
        <link rel="apple-touch-icon" sizes="144x144" href="static/img/apple-icon-144x144.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="static/img/apple-icon-152x152.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="static/img/apple-icon-180x180.png" />
        <link rel="icon" type="image/png" href="static/img/favicon-32x32.png" sizes="32x32" />
        <link rel="icon" type="image/png" href="static/img/android-chrome-192x192.png" sizes="192x192" />
        <link rel="icon" type="image/png" href="static/img/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/png" href="static/img/favicon-16x16.png" sizes="16x16" />

        <link rel="stylesheet" href="static/css/bootstrap-italia_1.2.0.min.css" />
        <link href="static/css/messina.min.css" rel="stylesheet" type="text/css" />
        <link href="static/css/messina-print.min.css" rel="stylesheet" type="text/css" />
    </head>	
    <body class="push-body" data-ng-app="ponmetroca">
        <div class="body_wrapper push_container clearfix" id="page_top">
            <!--[if lt IE 8]>
                    <p class="browserupgrade">È necessario aggiornare il browser</p>
            <![endif]-->
            <div class="skiplink sr-only">
                <ul>
                    <li><a accesskey="2" href="cimiteri_home.php#main_container">Vai ai contenuti</a></li>
                    <li><a accesskey="3" href="cimiteri_home.php#menup">Vai al menu di navigazione</a></li>
                    <li><a accesskey="4" href="cimiteri_home.php#footer">Vai al footer</a></li>
                </ul>
            </div>		
            <header id="mainheader" class="navbar-fixed-top bg-blu container-fullwidth">
                <!-- Fascia Appartenenza -->
                <section class="preheader bg-bluscuro">
                    <div class="container">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 entesup">
                                <a aria-label="Collegamento a sito esterno - Sito della Regione Sardegna - nuova finestra" title="Regione Sicilia" href="#" target="_blank">Regione Sicilia</a>
                                <div class="float-right">
                                    <!-- siti verticali -->
                                    <!-- siti verticali -->
                                    <!-- accedi -->
                                    <div class="accedi float-left text-right">                                  
                                            <a class="btn btn-default btn-accedi" href="cimiteri_login.php">
                                                <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_circle"></use></svg>
                                                <span>Accedi all&#8217;area personale</span>
                                            </a>
                                    </div>
                                    <!-- accedi -->
                                </div>
                            </div>
                        </div>
                    </div>               
                </section>
                <!-- Fascia Appartenenza -->    
                <!-- Button Menu -->
                <button class="navbar-toggle menu-btn pull-left menu-left push-body jPushMenuBtn">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar icon-bar1"></span>
                    <span class="icon-bar icon-bar2"></span>
                    <span class="icon-bar icon-bar3"></span>
                </button>
                <!--End Button Menu -->
 
                <!-- Menu -->
                <nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left" id="menup">
                    <div class="cbp-menu-wrapper clearfix">
                        <div class="logo-burger">
                            <div class="logoimg-burger">
                                <a href="index.html" title="Comune di Messina - Sito istituzionale"> 
                                    <img src="static/img/logo_messina_print.svg" alt="Logo del Comune di Messina"/>
                                </a>
                            </div>
                            <div class="logotxt-burger">
                                <a href="cimiteri_home.php" title="Comune di Messina - Sito istituzionale">Cimiteri del Comune di Messina</a>
                            </div>
                        </div>
                        <!-- accedi -->
                        <div class="accedi float-left text-right">                                  
                            <a class="btn-accedi" href="#">
                                <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_circle"></use></svg>
                                <span>Accedi all&#8217;area personale</span>
                            </a>
                        </div>
                        <!-- accedi -->
                    </div>
                </nav>
                <!-- End Menu -->
                <!-- Intestazione -->
                <div class="container header" data-ng-controller="ctrlRicerca as ctrl">
                    <div class="row clearfix">
                        <div class="col-xl-7 col-lg-7 col-md-8 col-sm-12 comune">
                            <div class="logoimg">
                                <a href="cimiteri_home.php" title="Comune di Messina - Sito istituzionale"> 
                                    <img src="../cimiteri/foto/logomessina.png" alt="Logo del Comune di Messina"/>
                                </a>
                            </div>
                            <div class="logotxt">
                                <h1>
                                    <a href="cimiteri_home.php" title="Comune di Messina - Sito istituzionale">Cimiteri del Comune di Messina</a>
                                </h1>
                            </div>
                            <!-- pulsante ricerca mobile -->
                            <div class="p_cercaMobile clearfix">
                                <button aria-label="Cerca" class="btn btn-default btn-cerca pull-right" data-target="#searchModal" data-toggle="modal" type="button">
                                    <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-search"></use></svg>
                                </button>
                            </div>
                            <!-- pulsante ricerca mobile -->
                        </div>
                        <div class="col-xl-3 col-lg-3 d-none d-lg-block d-md-none pull-right text-right">
                            <!-- social-->

                            <!-- social-->
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-4 d-none d-md-block d-md-none text-right">
                            <!-- ricerca -->
                            <!-- div class="cerca float-right">
                                <span>Cerca</span>
                                <button aria-label="Cerca" class="btn btn-default btn-cerca pull-right" type="button" data-toggle="modal" data-target="#searchModal">
                                    <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-search"></use></svg>
                                </button>
                            </div -->
                            <!-- ricerca -->
                        </div>
                    </div>
                </div>
                <!-- Intestazione -->
                <section class="hidden-xs" id="sub_nav">
                    <div class="container">
                        <div class="row">
                                <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="cimiteri_home.php" role="button" id="dropdownMenuLink1" aria-haspopup="true" aria-expanded="false">
                                    Home
                                </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="dropdown">
                                <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Cimiteri
                                </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <div class="link-list-wrapper">
                                            <ul class="link-list">
                                                <li><a class="list-item" href="cimiteri_storia.php"><span>Storia</span></a></li>
                                                <li><a class="list-item" href="cimiteri_impreselocali.php"><span>Imprese locali di OO.FF.</span></a></li>
                                                <li><a class="list-item" href="cimiteri_avvisi.php"><span>Avvisi</span></a></li>
                                                <li><a class="list-item" href="cimiteri_mappe.php"><span>Mappe</span></a></li>
                                                <li><a class="list-item" href="cimiteri_concessionimanufatti.php"><span>Concessioni manufatti</span></a></li>
                                                <li><a class="list-item" href="cimiteri_camposanto.php"><span>Gran Camposanto</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLink3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Servizi online
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink3">
                                            <div class="link-list-wrapper">
                                                <ul class="link-list">
                                                    <li><a class="list-item" href="cimiteri_servizionline.php"><span>Servizi cimiteriali</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_cartaservizi.php"><span>Carta dei servizi</span></a></li>
                                                    
                                                </ul>
                                            </div>
                                        </div>
                                </div>
                                <div class="dropdown">
                                    <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="modulistica.php" role="button" id="dropdownMenuLink4" aria-haspopup="true" aria-expanded="false">
                                        Modulistica
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>  
                                    <div class="dropdown">
                                    <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLink5" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Polizia mortuaria
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink5">
                                                <div class="link-list-wrapper">
                                                    <ul class="link-list">
                                                        <li><a class="list-item" href="cimiteri_poliziamortuaria.php"><span>Polizia Mortuaria</span></a></li>
                                                        <li><a class="list-item" href="cimiteri_poliziamortuaria_tariffe.php"><span>Tariffe</span></a></li>
                                                        <li><a class="list-item" href="cimiteri_lampvot_sperimentale.php"><span>Lampade Votive</span></a></li>
                                                    </ul>
                                                </div>
                                        </div>
                                </div>
                                <div class="dropdown">
                                    <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLink6" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Cremazioni
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink6">
                                            <div class="link-list-wrapper">
                                                <ul class="link-list">
                                                    <li><a class="list-item" href="cimiteri_cremazioni.php"><span>Cremazioni</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_cremazioni_info.php"><span>Info ex residenti</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_cremazioni_prenotazioni.php"><span>Prenotazioni</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_cremazioni_tariffe.php"><span>Tariffe</span></a></li>
                                                </ul>
                                            </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a style="color:rgb(255, 255, 255);" class="btn btn-dropdown dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Interventi edili/marmorei
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                            <div class="link-list-wrapper">
                                                <ul class="link-list">
                                                    <li><a class="list-item" href="cimiteri_areainterventi.php"><span>Area interventi</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_concessionimanufatti.php"><span>Concessioni manufatti</span></a></li>
                                                    <li><a class="list-item" href="cimiteri_trasformazionitumuli.php"><span>Trasformazioni tumuli</span></a></li>
                                                </ul>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </header>
            <main id="main_container">
                <section id="briciole">
                    <div class="container">
                        <div class="row">
                            <div class="offset-lg-1 col-lg-10 col-md-12">
                                <nav class="breadcrumb-container" aria-label="breadcrumb">
                                    <ol class="breadcrumb">										
                                        <li class="breadcrumb-item"><a href="cimiteri_home.php" class="">Home</a><span class="separator">/</span></li>
                                        <li class="breadcrumb-item active" aria-current="page">Servizi cimiteriali online</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </section>
				<section id="sezioni-eventi">
                   <div class="container">
                            <div class="row row-eq-height mt-128n">
                                <div class="col-md-20">
                                    <article class="scheda scheda-round">
                                        <div class="scheda-testo">
                                            <h4>Servizi Cimiteriali Online</h4><br>
                                            <p>Grazie all'attuale Gestore del Sistema Informatico in dotazione al Dipartimento Servizi Cimiteri è stato possibile ridurre al massimo sia l’ingresso dell’utenza presso i Front-Office della Direzione Cimiteri siti in via Catania 118 sia con la sola possibile prenotazione ONLINE appuntamento (https://wp.me/p530dD-g1) sia attivando tutta la modulistica necessaria e procedura ONLINE in modo da velocizzare e semplificano le comunicazioni con l’utenza in ottemperanza alle disposizioni del Decreto Legislativo 7 marzo 2005, n. 82 “Codice dell'amministrazione digitale”.<br>
                                            Sono pertanto stati implementati ed aggiornati nel sito istituzionale del dipartimento www.cimiteri.it i seguenti moduli da compilare e seguire i passaggi richiesti:<br><br></p>

                                            <p><a><h5> >PRENOTAZIONE APPUNTAMENTO PER I SEGUENTI SERVIZI di P.M.</h5></a><br>
                                            <b>->   FRONT-OFFICE A</b>
                                            <i>informazioni / iter procedurali di polizia mortuaria - processi verbali.</i><br>
                                            <b>->   FRONT-OFFICE B</b>
                                            <i>aut. ingresso salme / decreti per partenze salme/bollettazioni lavori di P.M. (tumulazioni, trasformazioni tumuli, ecc.)</i><br>
                                            <b>->   FRONT-OFFICE C</b>
                                            <i>CREMAZIONI - relative emissione bollette e pagamento cremazione</i><br><br></p>

                                            <p><a href="cimiteri_login.php"><h5> >COMUNICAZIONE INGRESSO FERETRO Deposito Cimitero</h5></a><br>
                                            <a href="cimiteri_login.php"><h6> >Modulistica per richiesta Concessione e/o tumulazione</h6></a>
                                            <a href="cimiteri_login.php"><h6> >Modulistica comunicazione lavori</h6></a>
                                            <a href="cimiteri_login.php"><h6> >ISTANZE ON LINE PER RICHIEDERE CREMAZIONE</h6></a>
                                            Area dedicata alla compilazione di domande per richiedere trasferimenti, ricongiungimenti, nuove sepolture, ecc..<br><br></p>

                                            <p><a href="cimiteri_cremazioni_info.php"><h5> >INFO PER EFFETTUARE UNA CREMAZIONE ex residente deceduto nel comune di Messina</h5> (procedure, autoriz. cremazione, affidamento, dispersione, ecc.)</a><br>
                                            <a href="cimiteri_cremazioni_prenotazioni.php"><h6> >COME PRENOTARE UNA CREMAZIONE DA FUORI MESSINA (Informazioni, procedure, modulistica)</h6></a>
                                            <a href="cimiteri_login.php"><h6> >MODULO PER RICHIEDERE CREMAZIONE</h6></a>
                                            Per permettere di rendere gli atti amministrativi per la cremazione più semplici possibili, in particolare per chi proviene da fuori comune.<br><br></p>
                                        </div>
                                    </article>
                                </div>
                                <br>
                            </div>                    
                   </div>
                </section>	
                <main id="main_container">    
            </main>		
            </main>
           <footer id="footer">
                <div class="container">
                    <section>
                        <div class="row clearfix">
                            <div class="col-sm-12 intestazione">
                                <div class="logoimg">
                                    <img src="../cimiteri/foto/logomessina.png" alt="Logo del Comune di Messina"/>
                                </div>
                                <div class="logotxt">
                                    <h3>
                                        <a href="cimiteri_home.php" title="Vai alla pagina: Comune di Messina - Sito istituzionale">Comune di Messina</a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <section class="lista-linkutili">
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <h4><a href="cimiteri_home.php" title="Vai alla pagina: Amministrazione Trasparente">Amministrazione Trasparente</a></h4>
                                <p>
                                    I dati personali pubblicati sono riutilizzabili solo ai sensi dell'articolo 7 del decreto legislativo 33/2013
                                </p>
                            </div>
                            <div class="col-lg-5 col-md-3 col-sm-6">
                                <h4><a href="#" title="Vai alla pagina: Contatti">Contatti</a></h4>
                                <p>
                                    <strong>Dipartimento Ambiente - Servizio Cimiteri</strong><br>
                                    Via Catania, n.118 - 98124 Messina<br>
                                    tel: 0902923548 (centralino)<br>
                                    segnalazioni: compilare il form su pagina "segnalazioni"<br>
                                </p>
                            </div>
                            
                        </div>
                    </section>
                    <section class="postFooter clearfix">
                        <h3 class="sr-only">Sezione Link Utili</h3>
                            <a href="#" title="Privacy-Cookies">Privacy-Cookies</a> | 
                            <a href="#" title="Note Legali">Note Legali</a> | 
                            <a href="#" title="Contatti">Contatti</a> | 
                            <a href="#" title="Accessibilità">Accessibilità</a> | 
                            <a href="#" title="Mappa del sito">Mappa del sito</a> | 
                            <a href="#" title="La Redazione">La Redazione</a>
                    </section>
                </div>
            </footer>
        </div>
        <div id="topcontrol" class="topcontrol bg-bluscuro" title="Torna su">
            <svg class="icon"><use xlink:href="static/img/bootstrap-italia.svg#it-collapse"></use></svg>
        </div>
        <link href="static/css/jquery-ui.css" rel="stylesheet" type="text/css" />
        <link href="static/css/owl.carousel.min.css" rel="stylesheet" type="text/css" />
        <link href="static/css/owl.theme.default.min.css" rel="stylesheet" type="text/css" />
        <link href="static/css/sezioni.min.css" rel="stylesheet" type="text/css" />
        <link href="static/css/angular-material.min.css" rel="stylesheet" type="text/css" />
        <script src="static/js/jquery-3.3.1.min.js"></script>
        <script src="static/js/popper.min.js"></script>
        <script>window.__PUBLIC_PATH__ = 'static/font'</script>
        <script src="static/js/bootstrap-italia_1.2.0.min.js"></script>
        <script src="static/js/messina.min.js"></script>
        <script src="static/js/jquery-ui.js"></script>
        <script src="static/js/i18n/datepicker-it.js"></script>
        <script src="static/js/angular.min.js"></script>
        <script src="static/js/angular-animate.min.js"></script>
        <script src="static/js/angular-aria.min.js"></script>
        <script src="static/js/angular-messages.min.js"></script>
        <script src="static/js/angular-sanitize.min.js"></script>
        <script src="static/js/app.js"></script>
        <script src="static/js/ricerca.js"></script>
        <script src="static/js/ricerca-service.js"></script>
        <script src="static/js/general-service.js"></script>
        <script src="static/js/angular-material.min.js"></script>
        <script src="static/js/filtri-controller.js"></script>
        <script src="static/js/general-service.js"></script>
        <script src="static/js/filtri-service.js"></script>
        <script src="static/js/owl.carousel.min.js"></script>

        <!-- COOKIE BAR -->
        <div class="cookiebar hide bg-bluscuro" aria-hidden="true">
            <p class="text-white">
                Questo sito utilizza cookie tecnici, analytics e di terze parti. Proseguendo nella navigazione accetti l’utilizzo dei cookie.<br />
                <button data-accept="cookiebar" class="btn btn-info mr-2 btn-verde">Accetto</button>
                <a href="#" class="btn btn-outline-info btn-trasp">Privacy policy</a>
            </p>
        </div>


        <div id="dvRicerca">
            <div data-ng-cloak data-ng-controller="ctrlRicerca as ctrl">
                <!-- CERCA -->
                <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalTitle" aria-hidden="false">
                    <script>
                                $(function () {
                                    $("#datepicker_start").datepicker();
                                    $("#datepicker_end").datepicker();
                                });
                    </script>
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form id="ricerca" action="ricerca.html" method="post">
                                <div class="modal-header-fullsrc">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-sm-1" data-ng-class="{pb12: !ctrl.General.getFiltriMode().filtri}">
                                                <button data-ng-hide="ctrl.General.getFiltriMode().filtri && !ctrl.General.getFiltriMode().internal" type="button" class="close" data-dismiss="modal" aria-label="Chiudi filtri di ricerca" data-ng-click="ctrl.General.setFiltriMode(false, false)">
                                                    <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_back"></use></svg>
                                                </button>
                                                <button data-ng-show="ctrl.General.getFiltriMode().filtri && !ctrl.General.getFiltriMode().internal" type="button" class="close" aria-label="Chiudi filtri di ricerca" data-ng-click="ctrl.General.setFiltriMode(false, false)">
                                                    <svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-arrow_back"></use></svg>
                                                </button>
                                            </div>
                                            <div class="col-sm-11">
                                                <h1 class="modal-title" id="searchModalTitle">
                                                    <span data-ng-hide="ctrl.General.getFiltriMode().filtri">Cerca</span>
                                                    <span data-ng-show="ctrl.General.getFiltriMode().filtri">Filtri</span>
                                                </h1>
                                                <button data-ng-show="ctrl.General.getFiltriMode().filtri" class="confirm btn btn-default btn-trasparente float-right">Conferma</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-filter-type ng-hide" data-ng-show="ctrl.General.getFiltriMode().filtri">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation">
                                                <a data-ng-if="ctrl.General.getFiltriMode().categoria == ''" href="" aria-controls="categoriaTab" role="tab" data-toggle="tab" data-ng-click="ctrl.Ricerca.setActive('categorie')" data-ng-class="ctrl.Ricerca.get_categoriaTab()">Categorie</a>
                                                <a data-ng-if="ctrl.General.getFiltriMode().categoria != ''" href="" aria-controls="categoriaTab" role="tab" data-toggle="tab" data-ng-click="ctrl.Ricerca.setActive('categorie')" data-ng-class="ctrl.Ricerca.get_categoriaTab()">{{ctrl.General.getFiltriMode().categoria}}</a>
                                            </li>
                                            <li role="presentation"><a href="" aria-controls="argomentoTab" role="tab" data-toggle="tab"
                                                                       data-ng-click="ctrl.Ricerca.setActive('argomenti')" data-ng-class="ctrl.Ricerca.get_argomentoTab()">Argomenti</a></li>
                                            <li role="presentation"><a href="" aria-controls="opzioniTab" role="tab" data-toggle="tab"
                                                                       data-ng-click="ctrl.Ricerca.setActive('opzioni')" data-ng-class="ctrl.Ricerca.get_opzioniTab()">Opzioni</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="modal-body-search">
                                    <div class="container">
                                        <div class="row" data-ng-hide="ctrl.General.getFiltriMode().filtri">
                                            <div class="col-lg-12 col-md-12 col-sm-12">
                                                <div class="form-group">
                                                    <label class="sr-only active">Cerca</label>
                                                    <md-autocomplete  
                                                        type="text"
                                                        md-input-name ="cercatxt"
                                                        md-input-id ="cerca-txt"
                                                        placeholder="Cerca informazioni, persone, servizi"
                                                        md-selected-item="ctrl.Ricerca.cercatxt"
                                                        md-search-text="searchStringRicercaTxt"
                                                        md-selected-item-change="ctrl.Ricerca.selectedRicercaTxtItemChanged(item)"
                                                        md-items="item in ctrl.Ricerca.getRisultatiRicerca(searchStringRicercaTxt)"
                                                        md-item-text="item.contentName"
                                                        md-min-length="1"
                                                        md-select-on-match="true"
                                                        md-clear-button="true"
                                                        md-dropdown-items="10">
                                                        <md-item-template>
                                                            <div data-ng-if="item.contentName != ''">
                                                                <svg data-ng-show="item.category == 'Pagamenti'" class="icon icon-sm"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg>
                                                                <svg data-ng-show="item.category == 'Modulistica'" class="icon icon-sm"><use xlink:href="static/img/ponmetroca.svg#ca-description"></use></svg>
                                                                <svg data-ng-show="item.category == 'Concorsi'" class="icon icon-sm"><use xlink:href="static/img/ponmetroca.svg#ca-account_balance"></use></svg>
                                                                <span class="autocomplete-list-text">
                                                                    <span data-md-highlight-text="searchStringRicercaTxt" data-md-highlight-flags="i">{{item.contentName}}</span>
                                                                    <em>{{item.category}}</em>
                                                                </span>
                                                            </div>
                                                        </md-item-template>
                                                        <md-not-found>
                                                            Nessun elemento trovato
                                                        </md-not-found>
                                                    </md-autocomplete>
                                                    <svg class="icon ico-prefix"><use xlink:href="static/img/bootstrap-italia.svg#it-search"></use></svg>
                                                </div>
                                                <div class="form-filtro" data-ng-show="searchStringRicercaTxt == '' || ctrl.Ricerca.cercatxt != undefined">
                                                    <a class="btn btn-default btn-trasparente" data-ng-class="ctrl.Ricerca.class_tutti('categorie')" data-ng-click="ctrl.Ricerca.toggleAll('categorie')">Tutto</a>
                                                    <a class="btn btn-default btn-trasparente" data-ng-class="ctrl.Ricerca.class_categorie(ctrl.Ricerca.get_ammins_selected(), ctrl.Ricerca.get_ammins_chk())" data-ng-click="ctrl.Ricerca.ammins_toggleAll()"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_balance"></use></svg> Amministrazione</a>
                                                    <a class="btn btn-default btn-trasparente" data-ng-class="ctrl.Ricerca.class_categorie(ctrl.Ricerca.get_servizi_selected(), ctrl.Ricerca.get_servizi_chk())" data-ng-click="ctrl.Ricerca.servizi_toggleAll()"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Servizi</a>
                                                    <a class="btn btn-default btn-trasparente" data-ng-class="ctrl.Ricerca.class_categorie(ctrl.Ricerca.get_novita_selected(), ctrl.Ricerca.get_novita_chk())" data-ng-click="ctrl.Ricerca.novita_toggleAll()"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-event"></use></svg> Novit&agrave;</a>
                                                    <a class="btn btn-default btn-trasparente" data-ng-class="ctrl.Ricerca.class_categorie(ctrl.Ricerca.get_docs_selected(), ctrl.Ricerca.get_docs_chk())" data-ng-click="ctrl.Ricerca.docs_toggleAll()"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-description"></use></svg> Documenti</a>
                                                    <a class="btn btn-default btn-trasparente" data-ng-click="ctrl.Ricerca.setActive(); ctrl.General.setFiltriMode(true, false)" aria-label="Filtra per categorie">...</a>
                                                </div>
                                                <div class="form-blockopz" data-ng-show="searchStringRicercaTxt == '' || ctrl.Ricerca.cercatxt != undefined">
                                                    <div data-ng-if="getCookie('search') == ''">
                                                        <h2>Suggerimenti</h2>
                                                        <ul>
                                                            <li><a href="#" title="Vai alla pagina: Uffici comunali"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_balance"></use></svg> Uffici comunali</a></li>
                                                            <li><a href="#" title="Vai alla pagina: Servizi demografici"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Servizi demografici</a></li>
                                                            <li><a href="#" title="Vai alla pagina: SUAP"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_balance"></use></svg> SUAP</a></li>
                                                            <li><a href="#" title="Vai alla pagina: Sostegno alle famiglie"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Sostegno alle famiglie</a></li>
                                                            <li><a href="#" title="Vai alla pagina: Segnalazioni"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Segnalazioni</a></li>
                                                        </ul>
                                                    </div>
                                                    <div data-ng-if="getCookie('search')">
                                                        <h2>Ricerche frequenti</h2>
                                                        <ul>
                                                            <li><a href="#" title="Vai alla pagina: SUAPE"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> SUAPE </a></li>
                                                            <li><a href="#" title="Vai alla pagina: Pagamento tassa di cicolazione"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Pagamento tassa di cicolazione </a></li>
                                                            <li><a href="#" title="Vai alla pagina: Modulo iscrizione Asilo nido"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-description"></use></svg> Modulo iscrizione Asilo nido </a></li>
                                                            <li><a href="#" title="Vai alla pagina: Municipi di Messina"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-account_balance"></use></svg> Municipi di Messina </a></li>
                                                            <li><a href="#" title="Vai alla pagina: Pagamento Imposte comunali"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-settings"></use></svg> Albo pretorio </a></li>
                                                            <li><a href="#" title="Vai alla pagina: Albo pretorio"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-description"></use></svg> Albo pretorio </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <button class="search-start" data-ng-show="ctrl.Ricerca.cercatxt != undefined && ctrl.Ricerca.cercatxt != ''">
                                                    <svg class="icon"><use xlink:href="static/img/bootstrap-italia.svg#it-search"></use></svg>Cerca in tutto il sito</button>
                                                <input type="hidden" id="amministrazione" name="amministrazione" value="{{ctrl.Ricerca.get_ammins_selected()}}">
                                                <input type="hidden" id="servizi" name="servizi" value="{{ctrl.Ricerca.get_servizi_selected()}}">
                                                <input type="hidden" id="novita" name="novita" value="{{ctrl.Ricerca.get_novita_selected()}}">
                                                <input type="hidden" id="documenti" name="documenti" value="{{ctrl.Ricerca.get_docs_selected()}}">
                                                <input type="hidden" id="argomenti" name="argomenti" value="{{ctrl.Ricerca.get_args_selected()}}">
                                                <input type="hidden" id="attivi" name="attivi" value="{{ctrl.Ricerca.active_chk}}">
                                                <input type="hidden" id="inizio" name="inizio" value="{{ctrl.Ricerca.data_inizio}}">
                                                <input type="hidden" id="fine" name="fine" value="{{ctrl.Ricerca.data_fine}}">
                                            </div>
                                        </div>

                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Argomenti')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Istruzione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Gestione dei rifiuti')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Integrazione sociale')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Cultura')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Sport')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setArgomenti('Turismo')"></data-ng-container>

                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Organi di governo', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Aree amministrative', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Uffici', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Enti e fondazioni', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Politici', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Personale amministrativo', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Luoghi', 'Amministrazione')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Anagrafe e stato civile', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Cultura e tempo libero', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Vita lavorativa', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Attività produttive e commercio', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Appalti pubblici', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Catasto e urbanistica', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Turismo', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Mobilità e trasporti', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Educazione e formazione', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Giustizia e sicurezza pubblica', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Tributi e finanze', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Ambiente', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Salute, benessere e assistenza', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Autorizzazioni', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Agricoltura', 'Servizi')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Notizie', 'Novità')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Comunicati stampa', 'Novità')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Eventi', 'Novità')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Documenti Albo pretorio', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Modulistica', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Documenti funzionamento interno', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Normative', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Accordi tra enti', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Documenti attività politica', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Rapporti tecnici', 'Documenti e dati')"></data-ng-container>
                                        <data-ng-container data-ng-init="ctrl.Ricerca.setCategorie('Dataset', 'Documenti e dati')"></data-ng-container>

                                        <div role="tabpanel" data-ng-show="ctrl.General.getFiltriMode().filtri" class="ng-hide" data-ng-init="showallcat = false; showallarg = false">
                                            <div class="tab-content">
                                                <div role="tabpanel" data-ng-class="ctrl.Ricerca.get_categoriaTab() == 'active' ? 'tab-pane active' : 'tab-pane'" id="categoriaTab">
                                                    <div class="row">
                                                        <div class="offset-md-1 col-md-10 col-sm-12">
                                                            <div class="search-filter-ckgroup">
                                                                <md-checkbox aria-label="Amministrazione"
                                                                             data-ng-if="ctrl.General.getFiltriMode().categoria == ''"
                                                                             data-ng-checked="ctrl.Ricerca.ammins_isChecked()"
                                                                             md-indeterminate="ctrl.Ricerca.ammins_isIndeterminate()"
                                                                             data-ng-click="ctrl.Ricerca.ammins_toggleAll()"><label class="search-filter-ckgroup-title">Amministrazione</label>
                                                                </md-checkbox>
                                                                <div class="flex-100" data-ng-repeat="item in ctrl.Ricerca.get_ammins_chk()"
                                                                     data-ng-if="ctrl.General.getFiltriMode().categoria == 'Amministrazione' || ctrl.General.getFiltriMode().categoria == ''">
                                                                    <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_ammins_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_ammins_selected())" aria-label="{{item}}">
                                                                        <label data-ng-bind-html="item"></label>
                                                                    </md-checkbox>
                                                                </div>
                                                            </div>
                                                            <div class="search-filter-ckgroup">
                                                                <md-checkbox aria-label="Servizi"
                                                                             data-ng-if="ctrl.General.getFiltriMode().categoria == ''"
                                                                             data-ng-checked="ctrl.Ricerca.servizi_isChecked()"
                                                                             md-indeterminate="ctrl.Ricerca.servizi_isIndeterminate()"
                                                                             data-ng-click="ctrl.Ricerca.servizi_toggleAll()"><label class="search-filter-ckgroup-title">Servizi</label>
                                                                </md-checkbox>
                                                                <div class="flex-100" data-ng-repeat="item in ctrl.Ricerca.get_servizi_chk()"
                                                                     data-ng-if="ctrl.General.getFiltriMode().categoria == 'Servizi' || ctrl.General.getFiltriMode().categoria == ''">
                                                                    <div data-ng-if = "$index <= 2">
                                                                        <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_servizi_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_servizi_selected())" aria-label="{{item}}">
                                                                            <label data-ng-bind-html="item"></label>
                                                                        </md-checkbox>
                                                                    </div>
                                                                    <div data-ng-if = "$index >= 3">									
                                                                        <div data-ng-show="showallcat || ctrl.General.getFiltriMode().categoria != ''">
                                                                            <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_servizi_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_servizi_selected())" aria-label="{{item}}">
                                                                                <label data-ng-bind-html="item"></label>
                                                                            </md-checkbox>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <a href="" data-ng-hide="showallcat || ctrl.General.getFiltriMode().categoria != ''" data-ng-click="showallcat = true"><b>Mostra tutto</b></a>
                                                            </div>
                                                            <div data-ng-show="showallcat || ctrl.General.getFiltriMode().categoria != ''">
                                                                <div class="search-filter-ckgroup">
                                                                    <md-checkbox aria-label="Novità"
                                                                                 data-ng-if="ctrl.General.getFiltriMode().categoria == ''"
                                                                                 data-ng-checked="ctrl.Ricerca.novita_isChecked()"
                                                                                 md-indeterminate="ctrl.Ricerca.novita_isIndeterminate()"
                                                                                 data-ng-click="ctrl.Ricerca.novita_toggleAll()"><label class="search-filter-ckgroup-title">Novità</label>
                                                                    </md-checkbox>
                                                                    <div class="flex-100" data-ng-repeat="item in ctrl.Ricerca.get_novita_chk()"
                                                                         data-ng-if="ctrl.General.getFiltriMode().categoria == 'Novità' || ctrl.General.getFiltriMode().categoria == ''">
                                                                        <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_novita_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_novita_selected())" aria-label="{{item}}">
                                                                            <label data-ng-bind-html="item"></label>
                                                                        </md-checkbox>
                                                                    </div>
                                                                </div>
                                                                <div class="search-filter-ckgroup">
                                                                    <md-checkbox aria-label="Documenti"
                                                                                 data-ng-if="ctrl.General.getFiltriMode().categoria == ''"
                                                                                 data-ng-checked="ctrl.Ricerca.docs_isChecked()"
                                                                                 md-indeterminate="ctrl.Ricerca.docs_isIndeterminate()"
                                                                                 data-ng-click="ctrl.Ricerca.docs_toggleAll()"><label class="search-filter-ckgroup-title">Documenti</label>
                                                                    </md-checkbox>
                                                                    <div class="flex-100" data-ng-repeat="item in ctrl.Ricerca.get_docs_chk()"
                                                                         data-ng-if="ctrl.General.getFiltriMode().categoria == 'Documenti' || ctrl.General.getFiltriMode().categoria == ''">
                                                                        <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_docs_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_docs_selected())" aria-label="{{item}}">
                                                                            <label data-ng-bind-html="item"></label>
                                                                        </md-checkbox>
                                                                    </div>
                                                                    <a href="" data-ng-hide="ctrl.General.getFiltriMode().categoria != ''" data-ng-click="showallcat = false"><b>Mostra meno</b></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div role="tabpanel" data-ng-class="ctrl.Ricerca.get_argomentoTab() == 'active' ? 'tab-pane active' : 'tab-pane'" id="argomentoTab">
                                                    <div class="row">
                                                        <div class="offset-md-1 col-md-10 col-sm-12">
                                                            <div class="search-filter-ckgroup">
                                                                <div class="flex-100" data-ng-repeat="item in ctrl.Ricerca.get_argomenti()">
                                                                    <div data-ng-if = "$index <= 11">
                                                                        <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_args_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_args_selected())" aria-label="{{item}}">
                                                                            <label data-ng-bind-html="item"></label></md-checkbox>
                                                                    </div>
                                                                    <div data-ng-if = "$index >= 12">									
                                                                        <div data-ng-show="showallarg">
                                                                            <md-checkbox data-ng-checked="ctrl.Ricerca.exists(item, ctrl.Ricerca.get_args_selected())" data-ng-click="ctrl.Ricerca.toggle(item, ctrl.Ricerca.get_args_selected())" aria-label="{{item}}">
                                                                                <label data-ng-bind-html="item"></label></md-checkbox>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <a href="" data-ng-hide="showallarg" data-ng-click="showallarg = true"><b>Mostra tutto</b></a>
                                                                <a href="" data-ng-show="showallarg" data-ng-click="showallarg = false"><b>Mostra meno</b></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div role="tabpanel" data-ng-class="ctrl.Ricerca.get_opzioniTab() == 'active' ? 'tab-pane active' : 'tab-pane'" id="opzioniTab">
                                                    <div class="row">
                                                        <div class="offset-lg-1 col-lg-4 col-md-6 col-sm-12">
                                                            <div class="form-check form-check-group">
                                                                <div class="toggles">
                                                                    <label for="active_chk">
                                                                        Cerca solo tra i contenuti attivi
                                                                        <input type="checkbox" id="active_chk" data-ng-model="ctrl.Ricerca.active_chk">
                                                                        <span class="lever"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <p class="small">Verranno esclusi dalla ricerca i contenuti archiviati e non più validi come gli eventi terminati o i bandi scaduti.</p>
                                                        </div>
                                                        <div class="col-md-3 col-sm-12 search-filter-dategroup">
                                                            <div class="form-group">
                                                                <label for="datepicker_start">Data inizio</label>
                                                                <input type="text" class="form-control" id="datepicker_start" data-ng-model="ctrl.Ricerca.data_inizio" placeholder="gg/mm/aaaa" />
                                                                <button aria-label="Apri calendario" type="button" class="ico-sufix" onclick="$('#datepicker_start').focus();" onkeypress="$('#datepicker_start').focus();"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-event"></use></svg></button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-sm-12 search-filter-dategroup">
                                                            <div class="form-group">
                                                                <label for="datepicker_end">Data fine</label>
                                                                <input type="text" class="form-control" id="datepicker_end" data-ng-model="ctrl.Ricerca.data_fine" placeholder="gg/mm/aaaa" />
                                                                <button aria-label="Apri calendario" type="button" class="ico-sufix" onclick="$('#datepicker_end').focus();" onkeypress="$('#datepicker_end').focus();"><svg class="icon"><use xlink:href="static/img/ponmetroca.svg#ca-event"></use></svg></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>	
    </body>
</html>
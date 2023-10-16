<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Area riservata</title>
        <?php echo get_importazioni_sibada_header(); ?>
        
        <!--style>
        .footer {
          position: fixed;
          left: 0;
          bottom: 0;
          width: 100%;
        }
        </style-->
        
    </head>	
    <body class="push-body" data-ng-app="ponmetroca">
        <div class="body_wrapper push_container clearfix" id="page_top">
			
            <?php echo get_header_sibada_out(); ?>

            <main id="main_container">
                <section id="briciole">
                    <div class="container">
                        <div class="row">
                            <div class="offset-lg-1 col-lg-10 col-md-12">
                                <nav class="breadcrumb-container" aria-label="breadcrumb">
                                    <ol class="breadcrumb">										
                                        <li class="breadcrumb-item"><a href="sibada_login.php" title="Vai alla pagina Home" class="">Home</a><span class="separator">/</span></li>
                                        <li class="breadcrumb-item active" aria-current="page"><a>Registrazione</a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </section>
				<section id="sezioni-eventi">
                    <main id="main-content" class="container px-4 my-4">
                        <div class="row">
                            <div class="col-md-8 offset-md-2 col-lg-4 offset-lg-4">
                                <h1 class="text-center title">Registrati</h1><br>
                                <form style="max-width: 400px;margin: 0 auto;" class="validate-form" method="post" action="/user/login" name="loginform">
                                    <input type="hidden" name="hidden" value="" />
                                    <div class="form-group">
                                        <div class="controls">
                                            <label for="exampleInputPassword"><a href="sibada_registrazione.php">Sei un cittadino? Clicca qui!</a></label>
                                            <input type="text" id="user" autocomplete="off" name="user" class="form-control" data-rule-required="true" maxlength="30" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="controls">
                                            <label for="exampleInputPassword"><a href="sibada_registrazione_ditte.php">Sei una ditta? Clicca qui!</a></label>
                                            <input type="text" id="user" autocomplete="off" name="user" class="form-control" data-rule-required="true" maxlength="30" value="">
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
<script>
</script>
</html>

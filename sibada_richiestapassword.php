<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">
    <head>
        <title>Recupero credenziali</title>
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
                                        <li class="breadcrumb-item active" aria-current="page"><a>Password dimenticata</a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </section>
				<section id="sezioni-eventi">
                    <main id="main-content" class="container px-4 my-4">
						<br><br>
                        <div class="row mb-5">
                            <div class="col-md-6 offset-md-3">
                                <form method="post" name="forgotpassword">
                                    <input type="hidden" name="pas" value="96809c49546d0d079dea02150343d277c59508a1" />
                                        <h1>Hai dimenticato la password?</h1>
                                            <div class="alert alert-info">
                                                <p>Se hai dimenticato la tua password possiamo generartene un'altra. Devi solo inserire il tuo indirizzo email e noi ti creeremo una nuova password.</p>
                                            </div>
                                        <div class="input-group mb-3">
                                            <input class="form-control" placeholder="Email" id="email" type="text" name="email" size="40" value="" />
                                            <div class="input-group-append">
                                                <input class="btn btn-primary" type="submit" onclick="recuperaPassword()" value="Genera una nuova password" />
                                            </div>
                                        </div>

                                        <div id="div_result" style="display:none;">
                                            <b>E' stata inviata un'email con le nuove credenziali</b>.
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
function recuperaPassword()
{ 
  var params_recupero="mail";
  var value=$("#email").val();

  $("#div_result").hide();
  $("#div_result").removeClass("has-error");
  $("#div_result").removeClass("has-success");

  var page="./esibada_action.php";
  var params="_action=recupera&"+params_recupero+"="+value;
  $.ajax({
    type: "POST",
    url: page,
    data: params, 
    dataType: "html",
    success: function(result)
    {
      //alert(result)
      var response=result.split("|");
      var result=response[0];
      var desc=response[1];

      $("#label_result").html('<br><center>'+desc+'</center>')
      
      if(result=='true')
      {
        $("#div_result").addClass("has-success");
        $("#div_result").show();
      } 

    },
    error: function()
    {
      console.log("Chiamata fallita, si prega di riprovare...");
    }
  });
}
</script>
</html>
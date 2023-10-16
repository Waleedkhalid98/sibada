<?php
include("./common.php");
include("../librerie/librerie.php");

global $db;
global $db_front;
?>
<!doctype html>
<html lang="it" style="background: #FFFFFF;">

<head>
	<title>SiBada - Area riservata</title>
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
			<section id="sezioni-eventi">
				<main id="main-content" class="container px-4 mt-5">
					<div class="row">
						<div class="col-sm">
							<h1>Servizi online</h1>
							<h5>Benvenuto nel Portale dei lavoratori socio-sanitario del Comune di Ozieri.<br>
								
							</h5>
						</div>
						<div class="col-md-8 offset-md-2 col-lg-4 offset-lg-4">
							<h1 class="text-center title">Area riservata Admin</h1><br>
							<form style="max-width: 400px;margin: 0 auto;" class="validate-form" method="post"
								action="/user/login" name="loginform">
								<input type="hidden" name="hidden" value="" />
								<div class="form-group">
									<div class="controls">
										<label for="user">Login</label>
										<input type="text" id="user" autocomplete="off" name="user" class="form-control"
											data-rule-required="true" maxlength="30" value=""
											onkeydown="if(event.keyCode==13) doLOGIN()" />
									</div>
								</div>
								<div class="form-group">
									<input type="password" id="password" name="password"
										class="form-control input-password" aria-labelledby="infoPassword">
									<span class="password-icon" aria-hidden="true">
										<svg class="password-icon-visible icon icon-sm">
											<use xlink:href="static/img/sprite.svg#it-password-visible"></use>
										</svg>
										<svg class="password-icon-invisible icon icon-sm d-none">
											<use xlink:href="static/img/sprite.svg#it-password-invisible"></use>
										</svg>
									</span>
									<label for="password">Password</label>
								</div>
								<div class="text-center mb-5">
									<span id="alert_div" class="help-block"><b>Attenzione:</b> nome utente o password
										errati.<br></span><br>
									<button class="btn btn-primary btn-sm" name="_conferma" id="_conferma" type="button"
										value="Login" onclick="doLOGIN()">Login</button><br><br>
									<!--a href="sibada_registrazionescelta.php">Non hai le credenziali, registrati</a><br>
									<a href="sibada_richiestapassword.php">Hai dimenticato la password?</a-->
								</div>
							</form>
						</div>
					</div>
				</main>

			</section>
		</main>
	</div>
	<br><br><br><br><br>
	<?php echo get_footer_sibada(); ?>
	<?php echo get_importazioni_sibada(); ?>

</body>

<script>
	$(document).ready(function () {
		$("#form_login").removeClass("has-error");
		$("#alert_div").css('visibility', 'hidden');
	});

	function doLOGIN() {
		$("#form_login").removeClass("has-error");
		$("#alert_div").css('visibility', 'hidden');

		setTimeout(function () {
			flduser = $("#user").val()
			fldpwd = $("#password").val()

			var page = "sibada_action.php";
			var params = "_action=sibada_login_back&_u=" + flduser + "&_p=" + fldpwd;
			var loader = dhtmlxAjax.postSync(page, params);
			myParam = loader.xmlDoc.responseText;
			(myParam)
			if (myParam)
				window.location = (myParam)
			else {
				$("#form_login").addClass("has-error");
				$("#alert_div").css('visibility', 'visible');
			}
		}, 500);
	}
</script>

</html>
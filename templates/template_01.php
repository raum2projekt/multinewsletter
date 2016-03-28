<?php
$impressum_id = 70;
?>
<!DOCTYPE html>
<html lang="<?php echo seo42::getLangCode(); ?>">
<head>
	<meta charset="utf-8" />
	<base href="<?php echo seo42::getBaseUrl(); ?>" />
	<title><?php echo seo42::getTitle(); ?></title>
	<meta name="robots" content="<?php echo seo42::getRobotRules();?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript"><?php print file_get_contents(rex_path::addonAssets("multinewsletter") .'/template/jquery-2.1.4.min.js'); ?></script>
	<link rel="icon" href="<?php echo seo42::getMediaFile("favicon.ico"); ?>">
	<style type="text/css"><?php print file_get_contents(rex_path::addonAssets("multinewsletter") .'/template/bootstrap.min.css'); ?></style>
	<style type="text/css"><?php print file_get_contents(rex_path::addonAssets("multinewsletter") .'/template/style.css'); ?></style>
</head>

<body class="newsletter">
	<header>
		<center>
			<?php
				print '<img class="logo" src="'. seo42::getMediaFile("dks-logo-gr.png") .'" alt="Dieter-Kaltenbach-Stiftung">';
			?>
			<p class="onlinelink"><a href="+++NEWSLETTERLINK+++">Wenn dieser
				Newsletter nicht korrekt angezeigt wird, klicken Sie bitte hier</a>.</p>
		</center>
	</header>
	<br clear="all">
	<section class="section">
		<div class="container">
			<div class="row" data-match-height>
				REX_ARTICLE[]
			</div>
			<div class="row" data-match-height>
				<div class="col-xs-12"><br></div>
			</div>
		</div>
		<br clear="all">
	</section>
	<footer>
		<div class="container">
			<div class="row" data-match-height>
				<?php
					$impressum = rex_article::get($impressum_id);
					print '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
					print '<div class="footer-box" data-height-watch><a href="'. $impressum->getUrl() .'">'. $impressum->getName() .'</a></div>';
					print '</div>';

					print '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
					print '<div class="footer-box" data-height-watch><a href="+++ABMELDELINK+++">Newsletter abmelden</a></div>';
					print '</div>';
				?>
			</div>
		</div>
		<br clear="all">
	</footer>
	<script type="text/javascript"><?php print file_get_contents(rex_path::addonAssets("multinewsletter") .'/template/bootstrap.min.js'); ?></script>
	<script>
		$(window).on("load",
			function(e) {
				$("[data-match-height]").each(
					function() {
						var e=$(this),
							t=$(this).find("[data-height-watch]"),
							n=t.map(function() {
								return $(this).innerHeight();
							}).get(),
							i=Math.max.apply(Math,n);
						t.css("min-height", i+1);
					}
				)
			}
		)
	</script>
</body>
</html>
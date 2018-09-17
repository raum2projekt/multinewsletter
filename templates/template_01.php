<?php
$impressum_id = 70;
?>
<!DOCTYPE html>
<?php print '<html lang="'. rex_clang::getCurrent()->getCode() .'">'; ?>
<head>
	<meta charset="utf-8" />
	<base href="<?php echo rex::getServer(); ?>" />
<?php
if (rex_addon::get('yrewrite')->isAvailable()) {
	$yrewrite = new \rex_yrewrite_seo();
	echo $yrewrite->getRobotsTag();
	echo $yrewrite->getTitleTag();
}
?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?php echo rex_url::media('favicon.ico'); ?>">
	<style><?php print file_get_contents(rex_path::media("newsletterstyles.css")); ?></style>
</head>

<body class="newsletter prevent_d2u_helper_styles">
	<header>
		<center>
			<?php
				print '<img class="logo" src="'. rex_url::media("mein-logo.jpg") .'" alt="Logo">';
			?>
			<p class="onlinelink"><a href="+++NEWSLETTERLINK+++">Wenn dieser
				Newsletter nicht korrekt angezeigt wird, klicken Sie bitte hier</a>.</p>
		</center>
	</header>
	<br clear="all">
	<section class="section">
		<div class="container">
			<div class="row">
				REX_ARTICLE[]
			</div>
			<div class="row">
				<div class="col-xs-12"><br></div>
			</div>
		</div>
		<br clear="all">
	</section>
	<footer>
		<div class="container">
			<div class="row">
				<?php
					$impressum = rex_article::get($impressum_id);
					print '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
					print '<div class="footer-box"><a href="'. $impressum->getUrl() .'">'. $impressum->getName() .'</a></div>';
					print '</div>';

					print '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">';
					print '<div class="footer-box"><a href="+++ABMELDELINK+++">Newsletter abmelden</a></div>';
					print '</div>';
				?>
			</div>
		</div>
		<br clear="all">
	</footer>
</body>
</html>
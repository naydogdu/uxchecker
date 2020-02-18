<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>">
<head>
	<meta charset="UTF-8">
	<title><?php echo siteInfo('title'); ?></title>
	<meta name="description" content="<?php echo siteInfo('description'); ?>">
	<meta name="author" content="Nazmi Aydogdu - @aydogduN">
	<meta name="viewport" content="width=device-width" />
	<link rel="canonical" href="<?php echo getCanonical(); ?>">
	<script src="https://use.typekit.net/fny2bhc.js"></script>
	<script>try{Typekit.load({ async: true });}catch(e){}</script>
    <script src="https://kit.fontawesome.com/76db41754c.js" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo cssPath(); ?>" />
	<link rel="shortcut icon" href="<?php echo imgPath( 'favicon.png' ); ?>">
	<link rel="apple-touch-icon" href="<?php echo imgPath( 'appicon.png' ); ?>">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo imgPath( 'appicon-76.png' ); ?>">
	<link rel="apple-touch-icon" sizes="120x120" href="<?php echo imgPath( 'appicon-120.png' ); ?>">
	<link rel="apple-touch-icon" sizes="152x152" href="<?php echo imgPath( 'appicon-152.png' ); ?>">
</head>
<body <?php bodyClass(); ?>>
	<header class="posrel">
		<div class="wrap clear pd36-0">
			<a class="site-logo floatl" href="<?php echo getCanonical(); ?>" title="<?php echo siteInfo('title'); ?>" rel="home"><i class="fas fa-angle-left"></i> <b><?php siteLogo(); ?></b> / <i class="fas fa-angle-right"></i></a>
			<span class="nav-toggle floatr dis-no-960"><i class="fas fa-bars"></i></span>
			<nav class="main-nav dis-no dis-b-960 floatr-960"><?php echo mainNav(); ?></nav>
		</div>		
	</header>
<?php if( !canAnalyze() || inputError() ) : 
include('intro.php');
else : ?>
	<main>
		<?php analyzeHead(); ?>
		<div class="wrap clear pd48-0">	
			<div class="floatl-960 w25-960"><?php sideNav(); ?></div>
			<div class="entry-content floatr-960 w70-960"><?php siteAnalyze(); ?></div>
		</div>
	</main>
<?php endif; ?>	
	<footer>
	</footer>
</body>
</html>
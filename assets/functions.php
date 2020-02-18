<?php 

define('SITE_URL', 'https://uxchecker.grapheek.com');

/* i18n */
function getLang() {
	if( substr($_SERVER['REQUEST_URI'],0,3) == '/en')
		return 'en-US';
	else
		return 'fr-FR';
}

function getApp($index='index.php') {
	include dirname( __FILE__ ) . '/template/' . $index;
}

function _i($string) {
	$lang = getLang();
	switch( $lang ) :
	case 'fr-FR' :
		return $string;
		break;
	default :
		/* va récupérer le string dans le fichier de traduction */
		$filename = dirname( __FILE__ ) . '/lang/'. getLang() .'.php';
		if (file_exists($filename)) {
			$handle = include $filename;
			foreach( $strings as $key => $val) :
				if( $key === $string ) $string = $val;
			endforeach;
		} 
		return $string;
	endswitch;
}

/* global */
function _e($string) {
	echo _i($string);
}

function siteInfo($info) {
	switch ( $info ) :
	case 'url' :
		/* URL DE BASE --- A MODIFIER */
		return SITE_URL; 
		break;
	case 'title' :
		return _i('CheckUx, Outil de vérification de site Internet');
		break;
	case 'lang' :
		return getLang();
		break;
	case 'description' : 
		return _i('Notre outil d\'analyse notera votre site Internet sur sa conception, son contenu, son optimisation SEO, afin que vous puissiez passer en revue les points à améliorer.');
	/* case 'XXX' : */
	endswitch;
}

function imgPath($url) {
	return siteInfo('url') . '/assets/img/'. $url;
}

function cssPath($file='style.css') {
	return siteInfo('url') . '/assets/css/'. $file;
}

function langPath($file='', $ext='.php') {
	return siteInfo('url') . '/assets/lang/'. $file . $ext;
}

function getCanonical($path='') {
	$lang = getLang();
	if( $lang != 'fr-FR' ) {
		$path = substr($lang,0,2);
		$path .= '/';
	}
	return siteInfo('url') . '/' . $path ;
}

function canAnalyze() {
	if( isset($_GET['site']) ) {
		return true;
	} else {
		return false;
	}
}

function inputError() {
	if( canAnalyze() ) :		
		if( !empty($_GET['site']) ) :
		
			return false;
			
		else :
			$content = '<div class="bgcol-red"><p class="wrap clear pd24-0 col-white">';
				$content .= '<i class="fas fa-exclamation-circle pdr12"></i>';
				$content .= _i('Veuillez renseigner l\'url.');
			$content .= '</p></div>';
			
			return $content;
			
		endif;				
	else : 
	
		return false; 
		
	endif;
}

function siteInput($val='',$class='') {
	?>
	<form class="<?php echo $class; ?>" action="" method="">
		<input class="main-input" type="text" name="site" placeholder="<?php _e('URL de votre site Internet'); ?>" value="<?php echo $val; ?>" />
		<button type="submit"><i class="fas fa-paper-plane"></i></button>
	</form>
	<?php 
}

function getCheckedUrl() {
	$siteUrl = $_GET['site'];
	$check = parse_url($siteUrl);
	
	if( empty($check['scheme']) ) : 
		$check['scheme'] = 'http'; 		
		$checkedUrl = $check['scheme'] . '://' . $siteUrl; 
	else :
		$checkedUrl = $siteUrl;
	endif;
	
	return $checkedUrl;
}

function sideNav() {
	return '';
}

function siteAnalyze() {
		
	$checkedUrl = getCheckedUrl();
	function file_get_contents_curl($url) {
		$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$result = curl_exec($ch);
		if(curl_errno($ch)){
			echo 'Curl error: ' . curl_error($ch);
		}
		$last = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		return array($result,$last);
	}

	$html = file_get_contents_curl($checkedUrl);

	//parsing begins here:
	$doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTML($html[0]);
	/*
	$root = $doc->createElement('html');
	$root = $doc->appendChild($root);

	$head = $doc->createElement('head');
	$head = $root->appendChild($head);

	$title = $doc->createElement('title');
	$title = $head->appendChild($title);

	$analyzed = $doc->saveHTML();
	*/
	libxml_use_internal_errors();
	$nodes = $doc->getElementsByTagName('title');

	
	//get and display what you need:
	$title = $nodes->item(0)->nodeValue;
	$doctype = $doc->doctype;
	
	$html5 = array();
	$html5['header'] = $doc->getElementsByTagName('header');
	$html5['nav'] = $doc->getElementsByTagName('nav');
	$html5['article'] = $doc->getElementsByTagName('article');
	$html5['footer'] = $doc->getElementsByTagName('footer');
	
	$html5['lang'] = $doc->getElementsByTagName('html');	
	foreach($html5['lang'] as $l) :
		$lang = $l->getAttribute('lang');
	endforeach;
	
	$valid = 0;
	$total = 0;
	foreach( $html5 as $key => $h) :
		if( $h->length > 0 ) :
			$valid++;
			$html5ValidItem[] = $key;	
		else :
			$html5NotValidItem[] = $key; 
		endif;
		$total++;
	endforeach;

	if( $valid === $total) $html5Valid = true;
	else $html5Valid = false;
	
	print('<pre>');
	//print_r( $html5notValidItem );
	print('</pre>');
	
	$metas = $doc->getElementsByTagName('meta');

	for ($i = 0; $i < $metas->length; $i++)
	{
		$meta = $metas->item($i);
		if( !empty( $meta->getAttribute('charset') ) )
			$charset = $meta->getAttribute('charset');
		if( strtolower( $meta->getAttribute('name') ) == 'description' || strtolower( $meta->getAttribute('property') ) == 'og:description') 
			$description = $meta->getAttribute('content');
		if( strtolower( $meta->getAttribute('name') ) == 'keywords' || strtolower( $meta->getAttribute('property') ) == 'og:keywords')
			$keywords = $meta->getAttribute('content');
		if( strtolower( $meta->getAttribute('name') ) == 'author' || strtolower( $meta->getAttribute('property') ) == 'og:author')
			$author = $meta->getAttribute('author');
	}
	?>
		<h3><?php _e('HTML5'); ?></h3>
		<?php if($html5Valid && !empty($doctype) && $charset) : ?>			
			<p><?php _e('Votre site est bien conçu en HTML5.'); ?></p>
			<small><?php foreach($html5ValidItem as $h ) : if($h != 'lang' ) echo '<span class="pdr6">'.htmlspecialchars('<'.$h.'>').'</span>'; endforeach; ?></small>
			<h3><?php _e('Doctype'); ?></h3>		
			<p><?php _e('Le Doctype est correctement déclarée.'); ?></p>
			<small><?php echo htmlspecialchars($doctype->internalSubset); ?></small>
			<h3><?php _e('Charset'); ?></h3>		
			<p><?php _e('Le Charset est défini en'); ?> <?php echo $charset . '.'; ?></p>
		<?php else : ?>
			<?php if(!$html5Valid) : ?>
				<p><?php _e('Votre site n\'est pas conçu en HTML5.'); ?></p>
				<small>
					<?php _e('Le HTML5 nécessite certaines balises afin de sémantiser le contenu. Votre site n\'utilise pas les balises suivantes :'); ?>
					<?php foreach($html5NotValidItem as $h ) : if($h != 'lang' ) echo '<span class="pdr6">'.htmlspecialchars('<'.$h.'>').'</span>'; endforeach; ?>
				</small>
			<?php endif; ?>
			<?php if( empty($doctype) ) : ?>
				<p><?php _e('Votre site ne contient pas de Doctype. C\'est plutôt embarassant.'); ?></p>
				<small><?php _e('Ajoutez au tout début de votre document la balise'); ?> <?php echo htmlspecialchars('<!DOCTYPE html>'); ?></small>
			<?php endif; ?>	
			<h3><?php _e('Charset'); ?></h3>	
			<?php if( empty($charset) ) : ?>					
				<p><?php _e('Le Charset n\'est pas défini.'); ?></p>
				<small><?php _e('Ajoutez la meta charset juste après la balise &lt;head&gt;. Exemple :'); ?> <?php echo htmlspecialchars('<meta charset="UTF-8">'); ?></small>
			<?php else : ?>		
				<p><?php _e('Le Charset est défini en'); ?> <?php echo $charset; ?></p>
			<?php endif; ?>
			
		<?php endif; ?>
		
		<h3><?php _e('Langue'); ?></h3>
		<?php if( !empty($lang) ) : ?>			
			<p><?php _e('L\'attribut langue est bien déclarée.'); ?></p>
			<small><?php echo htmlspecialchars('<html lang="'.$lang.'">'); ?></small>
		<?php else : ?>
			<p><?php _e('La langue de votre site n\'est pas déclarée.'); ?></p>
			<small><?php _e('Ajoutez l\'attribut "lang" à votre balise html. Exemple :'); ?> <?php echo htmlspecialchars('<html lang="'.getLang().'">'); ?></small>
		<?php endif; ?>
		
		<h3><?php _e('Titre'); ?></h3>
		<?php if( !empty($title) ) : ?>			
			<p><?php echo $title; ?></p>
		<?php else : ?>
			<p><?php _e('Pas de titre'); ?></p>
		<?php endif; ?>
		
		<h3><?php _e('Description'); ?></h3>
		<?php if( !empty($description) ) : ?>			
			<p><?php echo $description; ?></p>
		<?php else : ?>
			<p><?php _e('Pas de description'); ?></p>
		<?php endif; ?>
		
		<h3><?php _e('Mots-clés'); ?></h3>
		<?php if( !empty($keywords) ) : ?>			
			<p><?php echo $keywords; ?></p>
		<?php else : ?>
			<p><?php _e('Pas de mots-clés'); ?></p>
		<?php endif; ?>	
		
		<h3><?php _e('Auteur'); ?></h3>
		<?php if( !empty($author) ) : ?>			
			<p><?php echo $author; ?></p>
		<?php else : ?>
			<p><?php _e('Pas d\'auteur'); ?></p>
		<?php endif; ?>
	<?php 
}

function analyzeHead() {
	?>
	<div class="pd48-0 primary-gradient col-white">
		<div class="wrap clear">
			<div class="floatl-600 w65-600">
				<span class="js-global-rate pdr12" data-rating="">76.5</span>
				<p class="dis-ib vatop pd6-0"><span class="dis-b box bgcol-black01 pd6 pdr12 pdl12"><?php _e('Note globale'); ?></span></p>
			</div>
			<div class="floatr-600 w35-600">
				<?php siteInput( getCheckedUrl(), 'small tar'); ?>
			</div>
		</div>
	</div>
	<?php 
}

function bodyClass($new='') {
	$classes = 'class="';
	$classes .= $new;
	$classes .= '"';
	echo $classes;
}

function mainNav() {
	$menu = '';
	$items = array(
		_i('Accueil') => getCanonical(),
	);
	foreach( $items as $m => $href) :
		$menu .= '<a href="'. $href .'" title="'. $m .'">'. $m .'</a>';
	endforeach;
	
	return $menu;
}

function siteLogo() {
	echo 'check<span class="primary-col">ux</span>';
}

function homeFeatures() {
	$contents = array(
		array( 'icon' => 'fa-code', 'title' => _i('Conception'), 'desc' => _i('Analysez la conception de votre site web.') ),
		array( 'icon' => 'fa-pencil-alt', 'title' => _i('Contenu'), 'desc' => _i('Organisez votre contenu et gagnez des visiteurs.') ),
		array( 'icon' => 'fa-chart-line', 'title' => _i('SEO'), 'desc' => _i('Améliorez votre positionnement sur les moteurs de recherche.') ),
	);
	foreach( $contents as $t ) : ?>
		<article class="pd24 tac">
			<i class="fas <?php echo $t['icon']; ?> fa-5x primary-col mb24"></i>
			<h2 class="mb12"><?php echo $t['title']; ?></h2>
			<p><?php echo $t['desc']; ?></p>
		</article>
	<?php endforeach;
}
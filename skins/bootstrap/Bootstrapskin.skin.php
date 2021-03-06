<?php
/**
 * Vector - Modern version of MonoBook with fresh look and many usability
 * improvements.
 *
 * @todo document
 * @file
 * @ingroup Skins
 */
if ( ! defined ( 'MEDIAWIKI' ) ) {
	die( - 1 );
}

/**
 * SkinTemplate class for Vector skin
 * @ingroup Skins
 */
class SkinBootstrap extends SkinTemplate {

	var $skinname = 'bootstrapskin' , $stylename = 'bootstrapskin' , $template = 'StrappingTemplate' , $useHeadElement = true;

	/**
	 * Initializes output page and sets up skin-specific parameters
	 *
	 * @param $out OutputPage object to initialize
	 */
	public function initPage ( OutputPage $out ) {
		global $wgLocalStylePath;
		parent::initPage ( $out );
		// Append CSS which includes IE only behavior fixes for hover support -
		// this is better than including this in a CSS fille since it doesn't
		// wait for the CSS file to load before fetching the HTC file.
		$min = $this->getRequest ()->getFuzzyBool ( 'debug' ) ? '' : '.min';
		$out->addHeadItem ( 'csshover' ,
		                    '<!--[if lt IE 7]><style type="text/css">body{behavior:url("' .
		                    htmlspecialchars ( $wgLocalStylePath ) .
		                    "/{$this->stylename}/csshover{$min}.htc\")}</style><![endif]-->" );
		//Replace the following with your own google analytic info
		$out->addHeadItem ( 'analytics' , '<script type="text/javascript">' . "

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-0000000-00']);
_gaq.push(['_setDomainName', 'yourdomain.com/with-no-http://']);
_gaq.push(['_setAllowHash', 'false']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

</script>" );
		$out->addHeadItem ( 'responsive' , '<meta name="viewport" content="width=device-width, initial-scale=1.0">' );
		$out->addModuleScripts ( 'skins.bootstrapskin' );
	}

	/**
	 * Load skin and user CSS files in the correct order
	 * fixes bug 22916
	 *
	 * @param $out OutputPage object
	 */
	function setupSkinUserCss ( OutputPage $out ) {
		global $wgResourceModules;
		parent::setupSkinUserCss ( $out );
		// FIXME: This is the "proper" way to include CSS
		// however, MediaWiki's ResourceLoader messes up media queries
		// See: https://bugzilla.wikimedia.org/show_bug.cgi?id=38586
		// &: http://stackoverflow.com/questions/11593312/do-media-queries-work-in-mediawiki
		//
		//$out->addModuleStyles( 'skins.strapping' );
		// Instead, we're going to manually add each,
		// so we can use media queries
		foreach ( $wgResourceModules[ 'skins.bootstrapskin' ][ 'styles' ] as $cssfile => $cssvals ) {
			if ( isset( $cssvals ) ) {
				$out->addStyle ( $cssfile , $cssvals[ 'media' ] );
			}
			else {
				$out->addStyle ( $cssfile );
			}
		}

	}
}

/**
 * QuickTemplate class for Vector skin
 * @ingroup Skins
 */
class StrappingTemplate extends BaseTemplate {

	/* Functions */
	/**
	 * Outputs the entire contents of the (X)HTML page
	 */
	public function execute () {
		global $wgGroupPermissions;
		global $wgVectorUseIconWatch;
		global $wgSearchPlacement;
		global $wgBootstrapSkinLogoLocation;
		global $wgBootstrapSkinLoginLocation;
		global $wgBootstrapSkinAnonNavbar;
		global $wgBootstrapSkinUseStandardLayout;
		global $wgBootstrapSkinUseSidebar;
		global $wgBootStrapSkinSideBar;
		if ( ! $wgSearchPlacement ) {
			$wgSearchPlacement[ 'header' ] = true;
			$wgSearchPlacement[ 'nav' ]    = false;
			$wgSearchPlacement[ 'footer' ] = false;
		}
		// Build additional attributes for navigation urls
		$nav = $this->data[ 'content_navigation' ];
		if ( $wgVectorUseIconWatch ) {
			$mode = $this->getSkin ()->getTitle ()->userIsWatching () ? 'unwatch' : 'watch';
			if ( isset( $nav[ 'actions' ][ $mode ] ) ) {
				$nav[ 'views' ][ $mode ]              = $nav[ 'actions' ][ $mode ];
				$nav[ 'views' ][ $mode ][ 'class' ]   = rtrim ( 'icon ' . $nav[ 'views' ][ $mode ][ 'class' ] , ' ' );
				$nav[ 'views' ][ $mode ][ 'primary' ] = true;
				unset( $nav[ 'actions' ][ $mode ] );
			}
		}
		$xmlID = '';
		foreach ( $nav as $section => $links ) {
			foreach ( $links as $key => $link ) {
				if ( $section == 'views' && ! ( isset( $link[ 'primary' ] ) && $link[ 'primary' ] ) ) {
					$link[ 'class' ] = rtrim ( 'collapsible ' . $link[ 'class' ] , ' ' );
				}
				$xmlID                                   = isset( $link[ 'id' ] ) ? $link[ 'id' ] : 'ca-' . $xmlID;
				$nav[ $section ][ $key ][ 'attributes' ] = ' id="' . Sanitizer::escapeId ( $xmlID ) . '"';
				if ( $link[ 'class' ] ) {
					$nav[ $section ][ $key ][ 'attributes' ] .= ' class="' . htmlspecialchars ( $link[ 'class' ] ) . '"';;
					unset( $nav[ $section ][ $key ][ 'class' ] );
				}
				if ( isset( $link[ 'tooltiponly' ] ) && $link[ 'tooltiponly' ] ) {
					$nav[ $section ][ $key ][ 'key' ] = Linker::tooltip ( $xmlID );
				}
				else {
					$nav[ $section ][ $key ][ 'key' ] = Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( $xmlID ) );
				}
			}
		}
		$this->data[ 'namespace_urls' ] = $nav[ 'namespaces' ];
		$this->data[ 'view_urls' ]      = $nav[ 'views' ];
		$this->data[ 'action_urls' ]    = $nav[ 'actions' ];
		$this->data[ 'variant_urls' ]   = $nav[ 'variants' ];
		// Output HTML Page
		$this->html ( 'headelement' );
		?>

		<?php if ( $wgGroupPermissions[ '*' ][ 'edit' ] || $wgBootstrapSkinAnonNavbar || $this->data[ 'loggedin' ] ) { ?>

			<?php //phpinfo(); ?>
			<!-- wrap -->
			<div id = "wrap">
			<div id = "userbar" class = "color-white bg-gray navbar" >
<div class = "navbar-inner" >
<div class = "col-md-8 col-sm-8 col-xs-12 pull-left" >
<ul id = "tabs-default-lighter" class = "nav nav-tabs nav-tabs-lighter" >
<li ><?php $this->renderNavigation ( array ( 'EDIT' ) ); ?></li >
<li ><?php $this->renderNavigation ( array ( 'PERSONALNAV' ) ); ?></li >
<li ><?php $this->renderNavigation ( array ( 'PAGE' ) ); ?></li >
<li ><?php $this->renderNavigation ( array ( 'ACTIONS' ) ); ?></li >
<li ><?php if ( ! isset( $portals[ 'TOOLBOX' ] )) {
	$this->renderNavigation ( array ( 'TOOLBOX' ) ); ?></li >
</ul >
	<?php
	if ( $wgBootstrapSkinLogoLocation == 'navbar' ) {
		$this->renderLogo ();
	}
	# This content in other languages
	if ( $this->data[ 'language_urls' ] ) {
		$this->renderNavigation ( array ( 'LANGUAGES' ) );
	}
	# Sidebar items to display in navbar
	$this->renderNavigation ( array ( 'SIDEBARNAV' ) );
	}
	?>
</div >
<div class = "padding-top col-md-4 col-sm-4 col-xs-12" >
<div class = "pull-right" >
<?php
if ( $wgSearchPlacement[ 'header' ] ) {
	$this->renderNavigation ( array ( 'SEARCH' ) );
}
# Personal menu (at the right)
# $this->renderNavigation( array( 'PERSONAL' ) );
?>
</div >
</div >
</div >
</div >
		<?php } ?>
<div id="mw-page-base" class="noprint"></div>
<div id="mw-head-base" class="noprint"></div>

<!-- Header -->
<div id="page-header" class="bg-water container-fluid <?php echo $this->data[ 'loggedin' ] ? 'signed-in' : 'signed-out'; ?>">
<div id="header-row-custom" class="row">
<div id="header-title" class="col-md-2 col-sm-2 col-xs-12 padding-left pull-left">
<div class="row">
<div class="">
<h1><b><a href="/">MediaKenjaβ</a></b></h1>
<span><b>電子版メディアカード</b></span>
</div>

<?php $current_url = ( empty( $_SERVER[ "HTTPS" ] ) ? "http://" : "https://" ) . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ]; ?>
<div id = "social-btns" class = "buttons-whitespace-social text-center pull-left">
<a href = "http://www.twitter.com/share?url=<?php echo urlencode ( $current_url ); ?>" onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
<button type = "button" class = "btn btn-cyanide btn-social"><i class = "fa fa-twitter-square"></i></button>
</a>
<a href = "https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode ( $current_url ); ?>" onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
<button type = "button" class = "btn btn-info btn-social"><i class = "fa fa-facebook-square"></i></button>
</a>
<a href = "https://plus.google.com/share?url=<?php echo urlencode ( $current_url ); ?>" onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
<button type = "button" class = "btn btn-berry btn-social"><i class = "fa fa-google-plus-square"></i></button>
</a>
<a href = "http://www.tumblr.com/share?v=3&amp;u=<?php echo urlencode ( $current_url ); ?>" onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
<button type = "button" class = "btn btn-info btn-social"><i class = "fa fa-tumblr-square"></i></button>
</a>
</div>
<?php
		/*if ( $wgBootstrapSkinLogoLocation == 'bodycontent' ) {
		$this->renderLogo ();
		}*/ ?>

</div>
</div>
<div id="header-menu" class="col-md-9 col-sm-9 col-xs-12 pull-right offset1">
<div class="menubar">
<ul class="navigation nav nav-pills pull-right searchform-disabled">
<?php
		$this->renderNavigation ( array ( 'SIDEBAR' ) );
		//								if ( $wgSearchPlacement[ 'nav' ] ) {
		//									$this->renderNavigation ( array ( 'SEARCHNAV' ) );
		//								}
		?>
</ul>
</div>
</div>
</div>
</div>

<?php
		if ( $this->data[ 'loggedin' ] ) {
			$userStateClass = "user-loggedin";
		}
		else {
			$userStateClass = "user-loggedout";
		}
		?>
		<?php
		if ( $wgGroupPermissions[ '*' ][ 'edit' ] || $this->data[ 'loggedin' ] ) {
			$userStateClass += " editable";
		}
		else {
			$userStateClass += " not-editable";
		}
		?>

<!-- content -->
<section id="content" class="mw-body container-fluid <?php echo $userStateClass; ?>">
<div id="top"></div>
<div id="mw-js-message" style="display:none;"<?php $this->html ( 'userlangattributes' ) ?>></div>
<?php if ( $this->data[ 'sitenotice' ] ): ?>
			<!-- sitenotice -->
			<div id = "siteNotice" ><?php $this->html ( 'sitenotice' ) ?></div >
			<!-- /sitenotice -->
		<?php endif; ?>

<!-- bodyContent -->
<div id="bodyContent" class="col-md-7 col-sm-7 col-xs-12 pull-left">
<?php if ( $this->data[ 'newtalk' ] ): ?>
			<!-- newtalk -->
			<div class = "usermessage" ><?php $this->html ( 'newtalk' ) ?></div >
			<!-- /newtalk -->
		<?php endif; ?>
		<?php if ( $this->data[ 'showjumplinks' ] ): ?>
			<!-- jumpto -->
			<div id = "jump-to-nav" class = "mw-jump" >
<?php $this->msg ( 'jumpto' ) ?> <a href = "#mw-head" ><?php $this->msg ( 'jumptonavigation' ) ?></a >,
<a href = "#p-search" ><?php $this->msg ( 'jumptosearch' ) ?></a >
</div >
			<!-- /jumpto -->
		<?php endif; ?>


<!-- innerbodycontent -->
<?php # Peek into the body content of articles, to see if a custom layout is used
		if ( $wgBootstrapSkinUseStandardLayout || preg_match ( "/<div.*class.*row.*>/i" , $this->data[ 'bodycontent' ] ) && $this->data[ 'articleid' ]
		) {
			# If there's a custom layout, the H1 and layout is up to the page ?>
			<div id = "innerbodycontent" class = "row layout" >
<h1 id = "firstHeading" class = "firstHeading page-header" >
<?php
$result = preg_match_all ( '/カテゴリ:(.+)/u' , urldecode ( $current_url ) , $matches );
if ( $result > 0 ) {
	?>
	<span class = "glyphicon glyphicon-folder-open" ></span >
<?php }
else { ?>
	<span class = "glyphicon glyphicon-file" ></span >
<?php } ?>
	<span dir = "auto" > <?php $this->html ( 'title' ) ?></span >
</h1 >

				<!-- to bbs link -->
				<?php
				$path_name = urldecode ( parse_url ( $current_url , PHP_URL_PATH ) );
				preg_match ( '/index\.php\/(.+?)$/u' , $path_name , $matches_page_name );
				$page_name = isset( $matches_page_name[ 1 ] ) ? $matches_page_name[ 1 ] : '';
				preg_match ( '/カテゴリ[(:|・トーク:)](.+?)$/u' , $page_name , $matches_category_name );
				$is_category_page = isset( $matches_category_name[ 1 ] ) ? true : false;
				$is_talk_page     = preg_match ( '/トーク:/u' , $page_name ) == 1 ? true : false;
				if ( $is_category_page ) {
					$page_name =
						$is_talk_page
							? 'カテゴリ:' . str_replace ( 'カテゴリ・トーク:' , '' , $page_name ) : 'カテゴリ・トーク:' . str_replace ( 'カテゴリ:' , '' , $page_name );
				}
				else {
					$page_name = $is_talk_page ? str_replace ( 'トーク:' , '' , $page_name ) : 'トーク:' . $page_name;
				}
				$reverse_card_url = $page_name;
				?>
				<a href = "<?php echo $reverse_card_url; ?>" >
<button class = "btn btn-primary btn-sm" >
<span class = "glyphicon glyphicon-chevron-right" ></span ><span class = "glyphicon glyphicon-list-alt" ></span >
	<?php echo $is_talk_page ? ' カードに戻る' : ' 掲示板に進む'; ?>
</button >
</a >
<!-- /to bbs link -->
<!-- save category's cards as PDF -->
				<?php
				$result = preg_match_all ( '/カテゴリ:(.+)/u' , urldecode ( $current_url ) , $matches );
				if ( $result > 0 ) {
					$category_name = $matches[ 1 ][ 0 ];
					?>
					<a href = "../printPDF/printPDF.php?category=<?php echo $category_name; ?>" target = "_blank" >
<button class = "btn btn-primary btn-sm" >
<span class = "glyphicon glyphicon-save" ></span >　今見ているカテゴリのカードをPDF形式で一括ダウンロード
</button >
</a >
				<?php
				}
				?>
				<!-- /save category's cards as PDF -->

<!-- catlinks -->
				<?php if ( $this->data[ 'catlinks' ] ) { ?>
					<?php $this->html ( 'catlinks' ); ?>
				<?php } ?>
				<!-- /catlinks -->
<!-- subtitle -->
<div id = "contentSub" <?php $this->html ( 'userlangattributes' ) ?>><?php $this->html ( 'subtitle' ) ?></div >
<!-- /subtitle -->
				<?php if ( $this->data[ 'undelete' ] ): ?>
					<!-- undelete -->
					<div id = "contentSub2" ><?php $this->html ( 'undelete' ) ?></div >
					<!-- /undelete -->
				<?php endif; ?>
				<?php $this->html ( 'bodycontent' ); ?>
</div >
		<?php }
		else {
			# If there's no custom layout, then we automagically add one
			?>
			<div id = "innerbodycontent" class = "row nolayout" >
<div class = "offset1 span10" >
<h1 id = "firstHeading" class = "firstHeading page-header" >
<?php
$result = preg_match_all ( '/カテゴリ:(.+)/u' , urldecode ( $current_url ) , $matches );
if ( $result > 0 ) {
	?>
	<span class = "glyphicon glyphicon-folder-open" ></span >
<?php }
else { ?>
	<span class = "glyphicon glyphicon-file" ></span >
<?php } ?>
	<span dir = "auto" > <?php $this->html ( 'title' ) ?></span >
</h1 >

<!-- to bbs link -->
	<?php
	$path_name = urldecode ( parse_url ( $current_url , PHP_URL_PATH ) );
	preg_match ( '/index\.php\/(.+?)$/u' , $path_name , $matches_page_name );
	$page_name = isset( $matches_page_name[ 1 ] ) ? $matches_page_name[ 1 ] : '';
	preg_match ( '/カテゴリ[(:|・トーク:)](.+?)$/u' , $page_name , $matches_category_name );
	$is_category_page = isset( $matches_category_name[ 1 ] ) ? true : false;
	$is_talk_page     = preg_match ( '/トーク:/u' , $page_name ) == 1 ? true : false;
	if ( $is_category_page ) {
		$page_name =
			$is_talk_page ? 'カテゴリ:' . str_replace ( 'カテゴリ・トーク:' , '' , $page_name ) : 'カテゴリ・トーク:' . str_replace ( 'カテゴリ:' , '' , $page_name );
	}
	else {
		$page_name = $is_talk_page ? str_replace ( 'トーク:' , '' , $page_name ) : 'トーク:' . $page_name;
	}
	$reverse_card_url = $page_name;
	?>
	<a href = "<?php echo $reverse_card_url; ?>" >
<button class = "btn btn-primary btn-sm" >
<?php if ( $is_talk_page ) { ?>
	<span class = "glyphicon glyphicon-chevron-left" ></span >
<?php }
else { ?>
	<span class = "glyphicon glyphicon-chevron-right" ></span >
<?php } ?>
	<span class = "glyphicon glyphicon-list-alt" ></span >
	<?php echo $is_talk_page ? ' カードに戻る' : ' 掲示板に進む'; ?>
</button >
</a >
<!-- /to bbs link -->
<!-- save category's cards as PDF -->
	<?php
	$result = preg_match_all ( '/カテゴリ:(.+)/u' , urldecode ( $current_url ) , $matches );
	if ( $result > 0 ) {
		$category_name = $matches[ 1 ][ 0 ];
		?>
		<a href = "../printPDF/printPDF.php?category=<?php echo $category_name; ?>" target = "_blank" >
<button class = "btn btn-primary btn-sm" >
<span class = "glyphicon glyphicon-save" ></span >　今見ているカテゴリのカードをPDF形式で一括ダウンロード
</button >
</a >
	<?php
	}
	?>
<!-- /save category's cards as PDF -->

<!-- catlinks -->
	<?php if ( $this->data[ 'catlinks' ] ) { ?>
		<?php $this->html ( 'catlinks' ); ?>
	<?php } ?>
<!-- /catlinks -->

<!-- subtitle -->
<div
	id = "contentSub" <?php $this->html ( 'userlangattributes' ) ?>><?php $this->html ( 'subtitle' ) ?></div >
<!-- /subtitle -->
	<?php if ( $this->data[ 'undelete' ] ): ?>
		<!-- undelete -->
		<div id = "contentSub2" ><?php $this->html ( 'undelete' ) ?></div >
		<!-- /undelete -->
	<?php endif; ?>
	<?php $this->html ( 'bodycontent' ); ?>
</div >
</div >
		<?php } ?>
<!-- /innerbodycontent -->

<?php if ( $this->data[ 'printfooter' ] ): ?>
			<!-- printfooter -->
			<div class = "printfooter" >
<?php $this->html ( 'printfooter' ); ?>
</div >
			<!-- /printfooter -->
		<?php endif; ?>
		<?php if ( $this->data[ 'dataAfterContent' ] ): ?>
			<!-- dataAfterContent -->
			<?php $this->html ( 'dataAfterContent' ); ?>
			<!-- /dataAfterContent -->
		<?php endif; ?>
<div class="visualClear"></div>
<!-- debughtml -->
<?php $this->html ( 'debughtml' ); ?>
<!-- /debughtml -->

</div>
<!-- /bodyContent -->

<!-- sidemenu -->
<div id="sidebar" class="bg-purple col-md-5 col-sm-5 col-xs-12 pull-right">
<div id="page-info">
<h2 class="title page-header firstHeading">
<?php if ( $result > 0 ) {
			$side_title = '<span class="glyphicon glyphicon-folder-open"></span>　このカテゴリのカード';
		}
		else {
			$side_title = '<span class="glyphicon glyphicon-folder-open"></span>　同じカテゴリのカード';
		}
		echo $side_title;
		?>
</h2>
<div id="category-page">
	<?php
		$result = preg_match_all ( '/カテゴリ:(.+)/u' , urldecode ( $current_url ) , $matches );
		if ( $result > 0 ) {
			$category_name  = $matches[ 1 ][ 0 ];
			$cate_mem_api   =
				'http://media.cs.inf.shizuoka.ac.jp/api.php?format=json&action=query&list=categorymembers&cmlimit=max&cmtitle=Category:';
			$category       = $category_name;
			$cate_mem_call  = $cate_mem_api . $category;
			$cate_mem_json  = file_get_contents ( $cate_mem_call );
			$cate_mem_array = json_decode ( $cate_mem_json );
			$r              = "<ul>";
			/* 一覧生成 */
			$page = new stdClass();
			foreach ( $cate_mem_array->{'query'}->{'categorymembers'} as $key => $value ) {
				$cate_mem_pageid = $value->{'pageid'};
				$page_api        = 'http://media.cs.inf.shizuoka.ac.jp/api.php?format=json&action=query&prop=revisions&rvprop=content&pageids=';
				$page_json       = file_get_contents ( $page_api . $cate_mem_pageid );
				$page_array      = json_decode ( $page_json );
				$page->{$key}    = new stdClass();
				$title           = $page_array->{'query'}->{'pages'}->{$cate_mem_pageid}->{'title'};
				/* 項目生成 */
				$r .= '<li><span class="glyphicon glyphicon-file"></span>';
				$r .= '<a href="' . 'http://media.cs.inf.shizuoka.ac.jp/index.php/' . $title . '" target="_blank"> ' . $title . '</a>';
				$r .= "</li>";
			}
			$r .= "</ul>";
			echo $r;
		} ?>
<script type="text/javascript">
$(document).ready(function(){
/* menubar icons */
$('#header-menu li ').each(function(){
	if($(this).children('a').text().match(/^あ|か|さたな|はま$/)){
		$(this).html('　【'+$(this).children('a').text()+'】').css('font-size','smaller');
	}
	else if($(this).children('a').text().match(/^カテゴリ$/)){
		$(this).html(' ｜ '+$(this).children('a').text()+'');
	}
	else if($(this).children('a').text().indexOf('全')!=-1){
		$(this).children('a').append(' <span class="glyphicon glyphicon-th-large"></span>');
	}else{
		$(this).children('a').append(' <span class="glyphicon glyphicon-folder-open"></span>');
	}
});

/* bbs string replacing*/
$('title').html( $('title').html().replace('トーク','掲示板') );
$('#firstHeading').html( $('#firstHeading').html().replace('トーク','掲示板') );

/* tag-edit */
$delete_button_html = ' <a href="javascript:void(0);" style="color:indianred" alt="このカテゴリを削除" class="delete_category_button">' +
'<span class="glyphicon glyphicon-remove-circle"></span></a>';
$folder_open_html = ' <span class="glyphicon glyphicon-folder-open"></span>';
if( $("#tag-edit")[0] ){
	$("#tag-edit").toggle(function(){
		$(this).text("【編集完了】");
		$("#tagform input").css("display", "inline");
		$(".cat").each(function(){
			$(this).html($(this).html().split($folder_open_html).join("") + $delete_button_html);
		});
	},function(){
		$(this).text("【編集】");
		$("#tagform input").css("display", "none");
		$(".cat").each(function(){
			$(this).html($(this).html().split($delete_button_html).join("") + $folder_open_html);
		});
	});
}
/* delete category button */
if( $("a.delete_category_button")[0] ){
	$("a.delete_category_button").click(function(){
		console.log($(this).text());
	});
}
/* relational cards */
if( $("#catlinks")[0] ){

$("#catlinks").find(".cat").find("a").each(function(){
var category = $(this).text();

var r = '<h3><span class="glyphicon glyphicon-folder-open"></span>　'+category+'</h3><ul>';
var cate_mem_api = 'http://media.cs.inf.shizuoka.ac.jp/api.php?format=json&action=query&list=categorymembers&cmlimit=max&cmtitle=Category:';
var cate_mem_call = cate_mem_api+category;

$.ajax({type: 'GET',url: cate_mem_call,dataType: 'json',
success: function(json){
json.query.categorymembers.forIn(function(key, value, index){
var cate_mem_pageid = value.pageid;
var page_api = "http://media.cs.inf.shizuoka.ac.jp/api.php?format=json&action=query&prop=revision&rvprop=content&pageids=";
$.ajax({type:'GET',url:page_api+cate_mem_pageid,dataType:'json',
success: function(json){
var title = json.query.pages[cate_mem_pageid].title;
r += '<li><span class="glyphicon glyphicon-file"></span><a href="http://media.cs.inf.shizuoka.ac.jp/index.php/'+title+'" target="_blank"> '+title+'</a></li>';
}
}).done(function(){
if(index==json.query.categorymembers.length-1){
r += "</ul>";
$("#category-page").append(r);
}
});
});
}
});
$("#header-menu").find("li").each(function(){
if($(this).children("a").text()==category){
$(this).addClass("active");
}
});
});
}

});
</script>
</div>
</div>
<!-- /sidemenu-->

</section>
<!-- /content -->

</div>
<!-- /wrap -->

<!-- footer -->

<?php
		/* Support a custom footer, or use MediaWiki's default, if footer.php does not exist. */
		$footerfile = dirname ( __FILE__ ) . '/footer.php';
		if ( file_exists ( $footerfile ) ):
			?>
			<div id = "footer" class = "bg-gray color-white footer container-fluid custom-footer" ><?php
			include ( $footerfile );
			?></div ><?php
		else:
			?>

			<div id = "footer"
			     class = "bg-gray color-white footer container-fluid"<?php $this->html ( 'userlangattributes' ) ?>>
<div class = "row" >
<?php
$footerLinks = $this->getFooterLinks ();

if ( is_array ( $footerLinks ) ) {
	foreach ( $footerLinks as $category => $links ):
		if ( $category === 'info' ) {
			continue;
		} ?>

		<ul id = "footer-<?php echo $category ?>" >
<?php foreach ( $links as $link ): ?>
	<li id = "footer-<?php echo $category ?>-<?php echo $link ?>" ><?php $this->html ( $link ) ?></li >
<?php endforeach; ?>
			<?php
			if ( $category === 'places' ) {
				# Show sign in link, if not signed in
				if ( $wgBootstrapSkinLoginLocation == 'footer' && ! $this->data[ 'loggedin' ] ) {
					$personalTemp = $this->getPersonalTools ();
					if ( isset( $personalTemp[ 'login' ] ) ) {
						$loginType = 'login';
					}
					else {
						$loginType = 'anonlogin';
					}

					?>
					<li id = "pt-login" >
					<a href = "<?php echo $personalTemp[ $loginType ][ 'links' ][ 0 ][ 'href' ] ?>" ><?php echo $personalTemp[ $loginType ][ 'links' ][ 0 ][ 'text' ]; ?></a >
					</li ><?php
				}
				# Show the search in footer to all
				if ( $wgSearchPlacement[ 'footer' ] ) {
					echo '<li>';
					$this->renderNavigation ( array ( 'SEARCHFOOTER' ) );
					echo '</li>';
				}
			}
			?>
</ul >
	<?php
	endforeach;
}
?>
	<?php $footericons = $this->getFooterIcons ( "icononly" );
	if ( count ( $footericons ) > 0 ): ?>
		<ul id = "footer-icons" class = "noprint" >
<?php foreach ( $footericons as $blockName => $footerIcons ): ?>
	<li id = "footer-<?php echo htmlspecialchars ( $blockName ); ?>ico" >
<?php foreach ( $footerIcons as $icon ): ?>
	<?php echo $this->getSkin ()->makeFooterIcon ( $icon ); ?>

<?php endforeach; ?>
</li >

<?php endforeach; ?>
</ul >
	<?php endif; ?>
</div >
</div >
			<!-- /footer -->

		<?php endif; ?>

		<?php $this->printTrail (); ?>

</body>
</html>
<?php
	}

	/**
	 * Render logo
	 */
	private function renderLogo () {
		$mainPageLink = $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ];
		$toolTip      = Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( 'p-logo' ) );
		?>
		<ul class = "nav logo-container" role = "navigation" >
<li id = "p-logo" ><a
		href = "<?php echo htmlspecialchars ( $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ] ) ?>" <?php echo Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( 'p-logo' ) ) ?>><img
			src = "<?php $this->text ( 'logopath' ); ?>"
			alt = "<?php $this->html ( 'sitename' ); ?>"
			style = "width:90%" ></a >
<li >
</ul >

	<?php
	}

	/**
	 * Render one or more navigations elements by name, automatically reveresed
	 * when UI is in RTL mode
	 *
	 * @param $elements array
	 */
	private function renderNavigation ( $elements ) {
		global $wgVectorUseSimpleSearch;
		global $wgBootstrapSkinLoginLocation;
		global $wgBootstrapSkinDisplaySidebarNavigation;
		global $wgBootstrapSkinSidebarItemsInNavbar;
		// If only one element was given, wrap it in an array, allowing more
		// flexible arguments
		if ( ! is_array ( $elements ) ) {
			$elements = array ( $elements );
			// If there's a series of elements, reverse them when in RTL mode
		}
		elseif ( $this->data[ 'rtl' ] ) {
			$elements = array_reverse ( $elements );
		}
		// Render elements
		foreach ( $elements as $name => $element ) {
			echo "\n<!-- {$name} -->\n";
			switch ( $element ) {
				case 'EDIT':
					if ( ! array_key_exists ( 'edit' , $this->data[ 'content_actions' ] ) ) {
						break;
					}
					$navTemp = $this->data[ 'content_actions' ][ 'edit' ];
					if ( $navTemp ) { ?>
						<div class = "actions pull-left nav" >
<a id = "b-edit"
   href = "<?php echo $navTemp[ 'href' ]; ?>"
   class = "btn" >
<span class = "glyphicon glyphicon-edit" ></span >
<i class = "icon-edit" ></i ><?php echo $navTemp[ 'text' ]; ?>
</a >
</div >

					<?php }
					break;
				case 'PAGE':
					$theMsg  = 'namespaces';
					$theData = array_merge ( $this->data[ 'namespace_urls' ] , $this->data[ 'view_urls' ] );
					?>
					<ul class = "nav" role = "navigation" >
<li class = "dropdown"
    id = "p-<?php echo $theMsg; ?>"
    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
	    echo ' emptyPortlet';
    } ?>" >
<?php
foreach ( $theData as $link ) {
	if ( array_key_exists ( 'context' , $link ) && $link[ 'context' ] == 'subject' ) {
		?>
		<a data-toggle = "dropdown"
		   class = "dropdown-toggle brand"
		   role = "menu" ><?php echo htmlspecialchars ( $link[ 'text' ] ); ?>
			<b class = "caret" ></b ></a >
	<?php } ?>
<?php } ?>
	<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
	    role = "menu"
	    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>

<?php
foreach ( $theData as $link ) {
	# Skip a few redundant links
	if ( preg_match ( '/^ca-(view|edit)$/' , $link[ 'id' ] ) ) {
		continue;
	}

	?>
<li<?php echo $link[ 'attributes' ] ?>>
	<a href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
	   tabindex = "-1" ><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a ></li ><?php
}

?></ul >
</li >
</ul >

					<?php

					break;
				case 'TOOLBOX':
					$theMsg  = 'toolbox';
					$theData = array_reverse ( $this->getToolbox () );
					?>

<ul class="nav" role="navigation">

<li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if ( count ( $theData ) == 0 ) {
					echo ' emptyPortlet';
				} ?>">

<a data-toggle="dropdown" class="dropdown-toggle" role="button"><?php $this->msg ( $theMsg ) ?> <b class="caret"></b></a>

<ul aria-labelledby="<?php echo $this->msg ( $theMsg ); ?>" role="menu" class="dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>

<?php
					foreach ( $theData as $key => $item ) {
						if ( preg_match ( '/specialpages|whatlinkshere/' , $key ) ) {
							echo '<li class="divider"></li>';
						}
						echo $this->makeListItem ( $key , $item );
					}
					?>

</ul>

</li>

</ul>

</ul>

<?php
					break;
				case 'VARIANTS':
					$theMsg  = 'variants';
					$theData = $this->data[ 'variant_urls' ];
					?>
					<?php if ( count ( $theData ) > 0 ) { ?>
					<ul class = "nav" role = "navigation" >
<li class = "dropdown"
    id = "p-<?php echo $theMsg; ?>"
    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
	    echo ' emptyPortlet';
    } ?>" >
<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button" ><?php $this->msg ( $theMsg ) ?>
	<b
		class = "caret" ></b ></a >
<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
    role = "menu"
    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
<?php foreach ( $theData as $link ): ?>
	<li<?php echo $link[ 'attributes' ] ?>><a
			href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
			tabindex = "-1" ><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a ></li >
<?php endforeach; ?>
</ul >
</li >
</ul >
				<?php }
					break;
				case 'VIEWS':
					$theMsg  = 'views';
					$theData = $this->data[ 'view_urls' ];
					?>
					<?php if ( count ( $theData ) > 0 ) { ?>
					<ul class = "nav" role = "navigation" >
<li class = "dropdown"
    id = "p-<?php echo $theMsg; ?>"
    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
	    echo ' emptyPortlet';
    } ?>" >
<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button" ><?php $this->msg ( $theMsg ) ?>
	<b
		class = "caret" ></b ></a >
<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
    role = "menu"
    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
<?php foreach ( $theData as $link ): ?>
	<li<?php echo $link[ 'attributes' ] ?>><a
			href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
			tabindex = "-1" ><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a ></li >
<?php endforeach; ?>
</ul >
</li >
</ul >
				<?php }
					break;
				case 'ACTIONS':
					$theMsg  = 'actions';
					$theData = array_reverse ( $this->data[ 'action_urls' ] );
					if ( count ( $theData ) > 0 ) {
						?>
						<ul class = "nav" role = "navigation" >
						<li class = "dropdown"
						    id = "p-<?php echo $theMsg; ?>"
						    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
							    echo ' emptyPortlet';
						    } ?>" >
<a data-toggle = "dropdown"
   class = "dropdown-toggle"
   role = "button" ><?php echo $this->msg ( 'actions' ); ?> <b class = "caret" ></b ></a >
<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
    role = "menu"
    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
<?php foreach ( $theData as $link ):

	if ( preg_match ( '/MovePage/' , $link[ 'href' ] ) ) {
		echo '<li class="divider"></li>';
	}

	?>

	<li<?php echo $link[ 'attributes' ] ?>><a
			href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
			tabindex = "-1" ><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a ></li >

<?php endforeach; ?>
</ul >
</li >

						</ul ><?php
					}
					break;
				case 'PERSONAL':
					$theMsg       = 'personaltools';
					$theData      = $this->getPersonalTools ();
					$theTitle     = $this->data[ 'username' ];
					$showPersonal = true;
					foreach ( $theData as $key => $item ) {
						if ( ! preg_match ( '/(notifications|login|createaccount)/' , $key ) ) {
							$showPersonal = true;
						}
					}

					?>

					<ul class = "nav pull-left" role = "navigation" >
<li class = "dropdown" id = "p-notifications" class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
	echo ' emptyPortlet';
} ?>" >
<?php if ( array_key_exists ( 'notifications' , $theData ) ) {
	echo $this->makeListItem ( 'notifications' , $theData[ 'notifications' ] );
} ?>
</li >
						<?php if ( $wgBootstrapSkinLoginLocation == 'navbar' ): ?>
							<li class = "dropdown"
							    id = "p-createaccount"
							    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
								    echo ' emptyPortlet';
							    } ?>" >
<?php if ( array_key_exists ( 'createaccount' , $theData ) ) {
	echo $this->makeListItem ( 'createaccount' , $theData[ 'createaccount' ] );
} ?>
</li >
							<li class = "dropdown"
							    id = "p-login"
							    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
								    echo ' emptyPortlet';
							    } ?>" >
<?php if ( array_key_exists ( 'login' , $theData ) ) {
	echo $this->makeListItem ( 'login' , $theData[ 'login' ] );
} ?>
</li >
						<?php endif; ?>
						<?php
						if ( $showPersonal = true ):
							?>
							<li class = "dropdown"
							    id = "p-<?php echo $theMsg; ?>"
							    class = "vectorMenu<?php if ( ! $showPersonal ) {
								    echo ' emptyPortlet';
							    } ?>" >
<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button" >
<i class = "icon-user" ></i >
	<?php echo $theTitle; ?> <b class = "caret" ></b ></a >
<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
    role = "menu"
    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
<?php foreach ( $theData as $key => $item ) {
	if ( preg_match ( '/preferences|logout/' , $key ) ) {
		echo '<li class="divider"></li>';
	}
	else if ( preg_match ( '/(notifications|login|createaccount)/' , $key ) ) {
		continue;
	}
	echo $this->makeListItem ( $key , $item );
} ?>

</ul >

</li >

						<?php endif; ?>
</ul >
					<?php
					break;
				case 'PERSONALNAV':
					?>
					<ul class = "nav" role = "navigation" >
<li class = "dropdown" class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
	echo ' emptyPortlet';
} ?>" >
<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button" >Personal <b
		class = "caret" ></b ></a >
<ul class = "dropdown-menu" >
<?php foreach ( $this->getPersonalTools () as $key => $item ) {
	echo $this->makeListItem ( $key , $item );
}?>
</ul >
</li >
</ul >

					<?php
					break;
				case 'SEARCH':
					?>
					<div class = "input-group has-light" >
<form class = "navbar-search" action = "<?php $this->text ( 'wgScript' ) ?>" id = "searchform" >
<input id = "searchInput"
       class = "form-control"
       type = "search"
       accesskey = "f"
       title = "<?php $this->text ( 'searchtitle' ); ?>"
       placeholder = "<?php $this->msg ( 'search' ); ?>"
       name = "search"
       value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>" >
<span class = "input-group-btn" >
<?php echo $this->makeSearchButton ( 'go' , array ( 'id' => 'mw-searchButton' , 'class' => 'searchButton btn btn-default' ) ); ?>
</span >
</form >
</div >

					<?php
					break;
				case 'SEARCHNAV':
					?>
					<li >
<a id = "n-Search" class = "search-link" ><i class = "icon-search" ></i >Search</a >

<form class = "navbar-search" action = "<?php $this->text ( 'wgScript' ) ?>" id = "nav-searchform" >
<input id = "searchInput"
       class = "search-query"
       type = "search"
       accesskey = "f"
       title = "<?php $this->text ( 'searchtitle' ); ?>"
       placeholder = "<?php $this->msg ( 'search' ); ?>"
       name = "search"
       value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>" >
	<?php echo $this->makeSearchButton ( 'fulltext' , array ( 'id' => 'mw-searchButton' , 'class' => 'searchButton btn hidden' ) ); ?>
</form >
</li >

					<?php
					break;
				case 'SEARCHFOOTER':
					?>
					<form class = "" action = "<?php $this->text ( 'wgScript' ) ?>" id = "footer-search" >
<i class = "icon-search" ></i ><b class = "border" ></b ><input id = "searchInput"
                                                                class = "search-query"
                                                                type = "search"
                                                                accesskey = "f"
                                                                title = "<?php $this->text ( 'searchtitle' ); ?>"
                                                                placeholder = "<?php $this->msg ( 'search' ); ?>"
                                                                name = "search"
                                                                value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>" >
						<?php echo $this->makeSearchButton ( 'fulltext' , array ( 'id' => 'mw-searchButton' ,
						                                                          'class' => 'searchButton btn hidden' ) ); ?>
</form >

					<?php
					break;
				case 'SIDEBARNAV':
					foreach ( $this->data[ 'sidebar' ] as $name => $content ) {
						if ( ! $content ) {
							continue;
						}
						if ( ! in_array ( $name , $wgBootstrapSkinSidebarItemsInNavbar ) ) {
							continue;
						}
						$msgObj = wfMessage ( $name );
						$name   = htmlspecialchars ( $msgObj->exists () ? $msgObj->text () : $name ); ?>
						<ul class = "nav" role = "navigation">
						<li class = "dropdown" id = "p-<?php echo $name; ?>" class = "vectorMenu">
						<a data-toggle = "dropdown"
						   class = "dropdown-toggle"
						   role = "menu" ><?php echo htmlspecialchars ( $name ); ?> <b class = "caret" ></b ></a >
						<ul aria-labelledby = "<?php echo htmlspecialchars ( $name ); ?>" role = "menu" class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>><?php
						# This is a rather hacky way to name the nav.
						# (There are probably bugs here...)
						foreach ( $content as $key => $val ) {
							$navClasses = '';
							if ( array_key_exists ( 'view' , $this->data[ 'content_navigation' ][ 'views' ] ) &&
							     $this->data[ 'content_navigation' ][ 'views' ][ 'view' ][ 'href' ] == $val[ 'href' ]
							) {
								$navClasses = 'active';
							} ?>

							<li class = "<?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val ); ?></li ><?php
						}
					}?>
</li>
</ul></ul><?php
					break;
				case 'SIDEBAR':
					foreach ( $this->data[ 'sidebar' ] as $name => $content ) {
						if ( ! isset( $content ) ) {
							continue;
						}
						if ( in_array ( $name , $wgBootstrapSkinSidebarItemsInNavbar ) ) {
							continue;
						}
						$msgObj = wfMessage ( $name );
						$name   = htmlspecialchars ( $msgObj->exists () ? $msgObj->text () : $name );
						if ( $wgBootstrapSkinDisplaySidebarNavigation ) { ?>
							<li class = "dropdown">
							<a data-toggle = "dropdown"
							   class = "dropdown-toggle"
							   role = "button" ><?php echo htmlspecialchars ( $name ); ?><b class = "caret" ></b ></a >
							<ul aria-labelledby = "<?php echo htmlspecialchars ( $name ); ?>" role = "menu" class = "dropdown-menu"><?php
						}
						# This is a rather hacky way to name the nav.
						# (There are probably bugs here...)
						foreach ( $content as $key => $val ) {
							$navClasses = '';
							if ( array_key_exists ( 'view' , $this->data[ 'content_navigation' ][ 'views' ] ) &&
							     $this->data[ 'content_navigation' ][ 'views' ][ 'view' ][ 'href' ] == $val[ 'href' ]
							) {
								$navClasses = 'active';
							} ?>

							<li
							class = "bold <?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val ); ?></li ><?php
						}
						if ( $wgBootstrapSkinDisplaySidebarNavigation ) { ?>                </ul>              </li><?php
						}
					}
					break;
				case 'LANGUAGES':
					$theMsg  = 'otherlanguages';
					$theData = $this->data[ 'language_urls' ]; ?>
					<ul class = "nav" role = "navigation" >
					<li class = "dropdown"
					    id = "p-<?php echo $theMsg; ?>"
					    class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
						    echo ' emptyPortlet';
					    } ?>" >
<a data-toggle = "dropdown"
   class = "dropdown-toggle brand"
   role = "menu" ><?php echo $this->html ( $theMsg ) ?> <b class = "caret" ></b ></a >
<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>"
    role = "menu"
    class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>

<?php foreach ( $content as $key => $val ) { ?>
	<li
	class = "<?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val , $options ); ?></li ><?php
}?>

</ul >
</li >
					</ul ><?php
					break;
			}
			echo "\n<!-- /{$name} -->\n";
		}
	}
}

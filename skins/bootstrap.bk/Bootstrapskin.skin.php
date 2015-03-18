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
		die( -1 );
	}

	/**
	 * SkinTemplate class for Vector skin
	 * @ingroup Skins
	 */
	class SkinBootstrap extends SkinTemplate
	{

		var $skinname = 'bootstrapskin' , $stylename = 'bootstrapskin' ,
			$template = 'StrappingTemplate' , $useHeadElement = true;

		/**
		 * Initializes output page and sets up skin-specific parameters
		 * @param $out OutputPage object to initialize
		 */
		public function initPage ( OutputPage $out )
		{
			global $wgLocalStylePath;
			parent::initPage ( $out );
			// Append CSS which includes IE only behavior fixes for hover support -
			// this is better than including this in a CSS fille since it doesn't
			// wait for the CSS file to load before fetching the HTC file.
			$min = $this->getRequest ()->getFuzzyBool ( 'debug' ) ? '' : '.min';
			$out->addHeadItem ( 'csshover' ,
								'<!--[if lt IE 7]><style type="text/css">body{behavior:url("' .
								htmlspecialchars ( $wgLocalStylePath ) .
								"/{$this->stylename}/csshover{$min}.htc\")}</style><![endif]-->"
			);
			//Replace the following with your own google analytic info
			$out->addHeadItem ( 'analytics' ,
								'<script type="text/javascript">' . "

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

  </script>"
			);
			$out->addHeadItem ( 'responsive' , '<meta name="viewport" content="width=device-width, initial-scale=1.0">' );
			$out->addModuleScripts ( 'skins.bootstrapskin' );
		}

		/**
		 * Load skin and user CSS files in the correct order
		 * fixes bug 22916
		 * @param $out OutputPage object
		 */
		function setupSkinUserCss ( OutputPage $out )
		{
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
	class StrappingTemplate extends BaseTemplate
	{

		/* Functions */
		/**
		 * Outputs the entire contents of the (X)HTML page
		 */
		public function execute ()
		{
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
				$wgSearchPlacement[ 'nav' ]    = true;
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
					$xmlID = isset( $link[ 'id' ] ) ? $link[ 'id' ] : 'ca-' . $xmlID;
					$nav[ $section ][ $key ][ 'attributes' ]
						   = ' id="' . Sanitizer::escapeId ( $xmlID ) . '"';
					if ( $link[ 'class' ] ) {
						$nav[ $section ][ $key ][ 'attributes' ]
							.= ' class="' . htmlspecialchars ( $link[ 'class' ] ) . '"';
						unset( $nav[ $section ][ $key ][ 'class' ] );
					}
					if ( isset( $link[ 'tooltiponly' ] ) && $link[ 'tooltiponly' ] ) {
						$nav[ $section ][ $key ][ 'key' ]
							= Linker::tooltip ( $xmlID );
					}
					else {
						$nav[ $section ][ $key ][ 'key' ]
							= Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( $xmlID ) );
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

			<div id = "userbar" class = "navbar">
				<div class = "navbar-inner">
					<div class = "col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<ul id = "tabs-default-lighter" class = "nav nav-tabs nav-tabs-lighter">
							<li><?php $this->renderNavigation ( array ( 'EDIT' ) ); ?></li>
							<li><?php $this->renderNavigation ( array ( 'PERSONALNAV' ) ); ?></li>
							<li><?php $this->renderNavigation ( array ( 'PAGE' ) ); ?></li>
							<li><?php $this->renderNavigation ( array ( 'ACTIONS' ) ); ?></li>
							<li><?php if ( ! isset( $portals[ 'TOOLBOX' ] )) {
									$this->renderNavigation ( array ( 'TOOLBOX' ) ); ?></li>
						</ul>
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
						<?php
							# Personal menu (at the right)
							# $this->renderNavigation( array( 'PERSONAL' ) );
						?>
					</div>
					<div class = "col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<?php if ( $wgSearchPlacement[ 'nav' ] ) {
							$this->renderNavigation ( array ( 'SEARCH' ) );
						} ?>
					</div>
				</div>
			</div>

		<?php } ?>

  <div id="mw-page-base" class="noprint"></div>
  <div id="mw-head-base" class="noprint"></div>

  <!-- Header -->
<div class="container">
<!--  <div id="page-header" class="--><?php //echo $this->data[ 'loggedin' ] ? 'signed-in' : 'signed-out';
			?><!--"></div>-->
	<div class="row">
		<div class="title-header col-lg-8 col-md-8 col-sm-12 col-xs-12 pull-left">
			<h1><a href="/" alt="トップに移動する">Media Kenja(β)</a></h1>
			<span>知のコンボを生みだそう</span>
		</div>
		<?php $mainpage_url = 'http://media.cs.inf.shizuoka.ac.jp' ?>
		<div class="title-header col-lg-4 col-md-4 col-sm-12 col-xs-12 pull-left">
			<div id="social-btns" >
			  <a href="http://www.twitter.com/share?url=<?php echo urlencode ( $mainpage_url ); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;"><button type="button" class="btn btn-cyanide btn-social"><i class="fa fa-twitter-square"></i></button></a>
			  <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode ( $mainpage_url ); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;"><button type="button" class="btn btn-info btn-social"><i class="fa fa-facebook-square"></i></button></a>
			  <a href="https://plus.google.com/share?url=<?php echo urlencode ( $mainpage_url ); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;"><button type="button" class="btn btn-berry btn-social"><i class="fa fa-google-plus-square"></i></button></a>
			  <a href="http://www.tumblr.com/share?v=3&amp;u=<?php echo urlencode ( $mainpage_url ); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;"><button type="button" class="btn btn-info btn-social"><i class="fa fa-tumblr-square"></i></button></a>
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
  <section id="content" class="mw-body container <?php echo $userStateClass; ?>">
  	<div id="top"></div>
  	<div id="mw-js-message" style="display:none;"<?php $this->html ( 'userlangattributes' ) ?>></div>
  	<?php if ( $this->data[ 'sitenotice' ] ): ?>
			<!-- sitenotice -->
			<div id = "siteNotice"><?php $this->html ( 'sitenotice' ) ?></div>
			<!-- /sitenotice -->
		<?php endif; ?>
  	<!-- bodyContent -->
  	<div id="bodyContent" class="col-lg-8 col-md-8 col-sm-12 col-xs-12 pull-left">
  		<?php if ( $this->data[ 'newtalk' ] ): ?>
			<!-- newtalk -->
			<div class = "usermessage"><?php $this->html ( 'newtalk' ) ?></div>
			<!-- /newtalk -->
		<?php endif; ?>
			<?php if ( $this->data[ 'showjumplinks' ] ): ?>
			<!-- jumpto -->
			<div id = "jump-to-nav" class = "mw-jump">
				<?php $this->msg ( 'jumpto' ) ?> <a href = "#mw-head"><?php $this->msg ( 'jumptonavigation' ) ?></a>,
				<a href = "#p-search"><?php $this->msg ( 'jumptosearch' ) ?></a>
			</div>
			<!-- /jumpto -->
		<?php endif; ?>


  		<!-- innerbodycontent -->

        <?php # Peek into the body content of articles, to see if a custom layout is used
			if ( $wgBootstrapSkinUseStandardLayout || preg_match ( "/<div.*class.*row.*>/i" , $this->data[ 'bodycontent' ] ) && $this->data[ 'articleid' ] ) {
				# If there's a custom layout, the H1 and layout is up to the page ?>
				<div id = "innerbodycontent" class = "col-lg-12 col-md-12 col-sm-12 col-xs-12 layout">
					<h2 id = "firstHeading" class = "firstHeading page-header">
						<span dir = "auto"><?php $this->html ( 'title' ) ?></span>
					</h2>
					<input type = "button" name = "makePDF" value = "PDFダウンロード" id = "makePDF">
					<script>// forked from yamineko's "jsPDFを使って、canvasをPDFに出力" http://jsdo.it/yamineko/rX5b
						$("#makePDF").click(function () {

							var filename = 'mediacard' //仮
							var uniqueUrlStr = "http://media.cs.inf.shizuoka.ac.jp"
							var categoryStr = "【" + "今とても" + "】"
							var titleStr = "七輪で焼いたサンマが食べたい"
							var dateStr = "2014-12-14"
							var authorStr = "藤森雅人"
							var dateAuthorStr = dateStr + "  " + authorStr
							var bodyStr = "MMORPGは、プレイヤーが主人公役となって冒険をする過程で仲間を増やし、ダンジョンを攻略し、最後にボスを倒すという従来のコンピュータロールプレイングゲームをオンライン上で同一ワールド（サーバー）に集まった複数のプレイヤーとともに進めていくゲームだ。仮想空間で話し、チームを組み、助け合う人々は現実に存在するプレイヤーで、それぞれ思い通りのキャラクターとして参加している。この仮想空間での自分であるキャラクターと現実の自分との間にはどのような関係があるのだろうか。\n多くの欧米プレイヤーを有する日本発の「FF XIV：新生エオルゼア」を例にとると、日本のプレイヤーは概して自分の性とは逆のキャラクターを選択し、欧米プレイヤーは自分と同じ性で、ゲーム内での戦闘中の役割を反映するようなキャラクターを好む。味方の盾となって攻撃を一身に引き受けるタンクなら、外見もいかめしい巨体の種族を選ぶといった具合だ 。ゲームプレイの鍵となる役割選択にはプレイヤーの好みや性格が反映するが、キャラクターの外見にも同じく性格が関係している。小人の種族を選ぶプレイヤーの中には、実際に小柄というより周囲を和ませたいという性格から愛嬌のあるキャラクターを選んでいる場合があるだろう。一般的に欧米のプレイヤーのキャラクターが現実の自分を起点としているのに対し、日本のプレイヤーは現実の自分とは切り離したキャラクター作成を行っていると言える。日欧米のキャラクター作成における違いの背景には、「キャラクター」という語に対する意識の違いがあるのではないか。欧米では「キャラクター」が自分を表象するものと考えられているのに対し、多くの日本人にとって「キャラクター」は自分が演じている役柄なのではないか。男装、女装を気軽に楽しむという現象もその表れなのかもしれない。"
							var bibItem = [];

							var canvas = new fabric.Canvas('c', {backgroundColor: "#ffffff"})
							//文字列を投げると、canvas上でセンタリングできるleftの値を返す
							function centerWidth(str, margin) {
								if (margin == null)  margin = 0
								var canvas = document.getElementById('c')
								if (canvas.getContext) {
									var context = canvas.getContext('2d')
									var strWidth = context.measureText(str)
									var result = canvas.width / 2 - strWidth.width / 1.5 - margin;
									return result
								}
							}

							//右寄せ版
							function rightWidth(str, margin) {
								if (margin == null)    margin = 0
								var canvas = document.getElementById('c')
								if (canvas.getContext) {
									var context = canvas.getContext('2d')
									var strWidth = context.measureText(str)
									var result = canvas.width - strWidth.width * 2 - margin;
									return result
								}
							}

							function makeText(obj) {
								if (obj.fontWeight == null)    obj.fontWeight = 'normal'
								return new fabric.Text(obj.str, {
									left: obj.left,
									right: obj.right,
									top: obj.top,
									fontFamily: 'MS 明朝',
									fontSize: obj.fontSize,
									fontWeight: obj.fontWeight,
									fontStyle: 'normal',
									textAlign: obj.textAlign,
									lineHeight: obj.lineHeight
								})
							}

							// String.prototype.byteLength = function() {
							//  var l = 0
							//  var str = escape(this.concat())
							//  var length = str.length
							//  if(str){
							//    for(var i=0; i<length; i++){
							//      if(str.charAt(i)=='%' && str.charAt(i+1) =='u'){
							//        i+=5;
							//        l++;
							//      }else if(str.charAt(i)=='%' && (str.charAt(i+1)=='2' || str.charAt(i+1) == '3' || str.charAt(i+1) == '5' || str.charAt(i+1) == '7')){
							//        // *+-./@_以外の文字
							//        i+=2;
							//      }
							//      l++;
							//    }
							//    return l;
							//  }else{
							//    return 0;
							//  }
							// }
							function insertNewLine(str, num) {
								var canvas = document.getElementById('c')
								if (canvas.getContext) {
									var context = canvas.getContext('2d')
									var result = "　";
									var p = 1
									var c = 0;
									for (var c = 2; c <= str.length; c++) {
//        console.log(p+":"+(c-p)+":"+str.substr(p,c-p))
										result += str.charAt(c - 1);
										if (str.charAt(c - 1) == "\n") {
											p = c;
											result += "　" //段落頭
										} else if (str.substr(p, c - p).length % num == 0) {
											result += "\n"
											p = c
										}
									}
									return result;
								}
							}

							var categoryText = makeText({
								str: categoryStr,
								left: centerWidth(categoryStr),
								top: 70,
								fontSize: 18,
								fontWeight: 'bold',
								textAlign: 'right',
								lineHeight: 1
							})
							var titleText = makeText({
								str: titleStr,
								left: centerWidth(titleStr),
								top: 95,
								fontSize: 16,
								textAlign: 'right',
								lineHeight: 1,
							})
							var headerBar = new fabric.Rect({
								left: 50,
								top: 115,
								fill: '#622423',
								width: 540,
								height: 8
							})
							var dateAuthorText = makeText({
								str: dateAuthorStr,
								left: rightWidth(dateAuthorStr),
								top: 130,
								fontSize: 16,
								textAlign: 'right',
								lineHeight: 1,
							});
							bodyStr = insertNewLine(bodyStr, 36)
							var bodyText = makeText({
								str: bodyStr,
								left: 55,
								right: 590,
								top: 145,
								fontSize: 16,
								textAlign: 'left',
								lineHeight: 1.50
							})

							canvas.add(categoryText, titleText);
							canvas.add(headerBar);
							canvas.add(dateAuthorText);
							canvas.add(bodyText);
							var qrcodeUrl = "http://chart.apis.google.com/chart?cht=qr&chs=100x100&choe=Shift_JIS&chl=" + uniqueUrlStr
							fabric.Image.fromURL(qrcodeUrl, function (img) {
								canvas.add(img);
								var image = canvas.toDataURL();    // firfoxならtoblobで直接blobにして保存できます。
								var mm2pt = 2.834645669291;
								var pdf = new jsPDF('p', 'mm', 'A4');
								var w = 1197 / mm2pt;
								var card = document.getElementById("c");
								var h = (w / card.width) * card.height;
								console.log(w, h)
								pdf.addImage(image, 'png', 0, 0, w / 2, h / 2);
								pdf.save('Test.pdf');
							}, {crossOrigin: 'Anonymous'});

						});</script>
					<!-- subtitle -->
					<div
						id = "contentSub" <?php $this->html ( 'userlangattributes' ) ?>><?php $this->html ( 'subtitle' ) ?></div>
					<!-- /subtitle -->
					<?php if ( $this->data[ 'undelete' ] ): ?>
						<!-- undelete -->
						<div id = "contentSub2"><?php $this->html ( 'undelete' ) ?></div>
						<!-- /undelete -->
					<?php endif; ?>
					<?php $this->html ( 'bodycontent' ); ?>
				</div>
			<?php }
			else {
				# If there's no custom layout, then we automagically add one ?>
				<div id = "innerbodycontent" class = "row nolayout">
					<div class = "offset1 span10">
						<h2 id = "firstHeading" class = "firstHeading page-header">
							<span dir = "auto"><?php $this->html ( 'title' ) ?></span>
						</h2>
						<input type = "button" name = "makePDF" value = "PDFダウンロード" id = "makePDF">
						<!-- subtitle -->
						<div
							id = "contentSub" <?php $this->html ( 'userlangattributes' ) ?>><?php $this->html ( 'subtitle' ) ?></div>
						<!-- /subtitle -->
						<?php if ( $this->data[ 'undelete' ] ): ?>
							<!-- undelete -->
							<div id = "contentSub2"><?php $this->html ( 'undelete' ) ?></div>
							<!-- /undelete -->
						<?php endif; ?>
						<?php $this->html ( 'bodycontent' ); ?>
						<?php $current_url = ( empty( $_SERVER[ "HTTPS" ] ) ? "http://" : "https://" ) . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ]; ?>
						<div id = "social-btns" class = "buttons-whitespace-social text-center pull-left">
							<a href = "http://www.twitter.com/share?url=<?php echo urlencode ( $current_url ); ?>"
							   onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
								<button type = "button" class = "btn btn-cyanide btn-social"><i
										class = "fa fa-twitter-square"></i></button>
							</a>

							<a href = "https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode ( $current_url ); ?>"
							   onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
								<button type = "button" class = "btn btn-info btn-social"><i
										class = "fa fa-facebook-square"></i>
								</button>
							</a>

							<a href = "https://plus.google.com/share?url=<?php echo urlencode ( $current_url ); ?>"
							   onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
								<button type = "button" class = "btn btn-berry btn-social"><i
										class = "fa fa-google-plus-square"></i></button>
							</a>

							<!--<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=http://www.mediawikibootstrapskin.co.uk/&amp;title=Mediawiki%20BootStrap%20|Skin&amp;summary=Mediawiki%20BootStrap%20Skin&amp;source=http://www.mediawikibootstrapskin.co.uk/" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;"><button type="button" class="btn btn-cyanide btn-social"><i class="fa fa-linkedin-square"></i></button></a>-->


							<a href = "http://www.tumblr.com/share?v=3&amp;u=<?php echo urlencode ( $current_url ); ?>"
							   onclick = "javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=600');return false;">
								<button type = "button" class = "btn btn-info btn-social"><i
										class = "fa fa-tumblr-square"></i>
								</button>
							</a>
							このページについて感想や意見を書いてシェアしよう
						</div>
					</div>
				</div>
			<?php } ?>

          <!-- /innerbodycontent -->

          <?php if ( $this->data[ 'printfooter' ] ): ?>
			<!-- printfooter -->
			<div class = "printfooter">
				<?php $this->html ( 'printfooter' ); ?>
			</div>
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
        <!-- navigation -->

        <div id="tagcat" class="col-lg-4 col-md-4 col-sm-12 col-xs-12 pull-right">

        	<ul class="navgation nav nav-pills">
        		<h3>Category on This Page</h3>
        		<?php if ( ! $this->data[ 'catlinks' ] ): ?>
			<?php echo '<p>このページにはまだカテゴリがありません! <br/>ページを直接編集して本文に[[Category:好きな名前]]を追加するか、専用の機能を使ってください！</p>' ?>
		<?php endif; ?>

        		<!-- catlinks -->
        		<?php $this->html ( 'catlinks' ); ?>
        		<!-- /catlinks -->

        		<script type="text/javascript">
        			
        			function moveTaggerBox(){
        				
        				if (document.getElementById('catlinks')){
                // if the Article is already in one or more categories,
                // 'catlinks' will exist
                document.getElementById('catlinks').appendChild(document.getElementById('tagform'));
              }
              else
              {
                // if 'catlinks' doesn't exist (the article is in 0 categories)
                // we must recreate the HTML
                var div1=document.createElement('div');
                div1.setAttribute('id','catlinks');
                var p1=document.createElement('p');
                div1.appendChild(p1);
                var a1=document.createElement('a');
                a1.setAttribute('href',escape('{$sCategoriesLink}'));
                a1.setAttribute('title','{$sCategoriesLink}');
                p1.appendChild(a1);
                var txt1=document.createTextNode('{$sTags}');
                a1.appendChild(txt1);
                var txt2=document.createTextNode(': {$sNoTags}');
                p1.appendChild(txt2);
                
                document.getElementById('bodyContent').appendChild(div1);
                div1.appendChild(document.getElementById('tagform'));
              }
              
//            document.getElementById('tagform').style.display = 'table'; // we defined it as hidden. show it once it's placed correctly
}
$(document).ready(moveTaggerBox);

</script>

<?php global $wgScript;
			global $wgTitle;
			$actionUrl = htmlspecialchars ( $wgScript ); ?>
<form action="<?php echo $actionUrl ?>" method="get" id="tagform">
	<input type="hidden" name="title" value="<?php echo $wgTitle->mTextform; ?>">
	<input type="hidden" name="action" value="tag">
	<input type="text" name="tag" value="" size="15" />    
	<input type="submit" value="タグを追加" />
</form>

</ul>

</div>

<div id="sidebar" class="col-lg-4 col-md-4 col-sm-12 col-xs-12 pull-right">
	<h3>Menu & All Categories</h3>
	<ul class="navigation nav nav-pills pull-right searchform-disabled">
		<?php
			$this->renderNavigation ( array ( 'SIDEBAR' ) );
			// if($wgSearchPlacement['nav']) {
			// 	$this->renderNavigation( array( 'SEARCHNAV' ) );
			// }
			?>
	</ul>
	
</div>

</section>
<!-- /content -->

<!-- footer -->

<?php
			/* Support a custom footer, or use MediaWiki's default, if footer.php does not exist. */
			$footerfile = dirname ( __FILE__ ) . '/footer.php';
			if ( file_exists ( $footerfile ) ):
				?>
				<div id = "footer" class = "footer container custom-footer"><?php
				include ( $footerfile );
				?></div><?php
			else:
				?>

				<div id = "footer" class = "footer container"<?php $this->html ( 'userlangattributes' ) ?>>
					<div class = "row">
						<?php
							$footerLinks = $this->getFooterLinks ();

							if ( is_array ( $footerLinks ) ) {
								foreach ( $footerLinks as $category => $links ):
									if ( $category === 'info' ) {
										continue;
									} ?>

									<ul id = "footer-<?php echo $category ?>">
										<?php foreach ( $links as $link ): ?>
											<li id = "footer-<?php echo $category ?>-<?php echo $link ?>"><?php $this->html ( $link ) ?></li>
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
													<li id = "pt-login"><a
														href = "<?php echo $personalTemp[ $loginType ][ 'links' ][ 0 ][ 'href' ] ?>"><?php echo $personalTemp[ $loginType ][ 'links' ][ 0 ][ 'text' ]; ?></a>
													</li><?php
												}
												# Show the search in footer to all
												if ( $wgSearchPlacement[ 'footer' ] ) {
													echo '<li>';
													$this->renderNavigation ( array ( 'SEARCHFOOTER' ) );
													echo '</li>';
												}
											}
										?>
									</ul>
								<?php
								endforeach;
							}
						?>
						<?php $footericons = $this->getFooterIcons ( "icononly" );
							if ( count ( $footericons ) > 0 ): ?>
								<ul id = "footer-icons" class = "noprint">
									<?php foreach ( $footericons as $blockName => $footerIcons ): ?>
										<li id = "footer-<?php echo htmlspecialchars ( $blockName ); ?>ico">
											<?php foreach ( $footerIcons as $icon ): ?>
												<?php echo $this->getSkin ()->makeFooterIcon ( $icon ); ?>

											<?php endforeach; ?>
										</li>

									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
					</div>
				</div>
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
		private function renderLogo ()
		{
			$mainPageLink = $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ];
			$toolTip      = Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( 'p-logo' ) );
			?>
			<ul class = "nav logo-container" role = "navigation">
				<li id = "p-logo"><a
						href = "<?php echo htmlspecialchars ( $this->data[ 'nav_urls' ][ 'mainpage' ][ 'href' ] ) ?>" <?php echo Xml::expandAttributes ( Linker::tooltipAndAccesskeyAttribs ( 'p-logo' ) ) ?>><img
							src = "<?php $this->text ( 'logopath' ); ?>" alt = "<?php $this->html ( 'sitename' ); ?>"
							style = "width:90%">
					</a>
				<li>
			</ul>

		<?php
		}

		/**
		 * Render one or more navigations elements by name, automatically reveresed
		 * when UI is in RTL mode
		 *
		 * @param $elements array
		 */
		private function renderNavigation ( $elements )
		{
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
							<div class = "actions pull-left nav">
								<a id = "b-edit" href = "<?php echo $navTemp[ 'href' ]; ?>" class = "btn"><i
										class = "icon-edit"></i> <?php echo $navTemp[ 'text' ]; ?></a>
							</div>

						<?php }
						break;
					case 'PAGE':
						$theMsg  = 'namespaces';
						$theData = array_merge ( $this->data[ 'namespace_urls' ] , $this->data[ 'view_urls' ] );
						?>
						<ul class = "nav" role = "navigation">
							<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
								class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
									echo ' emptyPortlet';
								} ?>">
								<?php
									foreach ( $theData as $link ) {
										if ( array_key_exists ( 'context' , $link ) && $link[ 'context' ] == 'subject' ) {
											?>
											<a data-toggle = "dropdown" class = "dropdown-toggle brand"
											   role = "menu"><?php echo htmlspecialchars ( $link[ 'text' ] ); ?> <b
													class = "caret"></b></a>
										<?php } ?>
									<?php } ?>
								<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
									class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>

									<?php
										foreach ( $theData as $link ) {
											# Skip a few redundant links
											if ( preg_match ( '/^ca-(view|edit)$/' , $link[ 'id' ] ) ) {
												continue;
											}

											?>
										<li<?php echo $link[ 'attributes' ] ?>><a
												href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
												tabindex = "-1"><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a>
											</li><?php
										}

									?></ul>
							</li>
						</ul>

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
						<ul class = "nav" role = "navigation">
							<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
								class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
									echo ' emptyPortlet';
								} ?>">
								<a data-toggle = "dropdown" class = "dropdown-toggle"
								   role = "button"><?php $this->msg ( $theMsg ) ?>
									<b class = "caret"></b></a>
								<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
									class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
									<?php foreach ( $theData as $link ): ?>
										<li<?php echo $link[ 'attributes' ] ?>><a
												href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
												tabindex = "-1"><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						</ul>
					<?php }
						break;
					case 'VIEWS':
						$theMsg  = 'views';
						$theData = $this->data[ 'view_urls' ];
						?>
						<?php if ( count ( $theData ) > 0 ) { ?>
						<ul class = "nav" role = "navigation">
							<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
								class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
									echo ' emptyPortlet';
								} ?>">
								<a data-toggle = "dropdown" class = "dropdown-toggle"
								   role = "button"><?php $this->msg ( $theMsg ) ?>
									<b class = "caret"></b></a>
								<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
									class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
									<?php foreach ( $theData as $link ): ?>
										<li<?php echo $link[ 'attributes' ] ?>><a
												href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
												tabindex = "-1"><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						</ul>
					<?php }
						break;
					case 'ACTIONS':
						$theMsg  = 'actions';
						$theData = array_reverse ( $this->data[ 'action_urls' ] );
						if ( count ( $theData ) > 0 ) {
							?>
							<ul class = "nav" role = "navigation">
							<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
								class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
									echo ' emptyPortlet';
								} ?>">
								<a data-toggle = "dropdown" class = "dropdown-toggle"
								   role = "button"><?php echo $this->msg ( 'actions' ); ?> <b class = "caret"></b></a>
								<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
									class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>
									<?php foreach ( $theData as $link ):

										if ( preg_match ( '/MovePage/' , $link[ 'href' ] ) ) {
											echo '<li class="divider"></li>';
										}

										?>

										<li<?php echo $link[ 'attributes' ] ?>><a
												href = "<?php echo htmlspecialchars ( $link[ 'href' ] ) ?>" <?php echo $link[ 'key' ] ?>
												tabindex = "-1"><?php echo htmlspecialchars ( $link[ 'text' ] ) ?></a>
										</li>

									<?php endforeach; ?>
								</ul>
							</li>

							</ul><?php
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

						<ul class = "nav pull-left" role = "navigation">
							<li class = "dropdown" id = "p-notifications"
								class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
									echo ' emptyPortlet';
								} ?>">
								<?php if ( array_key_exists ( 'notifications' , $theData ) ) {
									echo $this->makeListItem ( 'notifications' , $theData[ 'notifications' ] );
								} ?>
							</li>
							<?php if ( $wgBootstrapSkinLoginLocation == 'navbar' ): ?>
								<li class = "dropdown" id = "p-createaccount"
									class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
										echo ' emptyPortlet';
									} ?>">
									<?php if ( array_key_exists ( 'createaccount' , $theData ) ) {
										echo $this->makeListItem ( 'createaccount' , $theData[ 'createaccount' ] );
									} ?>
								</li>
								<li class = "dropdown" id = "p-login"
									class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
										echo ' emptyPortlet';
									} ?>">
									<?php if ( array_key_exists ( 'login' , $theData ) ) {
										echo $this->makeListItem ( 'login' , $theData[ 'login' ] );
									} ?>
								</li>
							<?php endif; ?>
							<?php
								if ( $showPersonal = true ):
									?>
									<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
										class = "vectorMenu<?php if ( ! $showPersonal ) {
											echo ' emptyPortlet';
										} ?>">
										<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button">
											<i class = "icon-user"></i>
											<?php echo $theTitle; ?> <b class = "caret"></b></a>
										<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
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

										</ul>

									</li>

								<?php endif; ?>
						</ul>
						<?php
						break;
					case 'PERSONALNAV':
						?>
						<ul class = "nav" role = "navigation">
							<li class = "dropdown"
								class = "vectorMenu<?php //if ( count($theData) == 0 ) echo ' emptyPortlet';
								?>">
								<a data-toggle = "dropdown" class = "dropdown-toggle" role = "button">アカウント <b
										class = "caret"></b></a>
								<ul class = "dropdown-menu">
									<?php foreach ( $this->getPersonalTools () as $key => $item ) {
										echo $this->makeListItem ( $key , $item );
									}?>
								</ul>
							</li>
						</ul>

						<?php
						break;
					case 'SEARCH':
						?>
						<div id = "navbar-searchform" class = "input-inline input-group has-light">
							<form class = "navbar-search"
								  action = "<?php $this->text ( 'wgScript' ) ?>"
								  id = "searchform">
								<input id = "searchInput" class = "form-control" type = "search" accesskey = "f"
									   title = "<?php $this->text ( 'searchtitle' ); ?>"
									   placeholder = "<?php $this->msg ( 'search' ); ?>" name = "search"
									   value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>">
  							<span class = "input-group-btn">
  								<?php echo $this->makeSearchButton ( 'go' , array ( 'id' => 'mw-searchButton' , 'class' => 'searchButton btn btn-default' ) ); ?>
  							</span>
							</form>
						</div>

						<?php
						break;
					case 'SEARCHNAV':
						?>
						<li>
							<a id = "n-Search" class = "search-link"><i class = "icon-search"></i>Search</a>

							<form class = "navbar-search" action = "<?php $this->text ( 'wgScript' ) ?>"
								  id = "nav-searchform">
								<input id = "searchInput" class = "search-query" type = "search" accesskey = "f"
									   title = "<?php $this->text ( 'searchtitle' ); ?>"
									   placeholder = "<?php $this->msg ( 'search' ); ?>" name = "search"
									   value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>">
								<?php echo $this->makeSearchButton ( 'fulltext' , array ( 'id' => 'mw-searchButton' , 'class' => 'searchButton btn hidden' ) ); ?>
							</form>
						</li>

						<?php
						break;
					case 'SEARCHFOOTER':
						?>
						<form class = "" action = "<?php $this->text ( 'wgScript' ) ?>" id = "footer-search">
							<i class = "icon-search"></i><b class = "border"></b><input id = "searchInput"
																						class = "search-query"
																						type = "search" accesskey = "f"
																						title = "<?php $this->text ( 'searchtitle' ); ?>"
																						placeholder = "<?php $this->msg ( 'search' ); ?>"
																						name = "search"
																						value = "<?php echo htmlspecialchars ( $this->data[ 'search' ] ); ?>">
							<?php echo $this->makeSearchButton ( 'fulltext' , array ( 'id' => 'mw-searchButton' , 'class' => 'searchButton btn hidden' ) ); ?>
						</form>

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
							<a data-toggle = "dropdown" class = "dropdown-toggle"
							   role = "menu"><?php echo htmlspecialchars ( $name ); ?> <b class = "caret"></b></a>
							<ul aria-labelledby = "<?php echo htmlspecialchars ( $name ); ?>" role = "menu" class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>><?php
							# This is a rather hacky way to name the nav.
							# (There are probably bugs here...)
							foreach ( $content as $key => $val ) {
								$navClasses = '';
								if ( array_key_exists ( 'view' , $this->data[ 'content_navigation' ][ 'views' ] ) && $this->data[ 'content_navigation' ][ 'views' ][ 'view' ][ 'href' ] == $val[ 'href' ] ) {
									$navClasses = 'active';
								} ?>

								<li
								class = "<?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val ); ?></li><?php
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
								<a data-toggle = "dropdown" class = "dropdown-toggle"
								   role = "button"><?php echo htmlspecialchars ( $name ); ?><b class = "caret"></b></a>
								<ul aria-labelledby = "<?php echo htmlspecialchars ( $name ); ?>" role = "menu" class = "dropdown-menu"><?php
							}
							# This is a rather hacky way to name the nav.
							# (There are probably bugs here...)
							foreach ( $content as $key => $val ) {
								$navClasses = '';
								if ( array_key_exists ( 'view' , $this->data[ 'content_navigation' ][ 'views' ] ) && $this->data[ 'content_navigation' ][ 'views' ][ 'view' ][ 'href' ] == $val[ 'href' ] ) {
									$navClasses = 'active';
								} ?>

								<li
								class = "center-block <?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val ); ?></li><?php
							}
							if ( $wgBootstrapSkinDisplaySidebarNavigation ) { ?>                </ul>              </li><?php
							}
						}
						break;
					case 'LANGUAGES':
						$theMsg  = 'otherlanguages';
						$theData = $this->data[ 'language_urls' ]; ?>
						<ul class = "nav" role = "navigation">
						<li class = "dropdown" id = "p-<?php echo $theMsg; ?>"
							class = "vectorMenu<?php if ( count ( $theData ) == 0 ) {
						echo ' emptyPortlet';
					} ?>">
							<a data-toggle = "dropdown" class = "dropdown-toggle brand"
							   role = "menu"><?php echo $this->html ( $theMsg ) ?> <b class = "caret"></b></a>
							<ul aria-labelledby = "<?php echo $this->msg ( $theMsg ); ?>" role = "menu"
								class = "dropdown-menu" <?php $this->html ( 'userlangattributes' ) ?>>

								<?php foreach ( $content as $key => $val ) { ?>
						<li
						class = "<?php echo $navClasses ?>"><?php echo $this->makeLink ( $key , $val , $options ); ?></li><?php
					}?>

							</ul>
						</li>
						</ul><?php
						break;
				}
				echo "\n<!-- /{$name} -->\n";
			}
		}
	}

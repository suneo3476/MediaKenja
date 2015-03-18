	
	<div id="footer" class="footer container-fluid"<?php $this->html( 'userlangattributes' ) ?>>
    <div class="row">
	<?php
      $footerLinks = $this->getFooterLinks();

      if (is_array($footerLinks)) {
        foreach($footerLinks as $category => $links ):
          if ($category === 'info') { continue; } ?>

            <ul id="footer-<?php echo $category ?>">
              <?php foreach( $links as $link ): ?>
                <li id="footer-<?php echo $category ?>-<?php echo $link ?>"><?php $this->html( $link ) ?></li>
              <?php endforeach; ?>
              <?php
                if ($category === 'places') {

                  # Show sign in link, if not signed in
                  if ($wgBootstrapSkinLoginLocation == 'footer' && !$this->data['loggedin']) {
                    $personalTemp = $this->getPersonalTools();

                    if (isset($personalTemp['login'])) {
                      $loginType = 'login';
                    } else {
                      $loginType = 'anonlogin';
                    }

                    ?><li id="pt-login"><a href="<?php echo $personalTemp[$loginType]['links'][0]['href'] ?>"><?php echo $personalTemp[$loginType]['links'][0]['text']; ?></a></li><?php
                  }

                  # Show the search in footer to all
                  if ($wgSearchPlacement['footer']) {
                    echo '<li>';
                    $this->renderNavigation( array( 'SEARCHFOOTER' ) ); 
                    echo '</li>';
                  }
                }
              ?>
            </ul>
          <?php 
              endforeach; 
            }
          ?>
	</div>
	</div>	
	
    <footer>
      <ul id="footer-icons" class="noprint text-center">
        <li id="footer-poweredbyico">
        <a href="//www.mediawiki.org/">
          <img src="http://www.mediawikibootstrapskin.co.uk//skins/common/images/poweredby_mediawiki_88x31.png"
          alt="Powered by MediaWiki" height="31" width="88" />
        </a> 
        <a href="http://www.mediawikibootstrapskin.co.uk/">
          <img src="http://www.mediawikibootstrapskin.co.uk/images/BootStrapSkin_mediawiki_88x31.png"
          alt="Powered by BootStrapSkin" height="31" width="88" />
        </a>
      </ul>
      <p class="text-center no-margins push-up">
      <a href="#" class="color-hover-white">Media Studies Reader</a> &copy; All Rights Reserved</p>
    </footer>

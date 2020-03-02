<?php
	include_once('WkFbBox.php');
	$wkfbFunctions = new WkFbBox($this->cf['locale']);

	$url            = apply_filters( 'aspexifblikebox_url', $this->cf['url'] );
	$status         = apply_filters( 'aspexifblikebox_status', $this->cf['status'] );

	if( ( !strlen( $url ) || 'enabled' != $status ) && !$preview )
		return;

	$locale         = apply_filters( 'aspexifblikebox_locale', $this->cf['locale'] );
	$height         = apply_filters( 'aspexifblikebox_height', $this->cf['height'] );
	$width          = apply_filters( 'aspexifblikebox_width', $this->cf['width'] );
	$adaptative     = apply_filters( 'aspexifblikebox_adaptative', $this->cf['adaptative'] );
	$friendsFaces   = apply_filters( 'aspexifblikebox_friendsFaces', $this->cf['friendsFaces'] );
	$showPosts      = apply_filters( 'aspexifblikebox_showPosts', $this->cf['showPosts'] );
	$hideCta        = apply_filters( 'aspexifblikebox_hideCta', $this->cf['hideCta'] );
	$hideCover      = apply_filters( 'aspexifblikebox_hideCover', $this->cf['hideCover'] );
	$smallHeader    = apply_filters( 'aspexifblikebox_smallHeader', $this->cf['smallHeader'] );
	$timeLine       = apply_filters( 'aspexifblikebox_timeLine', $this->cf['timeLine'] );
	$messages       = apply_filters( 'aspexifblikebox_messages', $this->cf['messages'] );
	$events         = apply_filters( 'aspexifblikebox_smallHeader', $this->cf['events'] );
	$btimage        = apply_filters( 'aspexifblikebox_fbIcon', $this->cf['fbIcon'] );
	$edgeSpace 			= apply_filters( 'aspexifblikebox_edgeSpace', $this->cf['edgeSpace'] );
	$iconVertical		= apply_filters( 'aspexifblikebox_iconVertical', $this->cf['iconVertical'] );
	$placement			= 'right';
	$bordercolor    = '#3B5998';
	$borderwidth    = 2;
	$bgcolor        = '#ffffff';

	$buttonHeight = stripos($btimage, 'fb2') === 0 ? 48 : 155; 

	$cssPlacement = [];
	if( 'left' == $placement ) {
			$cssPlacement[0] = 'right';
			$cssPlacement[1] = '0 '.(48+$edgeSpace).'px 0 5px';
	} else {
			$cssPlacement[0] = 'left';
			$cssPlacement[1] = '0 0 0 '.(48+(int)$edgeSpace).'px';
	}

	$cssPlacement[2] = '50%;margin-top:-'.floor($height/2).'px';

	$smallscreenscss = '';
	if( $width > 0 ) {
			$widthmax = (int)($width + 48 + $borderwidth + 10);
			$smallscreenscss = '@media (max-width: '.$widthmax.'px) { .aspexifblikebox { display: none; } }';
	}

	$stream     = 'false';
	$header     = 'false';


	if( file_exists( plugin_dir_path( __FILE__ ).'images/'.$btimage ) ) $button_uri = WKFBOX_URL.'images/'.$btimage;
	else if( '' == $button_uri ) {
		$button_uri = WKFBOX_URL.'images/fb1-right.png' ;
	}

	$output = '';

	$page_url = 'https://www.facebook.com/'.$url;

	$output .= '<div class="fb-root"></div>
	<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/'.$locale.'/sdk.js#xfbml=1&version=v3.3&appId=339779149790099";
			fjs.parentNode.insertBefore(js, fjs);
	}(document, \'script\', \'facebook-jssdk\'));</script>
	<style type="text/css">' . $smallscreenscss.' .fb-xfbml-parse-ignore {
					display: none;
			}
			
			.aspexifblikebox {
					overflow: hidden;
					z-index: 99999999;
					position: fixed;
					padding: '.$cssPlacement[1].';
					top: ' . $cssPlacement[2] . ';
					right: -' . ($width) . 'px;
			}
			
			.aspexifblikebox .aspexi_facebook_iframe {
					padding: 0;
					border: ' . $borderwidth . 'px solid ' . $bordercolor . ';
					background: #fff;
					width: ' . $width . 'px;
					height: ' . $height . 'px;
					box-sizing: border-box;
			}
			
			.aspexifblikebox .fb-page {
					background: url("' . WKFBOX_URL . 'images/load.gif") no-repeat center center;
					width: ' . ($width - ($borderwidth * 2)). 'px;
					height: ' . ($height - ($borderwidth * 2)). 'px;
					margin: 0;
			}
			
			.aspexifblikebox .fb-page span {
					background: #fff;
					height: 100% !important;
			}
			
			.aspexifblikebox .aspexi_facebook_button {
					background: url("' . $button_uri . '") no-repeat scroll transparent;
					height: '.$buttonHeight.'px;
					width: 48px;
					position: absolute;
					'.$wkfbFunctions->setIconVertical($iconVertical, $this->cf['iconVerticalConst']).'
					left: 0;
					cursor: pointer;
			}
	</style>
	<div class="aspexifblikebox">
			<div class="aspexi_facebook_button"></div>
			<div class="aspexi_facebook_iframe">
					<div 
						class="fb-page" 
						data-href="'.$page_url.'" 
						data-hide-cta="'.$hideCta.'"
						data-width="'.($width - 4).'" 
						data-height="'.($height - 4).'" 
						data-hide-cover="'.$hideCover.'"
						data-show-posts="'.$showPosts.'" 
						data-show-facepile="'.$friendsFaces.'" 
						data-adapt-container-width="'.$adaptative.'"
						data-small-header="'.$smallHeader.'"
						data-tabs="'.$timeLine.$messages.$events.'"
					>
					<div class="fb-xfbml-parse-ignore"><blockquote cite="'.$page_url.'"><a href="'.$page_url.'">Facebook</a></blockquote></div></div>
			</div>
	</div>';

	$output = apply_filters( 'aspexifblikebox_output', $output );

	echo $output;
?>
<?php
	function wkfbSetIconVertical(String $iconVertical = '', Int $distance = 0)
	{
		if ($iconVertical == "middle") $iconVertical = "top: 50%; transform: translateY(-50%);";
		if ($iconVertical == "fixed"){
			$iconVerticalConst = apply_filters( 'aspexifblikebox_iconVerticalConst', $distance );
			$iconVertical = "top: {$iconVerticalConst}px;";
		}
		else $iconVertical = "{$iconVertical}: 0;";
		return $iconVertical;
	}

	/**
	* Webkor Facebook Block Functions.
	* Author: Adrian Korzan <adrian.korzan@gmail.com>
	*/
	class WkfbBox
	{

		public $cf = [];

		private $cssPlacement = [];

		private $placement = 'right';

		private $stream = 'false';

		private $header = 'false';

		private $buttonUri = '';

		public $output = '';

		/**
		* Constructor of class.
		*/
		public function __construct($config) {
			$this->cf = $config;
		}

		/**
		 * Box Templpate
		 */
		public function frontView(): Void
		{
			$page_url = 'https://www.facebook.com/'.$this->cf['url'];
			$this->output .= '<div class="fb-root"></div>
			<script>(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/'.$this->cf['locale'].'/sdk.js#xfbml=1&version=v3.3&appId=339779149790099";
					fjs.parentNode.insertBefore(js, fjs);
			}(document, \'script\', \'facebook-jssdk\'));</script>
			<style type="text/css">' . $this->smallScreensStyle().' .fb-xfbml-parse-ignore {
							display: none;
					}
					
					.aspexifblikebox {
							overflow: hidden;
							z-index: 99999999;
							position: fixed;
							padding: '.$this->cssPlacement[1].';
							top: ' . $this->cssPlacement[2] . ';
							right: -' . ($this->cf['width']) . 'px;
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
		}
			
		/**
		* Set vertical icon position
		* @param String $iconVertical
		* @param Int $distance
		* @return String
		*/
		private function setIconVertical(String $iconVertical = '', Int $distance = 0): String
		{
			if ($iconVertical == "middle") $iconVertical = "top: 50%; transform: translateY(-50%);";
			if ($iconVertical == "fixed"){
				$iconVerticalConst = apply_filters( 'aspexifblikebox_iconVerticalConst', $distance );
				$iconVertical = "top: {$iconVerticalConst}px;";
			}
			else $iconVertical = "{$iconVertical}: 0;";
			return $iconVertical;
		}

		/**
		* Set style for small devices
		* @return String
		*/
		public function smallScreensStyle(): String
		{
			if( $this->cf['width'] > 0 ) {
				$this->cf['borderWidth'] = 2; //temp
				$widthmax = (int)($this->cf['width'] + 48 + $this->cf['borderWidth'] + 10);
				return '@media (max-width: '.$widthmax.'px) { .aspexifblikebox { display: none; } }';
		}
		}

		/**
	 	* set placement of box. 
		*/
		public function setCssPlacement(): void
		{
			if( 'left' == $this->placement ) {
					$this->cssPlacement[0] = 'right';
					$this->cssPlacement[1] = '0 '.(48+$this->cf['edgeSpace']).'px 0 5px';
			} else {
					$this->cssPlacement[0] = 'left';
					$this->cssPlacement[1] = '0 0 0 '.(48+(int)$this->cf['edgeSpace']).'px';
			}
		
			$this->cssPlacement[2] = '50%;margin-top:-'.floor($this->cf['height']/2).'px';
		}

		/**
		* Set Button Height
		*/
		private function setButtonHeight(): Int
		{
			return stripos($this->cf['fbIcon'], 'fb2') === 0 ? 48 : 155;
		}

		/**
		* Set Button Uri
		*/
		private function setButtonUri(): void
		{
			if( file_exists( plugin_dir_path( __FILE__ ).'images/'.$this->cf['fbIcon'] ) ) $this->buttonUri = WKFBOX_URL.'images/'.$this->cf['fbIcon'];
			else if( '' == $this->buttonUri ) $this->buttonUri = WKFBOX_URL.'images/fb1-right.png' ;
		}

		
	}
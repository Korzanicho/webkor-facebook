<?php
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
			$this->setCssPlacement();
			$this->setButtonUri();
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
							border: ' . $this->cf['borderwidth'] . 'px solid ' . $this->cf['bordercolor'] . ';
							background: #fff;
							width: ' . $this->cf['width'] . 'px;
							height: ' . $this->cf['height'] . 'px;
							box-sizing: border-box;
					}
					
					.aspexifblikebox .fb-page {
							background: url("' . WKFBOX_URL . 'images/load.gif") no-repeat center center;
							width: ' . ($this->cf['width'] - ($this->cf['borderwidth'] * 2)). 'px;
							height: ' . ($this->cf['height'] - ($this->cf['borderwidth'] * 2)). 'px;
							margin: 0;
					}
					
					.aspexifblikebox .fb-page span {
							background: #fff;
							height: 100% !important;
					}
					
					.aspexifblikebox .aspexi_facebook_button {
							background: url("' . $this->buttonUri . '") no-repeat scroll transparent;
							height: '.$this->setButtonHeight().'px;
							width: 48px;
							position: absolute;
							'.$this->setIconVertical($this->cf['iconVertical'], $this->cf['iconVerticalConst']).'
							left: 0;
							cursor: pointer;
					}
			</style>
			<div class="aspexifblikebox">
					<div class="aspexi_facebook_button"></div>
					<div class="aspexi_facebook_iframe">
							<div 
								class="fb-page" 
								data-href="'.$this->setPageUrl().'" 
								data-hide-cta="'.$this->cf['hideCta'].'"
								data-width="'.($this->cf['width'] - 4).'" 
								data-height="'.($this->cf['height'] - 4).'" 
								data-hide-cover="'.$this->cf['hideCover'].'"
								data-show-posts="'.$this->cf['showPosts'].'" 
								data-show-facepile="'.$this->cf['friendsFaces'].'" 
								data-adapt-container-width="'.$this->cf['adaptative'].'"
								data-small-header="'.$this->cf['smallHeader'].'"
								data-tabs="'.$this->cf['timeLine'].$this->cf['messages'].$this->cf['events'].'"
							>
							<div class="fb-xfbml-parse-ignore"><blockquote cite="'.$this->setPageUrl().'"><a href="'.$this->setPageUrl().'">Facebook</a></blockquote></div></div>
					</div>
			</div>';

			echo $this->output;
		}
			
		/**
		* Set vertical icon position
		* @param String $iconVertical
		* @param Int $distance
		* @return String
		*/
		public function setIconVertical(String $iconVertical = '', Int $distance = 0): String
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

		public function setPageUrl()
		{
			return 'https://www.facebook.com/'.$this->cf['url'];
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
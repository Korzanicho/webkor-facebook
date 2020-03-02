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

		public function smallScreensesStyle()
		{
			if( $this->cf['width'] > 0 ) {
				$this->cf['borderWidth'] = 2; //temp
				$widthmax = (int)($this->cf['width'] + 48 + $this->cf['borderWidth'] + 10);
				return '@media (max-width: '.$widthmax.'px) { .aspexifblikebox { display: none; } }';
		}
		}

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
		private function setButtonUri()
		{
			if( file_exists( plugin_dir_path( __FILE__ ).'images/'.$this->cf['fbIcon'] ) ) $this->buttonUri = WKFBOX_URL.'images/'.$this->cf['fbIcon'];
			else if( '' == $this->buttonUri ) {
				$this->buttonUri = WKFBOX_URL.'images/fb1-right.png' ;
			}
		}

		
	}
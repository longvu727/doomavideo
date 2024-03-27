<?php

// {{{ Thaana_Conversions

/**
 * Functions for converting text to and from the common Thaana presentation
 * formats of Unicode and Ascii.
 *
 * This class currently provides two functions:
 * convertAsciiToUnicode($text) : Converts Thaana text in Ascii to Unicode
 * convertUnicodeToAscii($text) : Converts Thaana text in Unicode to Ascii
 *
 * Usage:
 * $thaana = new Thaana_Conversions();
 * echo $thaana->convertUnicodeToAscii('&#1931;&#1960;&#1928;&#1964;&#1920;&#1960;');
 * echo $thaana->convertAsciiToUnicode('rWacje');
 *
 *
 * @author     Jaa <jaa@jawish.org>
 * @copyright  2007 Jawish Hameed
 * @license    LGPL
 * @version    0.1
 * @link       http://www.jawish.org/
 */
class Thaana_Conversions {
	
	// {{{ properties
	
	/**
	 * Mapping for Ascii to Unicode
	 *
	 * @var array
	 * @access private
	 */
	private $mapAsciiToUnicode = array(	'h' => '1920', 'S' => '1921', 'n' => '1922', 'r' => '1923',
										'b' => '1924', 'L' => '1925', 'k' => '1926', 'a' => '1927', 
										'v' => '1928', 'm' => '1929', 'f' => '1930', 'd' => '1931', 
										't' => '1932', 'l' => '1933', 'g' => '1934', 'N' => '1935', 
										's' => '1936', 'D' => '1937', 'z' => '1938', 'T' => '1939', 
										'y' => '1940', 'p' => '1941', 'j' => '1942', 'C' => '1943', 
										'X' => '1944', 'H' => '1945', 'K' => '1946', 'J' => '1947', 
										'R' => '1948', 'x' => '1949', 'B' => '1950', 'F' => '1951', 
										'Y' => '1952', 'Z' => '1953', 'A' => '1954', 'G' => '1955', 
										'q' => '1956', 'V' => '1957', 'w' => '1958', 'W' => '1959', 
										'i' => '1960', 'I' => '1961', 'u' => '1962', 'U' => '1963', 
										'e' => '1964', 'E' => '1965', 'o' => '1966', 'O' => '1967', 
										'c' => '1968', ',' => '1548', ';' => '1563', '?' => '1567', 
										')' => '0041', '(' => '0040', 'Q' => '65010'
										);
	
	/**
	 * Mapping for Unicode to Ascii
	 *
	 * @var array
	 * @access private
	 */
	private $mapUnicodeToAscii;
	
	// }}}
	// {{{ convertAsciiToUnicode()

    /**
     * Converts ASCII Dhivehi text to Unicode
     *
     * @param	string $text	Dhivehi text in ASCII Thaana
     * @return	mixed			A String with Thaana text in Unicode if conversion was successful
     *
     * @access public
     */
	public function convertAsciiToUnicode($text)
	{
		// Watch out for errors
		try {
			// Declare output container variable
			$output = '';
			
			// Loop through the given text
			for ($i = 0; $i < strlen($text); $i++) {
				// Swap ASCII character with Unicode equivalent
				if (isset($this->mapAsciiToUnicode[$text[$i]])) {
					$output .=  '&#'. $this->mapAsciiToUnicode[$text[$i]] .';';
				} else {
					$output .=  $text[$i];
				}
			}
			
			// Return converted text
			return $output;
			
		} catch (Exception $e) {
			// Exception occured:
			
			// Return failure
			return false;
		}
	}
	
	// }}}
	// {{{ convertUnicodeToAscii()
	
	/**
	 * Convert HTML encoded Unicode text to Dhivehi Ascii equivalents
	 *
	 * @param	string $text	String with HTML encoded Unicode in Thaana range
	 * @return	mixed			String with the Ascii output if conversion successful
	 *							Boolean false if failure
	 *
	 * @access public
	 */
	public function convertUnicodeToAscii($text)
	{
		// Prepare the Unicode to Ascii translation map if not set
		if (empty($this->mapUnicodeToAscii)) {
			$this->mapUnicodeToAscii = array_flip($this->mapAsciiToUnicode);
		}
		
		// Attempt conversion
		try {
			// Replace Unicode entities with Ascii equivalents
			$text = preg_replace_callback(
							'/&#(.*);/U',
							array(&$this, 'getUnicodeChar'),
							$text
							);
			
			// Fix the numerics and return final output
			return $this->reverseNumerics($text);
			
		} catch (Exception $e) {
			// An error occured while converting:
			
			// Return failure
			return false;
		}
	}
	
	// }}}
	// {{{ getUnicodeChar()
	
	/**
	 *  Get the Unicode equivalent for a character in ASCII
	 *
	 * @param	string $key		Char in ASCII
	 * @return	string			String with Unicode character code
	 *
	 * @access private
	 */
	private function getUnicodeChar($key)
	{
		return ($this->mapUnicodeToAscii[$key[1]]) ? $this->mapUnicodeToAscii[$key[1]] : $key[1];
	}
	
	// }}}
	// {{{ reverseNumerics()
	
	/**
	 * Fix the numeric display order in the text from right-to-left to left-to-right
	 *
	 * @param	string	$text	String with the text to fix numerics
	 * @return	string			String with the numerics fixed text
	 *
	 * @access public
	 */
	private function reverseNumerics($text)
	{
		return preg_replace_callback(
					'/\b[0-9\.,:]+/',
					create_function('$matches', 'return strrev($matches[0]);'),
					$text
				);
	}
	
	// }}}
}

// }}}

?>
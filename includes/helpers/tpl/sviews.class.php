<?php
/**
 * SViews GIT version
 * Requires PHP 5.3+
 * Copyright (c) 2011 Bazinga Labs - https://www.bazingalabs.it
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class SViewsVerifone {

	/* Please don't change this attributes, use config file */

	//Folders
	private $template_dir = "no";
	private $compile_dir = "compiled"; // must have write permissions
	private $i18n_dir = "i18n";

	private $javascript_base_dir = "includes/javascript";
	private $css_dir = "includes/css";
	private $javascript_non_localized_script = "common";

	//Tag delimiters
	private $ldelim = "{{";
	private $rdelim = "}}";
	private $tag_rdelim = "}";
	private $tag_ldelim = "{";

	//i18n
	private $use_i18n = true;
	private $i18n_ltag = "\[\[";
	private $i18n_rtag = "\]\]";
	private $i18n_strings = array();
	private $i18n_default_lang = 'it';
	private $language = "it";

	private $debug = false;
	private $cache_lifetime = 0; // in seconds, 0=disabled

	public function __construct($base_template_path = null, $base_css_path = null, $base_javascript_path = null) {

		$this->initConfig();


		if($base_template_path != null){
			$this->template_dir = $base_template_path;
		}

		if($base_css_path != null){
			$this->css_dir = $base_css_path;
		}

		if($base_javascript_path != null){
			$this->javascript_base_dir = $base_javascript_path;
		}

		if($this->use_i18n){
			$this->language = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
			$lang_file = $this->i18n_dir . DIRECTORY_SEPARATOR . $this->language . ".lang";

			if (empty($lang_file) || !file_exists($lang_file)) {
				$this->language = $this->i18n_default_lang;
				$lang_file = $this->i18n_dir . DIRECTORY_SEPARATOR . $this->i18n_default_lang . ".lang";
			}

			//Read localized strings
			$localized_strings = file_get_contents($lang_file);
			preg_match_all('/([a-zA-Z\_\-]+)\\s=\\s"""(.*?)"""/sm', $localized_strings, $matches);

			//Build i18n array
			$keys = $matches[1];
			$values = $matches[2];
			for ($i = 0; $i < count($keys); $i++) {
				$this->i18n_strings[$keys[$i]] = $values[$i];
			}
		}
	}

	private function initConfig(){
		@require 'sviews.config.php';

		$this->template_dir = $_SVIEWS_CONFIG['template_dir'];
		$this->compile_dir = $_SVIEWS_CONFIG['compile_dir'];
		$this->javascript_base_dir = $_SVIEWS_CONFIG['javascript_base_dir'];
		$this->javascript_non_localized_script = $_SVIEWS_CONFIG['javascript_non_localized_dir'];
		$this->css_dir = $_SVIEWS_CONFIG['css_base_dir'];
		$this->i18n_dir = $_SVIEWS_CONFIG['i18n_dir'];
		$this->use_i18n = $_SVIEWS_CONFIG['use_i18n'];
		$this->i18n_default_lang = $_SVIEWS_CONFIG['i18n_default_language'];
		$this->debug = $_SVIEWS_CONFIG['debug'];
		$this->cache_lifetime = $_SVIEWS_CONFIG['cache_lifetime'];

	}

	private static function __debug($str) {
		echo '<p style="color:grey;">'.$str.'</p>';
	}

	public function render($template_name, array $context = array()) {
		// Compiles and includes requested template
		$compiled_file = $this->compile($template_name, $context);
		if ($this->debug) SViewsVerifone::__debug($compiled_file);

		include ($compiled_file);
	}

	public function compile($template_name, array $context = array()) {

		if(strpos($template_name, DIRECTORY_SEPARATOR)){

			list($path,$fileName)  = explode(DIRECTORY_SEPARATOR, $template_name,2);

			$prefix = md5($path);
			$compiledName = $prefix.$fileName;

		}else{
			$compiledName = $template_name;
		}

		$compiled_filename = $this->compile_dir . DIRECTORY_SEPARATOR . $compiledName . '.php';

		if (file_exists($compiled_filename) && filemtime($compiled_filename) > time() - $this->cache_lifetime) {
			// File is already compiled and cached
			if ($this->debug) SViewsVerifone::__debug('Cached template: '.$compiled_filename);

		} else {
			// Template is not already compiled
			if ($this->debug) {
				SViewsVerifone::__debug('Compiling template file: '.$template_name);
				SViewsVerifone::__debug('Template context: <?php print_r(get_defined_vars()); ?>');
			}

			$filename = $this->template_dir . DIRECTORY_SEPARATOR . $template_name;
			if (!file_exists($filename)) {
				throw new TemplateExceptionVerifone("Template file does not exists: $filename");

			} else {
				$template = file_get_contents($filename);

				$template = '<?php if(!isset($foreachEmptyValues)){$foreachEmptyValues = array();} ?>'.$template;


				if($this->use_i18n){
					/* ************ I18n ************ */
					// [[ I18n_STRING_ID attr_name1="...", attr_name2="..." ]]
					// Warning: I1n must be before {{ var.foo }} to support vars in attributes.
					$i18n_strings = $this->i18n_strings;
					$template = preg_replace_callback('/' . $this->i18n_ltag . '\s(.*?)\s' . $this->i18n_rtag . '/i', function($matches) use ($i18n_strings) {
						return SParserVerifone::_parseI18n($matches, $i18n_strings);
					}, $template);
				}


				/* ************ IF ************ */
				// { if var }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sif ([_\w]+)\s' . $this->tag_rdelim . '/i', function($matches) use ($context) {
					return SParserVerifone::_parseSimpleIf($matches, $context);
				}, $template);

				// { if (some complex php {{ condition }}) }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sif \(([^\n]*)\)\s' . $this->tag_rdelim . '/i', function($matches) use ($context) {
					return SParserVerifone::_parseIfBegin($matches, $context);
				}, $template);

				// { elif (some complex php {{ condition }}) }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\selif \(([^\n]*)\)\s' . $this->tag_rdelim . '/i', function($matches) use ($context) {
					return SParserVerifone::_parseElif($matches, $context);
				}, $template);

				// { else }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\selse\s' . $this->tag_rdelim . '/i', function($matches) {
					return SParserVerifone::_parseIfElse($matches);
				}, $template);

				// { endif }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sendif\s' . $this->tag_rdelim . '/i', function($matches) {
					return SParserVerifone::_parseIfEnd($matches);
				}, $template);


				/* ************ FOREACH ************ */
				// { foreach container_var as inner_var_name }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sforeach ([_\w]+)\sas\s?([_\w]+)\s' . $this->tag_rdelim . '/i', function($matches) use (&$context) {
					return SParserVerifone::_parseForeach($matches, $context);
				}, $template);

				//{ foreachelse }
                $template = preg_replace_callback('/'.$this->tag_ldelim.'\sforeachelse\s'.$this->tag_rdelim.'/i', function($matches) {
                       return SParserVerifone::_parseForeachElse($matches);
                }, $template);

				// { endforeach }
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sendforeach\s' . $this->tag_rdelim . '/i', function($matches) {
					return SParserVerifone::_parseEndForeach($matches);
				}, $template);


				/* ************ VARIABLES ************ */
				// {{ foo_var }}
				// - if the variable is not defined in $context, an exception will be thrown
				// ** Deleted **
				/*$template = preg_replace_callback('/' . $this->ldelim . '\s([_\w]*)\s' . $this->rdelim . '/i', function($matches) use ($context) {
					return SParserVerifone::_parseDottedVar($matches, $context);
				}, $template);*/

				// {{ foo.bar }}
				// - currently this is limited to one-level of dots. E.g.: class.method.method is *not* supported
				// - for {{ foo.bar }} this order is used: $foo['bar'], $foo->bar(), $foo->getBar(), $foo->get('bar')
				// - if you need $foo.bar (where bar is an attribute) then you have something wrong in your mind
				// - case is preserved, except when calling getBar()
				$template = preg_replace_callback('/' . $this->ldelim . '\s([\.\w]*)\s' . $this->rdelim . '/i', function($matches) use ($context) {
					return SParserVerifone::_parseDottedVar($matches, $context);
				}, $template);


				/* ************ TEMPLATE INCLUDE ************ */
				//Includes - { include father.html }
				$current_template_dir = $this->template_dir;
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sinclude\s([a-zA-Z0-9\-\/\.\_]*)\s' . $this->tag_rdelim . '/i', function($matches) use ($context, $current_template_dir) {
					return SParserVerifone::_parseInclude($matches, $context, $current_template_dir);
				}, $template);


				/* ************ STATIC INCLUDES ************ */
				// { include-js script.js }
				$current_javascript_dir = $this->javascript_base_dir . DIRECTORY_SEPARATOR . $this->javascript_non_localized_script;
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sinclude-js\s([a-zA-Z0-9\-\.]*)\s' . $this->tag_rdelim . '/i', function($matches) use ($current_javascript_dir) {
					return SParserVerifone::_parseJavascriptInclude($matches, $current_javascript_dir);
				}, $template);

				// { include-localized-js script.js }
				$current_localized_javascript_dir = $this->javascript_base_dir . DIRECTORY_SEPARATOR . $this->language;
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sinclude-localized-js\s([a-zA-Z0-9\-\.]*)\s' . $this->tag_rdelim . '/i', function($matches) use ($current_localized_javascript_dir) {
					return SParserVerifone::_parseJavascriptInclude($matches, $current_localized_javascript_dir);
				}, $template);

				// { include-css style.css }
				$current_css_dir = $this->css_dir;
				$template = preg_replace_callback('/' . $this->tag_ldelim . '\sinclude-css\s([a-zA-Z0-9\-\.]*)\s' . $this->tag_rdelim . '/i', function($matches) use ($current_css_dir) {
					return SParserVerifone::_parseCssInclude($matches, $current_css_dir);
				}, $template);

				file_put_contents($compiled_filename, $template);
			}
		}
		return $compiled_filename;
	}
}

class SParserVerifone {

	// Helper class for SViews

	public static function __parseVarHelper($variableName, $context) {
		//Order: $foo['bar'], $foo->bar(), $foo->getBar(), $foo->get('bar')
		if (strpos($variableName, '.') === FALSE) {
			if (isset($context[$variableName])) {
				return '$context["' . $variableName . '"]';
			} else {
				//throw new TemplateExceptionVerifone("Variable, method or hash key '{$variableName}' is not defined in current context. Defined vars: " . implode(', ', array_keys($context)));

			}
		} else {
			list($foo, $bar) = explode('.', $variableName);
			if (!isset($context[$foo])) {
				return 'RuntimeVerifone::__parseVarHelper("'.$variableName.'",$context)';
				//throw new TemplateExceptionVerifone("Variable '$foo' is not defined in current context: " . implode(', ', array_keys($context)));
			} else {
				if (is_array($context[$foo]) && isset($context[$foo][$bar])) {
					return '$context["' . $foo . '"]["' . $bar . '"]';
				}

				if (is_object($context[$foo]) || \is_string($context[$foo])) {
					if (method_exists($context[$foo], $bar)) {
						return '$context["' . $foo . '"]->' . $bar . '()';
					} elseif (method_exists($context[$foo], 'get' . ucfirst($bar))) {
						$methodName = 'get' . ucfirst($bar);
						return '$context["' . $foo . '"]->' . $methodName . '()';
					} elseif (method_exists($context[$foo], 'get')) {
						return '$context["' . $foo . '"]->get("' . $bar . '")';
					}
				}

				//throw new TemplateExceptionVerifone("Variable, method or hash key '{$matches[1]}' is not defined in current context. Defined vars: " . implode(', ', array_keys($context)));
				//check this variable again at runtime
				return 'RuntimeVerifone::__parseVarHelper("' . $variableName . '",$context)';
			}
		}
	}

	public static function _parseDottedVar($matches, $context) {
		return '<?php echo htmlentities(' . SParserVerifone::__parseVarHelper($matches[1], $context) . ', ENT_QUOTES); ?>';
	}

	public static function _parseInclude($matches, $context, $template_dir) {
		$s = new SViews($template_dir);
		return '<?php include("' . $s->compile($matches[1], $context) . '"); ?>';
	}

	public static function _parseSimpleIf($matches, $context) {
		$var = SParserVerifone::__parseVarHelper($matches[1], $context);
		return '<?php if (isset(' . $var . ') && (' . $var . '!==NULL) && !empty(' . $var . ') && (' . $var . '!==FALSE)): ?>';
	}

	public static function _parseIfBegin($matches, $context) {
		$condition = $matches[1];

		$condition = preg_replace_callback('/{{\s*([_\.\w]*)\s*}}/i', function($matches) use ($context) {
			return SParserVerifone::__parseVarHelper($matches[1], $context);
		}, $condition);

		return '<?php if(' . $condition . '): ?>';
	}

	public static function _parseElif($matches, $context) {
		$condition = $matches[1];

		$condition = preg_replace_callback('/{{\s*([_\.\w]*)\s*}}/i', function($matches) use ($context) {
			return SParserVerifone::__parseVarHelper($matches[1], $context);
		}, $condition);

		return '<?php elseif(' . $condition . '): ?>';
	}

	public static function _parseIfElse($matches) {
		return '<?php else: ?>';
	}

	public static function _parseIfEnd($matches) {
		return '<?php endif; ?>';
	}

	public static function _parseEndForeach($matches) {
		return '<?php } array_pop($foreachEmptyValues); ?>';
	}

	public static function _parseForeach($matches, &$context) {
		//$matches[1] = source_collection, $matches[2] = inner_var_name
		$context_array_name = SParserVerifone::__parseVarHelper($matches[1], $context);

		$ret  = ' <?php $isEmpty = empty('.$context_array_name.'); ';
		$ret .= ' array_push($foreachEmptyValues, $isEmpty); ';
        $ret .= ' if(!$isEmpty) ';
        $ret .= ' foreach('.$context_array_name.' as $foreach_current_key => $foreach_value){ ';
        $ret .= ' $context["'.$matches[2].'"] = $foreach_value;  '; //Adds value var to result context
        $ret .= ' $context["current_key"] = $foreach_current_key; ?>'; //Adds value var to result context

         // Adds value var to current context
         @eval('$context[$matches[2]] = current(' . $context_array_name . ');');
        // Add key sample value to current context
        $context["current_key"] = "sample_key";

         return $ret;
	}

    public static function _parseForeachElse($matches){
        $ret =  '<?php } ';
        $ret.= '$isLastArrayEmpty = array_pop($foreachEmptyValues); array_push($foreachEmptyValues, $isLastArrayEmpty);';
        $ret.= 'if($isLastArrayEmpty){ ?>';
		return $ret;
    }

	public static function _parseI18n($matches, $i18n_strings) {
		preg_match_all('/(([a-zA-Z_]+))/is', $matches[1], $res);
		$string_id = $res[1][0]; // identifier

		if (!key_exists($string_id, $i18n_strings))
			throw new I18nException("No localized string found with identifier '" . $string_id . "'");

		$params = array();
		preg_match_all('/([a-z0-9\_\-]*)="(.*?)"/i', $matches[1], $res);

		$keys = $res[1];
		$values = $res[2];

		$string = $i18n_strings[$string_id];
		for ($i = 0; $i < count($keys); $i++) {
			$string = str_replace('{{ '.$keys[$i].' }}', $values[$i], $string);
		}

		return $string;
	}


	public static function _parseJavascriptInclude($matches, $js_dir) {
		$js_path = $js_dir . DIRECTORY_SEPARATOR . $matches[1];
		return '<script type="text/javascript" src="' . $js_path . '"></script>';
	}

	public static function _parseCssInclude($matches, $css_dir) {
		$css_path = $css_dir . DIRECTORY_SEPARATOR . $matches[1];
		return '<style type="text/css">@import url(' . $css_path . ');</style>';
	}

}


class RuntimeVerifone{

	public static function __parseVarHelper($variableName, $context) {
		//Order: $foo['bar'], $foo->bar(), $foo->getBar(), $foo->get('bar')
		if (strpos($variableName, '.') === FALSE) {
			if (isset($context[$variableName])) {
				return '$context["' . $variableName . '"]';
			} else {
				throw new TemplateExceptionVerifone("Variable, method or hash key '{$variableName}' is not defined in current context. Defined vars: " . implode(', ', array_keys($context)));
			}
		} else {
			list($foo, $bar) = explode('.', $variableName);
			if (!isset($context[$foo])) {
				throw new TemplateExceptionVerifone("Variable '$foo' is not defined in current context: " . implode(', ', array_keys($context)));
			} else {
				if (is_array($context[$foo]) && isset($context[$foo][$bar])) {
					return $context[$foo][$bar];
				} elseif (method_exists($context[$foo], $bar)) {
					return $context[$foo]->$bar();
				} elseif (method_exists($context[$foo], 'get' . ucfirst($bar))) {
					$methodName = 'get' . ucfirst($bar);
					return $context[$foo]->$methodName();
				} elseif (method_exists($context[$foo], 'get')) {
					return $context[$foo]->get($bar);
				} else {
					throw new TemplateExceptionVerifone("Variable, method or hash key '{$variableName}' is not defined in current context. Defined vars: " . implode(', ', array_keys($context)));
				}
			}
		}
	}
}

class TemplateExceptionVerifone extends RuntimeException {
}

class I18nExceptionVerifone extends RuntimeException {
}

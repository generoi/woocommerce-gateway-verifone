<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2017 Lamia Oy (https://lamia.fi)
 * @author    Szymon Nosal <simon@lamia.fi>
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Verifone_Tpl
{

    const REQUEST_FORM = 'request-form.thtml';
    const PAYMENT_METHODS_FORM = 'payment-methods-form.thtml';
	const PAYMENT_METHOD_LOGOS_FORM = 'payment-method-logos-form.thtml';
    const SUMMARY = 'summary.thtml';

    public static function render($context, $filename)
    {

		// $context is transmitted automatically to the included file
		include plugin_dir_path(__FILE__) . 'tpl/compiled/' . $filename . '.php';

    }
}

// Carry this legacy class to not break existing templates
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

<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		$me->addMacro('img', [$me, 'beginImg'], NULL, [$me, 'attrImg']);
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function beginImg(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo $imageStorage->link($imageStorage->getResource(%node.args));');
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function attrImg(MacroNode $node, PhpWriter $writer)
	{
		if ($node->htmlNode->name === 'a') {
			$attr = 'href=';
		} else {
			$attr = 'src=';
		}

		return $writer->write('echo \' ' . $attr . '"\' . $imageStorage->link($imageStorage->getResource(%node.args)) . \'"\'');
	}
}
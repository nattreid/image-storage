<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macro extends MacroSet
{

	public static function install(Compiler $compiler): void
	{
		$macro = new static($compiler);

		$macro->addMacro('img', function (MacroNode $node, PhpWriter $writer): string {
			return $writer->write('echo $imageStorage->link($imageStorage->getResource(%node.args));');
		}, null, function (MacroNode $node, PhpWriter $writer): string {
			if ($node->htmlNode->name === 'a') {
				$attr = 'href=';
			} else {
				$attr = 'src=';
			}

			return $writer->write('echo \' ' . $attr . '"\' . $imageStorage->link($imageStorage->getResource(%node.args)) . \'"\'');
		});
	}
}
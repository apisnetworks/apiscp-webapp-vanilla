<?php declare(strict_types=1);
/*
 * 	Copyright (C) Apis Networks, Inc - All Rights Reserved.
 *
 * 	Unauthorized copying of this file, via any medium, is
 * 	strictly prohibited without consent. Any dissemination of
 * 	material herein is prohibited.
 *
 * 	For licensing inquiries email <licensing@apisnetworks.com>
 *
 * 	Written by Matt Saladna <matt@apisnetworks.com>, January 2024
 */


namespace Module\Support\Webapps\App\Type\Vanilla;

use Module\Support\Php\TreeWalker;
use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\NodeFinder;

class ConstantFetch extends TreeWalker
{
	public function replace(string $var, mixed $new): TreeWalker
	{
		fatal("Unsupported");
		return $this;
	}

	public function set(string $var, mixed $new): TreeWalker
	{
		fatal("Unsupported");
		return $this;
	}

	/**
	 * Supports dot-delimited nested values
	 *
	 * @param string     $var
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get(string $var, mixed $default = null): mixed
	{
		$nodeFinder = new NodeFinder;
		$result = $nodeFinder->findFirst($this->ast, function (Node $node) use ($var) {
			if (!$node instanceof \PhpParser\Node\Expr\FuncCall || $node->name->toLowerString() !== 'define') {
				return false;
			}

			return $node->args[0]->value->value === $var;
		});

		if (!$result) {
			return $default;
		}

		$found = $result->args[1];

		try {
			return (new ConstExprEvaluator)->evaluateSilently($found->value);
		} catch (ConstExprEvaluationException $expr) {
			return (new \PhpParser\PrettyPrinter\Standard())->prettyPrint(
				[$found]
			);
		}
	}
}
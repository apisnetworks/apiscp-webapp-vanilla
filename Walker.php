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
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class Walker extends TreeWalker
{
	const STORAGE_VAR = 'Configuration';

	public function replace(string $var, mixed $new): TreeWalker
	{
		return $this->walkReplace($var, $new, false);
	}

	public function set(string $var, mixed $new): TreeWalker
	{
		return $this->walkReplace($var, $new, true);
	}

	protected function walkReplace(string $var, mixed $new, bool $append = false): self
	{
		$replacement = $this->inferType($new);
		$node = $this->locateNode($var);
		$traverser = new NodeTraverser;

		if (!$node) {
			if ($append) {
				$this->ast[] = $this->buildDimNode($var, $new);
			}
			return $this;
		}

		$traverser->addVisitor(new class ($node, $replacement) extends NodeVisitorAbstract {

			public function __construct(
				private ?Node $node,
				private Node $replacement
			) { }

			public function leaveNode(Node $node) {
				if ($node !== $this->node) {
					return;
				}

				$node->expr->expr = $this->replacement;
			}
		});

		$traverser->traverse($this->ast);

		return $this;
	}

	private function buildDimNode(string $var, mixed $val): ?Node
	{
		$components = explode('.', $var);
		$stack = new Node\Expr\ArrayDimFetch(
			new Node\Expr\Variable(
				self::STORAGE_VAR
			),
			$this->inferType(current($components))
		);

		while (false !== ($next = next($components))) {
			$stack = new Node\Expr\ArrayDimFetch(
				$stack,
				$this->inferType($next)
			);
		}

		$node = new Node\Expr\Assign(
			$stack,
			$this->inferType($val)
		);

		return new Stmt\Expression(
			$node
		);
	}

	private function locateNode(string $var): ?Node
	{
		$components = explode('.', $var);
		return $this->first(function (Node $stmt) use ($components) {

			if (!isset($stmt->expr) || !$stmt->expr instanceof Node\Expr\Assign || !$stmt->expr->var instanceof Node\Expr\ArrayDimFetch) {
				return false;
			}

			end($components);
			$head = $stmt->expr->var;
			do {
				if ($head->dim->value !== current($components)) {
					return false;
				}
				$head = $head->var;
			} while (false !== prev($components));

			if ($head->name !== self::STORAGE_VAR) {
				return false;
			}

			return $stmt;
		});
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
		if (null === ($node = $this->locateNode($var))) {
			return $default;
		}

		try {
			return (new ConstExprEvaluator)->evaluateSilently($node->expr->expr);
		} catch (ConstExprEvaluationException $expr) {
			return (new \PhpParser\PrettyPrinter\Standard())->prettyPrint(
				[$node]
			);
		}
	}
}
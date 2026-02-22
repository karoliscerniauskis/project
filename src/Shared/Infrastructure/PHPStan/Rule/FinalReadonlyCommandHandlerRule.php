<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\PHPStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 */
final readonly class FinalReadonlyCommandHandlerRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $file = $scope->getFile();

        if ($file === '') {
            return [];
        }

        if (!preg_match('~[/\\\\]src[/\\\\].+[/\\\\]Application[/\\\\]Handler[/\\\\].+\.php$~', $file)) {
            return [];
        }

        // Anonymous class
        if ($node->name === null) {
            return [];
        }

        $className = $node->namespacedName !== null
            ? $node->namespacedName->toString()
            : $node->name->toString();

        $errors = [];

        if (!$node->isFinal()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Application command handler class "%s" must be final.',
                $className,
            ))
                ->identifier('app.commandHandler.mustBeFinal')
                ->build();
        }

        if (!$node->isReadonly()) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Application command handler class "%s" must be readonly.',
                $className,
            ))
                ->identifier('app.commandHandler.mustBeReadonly')
                ->build();
        }

        return $errors;
    }
}

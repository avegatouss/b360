<?php

declare(strict_types=1);

namespace B360\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Règle PHPStan B360 : interdit l'accès direct aux tables d'un autre module via DB::table('prefix_*').
 *
 * Exemple bloqué :
 *   namespace Modules\Menuiserie360\Services;
 *   DB::table('eshop_products')->where(...)->get();
 *
 * Doit passer par : un contrat injecté du module propriétaire ou un événement.
 *
 * Configuration : voir tools/phpstan/phpstan.neon section services.
 *
 * @implements Rule<StaticCall>
 */
final class NoDirectCrossModuleTableAccess implements Rule
{
    /** @var array<string, string> Mapping préfixe table → namespace propriétaire */
    private array $tablePrefixToNamespace;

    /**
     * @param  array<string, string>  $tablePrefixToNamespace
     */
    public function __construct(array $tablePrefixToNamespace = [])
    {
        $this->tablePrefixToNamespace = $tablePrefixToNamespace;
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param  StaticCall  $node
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier || $node->name->toString() !== 'table') {
            return [];
        }

        if (! $node->class instanceof Name) {
            return [];
        }

        $className = $node->class->toString();
        if (! in_array($className, ['DB', 'Illuminate\\Support\\Facades\\DB'], true)) {
            return [];
        }

        if (! isset($node->args[0]) || ! $node->args[0]->value instanceof String_) {
            return [];
        }

        $tableName = $node->args[0]->value->value;
        $callerNamespace = $scope->getNamespace() ?? '';

        foreach ($this->tablePrefixToNamespace as $prefix => $ownerNamespace) {
            if (! str_starts_with($tableName, $prefix)) {
                continue;
            }
            if (str_starts_with($callerNamespace, $ownerNamespace)) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf(
                    'Accès DB direct interdit : la table "%s" appartient à %s. '
                    .'Utilise un contrat ou un événement publié par ce module. '
                    .'Voir docs/architecture/MODULE_DEPENDENCY_MAP.md',
                    $tableName,
                    $ownerNamespace
                ))
                    ->identifier('b360.crossModuleTableAccess')
                    ->build(),
            ];
        }

        return [];
    }
}

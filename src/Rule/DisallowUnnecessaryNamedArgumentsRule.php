<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Rule;

use PhpParser\Node;
use PhpParser\Node\{
    Arg,
    Attribute,
    Expr\CallLike,
    Expr\FuncCall,
    Expr\MethodCall,
    Expr\New_,
    Expr\StaticCall,
    Identifier,
    Name,
};
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\{
    ExtendedMethodReflection,
    FunctionReflection,
    ParametersAcceptorSelector,
    ReflectionProvider,
};
use PHPStan\Rules\{
    IdentifierRuleError,
    Rule,
    RuleErrorBuilder,
};
use Steevanb\PhpCollection\ScalarCollection\{
    IntegerCollection,
    StringCollection,
};

/** @implements Rule<Node> */
final readonly class DisallowUnnecessaryNamedArgumentsRule implements Rule
{
    private const string IDENTIFIER = 'steevanb.unnecessaryNamedArguments';

    public function __construct(private ReflectionProvider $reflectionProvider)
    {
    }

    #[\Override]
    public function getNodeType(): string
    {
        return Node::class;
    }

    /** @return list<IdentifierRuleError> */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $call = $this->getCall($node);
        if (
            $call instanceof CallLike === false
            || $call->isFirstClassCallable()
            || $this->hasNamedArgument($call) === false
            || $this->hasUnpack($call)
        ) {
            return [];
        }

        $callee = $this->getCallee($call, $scope);
        if ($callee === null) {
            return [];
        }

        $unnecessaryNames = $this->getUnnecessaryNames($call, $scope, $callee);
        if ($unnecessaryNames->count() === 0) {
            return [];
        }

        return [
            RuleErrorBuilder::message($this->createMessage($unnecessaryNames, $callee))
                ->identifier(self::IDENTIFIER)
                ->build(),
        ];
    }

    private function getCall(Node $node): ?CallLike
    {
        return match (true) {
            $node instanceof CallLike => $node,
            $node instanceof Attribute => new New_($node->name, $node->args, $node->getAttributes()),
            default => null,
        };
    }

    private function hasNamedArgument(CallLike $node): bool
    {
        foreach ($node->getArgs() as $arg) {
            if ($arg->name !== null) {
                return true;
            }
        }

        return false;
    }

    private function hasUnpack(CallLike $node): bool
    {
        foreach ($node->getArgs() as $arg) {
            if ($arg->unpack) {
                return true;
            }
        }

        return false;
    }

    private function getCallee(CallLike $node, Scope $scope): ExtendedMethodReflection|FunctionReflection|null
    {
        return match (true) {
            $node instanceof New_ => $this->getConstructor($node, $scope),
            $node instanceof MethodCall => $this->getMethod($node, $scope),
            $node instanceof StaticCall => $this->getStaticMethod($node, $scope),
            $node instanceof FuncCall => $this->getFunction($node, $scope),
            default => null,
        };
    }

    private function getConstructor(New_ $node, Scope $scope): ?ExtendedMethodReflection
    {
        if ($node->class instanceof Name === false) {
            return null;
        }

        $classReflections = $scope->resolveTypeByName($node->class)->getObjectClassReflections();

        return count($classReflections) === 1 && $classReflections[0]->hasConstructor()
            ? $classReflections[0]->getConstructor()
            : null;
    }

    private function getMethod(MethodCall $node, Scope $scope): ?ExtendedMethodReflection
    {
        if ($node->name instanceof Identifier === false) {
            return null;
        }

        $type = $scope->getType($node->var);

        return $type->hasMethod($node->name->toString())->yes()
            ? $type->getMethod($node->name->toString(), $scope)
            : null;
    }

    private function getStaticMethod(StaticCall $node, Scope $scope): ?ExtendedMethodReflection
    {
        if ($node->class instanceof Name === false || $node->name instanceof Identifier === false) {
            return null;
        }

        $type = $scope->resolveTypeByName($node->class);

        return $type->hasMethod($node->name->toString())->yes()
            ? $type->getMethod($node->name->toString(), $scope)
            : null;
    }

    private function getFunction(FuncCall $node, Scope $scope): ?FunctionReflection
    {
        return $node->name instanceof Name && $this->reflectionProvider->hasFunction($node->name, $scope)
            ? $this->reflectionProvider->getFunction($node->name, $scope)
            : null;
    }

    private function getUnnecessaryNames(
        CallLike $node,
        Scope $scope,
        ExtendedMethodReflection|FunctionReflection $callee,
    ): StringCollection {
        $parameterNames = new StringCollection();
        $parameters = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $node->getArgs(),
            $callee->getVariants(),
            $callee->getNamedArgumentsVariants(),
        )->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic() === false) {
                $parameterNames->add($parameter->getName());
            }
        }

        $namedPositions = $this->getNamedPositions($node, $parameterNames);
        $unnecessaryNames = new StringCollection();
        $position = $this->countPositionalArguments($node);
        while ($namedPositions->contains($position)) {
            $unnecessaryNames->add($parameterNames->toArray()[$position]);
            $position++;
        }

        return $unnecessaryNames;
    }

    private function getNamedPositions(CallLike $node, StringCollection $parameterNames): IntegerCollection
    {
        $namedPositions = new IntegerCollection();
        foreach ($node->getArgs() as $arg) {
            $position = $arg->name === null
                ? false
                : array_search($arg->name->toString(), $parameterNames->toArray(), true);
            if (is_int($position)) {
                $namedPositions->add($position);
            }
        }

        return $namedPositions;
    }

    private function countPositionalArguments(CallLike $node): int
    {
        return count(
            array_filter($node->getArgs(), static fn (Arg $arg): bool => $arg->name === null)
        );
    }

    private function createMessage(
        StringCollection $unnecessaryNames,
        ExtendedMethodReflection|FunctionReflection $callee,
    ): string {
        $isPlural = $unnecessaryNames->count() > 1;

        return sprintf(
            'Named argument%s %s of %s %s unnecessary: pass %s positionally, in the order of the parameters.',
            $isPlural ? 's' : '',
            implode(', ', $unnecessaryNames->toArray()),
            $this->getCalleeName($callee),
            $isPlural ? 'are' : 'is',
            $isPlural ? 'them' : 'it',
        );
    }

    private function getCalleeName(ExtendedMethodReflection|FunctionReflection $callee): string
    {
        return $callee instanceof ExtendedMethodReflection
            ? sprintf('%s::%s()', $callee->getDeclaringClass()->getName(), $callee->getName())
            : sprintf('%s()', $callee->getName());
    }
}

<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Rule;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\{
    IdentifierRuleError,
    Rule,
    RuleErrorBuilder,
};
use PHPStan\Type\Constant\ConstantStringType;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    CoversTrait,
};
use Steevanb\PhpCollection\ScalarCollection\StringCollection;

/** @implements Rule<InClassNode> */
final readonly class CoverageTargetRule implements Rule
{
    private const string SINGLE_IDENTIFIER = 'steevanb.singleCoverageTarget';

    private const string MIRRORED_IDENTIFIER = 'steevanb.mirroredCoverageTarget';

    private const array COVERAGE_ATTRIBUTES = [CoversClass::class, CoversTrait::class];

    private const string TEST_SUFFIX = 'Test';

    private StringCollection $testNamespacePrefixes;

    private StringCollection $targetNamespacePrefixes;

    /**
     * @param list<string> $testNamespacePrefixes
     * @param list<string> $targetNamespacePrefixes
     */
    public function __construct(array $testNamespacePrefixes, array $targetNamespacePrefixes)
    {
        $this->testNamespacePrefixes = new StringCollection($testNamespacePrefixes);
        $this->targetNamespacePrefixes = new StringCollection($targetNamespacePrefixes);
    }

    #[\Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /** @return list<IdentifierRuleError> */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $testClass = $node->getClassReflection()->getName();
        $targets = $this->getCoverageTargets($node, $scope);

        if ($targets->count() > 1) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        '%s declares %d #[CoversClass] / #[CoversTrait] attributes, '
                            . 'a test class covers a single class or a single trait.',
                        $testClass,
                        $targets->count(),
                    )
                )
                    ->identifier(self::SINGLE_IDENTIFIER)
                    ->build(),
            ];
        }

        $mirroredName = $this->getMirroredName($testClass);
        if (
            $targets->isEmpty()
            || $mirroredName === null
            || $this->removePrefix($targets->toArray()[0], $this->targetNamespacePrefixes) === $mirroredName
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    '%s covers %s, a test class covers the class or trait it mirrors: %s.',
                    $testClass,
                    $targets->toArray()[0],
                    $this->getMirroredCandidates($mirroredName),
                )
            )
                ->identifier(self::MIRRORED_IDENTIFIER)
                ->build(),
        ];
    }

    private function getCoverageTargets(InClassNode $node, Scope $scope): StringCollection
    {
        $targets = new StringCollection();
        foreach ($node->getOriginalNode()->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($this->isCoverageAttribute($attribute)) {
                    $targets->add($this->getTargetName($attribute, $scope));
                }
            }
        }

        return $targets;
    }

    private function isCoverageAttribute(Attribute $attribute): bool
    {
        return in_array($attribute->name->toString(), self::COVERAGE_ATTRIBUTES, true);
    }

    private function getTargetName(Attribute $attribute, Scope $scope): string
    {
        return implode(
            '',
            array_map(
                static fn(ConstantStringType $target): string => $target->getValue(),
                $scope->getType($attribute->args[0]->value)->getConstantStrings()
            )
        );
    }

    private function getMirroredName(string $testClass): ?string
    {
        $mirroredName = $this->removePrefix($testClass, $this->testNamespacePrefixes);

        return $mirroredName === $testClass || str_ends_with($mirroredName, self::TEST_SUFFIX) === false
            ? null
            : substr($mirroredName, 0, -strlen(self::TEST_SUFFIX));
    }

    private function getMirroredCandidates(string $mirroredName): string
    {
        return implode(
            ' or ',
            array_map(
                static fn(string $prefix): string => $prefix . $mirroredName,
                $this->targetNamespacePrefixes->toArray()
            )
        );
    }

    private function removePrefix(string $name, StringCollection $prefixes): string
    {
        foreach ($prefixes->toArray() as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return substr($name, strlen($prefix));
            }
        }

        return $name;
    }
}

<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Extension;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\{
    ClassConstantReflection,
    ReflectionProvider,
};
use PHPStan\Rules\RestrictedUsage\{
    RestrictedClassConstantUsageExtension,
    RestrictedUsage,
};
use PHPUnit\Framework\TestCase;
use Steevanb\PhpCollection\ScalarCollection\StringCollection;

final readonly class DisallowConstantsInTestsExtension implements RestrictedClassConstantUsageExtension
{
    private const string IDENTIFIER = 'steevanb.disallowConstantsInTests';

    private const string ABSOLUTE_PATH_PATTERN = '~^(/|[a-z]:[/\\\\]|[a-z0-9+.-]+://)~i';

    private StringCollection $testsDirectories;

    /** @param list<string> $testsDirectories relative ones are resolved from the current working directory */
    public function __construct(private ReflectionProvider $reflectionProvider, array $testsDirectories)
    {
        $this->testsDirectories = new StringCollection(
            array_map(
                fn(string $directory): string => rtrim($this->absolutize($directory), '/\\') . DIRECTORY_SEPARATOR,
                $testsDirectories
            )
        );
    }

    #[\Override]
    public function isRestrictedClassConstantUsage(
        ClassConstantReflection $constantReflection,
        Scope $scope,
    ): ?RestrictedUsage {
        $declaringClass = $constantReflection->getDeclaringClass();
        $constantFileName = $declaringClass->getFileName();

        if (
            $this->isInTestCase($scope) === false
            || $constantFileName === null
            || $declaringClass->isEnum()
            || $this->isInTestsDirectory($constantFileName)
        ) {
            return null;
        }

        return RestrictedUsage::create(
            sprintf(
                'Using %s::%s in tests is disallowed, use the literal value instead.',
                $declaringClass->getName(),
                $constantReflection->getName(),
            ),
            self::IDENTIFIER,
        );
    }

    private function absolutize(string $directory): string
    {
        $workingDirectory = getcwd();

        return $workingDirectory === false || preg_match(self::ABSOLUTE_PATH_PATTERN, $directory) === 1
            ? $directory
            : $workingDirectory . DIRECTORY_SEPARATOR . $directory;
    }

    private function isInTestCase(Scope $scope): bool
    {
        return $scope->getClassReflection()?->isSubclassOfClass($this->reflectionProvider->getClass(TestCase::class))
            ?? false;
    }

    private function isInTestsDirectory(string $fileName): bool
    {
        foreach ($this->testsDirectories->toArray() as $testsDirectory) {
            if (str_starts_with($fileName, $testsDirectory)) {
                return true;
            }
        }

        return false;
    }
}

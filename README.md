# steevanb/phpstan-rules

PHPStan rules:

| Rule | Identifier(s) | Purpose |
| --- | --- | --- |
| `DisallowUnnecessaryNamedArgumentsRule` | `steevanb.unnecessaryNamedArguments` | A named argument that could be passed positionally is reported |
| `CoverageTargetRule` | `steevanb.singleCoverageTarget`, `steevanb.mirroredCoverageTarget` | A test class covers a single class or trait, the one it mirrors |
| `DisallowConstantsInTestsExtension` | `steevanb.disallowConstantsInTests` | A `TestCase` uses literal values, not constants of the tested code |

## Installation

```bash
composer require --dev steevanb/phpstan-rules
```

With [phpstan/extension-installer](https://github.com/phpstan/extension-installer), the rules are registered
automatically. Otherwise, include `extension.neon` in your PHPStan configuration:

```neon
includes:
    - vendor/steevanb/phpstan-rules/extension.neon
```

## Configuration

Every parameter is optional:

```neon
parameters:
    steevanbPhpStanRules:
        coverageTarget:
            # Namespace prefixes of the test classes, removed to find the mirrored class.
            testNamespacePrefixes:
                - App\Tests\Unit\
                - App\Tests\Functional\
            # Namespace prefixes of the covered classes, the most specific first.
            targetNamespacePrefixes:
                - App\Tests\
                - App\
        disallowConstantsInTests:
            # Constants declared in these directories stay allowed in tests.
            # Relative paths are resolved from the directory PHPStan is launched from.
            # The "!" replaces the default value ("tests") instead of adding to it.
            testsDirectories!:
                - tests
```

## Rules

### `DisallowUnnecessaryNamedArgumentsRule`

Named arguments are only useful to skip optional parameters. When the named arguments of a call could be passed
positionally (they directly follow the positional arguments, in the order of the parameters), they are reported.

Method calls, nullsafe method calls, static calls, function calls, `new` and attributes are checked. Calls using
argument unpacking (`...$arguments`), first-class callables and calls whose callee can't be resolved are ignored.

```php
str_pad('x', 3, pad_type: STR_PAD_LEFT); // ✔ pad_string is skipped
str_pad('x', 3, ' ', pad_type: STR_PAD_LEFT); // ✘ Named argument pad_type of str_pad() is unnecessary
str_pad(length: 3, string: 'x'); // ✘ Named arguments string, length of str_pad() are unnecessary
```

### `CoverageTargetRule`

A class declaring several `#[CoversClass]` / `#[CoversTrait]` attributes is reported: a test class covers a single
class or a single trait.

When `testNamespacePrefixes` and `targetNamespacePrefixes` are configured, the covered class must be the one the test
class mirrors: with the configuration above, `App\Tests\Unit\Foo\BarTest` must cover `App\Tests\Foo\Bar` or
`App\Foo\Bar`. Classes outside of `testNamespacePrefixes` or without the `Test` suffix are not checked.

```php
namespace App\Tests\Unit\Foo;

#[CoversClass(\App\Foo\Bar::class)] // ✔
final class BarTest extends TestCase {}

#[CoversClass(\App\Foo\Baz::class)] // ✘ covers App\Foo\Baz, a test class covers the class or trait it mirrors
final class BarTest extends TestCase {}
```

### `DisallowConstantsInTestsExtension`

Inside a `PHPUnit\Framework\TestCase` subclass, using a class constant of the tested code is reported: a test asserts
literal values, so that changing the value of a constant breaks the test.

Enum cases and constants declared in `testsDirectories` stay allowed.

```php
final class FooTest extends TestCase
{
    public function testType(): void
    {
        static::assertSame(Foo::TYPE, new Foo()->getType()); // ✘ Using App\Foo::TYPE in tests is disallowed
        static::assertSame('foo', new Foo()->getType()); // ✔
    }
}
```

## Development

All the tooling runs inside a single Docker container (`ghcr.io/steevanb/phpstan-rules:ci`, built from `docker/ci/`),
through the Python wrappers of [steevanb/python-devops](https://github.com/steevanb/python-devops) in `bin/`.
Each wrapper re-runs itself in a `docker run` of this image, so the host needs only Python 3.10+ and Docker.

```bash
# Pull the CI image (--local to keep the local one) and install the dependencies
bin/ci/start.py

# Run every check in parallel
bin/ci/validate.py

# Or run them one by one
bin/ci/phpunit.py
bin/ci/phpstan.py
bin/ci/phpcs.py
bin/ci/phpcbf.py
bin/ci/composer-require-checker.py
bin/ci/composer-normalize.py
bin/ci/composer-validate.py
bin/ci/phpdd.py

# Run any command in the CI container
bin/ci/shell.py sh

# Build the CI image (--push to publish it to ghcr.io)
bin/docker/build.py
```

`steevanb/python-devops` is a private repository, declared as a `vcs` entry in `composer.json`. Composer needs a GitHub
token (classic, `repo` scope) in `~/.config/composer/auth.json` to install it:

```json
{
    "github-oauth": {
        "github.com": "ghp_yourtokenhere"
    }
}
```

`composer.lock` is not committed, so `bin/ci/start.py` mounts this file in the container to install the dependencies.

## CI

The GitHub Actions workflow (`.github/workflows/ci.yml`) runs `bin/ci/start.py` then `bin/ci/validate.py` on every
push. It needs a `GHCR_TOKEN` Actions **secret**, a classic token with the `read:packages` (pull
`ghcr.io/steevanb/phpstan-rules:ci`) and `repo` (install `steevanb/python-devops`) scopes:

https://github.com/steevanb/phpstan-rules/settings/secrets/actions

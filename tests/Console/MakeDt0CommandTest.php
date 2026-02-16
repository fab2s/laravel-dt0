<?php

/*
 * This file is part of fab2s/laravel-dt0.
 * (c) Fabrice de Stefanis / https://github.com/fab2s/laravel-dt0
 * This source file is licensed under the MIT license which you will
 * find in the LICENSE file or at https://opensource.org/licenses/MIT
 */

namespace fab2s\Dt0\Laravel\Tests\Console;

use fab2s\Dt0\Laravel\Dt0ServiceProvider;
use fab2s\Dt0\Laravel\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class MakeDt0CommandTest extends TestCase
{
    protected Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem;
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->app->basePath('app/Dto'));
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            Dt0ServiceProvider::class,
        ];
    }

    public function test_creates_dt0(): void
    {
        $this->artisan('make:dt0', ['name' => 'TestDto'])
            ->assertSuccessful()
        ;

        $filePath = $this->app->basePath('app/Dto/TestDto.php');
        $this->assertFileExists($filePath);

        $content = $this->files->get($filePath);
        $this->assertStringContainsString('namespace App\Dto;', $content);
        $this->assertStringContainsString('use fab2s\Dt0\Laravel\Dt0;', $content);
        $this->assertStringContainsString('class TestDto extends Dt0', $content);
        $this->assertStringNotContainsString('#[Validate', $content);
    }

    public function test_creates_validated_dt0(): void
    {
        $this->artisan('make:dt0', ['name' => 'ValidatedDto', '--validated' => true])
            ->assertSuccessful()
        ;

        $filePath = $this->app->basePath('app/Dto/ValidatedDto.php');
        $this->assertFileExists($filePath);

        $content = $this->files->get($filePath);
        $this->assertStringContainsString('namespace App\Dto;', $content);
        $this->assertStringContainsString('use fab2s\Dt0\Laravel\Dt0;', $content);
        $this->assertStringContainsString('#[Validate(Validator::class)]', $content);
        $this->assertStringContainsString('#[Rule(', $content);
        $this->assertStringContainsString('class ValidatedDto extends Dt0', $content);
    }

    public function test_does_not_overwrite_existing_class(): void
    {
        $this->artisan('make:dt0', ['name' => 'DupeDto'])
            ->assertSuccessful()
        ;

        $filePath        = $this->app->basePath('app/Dto/DupeDto.php');
        $originalContent = $this->files->get($filePath);

        $this->artisan('make:dt0', ['name' => 'DupeDto', '--validated' => true]);

        $this->assertSame($originalContent, $this->files->get($filePath));
    }

    public function test_uses_custom_stub_when_present(): void
    {
        $customStubPath = $this->app->basePath('stubs/dt0.stub');
        $this->files->ensureDirectoryExists(dirname($customStubPath));
        $this->files->put($customStubPath, <<<'STUB'
<?php

namespace {{ namespace }};

use fab2s\Dt0\Laravel\Dt0;

// custom stub
class {{ class }} extends Dt0
{
}
STUB);

        try {
            $this->artisan('make:dt0', ['name' => 'CustomDto'])
                ->assertSuccessful()
            ;

            $content = $this->files->get($this->app->basePath('app/Dto/CustomDto.php'));
            $this->assertStringContainsString('// custom stub', $content);
        } finally {
            $this->files->deleteDirectory($this->app->basePath('stubs'));
        }
    }
}

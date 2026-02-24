<?php

namespace Rehark\ApiGeneratorBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Command\MakeApiResourceCommand;
use Rehark\ApiGeneratorBundle\Tests\Utils\PrivateAccessor;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class MakeApiResourceCommandGenerateFilesTest extends TestCase
{
    private string $tmpDir;
    private Filesystem $filesystem;
    private KernelInterface $kernel;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->tmpDir = sys_get_temp_dir() . '/api_generator_test_' . uniqid();
        $this->filesystem->mkdir($this->tmpDir . '/src/Entity');

        $this->kernel = $this->createMock(KernelInterface::class);
        $this->kernel->method('getProjectDir')->willReturn($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
    }

    private function createTemplates(): void
    {
        $templatesDir = $this->tmpDir . '/templates';
        $types = ['Controller', 'CreateInput', 'UpdateInput', 'Output', 'PermissionQuery', 'Resource'];

        foreach ($types as $type) {
            $path = match($type) {
                'Controller' => "$templatesDir/Controller/Controller.tpl.php",
                'CreateInput' => "$templatesDir/DTO/CreateInput.tpl.php",
                'UpdateInput' =>  "$templatesDir/DTO/UpdateInput.tpl.php",
                'Output' => "$templatesDir/DTO/Output.tpl.php",
                'PermissionQuery' => "$templatesDir/Permission/PermissionQuery.tpl.php",
                'Resource' => "$templatesDir/Resource/Resource.tpl.php",
            };
            $this->filesystem->mkdir(dirname($path));
            file_put_contents($path, "$type {{NAME}} {{NAMESPACE}} {{ROUTE}} {{ENTITY}}");
        }
    }

    private function invokeGenerateFiles(
        MakeApiResourceCommand $command,
        string $entityName,
        bool $force = false
    ): mixed {
        $method = PrivateAccessor::getMethod(MakeApiResourceCommand::class, 'generateFiles');
        $method->setAccessible(true);

        return $method->invoke(
            $command,
            $this->filesystem,
            $this->tmpDir,
            $entityName,
            $entityName,
            $force,
            new NullOutput()
        );
    }

    public function testGenerateFilesWithoutForceDoesNotOverwrite(): void
    {
        $this->createTemplates();
        $command = new MakeApiResourceCommand($this->kernel);

        $file = "$this->tmpDir/src/Api/Controller/UserController.php";
        $this->filesystem->mkdir(dirname($file));
        $this->filesystem->dumpFile($file, 'OriginalContent');

        $this->invokeGenerateFiles($command, 'User', false);

        $content = file_get_contents($file);
        $this->assertSame('OriginalContent', $content);
    }

    public function testGenerateFilesCreatesAllFiles(): void
    {
        $this->createTemplates();
        $command = new MakeApiResourceCommand($this->kernel);

        $this->invokeGenerateFiles($command, 'User');

        $expectedFiles = [
            "$this->tmpDir/src/Api/Controller/UserController.php",
            "$this->tmpDir/src/Api/DTO/CreateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UpdateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UserOutput.php",
            "$this->tmpDir/src/Api/Permission/UserPermissionQuery.php",
            "$this->tmpDir/src/Api/Resource/UserResource.php",
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($file);
            $content = file_get_contents($file);
            $this->assertNotFalse($content);
            $this->assertStringContainsString('User', $content);
        }
    }

    public function testGenerateFilesWithForceCreatesFilesWhenNoneExist(): void
    {
        $this->createTemplates();
        $command = new MakeApiResourceCommand($this->kernel);

        // Pas de fichiers existants
        $this->invokeGenerateFiles($command, 'User', true);

        $expectedFiles = [
            "$this->tmpDir/src/Api/Controller/UserController.php",
            "$this->tmpDir/src/Api/DTO/CreateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UpdateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UserOutput.php",
            "$this->tmpDir/src/Api/Permission/UserPermissionQuery.php",
            "$this->tmpDir/src/Api/Resource/UserResource.php",
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($file);
            $content = file_get_contents($file);
            $this->assertNotFalse($content);
            $this->assertStringContainsString('User', $content);
        }
    }

    public function testGenerateFilesWithForceOverwrites(): void
    {
        $this->createTemplates();
        $command = new MakeApiResourceCommand($this->kernel);

        // Crée un fichier existant
        $file = "$this->tmpDir/src/Api/Controller/UserController.php";
        $this->filesystem->mkdir(dirname($file));
        $this->filesystem->dumpFile($file, 'OldContent');

        $this->invokeGenerateFiles($command, 'User', true);

        $content = file_get_contents($file);
        $this->assertNotFalse($content);
        $this->assertStringContainsString('User', $content);
        $this->assertNotSame('OldContent', $content);
    }

}

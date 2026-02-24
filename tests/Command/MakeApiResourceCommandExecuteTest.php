<?php

namespace Rehark\ApiGeneratorBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Command\MakeApiResourceCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class MakeApiResourceCommandExecuteTest extends TestCase
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

    private function createTemplates(): string
    {
        $templatesDir = $this->tmpDir . '/Templates';
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

        return $templatesDir;
    }

    private function assertFilesGenerated(string $expectedName): void
    {
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
            $this->assertStringContainsString($expectedName, $content);
        }
    }

    public function testExecuteEntityDoesNotExist(): void
    {
        $command = new MakeApiResourceCommand($this->kernel);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['name' => 'NonExistentEntity']);
        $output = $tester->getDisplay();

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Entity NonExistentEntity does not exist', $output);
    }

    public function testExecuteEntityExists(): void
    {
        $entityName = 'User';
        $entityFile = $this->tmpDir . '/src/Entity/' . $entityName . '.php';
        $this->filesystem->dumpFile($entityFile, '<?php class User {}');

        $this->createTemplates();

        $command = new MakeApiResourceCommand($this->kernel);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['name' => $entityName]);
        $output = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString("✔ Resource $entityName generated successfully", $output);

        $this->assertFilesGenerated('User');
    }
}

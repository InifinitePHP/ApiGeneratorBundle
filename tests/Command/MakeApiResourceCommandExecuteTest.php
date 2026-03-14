<?php

namespace Rehark\ApiGeneratorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rehark\ApiGeneratorBundle\Command\MakeApiResourceCommand;
use Rehark\ApiGeneratorBundle\Tests\Utils\PrivateAccessor;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\UnicodeString;

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

        $this->kernel = $this->createStub(KernelInterface::class);
        $this->kernel->method('getProjectDir')->willReturn($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
    }

    private function createTemplates(): string
    {
        $templatesDir = $this->tmpDir . '/Templates';

        $types = [
            'Controller', 'Resource',
            'SearchInput', 'CreateInput', 'UpdateInput', 'Output', 
            'PermissionQuery', 'Voter', 'Policy'
        ];

        foreach ($types as $type) {
            $path = match($type) {
                'Controller' => "$templatesDir/Controller/Controller.tpl.php",
                'SearchInput' => "$templatesDir/DTO/SearchInput.tpl.php",
                'CreateInput' => "$templatesDir/DTO/CreateInput.tpl.php",
                'UpdateInput' =>  "$templatesDir/DTO/UpdateInput.tpl.php",
                'Output' => "$templatesDir/DTO/Output.tpl.php",
                'PermissionQuery' => "$templatesDir/Permission/PermissionQuery.tpl.php",
                'Voter' => "$templatesDir/Permission/Voter.tpl.php",
                'Policy' => "$templatesDir/Permission/Policy.tpl.php",
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
            "$this->tmpDir/src/Api/DTO/SearchUserInput.php",
            "$this->tmpDir/src/Api/DTO/CreateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UpdateUserInput.php",
            "$this->tmpDir/src/Api/DTO/UserOutput.php",
            "$this->tmpDir/src/Api/Permission/UserPermissionQuery.php",
            "$this->tmpDir/src/Api/Permission/UserVoter.php",
            "$this->tmpDir/src/Api/Permission/UserPolicy.php",
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

    /**
     * @param string $input
     * @param string $expected
     */
    #[DataProvider('provideGetRouteCases')]
    public function testGetRoute(string $input, string $expected): void
    {
        $method = PrivateAccessor::getMethod(MakeApiResourceCommand::class, 'getRoute');
        $command = new MakeApiResourceCommand($this->kernel);

        $route = $method->invoke($command, $input);

        self::assertSame($expected, $route);
    }

    public static function provideGetRouteCases(): iterable
    {
        yield 'simple camelCase' => ['UserProfile', 'user-profiles'];
        yield 'déjà snake'       => ['user_profile', 'user-profiles'];
        yield 'avec S final'     => ['UsersList', 'users-lists'];
        yield 'tout minuscule'   => ['order', 'orders'];
    }
}

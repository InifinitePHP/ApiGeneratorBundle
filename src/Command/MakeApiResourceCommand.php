<?php

namespace Rehark\ApiGeneratorBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\UnicodeString;

#[AsCommand(
    name: 'make:api-resource',
    description: 'Generates a standard API resource from an existing entity'
)]
class MakeApiResourceCommand extends Command
{
    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel The Symfony kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    /**
     * Configures the command arguments and options.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Entity (e.g. User)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files if they already exist');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int Command status code
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        $name_arg = $input->getArgument('name');
        $force_arg = $input->getOption('force');

        if(!is_string($name_arg)) {
            throw new InvalidArgumentException('Argument name should be a string');
        }

        if(!is_bool($force_arg)) {
            throw new InvalidArgumentException('Argument force should be a boolean');
        }

        $name = ltrim($name_arg, '/');
        $projectDir = $this->kernel->getProjectDir();

        $entityPath = "$projectDir/src/Entity/$name.php";
        $filesystem = new Filesystem();

        if (!$filesystem->exists($entityPath)) {
            $output->writeln("<error>Entity $name does not exist in $projectDir/src/Entity/</error>");
            return Command::FAILURE;
        }


        $this->generateFiles($filesystem, $projectDir, $name, $force_arg, $output);

        $output->writeln("<info>✔ Resource $name generated successfully in $projectDir!</info>");
        return Command::SUCCESS;
    }

    /**
     * Generates API resource files from templates.
     *
     * @param Filesystem $filesystem
     * @param string $projectDir
     * @param string $name
     * @param bool $force Whether to overwrite existing files
     * @param OutputInterface $output
     */
    private function generateFiles(
        Filesystem $filesystem,
        string $projectDir,
        string $name,
        bool $force,
        OutputInterface $output
    ): void {

        $templatesDir = __DIR__ . '/../Templates';
        $templateTypes = ['Controller', 'CreateInput', 'UpdateInput', 'Output', 'PermissionQuery', 'Resource'];

        $parts = explode('/', $name);
        $basename = array_pop($parts);
        $namespace = $parts ? '\\' . implode('\\', $parts) : '';
        $entity = str_replace('/', '\\', $name);
        $route = (new UnicodeString($basename))->camel()->snake()->replace('_', '-')->lower()->toString();

        
        $fileMap = $this->getFilesPathMap($projectDir, $namespace, $basename);
        $templateMap = $this->getTemplatesPathMap($templatesDir);
        
        foreach ($templateTypes as $type) {
            $filePath = $fileMap[$type];

            if (!$force && $filesystem->exists($filePath)) {
                $output->writeln("<comment>Skipped (exists): $filePath</comment>");
                continue;
            }

            $content = file_get_contents($templateMap[$type]);

            if(!$content) {
                // TODO : preciser plus l'erreur
                throw new FileNotFoundException();
            }

            $content = str_replace(['{{NAMESPACE}}', '{{NAME}}'], [$namespace, $basename], $content);

            if ($type === 'Controller') {
                $content = str_replace(['{{ROUTE}}', '{{ENTITY}}'], [$route, $entity], $content);
            }

            $filesystem->dumpFile($filePath, $content);
            $output->writeln("<info>Generated: $filePath</info>");
        }
    }

    /**
     * Returns an array mapping template types to template file paths.
     *
     * @param string $templatesDir
     * @return array<string, string>
     */
    private function getTemplatesPathMap(
        string $templatesDir
    ): array {
        return [
            'Controller' => "$templatesDir/Controller/Controller.tpl.php",
            'CreateInput' => "$templatesDir/DTO/CreateInput.tpl.php",
            'UpdateInput' => "$templatesDir/DTO/UpdateInput.tpl.php",
            'Output' => "$templatesDir/DTO/Output.tpl.php",
            'PermissionQuery' => "$templatesDir/Permission/PermissionQuery.tpl.php",
            'Resource' => "$templatesDir/Resource/Resource.tpl.php",
        ];
    }

    /**
     * Returns an array mapping template types to target file paths in the project.
     *
     * @param string $projectDir
     * @param string $name
     * @return array<string, string>
     */
    private function getFilesPathMap(
        string $projectDir,
        string $namespace,
        string $entity,
    ): array {

        $namespace = str_replace('\\' , '/', $namespace);

        return [
            'Controller' => "$projectDir/src/Api/Controller{$namespace}/{$entity}Controller.php",
            'CreateInput' => "$projectDir/src/Api/DTO{$namespace}/Create{$entity}Input.php",
            'UpdateInput' => "$projectDir/src/Api/DTO{$namespace}/Update{$entity}Input.php",
            'Output' => "$projectDir/src/Api/DTO{$namespace}/{$entity}Output.php",
            'PermissionQuery' => "$projectDir/src/Api/Permission{$namespace}/{$entity}PermissionQuery.php",
            'Resource' => "$projectDir/src/Api/Resource{$namespace}/{$entity}Resource.php",
        ];
    }
}

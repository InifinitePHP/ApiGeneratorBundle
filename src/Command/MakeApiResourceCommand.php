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
            ->addArgument('new-name', InputArgument::OPTIONAL, 'The name of the Ressource (default Entity)')
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
        $new_name_arg = $input->getArgument('new-name') ?? $name_arg;
        $force_arg = $input->getOption('force');

        if(!is_string($name_arg)) {
            throw new InvalidArgumentException('Argument name should be a string');
        }

        if(!is_string($new_name_arg)) {
            throw new InvalidArgumentException('Argument name should be a string');
        }

        if(!is_bool($force_arg)) {
            throw new InvalidArgumentException('Argument force should be a boolean');
        }

        $name_arg = str_replace('\\', '/', $name_arg);
        $new_name_arg = str_replace('\\', '/', $new_name_arg);

        $entityName = ltrim($name_arg, '/');
        $rssName = ltrim($new_name_arg, '/');
        $projectDir = $this->kernel->getProjectDir();

        $entityPath = "$projectDir/src/Entity/$entityName.php";
        $filesystem = new Filesystem();

        if (!$filesystem->exists($entityPath)) {
            $output->writeln("<error>Entity $entityName does not exist in $projectDir/src/Entity/</error>");
            return Command::FAILURE;
        }

        $this->generateFiles($filesystem, $projectDir, $entityName, $rssName, $force_arg, $output);

        $output->writeln("<info>✔ Resource $entityName generated successfully in $projectDir!</info>");
        return Command::SUCCESS;
    }

    /**
     * Generates API resource files from templates.
     *
     * @param Filesystem $filesystem
     * @param string $projectDir
     * @param string $entityName
     * @param string $rssName
     * @param bool $force Whether to overwrite existing files
     * @param OutputInterface $output
     */
    private function generateFiles(
        Filesystem $filesystem,
        string $projectDir,
        string $entityName,
        string $rssName,
        bool $force,
        OutputInterface $output
    ): void {

        $templatesDir = __DIR__ . '/../Templates';
        $templateTypes = [
            'Controller', 'Resource',
            'SearchInput', 'CreateInput', 'UpdateInput', 'Output', 
            'PermissionQuery', 'Voter', 'Policy'
        ];

        $rssParts = explode('/', $rssName);
        $rssBasename = array_pop($rssParts);
        $rssNamespace = $rssParts ? '\\' . implode('\\', $rssParts) : '';
        
        $entity = str_replace('/', '\\', $entityName);
        $route = $this->getRoute($rssBasename);
        $var = (new UnicodeString($rssBasename))->camel()->toString();

        $fileMap = $this->getFilesPathMap($projectDir, $rssNamespace, $rssBasename);
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

            $content = str_replace(
                [
                    '{{NAMESPACE}}',
                    '{{NAME}}',
                    '{{ROUTE}}',
                    '{{ENTITY}}',
                    '{{VAR}}',
                ], 
                [
                    $rssNamespace,
                    $rssBasename,
                    $route,
                    $entity,
                    $var
                ], 
                $content
            );

            $filesystem->dumpFile($filePath, $content);
            $output->writeln("<info>Generated: $filePath</info>");
        }
    }

    private function getRoute(
        string $rssBasename
    ): string {
        $route = (new UnicodeString($rssBasename))->snake()->replace('_', '-')->lower()->toString();

        if(!str_ends_with('s', $rssBasename)) {
            $route .= 's';
        }

        return $route;
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
            'Resource' => "$templatesDir/Resource/Resource.tpl.php",
            'SearchInput' => "$templatesDir/DTO/SearchInput.tpl.php",
            'CreateInput' => "$templatesDir/DTO/CreateInput.tpl.php",
            'UpdateInput' => "$templatesDir/DTO/UpdateInput.tpl.php",
            'Output' => "$templatesDir/DTO/Output.tpl.php",
            'PermissionQuery' => "$templatesDir/Permission/PermissionQuery.tpl.php",
            'Voter' => "$templatesDir/Permission/Voter.tpl.php",
            'Policy' => "$templatesDir/Permission/Policy.tpl.php",
        ];
    }

    /**
     * Returns an array mapping template types to target file paths in the project.
     *
     * @param string $projectDir
     * @param string $namespace
     * @param string $entity
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
            'Resource' => "$projectDir/src/Api/Resource{$namespace}/{$entity}Resource.php",
            'SearchInput' => "$projectDir/src/Api/DTO{$namespace}/Search{$entity}Input.php",
            'CreateInput' => "$projectDir/src/Api/DTO{$namespace}/Create{$entity}Input.php",
            'UpdateInput' => "$projectDir/src/Api/DTO{$namespace}/Update{$entity}Input.php",
            'Output' => "$projectDir/src/Api/DTO{$namespace}/{$entity}Output.php",
            'PermissionQuery' => "$projectDir/src/Api/Permission{$namespace}/{$entity}PermissionQuery.php",
            'Voter' => "$projectDir/src/Api/Permission{$namespace}/{$entity}Voter.php",
            'Policy' => "$projectDir/src/Api/Permission{$namespace}/{$entity}Policy.php",
        ];
    }
}

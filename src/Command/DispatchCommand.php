<?php

namespace FrpRiddimDispatcher\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class DispatchCommand
 * @package FrpRiddimDispatcher\Command
 */
class DispatchCommand extends Command
{
    protected function configure()
    {
        $this->setName('frp:riddim:dispatcher');
        $this->addArgument('input', InputArgument::REQUIRED);
        $this->addArgument('output', InputArgument::OPTIONAL);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $fs = new Filesystem();
         if (!$fs->exists($input->getArgument('input'))) {
             throw new FileNotFoundException(sprintf('Input %s, directory not exist', $input->getArgument('input')));
         }
        if (!$input->getArgument('output')) {
            $input->setArgument('output', $input->getArgument('input'));
        }
        if (!$fs->exists($input->getArgument('output'))) {
            throw new FileNotFoundException(sprintf('Output %s, directory not exist', $input->getArgument('output')));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = Finder::create();
        $fs = new Filesystem();
        foreach ($finder->name('*.mp3')->in($input->getArgument('input'))->files()->getIterator() as $file) {
            if (false === $riddimName = $this->getRiddimName($file)) {
                continue;
            }

            $destDir = $input->getArgument('output') . DIRECTORY_SEPARATOR . 'VA-'.$riddimName.'-'.date('Y');
            $destDir = $input->getArgument('output') . DIRECTORY_SEPARATOR . 'VA-'.$riddimName.'-2015';
            $destFile = $destDir . DIRECTORY_SEPARATOR . $file->getFilename();

            if ($file->getPathname() == $destFile) {
                continue;
            }

            $fs->mkdir($destDir);

            try {
                $fs->rename($file->getPathname(), $destFile);
                $output->writeln('<info>['.$riddimName.']</info> '.$file->getFilename().' >> <comment>'.$destDir.'</comment>');
                if ($input->getArgument('output') != $file->getPath() && Finder::create()->in($file->getPath())->files()->count() == 0) {
                    $fs = new Filesystem();
                    $output->writeln(sprintf('%s must be cleaned because is empty now', $file->getPath()));
                    $fs->remove($file->getPath());
                }
            } catch (IOException $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    private function getRiddimName(SplFileInfo $splFileInfo) {
        preg_match('/\((?P<name>\w*\_riddim)\)/i', $splFileInfo->getFilename(), $match);
        if (isset($match['name'])) {
            return $match['name'];
        }

        return false;
    }

}
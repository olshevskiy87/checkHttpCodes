<?php
namespace App\Commands;

use App\Check\Network;
use App\Exceptions\FileExceptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Check\File;

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';

class Check extends Command
{
    public static $availableMethods = ['GET', 'POST'];

    protected function configure()
    {
        $this->setName('checkCodes')
            ->setDescription('Check http codes some urls.')
            ->setHelp('.')
            ->addArgument('code', InputArgument::REQUIRED, 'Expected http code for this urls.')
            ->addArgument('urls', InputArgument::OPTIONAL, 'Using file with urls.')
            ->addArgument('method', InputArgument::OPTIONAL, 'Using http method.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $expectedCode = (int)$input->getArgument('code');
        $file = $input->getArgument('urls') ?? DEFAULT_FILE;

        try {
            $method = $input->getArgument('method') ?? DEFAULT_HTTP_METHOD;
            if (!in_array($method, self::$availableMethods)) {
                throw new \Exception('http method ' . $method . ' not available');
            }
            $urls = $this->getUrls($file);
            $network = new Network();
            $curlsInstance = $network->getCurlInstances($urls);
            $results = $network->execAll($curlsInstance);
        } catch (\Exception $e) {
            $output->writeln('error: '. $e->getMessage());
            exit(1);
        }

        $isProblem = false;
        foreach ($results as $url => $code) {
            if ($code != $expectedCode) {
                $output->writeln('problem: ' . $url . ', current: ' . $code . ', expected: ' . $expectedCode);
                $isProblem = true;
            } elseif ($output->isVerbose()) {
                $output->writeln('ok: ' . $url . ', current: ' . $code . ', expected: ' . $expectedCode);
            }
        }

        $output->writeln('spend time: ' . (microtime(true) - $start));
        if (!$isProblem) {
            $output->writeln('all is well');
            exit(0);
        } else {
            $output->writeln('there is a problem');
            exit(1);
        }
    }

    /**
     * @param string $file
     * @return array
     * @throws \Exception
     */
    private function getUrls(string $file): array
    {
        try {
            return (new File($file))->getUrls();
        } catch (FileExceptions $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
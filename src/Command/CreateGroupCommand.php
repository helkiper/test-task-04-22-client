<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateGroupCommand extends Command
{
    protected static $defaultName = 'group:create';
    protected static $defaultDescription = 'Create group';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Group name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $response = $this->client->request(
                'POST',
                'http://server/api/group',
                [
                    'json' => [
                        'name' => $input->getArgument('name')
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ]
            );


            $statusCode = $response->getStatusCode();
            $responseArray = $response->toArray(false);
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage()); //в реальности логировал бы эксепшн

            return Command::FAILURE;
        }

        $output->writeln(
            $statusCode == '201'
                ? "Created group id: {$responseArray['id']}"
                : sprintf('%d %s', $statusCode, $responseArray['message'])
        );

        return Command::SUCCESS;
    }
}

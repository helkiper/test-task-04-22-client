<?php

namespace App\Command;

use App\Dto\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetUserCommand extends Command
{
    protected static $defaultName = 'user:get';
    protected static $defaultDescription = 'Get user';

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
            ->addArgument('id', InputArgument::REQUIRED, 'User ID');
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
                'GET',
                sprintf('http://server/api/user/%d', $input->getArgument('id')),
                [
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
            $statusCode == '200'
                ? User::createFromArray($responseArray)
                : sprintf('%d %s', $statusCode, $responseArray['message'])
        );

        return Command::SUCCESS;
    }
}

<?php
declare(strict_types=1);


namespace App\Infrastructure\Adapter\Scraper;

use App\Infrastructure\Adapter\Persistence\ProjectPersistence;
use App\Infrastructure\Transformer\htmlProjectGalleryTransformer;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GalleryScraperCommand extends Command
{
    protected static $defaultName = 'app:gallery-scraper';

    const string DEFAULT_SORT = 'trendy'; //editorschoice, new, trendy, popular

    const string BASE_URL = "https://planner5d.com/gallery/floorplans?sort=%s&page=%s";
    const int DEFAULT_LIMIT = 3;

    private LoggerInterface $logger;
    private htmlProjectGalleryTransformer $projectTransformer;
    private ProjectPersistence $projectPersistence;


    public function __construct(
        LoggerInterface               $logger,
        htmlProjectGalleryTransformer $projectTransformer,
        ProjectPersistence            $projectPersistence,
    )
    {
        $this->logger = $logger;
        $this->projectTransformer = $projectTransformer;
        $this->projectPersistence = $projectPersistence;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Symfony command to import projects from the Planner 5D gallery')
            ->addArgument('sort', InputArgument::OPTIONAL, 'Sort order for the projects (editorschoice, new, trendy, popular)', self::DEFAULT_SORT)
            ->addArgument('pages', InputArgument::OPTIONAL, 'Number of pages to scrape (3)', self::DEFAULT_LIMIT);;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo "Processing ...\n";
        $start_time = microtime(true);

        try {
            $client = HttpClient::create();
            $scrapedProjects = [];

            $sort = $input->getArgument('sort');
            $limit = (int)$input->getArgument('pages');

            // Scrape the first (3) pages of projects
            for ($page = 1; $page <= $limit; $page++) {
                $url = sprintf(self::BASE_URL, $sort, $page);

                $request = $client->request('GET', $url);
                $html = $request->getContent();

                $crawler = new Crawler($html);

                //Projects list grid target
                $grid = $crawler->filter('#grid > div:first-child')->first();

                //Transform the html to data, remove the array_merge for a page -> items structure instead
                $scrapedProjects = array_merge($scrapedProjects, $this->projectTransformer->transform($grid));
            }

            //Persist the data
            if (count($scrapedProjects) > 0) {
                $count = $this->projectPersistence->persist($scrapedProjects);
            } else {
                $count = 0;
            }


            $end_time = microtime(true);
            $execution_time = $end_time - $start_time;
            $output->writeln(sprintf("Scraped %d projects from the Planner 5D gallery in %d seconds.", $count, $execution_time));

            return Command::SUCCESS;

        } catch (Exception $exception) {
            //TODO Log proper exception errors here...
            $this->logger->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}

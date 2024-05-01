<?php
declare(strict_types=1);


namespace App\Infrastructure\Adapter\Scraper;

use App\Domain\Entity\Project;
use App\Domain\Repository\CommonRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
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

    private CommonRepositoryInterface $projectRepository;
    private LoggerInterface $logger;

    public function __construct(
        CommonRepositoryInterface $repository,
        LoggerInterface           $logger
    )
    {
        $this->projectRepository = $repository;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Symfony command to import projects from the Planner 5D gallery');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $client = HttpClient::create();
            $scrapedProjects = [];

            // Scrape the first (3) pages of projects
            for ($page = 1; $page <= self::DEFAULT_LIMIT; $page++) {
                $url = sprintf(self::BASE_URL, self::DEFAULT_SORT, $page);

                $request = $client->request('GET', $url);
                $html = $request->getContent();

                $crawler = new Crawler($html);

                //Projects list grid target
                $grid = $crawler->filter('#grid > div:first-child')->first();


                //TODO Use a Service Instead ...
                //Transform the html to data, remove the array_merge for a page -> items structure instead
                $scrapedProjects = array_merge($scrapedProjects, $this->htmlTransformer($grid));
            }

            //Persist the data
            if (count($scrapedProjects) > 0) {
                //TODO Use a Service Instead ...
                $count = $this->persistProjects($scrapedProjects);
            }else{
                $count = 0;
            }

            $output->writeln('Scraped ' . $count . ' projects from the Planner 5D gallery.');

            return Command::SUCCESS;

        } catch (Exception $exception) {
            //TODO Log proper exception errors here...
            $this->logger->error($exception->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * @param Crawler $grid
     * @return array
     */
    private function htmlTransformer(Crawler $grid): array
    {
        $projects = [];
        //TODO Use a TransformerService instead to perform this operations

        foreach ($grid->children() as $child) {
            $node = new Crawler($child);

            $projectNode = $node->filter('div:first-child');
            $statisticsNode = $node->filter('div:nth-of-type(2)');

            //Empty elements discard
            if (!$projectNode->count() > 0 && !$statisticsNode->count() > 0) {
                continue;
            }

            //Selectors by CSS classes
            $title = $projectNode->filter('div > h3 > a');
            $projectUrl = $projectNode->filter('a');

            $userUrl = $statisticsNode->filter('a')->attr('href');
            $array = explode('/', rtrim($userUrl, '/'));
            $user = end($array);

            $hits = $statisticsNode->filter('span')->eq(0);
            $likes = $statisticsNode->filter('span')->eq(1);
            $comments = $statisticsNode->filter('span')->eq(2);


            //TODO Should use a DTO->toArray() instead
            $project = [
                'title' => $title->text(),
                'user' => $user ?? '',
                'url' => $projectUrl->attr('href'),
                'hits' => (int)$hits->text(),
                'statistics' => [
                    'likes' => $likes->text(),
                    'comments' => $comments->text(),
                ]
            ];

            $projects[] = $project;
        }


        return $projects;
    }

    /**
     * @param array $projects
     * @return int
     */
    private function persistProjects(array $projects): int
    {
        //TODO Use a ProjectServiceAdapter instead ...
        $count = 0;
        foreach ($projects as $element) {

            /** @var Project $project */
            $project = $this->projectRepository->findOneBy(['url' => $element['url']]);

            //Update an existing one or create a new one
            if (!$project) {
                $project = new Project();
            }

            //Set desired and existing attributes in the Entity
            foreach ($element as $attribute => $value) {
                $project = $this->projectRepository->update($attribute, $value, $project, false);
            }

            //Persist
            if ($this->projectRepository->save($project)) {
                $count++;
            }
        }

        return $count;
    }
}

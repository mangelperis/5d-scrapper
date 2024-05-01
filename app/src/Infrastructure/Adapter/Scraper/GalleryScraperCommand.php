<?php
declare(strict_types=1);


namespace App\Infrastructure\Adapter\Scraper;

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

    const string DEFAULT_SORT = 'trendy';

    const string BASE_URL = "https://planner5d.com/gallery/floorplans?sort=%s&page=%s";
    const int DEFAULT_LIMIT = 3;

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
        $client = HttpClient::create();
        $projects = [];

        // Scrape the first (3) pages of projects
        for ($page = 1; $page <= self::DEFAULT_LIMIT; $page++) {
            $url = sprintf(self::BASE_URL, self::DEFAULT_SORT, $page);

            $request = $client->request('GET', $url);
            $html = $request->getContent();

            $crawler = new Crawler($html);

            //Projects list grid
            $grid = $crawler->filter('#grid > div:first-child')->first();

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

                $project = [
                    'title' => $title->text(),
                    'user' => $user ?? '',
                    'url' => $projectUrl->attr('href'),
                    'hits' => $hits->text(),
                    'likes' => $likes->text(),
                    'comments' => $comments->text(),
                ];

                $projects[] = $project;
            }

        }

        // TODO: Save to the database


        $output->writeln('Scraped ' . count($projects) . ' projects from the Planner 5D gallery.');

        return Command::SUCCESS;
    }
}

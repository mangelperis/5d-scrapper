<?php
declare(strict_types=1);


namespace App\Application\Service;

use App\Domain\Entity\Project;
use App\Infrastructure\Adapter\Persistence\ProjectRepositoryDoctrineAdapter;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;


class ProjectService
{
    public function __construct(
        private LoggerInterface                  $logger,
        private ProjectRepositoryDoctrineAdapter $projectRepository,
    )
    {
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findAll(): array
    {
        try {
            return $this->projectRepository->findAll();

        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Get All Projects fail: %s", $exception->getMessage()));
            throw new Exception('Error while fetching All Projects');

        }
    }

    /**
     * @param int $projectId
     * @return Project|null
     * @throws Exception
     */
    public function findProjectById(int $projectId): ?Project
    {
        try {

            $project = $this->projectRepository->findOneBy(['id' => $projectId]);

            if (!$project) {
                throw new \InvalidArgumentException('Project not found.', Response::HTTP_NOT_FOUND);
            }


            //TODO Should use a DTO with the desired output values only
            return $project;

        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Get Project Detail fail: %s", $exception->getMessage()));
            throw new Exception('Error while fetching Project');
        }
    }

    /** RETURN img:base64 string
     * @param string $url
     * @return string
     * @throws Exception
     */
    public function generateQR(string $url): string
    {
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->size(300)
                ->margin(10)
                ->logoPath(__DIR__ . '/../../Infrastructure/UI/Templates/project/Planner_5D_logo.png')
                ->logoResizeToWidth(50)
                ->logoPunchoutBackground(true)
                ->labelText('QR URL')
                ->labelFont(new NotoSans(20))
                ->labelAlignment(LabelAlignment::Center)
                ->build();

            return $result->getDataUri();
        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Generate QR fail: %s", $exception->getMessage()));
            throw new Exception('Error while generating a QR');
        }


    }


    /**
     * @param Project $project
     * @return void
     * @throws Exception
     */
    public function increaseHitCount(Project $project): void
    {
        try {
            $this->projectRepository->incrementHitCount($project);
        } catch (Exception $exception) {
            $this->logger->error(sprintf("[SERVICE] Increase Hit fail: %s", $exception->getMessage()));
            throw new Exception('Error increasing hit counter');
        }
    }
}
<?php
declare(strict_types=1);


namespace App\Infrastructure\UI\Controller;
use App\Application\Service\ProjectService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectViewController extends AbstractController
{
    public function __construct(
        private ProjectService $projectService,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/projects', name: 'app_project_list', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $projects = $this->projectService->findAll();

            return $this->render('project/list.html.twig', [
                'title' => sprintf("List of %d projects", count($projects)),
                'projects' => $projects,
            ]);
        } catch (Exception $exception) {
            $this->logger->error("[API] Projects list error: {$exception->getMessage()}");
            //TODO Create Error Response Handler
            return new Response('Something went wrong');
        }

    }

    /**
     * @throws Exception
     */
    #[Route('/project/{projectId}', name: 'app_project_detail', methods: ['GET'])]
    public function projectDetail(int $projectId): Response
    {
        try {
            $project = $this->projectService->findProjectById($projectId);

            $QR = $this->projectService->generateQR($project->getUrl());

            //Hit count++
            $this->projectService->increaseHitCount($project);

            return $this->render('project/detail.html.twig', [
                'title' => $project->getTitle(),
                'project' => $project,
                'QR' => $QR
            ]);
        } catch (Exception $exception) {
            $this->logger->error("[API] Project detail error: {$exception->getMessage()}");
            //TODO Create Error Response Handler
            return new Response('Something went wrong');
        }
    }
}
<?php
declare(strict_types=1);


namespace App\Infrastructure\UI\Controller;
use App\Application\Service\ProjectService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectViewController extends AbstractController
{
    public function __construct(
        private ProjectService $projectService,
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/projects', name: 'app_project_list', methods: ['GET'])]
    public function index(): Response
    {
        $projects = $this->projectService->findAll();

        return $this->render('project/list.html.twig', [
            'title' => sprintf("List of %d projects", count($projects)),
            'projects' => $projects,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/project/{projectId}', name: 'app_project_detail', methods: ['GET'])]
    public function projectDetail(int $projectId): Response
    {
        $project = $this->projectService->findProjectById($projectId);

        $QR = $this->projectService->generateQR($project->getUrl());

        //Hit count++
        $this->projectService->increaseHitCount($project);

        return $this->render('project/detail.html.twig', [
            'title' => $project->getTitle(),
            'project' => $project,
            'QR' => $QR
        ]);
    }
}
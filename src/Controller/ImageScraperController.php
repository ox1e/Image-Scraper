<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ImageScraperService;

class ImageScraperController extends AbstractController
{
    private ImageScraperService $imageScraperService;

    public function __construct(ImageScraperService $imageScraperService)
    {
        $this->imageScraperService = $imageScraperService;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('image_scraper/index.html.twig');
    }

    #[Route('/scrape', name: 'scrape', methods: ['POST'])]
    public function scrape(Request $request): Response
    {
        $url = $request->request->get('url');

        // Проверка на пустоту URL
        if (empty($url)) {
            return $this->render('image_scraper/error.html.twig', [
                'error' => 'URL не может быть пустым',
            ]);
        }

        // Проверка на валидность URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->render('image_scraper/error.html.twig', [
                'error' => 'Некорректный URL',
            ]);
        }

        try {
            $data = $this->imageScraperService->scrapeImages($url);
        } catch (\Exception $e) {
            return $this->render('image_scraper/error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->render('image_scraper/results.html.twig', [
            'images' => $data['images'],
            'totalImages' => $data['totalImages'],
            'totalSize' => $data['totalSize'],
        ]);
    }
}


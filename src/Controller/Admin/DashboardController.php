<?php

namespace App\Controller\Admin;

use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        CatalogRepository $catalogRepository,
    ): Response {
        return $this->render('admin/dashboard/index.html.twig', [
            'productCount' => $productRepository->count([]),
            'categoryCount' => $categoryRepository->count([]),
            'catalogCount' => $catalogRepository->count([]),
        ]);
    }
}

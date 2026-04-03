<?php

namespace App\Controller\Public;

use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Service\CatalogAccessChecker;
use App\Service\CatalogQueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogViewController extends AbstractController
{
    #[Route('/{slug}/view', name: 'catalog_view')]
    public function view(
        string $slug,
        Request $request,
        CatalogRepository $repo,
        CatalogAccessChecker $checker,
        CatalogQueryBuilder $queryBuilder,
        PaginatorInterface $paginator,
        CategoryRepository $categoryRepo,
        TagRepository $tagRepo,
    ): Response {
        $catalog = $repo->findOneBy(['slug' => $slug, 'isPublished' => true]);
        if (!$catalog) {
            throw $this->createNotFoundException('Catalog not found.');
        }

        if (!$checker->isGranted($catalog)) {
            return $this->redirectToRoute('catalog_login', ['slug' => $slug]);
        }

        $userFilters = [
            'search' => $request->query->get('search', ''),
            'category' => $request->query->get('category', ''),
            'tag' => $request->query->get('tag', ''),
            'price_min' => $request->query->get('price_min', ''),
            'price_max' => $request->query->get('price_max', ''),
            'sort' => $request->query->get('sort', ''),
        ];

        $qb = $queryBuilder->buildQuery($catalog, $userFilters);

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            24
        );

        $templateDir = $catalog->getTemplate()->getDirectory();
        $templatePath = "catalog/themes/{$templateDir}/layout.html.twig";

        $data = [
            'catalog' => $catalog,
            'products' => $pagination,
            'pagination' => $pagination,
            'inputs' => $catalog->getInputConfigs(),
            'categories' => $categoryRepo->findBy([], ['name' => 'ASC']),
            'tags' => $tagRepo->findBy([], ['name' => 'ASC']),
            'filters' => $userFilters,
        ];

        // AJAX requests return just the product partial
        if ($request->query->get('ajax')) {
            return $this->render("catalog/themes/{$templateDir}/products.html.twig", $data);
        }

        return $this->render($templatePath, $data);
    }
}

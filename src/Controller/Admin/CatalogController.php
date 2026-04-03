<?php

namespace App\Controller\Admin;

use App\Entity\Catalog;
use App\Entity\CatalogAccess;
use App\Entity\CatalogAccessPassword;
use App\Entity\CatalogFilter;
use App\Entity\CatalogInputConfig;
use App\Entity\CatalogSort;
use App\Enum\AccessMode;
use App\Enum\FilterOperator;
use App\Form\CatalogType;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/catalogs')]
class CatalogController extends AbstractController
{
    #[Route('/', name: 'admin_catalog_index')]
    public function index(CatalogRepository $repo): Response
    {
        return $this->render('admin/catalog/index.html.twig', [
            'catalogs' => $repo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_catalog_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $catalog = new Catalog();
        $form = $this->createForm(CatalogType::class, $catalog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Create default access (public)
            $access = new CatalogAccess();
            $access->setMode(AccessMode::Public);
            $catalog->setAccess($access);

            $em->persist($catalog);
            $em->flush();
            $this->addFlash('success', 'Catalog created.');
            return $this->redirectToRoute('admin_catalog_edit', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/form.html.twig', [
            'form' => $form,
            'catalog' => $catalog,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_catalog_edit')]
    public function edit(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CatalogType::class, $catalog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catalog updated.');
            return $this->redirectToRoute('admin_catalog_edit', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/form.html.twig', [
            'form' => $form,
            'catalog' => $catalog,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_catalog_delete', methods: ['POST'])]
    public function delete(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $catalog->getId(), $request->request->get('_token'))) {
            $em->remove($catalog);
            $em->flush();
            $this->addFlash('success', 'Catalog deleted.');
        }
        return $this->redirectToRoute('admin_catalog_index');
    }

    #[Route('/{id}/filters', name: 'admin_catalog_filters', methods: ['GET', 'POST'])]
    public function filters(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $filtersData = $request->request->all('filters');

            // Remove existing filters
            foreach ($catalog->getFilters() as $filter) {
                $catalog->removeFilter($filter);
                $em->remove($filter);
            }

            // Add new filters
            if (is_array($filtersData)) {
                $position = 0;
                foreach ($filtersData as $fd) {
                    if (empty($fd['field'])) continue;
                    $filter = new CatalogFilter();
                    $filter->setField($fd['field']);
                    $filter->setOperator(FilterOperator::from($fd['operator'] ?? 'eq'));
                    $filter->setValue([$fd['value'] ?? '']);
                    $filter->setPosition($position++);
                    $catalog->addFilter($filter);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Filters updated.');
            return $this->redirectToRoute('admin_catalog_filters', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/filters.html.twig', [
            'catalog' => $catalog,
            'operators' => FilterOperator::cases(),
        ]);
    }

    #[Route('/{id}/sorts', name: 'admin_catalog_sorts', methods: ['GET', 'POST'])]
    public function sorts(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $sortsData = $request->request->all('sorts');

            foreach ($catalog->getSorts() as $sort) {
                $catalog->removeSort($sort);
                $em->remove($sort);
            }

            if (is_array($sortsData)) {
                $position = 0;
                foreach ($sortsData as $sd) {
                    if (empty($sd['field'])) continue;
                    $sort = new CatalogSort();
                    $sort->setField($sd['field']);
                    $sort->setDirection($sd['direction'] ?? 'asc');
                    $sort->setPosition($position++);
                    $catalog->addSort($sort);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Sorts updated.');
            return $this->redirectToRoute('admin_catalog_sorts', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/sorts.html.twig', [
            'catalog' => $catalog,
        ]);
    }

    #[Route('/{id}/inputs', name: 'admin_catalog_inputs', methods: ['GET', 'POST'])]
    public function inputs(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $inputsData = $request->request->all('inputs');

            foreach ($catalog->getInputConfigs() as $input) {
                $catalog->removeInputConfig($input);
                $em->remove($input);
            }

            if (is_array($inputsData)) {
                $position = 0;
                foreach ($inputsData as $id) {
                    if (empty($id['input_type'])) continue;
                    $config = new CatalogInputConfig();
                    $config->setInputType($id['input_type']);
                    $config->setLabel($id['label'] ?? null);
                    $config->setPosition($position++);
                    $catalog->addInputConfig($config);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Input configuration updated.');
            return $this->redirectToRoute('admin_catalog_inputs', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/inputs.html.twig', [
            'catalog' => $catalog,
        ]);
    }

    #[Route('/{id}/access', name: 'admin_catalog_access', methods: ['GET', 'POST'])]
    public function access(Catalog $catalog, Request $request, EntityManagerInterface $em): Response
    {
        $access = $catalog->getAccess();
        if (!$access) {
            $access = new CatalogAccess();
            $access->setMode(AccessMode::Public);
            $catalog->setAccess($access);
            $em->flush();
        }

        if ($request->isMethod('POST')) {
            $mode = AccessMode::from($request->request->get('mode', 'public'));
            $access->setMode($mode);

            // Handle passwords for password mode
            if ($mode === AccessMode::Password) {
                $newPassword = $request->request->get('new_password', '');
                $newLabel = $request->request->get('new_password_label', '');
                if ($newPassword) {
                    $pwd = new CatalogAccessPassword();
                    $pwd->setPasswordHash(password_hash($newPassword, PASSWORD_DEFAULT));
                    $pwd->setLabel($newLabel ?: null);
                    $access->addPassword($pwd);
                }

                // Remove passwords marked for deletion
                $removeIds = $request->request->all('remove_passwords');
                if (is_array($removeIds)) {
                    foreach ($access->getPasswords() as $pwd) {
                        if (in_array((string) $pwd->getId(), $removeIds, true)) {
                            $access->removePassword($pwd);
                            $em->remove($pwd);
                        }
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'Access settings updated.');
            return $this->redirectToRoute('admin_catalog_access', ['id' => $catalog->getId()]);
        }

        return $this->render('admin/catalog/access.html.twig', [
            'catalog' => $catalog,
            'access' => $access,
            'modes' => AccessMode::cases(),
        ]);
    }
}

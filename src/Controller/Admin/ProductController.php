<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductCustomFieldValue;
use App\Form\ProductType;
use App\Repository\CustomFieldDefinitionRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'admin_product_index')]
    public function index(
        Request $request,
        ProductRepository $repo,
        PaginatorInterface $paginator,
    ): Response {
        $qb = $repo->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        $search = $request->query->get('search', '');
        if ($search) {
            $qb->andWhere('p.name LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20
        );

        if ($request->query->get('ajax')) {
            return $this->render('admin/product/_list.html.twig', [
                'pagination' => $pagination,
            ]);
        }

        return $this->render('admin/product/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'admin_product_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CustomFieldDefinitionRepository $cfRepo,
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveCustomFields($product, $form, $cfRepo, $em);
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Product created.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_product_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $em,
        CustomFieldDefinitionRepository $cfRepo,
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveCustomFields($product, $form, $cfRepo, $em);
            $em->flush();
            $this->addFlash('success', 'Product updated.');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Product deleted.');
        }
        return $this->redirectToRoute('admin_product_index');
    }

    private function saveCustomFields(
        Product $product,
        $form,
        CustomFieldDefinitionRepository $cfRepo,
        EntityManagerInterface $em,
    ): void {
        $definitions = $cfRepo->findAll();
        foreach ($definitions as $def) {
            $fieldName = 'custom_' . $def->getSlug();
            if (!$form->has($fieldName)) {
                continue;
            }

            $value = $form->get($fieldName)->getData();
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif ($value !== null) {
                $value = (string) $value;
            }

            $cfv = $product->getCustomFieldValue($def);
            if (!$cfv) {
                $cfv = new ProductCustomFieldValue();
                $cfv->setDefinition($def);
                $product->addCustomFieldValue($cfv);
            }
            $cfv->setValue($value);
        }
    }
}

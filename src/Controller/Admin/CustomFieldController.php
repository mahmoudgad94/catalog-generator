<?php

namespace App\Controller\Admin;

use App\Entity\CustomFieldDefinition;
use App\Enum\CustomFieldType;
use App\Form\CustomFieldDefinitionType;
use App\Repository\CustomFieldDefinitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/custom-fields')]
class CustomFieldController extends AbstractController
{
    #[Route('/', name: 'admin_custom_field_index')]
    public function index(CustomFieldDefinitionRepository $repo): Response
    {
        return $this->render('admin/custom_field/index.html.twig', [
            'fields' => $repo->findBy([], ['position' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_custom_field_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $field = new CustomFieldDefinition();
        $form = $this->createForm(CustomFieldDefinitionType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $optionsText = $form->get('options')->getData();
            if ($field->getFieldType() === CustomFieldType::Select && $optionsText) {
                $field->setOptions(array_filter(array_map('trim', explode("\n", $optionsText))));
            }
            $em->persist($field);
            $em->flush();
            $this->addFlash('success', 'Custom field created.');
            return $this->redirectToRoute('admin_custom_field_index');
        }

        return $this->render('admin/custom_field/form.html.twig', [
            'form' => $form,
            'field' => $field,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_custom_field_edit')]
    public function edit(CustomFieldDefinition $field, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CustomFieldDefinitionType::class, $field);

        if ($field->getFieldType() === CustomFieldType::Select && $field->getOptions()) {
            $form->get('options')->setData(implode("\n", $field->getOptions()));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $optionsText = $form->get('options')->getData();
            if ($field->getFieldType() === CustomFieldType::Select && $optionsText) {
                $field->setOptions(array_filter(array_map('trim', explode("\n", $optionsText))));
            } else {
                $field->setOptions(null);
            }
            $em->flush();
            $this->addFlash('success', 'Custom field updated.');
            return $this->redirectToRoute('admin_custom_field_index');
        }

        return $this->render('admin/custom_field/form.html.twig', [
            'form' => $form,
            'field' => $field,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_custom_field_delete', methods: ['POST'])]
    public function delete(CustomFieldDefinition $field, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $field->getId(), $request->request->get('_token'))) {
            $em->remove($field);
            $em->flush();
            $this->addFlash('success', 'Custom field deleted.');
        }
        return $this->redirectToRoute('admin_custom_field_index');
    }
}

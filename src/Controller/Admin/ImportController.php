<?php

namespace App\Controller\Admin;

use App\Form\CsvImportType;
use App\Service\CsvImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'admin_import')]
    public function index(): Response
    {
        $form = $this->createForm(CsvImportType::class);
        return $this->render('admin/import/form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/import/upload', name: 'admin_import_upload', methods: ['POST'])]
    public function upload(Request $request, CsvImporter $importer): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvFile')->getData();
            $tempPath = sys_get_temp_dir() . '/' . uniqid('csv_import_') . '.csv';
            $file->move(dirname($tempPath), basename($tempPath));

            try {
                $preview = $importer->preview($tempPath);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Error parsing CSV: ' . $e->getMessage());
                return $this->redirectToRoute('admin_import');
            }

            // Store temp path in session for confirm step
            $request->getSession()->set('csv_import_path', $tempPath);

            return $this->render('admin/import/preview.html.twig', [
                'preview' => $preview,
            ]);
        }

        $this->addFlash('danger', 'Please upload a valid CSV file.');
        return $this->redirectToRoute('admin_import');
    }

    #[Route('/import/confirm', name: 'admin_import_confirm', methods: ['POST'])]
    public function confirm(Request $request, CsvImporter $importer): Response
    {
        $tempPath = $request->getSession()->get('csv_import_path');
        if (!$tempPath || !file_exists($tempPath)) {
            $this->addFlash('danger', 'Import session expired. Please upload the file again.');
            return $this->redirectToRoute('admin_import');
        }

        try {
            $result = $importer->execute($tempPath);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Import failed: ' . $e->getMessage());
            return $this->redirectToRoute('admin_import');
        } finally {
            @unlink($tempPath);
            $request->getSession()->remove('csv_import_path');
        }

        return $this->render('admin/import/result.html.twig', [
            'result' => $result,
        ]);
    }
}

<?php

namespace App\Controller\Public;

use App\Entity\CatalogAccessLog;
use App\Enum\AccessMode;
use App\Repository\CatalogRepository;
use App\Service\CatalogAccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogAccessController extends AbstractController
{
    #[Route('/{slug}', name: 'catalog_entry')]
    public function entry(
        string $slug,
        CatalogRepository $repo,
        CatalogAccessChecker $checker,
    ): Response {
        $catalog = $repo->findOneBy(['slug' => $slug, 'isPublished' => true]);
        if (!$catalog) {
            throw $this->createNotFoundException('Catalog not found.');
        }

        if ($checker->isGranted($catalog)) {
            return $this->redirectToRoute('catalog_view', ['slug' => $slug]);
        }

        return $this->redirectToRoute('catalog_login', ['slug' => $slug]);
    }

    #[Route('/{slug}/login', name: 'catalog_login')]
    public function login(
        string $slug,
        Request $request,
        CatalogRepository $repo,
        CatalogAccessChecker $checker,
        EntityManagerInterface $em,
    ): Response {
        $catalog = $repo->findOneBy(['slug' => $slug, 'isPublished' => true]);
        if (!$catalog) {
            throw $this->createNotFoundException('Catalog not found.');
        }

        $access = $catalog->getAccess();
        if (!$access || $access->getMode() === AccessMode::Public) {
            return $this->redirectToRoute('catalog_view', ['slug' => $slug]);
        }

        $error = null;

        if ($request->isMethod('POST')) {
            if ($access->getMode() === AccessMode::Password) {
                $password = $request->request->get('password', '');
                foreach ($access->getPasswords() as $pwd) {
                    if (password_verify($password, $pwd->getPasswordHash())) {
                        $checker->grantAccess($catalog, 'password_verified');
                        return $this->redirectToRoute('catalog_view', ['slug' => $slug]);
                    }
                }
                $error = 'Invalid password.';
            }

            if ($access->getMode() === AccessMode::Email) {
                $email = $request->request->get('email', '');
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Log access
                    $log = new CatalogAccessLog();
                    $log->setCatalog($catalog);
                    $log->setEmail($email);
                    $log->setIpAddress($request->getClientIp());
                    $em->persist($log);
                    $em->flush();

                    $checker->grantAccess($catalog, 'email_verified');
                    return $this->redirectToRoute('catalog_view', ['slug' => $slug]);
                }
                $error = 'Please enter a valid email address.';
            }
        }

        $template = match ($access->getMode()) {
            AccessMode::Password => 'catalog/access/password.html.twig',
            AccessMode::Email => 'catalog/access/email.html.twig',
            default => 'catalog/access/password.html.twig',
        };

        return $this->render($template, [
            'catalog' => $catalog,
            'error' => $error,
        ]);
    }
}

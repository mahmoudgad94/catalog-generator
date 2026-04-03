<?php

namespace App\Service;

use App\Entity\Catalog;
use App\Enum\AccessMode;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogAccessChecker
{
    public function __construct(private RequestStack $requestStack) {}

    public function isGranted(Catalog $catalog): bool
    {
        $access = $catalog->getAccess();
        if (!$access) {
            return true;
        }

        return match ($access->getMode()) {
            AccessMode::Public => true,
            AccessMode::Password => $this->hasSessionFlag($catalog, 'password_verified'),
            AccessMode::Email => $this->hasSessionFlag($catalog, 'email_verified'),
        };
    }

    public function grantAccess(Catalog $catalog, string $type): void
    {
        $session = $this->requestStack->getSession();
        $session->set('catalog_' . $catalog->getId() . '_access', $type);
    }

    private function hasSessionFlag(Catalog $catalog, string $expected): bool
    {
        $session = $this->requestStack->getSession();
        return $session->get('catalog_' . $catalog->getId() . '_access') === $expected;
    }
}

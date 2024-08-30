<?php

namespace App\Controller\Admin;

use App\Entity\DayOff;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class DayOffCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DayOff::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('dayOff')->setFormat('dd/MM/Y')
                ->setLabel('Jour de congé'),
        ];
    }
}

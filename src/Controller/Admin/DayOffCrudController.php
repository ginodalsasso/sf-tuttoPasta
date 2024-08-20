<?php

namespace App\Controller\Admin;

use App\Entity\DayOff;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            DateTimeField::new('dayOff')->setFormat('dd/MM/Y')
                ->setLabel('Jour de congé'),
        ];
    }
}

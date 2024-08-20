<?php

namespace App\Controller\Admin;

use App\Entity\Quote;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quote::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('reference'),
            DateTimeField::new('quoteDate'),
            TextField::new('customerName'),
            TextField::new('customerFirstName'),
            TextField::new('customerEmail'),
            AssociationField::new('appointments')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setCrudController(AppointmentCrudController::class),
        ];
    }
}

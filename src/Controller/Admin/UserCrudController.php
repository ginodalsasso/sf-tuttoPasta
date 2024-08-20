<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Appointment;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email'),
            TextField::new('username'),

            // // Utilisation de formatValue pour afficher les rendez-vous
            // AssociationField::new('appointments')
            //     ->formatValue(function ($value, $entity) {
            //         if ($entity instanceof \App\Entity\User) {
            //             // Récupère les rendez-vous
            //             $appointments = $entity->getAppointments();

            //             // Crée une liste de dates de début formatées
            //             $appointmentTimes = [];
            //             foreach ($appointments as $appointment) {
            //                 $appointmentTimes[] = $appointment->getStartDate()->format('d/m/Y H:i');
            //             }

            //             // Retourne les dates sous forme de chaîne séparée par des virgules
            //             return implode(', ', $appointmentTimes);
            //         }

            //         return $value;
            //     })
            //     ->setLabel('Rendez-vous'),
            // Utilisation de CollectionField pour afficher la liste des rendez-vous
            CollectionField::new('appointments')
                ->setEntryType(TextField::class) // Définit le type des champs individuels
                ->setLabel('Rendez-vous')
                ->formatValue(function ($appointments) {
                    // Crée une liste de dates de début formatées
                    $appointmentTimes = [];
                    foreach ($appointments as $appointment) {
                        $appointmentTimes[] = $appointment->getStartDate()->format('d/m/Y H:i');
                    }

                    // Retourne les dates sous forme de liste
                    return implode(' - ', $appointmentTimes);
                }),
        ];
    }
}

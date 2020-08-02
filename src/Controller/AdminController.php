<?php

namespace App\Controller;

use App\Entity\Expenses;
use App\Repository\ExpensesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\NotNull;

class AdminController extends AbstractController
{
    public function dashboard(Request $request, EntityManagerInterface $em, ExpensesRepository $repo, UserRepository $userRepo)
    {
        $expensesForm = $this->createFormBuilder()
            ->add('insurance', NumberType::class, [
                'attr' => [
                    'placeholder' => 'Amount',
                    'class' => 'form-control',
                    'id' => 'input-insurance'
                ]
            ])
            ->add('utility', NumberType::class, [
                'attr' => [
                    'placeholder' => 'Amount',
                    'class' => 'form-control',
                    'id' => 'input-utility'
                ]
            ])
            ->add('maintenance', NumberType::class, [
                'attr' => [
                    'placeholder' => 'Amount',
                    'class' => 'form-control',
                    'id' => 'input-maintenance'
                ]
            ])
            ->getForm()
        ;

        $expensesForm->handleRequest($request);

        if ($expensesForm->isSubmitted() && $expensesForm->isValid()) {
            $newRecord = new Expenses();
            $data = $expensesForm->getData();

            $newRecord->setInsurance($data['insurance']);
            $newRecord->setUtility($data['utility']);
            $newRecord->setMaintenance($data['maintenance']);
            $newRecord->setMonth(new \DateTime());

            $em->persist($newRecord);
            $em->flush();

            return $this->redirectToRoute('app_admin_dashboard');
        }

        $insuranceAmount = $repo->findSumInsurance()[0][1] == !null ? $insuranceAmount = $repo->findSumInsurance()[0][1] : 0;
        $utilityAmount = $repo->findSumUtility()[0][1] == !null ? $utilityAmount = $repo->findSumUtility()[0][1] : 0;
        $maintenanceAmount = $repo->findSumMaintenance()[0][1] == !null ? $maintenanceAmount = $repo->findSumMaintenance()[0][1] : 0;
        $salaryAmount = $userRepo->findSumSalary()[0][1] == !null ? $salaryAmount = $userRepo->findSumSalary()[0][1] : 0;
        $cnssAmount = $userRepo->findSumCNSS()[0][1] == !null ? $userRepo->findSumCNSS()[0][1] : 0;
        $totalAmount = $insuranceAmount 
            + $utilityAmount
            + $maintenanceAmount
            + $salaryAmount
            + $cnssAmount > 0 ? $insuranceAmount 
            + $utilityAmount
            + $maintenanceAmount
            + $salaryAmount
            + $cnssAmount : 1; 
        ;

        return $this->render('admin/dashboard.html.twig', [
            'expensesForm' => $expensesForm->createView(),
            'insuranceAmount' => $insuranceAmount,
            'utilityAmount' => $utilityAmount,
            'maintenanceAmount' => $maintenanceAmount,
            'salaryAmount' => $salaryAmount,
            'cnssAmount' => $cnssAmount,
            'totalAmount' => $totalAmount
        ]);
    }
}

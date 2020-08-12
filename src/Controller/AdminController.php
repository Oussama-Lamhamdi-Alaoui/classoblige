<?php

namespace App\Controller;

use App\Entity\Cheque;
use App\Entity\Expenses;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Repository\ChequeRepository;
use App\Repository\ExpensesRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminController extends AbstractController
{
    public function dashboard(Request $request, 
        EntityManagerInterface $em, 
        ExpensesRepository $repo, 
        UserRepository $userRepo,
        ChequeRepository $chequeRepo,
        ItemRepository $itemRepo,
        OrderRepository $orderRepo)
    {
        $itemsInStock = $itemRepo->findSumStock()[0][1];

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
        $totalEarnings = $orderRepo->findSumEarnings()[0][1] == !null ? floatval($orderRepo->findSumEarnings()[0][1]) : 0;
        
        $chequesList = $chequeRepo->findAll();
        $employeesList = $userRepo->findByRole('ROLE_USER_EMPLOYEE');

        return $this->render('admin/dashboard.html.twig', [
            'itemsInStock' => $itemsInStock,
            'expensesForm' => $expensesForm->createView(),
            'insuranceAmount' => $insuranceAmount,
            'utilityAmount' => $utilityAmount,
            'maintenanceAmount' => $maintenanceAmount,
            'salaryAmount' => $salaryAmount,
            'cnssAmount' => $cnssAmount,
            'totalAmount' => $totalAmount,
            'chequesList' => $chequesList,
            'employeesList' => $employeesList,
            'totalEarnings' => $totalEarnings
        ]);
    }

    public function addCheque(Request $request, EntityManagerInterface $em) {
        $chequeForm = $this->createFormBuilder()
            ->add('uuid', NumberType::class, [
                'attr' => [
                    'placeholder' => '1234',
                    'class' => 'form-control',
                    'id' => 'input-uuid'
                ]
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Username',
                    'class' => 'form-control',
                    'id' => 'input-username'
                ]
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Address',
                    'class' => 'form-control',
                    'id' => 'input-address'
                ]
            ])
            ->add('beneficiary', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Beneficiary',
                    'class' => 'form-control',
                    'id' => 'input-beneficiary'
                ]
            ])
            ->add('amount', NumberType::class, [
                'attr' => [
                    'placeholder' => 'Enter Amount',
                    'class' => 'form-control',
                    'id' => 'input-amount'
                ]
            ])
            ->add('purpose', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Purpose',
                    'class' => 'form-control',
                    'id' => 'input-purpose'
                ]
            ])
            ->add('scan', FileType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document'
                    ])
                ],
                'attr' => [
                    'class' => 'custom-file-input form-control',
                    'id' => 'input-scan'
                ]
            ])
            ->getForm()
        ;

        $chequeForm->handleRequest($request);

        if($chequeForm->isSubmitted() && $chequeForm->isValid()) {
            $newCheque = new Cheque();
            $data = $chequeForm->getData();
            $scanFile = $chequeForm->get('scan')->getData();
            
            $newCheque->setUuid($data['uuid']);
            $newCheque->setName($data['name']);
            $newCheque->setAddress($data['address']);
            $newCheque->setBeneficiary($data['beneficiary']);
            $newCheque->setAmount($data['amount']);
            $newCheque->setPurpose($data['purpose']);
            
            $newFilename = 'scan-'.bin2hex(openssl_random_pseudo_bytes(11)).'.'.$scanFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $scanFile->move(
                    $this->getParameter('upload_dir_cheques'),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $newCheque->setScan($newFilename);

            $em->persist($newCheque);
            $em->flush();

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/addcheque.html.twig', [
            'chequeForm' => $chequeForm->createView()
        ]);
    }

    public function deleteCheque(Cheque $cheque, EntityManagerInterface $em): Response {
        $filename = $cheque->getScan();

        $filesystem = new Filesystem();
        $filesystem->remove('uploads/cheques/'.$filename);

        $em->remove($cheque);
        $em->flush();
        
        return $this->redirectToRoute('app_admin_dashboard');
    }
    
    public function editEmployee(User $employee, Request $request, EntityManagerInterface $em): Response {
        $employeeForm = $this->createFormBuilder($employee)
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-email'
                ]
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-name'
                ]
            ])
            ->add('phone', TelType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-phone'
                ]
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-address'
                ]
            ])
            ->add('zip', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-zip'
                ]
            ])
            ->add('salary', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-salary'
                ]
            ])
            ->add('cnss', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'input-cnss'
                ]
            ])
            ->getForm()
        ;
        
        $employeeForm->handleRequest($request);

        if($employeeForm->isSubmitted() && $employeeForm->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('admin/editemployee.html.twig', [
            'employeeForm' => $employeeForm->createView(),
            'employee' => $employee
        ]);
    }

    public function deleteEmployee(User $employee, EntityManagerInterface $em): Response {
        $em->remove($employee);
        $em->flush();
        
        return $this->redirectToRoute('app_admin_dashboard');
    }
}

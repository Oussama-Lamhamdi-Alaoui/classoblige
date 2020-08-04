<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function signUp(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder) : Response {
        $signUpForm = $this->createFormBuilder()
            ->add('name', NumberType::class, [
                'attr' => [
                    'placeholder' => 'Enter Name',
                    'class' => 'form-control',
                    'id' => 'input-name'
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'example@example.com',
                    'class' => 'form-control',
                    'id' => 'input-email'
                ]
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Username',
                    'class' => 'form-control',
                    'id' => 'input-username'
                ]
            ])
            ->add('pass', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Enter Password',
                    'class' => 'form-control',
                    'id' => 'input-password'
                ]
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter Full Address',
                    'class' => 'form-control',
                    'id' => 'input-address'
                ]
            ])
            ->add('phone', TelType::class, [
                'attr' => [
                    'placeholder' => '612341234',
                    'class' => 'form-control',
                    'id' => 'input-phone',
                    'maxlength' => '9',
                    'pattern' => '[0-9]{9}'
                ]
            ])
            ->add('zip', NumberType::class, [
                'attr' => [
                    'placeholder' => '51000',
                    'class' => 'form-control',
                    'id' => 'input-zip'
                ]
            ])
            ->getForm()
        ;
        
        $signUpForm->handleRequest($request);

        if ($signUpForm->isSubmitted() && $signUpForm->isValid()) {
            $newUser = new User();
            $data = $signUpForm->getData();

            $newUser->setEmail($data['email']);
            $newUser->setName($data['name']);
            $newUser->setPassword($encoder->encodePassword($newUser, $data['pass']));
            $newUser->setAddress($data['address']);
            $newUser->setPhone($data['phone']);
            $newUser->setZIP($data['zip']);

            $em->persist($newUser);
            $em->flush();

            return $this->redirectToRoute('app_signup');
        }

        return $this->render('security/signup.html.twig', [
            'signUpForm' => $signUpForm->createView()
        ]);
    }
}

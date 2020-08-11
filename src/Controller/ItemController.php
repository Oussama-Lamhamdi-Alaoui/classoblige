<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\Order;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;

class ItemController extends AbstractController
{
    public function menPolos(ItemRepository $repo)
    {
        $poloItemList = $repo->findByTypeInStock('menpolo');

        return $this->render('item/men/polos.html.twig', [
            'poloItemList' => $poloItemList
        ]);
    }

    public function menShirts(ItemRepository $repo)
    {
        $shirtItemList = $repo->findByTypeInStock('menshirt');

        return $this->render('item/men/shirts.html.twig', [
            'shirtItemList' => $shirtItemList
        ]);
    }

    public function menPants(ItemRepository $repo)
    {
        $pantsItemList = $repo->findByTypeInStock('menpants');

        return $this->render('item/men/pants.html.twig', [
            'pantsItemList' => $pantsItemList
        ]);
    }

    public function viewCart(Request $request, ItemRepository $repo, UserInterface $user, EntityManagerInterface $em) {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $itemsInCart = [];
        $total = 0.00;
        
        foreach ($cart as $id => $qty) {
            $item = $repo->find($id);
            $total += $item->getItemPrice() * $qty;
            array_push($itemsInCart, $item);
        }

        $form = $this->createFormBuilder()
            ->add('checkout', SubmitType::class, [
                'label' => 'Check Out',
                'attr' => [
                    'class' => 'btn btn-success float-right',
                    'onclick' => 'javascript:return confirm("Are you sure you want to checkout?")'
                ]
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $newOrder = new Order;
            $qtyArray = [];

            foreach ($cart as $id => $qty) {
                $item = $repo->find($id);
                $newOrder->addItem($item);
                $qtyArray[$id] = $qty;
            }

            $newOrder->setDate(new \DateTime());
            $newOrder->setQuantities($qtyArray);
            $newOrder->setStatus(False);
            $newOrder->setClientId($user->getId());
            $newOrder->setTotal($total);

            $em->persist($newOrder);
            $em->flush();
            
            $session->remove('cart');

            return $this->redirectToRoute('app_cart_payment', ['id' => $newOrder->getId()]);
        }

        return $this->render('item/cart.html.twig', [
            'itemsInCart' => $itemsInCart,
            'cart' => $cart,
            'total' => $total,
            'form' => $form->createView()
        ]);
    }

    public function addToCart($id, $qty, Request $request) {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if(isset($cart[$id])) {
            $cart[$id] += intval($qty);
        }
        else {
            $cart[$id] = intval($qty);
        }
        
        $session->set('cart', $cart);
    
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    public function removeFromCart($id, Request $request) {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if(!empty($cart[$id])) {
            unset($cart[$id]);
        }
        
        $session->set('cart', $cart);
    
        return $this->redirectToRoute('app_cart_view');
    }

    public function payment($id, Request $request, OrderRepository $repo, UserRepository $userRepo, ItemRepository $itemRepo, EntityManagerInterface $em) {
        $total = $repo->find($id)->getTotal();
        $orderQty = $repo->find($id)->getQuantities();

        $items = $itemRepo->createQueryBuilder('i')
            ->select('o.id as orderId, i.id as itemId')
            ->innerJoin('i.orders', 'o')
            ->where('o.id = :order_id')
            ->setParameter('order_id', $id)
            ->getQuery()
            ->getResult()
        ;

        $cashForm = $this->createFormBuilder()
            ->add('submit', SubmitType::class, [
                'label' => 'Validate',
                'attr' => [
                    'class' => 'btn btn-success',
                    'onclick' => 'javascript:return confirm("Are you sure you want to validate?")'
                ]
            ])
            ->getForm()
        ;        

        $cashForm->handleRequest($request);

        if ($cashForm->isSubmitted()) {
            if ($total < 6000) {
                $repo->find($id)->setStatus(True);
                $repo->find($id)->setMethod('cash');

                foreach ($items as $key => $value) {
                    $item = $itemRepo->find($value['itemId']);
                    $item->setItemStock($item->getItemStock() - $orderQty[$value['itemId']]);
                }
                
                $cartItems = [];

                foreach ($items as $key => $value) {
                    $cartItems[$key]['id'] = $itemRepo->find($value['itemId'])->getItemName();
                    $cartItems[$key]['qty'] = $orderQty[$value['itemId']];
                }

                $receiptFileName = $this->generateReceipt($repo->find($id), 'cash', $userRepo, $cartItems, $total);
                $repo->find($id)->setReceipt($receiptFileName);

                $em->flush();

                return $this->redirectToRoute('app_cart_success', ['id' => $id]);
            }
            else {
                $referer = $request->headers->get('referer');
                return $this->redirect($referer);
            }
        }

        return $this->render('item/payment.html.twig', [
            'cashForm' => $cashForm->createView(),
            'total' => $total
        ]);
    }

    public function success($id, OrderRepository $repo) {
        return $this->render('item/success.html.twig', [
            'receipt' => $repo->find($id)->getReceipt()
        ]);
    }

    private function generateReceipt(Order $order, $method, UserRepository $repo, $cart, $total) {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $clientName = $repo->find($order->getClientId())->getName();
        
        $html = $this->renderView('item/receipt.html.twig', [
            'method' => $method,
            'clientName' => $clientName,
            'cart' => $cart,
            'total' => $total,
            'orderId' => $order->getId(),
            'orderDate' => $order->getDate()
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $output = $dompdf->output();

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/receipts/';
        
        $pdfFileName = 'receipt-'.bin2hex(openssl_random_pseudo_bytes(11)). '.pdf';
        $pdfFilepath =  $publicDirectory . $pdfFileName;
        
        file_put_contents($pdfFilepath, $output);
        return $pdfFileName;
    } 
}



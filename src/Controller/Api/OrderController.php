<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\User;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    private TokenDecoder $tokenDecoder;

    public function __construct(TokenDecoder $tokenDecoder) {
        $this->tokenDecoder = $tokenDecoder;
    }

    #[Route('/api/order', name: 'app_order', methods: ['POST'])]
    public function index(Request $request,
    EntityManagerInterface $entityManager,
    MailerInterface $mailer): JsonResponse
    {
        try {
            $user = $this->tokenDecoder->decodeToken($request);
    
            /**
             * @var object
             */
            $datas = json_decode($request->getContent());
    
            if (! isset($datas->cart_id)) {
                return $this->json(['error' => 'Parameters expected not provided'], 401);
            }
    
            $cart = $entityManager->getRepository(Cart::class)->find($datas->cart_id);
    
            if (! $cart) {
                return $this->json(['error' => 'Invalid cart ID provided'], 401);
            }
    
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $user['username']]);
    
            if ($user === null) {
                return $this->json(['error' => 'Invalid user'], 401);
            }

            if ($cart->getUser()->getId() != $user->getId()) {
                return $this->json(['error' => 'Can\'t access cart'], 401);
            }
    
            $cart_contents = $cart->getCartContents();
    
            if ($cart_contents->isEmpty()) {
                return $this->json(['error' => 'Empty cart content'], 401);
            }
    
            $total = 0;
            $order_mail = null;
    
            foreach ($cart_contents as $cart_content) {
                $product_total = $cart_content->getProduct()->getPrice() * $cart_content->getQuantity();
                $total += $product_total;
    
                $order_mail .= $cart_content->getQuantity().' '.$cart_content->getProduct()->getName().' = '.$product_total."€ \n";
            }
    
            $shipping_cost = ($total < 20) ? 5 : 0;
    
            if ($shipping_cost !== 0) {
                $total += $shipping_cost;
                $order_mail .= "\nFrais de port : ".$shipping_cost."€";
            }
    
            $order_mail .= "\nTotal : ".$total."€";
    
            $email = (new Email())
                ->from('dev.trope@gmail.com')
                ->to('quentin.schifferle@gmail.com')
                ->subject('Récapitulatif de votre commande')
                ->text($order_mail);
    
            $mailer->send($email);
    
            return $this->json(['success' => 'Order completed']);
        }
        catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }
}

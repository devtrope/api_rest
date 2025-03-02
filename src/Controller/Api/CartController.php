<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Entity\User;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    private TokenDecoder $tokenDecoder;

    public function __construct(TokenDecoder $tokenDecoder) {
        $this->tokenDecoder = $tokenDecoder;
    }

    #[Route('/api/cart', name: 'app_cart', methods: ['POST'])]
    public function append(Request $request,
    EntityManagerInterface $entityManager,
    SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->tokenDecoder->decodeToken($request);
    
            /**
             * @var object
             */
            $datas = json_decode($request->getContent());
    
            if (! isset($datas->product_id) || ! isset($datas->quantity)) {
                return $this->json(['error' => 'Parameters expected not provided'], 401);
            }
    
            $product_id = $datas->product_id;
            $quantity = $datas->quantity;
    
            $product = $entityManager->getRepository(Product::class)->find($product_id);
    
            if (! $product) {
                return $this->json(['error' => 'Invalid product id'], 401);
            }
    
            if (! is_numeric($quantity) || $quantity === 0) {
                return $this->json(['error' => 'Invalid quantity provided'], 401);
            }
    
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $user['username']]);
            
            if ($user === null) {
                return $this->json(['error' => 'Invalid user'], 401);
            }

            //Creation of the cart if it doesn't exist
            //because we'll need it to add the content
            if ($user->getCart() === null) {
                $cart = new Cart;
                $cart->setUser($user);
                $user->setCart($cart);
                $entityManager->persist($user);
                $entityManager->flush();
            }
    
            //A CartContent line must be unique with a product and a user
            //so if it already exists, we add the quantity to the quantity in the cart.
            //If it doesn't exist, we just add a new line
            $cart_content = $entityManager->getRepository(CartContent::class)->findOneBy([
                'cart' => $user->getCart()->getId(),
                'product' => $product->getId()
            ]);
    
            if (! $cart_content) {
                $content = new CartContent;
                $content->setCart($user->getCart());
                $content->setProduct($product);
                $content->setQuantity($quantity);
            }
            else {
                $content = $cart_content;
                $content->setQuantity($content->getQuantity() + $quantity);
            }
    
            $entityManager->persist($content);
            $entityManager->flush();
    
            $cart_data = $serializer->serialize($content, 'json', ['groups' => 'getCarts']);
    
            return $this->json(['cart' => json_decode($cart_data)]);
        }
        catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }
}

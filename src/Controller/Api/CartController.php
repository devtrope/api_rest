<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    #[Route('/api/cart', name: 'app_cart', methods: ['POST'])]
    public function append(Request $request,
    JWTEncoderInterface $JWTEncoder, 
    EntityManagerInterface $entityManager,
    SerializerInterface $serializer): JsonResponse
    {
        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');

        $token = $extractor->extract($request);

        if (! $token) {
            return $this->json(['error' => 'No token provided'], 401);
        }

        $user = $JWTEncoder->decode($token);

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

        //Creation of the cart if it doesn't exist
        //because we'll need it to add the content
        if ($user->getCart() === null) {
            $cart = new Cart;
            $cart->setUser($user);
            $user->setCart($cart);
            $entityManager->persist($user);
            $entityManager->flush();
        }

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

        $cart_data = $serializer->serialize($user->getCart()->getCartContents(), 'json', ['groups' => 'getCarts']);

        return $this->json(['cart' => json_decode($cart_data)]);
    }
}

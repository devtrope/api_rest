<?php

namespace App\Controller\Api;

use App\Entity\Cart;
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

        if (! is_numeric($quantity)) {
            return $this->json(['error' => 'Invalid quantity provided'], 401);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $user['username']]);

        //Checking if there's already a line with this product and this user so we update the quantity
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['product' => $product->getId(), 'user' => $user->getId()]);
        if (! $cart) {
            $cart = new Cart;
            $cart->setProduct($product);
            $cart->setUser($user);
            $cart->setQuantity($quantity);
        }
        else {
            $cart->setQuantity($cart->getQuantity() + $quantity);
        }

        $entityManager->persist($cart);
        $entityManager->flush();

        $user_cart = $entityManager->getRepository(Cart::class)->findBy(['user' => $user->getId()]);
        $cart_data = $serializer->serialize($user_cart, 'json', ['groups' => 'getCarts']);

        return $this->json(['cart' => json_decode($cart_data)]);
    }
}

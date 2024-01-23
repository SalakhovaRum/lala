<?php

namespace App\Controller;

use App\Entity\ShopCart;
use App\Repository\ShopCartRepository;
use App\Repository\ShopItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private $entityManager;
    private $itemsRepository;
    public function __construct(EntityManagerInterface $entityManager, ShopItemsRepository $itemsRepository)
    {
        $this->entityManager = $entityManager;
        $this->itemsRepository = $itemsRepository;
    }
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'title' => 'IndexController',
        ]);
    }
    #[Route('/shop/list', name: 'app_shopList')]
    public function shopList(): Response
    {
        $items = $this->itemsRepository->findAll();
        return $this->render('index/shopList.html.twig', [
            'title' => 'SHOP LIST',
            'items' => $items,
        ]);
    }
    #[Route('/shop/item/{id<\d+>}', name: 'app_shopItem')]
    public function shopItem(int $id, SessionInterface $session): Response
    {
        $shopItem = $this->itemsRepository->find($id);
        if (!$shopItem) {
            throw $this->createNotFoundException('Товар не найден');
        }
        $sessionId = $session->getId();
        return $this->render('index/shopItem.html.twig', [
            'title' => 'SHOP ITEM ' . $id,
            'description' => $shopItem->getDefcription(),
            'price' => $shopItem->getPrice(),
            'id' => $id,
            'sessionId' => $sessionId,
            'shopItem' => $shopItem,
        ]);
    }

    #[Route('/shop/cart', name: 'app_shopCart')]
    public function shopCart(ShopCartRepository $cartRepository): Response
    {
        $items = $cartRepository->findAll();  // Получаем все товары из корзины без учета сессии

        return $this->render('index/shopCart.html.twig', [
            'title' => 'Корзина',
            'items' => $items,
        ]);
    }


    #[Route('/shop/cart/add/{id<\d+>}/{sessionId}', name: 'app_shopCartAdd', requirements: ['sessionId' => '.+'])]
    public function shopCartAdd(int $id, string $sessionId): Response
    {
        $shopItem = $this->itemsRepository->find($id);
        if (!$shopItem) {
            throw $this->createNotFoundException('Товар не найден');
        }
        $existingCartItem = $this->entityManager->getRepository(ShopCart::class)->findOneBy([
            'shopItem' => $shopItem,
            'sessionId' => $sessionId,
        ]);
        if ($existingCartItem) {
            $existingCartItem->setCount($existingCartItem->getCount() + 1);
        } else {
            $shopCart = new ShopCart();
            $shopCart->setShopItem($shopItem);
            $shopCart->setCount(1);
            $shopCart->setSessionId($sessionId);
            $this->entityManager->persist($shopCart);
        }

        $this->entityManager->flush();

        // После добавления товара в корзину, перенаправляем на страницу корзины
        return $this->redirectToRoute('app_shopCart');
    }
}
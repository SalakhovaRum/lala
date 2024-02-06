<?php

namespace App\Controller;

use App\Entity\ShopCart;
use Psr\Log\LoggerInterface;
use App\Entity\ShopOrder;
use App\Form\OrderFormType;
use App\Repository\ShopCartRepository;
use App\Repository\ShopItemsRepository;
use Symfony\Component\HttpFoundation\Request;
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
            'title' => $shopItem->getTitle(),
            'description' => $shopItem->getDefcription(), // Пожалуйста, исправьте на getDescription()
            'price' => $shopItem->getPrice(),
            'id' => $id,
            'sessionId' => $sessionId,
            'shopItem' => $shopItem,
        ]);
    }


    #[Route('/shop/cart', name: 'app_shopCart')]
    public function shopCart(ShopCartRepository $cartRepository): Response
    {
        $items = $cartRepository->findAll();

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

        return $this->redirectToRoute('app_shopCart');
    }

    #[Route('/shop/order', name: 'app_shopOrder')]
    public function shopOrder(Request $request, EntityManagerInterface $em, SessionInterface $session, ShopCartRepository $cartRepository): Response
    {
        $shopOrder = new ShopOrder();
        $form = $this->createForm(OrderFormType::class, $shopOrder);

        $orderPlaced = false;
        $itemsInCart = $cartRepository->findBy(['sessionId' => $session->getId()]);

        $cartEmpty = empty($itemsInCart);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$cartEmpty) {
            $shopOrder = $form->getData();
            if ($shopOrder instanceof ShopOrder) {
                $sessionId = $session->getId();
                $shopOrder->setStatus(ShopOrder::STATUS_NEW_ORDER);
                $shopOrder->setSessionId($sessionId);
                $em->persist($shopOrder);
                $em->flush();
                $session->migrate();
                $orderPlaced = true;
            }
        }

        return $this->render(
            'index/shopOrder.html.twig',
            [
                'title' => 'Оформление заказа',
                'form' => $form->createView(),
                'orderPlaced' => $orderPlaced,
                'cartEmpty' => $cartEmpty,
            ]
        );
    }

    #[Route('/shop/cart/remove/{id<\d+>}/{sessionId}', name: 'app_shopCartRemove', requirements: ['sessionId' => '.+'])]
    public function shopCartRemove(int $id, string $sessionId, LoggerInterface $logger): Response
    {
        $logger->info('Debugging...');

        $shopItem = $this->itemsRepository->find($id);
        if (!$shopItem) {
            throw $this->createNotFoundException('Товар не найден');
        }

        $existingCartItem = $this->entityManager->getRepository(ShopCart::class)->findOneBy([
            'shopItem' => $shopItem,
            'sessionId' => $sessionId,
        ]);

        if ($existingCartItem) {
            $logger->info('Existing cart item found. Count before: ' . $existingCartItem->getCount());

            if ($existingCartItem->getCount() > 1) {
                $existingCartItem->setCount($existingCartItem->getCount() - 1);
            } else {
                $this->entityManager->remove($existingCartItem);
            }

            $this->entityManager->flush();

            $logger->info('Count after: ' . $existingCartItem->getCount());
        } else {
            $logger->info('No existing cart item found for product ' . $id . ' and session ' . $sessionId);
        }

        // После изменения базы данных, получим все товары в корзине
        $itemsInCart = $this->entityManager->getRepository(ShopCart::class)->findBy(['sessionId' => $sessionId]);

        $logger->info('Items in cart after removal: ' . count($itemsInCart));

        return $this->redirectToRoute('app_shopCart');
    }

}
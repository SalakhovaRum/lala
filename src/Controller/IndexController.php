<?php

namespace App\Controller;

use App\Entity\ShopItems;
use App\Repository\ShopItemsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'title' => 'IndexController',
        ]);
    }

    #[Route('/shop/list', name: 'app_shopList')]
    public function shopList(ShopItemsRepository $itemsRepository): Response
    {
        $items = $itemsRepository->findAll();

        return $this->render('index/shopList.html.twig', [
            'title' => 'SHOP LIST',
            'items' => $items
        ]);
    }

    #[Route('/shop/item/{id<\d+>}', name: 'app_shopItem')]
    public function shopItem(int $id, ShopItemsRepository $itemsRepository): Response
    {
        $shopItem = $itemsRepository->find($id);

        if (!$shopItem) {
            throw $this->createNotFoundException('Товар не найден');
        }

        return $this->render('index/shopItem.html.twig', [
            'title' => 'SHOP ITEM ' . $id,
            'description' => $shopItem->getDefcription(),
            'price' => $shopItem->getPrice(),
            // Другие свойства сущности, которые вы хотите передать в шаблон
        ]);
    }

    #[Route('/shop/cart', name: 'app_shopCart')]
    public function shopCart(): Response
    {
        return $this->render('index/shopCart.html.twig', [
            'title' => 'SHOP CART',
        ]);
    }

    
}


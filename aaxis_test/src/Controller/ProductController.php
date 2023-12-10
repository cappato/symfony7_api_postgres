<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ProductController extends AbstractController
{
    #[Route('/api/products/create', name: 'create_products', methods: ['POST'])]
    public function createProducts(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entityManager->getConnection()->beginTransaction();

        try {
            $jsonData = json_decode($request->getContent(), true);

            if ($jsonData === null) {
                return new Response('Invalid JSON payload.', Response::HTTP_BAD_REQUEST);
            }

            $jsonData = $this->normalizeJsonInput($jsonData);
            $this->validateJsonDataCreate($jsonData);

            foreach ($jsonData as $data) {
                $product = new Product();
                $product->setSku($data['sku']);
                $product->setProductName($data['product_name']);
                $product->setDescription($data['description'] ?? null);
                $entityManager->persist($product);
            }

            $entityManager->flush();
            $entityManager->commit();

            return new Response('Successfully created products.');
        } catch (UniqueConstraintViolationException $e) {
            $entityManager->rollback();
            $duplicateSku = $data['sku'];
            return new Response('A product with the SKU already exists: ' . $duplicateSku, Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateJsonDataCreate(array $jsonData): void
    {
        foreach ($jsonData as $data) {
            if (!isset($data['sku'], $data['product_name'])) {
                throw new \Exception('Invalid JSON data format. Each record must have sku and product_name.');
            }
        }
    }

    private function validateJsonDataUpdate(array $jsonData): void
    {
        foreach ($jsonData as $data) {
            if (!isset($data['sku'])) {
                throw new \Exception('Invalid JSON data format. Each record must have sku.');
            }
            if (!isset($data['product_name']) && !isset($data['description'])) {
                throw new \Exception('Invalid JSON data format. Each record must have at least product_name or description.');
            }
        }
    }

    private function normalizeJsonInput($jsonData): array
    {
        if (!is_array(current($jsonData))) {
            return [$jsonData];
        }

        return $jsonData;
    }

    #[Route('/api/products/update', name: 'update_products', methods: ['POST'])]
    public function handleProductUpdates(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $jsonData = json_decode($request->getContent(), true);

            if ($jsonData === null) {
                throw new \Exception('Invalid JSON payload.');
            }
            
            $jsonData = $this->normalizeJsonInput($jsonData);
            $this->validateJsonDataUpdate($jsonData);

            $updatedSkus = $this->updateProductsInternal($jsonData, $entityManager);

            if (!empty($updatedSkus)) {
                $response = [
                    'message' => 'Some products were updated successfully.',
                    'updated_skus' => $updatedSkus,
                ];

                return $this->json($response, Response::HTTP_OK);
            }

            return new JsonResponse(['message' => 'No products were updated.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function updateProductsInternal(array $jsonData, EntityManagerInterface $entityManager): array
    {
        $updatedSkus = [];

        foreach ($jsonData as $data) {
            $sku = $data['sku'];

            $product = $entityManager->getRepository(Product::class)->findOneBy(['sku' => $sku]);

            if ($product) {
                if (isset($data['product_name'])) {
                    $product->setProductName($data['product_name']);
                }
                if (isset($data['description'])) {
                    $product->setDescription($data['description']);
                }

                $entityManager->persist($product);

                $updatedSkus[] = $sku;
            }
        }

        $entityManager->flush();

        return $updatedSkus;
    }

    #[Route('/api/products', name: 'get_all_products', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository): JsonResponse
    {
        try {
            $products = $productRepository->findAll();
            
            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'productName' => $product->getProductName(),
                    'description' => $product->getDescription(),
                    'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            }
    
            return $this->json($formattedProducts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while fetching products.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

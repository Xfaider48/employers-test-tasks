<?php


namespace App\Http\Controllers;


use App\Application;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generate(Request $request): Response
    {
        $faker = Application::getInstance()->getFaker();
        $numberOnGenerations = (int)$request->get('generate', 20);
        for ($i = 0; $i < $numberOnGenerations; $i++) {
            $product = new Product([
                'name' => $faker->productName,
                'price' => $faker->randomFloat(2, 0, 99999)
            ]);

            $product->save();
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}